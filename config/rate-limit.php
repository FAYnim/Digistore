<?php

function rate_limit_identifier(): string
{
    $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    $ip = trim(explode(',', $forwardedFor)[0]);

    if ($ip === '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    return preg_replace('/[^a-zA-Z0-9:.\-_]/', '_', $ip);
}

function rate_limit_exceeded(string $key, int $maxAttempts, int $windowSeconds): bool
{
    if ($maxAttempts <= 0 || $windowSeconds <= 0) {
        return false;
    }

    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'digital-store-rate-limit';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }

    $file = $dir . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    $now = time();
    $windowStart = $now - $windowSeconds;
    $hits = [];

    if (is_file($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded)) {
            $hits = array_filter($decoded, static fn ($hit) => is_int($hit) && $hit > $windowStart);
        }
    }

    if (count($hits) >= $maxAttempts) {
        return true;
    }

    $hits[] = $now;
    file_put_contents($file, json_encode(array_values($hits)), LOCK_EX);

    return false;
}

function rate_limit(string $key, int $maxAttempts, int $windowSeconds): void
{
    if (rate_limit_exceeded($key, $maxAttempts, $windowSeconds)) {
        json_error('Terlalu banyak percobaan. Silakan coba lagi nanti.', null, 429);
    }
}
