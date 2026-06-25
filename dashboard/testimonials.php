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
  <div class="card w-full max-w-xl p-5">
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
