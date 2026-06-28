<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/rate-limit.php';
require_once __DIR__ . '/../includes/order-expiration.php';

require_method('GET');
rate_limit('order-lookup:' . rate_limit_identifier(), 30, 300);

$code = strtoupper(trim($_GET['code'] ?? ''));
if ($code === '') json_error('Kode order wajib diisi.', null, 422);
if (strlen($code) > 50 || !preg_match('/^[A-Z0-9-]+$/', $code)) json_error('Kode order tidak valid.', null, 422);

try {
    $stmt = $pdo->prepare('SELECT id, order_code, customer_name, customer_email, customer_phone, total_amount, payment_method, payment_deadline, status, note, delivery_note, created_at FROM orders WHERE order_code = ? LIMIT 1');
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if (!$order) json_error('Order tidak ditemukan.', null, 404);

    expire_pending_orders($pdo, (int) $order['id']);

    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if ($order['status'] === 'pending') $order['status'] = 'pending_payment';

    $items = $pdo->prepare('SELECT product_name, quantity, price, subtotal FROM order_items WHERE order_id = ?');
    $items->execute([(int) $order['id']]);
    $orderItems = $items->fetchAll();

    foreach ($orderItems as &$item) {
        $item['quantity'] = (int) $item['quantity'];
        $item['price'] = (int) $item['price'];
        $item['subtotal'] = (int) $item['subtotal'];
    }
    unset($item);

    $paymentDefaults = [
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
    $placeholders = implode(',', array_fill(0, count($paymentDefaults), '?'));
    $settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM store_settings WHERE setting_key IN ($placeholders)");
    $settingsStmt->execute(array_keys($paymentDefaults));
    $settings = $paymentDefaults;
    foreach ($settingsStmt->fetchAll() as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    $statusLabels = [
        'pending' => 'Menunggu Pembayaran',
        'pending_payment' => 'Menunggu Pembayaran',
        'paid' => 'Pembayaran Diterima',
        'processing' => 'Diproses',
        'delivered' => 'Dikirim',
        'completed' => 'Selesai',
        'expired' => 'Expired',
        'cancelled' => 'Dibatalkan',
    ];
    $qrisEnabled = $settings['payment_qris_enabled'] === '1' && trim((string) $settings['payment_qris_image']) !== '';
    $bankEnabled = $settings['payment_bank_enabled'] === '1' && trim((string) $settings['payment_bank_name']) !== '' && trim((string) $settings['payment_bank_account']) !== '' && trim((string) $settings['payment_bank_holder']) !== '';
    $paymentAvailable = ($qrisEnabled || $bankEnabled) && trim((string) $settings['payment_instruction']) !== '';
    $whatsappTemplate = trim((string) $settings['payment_whatsapp_message']) ?: $paymentDefaults['payment_whatsapp_message'];
    $adminWhatsapp = preg_replace('/\D+/', '', (string) $settings['payment_admin_whatsapp']);
    if (str_starts_with($adminWhatsapp, '0')) $adminWhatsapp = '62' . substr($adminWhatsapp, 1);
    if (!preg_match('/^\d{10,15}$/', $adminWhatsapp)) $adminWhatsapp = '';
    $whatsappMessage = strtr($whatsappTemplate, [
        '{order_code}' => $order['order_code'],
        '{customer_name}' => $order['customer_name'],
        '{total_amount}' => 'Rp' . number_format((int) $order['total_amount'], 0, ',', '.'),
        '{status}' => $statusLabels[$order['status']] ?? $order['status'],
    ]);

    unset($order['id']);
    $order['total_amount'] = (int) $order['total_amount'];
    $order['items'] = $orderItems;
    $order['payment'] = [
        'available' => $paymentAvailable,
        'qris_enabled' => $paymentAvailable && $qrisEnabled,
        'qris_image' => $paymentAvailable && $qrisEnabled ? $settings['payment_qris_image'] : '',
        'bank_enabled' => $paymentAvailable && $bankEnabled,
        'bank_name' => $paymentAvailable && $bankEnabled ? $settings['payment_bank_name'] : '',
        'bank_account' => $paymentAvailable && $bankEnabled ? $settings['payment_bank_account'] : '',
        'bank_holder' => $paymentAvailable && $bankEnabled ? $settings['payment_bank_holder'] : '',
        'instruction' => $paymentAvailable ? $settings['payment_instruction'] : '',
        'admin_whatsapp' => $adminWhatsapp,
        'whatsapp_message' => $whatsappMessage,
    ];

    json_success('Order berhasil dimuat', $order);
} catch (Throwable $e) {
    error_log('Order lookup failed: ' . $e->getMessage());
    json_error('Gagal memuat order.', null, 500);
}
