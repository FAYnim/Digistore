<?php
/**
 * Standalone assert harness for api/recent-purchases.php
 * Run: php tests/api/recent-purchases.test.php
 * Requires: DB seeded (database/seed.sql) — needs 3 completed orders.
 */

$failures = 0;
$assertions = 0;

function check(string $label, bool $cond): void {
    global $failures, $assertions;
    $assertions++;
    if ($cond) {
        fwrite(STDOUT, "  ok  $label\n");
    } else {
        $failures++;
        fwrite(STDOUT, "  FAIL $label\n");
    }
}

function mask_first(string $name): string {
    // Mirror of endpoint helper
    $name = trim($name);
    if ($name === '') return '';
    $first = mb_substr($name, 0, 1, 'UTF-8');
    return $first . '***';
}

fwrite(STDOUT, "Mask helper:\n");
check('Rudi Hartono -> R***', mask_first('Rudi Hartono') === 'R***');
check('Lina -> L***',         mask_first('Lina') === 'L***');
check('empty -> empty',       mask_first('') === '');
check('  sp  -> empty',       mask_first('   ') === '');

// Live endpoint test (only when DB available)
$baseUrl = getenv('TEST_BASE_URL');
if ($baseUrl) {
    fwrite(STDOUT, "Endpoint $baseUrl/api/recent-purchases:\n");
    $body = @file_get_contents($baseUrl . '/api/recent-purchases?limit=10');
    check('response not empty', $body !== false && $body !== '');
    $json = json_decode((string)$body, true);
    check('json valid',          $json !== null);
    check('success=true',        ($json['success'] ?? null) === true);
    check('data is array',       is_array($json['data'] ?? null));
    $first = $json['data'][0] ?? null;
    if ($first) {
        check('has customer_name',  isset($first['customer_name']));
        check('masked format',      preg_match('/^.{1,2}\*\*\*$/', $first['customer_name']) === 1);
        check('has product_name',   isset($first['product_name']));
        check('has created_at',     isset($first['created_at']));
    }
    $body2 = @file_get_contents($baseUrl . '/api/recent-purchases?limit=1');
    $json2 = json_decode((string)$body2, true);
    check('limit=1 returns <=1',  count($json2['data'] ?? []) <= 1);
}

fwrite(STDOUT, "\n$assertions assertions, $failures failures\n");
exit($failures === 0 ? 0 : 1);
