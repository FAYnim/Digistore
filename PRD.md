# PRD — Catalog-First Produk Digital

## 1. Ringkasan Produk

Website ini adalah katalog produk digital untuk toko online sederhana. Fokus utama halaman adalah membantu buyer/visitor langsung melihat, mencari, memfilter, dan membeli produk digital, terutama **akun premium**.

Saat halaman dibuka, katalog harus langsung terlihat tanpa perlu scroll melewati section company profile atau hero panjang. Elemen branding tetap ada, tetapi ringkas dan mendukung tujuan utama: menampilkan produk secepat mungkin.

Pada tahap awal, website belum terhubung ke backend, database, payment gateway, atau checkout asli. Semua data menggunakan dummy data dari JavaScript. Gambar produk menggunakan `placehold.co`.

---

## 2. Tujuan Utama

Tujuan utama website adalah menjadi etalase/catalog marketplace ringan untuk membeli produk digital.

Halaman harus mampu:

1. Menampilkan katalog produk segera setelah navbar.
2. Memprioritaskan produk akun premium.
3. Memiliki search produk yang mudah terlihat.
4. Memiliki filter kategori yang mudah digunakan.
5. Memiliki sorting produk.
6. Menampilkan harga, stok, rating, badge, dan tombol beli secara jelas.
7. Responsive di mobile, tablet, dan desktop.
8. Mendukung dark mode dan light mode.
9. Menggunakan data dummy dari JavaScript.
10. Siap dikembangkan ke checkout, detail produk, keranjang, dan admin.

---

## 3. Prinsip Halaman

### Wajib

- Katalog muncul langsung di viewport awal.
- Search, sorting, dan kategori berada sebelum atau dekat product grid.
- Product card menjadi elemen visual paling dominan.
- Copywriting singkat, langsung ke kebutuhan pembeli.
- Company profile tidak menjadi fokus halaman utama.

### Tidak boleh

- Hero panjang yang membuat katalog turun jauh.
- Section company profile di atas katalog.
- CTA umum yang mengganggu alur belanja.
- Terlalu banyak teks sebelum user melihat produk.

---

## 4. Scope Versi Ini

### Termasuk

- Navbar sticky.
- Catalog-first section sebagai section pertama.
- Search produk.
- Filter kategori.
- Sorting produk.
- Product grid responsive.
- Product card.
- Section produk unggulan ringkas setelah katalog.
- Benefit ringkas setelah katalog.
- Testimoni dummy setelah katalog.
- Footer.
- Theme switcher dark/light.
- Mobile menu.
- Modal/alert checkout dummy.
- Data dummy dari JavaScript.

### Tidak termasuk

- Login user.
- Admin dashboard.
- Database.
- API backend.
- Checkout asli.
- Payment gateway.
- Upload bukti pembayaran.
- Sistem order.
- Detail produk dinamis.
- Keranjang permanen.
- Autentikasi.
- Email notification.
- Halaman about lengkap.

---

## 5. Rekomendasi Halaman Terpisah

Jika toko ingin menampilkan profil bisnis, buat halaman terpisah:

```text
/about.html
```

Konten about dapat berisi:

- Cerita toko.
- Keunggulan toko.
- Cara order.
- Kontak.
- FAQ.

Navigasi utama boleh memiliki link `About`, tetapi halaman utama tetap berfokus pada katalog.

---

## 6. Target Pengguna

### 6.1 Buyer Produk Digital

Pengguna yang ingin membeli produk digital seperti akun premium, tools AI, template, source code, dan produk digital lain.

Kebutuhan utama:

- Langsung melihat produk.
- Cepat mencari produk tertentu.
- Melihat harga dan stok.
- Memfilter berdasarkan kategori.
- Membeli lewat tombol aksi yang jelas.
- Nyaman digunakan dari HP.

### 6.2 Pemilik Toko

Pemilik toko membutuhkan katalog yang mudah diedit dan terlihat profesional.

Kebutuhan utama:

- Produk mudah diganti dari JavaScript.
- Tampilan ringan.
- Fokus pada konversi.
- Siap dikembangkan ke sistem toko penuh.

---

## 7. Tech Stack

```text
Frontend:
- HTML
- CSS
- Tailwind CSS CDN
- JavaScript Vanilla

Data:
- Dummy data dari array JavaScript

Image:
- placehold.co

Storage:
- localStorage untuk preferensi tema

Deployment:
- Shared hosting
- Netlify
- Vercel
- GitHub Pages
```

Catatan:

- Tidak menggunakan framework seperti React, Vue, atau Next.js.
- Tidak menggunakan jQuery.
- Tidak menggunakan database.
- Tailwind CDN boleh untuk prototype.

---

## 8. Struktur Halaman

