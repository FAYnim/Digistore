# PRD / Plan — Integrasi Landing Page Katalog dengan Database

## 1. Ringkasan

Tahap ini berfokus pada menghubungkan **landing page katalog produk digital** dengan database yang sudah dikelola melalui dashboard.

Sebelumnya:

```text id="4gvd21"
/
Landing page katalog masih memakai dummy data JavaScript.

/dashboard/
Dashboard admin sudah dirancang untuk mengelola data ke database.
```

Pada tahap ini, landing page tidak lagi menggunakan dummy data statis. Produk, kategori, testimoni, dan setting toko akan diambil dari database melalui API publik.

Relasi sistem:

```text id="cod6u5"
Dashboard
→ Mengelola data ke database

Database
→ Menyimpan produk, kategori, testimoni, setting toko

Landing Page
→ Membaca data dari database melalui API publik
```

Landing page hanya membaca data. Untuk tahap ini, landing page belum membuat order, checkout, atau pembayaran.

---

## 2. Tujuan

Tujuan utama tahap ini:

```text id="63mp7l"
1. Menghubungkan landing page katalog ke database.
2. Menampilkan produk dari tabel products.
3. Menampilkan kategori dari tabel categories.
4. Menampilkan produk unggulan berdasarkan is_featured.
5. Menampilkan testimoni dari tabel testimonials.
6. Menampilkan informasi toko dari tabel store_settings.
7. Membuat API publik read-only.
8. Menghapus ketergantungan landing page dari dummy data JavaScript.
9. Menjaga landing page tetap ringan, cepat, dan responsive.
```

---

## 3. Scope Tahap Ini

### Termasuk

```text id="iw2l7o"
- Public API untuk landing page
- GET produk aktif
- GET kategori aktif
- GET produk unggulan
- GET testimoni visible
- GET setting toko
- Search produk dari frontend
- Filter kategori dari frontend
- Sorting produk dari frontend atau API
- Loading state
- Empty state
- Error state
- Fallback gambar produk
```

### Tidak Termasuk

```text id="y8p24r"
- Checkout
- Keranjang belanja permanen
- Membuat order
- Upload bukti pembayaran
- Payment gateway
- Login user
- Detail produk penuh
- Auto delivery produk
- Email notification
- WhatsApp notification otomatis
```

Catatan:

```text id="gspkiz"
Landing page hanya menjadi katalog publik.
Transaksi akan dibuat pada PRD/plan berikutnya.
```

---

## 4. Tech Stack

```text id="hnm2uq"
Frontend:
- HTML
- Tailwind CSS
- JavaScript Vanilla
- Fetch API

Backend:
- PHP Native

Database:
- MySQL / MariaDB

Response:
- JSON

Hosting:
- Shared hosting / cPanel / Apache
```

---

## 5. Struktur Folder

Struktur folder setelah landing page mulai menggunakan API:

```text id="zm4hkb"
digital-store/
├── index.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── app.js
│       └── api.js
│
├── api/
│   ├── products.php
│   ├── categories.php
│   ├── testimonials.php
│   └── settings.php
│
├── dashboard/
│   ├── index.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   ├── testimonials.php
│   ├── settings.php
│   └── api/
│       ├── products.php
│       ├── categories.php
│       ├── orders.php
│       ├── testimonials.php
│       ├── settings.php
│       └── stats.php
│
├── config/
│   ├── database.php
│   └── response.php
│
└── database/
    ├── schema.sql
    └── seed.sql
```

Perubahan penting:

```text id="qa6v3q"
index.html berubah menjadi index.php.
```

Alasan:

```text id="hubc8q"
Agar lebih mudah digunakan di shared hosting PHP.
Namun data tetap diambil menggunakan JavaScript fetch dari API.
```

---

## 6. Perbedaan API Dashboard dan API Publik

### API Dashboard

Path:

```text id="7um6cl"
/dashboard/api/
```

Fungsi:

```text id="i67o8o"
Untuk admin.
Bisa GET, POST, PUT, DELETE.
Harus diproteksi login admin.
```

Contoh:

```text id="n7fnfh"
/dashboard/api/products.php
/dashboard/api/categories.php
/dashboard/api/settings.php
```

### API Publik Landing Page

Path:

```text id="r1h80b"
/api/
```

Fungsi:

