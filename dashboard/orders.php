<?php
$pageTitle = 'Pesanan';
$activePage = 'orders';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="orders">
  <div><h2 class="text-2xl font-black">Pesanan</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola dan pantau status pesanan.</p></div>
  <div class="card p-4">
    <div class="flex flex-wrap gap-1 border-b border-slate-200 dark:border-slate-800 -mx-4 px-4" id="orderTabs">
      <button class="tab-btn active" data-tab="pending_payment"><span class="tab-label">Menunggu Pembayaran</span> <span class="tab-count" id="tabCount-pending_payment">0</span></button>
      <button class="tab-btn" data-tab="pending_verify"><span class="tab-label">Perlu Verifikasi</span> <span class="tab-count" id="tabCount-pending_verify">0</span></button>
      <button class="tab-btn" data-tab="completed"><span class="tab-label">Selesai</span> <span class="tab-count" id="tabCount-completed">0</span></button>
      <button class="tab-btn" data-tab="expired"><span class="tab-label">Expired</span> <span class="tab-count" id="tabCount-expired">0</span></button>
      <button class="tab-btn" data-tab="cancelled"><span class="tab-label">Batal</span> <span class="tab-count" id="tabCount-cancelled">0</span></button>
      <button class="tab-btn" data-tab="all"><span class="tab-label">Semua</span> <span class="tab-count" id="tabCount-all">0</span></button>
    </div>
  </div>
  <div class="card p-3">
    <input class="input w-full sm:max-w-xs" id="orderSearch" placeholder="Cari kode order, nama, email, atau nomor HP" type="search">
  </div>
  <div class="rounded-2xl border border-blue-100 bg-blue-50/80 p-4 text-sm text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-100">
    <p class="font-bold">Tips kerja</p>
    <p class="mt-1">Order menunggu pembayaran berpindah ke <span class="font-semibold">Perlu Verifikasi</span> setelah buyer upload bukti bayar. Jika diterima, credentials otomatis tampil dan order menjadi selesai.</p>
  </div>
  <div class="card overflow-hidden">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 text-sm font-bold text-slate-500 dark:border-slate-800 dark:text-slate-400">
      <span>Daftar pesanan</span>
      <span class="text-xs font-semibold md:hidden">Geser tabel →</span>
    </div>
    <div class="table-wrap orders-table-wrap">
      <table class="orders-table">
        <thead>
          <tr><th>Kode</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status</th><th>Verifikasi</th><th>Tanggal</th><th>Aksi</th></tr>
        </thead>
        <tbody id="ordersTable"></tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal Detail Order -->
<div class="modal fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 p-4" id="orderModal">
  <div class="card mx-auto my-6 flex max-h-[calc(100vh-3rem)] w-full max-w-4xl flex-col overflow-hidden">
    <div class="flex shrink-0 items-center justify-between border-b border-slate-200 p-5 dark:border-slate-800">
      <h3 class="text-xl font-black">Detail Order</h3>
      <button class="btn-soft" data-close-modal type="button">Tutup</button>
    </div>
    <div class="overflow-y-auto p-5">
    <div class="grid gap-3 text-sm md:grid-cols-2" id="orderDetail"></div>
    <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-800 hidden" id="deliveryNoteSection">
      <input id="orderId" type="hidden">
      <label class="block font-bold">Credentials
        <textarea class="input mt-1 min-h-28" id="orderDeliveryNote" placeholder="Credentials akan tampil setelah pembayaran diterima." readonly></textarea>
      </label>
      <div class="mt-3 flex justify-end gap-2">
        <button class="btn-soft" data-close-modal type="button">Tutup</button>
      </div>
    </div>
    </div>
  </div>
</div>
<?php renderFooter(); ?>
