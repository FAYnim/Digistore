<?php
$pageTitle = 'Setting Toko';
$activePage = 'settings';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="settings">
  <div><h2 class="text-2xl font-black">Setting Toko</h2><p class="text-sm text-slate-500 dark:text-slate-400">Simulasi pengaturan UI.</p></div>
  <form class="grid gap-5 xl:grid-cols-3" id="settingsForm">
    <div class="card space-y-3 p-5"><h3 class="font-black">Informasi Toko</h3><label>Nama Toko<input class="input mt-1" value="DigiStore"></label><label>Tagline<input class="input mt-1" value="Produk digital siap pakai"></label><label>Deskripsi<textarea class="textarea mt-1" rows="4">Katalog produk digital ringan dan cepat.</textarea></label></div>
    <div class="card space-y-3 p-5"><h3 class="font-black">Kontak</h3><label>WhatsApp<input class="input mt-1" value="081234567890"></label><label>Email<input class="input mt-1" value="admin@digistore.test"></label><label>Instagram<input class="input mt-1" value="@digistore"></label></div>
    <div class="card space-y-3 p-5"><h3 class="font-black">Tampilan</h3><label>Tema Default<select class="select mt-1"><option>System</option><option>Light</option><option>Dark</option></select></label><label>Warna Accent<input class="input mt-1" value="#2563EB"></label><label>Produk per Baris<select class="select mt-1"><option>3</option><option>4</option></select></label><button class="btn-primary w-full" type="submit">Simpan</button></div>
  </form>
</section>
<?php renderFooter(); ?>
