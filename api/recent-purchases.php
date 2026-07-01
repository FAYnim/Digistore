<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$limit = max(1, min($limit, 50));

function mask_customer_name(string $name): string {
    $name = trim($name);
    if ($name === '') return '';
    $first = mb_substr($name, 0, 1, 'UTF-8');
    return $first . '***';
}

try {
    $stmt = $pdo->prepare(
        "SELECT
           o.customer_name,
           oi.product_name,
           p.slug AS product_slug,
           o.created_at
         FROM orders o
         JOIN order_items oi ON oi.order_id = o.id
         LEFT JOIN products p ON p.id = oi.product_id
         WHERE o.status = 'completed'
         ORDER BY o.created_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $data = array_map(static function (array $row): array {
        return [
            'customer_name' => mask_customer_name((string) $row['customer_name']),
            'product_name'  => (string) $row['product_name'],
            'product_slug'  => (string) ($row['product_slug'] ?? ''),
            'created_at'    => (string) $row['created_at'],
        ];
    }, $rows);

    json_success('Pembeli terbaru berhasil dimuat', $data);
} catch (PDOException $e) {
    error_log('recent-purchases error: ' . $e->getMessage());
    json_error('Gagal memuat pembeli terbaru', null, 500);
}
