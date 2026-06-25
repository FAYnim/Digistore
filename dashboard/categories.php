<?php
$pageTitle = 'Kategori';
$activePage = 'categories';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="categories">
  <div class="flex items-center justify-between"><div><h2 class="text-2xl font-black">Kategori</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola kategori dummy.</p></div><button class="btn-primary" id="addCategoryBtn" type="button">Tambah Kategori</button></div>
  <div class="card table-wrap"><table><thead><tr><th>Nama</th><th>Slug</th><th>Jumlah Produk</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="categoriesTable"></tbody></table></div>
</section>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="categoryModal"><div class="card w-full max-w-xl p-5"><div class="mb-4 flex items-center justify-between"><h3 class="text-xl font-black" id="categoryModalTitle">Tambah Kategori</h3><button class="btn-soft" data-close-modal type="button">Tutup</button></div><form class="grid gap-3" id="categoryForm"><input type="hidden" id="categoryId"><label>Nama Kategori<input class="input mt-1" id="categoryName" required placeholder="Tools AI"></label><label>Slug<input class="input mt-1" id="categorySlug" required placeholder="tools-ai"></label><label>Icon<input class="input mt-1" id="categoryIcon" placeholder="✦"></label><label>Status<select class="select mt-1" id="categoryStatus"><option>Aktif</option><option>Draft</option></select></label><div class="flex justify-end gap-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary" type="submit">Simpan</button></div></form></div></div>
<?php renderFooter(); ?>
