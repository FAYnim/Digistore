<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../config/database.php';

start_admin_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';

if (!csrf_validate($csrfToken)) {
    $_SESSION['login_error'] = 'Session tidak valid. Silakan coba lagi.';
    header('Location: ../login.php');
    exit;
}

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password, name, status FROM admin_users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    $_SESSION['login_error'] = 'Username atau password salah.';
    header('Location: ../login.php');
    exit;
}

if ($admin['status'] !== 'active') {
    $_SESSION['login_error'] = 'Akun tidak aktif.';
    header('Location: ../login.php');
    exit;
}

session_regenerate_id(true);

$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = (int) $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
$_SESSION['admin_name'] = $admin['name'];
unset($_SESSION['csrf_token'], $_SESSION['login_error']);

$update = $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?');
$update->execute([(int) $admin['id']]);

header('Location: ../index.php');
exit;
