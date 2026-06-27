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
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="productModal"><div class="card max-h-[90vh] w-full max-w-3xl overflow-auto p-5"><div class="mb-4 flex items-center justify-between"><h3 class="text-xl font-black" id="productModalTitle">Tambah Produk</h3><button class="btn-soft" data-close-modal type="button">Tutup</button></div><form class="grid gap-3 md:grid-cols-2" id="productForm"><input type="hidden" id="productId"><label>Nama Produk<input class="input mt-1" id="productName" required placeholder="Google AI Pro"></label><label>Slug<input class="input mt-1" id="productSlug" required placeholder="google-ai-pro"></label><label>Harga<input class="input mt-1" id="productPrice" required type="number" placeholder="25000"></label><label>Harga Coret<input class="input mt-1" id="productOriginalPrice" type="number" placeholder="50000"></label><label>Stok<input class="input mt-1" id="productStock" required type="number" placeholder="12"></label><label>Status<select class="select mt-1" id="productStatus"><option value="active">Aktif</option><option value="draft">Draft</option><option value="out_of_stock">Habis</option></select></label><label>Gambar URL<input class="input mt-1" id="productImage" placeholder="https://placehold.co/600x400"></label><label class="md:col-span-2">Deskripsi<textarea class="textarea mt-1" id="productDescription" rows="3" placeholder="Lorem ipsum dolor sit amet."></textarea></label><label class="flex items-center gap-2 font-bold"><input id="productFeatured" type="checkbox"> Featured</label><div class="flex justify-end gap-2 md:col-span-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary" type="submit">Simpan</button></div></form></div></div>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="deleteModal"><div class="card w-full max-w-md p-5"><h3 class="text-xl font-black">Hapus Produk?</h3><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Hapus produk <strong id="deleteModalName"></strong>? Tindakan ini tidak dapat dibatalkan.</p><div class="mt-5 flex justify-end gap-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary !bg-red-600" id="confirmDelete" type="button">Hapus</button></div></div></div>
<?php renderFooter(); ?>
