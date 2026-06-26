<?php

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);

$defaults = [
    'payment_qris_enabled' => '1',
    'payment_qris_image' => 'https://placehold.co/400x400?text=QRIS+Dummy',
    'payment_bank_enabled' => '0',
    'payment_bank_name' => '',
    'payment_bank_account' => '',
    'payment_bank_holder' => '',
    'payment_instruction' => 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.',
    'payment_admin_whatsapp' => '6281234567890',
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
        if ($qrisEnabled === '1' && $qrisImage === '') $errors[] = 'QRIS Image URL wajib diisi';
        if ($qrisImage !== '' && !filter_var($qrisImage, FILTER_VALIDATE_URL)) $errors[] = 'QRIS Image URL tidak valid';
        if ($instruction === '') $errors[] = 'Instruksi pembayaran wajib diisi';
        if ($adminWhatsapp === '') $errors[] = 'Nomor WhatsApp admin wajib diisi';
        if ($adminWhatsapp !== '' && !ctype_digit($adminWhatsapp)) $errors[] = 'Nomor WhatsApp admin hanya boleh angka';
        if ($adminWhatsapp !== '' && (strlen($adminWhatsapp) < 10 || strlen($adminWhatsapp) > 15)) $errors[] = 'Nomor WhatsApp admin harus 10-15 digit';
        if ($bankEnabled === '1' && $bankName === '') $errors[] = 'Nama bank wajib diisi';
        if ($bankEnabled === '1' && $bankAccount === '') $errors[] = 'Nomor rekening wajib diisi';
        if ($bankEnabled === '1' && $bankHolder === '') $errors[] = 'Nama pemilik rekening wajib diisi';

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
