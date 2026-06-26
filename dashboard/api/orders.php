<?php
/**
 * API: Orders
 * GET    /dashboard/api/orders.php              — list pesanan (+ filter: status, search)
 * GET    /dashboard/api/orders.php?id=N         — detail pesanan + order items
 * PUT    /dashboard/api/orders.php?id=N         — update status dan delivery note pesanan
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

$validStatuses = ['pending', 'paid', 'completed', 'cancelled'];

switch ($method) {

    // ----------------------------------------------------------------
    // GET — list atau detail
    // ----------------------------------------------------------------
    case 'GET':
        if ($id) {
            // Detail pesanan + items
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order) json_error('Pesanan tidak ditemukan', null, 404);

            // Ambil order items
            $items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $items->execute([$id]);
            $order['items']        = $items->fetchAll();
            $order['total_amount'] = (int) $order['total_amount'];

            json_success('Detail pesanan berhasil dimuat', $order);
        }

        // List pesanan dengan filter
        $conditions = [];
        $params     = [];

        if (!empty($_GET['status']) && in_array($_GET['status'], $validStatuses)) {
            $conditions[] = 'o.status = ?';
            $params[]     = $_GET['status'];
        }
        if (!empty($_GET['search'])) {
            $conditions[] = '(o.order_code LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ?)';
            $keyword      = '%' . $_GET['search'] . '%';
            $params[]     = $keyword;
            $params[]     = $keyword;
            $params[]     = $keyword;
            $params[]     = $keyword;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt  = $pdo->prepare("SELECT o.*, COALESCE(oi.items_count, 0) AS items_count, oi.items_summary FROM orders o LEFT JOIN (SELECT order_id, COUNT(id) AS items_count, GROUP_CONCAT(product_name ORDER BY id SEPARATOR ', ') AS items_summary FROM order_items GROUP BY order_id) oi ON oi.order_id = o.id $where ORDER BY o.created_at DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['total_amount'] = (int) $row['total_amount'];
            $row['items_count'] = (int) $row['items_count'];
            $row['items_summary'] = $row['items_count'] > 1 ? $row['items_count'] . ' produk' : ($row['items_summary'] ?: '—');
        }
        unset($row);

        json_success('Pesanan berhasil dimuat', $rows);
        break;

    // ----------------------------------------------------------------
    // PUT — update status pesanan
    // ----------------------------------------------------------------
    case 'PUT':
        if (!$id) json_error('ID pesanan diperlukan', null, 400);

        $chk = $pdo->prepare('SELECT id FROM orders WHERE id = ?');
        $chk->execute([$id]);
        if (!$chk->fetch()) json_error('Pesanan tidak ditemukan', null, 404);

        $body = json_body();
        if (empty($body['status'])) json_error('status wajib diisi', null, 422);
        if (!in_array($body['status'], $validStatuses)) {
            json_error('status hanya boleh: ' . implode(', ', $validStatuses), null, 422);
        }
        $deliveryNote = isset($body['delivery_note']) ? trim((string) $body['delivery_note']) : null;

        $stmt = $pdo->prepare('UPDATE orders SET status = ?, delivery_note = ? WHERE id = ?');
        $stmt->execute([$body['status'], $deliveryNote, $id]);

        $updated = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $updated->execute([$id]);
        $result = $updated->fetch();
        $result['total_amount'] = (int) $result['total_amount'];

        json_success('Status pesanan berhasil diperbarui', $result);
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
