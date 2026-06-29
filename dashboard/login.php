<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/auth/csrf.php';

start_admin_session();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index');
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
  <title>Login — Digistore Dashboard</title>
  <script>
    tailwind = { config: { darkMode: 'class' } };
    if (localStorage.getItem('digistore-dashboard-theme') === 'dark' || (!localStorage.getItem('digistore-dashboard-theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="min-h-screen bg-[#f7f8fb] text-slate-950 antialiased dark:bg-slate-950 dark:text-white">
  <main class="grid min-h-screen place-items-center px-4 py-10">
    <section class="w-full max-w-[510px] rounded-2xl border border-slate-200/80 bg-white p-7 shadow-[0_18px_55px_rgba(15,23,42,.10)] dark:border-white/10 dark:bg-slate-900 sm:p-10">
      <div class="mb-8">
        <h1 class="text-2xl font-black tracking-[-.03em]">Login to Digistore</h1>
        <p class="mt-3 text-base text-slate-500 dark:text-slate-400">Enter your credentials to continue</p>
      </div>

      <?php if ($error): ?>
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form class="space-y-6" method="post" action="auth/login-process">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <label class="block">
          <span class="mb-3 block text-sm font-bold text-slate-800 dark:text-slate-200">Username</span>
          <span class="relative block">
            <i class="fa-regular fa-user absolute left-5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
            <input class="w-full rounded-xl border border-slate-200 bg-white py-4 pl-14 pr-5 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-slate-950 dark:text-white" type="text" name="username" autocomplete="username" placeholder="admin" autofocus required>
          </span>
        </label>
        <label class="block">
          <span class="mb-3 block text-sm font-bold text-slate-800 dark:text-slate-200">Password</span>
          <span class="relative block">
            <i class="fa-solid fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
            <input class="w-full rounded-xl border border-slate-200 bg-white py-4 pl-14 pr-12 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-slate-950 dark:text-white" type="password" name="password" autocomplete="current-password" placeholder="••••••••" required>
            <i class="fa-regular fa-eye absolute right-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
          </span>
        </label>
        <div class="text-right">
          <a class="text-sm font-bold text-blue-600 hover:text-blue-700 dark:text-blue-300" href="../index">Kembali ke katalog</a>
        </div>
        <button class="w-full rounded-xl bg-slate-950 px-4 py-4 text-sm font-black text-white shadow-[0_10px_25px_rgba(15,23,42,.16)] transition hover:-translate-y-0.5 hover:bg-blue-600 dark:bg-white dark:text-slate-950 dark:hover:bg-blue-200" type="submit">Login</button>
      </form>
    </section>
  </main>
</body>
</html>
