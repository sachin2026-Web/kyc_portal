<?php
require_once __DIR__ . '/db.php';

session_start();

$requiredFields = ['full_name', 'email', 'phone'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error_message'] = 'Please fill all required fields.';
        header('Location: index.php');
        exit;
    }
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$documentKeys = ['photo', 'bank_account', 'aadhaar_front', 'aadhaar_back', 'pan', 'signature'];
$uploadedPaths = [];

try {
    foreach ($documentKeys as $key) {
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Please upload all KYC documents. Missing: ' . $key);
        }

        $tmpName = $_FILES[$key]['tmp_name'];
        $originalName = basename($_FILES[$key]['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('Invalid file type for ' . $key . '. Allowed: jpg, jpeg, png, gif, webp');
        }

        $safeName = time() . '_' . uniqid('', true) . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
        $destination = $uploadDir . '/' . $safeName;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new RuntimeException('Could not upload file: ' . $key);
        }

        $uploadedPaths[$key] = 'uploads/' . $safeName;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare(
        'INSERT INTO kyc_records (full_name, email, phone, photo, bank_account, aadhaar_front, aadhaar_back, pan, signature, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")'
    );

    $stmt->bind_param(
        'sssssssss',
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone'],
        $uploadedPaths['photo'],
        $uploadedPaths['bank_account'],
        $uploadedPaths['aadhaar_front'],
        $uploadedPaths['aadhaar_back'],
        $uploadedPaths['pan'],
        $uploadedPaths['signature']
    );

    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to save KYC record.');
    }

    $_SESSION['success_message'] = 'KYC submitted successfully. Your status is pending.';
    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    foreach ($uploadedPaths as $filePath) {
        $absolutePath = __DIR__ . '/' . $filePath;
        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }
    }

    $_SESSION['error_message'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
