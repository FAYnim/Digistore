<?php

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

$defaults = [
    'payment_qris_enabled' => '0',
    'payment_qris_image' => '',
    'payment_bank_enabled' => '0',
    'payment_bank_name' => '',
    'payment_bank_account' => '',
    'payment_bank_holder' => '',
    'payment_instruction' => '',
    'payment_admin_whatsapp' => '',
    'payment_whatsapp_message' => 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.',
];

$allowedKeys = array_keys($defaults);

function get_payment_settings(PDO $pdo, array $defaults): array
{
    $placeholders = implode(',', array_fill(0, count($defaults), '?'));
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM store_settings WHERE setting_key IN ($placeholders)");
    $stmt->execute(array_keys($defaults));

    $settings = $defaults;
    foreach ($stmt->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

switch ($method) {
    case 'GET':
        json_success('Setting pembayaran berhasil dimuat', get_payment_settings($pdo, $defaults));
        break;

    case 'PUT':
        $body = json_body();
        $errors = [];

        $qrisEnabled = (string) ($body['payment_qris_enabled'] ?? '');
        $bankEnabled = (string) ($body['payment_bank_enabled'] ?? '');
        $qrisImage = trim((string) ($body['payment_qris_image'] ?? ''));
        $instruction = trim((string) ($body['payment_instruction'] ?? ''));
        $adminWhatsapp = preg_replace('/\D+/', '', trim((string) ($body['payment_admin_whatsapp'] ?? '')));
        if (str_starts_with($adminWhatsapp, '0')) $adminWhatsapp = '62' . substr($adminWhatsapp, 1);
        $bankName = trim((string) ($body['payment_bank_name'] ?? ''));
        $bankAccount = trim((string) ($body['payment_bank_account'] ?? ''));
        $bankHolder = trim((string) ($body['payment_bank_holder'] ?? ''));

        if (!in_array($qrisEnabled, ['0', '1'], true)) $errors[] = 'Status QRIS tidak valid';
        if (!in_array($bankEnabled, ['0', '1'], true)) $errors[] = 'Status bank transfer tidak valid';
        if ($qrisEnabled === '0' && $bankEnabled === '0') $errors[] = 'Aktifkan minimal satu metode pembayaran';
        if ($qrisEnabled === '1' && $qrisImage === '') $errors[] = 'QRIS Image URL wajib diisi';
        if ($qrisImage !== '' && (strlen($qrisImage) > 255 || !filter_var($qrisImage, FILTER_VALIDATE_URL) || parse_url($qrisImage, PHP_URL_SCHEME) !== 'https')) $errors[] = 'QRIS Image URL harus https valid maksimal 255 karakter';
        if (($qrisEnabled === '1' || $bankEnabled === '1') && $instruction === '') $errors[] = 'Instruksi pembayaran wajib diisi';
        if (strlen($instruction) > 1000) $errors[] = 'Instruksi pembayaran maksimal 1000 karakter';
        if ($adminWhatsapp === '') $errors[] = 'Nomor WhatsApp admin wajib diisi';
        if ($adminWhatsapp !== '' && !ctype_digit($adminWhatsapp)) $errors[] = 'Nomor WhatsApp admin hanya boleh angka';
        if ($adminWhatsapp !== '' && (strlen($adminWhatsapp) < 10 || strlen($adminWhatsapp) > 15)) $errors[] = 'Nomor WhatsApp admin harus 10-15 digit';
        if ($bankEnabled === '1' && $bankName === '') $errors[] = 'Nama bank wajib diisi';
        if (strlen($bankName) > 100) $errors[] = 'Nama bank maksimal 100 karakter';
        if ($bankEnabled === '1' && $bankAccount === '') $errors[] = 'Nomor rekening wajib diisi';
        if ($bankAccount !== '' && (!ctype_digit($bankAccount) || strlen($bankAccount) > 50)) $errors[] = 'Nomor rekening hanya angka maksimal 50 digit';
        if ($bankEnabled === '1' && $bankHolder === '') $errors[] = 'Nama pemilik rekening wajib diisi';
        if (strlen($bankHolder) > 100) $errors[] = 'Nama pemilik rekening maksimal 100 karakter';
        if (strlen(trim((string) ($body['payment_whatsapp_message'] ?? ''))) > 1000) $errors[] = 'Template pesan maksimal 1000 karakter';

        if ($errors) json_error('Validasi gagal', $errors, 422);

        $payload = [];
        foreach ($allowedKeys as $key) {
            $payload[$key] = trim((string) ($body[$key] ?? $defaults[$key]));
        }
        $payload['payment_admin_whatsapp'] = $adminWhatsapp;
        if ($payload['payment_whatsapp_message'] === '') $payload['payment_whatsapp_message'] = $defaults['payment_whatsapp_message'];

        $stmt = $pdo->prepare(
            'INSERT INTO store_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        foreach ($payload as $key => $value) {
            $stmt->execute([$key, $value]);
        }

        json_success('Setting pembayaran berhasil disimpan', null);
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