```text id="0ezhog"
Untuk landing page.
Hanya read-only.
Tidak perlu login.
Hanya menampilkan data yang boleh dilihat publik.
```

Contoh:

```text id="2klfxw"
/api/products.php
/api/categories.php
/api/testimonials.php
/api/settings.php
```

Aturan:

```text id="ygze0g"
API publik tidak boleh menampilkan produk draft.
API publik tidak boleh menampilkan kategori inactive.
API publik tidak boleh menampilkan testimoni hidden.
API publik tidak boleh menerima POST, PUT, DELETE.
```

---

## 7. Data yang Dipakai Landing Page

Landing page mengambil data dari tabel berikut:

```text id="o1xvn7"
categories
products
testimonials
store_settings
```

Tabel yang belum dipakai:

```text id="ik9jbo"
orders
order_items
admin_users
```

Alasan:

```text id="3c1bhp"
Tahap ini belum membuat checkout atau sistem order.
```

---

# 8. Mapping Database ke UI Landing Page

## 8.1 Products

Tabel:

```text id="e0jpbg"
products
```

Kolom yang dipakai landing page:

```text id="xs196f"
id
category_id
category_name
name
slug
description
price
original_price
stock
image_url
badge
status
is_featured
sold_count
rating
created_at
```

Aturan tampil:

```text id="xe9qle"
status = active saja yang tampil.
Produk draft tidak tampil.
Produk out_of_stock boleh tampil, tetapi tombol beli disabled.
```

Mapping ke product card:

```text id="653p8i"
name           → Nama produk
category_name  → Badge kategori
description    → Deskripsi singkat
price          → Harga utama
original_price → Harga coret
stock          → Status stok
image_url      → Gambar produk
badge          → Badge promo
rating         → Rating
sold_count     → Jumlah terjual
is_featured    → Produk unggulan
```

---

## 8.2 Categories

Tabel:

```text id="6lfvrq"
categories
```

Kolom yang dipakai:

```text id="yqjmme"
id
name
slug
icon
status
sort_order
product_count
```

Aturan tampil:

```text id="o2cdz4"
status = active saja yang tampil.
Kategori inactive tidak tampil.
Kategori diurutkan berdasarkan sort_order.
```

Mapping ke UI:

```text id="3tfi8f"
name          → Nama tombol kategori
icon          → Icon kategori
product_count → Jumlah produk
slug          → Filter kategori
```

---

## 8.3 Testimonials

Tabel:

```text id="bo8s13"
testimonials
```

Kolom yang dipakai:

```text id="w7y90g"
id
name
role
message
rating
status
created_at
```

Aturan tampil:

```text id="abxtz0"
status = visible saja yang tampil.
Testimoni hidden tidak tampil.
```

Mapping ke UI:

```text id="bq3han"
name    → Nama pelanggan
role    → Role pelanggan
message → Isi testimoni
rating  → Rating bintang
```

---

## 8.4 Store Settings

Tabel:

```text id="8pjr0a"
store_settings
```

Setting yang dipakai landing page:

```text id="woa5oe"
store_name
store_tagline
store_description
store_whatsapp
store_email
store_instagram
default_theme
accent_color
```

Mapping ke UI:

```text id="s8mvmv"
store_name        → Logo / nama toko
store_tagline     → Hero headline kecil / subtitle
store_description → Hero description / footer description
store_whatsapp    → CTA WhatsApp
store_email       → Footer contact
store_instagram   → Footer social link
default_theme     → Tema awal
accent_color      → Warna aksen
```

---

# 9. Public API Contract

Semua API publik menggunakan format JSON konsisten.

## 9.1 Format Response Sukses

```json id="ry64id"
{
  "success": true,
  "message": "Data berhasil dimuat",
  "data": []
}
```

## 9.2 Format Response Error

```json id="iz8ufx"
{
  "success": false,
  "message": "Gagal memuat data",
  "data": null,
  "errors": null
}
```

---

# 10. Endpoint API Publik

## 10.1 Products API

File:

```text id="6fyk5a"
/api/products.php
```

Method:

```text id="f28f2w"
GET
```

Fungsi:

```text id="51gb6w"
Mengambil produk aktif untuk landing page katalog.
```

Query opsional:

```text id="klh73b"
search
category
featured
sort
limit
```

Contoh endpoint:

```text id="e95t6d"
/api/products.php
/api/products.php?featured=true
/api/products.php?category=tools-ai
/api/products.php?search=google
/api/products.php?sort=price_low
/api/products.php?limit=8
```

