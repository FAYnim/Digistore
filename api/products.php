<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$allowedSort = [
    'newest'      => 'p.created_at DESC',
    'price_low'   => 'p.price ASC',
    'price_high'  => 'p.price DESC',
    'rating_high' => 'p.rating DESC',
    'sold_high'   => 'p.sold_count DESC',
];

$conditions = ["p.status IN ('active', 'out_of_stock')", "(c.status = 'active' OR p.category_id IS NULL)"];
$params = [];
$featured = isset($_GET['featured']) && $_GET['featured'] === 'true';
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug !== '') {
    $conditions[] = 'p.slug = ?';
    $params[] = $slug;
}

if ($featured) {
    $conditions[] = 'p.status = ?';
    $params[] = 'active';
    $conditions[] = 'p.is_featured = 1';
}

if (!empty($_GET['search'])) {
    $keyword = '%' . trim($_GET['search']) . '%';
    $conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = $keyword;
    $params[] = $keyword;
}

if (!empty($_GET['category'])) {
    $conditions[] = 'c.slug = ?';
    $params[] = trim($_GET['category']);
}

$sort = $_GET['sort'] ?? 'newest';
$orderBy = $allowedSort[$sort] ?? $allowedSort['newest'];
$where = implode(' AND ', $conditions);
$sql = "SELECT
          p.id,
          p.category_id,
          c.name AS category_name,
          c.slug AS category_slug,
          p.name,
          p.slug,
          p.description,
          p.price,
          p.original_price,
          p.stock,
          p.image_url,
          p.badge,
          p.status,
          p.is_featured,
          p.sold_count,
          p.rating,
          p.created_at
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where
        ORDER BY $orderBy";

if (isset($_GET['limit']) || $featured) {
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 4;
    $limit = max(1, min($limit, 50));
    $sql .= " LIMIT $limit";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id'] = (int) $row['id'];
        $row['category_id'] = $row['category_id'] !== null ? (int) $row['category_id'] : null;
        $row['price'] = (int) $row['price'];
        $row['original_price'] = $row['original_price'] !== null ? (int) $row['original_price'] : null;
        $row['stock'] = (int) $row['stock'];
        $row['is_featured'] = (bool) $row['is_featured'];
        $row['sold_count'] = (int) $row['sold_count'];
        $row['rating'] = (float) $row['rating'];
    }
    unset($row);

    if ($slug !== '') {
        if (!$rows) {
            json_error('Produk tidak ditemukan.', null, 404);
        }
        json_success('Produk berhasil dimuat', $rows[0]);
    }

    json_success('Produk berhasil dimuat', $rows);
} catch (PDOException $e) {
    error_log('Product listing failed: ' . $e->getMessage());
    json_error('Gagal memuat produk', null, 500);
}
