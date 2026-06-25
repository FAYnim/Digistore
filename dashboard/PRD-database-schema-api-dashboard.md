# PRD / Plan — Database Schema & API Dashboard Katalog Produk Digital

## 1. Ringkasan

Tahap ini berfokus pada pembuatan **database schema** dan **API backend PHP** untuk menghubungkan halaman dashboard dengan database.

Dashboard sebelumnya masih menggunakan dummy data dari JavaScript. Pada tahap ini, data dashboard mulai dipindahkan ke database agar halaman admin bisa melakukan operasi nyata seperti menambah produk, mengedit produk, menghapus produk, mengelola kategori, mengelola testimoni, melihat pesanan, dan mengubah setting toko.

Scope ini hanya untuk kebutuhan dashboard.

```text
/dashboard/
Dashboard admin

/dashboard/api/
API khusus dashboard

Database:
Menyimpan data yang digunakan oleh dashboard
```

Landing page katalog utama belum ikut terhubung pada tahap ini. Koneksi landing page ke database akan dibuat dalam PRD/plan terpisah.

---

## 2. Tujuan

Tujuan utama tahap ini:

```text
1. Membuat struktur database MySQL.
2. Membuat API PHP Native untuk dashboard.
3. Mengganti dummy data dashboard menjadi data dari database.
4. Membuat fitur CRUD produk.
5. Membuat fitur CRUD kategori.
6. Membuat fitur CRUD testimoni.
7. Membuat fitur update status pesanan.
8. Membuat fitur update setting toko.
9. Menyiapkan struktur backend agar mudah dikembangkan ke landing page nanti.
```

---

## 3. Tech Stack

```text
Frontend Dashboard:
- HTML
- Tailwind CSS
- JavaScript Vanilla
- Fetch API

Backend:
- PHP Native

Database:
- MySQL / MariaDB

Format Response API:
- JSON

Server:
- Shared hosting / cPanel
- Apache
```

Catatan:

```text
Tidak menggunakan framework PHP.
Tidak menggunakan Node.js.
Tidak menggunakan React/Vue.
Tidak menggunakan ORM.
```

---

## 4. Scope Tahap Ini

### Termasuk

```text
- Schema database
- Koneksi database PHP
- API dashboard
- CRUD produk
- CRUD kategori
- CRUD testimoni
- Read pesanan
- Update status pesanan
- Read/update setting toko
- Endpoint statistik dashboard
- Validasi input dasar
- Response JSON konsisten
- Error handling sederhana
```

### Tidak Termasuk

```text
- API untuk landing page publik
- Checkout publik
- Payment gateway
- Upload gambar file asli
- Auto delivery produk digital
- Email notification
- WhatsApp notification
- Role multi-admin kompleks
- Export laporan
```

---

## 5. Struktur Folder Backend

Struktur folder yang disarankan:

```text
digital-store/
├── index.html
├── dashboard/
│   ├── index.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   ├── testimonials.php
│   ├── settings.php
│   │
│   ├── api/
│   │   ├── stats.php
│   │   ├── products.php
│   │   ├── categories.php
│   │   ├── orders.php
│   │   ├── testimonials.php
│   │   └── settings.php
│   │
│   ├── config/
│   │   ├── database.php
│   │   └── response.php
│   │
│   └── assets/
│       └── js/
│           ├── dashboard.js
│           ├── products.js
│           ├── categories.js
│           ├── orders.js
│           ├── testimonials.js
│           └── settings.js
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
└── .env
```

---

## 6. Database yang Dibutuhkan

Database utama:

```text
digital_store
```

Tabel inti:

```text
1. admin_users
2. categories
3. products
4. orders
5. order_items
6. testimonials
7. store_settings
```

Catatan:

```text
orders dan order_items tetap dibuat meskipun checkout landing page belum ada.
Untuk tahap dashboard, datanya bisa dimasukkan lewat seed dummy atau manual dari database.
```

---

# 7. Schema Database

## 7.1 Tabel admin_users

Fungsi:

```text
Menyimpan akun admin dashboard.
```

Kolom:

```text
id
username
password
name
status
created_at
updated_at
```

Struktur:

