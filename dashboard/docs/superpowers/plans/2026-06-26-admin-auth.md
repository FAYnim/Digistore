# Admin Auth Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build PHP session-based admin authentication for dashboard pages and dashboard API endpoints.

**Architecture:** Add focused auth helpers under `dashboard/auth/`, route login/logout through small PHP entrypoints, and require the guard from dashboard layout plus every API endpoint. Keep credentials in `admin_users` with hashed passwords and use a one-time manual seed script.

**Tech Stack:** PHP, PDO, MySQL, Tailwind CDN, vanilla JavaScript fetch helper.

---

## File Structure

- Create: `dashboard/auth/session.php` — secure session startup.
- Create: `dashboard/auth/csrf.php` — login CSRF token helpers.
- Create: `dashboard/auth/check-auth.php` — page/API auth guard.
- Create: `dashboard/auth/login-process.php` — credential validation and session creation.
- Create: `dashboard/login.php` — admin login page.
- Create: `dashboard/logout.php` — session destroy redirect.
- Create: `database/seed-admin.php` — one-time admin seed script.
- Modify: `dashboard/components/layout.php` — protect dashboard pages.
- Modify: `dashboard/components/topbar.php` — show admin identity and logout.
- Modify: `dashboard/assets/js/api.js` — redirect on 401.
- Modify: `dashboard/api/products.php` — protect endpoint.
- Modify: `dashboard/api/categories.php` — protect endpoint.
- Modify: `dashboard/api/orders.php` — protect endpoint.
- Modify: `dashboard/api/settings.php` — protect endpoint.
- Modify: `dashboard/api/stats.php` — protect endpoint.
- Modify: `dashboard/api/testimonials.php` — protect endpoint.

---

### Task 1: Session and CSRF Helpers

**Files:**
- Create: `dashboard/auth/session.php`
- Create: `dashboard/auth/csrf.php`

- [ ] **Step 1: Create secure session helper**

Create `dashboard/auth/session.php`:

```php
<?php

function start_admin_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}
```

- [ ] **Step 2: Create CSRF helper**

Create `dashboard/auth/csrf.php`:

```php
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
```

- [ ] **Step 3: Syntax check**

Run:

```bash
php -l dashboard/auth/session.php && php -l dashboard/auth/csrf.php
```

Expected: both files report `No syntax errors detected`.

---

### Task 2: Auth Guard

**Files:**
- Create: `dashboard/auth/check-auth.php`

- [ ] **Step 1: Create guard**

Create `dashboard/auth/check-auth.php`:

```php
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
```

- [ ] **Step 2: Syntax check**

Run:

```bash
php -l dashboard/auth/check-auth.php
```

Expected: `No syntax errors detected`.

---

### Task 3: Login Page and Login Processor

**Files:**
- Create: `dashboard/login.php`
- Create: `dashboard/auth/login-process.php`

- [ ] **Step 1: Create login page**

Create `dashboard/login.php`:

```php
<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/csrf.php';

start_admin_session();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
$token = csrf_token();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login — DigiStore Dashboard</title>
  <script>
    tailwind = { config: { darkMode: 'class' } };
    if (localStorage.getItem('digistore-dashboard-theme') === 'dark' || (!localStorage.getItem('digistore-dashboard-theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased dark:bg-slate-950 dark:text-white">
  <main class="grid min-h-screen place-items-center px-4 py-10">
    <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-6 text-center">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-blue-600 text-white">
          <i class="fa-solid fa-store"></i>
        </div>
        <h1 class="text-2xl font-black tracking-tight">Admin Login</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Masuk untuk mengelola katalog.</p>
      </div>

      <?php if ($error): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form class="space-y-4" method="post" action="auth/login-process.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <label class="block">
          <span class="mb-2 block text-sm font-bold">Username</span>
          <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500 dark:border-slate-800 dark:bg-slate-950" type="text" name="username" autocomplete="username" required>
        </label>
        <label class="block">
          <span class="mb-2 block text-sm font-bold">Password</span>
          <input class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500 dark:border-slate-800 dark:bg-slate-950" type="password" name="password" autocomplete="current-password" required>
        </label>
        <button class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700" type="submit">Login</button>
      </form>

      <a class="mt-5 block text-center text-sm font-bold text-blue-600 dark:text-blue-300" href="../index.php">Kembali ke katalog</a>
    </section>
  </main>
</body>
</html>
```

