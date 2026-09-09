<?php
require_once __DIR__ . '/admin_auth.php';
requireAdmin();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Invalid request method.');
    }
    verifyCsrfToken();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id <= 0 || !in_array($status, ['approved', 'rejected'], true)) {
        throw new RuntimeException('Invalid application update.');
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare('UPDATE kyc_records SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    if (!$stmt->execute() || $stmt->affected_rows === 0) {
        throw new RuntimeException('Application was not found or status is already unchanged.');
    }
    $_SESSION['success_message'] = 'KYC application marked as ' . $status . '.';
} catch (Throwable $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: admin_dashboard.php');
exit;