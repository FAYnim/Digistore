<?php
/**
 * API: Products
 * GET    /dashboard/api/products.php                        — list produk (+ filter: search, category_id, status)
 * GET    /dashboard/api/products.php?id=N                  — detail satu produk
 * POST   /dashboard/api/products.php                       — tambah produk
 * PUT    /dashboard/api/products.php?id=N                  — edit produk
 * DELETE /dashboard/api/products.php?id=N                  — hapus produk
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

switch ($method) {

    // ----------------------------------------------------------------
    // GET — list atau detail
    // ----------------------------------------------------------------
    case 'GET':
        if ($id) {
            // Detail satu produk + nama kategori
            $stmt = $pdo->prepare(
                'SELECT p.*, c.name AS category_name
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 WHERE p.id = ?'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) json_error('Produk tidak ditemukan', null, 404);
            $row['is_featured'] = (bool) $row['is_featured'];
            json_success('Produk berhasil dimuat', $row);
        }

        // List produk dengan filter opsional
        $conditions = [];
        $params     = [];

        if (!empty($_GET['search'])) {
            $conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $keyword      = '%' . $_GET['search'] . '%';
            $params[]     = $keyword;
            $params[]     = $keyword;
        }
        if (!empty($_GET['category_id'])) {
            $conditions[] = 'p.category_id = ?';
            $params[]     = (int) $_GET['category_id'];
        }
        if (!empty($_GET['status'])) {
            $allowedStatus = ['active', 'draft', 'out_of_stock'];
            if (in_array($_GET['status'], $allowedStatus)) {
                $conditions[] = 'p.status = ?';
                $params[]     = $_GET['status'];
            }
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT p.*, c.name AS category_name
                  FROM products p
                  LEFT JOIN categories c ON c.id = p.category_id
                  $where
                  ORDER BY p.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Cast types
        foreach ($rows as &$row) {
            $row['is_featured'] = (bool) $row['is_featured'];
            $row['price']          = (int) $row['price'];
            $row['original_price'] = $row['original_price'] !== null ? (int) $row['original_price'] : null;
            $row['stock']          = (int) $row['stock'];
            $row['sold_count']     = (int) $row['sold_count'];
            $row['rating']         = (float) $row['rating'];
        }
        unset($row);

        json_success('Produk berhasil dimuat', $rows);
        break;

    // ----------------------------------------------------------------
    // POST — tambah produk
    // ----------------------------------------------------------------
    case 'POST':
        $body   = json_body();
        $errors = [];

        if (empty($body['name']))                          $errors[] = 'name wajib diisi';
        if (empty($body['slug']))                          $errors[] = 'slug wajib diisi';
        if (!isset($body['price']) || !is_numeric($body['price'])) $errors[] = 'price wajib angka';
        if (!isset($body['stock']) || !is_numeric($body['stock'])) $errors[] = 'stock wajib angka';
        if (isset($body['status']) && !in_array($body['status'], ['active', 'draft', 'out_of_stock'])) {
            $errors[] = 'status hanya boleh: active, draft, out_of_stock';
        }
        if ($errors) json_error('Validasi gagal', $errors, 422);

        // Cek slug unik
        $chk = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
        $chk->execute([$body['slug']]);
        if ($chk->fetch()) json_error('Slug sudah digunakan', null, 409);

        // Validasi category_id jika diisi
        $catId = !empty($body['category_id']) ? (int) $body['category_id'] : null;
        if ($catId) {
            $catChk = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
            $catChk->execute([$catId]);
            if (!$catChk->fetch()) json_error('category_id tidak valid', null, 422);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO products
               (category_id, name, slug, description, price, original_price, stock, image_url, badge, status, is_featured)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $catId,
            trim($body['name']),
            trim($body['slug']),
            $body['description']    ?? null,
            (int)   $body['price'],
            isset($body['original_price']) ? (int) $body['original_price'] : null,
            (int)   $body['stock'],
            $body['image_url']      ?? null,
            $body['badge']          ?? null,
            $body['status']         ?? 'draft',
            !empty($body['is_featured']) ? 1 : 0,
        ]);

        $newId = (int) $pdo->lastInsertId();
        $stmt2 = $pdo->prepare(
            'SELECT p.*, c.name AS category_name FROM products p
             LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?'
        );
        $stmt2->execute([$newId]);
        $created = $stmt2->fetch();
        $created['is_featured'] = (bool) $created['is_featured'];
        json_success('Produk berhasil ditambahkan', $created, 201);
        break;

    // ----------------------------------------------------------------
    // PUT — edit produk
    // ----------------------------------------------------------------
    case 'PUT':
        if (!$id) json_error('ID produk diperlukan', null, 400);

        // Ambil data lama
        $old = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $old->execute([$id]);
        $current = $old->fetch();
        if (!$current) json_error('Produk tidak ditemukan', null, 404);

        $body   = json_body();
        $errors = [];
        if (isset($body['name'])  && empty($body['name']))  $errors[] = 'name tidak boleh kosong';
        if (isset($body['slug'])  && empty($body['slug']))  $errors[] = 'slug tidak boleh kosong';
        if (isset($body['price']) && !is_numeric($body['price'])) $errors[] = 'price wajib angka';
        if (isset($body['stock']) && !is_numeric($body['stock'])) $errors[] = 'stock wajib angka';
        if (isset($body['status']) && !in_array($body['status'], ['active', 'draft', 'out_of_stock'])) {
            $errors[] = 'status hanya boleh: active, draft, out_of_stock';
        }
        if ($errors) json_error('Validasi gagal', $errors, 422);

        // Cek slug unik (kecuali milik sendiri)
        if (!empty($body['slug'])) {
            $slugChk = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id != ?');
            $slugChk->execute([$body['slug'], $id]);
            if ($slugChk->fetch()) json_error('Slug sudah digunakan', null, 409);
        }

        $catId = array_key_exists('category_id', $body)
            ? (!empty($body['category_id']) ? (int) $body['category_id'] : null)
            : $current['category_id'];

        $stmt = $pdo->prepare(
            'UPDATE products SET
               category_id=?, name=?, slug=?, description=?, price=?, original_price=?,
               stock=?, image_url=?, badge=?, status=?, is_featured=?
             WHERE id=?'
        );
        $stmt->execute([
            $catId,
            isset($body['name'])           ? trim($body['name'])           : $current['name'],
            isset($body['slug'])           ? trim($body['slug'])           : $current['slug'],
            array_key_exists('description', $body) ? $body['description'] : $current['description'],
            isset($body['price'])          ? (int) $body['price']          : (int) $current['price'],
            array_key_exists('original_price', $body)
                ? (!empty($body['original_price']) ? (int) $body['original_price'] : null)
                : $current['original_price'],
            isset($body['stock'])          ? (int) $body['stock']          : (int) $current['stock'],
            array_key_exists('image_url', $body) ? $body['image_url']     : $current['image_url'],
            array_key_exists('badge', $body)     ? $body['badge']         : $current['badge'],
            $body['status']     ?? $current['status'],
            isset($body['is_featured'])    ? ($body['is_featured'] ? 1 : 0) : (int) $current['is_featured'],
            $id,
        ]);

        $updated = $pdo->prepare(
            'SELECT p.*, c.name AS category_name FROM products p
             LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?'
        );
        $updated->execute([$id]);
        $result = $updated->fetch();
        $result['is_featured'] = (bool) $result['is_featured'];
        json_success('Produk berhasil diperbarui', $result);
        break;

    // ----------------------------------------------------------------
    // DELETE — hapus produk
    // ----------------------------------------------------------------
    case 'DELETE':
        if (!$id) json_error('ID produk diperlukan', null, 400);

        $chk = $pdo->prepare('SELECT id FROM products WHERE id = ?');
        $chk->execute([$id]);
        if (!$chk->fetch()) json_error('Produk tidak ditemukan', null, 404);

        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        json_success('Produk berhasil dihapus');
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
