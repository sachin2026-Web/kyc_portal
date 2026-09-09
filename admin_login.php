<?php
require_once __DIR__ . '/admin_auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $conn = getDbConnection();
        ensureDefaultAdmin($conn);

        $stmt = $conn->prepare('SELECT id, name, password_hash FROM admin_users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            throw new RuntimeException('Invalid email or password.');
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: admin_dashboard.php');
        exit;
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | KYC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
    <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">KYC Portal</p>
        <h1 class="mt-3 text-3xl font-bold text-slate-900">Admin sign in</h1>
        <p class="mt-2 text-sm text-slate-500">Review and manage submitted KYC applications.</p>

        <?php if ($errorMessage): ?>
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-6 space-y-5">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-medium text-white hover:bg-indigo-700">Sign in</button>
        </form>
        <a href="index.php" class="mt-5 block text-center text-sm font-medium text-slate-500 hover:text-slate-800">Back to KYC form</a>
    </main>
</body>
</html>