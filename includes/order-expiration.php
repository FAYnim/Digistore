<?php

function expire_pending_orders(PDO $pdo, ?int $orderId = null): array
{
    $ownTransaction = !$pdo->inTransaction();
    $expiredOrders = [];
    $releasedAccounts = 0;

    try {
        if ($ownTransaction) $pdo->beginTransaction();

        $sql = 'SELECT id, order_code FROM orders WHERE status IN ("pending", "pending_payment") AND payment_deadline IS NOT NULL AND payment_deadline < NOW()';
        $params = [];

        if ($orderId !== null) {
            $sql .= ' AND id = ?';
            $params[] = $orderId;
        }

        $sql .= ' ORDER BY id ASC FOR UPDATE';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        if ($orders) {
            $ids = array_map(static fn(array $order): int => (int) $order['id'], $orders);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $updateOrders = $pdo->prepare("UPDATE orders SET status = 'expired' WHERE id IN ($placeholders) AND status IN ('pending', 'pending_payment')");
            $updateOrders->execute($ids);

            $releaseAccounts = $pdo->prepare("UPDATE product_accounts SET status = 'available', order_id = NULL WHERE order_id IN ($placeholders) AND status = 'reserved'");
            $releaseAccounts->execute($ids);
            $releasedAccounts = $releaseAccounts->rowCount();

            foreach ($orders as $order) {
                $expiredOrders[] = [
                    'id' => (int) $order['id'],
                    'order_code' => $order['order_code'],
                ];
            }
        }

        if ($ownTransaction) $pdo->commit();

        return [
            'expired_orders' => count($expiredOrders),
            'released_accounts' => $releasedAccounts,
            'orders' => $expiredOrders,
        ];
    } catch (Throwable $e) {
        if ($ownTransaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
