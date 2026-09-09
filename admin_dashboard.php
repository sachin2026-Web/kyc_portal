<?php
require_once __DIR__ . '/admin_auth.php';
requireAdmin();

$statusFilter = $_GET['status'] ?? 'all';
$allowedStatuses = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

$conn = getDbConnection();
$counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$countResult = $conn->query('SELECT status, COUNT(*) AS total FROM kyc_records GROUP BY status');
while ($countRow = $countResult->fetch_assoc()) {
    if (isset($counts[$countRow['status']])) {
        $counts[$countRow['status']] = (int) $countRow['total'];
    }
}
$counts['all'] = array_sum(array_slice($counts, 1));

if ($statusFilter === 'all') {
    $result = $conn->query('SELECT * FROM kyc_records ORDER BY created_at DESC');
} else {
    $stmt = $conn->prepare('SELECT * FROM kyc_records WHERE status = ? ORDER BY created_at DESC');
    $stmt->bind_param('s', $statusFilter);
    $stmt->execute();
    $result = $stmt->get_result();
}
$records = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | KYC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">KYC Portal</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Admin dashboard</h1>
                <p class="mt-1 text-sm text-slate-500">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">KYC form</a>
                <a href="admin_logout.php" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Logout</a>
            </div>
        </header>

        <?php if ($successMessage || $errorMessage): ?>
            <div class="mb-6 rounded-lg border <?= $errorMessage ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700'; ?> px-4 py-3 text-sm">
                <?= htmlspecialchars($errorMessage ?: $successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach (['all' => 'All applications', 'pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
                <a href="?status=<?= $key; ?>" class="rounded-2xl bg-white p-5 shadow-sm ring-1 <?= $statusFilter === $key ? 'ring-indigo-500' : 'ring-slate-200'; ?>">
                    <p class="text-sm text-slate-500"><?= $label; ?></p>
                    <p class="mt-2 text-3xl font-bold text-slate-900"><?= $counts[$key]; ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-6 flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-slate-800">KYC applications</h2>
                <span class="text-sm text-slate-500"><?= count($records); ?> shown</span>
            </div>
            <?php if (empty($records)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-slate-500">No applications found.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-3 py-3">Applicant</th><th class="px-3 py-3">Contact</th><th class="px-3 py-3">Submitted</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($records as $record): ?>
                                <?php $status = $record['status'] ?? 'pending'; $statusClass = ['approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'][$status] ?? 'bg-amber-100 text-amber-700'; ?>
                                <tr><td class="px-3 py-4"><p class="font-semibold text-slate-800"><?= htmlspecialchars($record['full_name'], ENT_QUOTES, 'UTF-8'); ?></p><p class="text-xs text-slate-500">#<?= (int) $record['id']; ?></p></td><td class="px-3 py-4 text-slate-600"><?= htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?><br><?= htmlspecialchars($record['phone'], ENT_QUOTES, 'UTF-8'); ?></td><td class="whitespace-nowrap px-3 py-4 text-slate-600"><?= htmlspecialchars(date('d M Y, h:i A', strtotime($record['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase <?= $statusClass; ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span></td><td class="px-3 py-4"><div class="flex justify-end gap-2"><a href="view_kyc.php?id=<?= (int) $record['id']; ?>" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">View</a><form method="POST" action="admin_action.php" class="flex gap-2"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="id" value="<?= (int) $record['id']; ?>"><button name="status" value="approved" class="rounded-lg bg-green-600 px-3 py-2 text-xs font-medium text-white hover:bg-green-700">Approve</button><button name="status" value="rejected" class="rounded-lg bg-red-600 px-3 py-2 text-xs font-medium text-white hover:bg-red-700">Reject</button></form></div></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>