<?php
$navItems = [
    ['label' => 'Overview', 'href' => 'index.php', 'key' => 'overview', 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Produk', 'href' => 'products.php', 'key' => 'products', 'icon' => 'fa-solid fa-box'],
    ['label' => 'Pesanan', 'href' => 'orders.php', 'key' => 'orders', 'icon' => 'fa-solid fa-receipt'],
    ['label' => 'Pembayaran', 'href' => 'settings-payment.php', 'key' => 'payment-settings', 'icon' => 'fa-solid fa-qrcode'],
    // ['label' => 'Testimoni', 'href' => 'testimonials.php', 'key' => 'testimonials', 'icon' => 'fa-solid fa-comments'], // disembunyikan sementara
    ['label' => 'Setting', 'href' => 'settings.php', 'key' => 'settings', 'icon' => 'fa-solid fa-gear'],
];
?>
<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-slate-200 bg-white transition dark:border-slate-800 dark:bg-slate-950 lg:translate-x-0">
  <div class="flex h-full flex-col">
    <div class="flex h-14 items-center border-b border-slate-200 px-4 dark:border-slate-800">
      <div>
        <p class="text-sm font-black tracking-tight text-slate-950 dark:text-white">DigiStore</p>
      </div>
    </div>
    <nav class="flex-1 space-y-1 p-3">
      <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-link <?= $activePage === $item['key'] ? 'active' : '' ?>">
          <i class="<?= $item['icon'] ?> w-4"></i>
          <span><?= $item['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="border-t border-slate-200 p-4 dark:border-slate-800">
      <a href="../index.html" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-500 hover:text-blue-600 dark:border-slate-800 dark:text-slate-200 dark:hover:border-blue-400 dark:hover:text-blue-300"><i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog</a>
    </div>
  </div>
</aside>
<div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 lg:hidden"></div>
