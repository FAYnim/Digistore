<?php
/**
 * API: Testimonials
 * GET    /dashboard/api/testimonials.php          — list semua testimoni
 * GET    /dashboard/api/testimonials.php?id=N     — detail satu testimoni
 * POST   /dashboard/api/testimonials.php          — tambah testimoni
 * PUT    /dashboard/api/testimonials.php?id=N     — edit testimoni
 * DELETE /dashboard/api/testimonials.php?id=N     — hapus testimoni
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/csrf.php';

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    csrf_validate_request();
}

switch ($method) {

    // ----------------------------------------------------------------
    // GET
    // ----------------------------------------------------------------
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) json_error('Testimoni tidak ditemukan', null, 404);
            $row['rating'] = (int) $row['rating'];
            json_success('Testimoni berhasil dimuat', $row);
        }

        $filter = '';
        $params = [];
        if (!empty($_GET['status']) && in_array($_GET['status'], ['visible', 'hidden'])) {
            $filter   = 'WHERE status = ?';
            $params[] = $_GET['status'];
        }

        $stmt = $pdo->prepare("SELECT * FROM testimonials $filter ORDER BY created_at DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['rating'] = (int) $row['rating'];
        }
        unset($row);

        json_success('Testimoni berhasil dimuat', $rows);
        break;

    // ----------------------------------------------------------------
    // POST — tambah
    // ----------------------------------------------------------------
    case 'POST':
        $body   = json_body();
        $errors = [];
        if (empty($body['name']))    $errors[] = 'name wajib diisi';
        if (empty($body['message'])) $errors[] = 'message wajib diisi';
        if (isset($body['rating'])) {
            $r = (int) $body['rating'];
            if ($r < 1 || $r > 5) $errors[] = 'rating harus angka 1 sampai 5';
        }
        if (isset($body['status']) && !in_array($body['status'], ['visible', 'hidden'])) {
            $errors[] = 'status hanya boleh: visible, hidden';
        }
        if ($errors) json_error('Validasi gagal', $errors, 422);

        $stmt = $pdo->prepare(
            'INSERT INTO testimonials (name, role, message, rating, status) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($body['name']),
            $body['role']    ?? null,
            trim($body['message']),
            isset($body['rating']) ? max(1, min(5, (int) $body['rating'])) : 5,
            $body['status']  ?? 'visible',
        ]);

        $newId = (int) $pdo->lastInsertId();
        $stmt2 = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
        $stmt2->execute([$newId]);
        $created = $stmt2->fetch();
        $created['rating'] = (int) $created['rating'];
        json_success('Testimoni berhasil ditambahkan', $created, 201);
        break;

    // ----------------------------------------------------------------
    // PUT — edit
    // ----------------------------------------------------------------
    case 'PUT':
        if (!$id) json_error('ID testimoni diperlukan', null, 400);

        $old = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
        $old->execute([$id]);
        $current = $old->fetch();
        if (!$current) json_error('Testimoni tidak ditemukan', null, 404);

        $body   = json_body();
        $errors = [];
        if (isset($body['name'])    && empty($body['name']))    $errors[] = 'name tidak boleh kosong';
        if (isset($body['message']) && empty($body['message'])) $errors[] = 'message tidak boleh kosong';
        if (isset($body['rating'])) {
            $r = (int) $body['rating'];
            if ($r < 1 || $r > 5) $errors[] = 'rating harus angka 1 sampai 5';
        }
        if (isset($body['status']) && !in_array($body['status'], ['visible', 'hidden'])) {
            $errors[] = 'status hanya boleh: visible, hidden';
        }
        if ($errors) json_error('Validasi gagal', $errors, 422);

        $stmt = $pdo->prepare(
            'UPDATE testimonials SET name=?, role=?, message=?, rating=?, status=? WHERE id=?'
        );
        $stmt->execute([
            isset($body['name'])    ? trim($body['name'])    : $current['name'],
            array_key_exists('role', $body)    ? $body['role']    : $current['role'],
            isset($body['message']) ? trim($body['message']) : $current['message'],
            isset($body['rating'])  ? max(1, min(5, (int)$body['rating'])) : (int)$current['rating'],
            $body['status'] ?? $current['status'],
            $id,
        ]);

        $updated = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
        $updated->execute([$id]);
        $result = $updated->fetch();
        $result['rating'] = (int) $result['rating'];
        json_success('Testimoni berhasil diperbarui', $result);
        break;

    // ----------------------------------------------------------------
    // DELETE
    // ----------------------------------------------------------------
    case 'DELETE':
        if (!$id) json_error('ID testimoni diperlukan', null, 400);

        $chk = $pdo->prepare('SELECT id FROM testimonials WHERE id = ?');
        $chk->execute([$id]);
        if (!$chk->fetch()) json_error('Testimoni tidak ditemukan', null, 404);

        $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = ?');
        $stmt->execute([$id]);
        json_success('Testimoni berhasil dihapus');
        break;

    default:
        json_error('Method tidak diizinkan', null, 405);
}
