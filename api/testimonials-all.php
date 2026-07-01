<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';

require_method('GET');

$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
$limit  = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 12;

try {
    $stmt = $pdo->prepare(
        "SELECT id, name, role, message, image_path, rating
         FROM testimonials
         WHERE status = 'visible'
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->execute([$limit, $offset]);
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
        'offset'   => $offset,
        'limit'    => $limit,
        'has_more' => ($offset + $limit) < $total,
    ]);
} catch (PDOException $e) {
    json_error('Gagal memuat testimoni', null, 500);
}
?>