<?php
/**
 * API: Orders
 * GET    /dashboard/api/orders.php              — list pesanan (+ filter: status, search)
 * GET    /dashboard/api/orders.php?id=N         — detail pesanan + order items
 * PUT    /dashboard/api/orders.php?id=N         — update delivery note pesanan
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../../includes/order-expiration.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

$validStatuses = ['pending_payment', 'paid', 'processing', 'delivered', 'completed', 'expired', 'cancelled'];
$legacyStatusMap = ['pending' => 'pending_payment'];

function normalize_order_status(array &$order): void
{
    if (($order['status'] ?? '') === 'pending') $order['status'] = 'pending_payment';
}

switch ($method) {

    // ----------------------------------------------------------------
    // GET — list atau detail
    // ----------------------------------------------------------------
    case 'GET':
        expire_pending_orders($pdo, $id ?: null);

        if ($id) {
            // Detail pesanan + items
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order) json_error('Pesanan tidak ditemukan', null, 404);
            normalize_order_status($order);

            // Ambil order items
            $items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $items->execute([$id]);
            $order['items']        = $items->fetchAll();
            $order['total_amount'] = (int) $order['total_amount'];

            $confirmations = $pdo->prepare('SELECT id, sender_name, payment_method, note, proof_path, verification_status, admin_note, verified_by, verified_at, created_at FROM payment_confirmations WHERE order_id = ? ORDER BY created_at DESC');
            $confirmations->execute([$id]);
            $order['payment_confirmations'] = $confirmations->fetchAll();

            json_success('Detail pesanan berhasil dimuat', $order);
        }

        // List pesanan dengan filter
        $conditions = [];
        $params     = [];

        if (!empty($_GET['status'])) {
            $filterStatus = $legacyStatusMap[$_GET['status']] ?? $_GET['status'];
            if (in_array($filterStatus, $validStatuses, true)) {
                $conditions[] = $filterStatus === 'pending_payment' ? 'o.status IN (?, ?)' : 'o.status = ?';
                $params[] = $filterStatus === 'pending_payment' ? 'pending_payment' : $filterStatus;
                if ($filterStatus === 'pending_payment') $params[] = 'pending';
            }
        }
        if (!empty($_GET['has_pending_confirmation'])) {
            $conditions[] = 'pc.pending_confirmations > 0';
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
        $stmt  = $pdo->prepare("SELECT o.*, COALESCE(oi.items_count, 0) AS items_count, oi.items_summary, COALESCE(pc.pending_confirmations, 0) AS pending_confirmations FROM orders o LEFT JOIN (SELECT order_id, COUNT(id) AS items_count, GROUP_CONCAT(product_name ORDER BY id SEPARATOR ', ') AS items_summary FROM order_items GROUP BY order_id) oi ON oi.order_id = o.id LEFT JOIN (SELECT order_id, COUNT(id) AS pending_confirmations FROM payment_confirmations WHERE verification_status = 'pending' GROUP BY order_id) pc ON pc.order_id = o.id $where ORDER BY o.created_at DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            normalize_order_status($row);
            $row['total_amount'] = (int) $row['total_amount'];
            $row['items_count'] = (int) $row['items_count'];
            $row['pending_confirmations'] = (int) $row['pending_confirmations'];
            $row['items_summary'] = $row['items_count'] > 1 ? $row['items_count'] . ' produk' : ($row['items_summary'] ?: '—');
        }
        unset($row);

        json_success('Pesanan berhasil dimuat', $rows);
        break;

    // ----------------------------------------------------------------
    // POST — verifikasi pembayaran
    // ----------------------------------------------------------------
    case 'POST':
        if (($_GET['action'] ?? '') === 'expire_orders') {
            json_success('Expired order berhasil diproses', expire_pending_orders($pdo));
        }

        if (($_GET['action'] ?? '') === 'complete_order') {
            $body = json_body();
            $orderId = (int) ($body['order_id'] ?? 0);
            if ($orderId <= 0) json_error('order_id wajib diisi', null, 422);

            $stmt = $pdo->prepare('SELECT id, status, delivery_note FROM orders WHERE id = ? LIMIT 1');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if (!$order) json_error('Pesanan tidak ditemukan', null, 404);
            if ($order['status'] !== 'delivered' || trim((string) ($order['delivery_note'] ?? '')) === '') json_error('Pesanan hanya bisa selesai setelah delivery note dikirim', null, 422);

            $update = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $update->execute(['completed', $orderId]);
            json_success('Pesanan berhasil ditandai selesai', ['order_id' => $orderId, 'status' => 'completed']);
        }

        if (($_GET['action'] ?? '') !== 'verify_payment') json_error('Aksi tidak valid', null, 400);

        $body = json_body();
        $confirmationId = (int) ($body['confirmation_id'] ?? 0);
        $action = trim((string) ($body['action'] ?? ''));
        $adminNote = trim((string) ($body['admin_note'] ?? ''));
        $validActions = ['accept', 'reject'];

        if ($confirmationId <= 0) json_error('confirmation_id wajib diisi', null, 422);
        if (!in_array($action, $validActions, true)) json_error('action tidak valid', null, 422);
        if ($action === 'reject' && $adminNote === '') json_error('Catatan admin wajib diisi', null, 422);

        $stmt = $pdo->prepare('SELECT pc.id, pc.order_id, pc.verification_status, o.status FROM payment_confirmations pc JOIN orders o ON o.id = pc.order_id WHERE pc.id = ? LIMIT 1');
        $stmt->execute([$confirmationId]);
        $confirmation = $stmt->fetch();
        if (!$confirmation) json_error('Konfirmasi pembayaran tidak ditemukan', null, 404);

        expire_pending_orders($pdo, (int) $confirmation['order_id']);

        $stmt->execute([$confirmationId]);
        $confirmation = $stmt->fetch();

        if (!$confirmation) json_error('Konfirmasi pembayaran tidak ditemukan', null, 404);
        if ($confirmation['verification_status'] !== 'pending') json_error('Konfirmasi ini sudah diverifikasi', null, 422);
        if ($confirmation['status'] === 'expired') json_error('Pesanan sudah expired', null, 422);
        if (!in_array($confirmation['status'], ['pending', 'pending_payment'], true)) json_error('Pesanan tidak menunggu pembayaran', null, 422);

        $verificationStatus = $action === 'accept' ? 'accepted' : 'rejected';
        $orderStatus = $action === 'accept' ? 'delivered' : 'pending_payment';

        $pdo->beginTransaction();

        $reservedAccount = null;
        if ($action === 'accept') {
            $accountStmt = $pdo->prepare('SELECT id, account_data FROM product_accounts WHERE order_id = ? AND status = "reserved" LIMIT 1 FOR UPDATE');
            $accountStmt->execute([(int) $confirmation['order_id']]);
            $reservedAccount = $accountStmt->fetch();

            if (!$reservedAccount) {
                $pdo->rollBack();
                json_error('Tidak ada akun yang direservasi untuk order ini', null, 422);
            }
        }

        $updateConfirmation = $pdo->prepare('UPDATE payment_confirmations SET verification_status = ?, admin_note = ?, verified_by = ?, verified_at = NOW() WHERE id = ?');
        $updateConfirmation->execute([$verificationStatus, $adminNote !== '' ? $adminNote : null, $_SESSION['admin_id'] ?? null, $confirmationId]);

        if ($action === 'accept') {
            $soldStmt = $pdo->prepare('UPDATE product_accounts SET status = "sold", sold_at = NOW() WHERE id = ? AND status = "reserved"');
            $soldStmt->execute([$reservedAccount['id']]);

            if ($soldStmt->rowCount() !== 1) {
                $pdo->rollBack();
                json_error('Gagal mengirim akun premium', null, 422);
            }

            $updateOrder = $pdo->prepare('UPDATE orders SET status = "delivered", delivery_note = ? WHERE id = ?');
            $updateOrder->execute([$reservedAccount['account_data'], (int) $confirmation['order_id']]);
        } else {
            $updateOrder = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $updateOrder->execute([$orderStatus, (int) $confirmation['order_id']]);
        }

        $pdo->commit();

        json_success('Verifikasi pembayaran berhasil diproses', ['order_id' => (int) $confirmation['order_id'], 'status' => $orderStatus]);
        break;

    // ----------------------------------------------------------------
    // PUT — update delivery note pesanan
    // ----------------------------------------------------------------
    case 'PUT':
        if (!$id) json_error('ID pesanan diperlukan', null, 400);

        $chk = $pdo->prepare('SELECT id, status FROM orders WHERE id = ?');
        $chk->execute([$id]);
        $order = $chk->fetch();
        if (!$order) json_error('Pesanan tidak ditemukan', null, 404);
        if (!in_array($order['status'], ['paid', 'processing', 'delivered', 'completed'], true)) json_error('Delivery note hanya bisa diisi setelah pembayaran diterima', null, 422);

        $body = json_body();
        $deliveryNote = isset($body['delivery_note']) ? trim((string) $body['delivery_note']) : null;

        $nextStatus = $deliveryNote !== '' ? 'delivered' : 'paid';
        $stmt = $pdo->prepare('UPDATE orders SET delivery_note = ?, status = ? WHERE id = ?');
        $stmt->execute([$deliveryNote !== '' ? $deliveryNote : null, $nextStatus, $id]);

        $updated = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $updated->execute([$id]);
        $result = $updated->fetch();
        normalize_order_status($result);
        $result['total_amount'] = (int) $result['total_amount'];

        json_success('Delivery note berhasil diperbarui', $result);
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