```sql
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Catatan:

```text
Password wajib disimpan menggunakan password_hash().
Jangan simpan password plain text.
```

---

## 7.2 Tabel categories

Fungsi:

```text
Menyimpan kategori produk.
```

Kolom:

```text
id
name
slug
icon
status
sort_order
created_at
updated_at
```

Struktur:

```sql
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  icon VARCHAR(50) DEFAULT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Contoh data:

```text
Akun Premium
Source Code
Template Website
Tools AI
Desain Digital
Produktivitas
```

---

## 7.3 Tabel products

Fungsi:

```text
Menyimpan data produk digital.
```

Kolom:

```text
id
category_id
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
updated_at
```

Struktur:

```sql
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  description TEXT,
  price INT NOT NULL DEFAULT 0,
  original_price INT DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  image_url VARCHAR(255) DEFAULT NULL,
  badge VARCHAR(50) DEFAULT NULL,
  status ENUM('active', 'draft', 'out_of_stock') DEFAULT 'draft',
  is_featured TINYINT(1) DEFAULT 0,
  sold_count INT DEFAULT 0,
  rating DECIMAL(2,1) DEFAULT 0.0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
);
```

Catatan:

```text
image_url menggunakan URL biasa dulu.
Belum ada upload gambar.
Untuk dummy image, bisa pakai placehold.co.
```

Contoh image:

```text
https://placehold.co/600x400?text=Google+AI+Pro
```

---

## 7.4 Tabel orders

Fungsi:

```text
Menyimpan data pesanan.
```

Kolom:

```text
id
order_code
customer_name
customer_email
customer_phone
total_amount
payment_method
status
note
created_at
updated_at
```

Struktur:

```sql
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(50) NOT NULL UNIQUE,
  customer_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(150) DEFAULT NULL,
  customer_phone VARCHAR(30) DEFAULT NULL,
  total_amount INT NOT NULL DEFAULT 0,
  payment_method VARCHAR(50) DEFAULT NULL,
  status ENUM('pending', 'paid', 'completed', 'cancelled') DEFAULT 'pending',
  note TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Status:

```text
pending    = Menunggu
paid       = Dibayar
completed  = Selesai
cancelled  = Batal
```

---

## 7.5 Tabel order_items

Fungsi:

```text
Menyimpan detail produk dalam pesanan.
```

Kolom:

```text
id
order_id
product_id
product_name
quantity
price
subtotal
created_at
```

Struktur:

```sql
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(150) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price INT NOT NULL DEFAULT 0,
  subtotal INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE SET NULL
);
```

Catatan:

```text
product_name tetap disimpan agar riwayat order tidak rusak jika produk dihapus.
```

---

## 7.6 Tabel testimonials

Fungsi:

```text
Menyimpan testimoni pelanggan.
```

Kolom:

```text
id
name
role
message
rating
status
created_at
updated_at
```

Struktur:

```sql
CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  role VARCHAR(100) DEFAULT NULL,
  message TEXT NOT NULL,
  rating TINYINT DEFAULT 5,
  status ENUM('visible', 'hidden') DEFAULT 'visible',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Status:

```text
visible = Tampil
hidden  = Sembunyi
```

---

## 7.7 Tabel store_settings

Fungsi:

```text
Menyimpan setting toko.
```

Kolom:

```text
id
setting_key
setting_value
created_at
updated_at
```

Struktur:

