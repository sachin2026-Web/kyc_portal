<?php
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $conn = getDbConnection();
    $stmt = $conn->prepare('SELECT * FROM kyc_records WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    $record = null;
}

if (!$record) {
    header('Location: index.php');
    exit;
}

$documentLabels = [
    'photo' => 'Photo',
    'bank_account' => 'Bank Account',
    'aadhaar_front' => 'Aadhaar Front',
    'aadhaar_back' => 'Aadhaar Back',
    'pan' => 'PAN Card',
    'signature' => 'Signature',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View KYC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">KYC Details</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900"><?= htmlspecialchars($record['full_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            </div>
            <a href="index.php" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                Back to List
            </a>
        </div>

        <div class="mb-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm text-slate-500">Email</p>
                <p class="mt-2 font-semibold text-slate-800"><?= htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm text-slate-500">Phone</p>
                <p class="mt-2 font-semibold text-slate-800"><?= htmlspecialchars($record['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm text-slate-500">Status</p>
                <p class="mt-2 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                    <?= htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($documentLabels as $key => $label): ?>
                <?php if (!empty($record[$key])): ?>
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                        <div class="border-b border-slate-200 px-4 py-3 font-semibold text-slate-800"><?= $label; ?></div>
                        <div class="p-4">
                            <img src="<?= htmlspecialchars($record[$key], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= $label; ?>" class="h-60 w-full rounded-xl object-cover">
                        </div>
                        <div class="px-4 pb-4">
                            <a href="<?= htmlspecialchars($record[$key], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="inline-flex rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                                Open Full Image
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
