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
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

function validate_product_payload(array $body, bool $partial = false): array
{
    $errors = [];
    $status = ['active', 'draft', 'out_of_stock'];

    if (!$partial || array_key_exists('name', $body)) {
        $name = trim((string) ($body['name'] ?? ''));
        if ($name === '') $errors[] = $partial ? 'name tidak boleh kosong' : 'name wajib diisi';
        if (strlen($name) > 150) $errors[] = 'name maksimal 150 karakter';
    }
    if (!$partial || array_key_exists('slug', $body)) {
        $slug = trim((string) ($body['slug'] ?? ''));
        if ($slug === '') $errors[] = $partial ? 'slug tidak boleh kosong' : 'slug wajib diisi';
        if ($slug !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) $errors[] = 'slug tidak valid';
        if (strlen($slug) > 180) $errors[] = 'slug maksimal 180 karakter';
    }
    if (!$partial || array_key_exists('price', $body)) {
        if (!isset($body['price']) || !is_numeric($body['price']) || (int) $body['price'] < 0) $errors[] = 'price wajib angka positif';
    }
    if (!$partial || array_key_exists('stock', $body)) {
        if (!isset($body['stock']) || !is_numeric($body['stock']) || (int) $body['stock'] < 0) $errors[] = 'stock wajib angka positif';
    }
    if (array_key_exists('original_price', $body) && $body['original_price'] !== null && $body['original_price'] !== '' && (!is_numeric($body['original_price']) || (int) $body['original_price'] < 0)) $errors[] = 'original_price wajib angka positif';
    if (array_key_exists('status', $body) && !in_array($body['status'], $status, true)) $errors[] = 'status hanya boleh: active, draft, out_of_stock';
    if (array_key_exists('image_url', $body) && trim((string) $body['image_url']) !== '') {
        $url = trim((string) $body['image_url']);
        if (strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') $errors[] = 'image_url harus URL https valid maksimal 255 karakter';
    }
    if (array_key_exists('description', $body) && strlen(trim((string) $body['description'])) > 5000) $errors[] = 'description maksimal 5000 karakter';

    return $errors;
}

function default_product_category_id(PDO $pdo): int
{
    $slug = 'akun-premium';

    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if ($row) return (int) $row['id'];

    $insert = $pdo->prepare(
        'INSERT INTO categories (name, slug, icon, status, sort_order)
         VALUES (?, ?, ?, ?, ?)'
    );
    $insert->execute(['Akun Premium', $slug, 'fa-solid fa-crown', 'active', 1]);

    return (int) $pdo->lastInsertId();
}

function product_category_id_from_payload(PDO $pdo, array $body): int
{
    if (empty($body['category_id'])) return default_product_category_id($pdo);

    $catId = (int) $body['category_id'];
    $catChk = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
    $catChk->execute([$catId]);
    if (!$catChk->fetch()) json_error('category_id tidak valid', null, 422);

    return $catId;
}

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
            if (in_array($_GET['status'], $allowedStatus, true)) {
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
        $errors = validate_product_payload($body);
        if ($errors) json_error('Validasi gagal', $errors, 422);

        // Cek slug unik
        $chk = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
        $chk->execute([$body['slug']]);
        if ($chk->fetch()) json_error('Slug sudah digunakan', null, 409);

        $catId = product_category_id_from_payload($pdo, $body);

        $stmt = $pdo->prepare(
            'INSERT INTO products
               (category_id, name, slug, description, price, original_price, stock, image_url, status, is_featured)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
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
        $errors = validate_product_payload($body, true);
        if ($errors) json_error('Validasi gagal', $errors, 422);

        // Cek slug unik (kecuali milik sendiri)
        if (!empty($body['slug'])) {
            $slugChk = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id != ?');
            $slugChk->execute([$body['slug'], $id]);
            if ($slugChk->fetch()) json_error('Slug sudah digunakan', null, 409);
        }

        $catId = array_key_exists('category_id', $body)
            ? product_category_id_from_payload($pdo, $body)
            : default_product_category_id($pdo);

        $stmt = $pdo->prepare(
            'UPDATE products SET
               category_id=?, name=?, slug=?, description=?, price=?, original_price=?,
               stock=?, image_url=?, status=?, is_featured=?
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