Query behavior:

```text id="9qoah7"
search    → cari berdasarkan nama/deskripsi produk
category  → filter berdasarkan slug kategori
featured  → ambil produk unggulan
sort      → urutan produk
limit     → batas jumlah produk
```

Pilihan sort:

```text id="ve6qd9"
newest
price_low
price_high
rating_high
sold_high
```

SQL logic:

```text id="ku0wwl"
Ambil produk dari products.
LEFT JOIN categories.
Produk wajib status active atau out_of_stock.
Kategori wajib active jika ada.
```

Produk yang tampil:

```text id="vxivxs"
products.status IN ('active', 'out_of_stock')
```

Response contoh:

```json id="y1mlf7"
{
  "success": true,
  "message": "Produk berhasil dimuat",
  "data": [
    {
      "id": 1,
      "category_id": 4,
      "category_name": "Tools AI",
      "category_slug": "tools-ai",
      "name": "Google AI Pro 12 Bulan",
      "slug": "google-ai-pro-12-bulan",
      "description": "Lorem ipsum dolor sit amet.",
      "price": 25000,
      "original_price": 50000,
      "stock": 12,
      "image_url": "https://placehold.co/600x400?text=Google+AI+Pro",
      "badge": "Best Seller",
      "status": "active",
      "is_featured": true,
      "sold_count": 320,
      "rating": 4.9
    }
  ]
}
```

---

## 10.2 Categories API

File:

```text id="7f9yn4"
/api/categories.php
```

Method:

```text id="odxeeg"
GET
```

Fungsi:

```text id="olkl0q"
Mengambil kategori aktif untuk filter katalog.
```

Response contoh:

```json id="6nwnyv"
{
  "success": true,
  "message": "Kategori berhasil dimuat",
  "data": [
    {
      "id": 1,
      "name": "Akun Premium",
      "slug": "akun-premium",
      "icon": "star",
      "status": "active",
      "sort_order": 1,
      "product_count": 8
    }
  ]
}
```

SQL logic:

```text id="xd3v7q"
Ambil categories dengan status active.
Hitung jumlah produk aktif di setiap kategori.
Urutkan berdasarkan sort_order ASC.
```

---

## 10.3 Featured Products API

Tidak wajib dibuat sebagai file terpisah.

Gunakan:

```text id="9y1qv5"
/api/products.php?featured=true&limit=4
```

Fungsi:

```text id="bwmfno"
Menampilkan produk unggulan di section Featured Products.
```

Aturan:

```text id="pkeky2"
is_featured = 1
status = active
limit default = 4
```

---

## 10.4 Testimonials API

File:

```text id="4md974"
/api/testimonials.php
```

Method:

```text id="iktvp6"
GET
```

Fungsi:

```text id="jcvw2u"
Mengambil testimoni visible untuk landing page.
```

Query opsional:

```text id="33pvw9"
limit
```

Contoh:

```text id="8v2ysy"
/api/testimonials.php?limit=3
```

Response contoh:

```json id="7un2ib"
{
  "success": true,
  "message": "Testimoni berhasil dimuat",
  "data": [
    {
      "id": 1,
      "name": "Raka Pratama",
      "role": "Mahasiswa",
      "message": "Lorem ipsum dolor sit amet.",
      "rating": 5
    }
  ]
}
```

SQL logic:

```text id="1sdwit"
Ambil testimonials dengan status visible.
Urutkan dari terbaru.
```

---

## 10.5 Settings API

File:

```text id="xcm22q"
/api/settings.php
```

Method:

```text id="p0j3fi"
GET
```

Fungsi:

```text id="eaz3jc"
Mengambil informasi toko untuk landing page.
```

Response contoh:

```json id="4wh9gv"
{
  "success": true,
  "message": "Setting toko berhasil dimuat",
  "data": {
    "store_name": "DigiStore",
    "store_tagline": "Produk Digital Premium",
    "store_description": "Katalog produk digital sederhana.",
    "store_whatsapp": "6281234567890",
    "store_email": "admin@example.com",
    "store_instagram": "digistore",
    "default_theme": "light",
    "accent_color": "blue"
  }
}
```

SQL logic:

```text id="uzm7sz"
Ambil semua setting dari store_settings.
Ubah key-value rows menjadi object JSON.
```

---

