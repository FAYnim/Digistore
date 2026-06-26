<?php

require_once __DIR__ . '/session.php';

start_admin_session();

$isLoggedIn = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if ($isLoggedIn) {
    return;
}

$isApiRequest = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/dashboard/api/') !== false;

if ($isApiRequest) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'data' => null,
        'errors' => null,
    ]);
    exit;
}

header('Location: login.php');
exit;
