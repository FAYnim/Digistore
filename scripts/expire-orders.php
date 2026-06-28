<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/order-expiration.php';

try {
    $result = expire_pending_orders($pdo);

    fwrite(STDOUT, sprintf(
        "Expired orders: %d\nReleased accounts: %d\n",
        $result['expired_orders'],
        $result['released_accounts']
    ));

    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Failed to expire orders: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
