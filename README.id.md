# DigiStore

Toko katalog produk digital berbasis PHP dan MySQL. Menyediakan akun premium, source code, tools AI, template, dan lainnya.

[English](./README.md) | **Bahasa Indonesia**

## Fitur

- **Desain Katalog-First** — Produk langsung terlihat saat halaman dimuat
- **Pencarian Real-Time** — Cari produk berdasarkan nama atau deskripsi secara instan
- **Filter Kategori** — Filter berdasarkan kategori produk
- **Opsi Pengurutan** — Urutkan berdasarkan terbaru, harga, rating, atau popularitas
- **Mode Gelap/Terang** — Preferensi tema disimpan di localStorage
- **Desain Responsif** — Optimal untuk mobile, tablet, dan desktop
- **Produk Unggulan** — Tampilkan best seller dan produk populer
- **Manajemen Stok** — Tampilan stok real-time
- **Dashboard Admin** — CRUD lengkap untuk produk, kategori, dan pesanan
- **Integrasi Pembayaran** — Sistem konfirmasi pembayaran siap pakai

## Tech Stack

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript Vanilla
- **Styling**: Tailwind CSS (CDN)
- **Icons**: Font Awesome 6
- **Fonts**: Plus Jakarta Sans, Sora

## Persyaratan

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache dengan mod_rewrite (atau konfigurasi nginx)
- Composer (opsional, untuk development)

## Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd digital-store
```

### 2. Konfigurasi Environment

Salin file environment contoh dan update dengan pengaturan Anda:

```bash
cp .env.example .env
```

Update nilai-nilai berikut di `.env`:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=digital_store
DB_USER=username_anda
DB_PASS=password_anda
DB_CHARSET=utf8mb4

APP_URL=http://localhost
APP_DEBUG=false

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW_SECONDS=60
```

### 3. Buat Database

Buat database MySQL dan import schema:

```bash
mysql -u username_anda -p -e "CREATE DATABASE digital_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u username_anda -p digital_store < database/schema.sql
mysql -u username_anda -p digital_store < database/seed.sql
```

### 4. Konfigurasi Web Server

Untuk Apache (`.htaccess` sudah termasuk):

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/digital-store
    <Directory /var/www/digital-store>
        AllowOverride All
        Require all granted
    </Directory>
</Directory>
</VirtualHost>
```

Untuk nginx:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/digital-store;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Setup Cron Job

Set up cron job untuk mengakhiri pesanan yang tidak dibayar (jalankan setiap menit):

```bash
* * * * * php /path/to/digital-store/scripts/expire-orders.php
```

Atau tambahkan ke crontab sistem:

```bash
crontab -e
# Tambahkan baris ini:
* * * * * /usr/bin/php /path/to/digital-store/scripts/expire-orders.php
```

## Struktur Proyek

```
digital-store/
├── index.php              # Halaman katalog utama
├── checkout.php           # Halaman checkout
├── payment.php            # Halaman pembayaran
├── product.php            # Halaman detail produk
├── order-status.php       # Pelacakan status pesanan
│
├── api/                   # Endpoint API publik
│   ├── products.php       # GET produk, filter berdasarkan kategori
│   ├── categories.php     # GET semua kategori
│   ├── checkout.php       # POST buat pesanan baru
│   ├── orders.php         # GET/PATCH status pesanan
│   ├── payment-confirmations.php
│   ├── settings.php
│   └── testimonials.php
│
├── dashboard/             # Panel admin
│   ├── index.php          # Home dashboard
│   ├── login.php          # Autentikasi admin
│   ├── products.php       # Manajemen produk (CRUD)
│   ├── categories.php     # Manajemen kategori
│   ├── orders.php         # Manajemen pesanan
│   ├── testimonials.php   # Manajemen testimoni
│   ├── settings.php       # Pengaturan toko
│   ├── settings-payment.php # Pengaturan pembayaran
│   └── api/               # API protected (admin only)
│
├── config/                # File konfigurasi
│   ├── database.php       # Koneksi database
│   ├── env.php            # Loader environment
│   ├── rate-limit.php     # Rate limiting
│   ├── response.php       # Helper respons API
│   └── security-headers.php # Header keamanan
│
├── database/              # Migrasi SQL
│   ├── schema.sql         # Schema database
│   ├── seed.sql           # Data contoh
│   └── migrate-*.sql      # Migrasi tambahan
│
├── includes/              # Include PHP
│   └── order-expiration.php
│
├── scripts/               # Script CLI
│   └── expire-orders.php  # Script pengakhiran pesanan
│
└── assets/                # Aset statis
    ├── css/
    └── js/
