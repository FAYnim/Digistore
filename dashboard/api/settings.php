<?php
/**
 * API: Settings
 * GET   /dashboard/api/settings.php   — ambil semua setting sebagai object key-value
 * PUT   /dashboard/api/settings.php   — update settings (batch)
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

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

        if (isset($body['store_name']) && trim((string) $body['store_name']) === '') $errors[] = 'store_name tidak boleh kosong';
        if (isset($body['store_name']) && strlen(trim((string) $body['store_name'])) > 100) $errors[] = 'store_name maksimal 100 karakter';
        if (isset($body['store_tagline']) && strlen(trim((string) $body['store_tagline'])) > 160) $errors[] = 'store_tagline maksimal 160 karakter';
        if (isset($body['store_description']) && strlen(trim((string) $body['store_description'])) > 1000) $errors[] = 'store_description maksimal 1000 karakter';
        if (!empty($body['store_email']) && (!filter_var($body['store_email'], FILTER_VALIDATE_EMAIL) || strlen((string) $body['store_email']) > 150)) $errors[] = 'store_email harus valid maksimal 150 karakter';
        if (!empty($body['store_whatsapp']) && (!ctype_digit((string) $body['store_whatsapp']) || strlen((string) $body['store_whatsapp']) < 10 || strlen((string) $body['store_whatsapp']) > 15)) $errors[] = 'store_whatsapp harus 10-15 digit';
        if (!empty($body['store_instagram']) && strlen(trim((string) $body['store_instagram'])) > 100) $errors[] = 'store_instagram maksimal 100 karakter';
        if (isset($body['default_theme']) && !in_array($body['default_theme'], ['light', 'dark', 'system'], true)) $errors[] = 'default_theme tidak valid';
        if (isset($body['accent_color']) && !preg_match('/^#[0-9a-fA-F]{6}$/', (string) $body['accent_color'])) $errors[] = 'accent_color tidak valid';
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
