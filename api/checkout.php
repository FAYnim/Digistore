<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/rate-limit.php';

require_method('POST');
rate_limit('checkout:' . rate_limit_identifier(), 10, 300);

function generate_order_code(PDO $pdo): string
{
    $date = date('Ymd');

    do {
        $random = strtoupper(bin2hex(random_bytes(8)));
        $code = "ORD-$date-$random";
        $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
        $stmt->execute([$code]);
    } while ($stmt->fetch());

    return $code;
}

$body = json_body();
$productId = isset($body['product_id']) ? (int) $body['product_id'] : 0;
$quantity = isset($body['quantity']) ? (int) $body['quantity'] : 1;
$customerName = trim($body['customer_name'] ?? '');
$customerEmail = trim($body['customer_email'] ?? '');
$customerPhone = trim($body['customer_phone'] ?? '');
$note = trim($body['note'] ?? '');

if ($productId <= 0) json_error('Produk wajib dipilih.', null, 422);
if ($quantity < 1) json_error('Jumlah minimal 1.', null, 422);
if ($customerName === '') json_error('Nama wajib diisi.', null, 422);
if ($customerPhone === '') json_error('WhatsApp wajib diisi.', null, 422);
if ($customerEmail !== '' && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) json_error('Email tidak valid.', null, 422);

try {
    $orderCode = generate_order_code($pdo);
    $deadline = date('Y-m-d 23:59:00', strtotime('+1 day'));

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, name, price, stock, status FROM products WHERE id = ? FOR UPDATE');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product || $product['status'] !== 'active' || (int) $product['stock'] < $quantity) {
        $pdo->rollBack();
        json_error('Produk tidak tersedia atau stok tidak cukup', null, 422);
    }

    $price = (int) $product['price'];
    $subtotal = $price * $quantity;

    $stock = $pdo->prepare("UPDATE products SET stock = stock - ?, status = IF(stock - ? <= 0, 'out_of_stock', status) WHERE id = ? AND status = 'active' AND stock >= ?");
    $stock->execute([$quantity, $quantity, $productId, $quantity]);

    if ($stock->rowCount() !== 1) {
        $pdo->rollBack();
        json_error('Stok produk tidak cukup', null, 422);
    }

    $order = $pdo->prepare('INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, total_amount, payment_method, payment_deadline, status, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $order->execute([
        $orderCode,
        $customerName,
        $customerEmail !== '' ? $customerEmail : null,
        $customerPhone,
        $subtotal,
        'QRIS',
        $deadline,
        'pending_payment',
        $note !== '' ? $note : null,
    ]);

    $orderId = (int) $pdo->lastInsertId();
    $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    $item->execute([$orderId, $productId, $product['name'], $quantity, $price, $subtotal]);

    $pdo->commit();

    json_success('Pesanan berhasil dibuat', [
        'order_code' => $orderCode,
        'redirect_url' => '/payment.php?code=' . rawurlencode($orderCode),
    ], 201);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Checkout failed: ' . $e->getMessage());
    json_error('Gagal membuat pesanan.', null, 500);
}
