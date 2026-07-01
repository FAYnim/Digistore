<?php
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

function getInitials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($parts as $p) {
        if ($p !== '') $initials .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return mb_substr($initials, 0, 2) ?: '?';
}

fwrite(STDOUT, "Helper logic:\n");
check('Raka Pratama -> RP',  getInitials('Raka Pratama') === 'RP');
check('Budi -> B',           getInitials('Budi') === 'B');
check('empty -> ?',           getInitials('') === '?');
check('single -> S',          getInitials('sari') === 'S');

$baseUrl = getenv('TEST_BASE_URL');
if ($baseUrl) {
    fwrite(STDOUT, "\nEndpoint $baseUrl/api/testimonials:\n");
    $body = @file_get_contents($baseUrl . '/api/testimonials?limit=3');
    check('response not empty', $body !== false && $body !== '');
    $json = json_decode((string)$body, true);
    check('json valid',              $json !== null);
    check('success=true',            ($json['success'] ?? null) === true);
    check('data is array',           is_array($json['data'] ?? null));
    check('max 3 items',             count($json['data']) <= 3);

    fwrite(STDOUT, "\nEndpoint $baseUrl/api/testimonials-all:\n");
    $body2 = @file_get_contents($baseUrl . '/api/testimonials-all?limit=2&offset=0');
    check('response not empty', $body2 !== false && $body2 !== '');
    $json2 = json_decode((string)$body2, true);
    check('json valid',               $json2 !== null);
    check('success=true',             ($json2['success'] ?? null) === true);
    check('data.data is array',       is_array($json2['data']['data'] ?? null));
    check('has_more is bool',         is_bool($json2['data']['has_more'] ?? null));
    check('total is int',             is_int($json2['data']['total'] ?? null));
    $first = $json2['data']['data'][0] ?? null;
    if ($first) {
        check('has image_path key', array_key_exists('image_path', $first));
        check('has name',          isset($first['name']));
        check('has message',       isset($first['message']));
        check('has rating',        isset($first['rating']));
    }
}

fwrite(STDOUT, "\n$assertions assertions, $failures failures\n");
exit($failures === 0 ? 0 : 1);
