<?php
/**
 * API: Orders
 * GET    /dashboard/api/orders.php              — list pesanan (+ filter: status, search)
 * GET    /dashboard/api/orders.php?id=N         — detail pesanan + order items
 * PUT    /dashboard/api/orders.php?id=N         — update status pesanan
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

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
            $conditions[] = 'status = ?';
            $params[]     = $_GET['status'];
        }
        if (!empty($_GET['search'])) {
            $conditions[] = '(order_code LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)';
            $keyword      = '%' . $_GET['search'] . '%';
            $params[]     = $keyword;
            $params[]     = $keyword;
            $params[]     = $keyword;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt  = $pdo->prepare("SELECT * FROM orders $where ORDER BY created_at DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['total_amount'] = (int) $row['total_amount'];
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

        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$body['status'], $id]);

        $updated = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $updated->execute([$id]);
        $result = $updated->fetch();
        $result['total_amount'] = (int) $result['total_amount'];

        json_success('Status pesanan berhasil diperbarui', $result);
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