```

## Schema Database

### Tabel Utama

| Tabel | Deskripsi |
|-------|------------|
| `products` | Katalog produk digital |
| `categories` | Kategori produk |
| `orders` | Pesanan pelanggan |
| `order_items` | Item dalam setiap pesanan |
| `product_accounts` | Kredensial akun yang dikirimkan |
| `testimonials` | Testimoni pelanggan |
| `payment_confirmations` | Upload bukti pembayaran |
| `store_settings` | Nilai konfigurasi |
| `admin_users` | Akun admin |

### Endpoint API

#### API Publik (`/api/`)

| Endpoint | Method | Deskripsi |
|----------|--------|-------------|
| `/api/products.php` | GET | Daftar produk (dukung `?category=`, `?search=`) |
| `/api/categories.php` | GET | Daftar semua kategori |
| `/api/orders.php` | GET | Ambil pesanan berdasarkan ID |
| `/api/checkout.php` | POST | Buat pesanan baru |
| `/api/testimonials.php` | GET | Daftar testimoni yang disetujui |
| `/api/settings.php` | GET | Pengaturan toko publik |

#### API Dashboard (`/dashboard/api/`)

| Endpoint | Method | Deskripsi |
|----------|--------|-------------|
| `/dashboard/api/orders.php` | GET | Daftar semua pesanan |
| `/dashboard/api/orders.php` | PATCH | Update status pesanan |
| `/dashboard/api/products.php` | GET/POST | Daftar/buat produk |
| `/dashboard/api/products.php` | GET/PUT/DELETE | Operasi produk tunggal |

## Konfigurasi

### Pengaturan Toko

Akses via Dashboard → Settings:

- Nama toko
- Informasi kontak
- Nomor WhatsApp
- Link media sosial

### Pengaturan Pembayaran

Akses via Dashboard → Settings → Payment:

- Konfigurasi metode pembayaran
- Nomor rekening
- Instruksi pembayaran

### Rate Limiting

Konfigurasi di `.env`:

```env
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW_SECONDS=60
```

## Development

### Menambah Produk Baru

1. Login ke dashboard
2. Navigasi ke Products
3. Klik "Add Product"
4. Isi detail produk:
   - Nama, deskripsi, harga
   - Pilihan kategori
   - Jumlah stok
   - Status featured
   - Akun produk (pisahkan dengan koma)

### Alur Pesanan

1. Pelanggan pilih produk → Checkout
2. Pesanan dibuat dengan status "pending"
3. Pelanggan melakukan pembayaran
4. Pelanggan submit bukti pembayaran
5. Admin verifikasi pembayaran → Status: "paid"
6. Sistem kirim otomatis akun produk → Status: "completed"

### Kadaluarsa Pesanan

Pesanan yang tidak dibayar akan berakhir otomatis setelah waktu yang dikonfigurasi:

- Cron job berjalan setiap menit
- Scan pesanan pending yang sudah lewat waktu
- Update status ke "expired"
- Bebaskan stok yang dipesan

## Keamanan

- Password hashing dengan `password_hash()`
- Prepared statements (pencegahan SQL injection)
- Rate limiting pada endpoint API
- Validasi CSRF token
- Pencegahan XSS (output escaping)
- Header keamanan via `config/security-headers.php`

## Lisensi

Copyright © 2026 DigiStore. All rights reserved.