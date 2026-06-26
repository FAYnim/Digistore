<?php

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/env.php';

define('DB_HOST',    env_value('DB_HOST', '127.0.0.1'));
define('DB_NAME',    env_value('DB_NAME', 'digital_store'));
define('DB_USER',    env_value('DB_USER', 'digital_store_app'));
define('DB_PASS',    env_value('DB_PASS', ''));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8mb4'));

set_exception_handler(function (Throwable $e): void {
    error_log('Unhandled exception: ' . $e->getMessage());
    json_error('Terjadi kesalahan server.', null, 500);
});

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    json_error('Koneksi database gagal. Periksa konfigurasi server.', null, 500);
}
