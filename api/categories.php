<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

try {
    $stmt = $pdo->query(
        "SELECT
           c.id,
           c.name,
           c.slug,
           c.icon,
           c.status,
           c.sort_order,
           COUNT(p.id) AS product_count
         FROM categories c
         LEFT JOIN products p
           ON p.category_id = c.id
           AND p.status IN ('active', 'out_of_stock')
         WHERE c.status = 'active'
         GROUP BY c.id
         ORDER BY c.sort_order ASC, c.name ASC"
    );
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id'] = (int) $row['id'];
        $row['sort_order'] = (int) $row['sort_order'];
        $row['product_count'] = (int) $row['product_count'];
    }
    unset($row);

    json_success('Kategori berhasil dimuat', $rows);
} catch (PDOException $e) {
    json_error('Gagal memuat kategori', null, 500);
}
