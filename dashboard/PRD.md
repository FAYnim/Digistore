# PRD — Dashboard UI untuk Landing Page Katalog Produk Digital

## 1. Ringkasan Produk

Dashboard ini adalah halaman admin berbasis UI untuk mengelola tampilan katalog produk digital yang berada di path utama proyek.

Struktur relasi halaman:

```text
/
Landing page katalog produk digital

/dashboard/
Dashboard admin untuk mengelola data katalog
```

Pada tahap ini, dashboard belum terhubung ke backend, database, atau API. Semua data yang tampil menggunakan dummy data dari JavaScript. Backend PHP disiapkan sebagai struktur dasar halaman, tetapi belum menjalankan proses CRUD nyata.

Dashboard dibuat sederhana, desktop first, responsive, mendukung tema gelap dan terang, serta tidak menggunakan teks panjang yang membuat pengguna malas membaca.

---

## 2. Tujuan Dashboard

Dashboard bertujuan menjadi pusat pengelolaan UI untuk katalog produk digital.

Fungsi utama:

```text
- Melihat ringkasan toko
- Melihat daftar produk
- Menambah produk dummy secara UI
- Mengedit produk dummy secara UI
- Menghapus produk dummy secara UI
- Mengelola kategori dummy
- Melihat pesanan dummy
- Melihat testimoni dummy
- Mengatur tampilan toko secara dummy
```

Catatan penting:

```text
Semua fitur masih berbasis UI.
Tidak ada data yang tersimpan ke database.
Tidak ada koneksi nyata ke landing page katalog.
Tidak ada autentikasi sungguhan.
Tidak ada proses CRUD backend.
```

---

## 3. Scope Versi Ini

### Termasuk

```text
- Layout dashboard
- Sidebar
- Topbar
- Dark mode / light mode
- Halaman overview
- Halaman produk
- Halaman kategori
- Halaman pesanan
- Halaman testimoni
- Halaman setting toko
- Form tambah/edit produk UI-only
- Modal konfirmasi hapus UI-only
- Dummy data JavaScript
- Responsive layout
- Struktur folder dashboard
- File PHP sebagai wrapper halaman
```

### Tidak Termasuk

```text
- Login asli
- Session PHP
- Database MySQL
- API
- CRUD backend
- Upload gambar asli
- Validasi server-side
- Payment gateway
- Sinkronisasi data dengan landing page utama
- Role admin
- Export laporan
```

---

## 4. Tech Stack

```text
Frontend:
- HTML
- CSS
- Tailwind CSS
- JavaScript Vanilla

Backend Structure:
- PHP Native

Data:
- Dummy data JavaScript

Theme:
- Dark mode dan light mode
- Preferensi disimpan di localStorage

Image Dummy:
- placehold.co
```

Catatan:

```text
PHP hanya digunakan untuk struktur halaman dan persiapan backend.
Untuk tahap UI, PHP belum perlu koneksi ke database.
```

---

## 5. Struktur Folder

Dashboard berada di dalam subfolder proyek utama.

```text
digital-store/
├── index.html
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── data.js
│       └── app.js
│
└── dashboard/
    ├── index.php
    ├── products.php
    ├── categories.php
    ├── orders.php
    ├── testimonials.php
    ├── settings.php
    ├── assets/
    │   ├── css/
    │   │   └── dashboard.css
    │   └── js/
    │       ├── dashboard-data.js
    │       └── dashboard.js
    └── components/
        ├── sidebar.php
        ├── topbar.php
        └── layout.php
```

Penjelasan:

```text
index.html
Landing page katalog utama.

dashboard/index.php
Halaman utama dashboard.

dashboard/products.php
Halaman kelola produk.

dashboard/categories.php
Halaman kelola kategori.

dashboard/orders.php
Halaman daftar pesanan dummy.

dashboard/testimonials.php
Halaman testimoni dummy.

dashboard/settings.php
Halaman setting toko dummy.

dashboard/components/
Komponen layout dashboard agar tidak menulis ulang sidebar dan topbar.
```

---

## 6. Konsep UI

Dashboard harus terlihat seperti sistem admin custom, bukan template generik.