Urutan halaman catalog-first:

```text
1. Navbar
2. Product Catalog Section
3. Featured Products Section
4. Benefits Section
5. Testimonials Section
6. Footer
7. Modal Checkout Dummy
```

Hero panjang, statistik besar, dan final CTA tidak menjadi prioritas. Jika tetap digunakan, harus ringkas dan berada setelah katalog.

---

## 9. Detail Section

## 9.1 Navbar

Navbar berada di bagian atas halaman dan sticky.

Elemen navbar:

- Logo toko.
- Menu navigasi ringkas.
- Tombol toggle tema.
- Tombol cart dummy.
- Tombol menu mobile.

Menu rekomendasi:

```text
Katalog
Kategori
Unggulan
Testimoni
Kontak
About
```

Elemen wajib:

```text
Logo: DigiStore
Theme Toggle: Light/Dark
Cart Button: icon + jumlah item dummy
Mobile Menu Button
```

---

## 9.2 Product Catalog Section

Section ini adalah section pertama setelah navbar.

Konten atas katalog:

```text
Badge:
Katalog Produk Digital

Headline:
Pilih Akun Premium & Produk Digital Siap Pakai

Subheadline:
Cari produk digital favoritmu, cek harga dan stok, lalu beli dengan cepat.
```

Fitur katalog:

- Search input.
- Sort dropdown.
- Filter kategori.
- Product grid responsive.
- Empty state jika produk tidak ditemukan.

Product grid:

```text
Mobile: 1 kolom
Tablet: 2 kolom
Desktop: 3-4 kolom
```

Katalog harus terlihat dalam viewport awal pada desktop dan mobile. Di mobile, minimal heading, search, kategori awal, dan bagian awal product card harus terlihat tanpa scroll panjang.

---

## 9.3 Category Filter

Kategori dummy:

```text
Semua Produk
Akun Premium
Source Code
Template Website
Tools AI
Desain Digital
Produktivitas
```

Interaksi:

- Klik kategori memfilter product grid.
- Kategori aktif memiliki style berbeda.
- Tombol `Semua Produk` mengembalikan semua produk.

---

## 9.4 Featured Products Section

Section ini berada setelah katalog utama.

Tujuan:

- Menampilkan produk rekomendasi tambahan.
- Tidak menghalangi katalog utama.

Kriteria:

- Produk dengan `isFeatured: true`.
- Tampil 3-4 produk.
- Badge seperti `Best Seller`, `Popular`, `Recommended`, `Limited`.

---

## 9.5 Product Card

Isi product card:

```text
- Gambar produk
- Badge kategori
- Badge status stok
- Nama produk
- Deskripsi singkat
- Rating dummy
- Jumlah terjual dummy
- Harga
- Harga coret opsional
- Stok
- Tombol Lihat Detail
- Tombol Beli Sekarang
```

State stok:

```text
Tersedia
Stok Terbatas
Habis
```

Jika stok habis:

- Tombol beli disabled.
- Badge berubah menjadi `Habis`.
- Card tetap tampil.

---

## 9.6 Benefits Section

Benefit ditampilkan setelah katalog/unggulan.

Benefit dummy:

```text
Pengiriman Cepat
Produk dikirim secara cepat setelah pembayaran dikonfirmasi.

Produk Terverifikasi
Setiap produk dicek sebelum ditampilkan di katalog.

Harga Terjangkau
Pilihan produk digital dengan harga kompetitif.

Support Responsif
Bantuan tersedia untuk kendala produk dan pesanan.
```

---

## 9.7 Testimonials Section

Testimoni dummy ditampilkan setelah benefit.

Jumlah awal:

```text
3 testimoni
```

Layout:

```text
Mobile: 1 kolom
Desktop: 3 kolom
```

---

## 9.8 Footer

Footer berisi informasi ringkas toko dan navigasi.

Isi footer:

```text
Logo: DigiStore
Deskripsi singkat katalog produk digital.
Menu: Katalog, Kategori, Unggulan, Testimoni, About
Kontak: WhatsApp dummy, Email dummy, Instagram dummy
Copyright: © 2026 DigiStore. All rights reserved.
```

---

## 10. Desain UI

Gaya visual:

```text
Catalog-first
Clean
Modern
Marketplace
Fast-loading
Mobile-friendly
```

Karakter visual:

- Product grid dominan.
- Card jelas dan mudah dibaca.
- Search/filter mudah ditemukan.
- Spacing lebih rapat dibanding company profile landing page.
- Tidak terlalu banyak animasi.
- Warna kontras jelas.

---

## 11. Tema

### Light Mode

