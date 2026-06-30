<?php
$pageTitle = 'Kelola Produk';
$activePage = 'products';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="products">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div><h2 class="text-2xl font-black tracking-tight">Produk</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola produk digital toko.</p></div>
    <button class="btn-primary" id="addProductBtn" type="button">Tambah Produk</button>
  </div>
  <div class="card p-4">
    <input class="input" id="productSearch" placeholder="Cari produk" type="search">
  </div>
  <div class="card table-wrap"><table><thead><tr><th>Produk</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="productsTable"></tbody></table><div class="hidden p-8 text-center text-sm font-bold text-slate-500" id="productsEmpty">Produk tidak ditemukan.</div></div>
</section>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="productModal">
  <div class="card max-h-[90vh] w-full max-w-5xl overflow-auto p-5">
    <div class="mb-4 flex items-center justify-between"><h3 class="text-xl font-black" id="productModalTitle">Tambah Produk</h3><button class="btn-soft" data-close-modal type="button">Tutup</button></div>
    <form class="grid gap-3 md:grid-cols-2" id="productForm">
      <input type="hidden" id="productId">
      <label class="md:row-start-1 md:col-start-1">Gambar Produk</label>
      <input type="hidden" id="productImage">
      <div class="mt-1 md:row-start-2 md:col-start-1 md:row-span-4" id="productImageDropzone">
        <div class="relative cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 p-6 text-center transition hover:border-slate-400 dark:border-slate-700 dark:hover:border-slate-500" id="productImageDropArea">
          <div id="productImagePlaceholder">
            <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16v-8m0 0l-3 3m3-3l3 3M3 16V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            <p class="mt-2 text-sm font-bold text-slate-500 dark:text-slate-400">Seret gambar ke sini atau <span class="text-blue-600 dark:text-blue-400">pilih file</span></p>
            <p class="mt-1 text-xs text-slate-400">JPG, JPEG, atau PNG &middot; Maks 2MB &middot; Auto-resize 800×800</p>
          </div>
          <div class="hidden" id="productImagePreview">
            <img class="mx-auto h-40 w-40 rounded-2xl object-cover" id="productImagePreviewImg" alt="Preview">
            <div class="mt-2 flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <span id="productImageFileName"></span>
              <span id="productImageFileSize"></span>
            </div>
            <button type="button" class="mt-2 text-xs font-bold text-red-600 hover:underline dark:text-red-400" id="productImageRemoveBtn">Hapus gambar</button>
          </div>
          <input type="file" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" id="productImageFileInput" accept="image/jpeg,image/png">
        </div>
        <div class="mt-2 hidden" id="productImageUploadProgress">
          <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
            <div class="h-full w-0 rounded-full bg-blue-600 transition-all" id="productImageProgressBar"></div>
          </div>
        </div>
      </div>
      <label class="md:row-start-1 md:col-start-2">Nama Produk<input class="input mt-1" id="productName" required placeholder="Google AI Pro"></label>
      <label class="md:row-start-2 md:col-start-2">Harga<input class="input mt-1" id="productPrice" required type="number" placeholder="25000"></label>
      <label class="md:row-start-3 md:col-start-2">Harga Coret<input class="input mt-1" id="productOriginalPrice" type="number" placeholder="50000"></label>
      <label class="md:row-start-4 md:col-start-2">Status<select class="select mt-1" id="productStatus"><option value="active">Aktif</option><option value="draft">Draft</option><option value="out_of_stock">Habis</option></select></label>
      <label class="md:row-start-5 md:col-start-2 flex items-center gap-2 font-bold"><input id="productFeatured" type="checkbox"> Featured</label>
      <label class="md:col-span-2">Deskripsi<textarea class="textarea mt-1" id="productDescription" rows="3" placeholder="Lorem ipsum dolor sit amet."></textarea></label>
      <section class="md:col-span-2 rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div><h4 class="font-black">Data Akun Premium</h4><p class="text-xs font-bold text-slate-500 dark:text-slate-400">Stok otomatis dari akun berstatus available.</p></div>
          <button class="btn-soft" id="addProductAccountBtn" type="button">Tambah Akun</button>
        </div>
        <div id="productAccountsSummary" class="mb-3 text-xs font-bold text-slate-500 dark:text-slate-400"></div>
        <div class="table-wrap rounded-2xl border border-slate-200 dark:border-slate-800"><table><thead><tr><th>Data Akun</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="productAccountsTable"></tbody></table><div class="hidden p-6 text-center text-sm font-bold text-slate-500" id="productAccountsEmpty">Belum ada akun.</div></div>
      </section>
      <div class="flex justify-end gap-2 md:col-span-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary" type="submit">Simpan</button></div>
    </form>
  </div>
</div>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="accountModal"><div class="card w-full max-w-2xl p-5"><div class="mb-4 flex items-center justify-between"><h3 class="text-xl font-black" id="accountModalTitle">Tambah Akun</h3><button class="btn-soft" data-close-modal type="button">Tutup</button></div><form class="grid gap-3" id="accountForm"><input type="hidden" id="accountId"><label>Data Akun<textarea class="textarea mt-1" id="accountData" rows="8" required placeholder="email@example.com | password123 | PIN 1234"></textarea></label><label>Status<select class="select mt-1" id="accountStatus"><option value="available">available</option><option value="reserved">reserved</option><option value="sold">sold</option></select></label><div class="flex justify-end gap-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary" type="submit">Simpan Akun</button></div></form></div></div>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="deleteModal"><div class="card w-full max-w-md p-5"><h3 class="text-xl font-black">Arsipkan Produk?</h3><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Arsipkan produk <strong id="deleteModalName"></strong>? Produk tidak tampil ke pembeli, tetapi histori order tetap aman.</p><div class="mt-5 flex justify-end gap-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary !bg-red-600" id="confirmDelete" type="button">Arsipkan</button></div></div></div>
<?php
$GLOBALS['pageScript'] = 'assets/js/product-image-upload.js';
renderFooter();
