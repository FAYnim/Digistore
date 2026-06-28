<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/rate-limit.php';
require_once __DIR__ . '/../includes/order-expiration.php';

require_method('POST');
rate_limit('payment-confirmation:' . rate_limit_identifier(), 10, 300);

$orderCode = strtoupper(trim($_POST['order_code'] ?? ''));
$senderName = trim($_POST['sender_name'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? '');
$note = trim($_POST['note'] ?? '');

if ($orderCode === '' || strlen($orderCode) > 50 || !preg_match('/^[A-Z0-9-]+$/', $orderCode)) json_error('Kode order tidak valid.', null, 422);
if ($senderName === '' || mb_strlen($senderName) > 100) json_error('Nama pengirim wajib diisi.', null, 422);
if ($paymentMethod === '' || mb_strlen($paymentMethod) > 50) json_error('Metode pembayaran wajib diisi.', null, 422);

if (empty($_FILES['proof']) || !is_uploaded_file($_FILES['proof']['tmp_name'])) json_error('Bukti bayar wajib diupload.', null, 422);
if ($_FILES['proof']['error'] !== UPLOAD_ERR_OK) json_error('Upload bukti bayar gagal.', null, 422);
if ((int) $_FILES['proof']['size'] > 2 * 1024 * 1024) json_error('Ukuran bukti bayar maksimal 2MB.', null, 422);

$extension = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array($extension, $allowedExtensions, true)) json_error('Bukti bayar hanya boleh JPG, PNG, atau PDF.', null, 422);

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['proof']['tmp_name']);
$allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
if (!in_array($mime, $allowedMimes, true)) json_error('Format bukti bayar tidak valid.', null, 422);

try {
    $stmt = $pdo->prepare('SELECT id, status FROM orders WHERE order_code = ? LIMIT 1');
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();

    if (!$order) json_error('Order tidak ditemukan.', null, 404);

    expire_pending_orders($pdo, (int) $order['id']);

    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();

    if (!$order) json_error('Order tidak ditemukan.', null, 404);
    if ($order['status'] === 'expired') json_error('Order ini sudah expired.', null, 422);
    if (!in_array($order['status'], ['pending', 'pending_payment'], true)) json_error('Order ini tidak menunggu pembayaran.', null, 422);

    $uploadDir = dirname(__DIR__) . '/uploads/payment-proofs';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) json_error('Folder upload tidak tersedia.', null, 500);

    $filename = $orderCode . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = 'uploads/payment-proofs/' . $filename;
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) json_error('Gagal menyimpan bukti bayar.', null, 500);

    $insert = $pdo->prepare('INSERT INTO payment_confirmations (order_id, sender_name, payment_method, note, proof_path) VALUES (?, ?, ?, ?, ?)');
    $insert->execute([
        (int) $order['id'],
        $senderName,
        $paymentMethod,
        $note !== '' ? $note : null,
        $relativePath,
    ]);

    json_success('Konfirmasi pembayaran berhasil dikirim. Admin akan melakukan verifikasi.', [
        'proof_path' => $relativePath,
    ], 201);
} catch (Throwable $e) {
    error_log('Payment confirmation failed: ' . $e->getMessage());
    json_error('Gagal mengirim konfirmasi pembayaran.', null, 500);
}
