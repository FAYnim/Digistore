<?php
$pageTitle = 'Kategori';
$activePage = 'categories';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="categories">
  <div class="flex items-center justify-between">
    <div><h2 class="text-2xl font-black">Kategori</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola kategori produk digital.</p></div>
    <button class="btn-primary" id="addCategoryBtn" type="button">Tambah Kategori</button>
  </div>
  <div class="card table-wrap"><table><thead><tr><th>Nama</th><th>Slug</th><th>Jumlah Produk</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="categoriesTable"></tbody></table></div>
</section>

<!-- Modal Tambah/Edit Kategori -->
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="categoryModal">
  <div class="card w-full max-w-xl p-5">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-black" id="categoryModalTitle">Tambah Kategori</h3>
      <button class="btn-soft" data-close-modal type="button">Tutup</button>
    </div>
    <form class="grid gap-3" id="categoryForm">
      <input type="hidden" id="categoryId">
      <label>Nama Kategori<input class="input mt-1" id="categoryName" required placeholder="Tools AI"></label>
      <label>Slug<input class="input mt-1" id="categorySlug" required placeholder="tools-ai"></label>
      <label>Icon (Font Awesome class)<input class="input mt-1" id="categoryIcon" placeholder="fa-solid fa-wand-magic-sparkles"></label>
      <label>Status
        <select class="select mt-1" id="categoryStatus">
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </label>
      <div class="flex justify-end gap-2">
        <button class="btn-soft" data-close-modal type="button">Batal</button>
        <button class="btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Hapus Kategori -->
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="deleteCategoryModal">
  <div class="card w-full max-w-md p-5">
    <h3 class="text-xl font-black">Hapus Kategori?</h3>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Hapus kategori <strong id="deleteCategoryModalName"></strong>? Produk yang menggunakan kategori ini akan menjadi tanpa kategori.</p>
    <div class="mt-5 flex justify-end gap-2">
      <button class="btn-soft" data-close-modal type="button">Batal</button>
      <button class="btn-primary !bg-red-600" id="confirmDeleteCategory" type="button">Hapus</button>
    </div>
  </div>
</div>
<?php renderFooter(); ?>
