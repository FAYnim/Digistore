<?php
/**
 * Response helper — JSON response konsisten untuk semua API endpoint
 */

function json_success(string $message, $data = null, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data,
        'errors'  => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, $errors = null, int $statusCode = 400): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data'    => null,
        'errors'  => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Baca body JSON dari request (POST/PUT)
 */
function json_body(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        json_error('Format JSON tidak valid', null, 400);
    }
    return $decoded ?? [];
}

/**
 * Validasi method HTTP
 */
function require_method(string ...$methods): void
{
    $current = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array(strtoupper($current), array_map('strtoupper', $methods), true)) {
        json_error('Method tidak diizinkan', null, 405);
    }
}
