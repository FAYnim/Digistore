<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 3;
$limit = max(1, min($limit, 50));

try {
    $stmt = $pdo->query(
        "SELECT id, name, role, message, rating
         FROM testimonials
         WHERE status = 'visible'
         ORDER BY created_at DESC
         LIMIT $limit"
    );
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id'] = (int) $row['id'];
        $row['rating'] = (int) $row['rating'];
    }
    unset($row);

    json_success('Testimoni berhasil dimuat', $rows);
} catch (PDOException $e) {
    json_error('Gagal memuat testimoni', null, 500);
}
