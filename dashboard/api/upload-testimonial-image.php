<?php
require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../auth/csrf.php';

require_method('POST');
csrf_validate_request();

if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
    json_error('Gambar wajib diupload.', null, 422);
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    json_error('Upload gambar gagal.', null, 422);
}

if ((int) $_FILES['image']['size'] > 2 * 1024 * 1024) {
    json_error('Ukuran gambar maksimal 2MB.', null, 422);
}

$extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png'];
if (!in_array($extension, $allowedExtensions, true)) {
    json_error('Format gambar hanya boleh JPG, JPEG, atau PNG.', null, 422);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['image']['tmp_name']);
$allowedMimes = ['image/jpeg', 'image/png'];
if (!in_array($mime, $allowedMimes, true)) {
    json_error('Format file tidak valid.', null, 422);
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/testimonials';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    json_error('Folder upload tidak tersedia.', null, 500);
}

try {
    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $relativePath = 'uploads/testimonials/' . $filename;
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        json_error('Gagal menyimpan gambar.', null, 500);
    }

    json_success('Gambar berhasil diupload.', [
        'path' => $relativePath,
    ]);
} catch (Throwable $e) {
    error_log('Testimonial image upload failed: ' . $e->getMessage());
    json_error('Gagal mengupload gambar.', null, 500);
}
?>