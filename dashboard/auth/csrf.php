<?php

require_once __DIR__ . '/session.php';

function csrf_token()
{
    start_admin_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_validate($token)
{
    start_admin_session();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_validate_request(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

    if (!csrf_validate($token)) {
        json_error('Session tidak valid. Silakan muat ulang halaman.', null, 419);
    }
}