Karakter desain:

```text
- Bersih
- Ringan
- Modern
- Tidak terlalu ramai
- Minim teks panjang
- Banyak menggunakan card dan tabel
- Fokus ke data penting
- Desktop first
- Tetap nyaman di mobile
```

Prinsip copywriting UI:

```text
Gunakan label pendek.

Contoh benar:
- Produk
- Harga
- Stok
- Status
- Simpan
- Hapus
- Edit
- Tambah Produk

Hindari teks panjang seperti:
- Silakan masukkan nama lengkap produk digital yang ingin Anda tampilkan pada halaman katalog utama
```

---

## 7. Layout Utama Dashboard

Layout desktop:

```text
Sidebar kiri
Topbar atas
Content area utama
```

Struktur:

```text
+------------------------------------------------+
| Sidebar | Topbar                               |
|         |--------------------------------------|
|         | Content Area                         |
|         |                                      |
+------------------------------------------------+
```

Layout mobile:

```text
Topbar
Sidebar berubah menjadi drawer / menu slide
Content satu kolom
```

---

## 8. Sidebar

Sidebar digunakan untuk navigasi halaman dashboard.

Menu sidebar:

```text
Overview
Produk
Kategori
Pesanan
Testimoni
Setting
Kembali ke Katalog
```

Requirement:

```text
- Sidebar fixed di desktop.
- Sidebar bisa collapse di mobile.
- Menu aktif memiliki style berbeda.
- Terdapat link kembali ke landing page katalog utama.
```

Link penting:

```text
Kembali ke Katalog → ../index.html
Dashboard → /dashboard/
```

---

## 9. Topbar

Topbar menampilkan informasi singkat dan aksi cepat.

Elemen topbar:

```text
- Judul halaman aktif
- Search kecil dummy
- Tombol theme toggle
- Tombol profile/admin dummy
- Tombol hamburger di mobile
```

Contoh judul:

```text
Overview
Kelola Produk
Kategori
Pesanan
Testimoni
Setting Toko
```

---

## 10. Halaman Overview

Path:

```text
/dashboard/index.php
```

Fungsi:

Menampilkan ringkasan data toko secara singkat.

Komponen:

```text
- Total Produk
- Produk Aktif
- Pesanan Hari Ini
- Total Testimoni
- Produk Terlaris dummy
- Pesanan terbaru dummy
```

Contoh card statistik:

```text
Total Produk: 24
Produk Aktif: 18
Pesanan Hari Ini: 7
Rating Toko: 4.9
```

Bagian bawah:

```text
- Tabel pesanan terbaru
- List produk populer
```

Tidak perlu grafik kompleks untuk versi awal.

---

## 11. Halaman Produk

Path:

```text
/dashboard/products.php
```

Fungsi:

Menampilkan daftar produk dummy dan form UI untuk tambah/edit produk.

Komponen utama:

```text
- Header halaman
- Tombol Tambah Produk
- Search produk
- Filter kategori
- Tabel produk
- Modal tambah/edit produk
- Modal hapus produk
```

Kolom tabel produk:

```text
Produk
Kategori
Harga
Stok
Status
Aksi
```

Aksi:

```text
Edit
Hapus
Preview
```

Status produk:

```text
Aktif
Draft
Habis
```

Field form produk:

```text
Nama Produk
Kategori
Harga
Harga Coret
Stok
Status
Badge
Gambar URL
Deskripsi Singkat
Featured
```

Aturan teks:

```text
- Label pendek.
- Placeholder singkat.
- Tidak perlu helper text panjang.
```

Contoh placeholder:

```text
Nama Produk → Google AI Pro
Harga → 25000
Stok → 12
Gambar URL → https://placehold.co/600x400
Deskripsi → Lorem ipsum dolor sit amet.
```

Behavior UI-only:

```text
Tambah Produk:
- Membuka modal.
- Data bisa ditambahkan ke array sementara.
- Setelah refresh, data kembali ke dummy awal.

Edit Produk:
- Membuka modal dengan data produk.
- Perubahan hanya sementara di browser.

Hapus Produk:
- Menampilkan konfirmasi.
- Produk hilang sementara dari tabel.
```

