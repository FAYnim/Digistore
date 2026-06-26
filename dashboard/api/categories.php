<?php
/**
 * API: Categories
 * GET    /dashboard/api/categories.php         — list semua kategori
 * GET    /dashboard/api/categories.php?id=N    — detail satu kategori
 * POST   /dashboard/api/categories.php         — tambah kategori
 * PUT    /dashboard/api/categories.php?id=N    — edit kategori
 * DELETE /dashboard/api/categories.php?id=N    — hapus kategori
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

function validate_category_payload(array $body, bool $partial = false): array
{
    $errors = [];

    if (!$partial || array_key_exists('name', $body)) {
        $name = trim((string) ($body['name'] ?? ''));
        if ($name === '') $errors[] = $partial ? 'name tidak boleh kosong' : 'name wajib diisi';
        if (strlen($name) > 100) $errors[] = 'name maksimal 100 karakter';
    }
    if (!$partial || array_key_exists('slug', $body)) {
        $slug = trim((string) ($body['slug'] ?? ''));
        if ($slug === '') $errors[] = $partial ? 'slug tidak boleh kosong' : 'slug wajib diisi';
        if ($slug !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) $errors[] = 'slug tidak valid';
        if (strlen($slug) > 120) $errors[] = 'slug maksimal 120 karakter';
    }
    if (array_key_exists('status', $body) && !in_array($body['status'], ['active', 'inactive'], true)) $errors[] = 'status hanya boleh: active, inactive';
    if (array_key_exists('icon', $body) && strlen(trim((string) $body['icon'])) > 100) $errors[] = 'icon maksimal 100 karakter';
    if (array_key_exists('sort_order', $body) && !is_numeric($body['sort_order'])) $errors[] = 'sort_order wajib angka';

    return $errors;
}

switch ($method) {

    // ----------------------------------------------------------------
    // GET — list atau detail
    // ----------------------------------------------------------------
    case 'GET':
        if ($id) {
            // Detail satu kategori
            $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                json_error('Kategori tidak ditemukan', null, 404);
            }
            json_success('Kategori berhasil dimuat', $row);
        }

        // List semua kategori + jumlah produk
        $stmt = $pdo->query(
            'SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.name ASC'
        );
        json_success('Kategori berhasil dimuat', $stmt->fetchAll());
        break;

    // ----------------------------------------------------------------
    // POST — tambah kategori
    // ----------------------------------------------------------------
    case 'POST':
        $body = json_body();

        $errors = validate_category_payload($body);
        if ($errors) json_error('Validasi gagal', $errors, 422);

        // Cek slug unik
        $chk = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
        $chk->execute([$body['slug']]);
        if ($chk->fetch()) json_error('Slug sudah digunakan', null, 409);

        $stmt = $pdo->prepare(
            'INSERT INTO categories (name, slug, icon, status, sort_order)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($body['name']),
            trim($body['slug']),
            $body['icon']       ?? null,
            $body['status']     ?? 'active',
            (int)($body['sort_order'] ?? 0),
        ]);

        $newId = (int) $pdo->lastInsertId();
        $stmt2 = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt2->execute([$newId]);
        json_success('Kategori berhasil ditambahkan', $stmt2->fetch(), 201);
        break;

    // ----------------------------------------------------------------
    // PUT — edit kategori
    // ----------------------------------------------------------------
    case 'PUT':
        if (!$id) json_error('ID kategori diperlukan', null, 400);

        // Cek exists
        $chk = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $chk->execute([$id]);
        if (!$chk->fetch()) json_error('Kategori tidak ditemukan', null, 404);

        $body   = json_body();
        $errors = validate_category_payload($body, true);
        if ($errors) json_error('Validasi gagal', $errors, 422);

        // Cek slug unik (kecuali milik sendiri)
        if (!empty($body['slug'])) {
            $slugChk = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
            $slugChk->execute([$body['slug'], $id]);
            if ($slugChk->fetch()) json_error('Slug sudah digunakan', null, 409);
        }

        // Ambil data lama sebagai fallback
        $old = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $old->execute([$id]);
        $current = $old->fetch();

        $stmt = $pdo->prepare(
            'UPDATE categories SET name=?, slug=?, icon=?, status=?, sort_order=? WHERE id=?'
        );
        $stmt->execute([
            isset($body['name'])       ? trim($body['name'])   : $current['name'],
            isset($body['slug'])       ? trim($body['slug'])   : $current['slug'],
            array_key_exists('icon', $body) ? $body['icon']   : $current['icon'],
            $body['status']            ?? $current['status'],
            isset($body['sort_order']) ? (int)$body['sort_order'] : $current['sort_order'],
            $id,
        ]);

        $updated = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $updated->execute([$id]);
        json_success('Kategori berhasil diperbarui', $updated->fetch());
        break;

    // ----------------------------------------------------------------
    // DELETE — hapus kategori
    // ----------------------------------------------------------------
    case 'DELETE':
        if (!$id) json_error('ID kategori diperlukan', null, 400);

        $chk = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $chk->execute([$id]);
        if (!$chk->fetch()) json_error('Kategori tidak ditemukan', null, 404);

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        json_success('Kategori berhasil dihapus');
        break;

    // ----------------------------------------------------------------
    // Method tidak dikenal
    // ----------------------------------------------------------------
    default:
        json_error('Method tidak diizinkan', null, 405);
}
