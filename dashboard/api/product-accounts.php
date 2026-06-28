<?php
/**
 * API: Product Accounts
 * GET    /dashboard/api/product-accounts.php?product_id=N
 * POST   /dashboard/api/product-accounts.php
 * PUT    /dashboard/api/product-accounts.php?id=N
 * DELETE /dashboard/api/product-accounts.php?id=N&force=1
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

function account_row(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM product_accounts WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function ensure_product_exists(PDO $pdo, int $product_id): void
{
    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) json_error('Produk tidak ditemukan', null, 404);
}

function validate_account_data(array $body): string
{
    $account_data = trim((string)($body['account_data'] ?? ''));
    if ($account_data === '') json_error('Data akun wajib diisi', null, 422);
    if (strlen($account_data) > 10000) json_error('Data akun maksimal 10000 karakter', null, 422);

    return $account_data;
}

function validate_account_status(array $body, string $fallback = 'available'): string
{
    $status = (string)($body['status'] ?? $fallback);
    if (!in_array($status, ['available', 'reserved', 'sold'], true)) {
        json_error('Status akun tidak valid', null, 422);
    }

    return $status;
}

switch ($method) {
    case 'GET':
        $product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
        if ($product_id <= 0) json_error('product_id diperlukan', null, 400);

        ensure_product_exists($pdo, $product_id);
        $stmt = $pdo->prepare('SELECT id, product_id, account_data, status, order_id, sold_at, created_at, updated_at FROM product_accounts WHERE product_id = ? ORDER BY id DESC');
        $stmt->execute([$product_id]);

        json_success('Akun produk berhasil dimuat', $stmt->fetchAll());
        break;

    case 'POST':
        $body = json_body();
        $product_id = (int)($body['product_id'] ?? 0);
        if ($product_id <= 0) json_error('product_id diperlukan', null, 422);
        ensure_product_exists($pdo, $product_id);

        $account_data = validate_account_data($body);
        $status = validate_account_status($body);
        $source_id = (int)($body['source_id'] ?? 0);

        if ($source_id > 0) {
            $source = account_row($pdo, $source_id);
            if (!$source || (int)$source['product_id'] !== $product_id) json_error('Akun sumber tidak ditemukan', null, 404);
            $account_data = $source['account_data'];
        }

        $stmt = $pdo->prepare('INSERT INTO product_accounts (product_id, account_data, status) VALUES (?, ?, ?)');
        $stmt->execute([$product_id, $account_data, $source_id > 0 ? 'available' : $status]);
        $newId = (int)$pdo->lastInsertId();
        $created = account_row($pdo, $newId);

        json_success($source_id > 0 ? 'Akun berhasil diduplikat' : 'Akun berhasil ditambahkan', $created, 201);
        break;

    case 'PUT':
        if (!$id) json_error('ID akun diperlukan', null, 400);
        $current = account_row($pdo, $id);
        if (!$current) json_error('Akun tidak ditemukan', null, 404);

        $body = json_body();
        $account_data = validate_account_data($body);
        $status = validate_account_status($body, $current['status']);

        $stmt = $pdo->prepare('UPDATE product_accounts SET account_data = ?, status = ? WHERE id = ?');
        $stmt->execute([$account_data, $status, $id]);

        json_success('Akun berhasil diperbarui', account_row($pdo, $id));
        break;

    case 'DELETE':
        if (!$id) json_error('ID akun diperlukan', null, 400);
        $current = account_row($pdo, $id);
        if (!$current) json_error('Akun tidak ditemukan', null, 404);

        $force = isset($_GET['force']) && $_GET['force'] === '1';
        if (in_array($current['status'], ['reserved', 'sold'], true) && !$force) {
            json_error('Akun reserved/sold perlu konfirmasi sebelum dihapus', ['requires_confirmation' => true], 409);
        }

        $stmt = $pdo->prepare('DELETE FROM product_accounts WHERE id = ?');
        $stmt->execute([$id]);

        json_success('Akun berhasil dihapus');
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
