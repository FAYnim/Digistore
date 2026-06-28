<?php

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../includes/order-expiration.php';

require_method('GET');

expire_pending_orders($pdo);

function scalar_int(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function scalar_float(PDO $pdo, string $sql, array $params = []): float
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (float) $stmt->fetchColumn();
}

$totalProducts = scalar_int($pdo, 'SELECT COUNT(*) FROM products WHERE archived_at IS NULL');
$availableProducts = scalar_int($pdo, 'SELECT COUNT(*) FROM products p WHERE p.archived_at IS NULL AND p.status = "active" AND EXISTS (SELECT 1 FROM product_accounts pa WHERE pa.product_id = p.id AND pa.status = "available")');
$outOfStockProducts = scalar_int($pdo, 'SELECT COUNT(*) FROM products p WHERE p.archived_at IS NULL AND (p.status = "out_of_stock" OR NOT EXISTS (SELECT 1 FROM product_accounts pa WHERE pa.product_id = p.id AND pa.status = "available"))');
$processingProducts = scalar_int($pdo, 'SELECT COUNT(DISTINCT oi.product_id) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.status = "pending_verify" AND oi.product_id IS NOT NULL');
$todayIncome = scalar_int($pdo, 'SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status = "completed"');
$todayOrders = scalar_int($pdo, 'SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()');
$totalIncome = scalar_int($pdo, 'SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = "completed"');
// $averageRating = scalar_float($pdo, 'SELECT COALESCE(AVG(rating), 0) FROM testimonials WHERE status = "visible"'); // disembunyikan sementara
$averageRating = 0;

$recentStmt = $pdo->query('SELECT o.id, o.order_code, o.customer_name, o.total_amount, o.status, COALESCE(GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ", "), "—") AS products FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.id GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5');
$recentOrders = $recentStmt->fetchAll();
foreach ($recentOrders as &$order) {
    $legacyStatusMap = ['pending' => 'pending_payment', 'paid' => 'completed', 'processing' => 'completed', 'delivered' => 'completed'];
    if (isset($legacyStatusMap[$order['status']])) $order['status'] = $legacyStatusMap[$order['status']];
    $order['total_amount'] = (int) $order['total_amount'];
}
unset($order);

$featuredStmt = $pdo->query('SELECT id, name, price, image_url, sold_count FROM products WHERE archived_at IS NULL AND status = "active" ORDER BY is_featured DESC, sold_count DESC, created_at DESC LIMIT 5');
$featuredProducts = $featuredStmt->fetchAll();
foreach ($featuredProducts as &$product) {
    $product['price'] = (int) $product['price'];
    $product['sold_count'] = (int) $product['sold_count'];
}
unset($product);

$incomeStmt = $pdo->query('SELECT DATE(created_at) AS day, DATE_FORMAT(created_at, "%d/%m") AS label, COALESCE(SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END), 0) AS total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at), DATE_FORMAT(created_at, "%d/%m") ORDER BY day ASC');
$incomeChart = $incomeStmt->fetchAll();
foreach ($incomeChart as &$row) {
    $row['total'] = (int) $row['total'];
}
unset($row);

$statusStmt = $pdo->query('SELECT CASE WHEN status IN ("pending", "pending_payment") THEN "pending_payment" WHEN status IN ("paid", "processing", "delivered") THEN "completed" ELSE status END AS status, COUNT(*) AS total FROM orders GROUP BY CASE WHEN status IN ("pending", "pending_payment") THEN "pending_payment" WHEN status IN ("paid", "processing", "delivered") THEN "completed" ELSE status END ORDER BY FIELD(status, "pending_payment", "pending_verify", "completed", "expired", "cancelled")');
$orderStatusChart = [];
$statusLabels = [
    'pending_payment' => 'Menunggu Bayar',
    'pending_verify' => 'Menunggu Verifikasi',
    'completed' => 'Selesai',
    'expired' => 'Expired',
    'cancelled' => 'Batal',
];
foreach ($statusStmt->fetchAll() as $row) {
    $status = $row['status'];
    $orderStatusChart[] = [
        'status' => $status,
        'label' => $statusLabels[$status] ?? $status,
        'total' => (int) $row['total'],
    ];
}

json_success('Statistik dashboard berhasil dimuat', [
    'total_products' => $totalProducts,
    'active_products' => $availableProducts,
    'available_products' => $availableProducts,
    'out_of_stock_products' => $outOfStockProducts,
    'processing_products' => $processingProducts,
    'today_income' => $todayIncome,
    'today_orders' => $todayOrders,
    'total_income' => $totalIncome,
    'average_rating' => $averageRating > 0 ? number_format($averageRating, 1) : null,
    'recent_orders' => $recentOrders,
    'featured_products' => $featuredProducts,
    'income_chart' => $incomeChart,
    'order_status_chart' => $orderStatusChart,
]);
