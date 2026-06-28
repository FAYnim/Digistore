<?php

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

require_method('POST');
csrf_validate_request();

if (empty($_FILES['qris_image']) || !is_uploaded_file($_FILES['qris_image']['tmp_name'])) {
    json_error('Gambar QRIS wajib diupload.', null, 422);
}

if ($_FILES['qris_image']['error'] !== UPLOAD_ERR_OK) {
    json_error('Upload gambar gagal.', null, 422);
}

if ((int) $_FILES['qris_image']['size'] > 2 * 1024 * 1024) {
    json_error('Ukuran gambar maksimal 2MB.', null, 422);
}

$extension = strtolower(pathinfo($_FILES['qris_image']['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png'];
if (!in_array($extension, $allowedExtensions, true)) {
    json_error('Format gambar hanya boleh JPG, JPEG, atau PNG.', null, 422);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['qris_image']['tmp_name']);
$allowedMimes = ['image/jpeg', 'image/png'];
if (!in_array($mime, $allowedMimes, true)) {
    json_error('Format file tidak valid.', null, 422);
}

$uploadDir = dirname(__DIR__) . '/uploads/qris';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    json_error('Folder upload tidak tersedia.', null, 500);
}

try {
    $stmt = $pdo->prepare("SELECT setting_value FROM store_settings WHERE setting_key = 'payment_qris_image' LIMIT 1");
    $stmt->execute();
    $oldPath = $stmt->fetchColumn();

    if ($oldPath && str_starts_with($oldPath, 'uploads/qris/')) {
        $oldFile = dirname(__DIR__) . '/' . $oldPath;
        if (is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    $filename = 'qris-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = 'uploads/qris/' . $filename;
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES['qris_image']['tmp_name'], $targetPath)) {
        json_error('Gagal menyimpan gambar QRIS.', null, 500);
    }

    json_success('Gambar QRIS berhasil diupload.', [
        'path' => $relativePath,
    ]);
} catch (Throwable $e) {
    error_log('QRIS upload failed: ' . $e->getMessage());
    json_error('Gagal mengupload gambar QRIS.', null, 500);
}
