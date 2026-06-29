<?php
require_once __DIR__ . '/../auth/check-auth.php';
require_once __DIR__ . '/../auth/csrf.php';

function renderHeader($pageTitle, $activePage) {
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
  <title><?= $pageTitle ?> — DigiStore Dashboard</title>
  <script>
    tailwind = { config: { darkMode: 'class' } };
    if (localStorage.getItem('digistore-dashboard-theme') === 'dark' || (!localStorage.getItem('digistore-dashboard-theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="../assets/favicon/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon/favicon-16x16.png">
  <link rel="apple-touch-icon" href="../assets/favicon/apple-touch-icon.png">
  <link rel="manifest" href="../assets/favicon/site.webmanifest">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="bg-slate-50 text-slate-950 antialiased dark:bg-slate-950 dark:text-white">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="min-h-screen lg:pl-64">
    <?php include __DIR__ . '/topbar.php'; ?>
    <main class="p-4 sm:p-5 lg:p-6">
<?php
}

function renderFooter() {
?>
    </main>
  </div>
  <script src="assets/js/api.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <?php if (!empty($GLOBALS['pageScript'])): ?>
    <script src="<?= $GLOBALS['pageScript'] ?>"></script>
  <?php endif; ?>
</body>
</html>
<?php
}
?>