```text
Background: #F8FAFC
Surface: #FFFFFF
Card: #FFFFFF
Text Primary: #0F172A
Text Secondary: #64748B
Border: #E2E8F0
Accent: #2563EB
Accent Hover: #1D4ED8
Success: #16A34A
Warning: #F59E0B
Danger: #DC2626
```

### Dark Mode

```text
Background: #020617
Surface: #0F172A
Card: #111827
Text Primary: #F8FAFC
Text Secondary: #94A3B8
Border: #1E293B
Accent: #60A5FA
Accent Hover: #3B82F6
Success: #22C55E
Warning: #FACC15
Danger: #F87171
```

---

## 12. Functional Requirement

## 12.1 Theme Toggle

- Toggle tersedia di navbar.
- Class `dark` ditambahkan/dihapus pada root element.
- Preferensi disimpan di `localStorage`.
- Saat halaman dibuka ulang, tema terakhir digunakan.

## 12.2 Product Rendering

- Produk dirender dari array JavaScript.
- Product card dibuat dinamis.
- Jika data kosong, tampilkan empty state.

## 12.3 Search Produk

- Search real-time.
- Tidak case-sensitive.
- Mencari berdasarkan nama dan deskripsi.
- Jika kosong, tampilkan empty state.

## 12.4 Filter Kategori

- Tersedia tombol semua produk.
- Tersedia tombol tiap kategori.
- Filter dapat digabung dengan search dan sorting.

## 12.5 Sorting Produk

Pilihan sorting:

```text
Terbaru
Harga Terendah
Harga Tertinggi
Rating Tertinggi
```

## 12.6 Tombol Beli Sekarang

Behavior MVP:

- Jika stok tersedia, klik membuka modal dummy.
- Modal menampilkan nama produk.
- Jika stok habis, tombol disabled.

---

## 13. Dummy Data Produk

Produk disimpan dalam array JavaScript dengan struktur:

```js
{
  id: 1,
  name: "ChatGPT Plus Sharing",
  category: "Akun Premium",
  price: 45000,
  originalPrice: 75000,
  image: "https://placehold.co/600x400?text=ChatGPT+Plus",
  description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
  rating: 4.8,
  sold: 210,
  stock: 8,
  badge: "Popular",
  isFeatured: true,
  createdAt: "2026-06-02"
}
```

Produk akun premium sebaiknya menjadi mayoritas data awal.

---

## 14. Format Harga

Harga ditampilkan dalam format Rupiah:

```js
function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0
  }).format(value);
}
```

---

## 15. Struktur File

```text
digital-store/
├── index.html
├── PRD.md
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        ├── data.js
        └── app.js
```

---

## 16. Acceptance Criteria

Website dianggap selesai jika:

1. Katalog tampil langsung setelah navbar.
2. Visitor tidak perlu melewati hero/company profile untuk melihat produk.
3. Semua produk dummy tampil dari JavaScript.
4. Search produk berjalan.
5. Filter kategori berjalan.
6. Sorting produk berjalan.
7. Product card memiliki gambar, nama, deskripsi, kategori, harga, rating, stok, dan tombol aksi.
8. Produk habis memiliki tombol disabled.
9. Empty state tampil ketika hasil kosong.
10. Dark/light mode berjalan dan tersimpan.
11. Navbar mobile berjalan.
12. Tampilan responsive di mobile dan desktop.
13. Section company profile tidak mendominasi halaman utama.
14. About/company profile diarahkan sebagai halaman terpisah bila dibutuhkan.

---

## 17. Prioritas Pengerjaan

```text
1. Pindahkan katalog menjadi section pertama.
2. Buat heading katalog ringkas.
3. Pastikan search/sort/filter berada di area awal.
4. Render product grid tepat setelah kontrol katalog.
5. Pindahkan featured/benefit/testimoni ke bawah katalog.
6. Sesuaikan navbar anchor.
7. Rapikan spacing agar katalog terlihat tanpa scroll panjang.
8. Test responsive mobile dan desktop.
```

---

## 18. Future Development

```text
1. Halaman detail produk.
2. Keranjang belanja.
3. Checkout.
4. Integrasi WhatsApp order.
5. Upload bukti pembayaran.
6. Halaman status pesanan.
7. Admin dashboard.
8. CRUD produk.
9. CRUD kategori.
10. Database MySQL.
11. Backend PHP.
12. Payment gateway.
13. Auto delivery produk digital.
14. Halaman about.
15. FAQ dan cara order.
```

---

## 19. Kesimpulan

Website ini bukan company profile, melainkan katalog produk digital. Halaman utama harus langsung menjawab kebutuhan buyer: melihat produk, mencari, memfilter, mengecek harga/stok, lalu membeli. Konten profil toko boleh ada, tetapi sebaiknya dipisahkan ke halaman about agar tidak mengganggu fokus katalog dan konversi.