# 11. Frontend Landing Page Behavior

## 11.1 Saat Halaman Dibuka

Flow:

```text id="hziagw"
User membuka /
→ Load setting toko
→ Terapkan nama toko, tagline, deskripsi, kontak, dan tema default
→ Load kategori aktif
→ Load produk unggulan
→ Load semua produk aktif
→ Load testimoni visible
→ Render semua section
```

---

## 11.2 Search Produk

Ada dua opsi:

### Opsi A — Search Client-side

```text id="6fky23"
Produk diambil sekali dari API.
Search dilakukan di JavaScript.
```

Kelebihan:

```text id="ul4w51"
Cepat untuk data sedikit.
Lebih ringan di server.
Cocok untuk MVP.
```

Kekurangan:

```text id="n4w2ow"
Kurang ideal jika produk sudah banyak.
```

### Opsi B — Search Server-side

```text id="5c9nu1"
Setiap search memanggil /api/products.php?search=keyword
```

Kelebihan:

```text id="v6qqj6"
Lebih cocok untuk data besar.
```

Kekurangan:

```text id="tadva6"
Lebih sering request ke server.
```

Rekomendasi MVP:

```text id="dyzubw"
Gunakan client-side search dulu.
Jika produk lebih dari 100 item, pindah ke server-side search.
```

---

## 11.3 Filter Kategori

Rekomendasi MVP:

```text id="9i3cqg"
Gunakan client-side filter.
```

Flow:

```text id="54angd"
User klik kategori
→ JavaScript memfilter produk berdasarkan category_slug
→ Product grid diperbarui
```

Alternatif server-side:

```text id="hf85w6"
/api/products.php?category=tools-ai
```

---

## 11.4 Sorting Produk

Sorting bisa dilakukan client-side.

Pilihan sorting:

```text id="1ta3eq"
Terbaru
Harga Terendah
Harga Tertinggi
Rating Tertinggi
Terlaris
```

Mapping:

```text id="867bi5"
Terbaru         → created_at DESC
Harga Terendah  → price ASC
Harga Tertinggi → price DESC
Rating Tertinggi → rating DESC
Terlaris        → sold_count DESC
```

---

## 11.5 Produk Habis

Jika:

```text id="1d7xko"
status = out_of_stock
atau
stock <= 0
```

Maka UI:

```text id="s1c4hr"
- Badge: Habis
- Tombol beli disabled
- Card tetap tampil
```

---

## 11.6 Fallback Gambar

Jika `image_url` kosong:

```text id="6yv1le"
Gunakan fallback:
https://placehold.co/600x400?text=No+Image
```

Jika gambar gagal dimuat:

```text id="2kwzp0"
Ganti src ke fallback image.
```

---

# 12. Komponen Landing Page yang Terhubung Database

## 12.1 Navbar

Data dari settings:

```text id="0gf59h"
store_name
store_instagram
store_whatsapp
```

Elemen:

```text id="pvrpvx"
Logo toko
Menu
Theme toggle
Cart dummy / CTA
```

---

## 12.2 Hero Section

Data dari settings:

```text id="r47unn"
store_name
store_tagline
store_description
```

Contoh output:

```text id="vs9u2j"
Headline:
Produk Digital Premium

Description:
Katalog produk digital sederhana.
```

CTA:

```text id="fduxt5"
Lihat Produk
Hubungi Admin
```

Tombol Hubungi Admin memakai:

```text id="t9s32m"
store_whatsapp
```

---

## 12.3 Category Section

Data dari:

```text id="r2d8d3"
/api/categories.php
```

Elemen:

```text id="ki6vdj"
Semua Produk
Kategori dari database
Jumlah produk per kategori
```

---

## 12.4 Featured Products Section

Data dari:

```text id="83hcok"
/api/products.php?featured=true&limit=4
```

Elemen:

```text id="ckyxz5"
Produk unggulan dari database
Badge featured
Harga
Rating
Tombol aksi
```

---

## 12.5 Product Catalog Section

Data dari:

```text id="rcjuni"
/api/products.php
```

Elemen:

```text id="ycavpl"
Search
Filter kategori
Sorting
Product grid
Empty state
```

---

## 12.6 Testimonials Section

Data dari:

```text id="k0nvmz"
/api/testimonials.php?limit=3
```

Elemen:

```text id="x4yzgt"
Nama
Role
Pesan
Rating
```

