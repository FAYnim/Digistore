<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$allowedKeys = [
    'store_name',
    'store_tagline',
    'store_description',
    'store_whatsapp',
    'store_email',
    'store_instagram',
    'default_theme',
    'accent_color',
];

try {
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM store_settings');
    $settings = array_fill_keys($allowedKeys, '');

    foreach ($stmt->fetchAll() as $row) {
        if (in_array($row['setting_key'], $allowedKeys, true)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    json_success('Setting toko berhasil dimuat', $settings);
} catch (PDOException $e) {
    json_error('Gagal memuat setting toko', null, 500);
}
