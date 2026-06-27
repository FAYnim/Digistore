<?php
$pageTitle = 'Setting Pembayaran';
$activePage = 'payment-settings';
$GLOBALS['pageScript'] = 'assets/js/payment-settings.js';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="payment-settings">
  <div>
    <h2 class="text-2xl font-black">Setting Pembayaran</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400">Atur metode pembayaran dan konfirmasi pembayaran.</p>
  </div>

  <div class="grid gap-5 xl:grid-cols-2">
    <form class="space-y-5" id="paymentSettingsForm">
      <div class="card space-y-3 p-5">
        <h3 class="font-black">QRIS</h3>
        <label>Status QRIS
          <select class="select mt-1" id="paymentQrisEnabled">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </label>
        <label>QRIS Image URL<input class="input mt-1" id="paymentQrisImage" placeholder="https://domain.com/qris.jpg" type="url"></label>
      </div>

      <div class="card space-y-3 p-5">
        <h3 class="font-black">Rekening Bank</h3>
        <label>Status Bank Transfer
          <select class="select mt-1" id="paymentBankEnabled">
            <option value="0">Nonaktif</option>
            <option value="1">Aktif</option>
          </select>
        </label>
        <label>Nama Bank<input class="input mt-1" id="paymentBankName" placeholder="BCA"></label>
        <label>Nomor Rekening<input class="input mt-1" id="paymentBankAccount" placeholder="1234567890"></label>
        <label>Nama Pemilik<input class="input mt-1" id="paymentBankHolder" placeholder="Digital Store"></label>
      </div>

      <div class="card space-y-3 p-5">
        <h3 class="font-black">Instruksi Pembayaran</h3>
        <label>Instruksi Pembayaran<textarea class="textarea mt-1" id="paymentInstruction" rows="3" placeholder="Selesaikan pembayaran, lalu konfirmasi ke admin."></textarea></label>
      </div>

      <div class="card space-y-3 p-5">
        <h3 class="font-black">WhatsApp Konfirmasi</h3>
        <label>Nomor WhatsApp Admin<input class="input mt-1" id="paymentAdminWhatsapp" placeholder="6281234567890" type="tel"></label>
        <label>Template Pesan<textarea class="textarea mt-1" id="paymentWhatsappMessage" rows="3" placeholder="Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek."></textarea></label>
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Variabel: {order_code}, {customer_name}, {total_amount}, {status}</p>
        <p class="hidden text-xs font-bold text-amber-600" id="paymentWhatsappWarning">Disarankan memakai {order_code}.</p>
        <button class="btn-primary w-full" type="submit">Simpan</button>
      </div>
    </form>

    <aside class="card h-fit p-5">
      <h3 class="font-black">Preview</h3>
      <div class="mt-4 rounded-3xl border border-slate-200 p-5 text-center dark:border-slate-800" id="paymentPreview"></div>
    </aside>
  </div>
</section>
<script src="../assets/js/whatsapp.js"></script>
<?php renderFooter(); ?>