---

## 12.7 Footer

Data dari settings:

```text id="froa2h"
store_name
store_description
store_email
store_instagram
store_whatsapp
```

---

# 13. File JavaScript yang Dibutuhkan

## 13.1 assets/js/api.js

Fungsi:

```text id="jtc4t2"
Menyimpan helper fetch API.
```

Contoh:

```js id="20br69"
const API_BASE = "/api";

async function apiGet(endpoint) {
  try {
    const response = await fetch(`${API_BASE}${endpoint}`);

    if (!response.ok) {
      throw new Error("Request gagal");
    }

    return await response.json();
  } catch (error) {
    return {
      success: false,
      message: error.message,
      data: null
    };
  }
}
```

---

## 13.2 assets/js/app.js

Fungsi:

```text id="bsg07l"
Mengatur render UI landing page.
```

Isi utama:

```text id="334rfn"
- Load settings
- Load categories
- Load products
- Load featured products
- Load testimonials
- Render navbar
- Render hero
- Render categories
- Render products
- Render featured products
- Render testimonials
- Handle search
- Handle category filter
- Handle sorting
- Handle theme toggle
```

State frontend:

```js id="3onf55"
let allProducts = [];
let allCategories = [];
let currentCategory = "all";
let currentSearch = "";
let currentSort = "newest";
```

---

# 14. Loading, Empty, dan Error State

## 14.1 Loading State

Saat data belum selesai dimuat:

```text id="9h40q8"
Tampilkan skeleton card atau teks singkat:
Memuat produk...
```

Jangan gunakan spinner besar yang berlebihan.

---

## 14.2 Empty State

Jika tidak ada produk:

```text id="spoh1n"
Produk tidak ditemukan.
```

Tambahan singkat:

```text id="l92nq4"
Coba kata kunci lain.
```

---

## 14.3 Error State

Jika API gagal:

```text id="71zriz"
Gagal memuat data.
```

Tombol:

```text id="im8vsw"
Coba Lagi
```

Catatan:

```text id="3g9ej4"
Jangan tampilkan error teknis database ke user.
```

---

# 15. Theme Integration

Theme landing page harus tetap sama dengan dashboard.

Sumber tema:

```text id="a3bfrp"
1. localStorage user
2. store_settings.default_theme
3. fallback light
```

Prioritas:

```text id="95q1ee"
Jika user sudah pernah memilih tema, gunakan localStorage.
Jika belum, gunakan default_theme dari database.
Jika setting kosong, gunakan light.
```

Flow:

```text id="28xhvn"
Load settings
→ Cek localStorage theme
→ Terapkan theme
→ User bisa toggle
→ Simpan ke localStorage
```

---

# 16. Performance Requirement

Landing page harus tetap cepat meskipun terhubung API.

Requirement:

```text id="5dikj4"
- API response ringan.
- Jangan ambil data yang tidak perlu.
- Produk bisa dibatasi jika jumlah terlalu banyak.
- Gambar gunakan loading lazy.
- Fetch dilakukan paralel jika memungkinkan.
- Hindari library berat.
- Gunakan JavaScript Vanilla.
```

Rekomendasi:

```js id="1beifm"
const [settings, categories, products, featured, testimonials] = await Promise.all([
  apiGet("/settings.php"),
  apiGet("/categories.php"),
  apiGet("/products.php"),
  apiGet("/products.php?featured=true&limit=4"),
  apiGet("/testimonials.php?limit=3")
]);
```

Gambar:

```html id="d0220d"
<img loading="lazy" src="..." alt="..." />
```

---

# 17. Security Requirement API Publik

Walaupun API publik hanya read-only, tetap perlu aman.

Requirement:

```text id="e9ut2m"
- Hanya izinkan method GET.
- Gunakan prepared statement.
- Validasi query parameter.
- Batasi limit maksimal.
- Jangan tampilkan produk draft.
- Jangan tampilkan data admin.
- Jangan tampilkan error database.
```

Limit:

```text id="gmapbs"
Default limit produk: semua untuk MVP
Maksimal limit jika dipakai: 50
```

---

# 18. SQL Logic Penting

## 18.1 Ambil Produk Publik

