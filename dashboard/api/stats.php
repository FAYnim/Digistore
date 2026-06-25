<?php
/**
 * API: Stats
 * GET /dashboard/api/stats.php   — statistik ringkasan untuk overview dashboard
 */

require_once __DIR__ . '/../config/database.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET') {
    json_error('Method tidak diizinkan', null, 405);
}

// Total & aktif produk
$p = $pdo->query('SELECT COUNT(*) AS total, SUM(status = "active") AS active FROM products');
$products = $p->fetch();

// Total & hari ini pesanan
$today = date('Y-m-d');
$o     = $pdo->prepare(
    'SELECT COUNT(*) AS total, SUM(DATE(created_at) = ?) AS today_count FROM orders'
);
$o->execute([$today]);
$orders = $o->fetch();

// Total testimoni & rata-rata rating
$t = $pdo->query('SELECT COUNT(*) AS total, ROUND(AVG(rating), 1) AS avg_rating FROM testimonials WHERE status = "visible"');
$testimonials = $t->fetch();

// Produk terlaris (featured)
$fp = $pdo->query(
    'SELECT p.id, p.name, p.price, p.image_url, p.sold_count, p.rating, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_featured = 1 AND p.status = "active"
     ORDER BY p.sold_count DESC
     LIMIT 5'
);
$featuredProducts = $fp->fetchAll();

// Pesanan terbaru (5 teratas)
$ro = $pdo->query(
    'SELECT o.id, o.order_code, o.customer_name, o.total_amount, o.status, o.created_at,
            GROUP_CONCAT(oi.product_name SEPARATOR ", ") AS products
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT 5'
);
$recentOrders = $ro->fetchAll();

foreach ($recentOrders as &$ord) {
    $ord['total_amount'] = (int) $ord['total_amount'];
}
unset($ord);

json_success('Statistik berhasil dimuat', [
    'total_products'     => (int) $products['total'],
    'active_products'    => (int) $products['active'],
    'total_orders'       => (int) $orders['total'],
    'today_orders'       => (int) $orders['today_count'],
    'total_testimonials' => (int) $testimonials['total'],
    'average_rating'     => $testimonials['avg_rating'] ? (float) $testimonials['avg_rating'] : 0.0,
    'featured_products'  => $featuredProducts,
    'recent_orders'      => $recentOrders,
]);
