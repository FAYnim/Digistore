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
        <label>Gambar QRIS</label>
        <input type="hidden" id="paymentQrisImage">
        <div class="mt-1" id="qrisDropzone">
          <div class="relative cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 p-6 text-center transition hover:border-slate-400 dark:border-slate-700 dark:hover:border-slate-500" id="qrisDropArea">
            <div id="qrisPlaceholder">
              <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16v-8m0 0l-3 3m3-3l3 3M3 16V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
              <p class="mt-2 text-sm font-bold text-slate-500 dark:text-slate-400">Seret gambar ke sini atau <span class="text-blue-600 dark:text-blue-400">pilih file</span></p>
              <p class="mt-1 text-xs text-slate-400">JPG, JPEG, atau PNG &middot; Maks 2MB</p>
            </div>
            <div class="hidden" id="qrisPreview">
              <img class="mx-auto h-48 w-48 rounded-2xl object-cover" id="qrisPreviewImg" alt="Preview QRIS">
              <p class="mt-2 truncate text-xs text-slate-500 dark:text-slate-400" id="qrisFileName"></p>
              <button type="button" class="mt-2 text-xs font-bold text-red-600 hover:underline dark:text-red-400" id="qrisRemoveBtn">Hapus gambar</button>
            </div>
            <input type="file" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" id="qrisFileInput" accept="image/jpeg,image/png">
          </div>
          <div class="mt-2 hidden" id="qrisUploadProgress">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
              <div class="h-full w-0 rounded-full bg-blue-600 transition-all" id="qrisProgressBar"></div>
            </div>
          </div>
        </div>
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
