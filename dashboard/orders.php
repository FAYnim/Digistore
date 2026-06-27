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
      <input class="input" id="orderSearch" placeholder="Cari kode order, nama, email, atau nomor HP" type="search">
      <select class="select" id="orderStatusFilter">
        <option value="">Semua status</option>
        <option value="pending_payment">Menunggu Pembayaran</option>
        <option value="paid">Pembayaran Diterima</option>
        <option value="processing">Diproses</option>
        <option value="delivered">Dikirim</option>
        <option value="completed">Selesai</option>
        <option value="expired">Expired</option>
        <option value="cancelled">Dibatalkan</option>
      </select>
    </div>
  </div>
  <div class="card table-wrap"><table><thead><tr><th>Kode</th><th>Customer</th><th>Produk</th><th>Total</th><th>Status</th><th>Verifikasi</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody id="ordersTable"></tbody></table></div>
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
      <label class="block font-bold">Delivery Note
        <textarea class="input mt-1 min-h-28" id="orderDeliveryNote" placeholder="Link, akun, atau catatan pengiriman."></textarea>
      </label>
      <div class="mt-3 flex justify-end gap-2">
        <button class="btn-soft" data-close-modal type="button">Tutup</button>
        <button class="btn-soft hidden" id="completeOrder" type="button">Tandai Selesai</button>
        <button class="btn-primary" id="saveOrderStatus" type="button">Simpan Delivery Note</button>
      </div>
    </div>
    </div>
  </div>
</div>
<?php renderFooter(); ?>
