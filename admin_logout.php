<?php
require_once __DIR__ . '/admin_auth.php';

unset($_SESSION['admin_id'], $_SESSION['admin_name']);
session_regenerate_id(true);
header('Location: admin_login.php');
exit;