---

## 12. Halaman Kategori

Path:

```text
/dashboard/categories.php
```

Fungsi:

Mengelola kategori produk dummy.

Komponen:

```text
- Tombol Tambah Kategori
- Tabel kategori
- Modal tambah/edit kategori
```

Kolom tabel:

```text
Nama
Slug
Jumlah Produk
Status
Aksi
```

Field form:

```text
Nama Kategori
Slug
Icon
Status
```

Kategori dummy:

```text
Akun Premium
Source Code
Template Website
Tools AI
Desain Digital
Produktivitas
```

---

## 13. Halaman Pesanan

Path:

```text
/dashboard/orders.php
```

Fungsi:

Menampilkan pesanan dummy dari landing page katalog.

Karena belum ada checkout asli, data pesanan hanya contoh.

Kolom tabel:

```text
Kode
Customer
Produk
Total
Status
Tanggal
Aksi
```

Status pesanan:

```text
Menunggu
Dibayar
Selesai
Batal
```

Aksi:

```text
Detail
Ubah Status
```

Detail pesanan dummy menampilkan:

```text
Kode Order
Nama Customer
Email
Nomor HP
Produk
Total
Metode Pembayaran
Status
Tanggal
```

Catatan:

```text
Halaman ini hanya preview UI untuk fitur order.
Belum menerima data dari landing page.
```

---

## 14. Halaman Testimoni

Path:

```text
/dashboard/testimonials.php
```

Fungsi:

Mengelola testimoni dummy yang nantinya bisa ditampilkan di landing page katalog.

Kolom tabel:

```text
Nama
Role
Rating
Status
Aksi
```

Field form:

```text
Nama
Role
Rating
Pesan
Status
```

Status:

```text
Tampil
Sembunyi
```

Pesan testimoni menggunakan lorem ipsum pendek.

---

## 15. Halaman Setting Toko

Path:

```text
/dashboard/settings.php
```

Fungsi:

Mengatur informasi toko secara dummy.

Section setting:

```text
Informasi Toko
Kontak
Tampilan
```

Field informasi toko:

```text
Nama Toko
Tagline
Deskripsi Singkat
```

Field kontak:

```text
WhatsApp
Email
Instagram
```

Field tampilan:

```text
Tema Default
Warna Accent
Produk per Baris
```

Catatan:

```text
Setting tidak tersimpan ke database.
Untuk tahap ini, setting hanya simulasi UI.
```

---

## 16. Dummy Data Dashboard

Data dashboard disimpan di:

```text
/dashboard/assets/js/dashboard-data.js
```

Isi data:

```js
const dashboardProducts = [
  {
    id: 1,
    name: "Google AI Pro 12 Bulan",
    category: "Tools AI",
    price: 25000,
    originalPrice: 50000,
    stock: 12,
    status: "Aktif",
    badge: "Best Seller",
    image: "https://placehold.co/600x400?text=Google+AI+Pro",
    description: "Lorem ipsum dolor sit amet.",
    featured: true
  },
  {
    id: 2,
    name: "ChatGPT Plus Sharing",
    category: "Akun Premium",
    price: 45000,
    originalPrice: 75000,
    stock: 8,
    status: "Aktif",
    badge: "Popular",
    image: "https://placehold.co/600x400?text=ChatGPT+Plus",
    description: "Lorem ipsum dolor sit amet.",
    featured: true
  },
  {
    id: 3,
    name: "Source Code Toko Online",
    category: "Source Code",
    price: 99000,
    originalPrice: 150000,
    stock: 20,
    status: "Aktif",
    badge: "Recommended",
    image: "https://placehold.co/600x400?text=Source+Code",
    description: "Lorem ipsum dolor sit amet.",
    featured: false
  }
];

const dashboardOrders = [
  {
    id: 1,
    code: "ORD-001",
    customer: "Raka Pratama",
    product: "Google AI Pro 12 Bulan",
    total: 25000,
    status: "Dibayar",
    date: "2026-06-24"
  },
  {
    id: 2,
    code: "ORD-002",
    customer: "Nadia Putri",
    product: "ChatGPT Plus Sharing",
    total: 45000,
    status: "Menunggu",
    date: "2026-06-24"
  }
];

const dashboardTestimonials = [
  {
    id: 1,
    name: "Raka Pratama",
    role: "Mahasiswa",
    rating: 5,
    message: "Lorem ipsum dolor sit amet.",
    status: "Tampil"
  },
  {
    id: 2,
    name: "Nadia Putri",
    role: "Freelancer",
    rating: 5,
    message: "Lorem ipsum dolor sit amet.",
    status: "Tampil"
  }
];
```

