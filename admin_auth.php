<?php
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function ensureDefaultAdmin(mysqli $conn): void
{
    $conn->query(
        'CREATE TABLE IF NOT EXISTS admin_users (' .
        'id INT AUTO_INCREMENT PRIMARY KEY, ' .
        'name VARCHAR(100) NOT NULL, ' .
        'email VARCHAR(255) NOT NULL UNIQUE, ' .
        'password_hash VARCHAR(255) NOT NULL, ' .
        'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)'
    );
    $result = $conn->query('SELECT id FROM admin_users LIMIT 1');
    if ($result && $result->num_rows > 0) {
        return;
    }

    $stmt = $conn->prepare('INSERT INTO admin_users (name, email, password_hash) VALUES (?, ?, ?)');
    $name = 'Administrator';
    $email = ADMIN_DEFAULT_EMAIL;
    $passwordHash = password_hash(ADMIN_DEFAULT_PASSWORD, PASSWORD_DEFAULT);
    $stmt->bind_param('sss', $name, $email, $passwordHash);
    $stmt->execute();
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: admin_login.php');
        exit;
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): void
{
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        throw new RuntimeException('Invalid request token. Please try again.');
    }
}