<?php
/**
 * API: Categories
 * GET    /dashboard/api/categories.php         — list semua kategori
 * GET    /dashboard/api/categories.php?id=N    — detail satu kategori
 * POST   /dashboard/api/categories.php         — tambah kategori
 * PUT    /dashboard/api/categories.php?id=N    — edit kategori
 * DELETE /dashboard/api/categories.php?id=N    — hapus kategori
 */

require_once __DIR__ . '/../config/database.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

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

        // Validasi
        $errors = [];
        if (empty($body['name']))  $errors[] = 'name wajib diisi';
        if (empty($body['slug']))  $errors[] = 'slug wajib diisi';
        if (isset($body['status']) && !in_array($body['status'], ['active', 'inactive'])) {
            $errors[] = 'status hanya boleh: active, inactive';
        }
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
        $errors = [];
        if (isset($body['name'])   && empty($body['name']))   $errors[] = 'name tidak boleh kosong';
        if (isset($body['slug'])   && empty($body['slug']))   $errors[] = 'slug tidak boleh kosong';
        if (isset($body['status']) && !in_array($body['status'], ['active', 'inactive'])) {
            $errors[] = 'status hanya boleh: active, inactive';
        }
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