---

## 17. Theme Requirement

Dashboard harus memiliki dark mode dan light mode yang selaras dengan landing page katalog.

Requirement:

```text
- Tombol toggle tersedia di topbar.
- Theme tersimpan di localStorage.
- Default mengikuti preferensi user jika tersedia.
- Warna dashboard konsisten dengan landing page.
```

Light mode:

```text
Background: #F8FAFC
Sidebar: #FFFFFF
Card: #FFFFFF
Text: #0F172A
Muted: #64748B
Border: #E2E8F0
Accent: #2563EB
```

Dark mode:

```text
Background: #020617
Sidebar: #0F172A
Card: #111827
Text: #F8FAFC
Muted: #94A3B8
Border: #1E293B
Accent: #60A5FA
```

---

## 18. Responsive Requirement

Pendekatan:

```text
Desktop first.
Tetap responsive di tablet dan mobile.
```

Desktop:

```text
- Sidebar selalu terlihat.
- Tabel tampil penuh.
- Content area lebar.
- Card statistik 4 kolom.
```

Tablet:

```text
- Card statistik 2 kolom.
- Tabel tetap bisa scroll horizontal.
- Sidebar bisa diperkecil.
```

Mobile:

```text
- Sidebar menjadi drawer.
- Card statistik 1 kolom.
- Tabel scroll horizontal.
- Tombol aksi tetap mudah diklik.
```

Breakpoint:

```text
Desktop: 1024px ke atas
Tablet: 768px - 1023px
Mobile: 320px - 767px
```

---

## 19. UX Writing

Dashboard harus minim teks panjang.

Prinsip:

```text
- Gunakan label pendek.
- Hindari deskripsi berlebihan.
- Gunakan icon secukupnya.
- Fokus ke aksi.
- Jangan membuat user membaca paragraf panjang.
```

Contoh baik:

```text
Tambah Produk
Simpan
Batal
Edit
Hapus
Cari produk
Pilih kategori
```

Contoh yang harus dihindari:

```text
Silakan isi semua informasi produk digital secara lengkap agar dapat ditampilkan pada halaman katalog utama dengan benar.
```

Alternatif lebih baik:

```text
Lengkapi data produk.
```

---

## 20. Interaksi UI

### 20.1 Sidebar Mobile

```text
User klik tombol menu
→ Sidebar terbuka
→ User pilih menu
→ Sidebar tertutup otomatis
```

### 20.2 Theme Toggle

```text
User klik toggle tema
→ Class dark berubah
→ localStorage diperbarui
→ Tampilan berubah langsung
```

### 20.3 Search Produk

```text
User mengetik keyword
→ Tabel produk difilter
→ Jika kosong, tampilkan empty state
```

### 20.4 Tambah Produk

```text
User klik Tambah Produk
→ Modal terbuka
→ User isi form
→ Klik Simpan
→ Produk muncul sementara di tabel
```

### 20.5 Edit Produk

```text
User klik Edit
→ Modal terbuka
→ Data produk tampil
→ User ubah data
→ Klik Simpan
→ Data tabel berubah sementara
```

### 20.6 Hapus Produk

```text
User klik Hapus
→ Modal konfirmasi muncul
→ Klik Hapus
→ Produk hilang sementara dari tabel
```

---

## 21. Komponen UI

Komponen yang perlu dibuat:

```text
- Dashboard Layout
- Sidebar
- Topbar
- Stat Card
- Data Table
- Search Input
- Filter Select
- Action Button
- Status Badge
- Product Modal
- Category Modal
- Order Detail Modal
- Delete Confirmation Modal
- Empty State
- Theme Toggle
- Mobile Sidebar Drawer
```

