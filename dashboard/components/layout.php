<?php
function renderHeader($pageTitle, $activePage) {
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?> — DigiStore Dashboard</title>
  <script>
    tailwind = { config: { darkMode: 'class' } };
    if (localStorage.getItem('digistore-dashboard-theme') === 'dark' || (!localStorage.getItem('digistore-dashboard-theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
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
</body>
</html>
<?php
}
?>
