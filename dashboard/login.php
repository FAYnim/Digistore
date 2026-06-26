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