```sql id="d2rknl"
SELECT 
  p.id,
  p.category_id,
  c.name AS category_name,
  c.slug AS category_slug,
  p.name,
  p.slug,
  p.description,
  p.price,
  p.original_price,
  p.stock,
  p.image_url,
  p.badge,
  p.status,
  p.is_featured,
  p.sold_count,
  p.rating,
  p.created_at
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status IN ('active', 'out_of_stock')
AND (c.status = 'active' OR p.category_id IS NULL)
ORDER BY p.created_at DESC;
```

---

## 18.2 Ambil Produk Unggulan

```sql id="r7yeu0"
SELECT 
  p.id,
  p.category_id,
  c.name AS category_name,
  c.slug AS category_slug,
  p.name,
  p.slug,
  p.description,
  p.price,
  p.original_price,
  p.stock,
  p.image_url,
  p.badge,
  p.status,
  p.is_featured,
  p.sold_count,
  p.rating
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active'
AND p.is_featured = 1
AND (c.status = 'active' OR p.category_id IS NULL)
ORDER BY p.created_at DESC
LIMIT 4;
```

---

## 18.3 Ambil Kategori Publik

```sql id="q3uyxj"
SELECT 
  c.id,
  c.name,
  c.slug,
  c.icon,
  c.status,
  c.sort_order,
  COUNT(p.id) AS product_count
FROM categories c
LEFT JOIN products p 
  ON p.category_id = c.id 
  AND p.status IN ('active', 'out_of_stock')
WHERE c.status = 'active'
GROUP BY c.id
ORDER BY c.sort_order ASC, c.name ASC;
```

---

## 18.4 Ambil Testimoni Publik

```sql id="67c3ml"
SELECT 
  id,
  name,
  role,
  message,
  rating
FROM testimonials
WHERE status = 'visible'
ORDER BY created_at DESC
LIMIT 3;
```

---

# 19. API Implementation Priority

Urutan pengerjaan:

```text id="yfc0cv"
1. Pindahkan config database ke folder global /config.
2. Buat /api/settings.php.
3. Buat /api/categories.php.
4. Buat /api/products.php.
5. Buat /api/testimonials.php.
6. Buat assets/js/api.js.
7. Update app.js agar fetch data dari API.
8. Render settings ke navbar, hero, footer.
9. Render kategori dari API.
10. Render produk dari API.
11. Render featured products dari API.
12. Render testimoni dari API.
13. Tambahkan loading, empty, error state.
14. Tes light/dark theme.
15. Tes mobile dan desktop.
```

---

# 20. Acceptance Criteria

Tahap ini selesai jika:

```text id="3r8m39"
1. Landing page bisa dibuka di path utama /.
2. Landing page mengambil produk dari /api/products.php.
3. Produk draft tidak tampil di landing page.
4. Produk active tampil di katalog.
5. Produk out_of_stock tampil dengan tombol disabled.
6. Kategori diambil dari /api/categories.php.
7. Kategori inactive tidak tampil.
8. Produk unggulan diambil dari is_featured.
9. Testimoni visible tampil.
10. Testimoni hidden tidak tampil.
11. Nama toko, tagline, deskripsi, dan kontak diambil dari store_settings.
12. Search produk tetap berjalan.
13. Filter kategori tetap berjalan.
14. Sorting produk tetap berjalan.
15. Loading state tampil saat fetch.
16. Empty state tampil jika data kosong.
17. Error state tampil jika API gagal.
18. Theme dark/light tetap berjalan.
19. Preferensi theme tersimpan di localStorage.
20. Landing page tetap responsive dan cepat.
```

---

# 21. Catatan Implementasi

Untuk MVP, lebih baik landing page mengambil semua produk aktif sekali dari API, lalu search/filter/sort dilakukan di frontend.

Alasan:

```text id="wdtgh2"
- Lebih sederhana.
- Lebih cepat dibuat.
- Server tidak sering menerima request.
- Cocok jika jumlah produk masih sedikit.
```

Jika produk sudah banyak, ubah ke server-side:

```text id="1p32yy"
/api/products.php?search=keyword&category=tools-ai&sort=price_low
```

---

# 22. Kesimpulan

Tahap ini mengubah landing page dari katalog dummy menjadi katalog dinamis yang membaca data dari database.

Dashboard tetap menjadi tempat mengelola data. Landing page hanya membaca data publik dari API read-only.

Fokus utama tahap ini adalah memastikan data yang tampil di landing page sama dengan data yang sudah dikelola dari dashboard, tanpa langsung masuk ke checkout atau order.