```sql
CREATE TABLE store_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Contoh data:

```text
store_name        = DigiStore
store_tagline     = Produk Digital Premium
store_description = Katalog produk digital sederhana.
store_whatsapp    = 6281234567890
store_email       = admin@example.com
store_instagram   = digistore
default_theme     = light
accent_color      = blue
```

Alasan menggunakan key-value:

```text
Setting toko lebih fleksibel.
Tidak perlu mengubah struktur tabel jika ada setting baru.
```

---

# 8. API Dashboard

Base path API:

```text
/dashboard/api/
```

Semua response menggunakan JSON.

Header:

```php
header("Content-Type: application/json");
```

Format response sukses:

```json
{
  "success": true,
  "message": "Data berhasil dimuat",
  "data": []
}
```

Format response error:

```json
{
  "success": false,
  "message": "Data tidak ditemukan",
  "errors": null
}
```

---

# 9. Endpoint API

## 9.1 Stats API

File:

```text
/dashboard/api/stats.php
```

Method:

```text
GET
```

Endpoint:

```text
GET /dashboard/api/stats.php
```

Fungsi:

```text
Mengambil ringkasan data dashboard.
```

Response:

```json
{
  "success": true,
  "message": "Statistik berhasil dimuat",
  "data": {
    "total_products": 24,
    "active_products": 18,
    "total_orders": 32,
    "today_orders": 7,
    "total_testimonials": 12,
    "average_rating": 4.8
  }
}
```

Digunakan di:

```text
/dashboard/index.php
```

---

## 9.2 Products API

File:

```text
/dashboard/api/products.php
```

### GET Products

Endpoint:

```text
GET /dashboard/api/products.php
```

Query opsional:

```text
search
category_id
status
```

Contoh:

```text
/dashboard/api/products.php?search=google&status=active
```

Response:

```json
{
  "success": true,
  "message": "Produk berhasil dimuat",
  "data": [
    {
      "id": 1,
      "category_id": 4,
      "category_name": "Tools AI",
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

### GET Product Detail

Endpoint:

```text
GET /dashboard/api/products.php?id=1
```

Fungsi:

```text
Mengambil detail satu produk untuk modal edit.
```

---

### POST Product

Endpoint:

```text
POST /dashboard/api/products.php
```

Fungsi:

```text
Menambah produk baru.
```

Body JSON:

```json
{
  "category_id": 4,
  "name": "Google AI Pro 12 Bulan",
  "slug": "google-ai-pro-12-bulan",
  "description": "Lorem ipsum dolor sit amet.",
  "price": 25000,
  "original_price": 50000,
  "stock": 12,
  "image_url": "https://placehold.co/600x400?text=Google+AI+Pro",
  "badge": "Best Seller",
  "status": "active",
  "is_featured": true
}
```

Validasi:

```text
name wajib
slug wajib
price wajib angka
stock wajib angka
status wajib valid
```

---

### PUT Product

Endpoint:

```text
PUT /dashboard/api/products.php?id=1
```

Fungsi:

```text
Mengedit produk.
```

Body JSON:

```json
{
  "category_id": 4,
  "name": "Google AI Pro 12 Bulan",
  "slug": "google-ai-pro-12-bulan",
  "description": "Lorem ipsum dolor sit amet.",
  "price": 30000,
  "original_price": 50000,
  "stock": 10,
  "image_url": "https://placehold.co/600x400?text=Google+AI+Pro",
  "badge": "Best Seller",
  "status": "active",
  "is_featured": true
}
```

---

### DELETE Product

Endpoint:

```text
DELETE /dashboard/api/products.php?id=1
```

Fungsi:

```text
Menghapus produk.
```

Catatan:

```text
Untuk tahap awal boleh hard delete.
Untuk versi lebih aman, gunakan soft delete dengan status draft/nonaktif.
```

Rekomendasi:

```text
Gunakan soft delete nanti.
Untuk MVP, hard delete masih cukup.
```

---

## 9.3 Categories API

File:

```text
/dashboard/api/categories.php
```

### GET Categories

```text
GET /dashboard/api/categories.php
```

Response:

```json
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
      "product_count": 8
    }
  ]
}
```

### POST Category

```text
POST /dashboard/api/categories.php
```

Body:

```json
{
  "name": "Tools AI",
  "slug": "tools-ai",
  "icon": "bot",
  "status": "active"
}
```

### PUT Category

```text
PUT /dashboard/api/categories.php?id=1
```

Body:

```json
{
  "name": "Akun Premium",
  "slug": "akun-premium",
  "icon": "star",
  "status": "active"
}
```

### DELETE Category

```text
DELETE /dashboard/api/categories.php?id=1
```

Catatan:

```text
Jika kategori dihapus, produk yang memakai kategori tersebut akan memiliki category_id NULL.
```

---

## 9.4 Orders API

File:

```text
/dashboard/api/orders.php
```

### GET Orders

```text
GET /dashboard/api/orders.php
```

Query opsional:

```text
status
search
```

Contoh:

```text
/dashboard/api/orders.php?status=pending
```

Response:

```json
{
  "success": true,
  "message": "Pesanan berhasil dimuat",
  "data": [
    {
      "id": 1,
      "order_code": "ORD-001",
      "customer_name": "Raka Pratama",
      "customer_email": "raka@example.com",
      "customer_phone": "628123456789",
      "total_amount": 25000,
      "payment_method": "QRIS",
      "status": "paid",
      "created_at": "2026-06-24 10:00:00"
    }
  ]
}
```

### GET Order Detail

```text
GET /dashboard/api/orders.php?id=1
```

Response:

```json
{
  "success": true,
  "message": "Detail pesanan berhasil dimuat",
  "data": {
    "id": 1,
    "order_code": "ORD-001",
    "customer_name": "Raka Pratama",
    "customer_email": "raka@example.com",
    "customer_phone": "628123456789",
    "total_amount": 25000,
    "payment_method": "QRIS",
    "status": "paid",
    "items": [
      {
        "product_name": "Google AI Pro 12 Bulan",
        "quantity": 1,
        "price": 25000,
        "subtotal": 25000
      }
    ]
  }
}
```

### PUT Order Status

```text
PUT /dashboard/api/orders.php?id=1
```

Body:

```json
{
  "status": "completed"
}
```

Fungsi:

```text
Mengubah status pesanan.
```

Catatan:

```text
Pada tahap dashboard, admin hanya perlu bisa melihat order dan ubah status.
Tambah order dari dashboard tidak wajib.
```

---

## 9.5 Testimonials API

File:

```text
/dashboard/api/testimonials.php
```

### GET Testimonials

```text
GET /dashboard/api/testimonials.php
```

### POST Testimonial

```text
POST /dashboard/api/testimonials.php
```

Body:

```json
{
  "name": "Raka Pratama",
  "role": "Mahasiswa",
  "message": "Lorem ipsum dolor sit amet.",
  "rating": 5,
  "status": "visible"
}
```

### PUT Testimonial

```text
PUT /dashboard/api/testimonials.php?id=1
```

Body:

```json
{
  "name": "Raka Pratama",
  "role": "Mahasiswa",
  "message": "Lorem ipsum dolor sit amet.",
  "rating": 5,
  "status": "visible"
}
```

### DELETE Testimonial

```text
DELETE /dashboard/api/testimonials.php?id=1
```

---

## 9.6 Settings API

File:

```text
/dashboard/api/settings.php
```

### GET Settings

```text
GET /dashboard/api/settings.php
```

Response:

```json
{
  "success": true,
  "message": "Setting berhasil dimuat",
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

### PUT Settings

```text
PUT /dashboard/api/settings.php
```

Body:

```json
{
  "store_name": "DigiStore",
  "store_tagline": "Produk Digital Premium",
  "store_description": "Katalog produk digital sederhana.",
  "store_whatsapp": "6281234567890",
  "store_email": "admin@example.com",
  "store_instagram": "digistore",
  "default_theme": "light",
  "accent_color": "blue"
}
```

Fungsi:

```text
Mengubah setting toko.
```

---

# 10. Koneksi Frontend Dashboard ke API

Dashboard menggunakan JavaScript `fetch()`.

Contoh pola umum:

```js
async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      "Content-Type": "application/json"
    },
    ...options
  });

  return await response.json();
}
```

Contoh mengambil produk:

```js
async function loadProducts() {
  const result = await apiRequest("/dashboard/api/products.php");

  if (!result.success) {
    console.error(result.message);
    return;
  }

  renderProducts(result.data);
}
```

Contoh tambah produk:

```js
async function createProduct(payload) {
  const result = await apiRequest("/dashboard/api/products.php", {
    method: "POST",
    body: JSON.stringify(payload)
  });

  if (result.success) {
    loadProducts();
  }
}
```

---

# 11. Validasi Backend

Validasi minimal:

## Produk

```text
name wajib
slug wajib dan unik
price wajib angka
stock wajib angka
status hanya active/draft/out_of_stock
category_id harus ada jika diisi
```

## Kategori

```text
name wajib
slug wajib dan unik
status hanya active/inactive
```

## Testimoni

```text
name wajib
message wajib
rating angka 1 sampai 5
status hanya visible/hidden
```

## Order

```text
status hanya pending/paid/completed/cancelled
```

## Setting

```text
store_name wajib
email harus format email jika diisi
whatsapp hanya angka jika diisi
```

---

# 12. Security Requirement Dasar

Walaupun masih sederhana, backend tetap harus aman secara dasar.

Requirement:

```text
- Gunakan prepared statement.
- Jangan langsung memasukkan input user ke query SQL.
- Password admin pakai password_hash().
- Login admin pakai password_verify().
- API dashboard sebaiknya hanya bisa diakses admin yang login.
- Response error jangan membocorkan detail database.
- Validasi method HTTP.
- Validasi input JSON.
```

Untuk MVP awal:

```text
Boleh buat API tanpa login dulu jika masih lokal.
Namun saat upload ke hosting, API dashboard harus diproteksi login.
```

---

# 13. Response Helper

Buat helper agar response JSON konsisten.

File:

```text
/dashboard/config/response.php
```

Konsep fungsi:

```php
function json_response($success, $message, $data = null, $errors = null, $status_code = 200) {
  http_response_code($status_code);
  header("Content-Type: application/json");

  echo json_encode([
    "success" => $success,
    "message" => $message,
    "data" => $data,
    "errors" => $errors
  ]);

  exit;
}
```

---

# 14. Database Connection

File:

```text
/dashboard/config/database.php
```

Requirement:

```text
- Koneksi pakai mysqli atau PDO.
- Rekomendasi: PDO.
- Config database sebaiknya dari .env atau file config terpisah.
```

Contoh config sederhana:

```php
$host = "localhost";
$dbname = "digital_store";
$username = "root";
$password = "";

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  json_response(false, "Koneksi database gagal", null, null, 500);
}
```

---

# 15. Urutan Implementasi

Urutan pengerjaan yang disarankan:

```text
1. Buat folder database.
2. Buat schema.sql.
3. Buat seed.sql.
4. Import schema ke MySQL.
5. Import seed dummy.
6. Buat dashboard/config/database.php.
7. Buat dashboard/config/response.php.
8. Buat API categories.php.
9. Buat API products.php.
10. Hubungkan halaman products.php ke API.
11. Buat API testimonials.php.
12. Hubungkan halaman testimonials.php ke API.
13. Buat API orders.php.
14. Hubungkan halaman orders.php ke API.
15. Buat API settings.php.
16. Hubungkan halaman settings.php ke API.
17. Buat API stats.php.
18. Hubungkan overview dashboard.
19. Tambahkan validasi backend.
20. Tambahkan proteksi login admin.
```

Prioritas utama:

```text
1. Categories
2. Products
3. Settings
4. Testimonials
5. Orders
6. Stats
7. Auth admin
```

Alasan:

```text
Produk dan kategori adalah inti dashboard.
Orders belum bisa real karena landing page checkout belum dibuat.
```

---

# 16. Acceptance Criteria

Tahap ini dianggap selesai jika:

```text
1. Database berhasil dibuat.
2. Semua tabel utama tersedia.
3. Seed dummy berhasil dimasukkan.
4. API products bisa GET, POST, PUT, DELETE.
5. API categories bisa GET, POST, PUT, DELETE.
6. API testimonials bisa GET, POST, PUT, DELETE.
7. API orders bisa GET detail dan update status.
8. API settings bisa GET dan PUT.
9. API stats bisa mengembalikan ringkasan dashboard.
10. Dashboard tidak lagi bergantung pada dummy array utama.
11. Data dashboard berasal dari database.
12. Response API konsisten dalam format JSON.
13. Error API ditampilkan dengan message yang jelas.
14. Input penting sudah divalidasi.
15. Query SQL menggunakan prepared statement.
```

---

# 17. Catatan Penting

Tahap ini belum menghubungkan database ke landing page publik.

Artinya:

```text
Dashboard sudah bisa mengelola data di database.
Landing page utama masih bisa tetap memakai dummy data.
```

Nanti pada PRD terpisah, landing page akan dibuat mengambil data dari database melalui API publik seperti:

```text
/api/products.php
/api/categories.php
/api/testimonials.php
/api/settings.php
```

Namun endpoint publik tersebut belum dibuat pada tahap ini.

---

# 18. Kesimpulan

Tahap ini adalah fondasi backend untuk dashboard. Fokusnya adalah membuat data dashboard menjadi nyata, tersimpan di MySQL, dan bisa diakses melalui API PHP Native.

Prioritas paling penting adalah membuat schema database yang rapi, endpoint API yang sederhana, response JSON yang konsisten, serta koneksi dashboard ke API menggunakan JavaScript `fetch()`.

Landing page publik tidak disentuh dulu agar proses pengembangan tetap fokus dan tidak melebar.
