<?php
$pageTitle = 'Overview';
$activePage = 'overview';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-6" data-page="overview">
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" id="statsGrid"></div>
  <div class="grid gap-6">
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-black">Pesanan Terbaru</h2>
        <a class="text-sm font-bold text-blue-600 dark:text-blue-300" href="orders.php">Lihat semua</a>
      </div>
      <div class="table-wrap"><table><thead><tr><th>Kode</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status</th></tr></thead><tbody id="recentOrders"></tbody></table></div>
    </div>
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-black">Produk Populer</h2>
        <a class="text-sm font-bold text-blue-600 dark:text-blue-300" href="products.php">Kelola</a>
      </div>
      <div class="space-y-3" id="popularProducts"></div>
    </div>
  </div>
</section>
<?php renderFooter(); ?>
