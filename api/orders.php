<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$code = trim($_GET['code'] ?? '');
if ($code === '') json_error('Kode order wajib diisi.', null, 422);

try {
    $stmt = $pdo->prepare('SELECT id, order_code, customer_name, total_amount, payment_method, payment_deadline, status, note, delivery_note, created_at FROM orders WHERE order_code = ? LIMIT 1');
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if (!$order) json_error('Order tidak ditemukan.', null, 404);

    $items = $pdo->prepare('SELECT product_name, quantity, price, subtotal FROM order_items WHERE order_id = ?');
    $items->execute([(int) $order['id']]);
    $orderItems = $items->fetchAll();

    foreach ($orderItems as &$item) {
        $item['quantity'] = (int) $item['quantity'];
        $item['price'] = (int) $item['price'];
        $item['subtotal'] = (int) $item['subtotal'];
    }
    unset($item);

    $settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM store_settings WHERE setting_key IN ('payment_qris_image', 'payment_instruction', 'payment_whatsapp_message', 'store_whatsapp')");
    $settingsStmt->execute();
    $settings = [];
    foreach ($settingsStmt->fetchAll() as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    unset($order['id']);
    $order['total_amount'] = (int) $order['total_amount'];
    $order['items'] = $orderItems;
    $order['payment'] = [
        'qris_image' => $settings['payment_qris_image'] ?? 'https://placehold.co/400x400?text=QRIS+Dummy',
        'instruction' => $settings['payment_instruction'] ?? 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.',
        'whatsapp' => $settings['store_whatsapp'] ?? '',
        'whatsapp_message' => $settings['payment_whatsapp_message'] ?? 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.',
    ];

    json_success('Order berhasil dimuat', $order);
} catch (Throwable $e) {
    json_error('Gagal memuat order.', null, 500);
}
