<?php
$pageTitle = 'Setting Toko';
$activePage = 'settings';
require __DIR__ . '/components/layout.php';
renderHeader($pageTitle, $activePage);
?>
<section class="space-y-5" data-page="settings">
  <div><h2 class="text-2xl font-black">Setting Toko</h2><p class="text-sm text-slate-500 dark:text-slate-400">Kelola informasi dan tampilan toko.</p></div>
  <form class="grid gap-5 xl:grid-cols-3" id="settingsForm">
    <div class="card space-y-3 p-5">
      <h3 class="font-black">Informasi Toko</h3>
      <label>Nama Toko<input class="input mt-1" id="settingStoreName" placeholder="DigiStore"></label>
      <label>Tagline<input class="input mt-1" id="settingStoreTagline" placeholder="Produk Digital Premium"></label>
      <label>Deskripsi<textarea class="textarea mt-1" id="settingStoreDescription" rows="4" placeholder="Katalog produk digital ringan dan cepat."></textarea></label>
    </div>
    <div class="card space-y-3 p-5">
      <h3 class="font-black">Kontak</h3>
      <label>WhatsApp (angka saja)<input class="input mt-1" id="settingWhatsapp" placeholder="6281234567890" type="tel"></label>
      <label>Email<input class="input mt-1" id="settingEmail" placeholder="admin@example.com" type="email"></label>
      <label>Instagram (tanpa @)<input class="input mt-1" id="settingInstagram" placeholder="digistore"></label>
    </div>
    <div class="card space-y-3 p-5">
      <h3 class="font-black">Tampilan</h3>
      <label>Tema Default
        <select class="select mt-1" id="settingTheme">
          <option value="light">Light</option>
          <option value="dark">Dark</option>
          <option value="system">System</option>
        </select>
      </label>
      <label>Warna Accent<input class="input mt-1" id="settingAccentColor" type="color" value="#2563EB"></label>
      <button class="btn-primary w-full mt-2" type="submit">Simpan Setting</button>
    </div>
  </form>
</section>
<?php renderFooter(); ?>
