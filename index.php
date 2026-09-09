<?php
require_once __DIR__ . '/db.php';

session_start();

$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

try {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM kyc_records ORDER BY created_at DESC");
    $records = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    $records = [];
    $errorMessage = $errorMessage ?: 'Database is not connected yet. Please import the SQL file first.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <header class="mb-8 text-center">
            <div class="mb-4 flex justify-end"><a href="admin_login.php" class="text-sm font-medium text-slate-600 hover:text-indigo-600">Admin Login</a></div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">KYC Portal</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-900">Know Your Customer Form</h1>
        </header>

        <?php if ($successMessage): ?>
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-6 text-xl font-semibold text-slate-800">Upload KYC Documents</h2>

                <form action="process_kyc.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Full Name</label>
                            <input type="text" name="full_name" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Enter full name">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Enter email">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Phone</label>
                            <input type="tel" name="phone" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Enter phone number">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Photo</label>
                            <input type="file" name="photo" accept="image/*" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Bank Account</label>
                            <input type="file" name="bank_account" accept="image/*" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Aadhar Card Front</label>
                            <input type="file" name="aadhaar_front" accept="image/*" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Aadhar Card Back</label>
                            <input type="file" name="aadhaar_back" accept="image/*" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Pan Card</label>
                            <input type="file" name="pan" accept="image/*" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Signature</label>
                            <input type="file" name="signature" accept="image/*" required class="block w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-white">
                        </div>
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        Submit
                    </button>
                </form>
            </section>

            <aside class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 p-6 text-white shadow-sm">
                <h3 class="text-xl font-semibold">KYC Checklist</h3>
                <ul class="mt-6 space-y-4 text-sm text-indigo-50">
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span> Photo and signature should be clear and readable.</li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span> Aadhaar front and back should be full document images.</li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span> Bank account copy should show the account holder details.</li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span> PAN card must be valid and visible.</li>
                </ul>
            </aside>
        </div>

        <section class="mt-10 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">My KYC Uploads</h2>
                    <p class="mt-1 text-sm text-slate-500">Your uploaded documents and current verification status.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <?= count($records); ?> Uploads
                </span>
            </div>

            <?php if (empty($records)): ?>
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-slate-500">
                    No KYC uploads found yet.
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($records as $record): ?>
                        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 p-4 md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-lg font-semibold text-slate-800"><?= htmlspecialchars($record['full_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-700">
                                        <?= htmlspecialchars($record['status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-slate-600">Email: <?= htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm text-slate-600">Phone: <?= htmlspecialchars($record['phone'], ENT_QUOTES, 'UTF-8'); ?></p>

                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    <?php foreach (['photo', 'bank_account', 'aadhaar_front', 'aadhaar_back', 'pan', 'signature'] as $docKey): ?>
                                        <?php if (!empty($record[$docKey])): ?>
                                            <span class="rounded bg-slate-100 px-2 py-1 text-slate-600"><?= str_replace('_', ' ', $docKey); ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <a href="view_kyc.php?id=<?= (int)$record['id']; ?>" class="inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">
                                    View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
