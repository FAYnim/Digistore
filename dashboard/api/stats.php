<?php
/**
 * API: Stats
 * GET /dashboard/api/stats.php   — statistik ringkasan untuk overview dashboard
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET') {
    json_error('Method tidak diizinkan', null, 405);
}

// Total, tersedia, dan habis produk
$p = $pdo->query('SELECT COUNT(*) AS total, SUM(status = "active" AND stock > 0) AS available, SUM(stock <= 0) AS out_of_stock FROM products');
$products = $p->fetch();

// Total & hari ini pesanan
$today = date('Y-m-d');
$o     = $pdo->prepare(
    'SELECT COUNT(*) AS total, SUM(DATE(created_at) = ?) AS today_count FROM orders'
);
$o->execute([$today]);
$orders = $o->fetch();

// Penghasilan dari pesanan dibayar/selesai
$i = $pdo->prepare(
    'SELECT COALESCE(SUM(total_amount), 0) AS total_income,
            COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN total_amount ELSE 0 END), 0) AS today_income
     FROM orders
     WHERE status IN ("paid", "processing", "delivered", "completed")'
);
$i->execute([$today]);
$income = $i->fetch();

$incomeChart = [];
for ($day = 6; $day >= 0; $day--) {
    $date = date('Y-m-d', strtotime("-$day days"));
    $incomeChart[$date] = [
        'date'  => $date,
        'label' => date('d M', strtotime($date)),
        'total' => 0,
    ];
}

$ic = $pdo->prepare(
    'SELECT DATE(created_at) AS order_date, COALESCE(SUM(total_amount), 0) AS total
     FROM orders
     WHERE status IN ("paid", "processing", "delivered", "completed")
       AND DATE(created_at) BETWEEN ? AND ?
     GROUP BY DATE(created_at)'
);
$ic->execute([array_key_first($incomeChart), array_key_last($incomeChart)]);
foreach ($ic->fetchAll() as $row) {
    if (isset($incomeChart[$row['order_date']])) {
        $incomeChart[$row['order_date']]['total'] = (int) $row['total'];
    }
}
$incomeChart = array_values($incomeChart);

// Produk pada pesanan yang sedang diproses
$pp = $pdo->query(
    'SELECT COALESCE(SUM(oi.quantity), 0) AS processing_products
     FROM order_items oi
     INNER JOIN orders o ON o.id = oi.order_id
     WHERE o.status IN ("paid", "processing")'
);
$processingProducts = $pp->fetch();

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

$os = $pdo->query(
    'SELECT status, COUNT(*) AS total
     FROM orders
     GROUP BY status
     ORDER BY total DESC'
);
$orderStatusChart = array_map(function ($row) {
    $labels = [
        'pending' => 'Menunggu Pembayaran',
        'pending_payment' => 'Menunggu Pembayaran',
        'paid' => 'Pembayaran Diterima',
        'processing' => 'Diproses',
        'delivered' => 'Dikirim',
        'completed' => 'Selesai',
        'expired' => 'Expired',
        'cancelled' => 'Batal',
    ];

    return [
        'status' => $row['status'],
        'label'  => $labels[$row['status']] ?? $row['status'],
        'total'  => (int) $row['total'],
    ];
}, $os->fetchAll());

foreach ($recentOrders as &$ord) {
    $ord['total_amount'] = (int) $ord['total_amount'];
}
unset($ord);

json_success('Statistik berhasil dimuat', [
    'total_products'        => (int) $products['total'],
    'active_products'       => (int) $products['available'],
    'available_products'    => (int) $products['available'],
    'out_of_stock_products' => (int) $products['out_of_stock'],
    'processing_products'   => (int) $processingProducts['processing_products'],
    'total_orders'          => (int) $orders['total'],
    'today_orders'          => (int) $orders['today_count'],
    'today_income'          => (int) $income['today_income'],
    'total_income'          => (int) $income['total_income'],
    'total_testimonials'    => (int) $testimonials['total'],
    'average_rating'        => $testimonials['avg_rating'] ? (float) $testimonials['avg_rating'] : 0.0,
    'featured_products'     => $featuredProducts,
    'recent_orders'         => $recentOrders,
    'income_chart'          => $incomeChart,
    'order_status_chart'    => $orderStatusChart,
]);
