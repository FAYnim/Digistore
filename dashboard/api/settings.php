<?php
/**
 * API: Settings
 * GET   /dashboard/api/settings.php   — ambil semua setting sebagai object key-value
 * PUT   /dashboard/api/settings.php   — update settings (batch)
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);

// Key yang boleh diubah
$allowedKeys = [
    'store_name', 'store_tagline', 'store_description',
    'store_whatsapp', 'store_email', 'store_instagram',
    'default_theme', 'accent_color',
];

switch ($method) {

    // ----------------------------------------------------------------
    // GET — ambil semua setting sebagai key-value object
    // ----------------------------------------------------------------
    case 'GET':
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM store_settings');
        $rows = $stmt->fetchAll();
        $data = [];
        foreach ($rows as $row) {
            $data[$row['setting_key']] = $row['setting_value'];
        }
        json_success('Setting berhasil dimuat', $data);
        break;

    // ----------------------------------------------------------------
    // PUT — update settings
    // ----------------------------------------------------------------
    case 'PUT':
        $body   = json_body();
        $errors = [];

        // Validasi wajib
        if (isset($body['store_name']) && empty(trim($body['store_name']))) {
            $errors[] = 'store_name tidak boleh kosong';
        }
        // Validasi email
        if (!empty($body['store_email']) && !filter_var($body['store_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'store_email harus berformat email yang valid';
        }
        // Validasi whatsapp (hanya angka)
        if (!empty($body['store_whatsapp']) && !ctype_digit($body['store_whatsapp'])) {
            $errors[] = 'store_whatsapp hanya boleh berisi angka';
        }
        if ($errors) json_error('Validasi gagal', $errors, 422);

        $stmt = $pdo->prepare(
            'INSERT INTO store_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $body)) {
                $stmt->execute([$key, $body[$key]]);
            }
        }

        // Kembalikan data terbaru
        $updated = $pdo->query('SELECT setting_key, setting_value FROM store_settings');
        $data    = [];
        foreach ($updated->fetchAll() as $row) {
            $data[$row['setting_key']] = $row['setting_value'];
        }
        json_success('Setting berhasil disimpan', $data);
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
