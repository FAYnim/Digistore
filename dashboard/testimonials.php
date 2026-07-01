<?php

$pageTitle = 'Testimoni';
$activePage = 'testimonials';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="testimonials">
  <div class="flex items-center justify-between">
    <div><h2 class="text-2xl font-black">Testimoni</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola ulasan pelanggan.</p></div>
    <button class="btn-primary" id="addTestimonialBtn" type="button">Tambah Testimoni</button>
  </div>
  <div class="card table-wrap"><table><thead><tr><th>Nama</th><th>Role</th><th>Rating</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="testimonialsTable"></tbody></table></div>
</section>

<!-- Modal Tambah/Edit Testimoni -->
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="testimonialModal">
  <div class="card max-h-[90vh] w-full max-w-xl overflow-y-auto p-5">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-black" id="testimonialModalTitle">Tambah Testimoni</h3>
      <button class="btn-soft" data-close-modal type="button">Tutup</button>
    </div>
    <form class="grid gap-3" id="testimonialForm">
      <input type="hidden" id="testimonialId">
      <label>Nama<input class="input mt-1" id="testimonialName" required placeholder="Raka Pratama"></label>
      <label>Role<input class="input mt-1" id="testimonialRole" placeholder="Mahasiswa, Freelancer, dll"></label>
      <label>Rating
        <select class="select mt-1" id="testimonialRating">
          <option value="5">★★★★★ (5)</option>
          <option value="4">★★★★☆ (4)</option>
          <option value="3">★★★☆☆ (3)</option>
          <option value="2">★★☆☆☆ (2)</option>
          <option value="1">★☆☆☆☆ (1)</option>
        </select>
      </label>
      <label>Status
        <select class="select mt-1" id="testimonialStatus">
          <option value="visible">Tampil</option>
          <option value="hidden">Sembunyi</option>
        </select>
      </label>
      <label class="col-span-full">Pesan<textarea class="textarea mt-1" id="testimonialMessage" rows="3" required placeholder="Produk cepat dan aman..."></textarea></label>
<label class="col-span-full">Gambar (opsional)
  <input type="hidden" id="testimonialImagePath">
  <div class="mt-1" id="testimonialImageDropzone">
    <div class="relative cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 p-6 text-center transition hover:border-slate-400 dark:border-slate-700 dark:hover:border-slate-500" id="testimonialImageDropArea">
      <div id="testimonialImagePlaceholder">
        <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16v-8m0 0l-3 3m3-3l3 3M3 16V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        <p class="mt-2 text-sm font-bold text-slate-500 dark:text-slate-400">Seret gambar ke sini atau <span class="text-blue-600 dark:text-blue-400">pilih file</span></p>
        <p class="mt-1 text-xs text-slate-400">JPG, JPEG, atau PNG &middot; Maks 2MB</p>
      </div>
      <div class="hidden" id="testimonialImagePreview">
        <img class="mx-auto h-40 w-40 rounded-2xl object-cover" id="testimonialImagePreviewImg" alt="Preview">
        <div class="mt-2 flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
          <span id="testimonialImageFileName"></span>
          <span id="testimonialImageFileSize"></span>
        </div>
        <button type="button" class="mt-2 text-xs font-bold text-red-600 hover:underline dark:text-red-400" id="testimonialImageRemoveBtn">Hapus gambar</button>
      </div>
      <input type="file" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" id="testimonialImageFileInput" accept="image/jpeg,image/png">
    </div>
    <div class="mt-2 hidden" id="testimonialImageUploadProgress">
      <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
        <div class="h-full w-0 rounded-full bg-blue-600 transition-all" id="testimonialImageProgressBar"></div>
      </div>
    </div>
  </div>
</label>
      <div class="flex justify-end gap-2">
        <button class="btn-soft" data-close-modal type="button">Batal</button>
        <button class="btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Hapus Testimoni -->
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="deleteTestimonialModal">
  <div class="card w-full max-w-md p-5">
    <h3 class="text-xl font-black">Hapus Testimoni?</h3>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Hapus testimoni dari <strong id="deleteTestimonialModalName"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
    <div class="mt-5 flex justify-end gap-2">
      <button class="btn-soft" data-close-modal type="button">Batal</button>
      <button class="btn-primary !bg-red-600" id="confirmDeleteTestimonial" type="button">Hapus</button>
    </div>
  </div>
</div>
<?php renderFooter(); ?>
