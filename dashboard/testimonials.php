<?php
$pageTitle = 'Testimoni';
$activePage = 'testimonials';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="testimonials">
  <div><h2 class="text-2xl font-black">Testimoni</h2><p class="text-sm text-slate-500 dark:text-slate-400">Feedback dummy katalog.</p></div>
  <div class="card table-wrap"><table><thead><tr><th>Nama</th><th>Role</th><th>Rating</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="testimonialsTable"></tbody></table></div>
</section>
<?php renderFooter(); ?>