- [ ] **Step 2: Create login processor**

Create `dashboard/auth/login-process.php`:

```php
<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../config/database.php';

start_admin_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';

if (!csrf_validate($csrfToken)) {
    $_SESSION['login_error'] = 'Session tidak valid. Silakan coba lagi.';
    header('Location: ../login.php');
    exit;
}

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password, name, status FROM admin_users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    $_SESSION['login_error'] = 'Username atau password salah.';
    header('Location: ../login.php');
    exit;
}

if ($admin['status'] !== 'active') {
    $_SESSION['login_error'] = 'Akun tidak aktif.';
    header('Location: ../login.php');
    exit;
}

session_regenerate_id(true);

$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = (int) $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
$_SESSION['admin_name'] = $admin['name'];
unset($_SESSION['csrf_token'], $_SESSION['login_error']);

$update = $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?');
$update->execute([(int) $admin['id']]);

header('Location: ../index.php');
exit;
```

- [ ] **Step 3: Syntax check**

Run:

```bash
php -l dashboard/login.php && php -l dashboard/auth/login-process.php
```

Expected: both files report `No syntax errors detected`.

---

### Task 4: Logout and Dashboard Page Protection

**Files:**
- Create: `dashboard/logout.php`
- Modify: `dashboard/components/layout.php`
- Modify: `dashboard/components/topbar.php`

- [ ] **Step 1: Create logout route**

Create `dashboard/logout.php`:

```php
<?php

require_once __DIR__ . '/auth/session.php';

start_admin_session();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

header('Location: login.php');
exit;
```

- [ ] **Step 2: Protect layout**

Modify the top of `dashboard/components/layout.php` to:

```php
<?php
require_once __DIR__ . '/../auth/check-auth.php';

function renderHeader($pageTitle, $activePage) {
?>
```

- [ ] **Step 3: Update topbar admin area**

Replace the final Admin button in `dashboard/components/topbar.php`:

```php
    <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200" type="button">Admin</button>
```

with:

```php
    <div class="hidden items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900 sm:flex">
      <div class="text-right leading-tight">
        <div class="text-sm font-black text-slate-800 dark:text-slate-100"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="text-xs font-bold text-slate-400"><?= htmlspecialchars($_SESSION['admin_username'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <a class="text-sm font-black text-red-600 hover:text-red-700 dark:text-red-400" href="logout.php">Logout</a>
    </div>
```

- [ ] **Step 4: Syntax check**

Run:

```bash
php -l dashboard/logout.php && php -l dashboard/components/layout.php && php -l dashboard/components/topbar.php
```

Expected: all files report `No syntax errors detected`.

---

### Task 5: API Protection and 401 Redirect

**Files:**
- Modify: `dashboard/api/products.php`
- Modify: `dashboard/api/categories.php`
- Modify: `dashboard/api/orders.php`
- Modify: `dashboard/api/settings.php`
- Modify: `dashboard/api/stats.php`
- Modify: `dashboard/api/testimonials.php`
- Modify: `dashboard/assets/js/api.js`

- [ ] **Step 1: Add auth guard to each API file**

In every `dashboard/api/*.php` file, add this line before `require_once __DIR__ . '/../config/database.php';`:

```php
require_once __DIR__ . '/../auth/check-auth.php';
```

Example final top for `dashboard/api/products.php`:

```php
<?php
/**
 * API: Products
 * GET    /dashboard/api/products.php                        — list produk (+ filter: search, category_id, status)
 * GET    /dashboard/api/products.php?id=N                  — detail satu produk
 * POST   /dashboard/api/products.php                       — tambah produk
 * PUT    /dashboard/api/products.php?id=N                  — edit produk
 * DELETE /dashboard/api/products.php?id=N                  — hapus produk
 */

require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../config/database.php';
```

