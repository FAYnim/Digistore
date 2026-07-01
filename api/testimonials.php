<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 3;
$limit = max(1, min($limit, 50));

try {
    $stmt = $pdo->query(
        "SELECT id, name, role, message, image_path, rating
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

    $countStmt = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'visible'");
    $total = (int) $countStmt->fetchColumn();

    json_success('Testimoni berhasil dimuat', [
        'data'     => $rows,
        'total'    => $total,
        'has_more' => $total > $limit,
    ]);
} catch (PDOException $e) {
    json_error('Gagal memuat testimoni', null, 500);
}
