<?php

require_once __DIR__ . '/../config/database.php';

$username = 'admin';
$password = 'admin123';
$name = 'Admin Store';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);

if ($stmt->fetch()) {
    echo "Admin already exists.\n";
    exit;
}

$insert = $pdo->prepare('INSERT INTO admin_users (username, password, name, status) VALUES (?, ?, ?, ?)');
$insert->execute([$username, $passwordHash, $name, 'active']);

echo "Admin created. Username: {$username} Password: {$password}\n";
echo "Delete database/seed-admin.php after use.\n";
