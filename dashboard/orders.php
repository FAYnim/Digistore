<?php
$pageTitle = 'Pesanan';
$activePage = 'orders';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="orders">
  <div><h2 class="text-2xl font-black">Pesanan</h2><p class="text-sm text-slate-500 dark:text-slate-400">Preview order dummy.</p></div>
  <div class="card table-wrap"><table><thead><tr><th>Kode</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody id="ordersTable"></tbody></table></div>
</section>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="orderModal"><div class="card w-full max-w-xl p-5"><div class="mb-4 flex items-center justify-between"><h3 class="text-xl font-black">Detail Order</h3><button class="btn-soft" data-close-modal type="button">Tutup</button></div><div class="grid gap-3 text-sm" id="orderDetail"></div></div></div>
<?php renderFooter(); ?>
