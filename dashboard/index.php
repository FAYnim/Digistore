<?php
$pageTitle = 'Overview';
$activePage = 'overview';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-6" data-page="overview">
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" id="statsGrid"></div>
  <div class="grid gap-6 xl:grid-cols-2">
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black">Pendapatan 7 Hari</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Pesanan dibayar dan selesai</p>
        </div>
      </div>
      <div class="relative h-72">
        <canvas id="incomeChart"></canvas>
        <p id="incomeChartEmpty" class="hidden absolute inset-0 grid place-items-center text-sm font-bold text-slate-400">Belum ada pendapatan</p>
      </div>
    </div>
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-black">Pesanan per Status</h2>
          <p class="text-sm text-slate-500 dark:text-slate-400">Distribusi semua pesanan</p>
        </div>
      </div>
      <div class="relative h-72">
        <canvas id="orderStatusChart"></canvas>
        <p id="orderStatusChartEmpty" class="hidden absolute inset-0 grid place-items-center text-sm font-bold text-slate-400">Belum ada pesanan</p>
      </div>
    </div>
  </div>
  <div class="grid gap-6">
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-black">Pesanan Terbaru</h2>
        <a class="text-sm font-bold text-blue-600 dark:text-blue-300" href="orders">Lihat semua</a>
      </div>
      <div class="table-wrap"><table><thead><tr><th>Kode</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status</th></tr></thead><tbody id="recentOrders"></tbody></table></div>
    </div>
    <div class="card min-w-0 p-5">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-black">Produk Populer</h2>
        <a class="text-sm font-bold text-blue-600 dark:text-blue-300" href="products">Kelola</a>
      </div>
      <div class="space-y-3" id="popularProducts"></div>
    </div>
  </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php renderFooter(); ?>
