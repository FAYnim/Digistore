<?php
$pageTitle = 'Pesanan';
$activePage = 'orders';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="orders">
  <div><h2 class="text-2xl font-black">Pesanan</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola dan pantau status pesanan.</p></div>
  <div class="card p-4">
    <div class="grid gap-3 md:grid-cols-[1fr_200px]">
      <input class="input" id="orderSearch" placeholder="Cari kode order atau nama customer" type="search">
      <select class="select" id="orderStatusFilter">
        <option value="">Semua status</option>
        <option value="pending">Menunggu</option>
        <option value="paid">Dibayar</option>
        <option value="completed">Selesai</option>
        <option value="cancelled">Dibatalkan</option>
      </select>
    </div>
  </div>
  <div class="card table-wrap"><table><thead><tr><th>Kode</th><th>Customer</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody id="ordersTable"></tbody></table></div>
</section>

<!-- Modal Detail Order -->
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="orderModal">
  <div class="card w-full max-w-xl p-5">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-black">Detail Order</h3>
      <button class="btn-soft" data-close-modal type="button">Tutup</button>
    </div>
    <div class="grid gap-3 text-sm" id="orderDetail"></div>
    <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-800">
      <label class="font-bold">Ubah Status
        <select class="select mt-1" id="orderStatusSelect">
          <option value="pending">Menunggu</option>
          <option value="paid">Dibayar</option>
          <option value="completed">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </label>
      <div class="mt-3 flex justify-end gap-2">
        <button class="btn-soft" data-close-modal type="button">Tutup</button>
        <button class="btn-primary" id="saveOrderStatus" type="button">Simpan Status</button>
      </div>
    </div>
  </div>
</div>
<?php renderFooter(); ?>