---

## 22. Status Badge

Badge harus sederhana dan mudah dibaca.

Produk:

```text
Aktif
Draft
Habis
```

Pesanan:

```text
Menunggu
Dibayar
Selesai
Batal
```

Testimoni:

```text
Tampil
Sembunyi
```

Warna badge:

```text
Aktif / Dibayar / Selesai / Tampil → Hijau
Menunggu / Draft → Kuning
Habis / Batal / Sembunyi → Abu-abu atau Merah
```

---

## 23. Performance Requirement

Dashboard harus ringan.

Requirement:

```text
- Tidak menggunakan framework berat.
- Tidak menggunakan chart library untuk versi awal.
- Tidak menggunakan animasi kompleks.
- Data dummy maksimal 50 item tetap lancar.
- JavaScript modular sederhana.
- Gambar menggunakan lazy loading.
- Tailwind boleh via CDN untuk prototype.
```

Untuk production:

```text
Tailwind sebaiknya di-build agar CSS lebih kecil.
```

---

## 24. Accessibility Requirement

Requirement:

```text
- Button memiliki teks jelas.
- Input memiliki label.
- Modal bisa ditutup.
- Kontras warna cukup.
- Tabel bisa dibaca di dark dan light mode.
- Tombol disabled terlihat jelas.
- Sidebar mobile bisa ditutup.
```

---

## 25. Acceptance Criteria

Dashboard dianggap selesai jika:

```text
1. Dashboard dapat dibuka di /dashboard/.
2. Dashboard jelas sebagai admin untuk landing page katalog di path utama.
3. Sidebar dan topbar tampil baik.
4. Dark mode dan light mode berjalan.
5. Preferensi tema tersimpan di localStorage.
6. Halaman overview tampil dengan data dummy.
7. Halaman produk tampil dengan tabel dummy.
8. Modal tambah/edit produk tampil.
9. Search produk berjalan secara UI.
10. Hapus produk berjalan secara UI sementara.
11. Halaman kategori tampil.
12. Halaman pesanan tampil.
13. Halaman testimoni tampil.
14. Halaman setting toko tampil.
15. Layout desktop first.
16. Mobile tetap usable.
17. Tidak ada teks panjang yang membingungkan.
18. Tidak ada koneksi database.
19. Tidak ada backend CRUD aktif.
20. Struktur folder siap dikembangkan ke PHP backend.
```

---

## 26. Prioritas Pengerjaan

Urutan pengerjaan:

```text
1. Buat folder /dashboard.
2. Buat layout utama dashboard.
3. Buat sidebar dan topbar.
4. Buat theme toggle.
5. Buat halaman overview.
6. Buat dummy data JavaScript.
7. Buat halaman produk dan tabel.
8. Buat modal tambah/edit produk.
9. Buat search dan filter produk.
10. Buat halaman kategori.
11. Buat halaman pesanan.
12. Buat halaman testimoni.
13. Buat halaman setting.
14. Rapikan responsive tablet dan mobile.
15. Cek dark mode dan light mode.
```

---

## 27. Future Development

Setelah UI dashboard selesai, tahap berikutnya:

```text
1. Buat database MySQL.
2. Buat koneksi PHP ke database.
3. Buat autentikasi admin.
4. Buat CRUD produk.
5. Buat CRUD kategori.
6. Hubungkan katalog utama dengan database.
7. Hubungkan dashboard dengan database.
8. Buat checkout.
9. Buat manajemen order.
10. Buat upload gambar.
11. Buat setting toko dinamis.
12. Buat deployment ke shared hosting.
```

---

## 28. Kesimpulan

Dashboard ini adalah subfolder admin untuk landing page katalog produk digital yang berada di path utama proyek. Untuk versi awal, dashboard hanya berfungsi sebagai UI prototype menggunakan HTML, Tailwind CSS, JavaScript, dan PHP sebagai struktur halaman.

Fokus utama bukan membuat sistem backend, melainkan membuat tampilan dashboard yang rapi, sederhana, desktop first, responsive, cepat diakses, dan siap dikembangkan menjadi dashboard admin sungguhan.