- [ ] **Step 2: Update fetch helper for 401**

In `dashboard/assets/js/api.js`, replace this block:

```js
    const json = await res.json();
    return json;
```

with:

```js
    if (res.status === 401) {
      window.location.href = 'login.php';
      return { success: false, message: 'Unauthorized', data: null, errors: null };
    }

    const json = await res.json();
    return json;
```

- [ ] **Step 3: Syntax check APIs**

Run:

```bash
php -l dashboard/api/products.php && php -l dashboard/api/categories.php && php -l dashboard/api/orders.php && php -l dashboard/api/settings.php && php -l dashboard/api/stats.php && php -l dashboard/api/testimonials.php
```

Expected: all files report `No syntax errors detected`.

---

### Task 6: Admin Seeder

**Files:**
- Create: `database/seed-admin.php`

- [ ] **Step 1: Create seed script**

Create `database/seed-admin.php`:

```php
<?php

require_once __DIR__ . '/../config/database.php';

$username = 'admin';
$password = 'admin123';
$name = 'Admin Store';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);

if ($stmt->fetch()) {
    echo "Admin already exists.\n";
    exit;
}

$insert = $pdo->prepare('INSERT INTO admin_users (username, password, name, status) VALUES (?, ?, ?, ?)');
$insert->execute([$username, $passwordHash, $name, 'active']);

echo "Admin created. Username: {$username} Password: {$password}\n";
echo "Delete database/seed-admin.php after use.\n";
```

- [ ] **Step 2: Syntax check**

Run:

```bash
php -l database/seed-admin.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Manual DB prerequisite**

Run this SQL in MySQL if `admin_users` does not exist:

```sql
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

- [ ] **Step 4: Run seeder manually**

Run only after DB table exists:

```bash
php database/seed-admin.php
```

Expected: `Admin created. Username: admin Password: admin123` or `Admin already exists.`

---

### Task 7: Final Verification

**Files:**
- Verify all changed PHP files.

- [ ] **Step 1: Syntax check all changed PHP files**

Run:

```bash
php -l dashboard/auth/session.php && php -l dashboard/auth/csrf.php && php -l dashboard/auth/check-auth.php && php -l dashboard/auth/login-process.php && php -l dashboard/login.php && php -l dashboard/logout.php && php -l dashboard/components/layout.php && php -l dashboard/components/topbar.php && php -l dashboard/api/products.php && php -l dashboard/api/categories.php && php -l dashboard/api/orders.php && php -l dashboard/api/settings.php && php -l dashboard/api/stats.php && php -l dashboard/api/testimonials.php && php -l database/seed-admin.php
```

Expected: every file reports `No syntax errors detected`.

- [ ] **Step 2: Manual browser checks**

Open:

```text
http://localhost/faydev/digital-store/dashboard/
```

Expected: redirect to login when unauthenticated.

Login with:

```text
username: admin
password: admin123
```

Expected: redirect to dashboard overview.

Open:

```text
http://localhost/faydev/digital-store/dashboard/logout.php
```

Expected: redirect to login and dashboard is protected again.

- [ ] **Step 3: Manual API check**

Without an active session, open:

```text
http://localhost/faydev/digital-store/dashboard/api/stats.php
```

Expected: HTTP 401 JSON with message `Unauthorized`.

---

## Self-Review

Spec coverage:
- Login/logout/session: Tasks 1, 3, 4.
- Page protection: Task 4.
- API protection: Task 5.
- Password hashing/verification: Tasks 3, 6.
- CSRF login: Tasks 1, 3.
- Session hardening: Task 1.
- Seeder: Task 6.
- JS 401 redirect: Task 5.

Placeholder scan: no TBD/TODO/later placeholders.

Type/name consistency: session keys match the design: `admin_logged_in`, `admin_id`, `admin_username`, `admin_name`.
