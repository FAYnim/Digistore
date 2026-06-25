# PRD — Alur Pembelian Minimal untuk Katalog Produk Digital

## 1. Ringkasan

Fitur ini menambahkan alur pembelian sederhana pada landing page katalog produk digital.

Tujuan utamanya adalah agar website tidak hanya menampilkan produk, tetapi sudah bisa menerima pembelian secara nyata dalam bentuk order yang tersimpan ke database.

Flow utama:

```text id="6o1j0s"
User pilih produk
→ User masuk halaman checkout
→ User mengisi form pembelian
→ User klik Buat Pesanan
→ Sistem menyimpan data order ke database
→ Sistem menampilkan halaman pembayaran QRIS dummy
→ User melakukan pembayaran manual
→ User menghubungi admin / menunggu konfirmasi
→ Admin mengubah status order dari dashboard
```

Pada tahap ini, pembayaran belum otomatis. QRIS masih dummy/manual, dan konfirmasi pembayaran dilakukan oleh admin.

---

## 2. Tujuan

Fitur ini dibuat untuk:

```text id="eio7du"
1. Membuat alur pembelian dasar.
2. Menyimpan data pembelian ke database.
3. Membuat order_code otomatis.
4. Menampilkan QRIS setelah order berhasil dibuat.
5. Membuat user bisa melihat instruksi pembayaran.
6. Menyiapkan data order agar bisa dikelola dari dashboard.
7. Menjaga flow tetap sederhana dan ringan.
```

---

## 3. Scope

### Termasuk

```text id="xz8w2d"
- Tombol Beli Sekarang di product card.
- Halaman checkout.
- Form data pembeli.
- Ringkasan produk.
- Simpan order ke database.
- Simpan order_items ke database.
- Generate kode order.
- Halaman pembayaran QRIS dummy.
- Halaman cek status pesanan.
- API create order.
- API get order detail.
- Status order awal: pending.
```

### Tidak Termasuk

```text id="2omkny"
- Payment gateway otomatis.
- Validasi pembayaran otomatis.
- Upload bukti pembayaran.
- Email otomatis.
- WhatsApp otomatis.
- Auto delivery produk digital.
- Keranjang multi-produk.
- Login user.
```

---

## 4. Konsep Alur Minimal

Alur pembelian dibuat **direct checkout**, bukan cart.

Artinya:

```text id="s8ty7w"
User klik Beli Sekarang pada satu produk
→ Langsung masuk halaman checkout produk tersebut
```

Alasan:

```text id="6ms4w1"
- Lebih sederhana.
- Cocok untuk MVP.
- Tidak perlu sistem cart dulu.
- Lebih cepat dikembangkan.
- Lebih cocok untuk produk digital satuan.
```

---

## 5. Struktur Halaman

Halaman baru yang dibutuhkan:

```text id="e2guj3"
1. checkout.php
2. payment.php
3. order-status.php
```

Struktur path:

```text id="vexlrl"
/checkout.php?product=google-ai-pro-12-bulan
/payment.php?code=ORD-20260624-001
/order-status.php?code=ORD-20260624-001
```

---

# 6. Flow Utama Pembelian

## 6.1 Dari Landing Page ke Checkout

Pada product card, tombol:

```text id="l7g93l"
Beli Sekarang
```

Behavior:

```text id="d92zys"
User klik Beli Sekarang
→ Sistem mengambil slug produk
→ User diarahkan ke checkout.php?product=slug-produk
```

Contoh:

```text id="5l8kju"
/checkout.php?product=google-ai-pro-12-bulan
```

Jika produk habis:

```text id="tsz12n"
- Tombol disabled
- Teks tombol: Habis
- User tidak bisa checkout
```

---

## 6.2 Checkout Page

Halaman checkout menampilkan:

```text id="w9o4da"
- Navbar sederhana
- Ringkasan produk
- Form data pembeli
- Total pembayaran
- Tombol Buat Pesanan
```

Data produk diambil dari API publik berdasarkan slug.

Endpoint:

```text id="pwg79r"
GET /api/products.php?slug=google-ai-pro-12-bulan
```

Jika produk tidak ditemukan:

```text id="6kclo4"
Produk tidak ditemukan.
```

Jika produk habis:

```text id="qzg8lu"
Produk sedang habis.
```

---

## 6.3 User Mengisi Form

Field form checkout:

```text id="u4n6hh"
Nama
Email
WhatsApp
Catatan
```

Field wajib:

```text id="achjoh"
Nama wajib
WhatsApp wajib
Email opsional
Catatan opsional
```

Form tidak perlu banyak teks panjang.

Label sederhana:

```text id="4gewx8"
Nama
Email
WhatsApp
Catatan
```

Placeholder singkat:

```text id="gh84mj"
Nama → Faris
Email → faris@email.com
WhatsApp → 6281234567890
Catatan → Opsional
```

---

## 6.4 User Klik Buat Pesanan

Tombol utama:

```text id="2onjol"
Buat Pesanan
```

Saat diklik:

```text id="xbja49"
1. Frontend validasi input dasar.
2. Frontend mengirim data ke API.
3. API validasi produk dan stok.
4. API membuat order_code.
5. API menyimpan data ke tabel orders.
6. API menyimpan produk ke tabel order_items.
7. API mengembalikan order_code.
8. Frontend redirect ke payment.php?code=order_code.
```

Endpoint:

```text id="xb58re"
POST /api/checkout.php
```

Body JSON:

```json id="yf6cuj"
{
  "product_id": 1,
  "quantity": 1,
  "customer_name": "Faris",
  "customer_email": "faris@email.com",
  "customer_phone": "6281234567890",
  "note": "Opsional"
}
```

Response sukses:

```json id="3784vj"
{
  "success": true,
  "message": "Pesanan berhasil dibuat",
  "data": {
    "order_code": "ORD-20260624-001",
    "redirect_url": "/payment.php?code=ORD-20260624-001"
  }
}
```

Response gagal:

```json id="bjdykc"
{
  "success": false,
  "message": "Produk tidak tersedia",
  "data": null
}
```

---

## 6.5 Setelah Data Tersimpan, Tampilkan QRIS

Halaman QRIS **hanya boleh tampil setelah order berhasil tersimpan ke database**.

Flow:

```text id="jj1wy6"
Order berhasil dibuat
→ Data masuk tabel orders
→ Data masuk tabel order_items
→ User diarahkan ke payment.php?code=...
→ payment.php mengambil detail order dari API
→ QRIS dummy ditampilkan
```

Halaman `payment.php` tidak membuat order baru. Halaman ini hanya membaca order yang sudah ada.

Endpoint:

```text id="nyk4pl"
GET /api/orders.php?code=ORD-20260624-001
```

---

# 7. UI Halaman Checkout

## 7.1 Layout Desktop

Desktop menggunakan 2 kolom:

```text id="15tldn"
Kiri:
Form data pembeli

Kanan:
Ringkasan produk dan total pembayaran
```

Struktur:

```text id="quphg7"
+------------------------------------------------------+
| Navbar                                               |
+------------------------------------------------------+
| Checkout                                             |
|                                                      |
| +---------------------------+ +--------------------+ |
| | Form Pembeli              | | Ringkasan Produk   | |
| | Nama                      | | Gambar Produk      | |
| | Email                     | | Nama Produk        | |
| | WhatsApp                  | | Harga              | |
| | Catatan                   | | Total              | |
| | [Buat Pesanan]            | |                    | |
| +---------------------------+ +--------------------+ |
+------------------------------------------------------+
```

---

## 7.2 Layout Mobile

Mobile menggunakan 1 kolom:

```text id="5im8wd"
1. Ringkasan produk
2. Form pembeli
3. Tombol Buat Pesanan
```

---

## 7.3 Komponen Checkout

Komponen UI:

```text id="gomd24"
- Navbar sederhana
- Breadcrumb kecil
- Product summary card
- Buyer form
- Total payment card
- Submit button
- Loading state
- Error message
```

Copywriting singkat:

```text id="ybqfpf"
Judul:
Checkout

Subtitle:
Lengkapi data pembelian.

Button:
Buat Pesanan
```

Error form:

```text id="imye0w"
Nama wajib diisi.
WhatsApp wajib diisi.
Produk tidak tersedia.
Gagal membuat pesanan.
```

---

# 8. UI Halaman Payment / QRIS

## 8.1 Fungsi

Halaman ini menampilkan instruksi pembayaran setelah order tersimpan.

Path:

```text id="gz4xwy"
/payment.php?code=ORD-20260624-001
```

Isi halaman:

```text id="hyoafa"
- Kode order
- Status pesanan
- Total pembayaran
- QRIS dummy
- Instruksi pembayaran
- Tombol hubungi admin
- Tombol cek status
```

---

## 8.2 Layout Desktop

```text id="bo8hqw"
+------------------------------------------------------+
| Navbar                                               |
+------------------------------------------------------+
| Pembayaran                                           |
|                                                      |
| +---------------------------+ +--------------------+ |
| | Detail Pesanan            | | QRIS               | |
| | Kode Order                | | Gambar QRIS        | |
| | Produk                    | | Total              | |
| | Status                    | | Instruksi          | |
| | Total                     | | [Hubungi Admin]    | |
| +---------------------------+ +--------------------+ |
+------------------------------------------------------+
```

---

## 8.3 QRIS Dummy

QRIS dummy bisa menggunakan:

```text id="b3c3xk"
https://placehold.co/400x400?text=QRIS+Dummy
```

Atau dari setting toko:

```text id="i8i43e"
store_settings.payment_qris_image
```

Untuk MVP:

```text id="v63o4c"
Gunakan payment_qris_image dari store_settings.
Jika kosong, gunakan placehold.co.
```

---

## 8.4 Instruksi Pembayaran

Teks harus pendek:

```text id="l54z49"
1. Scan QRIS.
2. Bayar sesuai total.
3. Simpan bukti pembayaran.
4. Hubungi admin untuk konfirmasi.
```

Tombol:

```text id="jt5mhv"
Konfirmasi WhatsApp
Cek Status
Kembali ke Katalog
```

Link WhatsApp:

```text id="0kdy9s"
https://wa.me/{store_whatsapp}?text=Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.
```

---

# 9. UI Halaman Status Pesanan

## 9.1 Fungsi

Halaman ini digunakan user untuk mengecek status order.

Path:

```text id="j4xgen"
/order-status.php
/order-status.php?code=ORD-20260624-001
```

Jika tanpa code:

```text id="j9xypc"
Tampilkan input kode order.
```

Jika dengan code:

```text id="4p25ny"
Ambil detail order dari API dan tampilkan status.
```

---

## 9.2 Data yang Ditampilkan

```text id="17w20e"
- Kode order
- Nama customer
- Produk
- Total pembayaran
- Status pesanan
- Tanggal order
- Catatan admin / delivery note jika ada
```

Status:

```text id="oorxbu"
pending    → Menunggu Pembayaran
paid       → Pembayaran Diterima
completed  → Selesai
cancelled  → Dibatalkan
```

Untuk MVP, status awal setelah checkout:

```text id="6ftlxa"
pending
```

---

# 10. Database Schema Tambahan

Schema sebelumnya sudah punya:

```text id="gedhft"
orders
order_items
store_settings
```

Namun untuk alur pembelian minimal, perlu beberapa penyesuaian.

---

## 10.1 Update Tabel orders

Tambahkan kolom:

```text id="34yiiy"
payment_deadline
delivery_note
```

SQL alter:

```sql id="87j9yj"
ALTER TABLE orders
ADD COLUMN payment_deadline DATETIME DEFAULT NULL AFTER payment_method,
ADD COLUMN delivery_note TEXT DEFAULT NULL AFTER note;
```

Struktur orders yang dipakai:

```text id="exzx6c"
id
order_code
customer_name
customer_email
customer_phone
total_amount
payment_method
payment_deadline
status
note
delivery_note
created_at
updated_at
```

Status default:

```text id="0a8qg1"
pending
```

Payment method default:

```text id="8ywwtc"
QRIS
```

---

## 10.2 Store Settings untuk Pembayaran

Tambahkan data ke `store_settings`:

```text id="h1owc6"
payment_qris_image
payment_instruction
payment_whatsapp_message
```

Contoh seed:

```sql id="bap1f1"
INSERT INTO store_settings (setting_key, setting_value) VALUES
('payment_qris_image', 'https://placehold.co/400x400?text=QRIS+Dummy'),
('payment_instruction', 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.'),
('payment_whatsapp_message', 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.');
```

---

# 11. API yang Dibutuhkan

## 11.1 Checkout API

File:

```text id="nys6bg"
/api/checkout.php
```

Method:

```text id="ue5enm"
POST
```

Fungsi:

```text id="ccwzty"
Membuat order baru dari landing page.
```

Validasi:

```text id="i8yqc3"
- product_id wajib
- product harus ada
- product status harus active
- stock harus lebih dari 0
- customer_name wajib
- customer_phone wajib
- customer_email valid jika diisi
- quantity minimal 1
```

Proses backend:

```text id="1boq96"
1. Baca input JSON.
2. Validasi input.
3. Ambil produk dari database.
4. Cek status produk.
5. Cek stok produk.
6. Generate order_code.
7. Hitung total.
8. Insert ke orders.
9. Insert ke order_items.
10. Return order_code.
```

Catatan stok:

```text id="qeo4a1"
Untuk MVP, stok belum dikurangi saat order pending.
Stok dikurangi nanti saat admin mengubah status order menjadi paid atau completed.
```

---

## 11.2 Order Detail API Publik

File:

```text id="xk0r18"
/api/orders.php
```

Method:

```text id="ztawp3"
GET
```

Endpoint:

```text id="ix6njc"
/api/orders.php?code=ORD-20260624-001
```

Fungsi:

```text id="wc2q3c"
Mengambil detail order berdasarkan order_code.
```

Response:

```json id="t5izlb"
{
  "success": true,
  "message": "Order berhasil dimuat",
  "data": {
    "order_code": "ORD-20260624-001",
    "customer_name": "Faris",
    "total_amount": 25000,
    "payment_method": "QRIS",
    "payment_deadline": "2026-06-24 23:59:00",
    "status": "pending",
    "note": "Opsional",
    "delivery_note": null,
    "created_at": "2026-06-24 12:00:00",
    "items": [
      {
        "product_name": "Google AI Pro 12 Bulan",
        "quantity": 1,
        "price": 25000,
        "subtotal": 25000
      }
    ],
    "payment": {
      "qris_image": "https://placehold.co/400x400?text=QRIS+Dummy",
      "instruction": "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.",
      "whatsapp": "6281234567890"
    }
  }
}
```

Keamanan:

```text id="uf6hzw"
API publik hanya boleh menampilkan data seperlunya.
Jangan tampilkan data sensitif admin.
```

---

# 12. Format Order Code

Format order code:

```text id="uxdrms"
ORD-YYYYMMDD-XXX
```

Contoh:

```text id="8t6hgr"
ORD-20260624-001
ORD-20260624-002
ORD-20260624-003
```

Logic:

```text id="pv37ko"
- Gunakan tanggal hari ini.
- Hitung jumlah order hari itu.
- Tambahkan nomor urut 3 digit.
```

Alternatif lebih aman:

```text id="vsnjrf"
ORD-YYYYMMDD-RANDOM
```

Contoh:

```text id="ksyc3x"
ORD-20260624-A8K3
```

Rekomendasi:

```text id="624x66"
Gunakan kombinasi tanggal + random agar lebih aman dari tabrakan order_code.
```

Contoh final:

```text id="zbbmfd"
ORD-20260624-A8K3
```

---

# 13. Status Order

Status yang digunakan:

```text id="4y08r0"
pending
paid
completed
cancelled
```

Mapping UI:

```text id="gc1isn"
pending   → Menunggu Pembayaran
paid      → Pembayaran Diterima
completed → Selesai
cancelled → Dibatalkan
```

Warna badge:

```text id="vhnbnx"
pending   → Kuning
paid      → Biru
completed → Hijau
cancelled → Merah / Abu-abu
```

---

# 14. Dashboard Impact

Dashboard order yang sebelumnya dummy harus bisa membaca data order nyata.

Halaman:

```text id="36yk6b"
/dashboard/orders.php
```

Fungsi tambahan:

```text id="s1xrep"
- Menampilkan order dari database.
- Melihat detail order.
- Mengubah status order.
- Mengisi delivery_note.
```

Aksi admin:

```text id="3yl067"
Detail
Tandai Dibayar
Tandai Selesai
Batalkan
```

Jika admin mengubah status menjadi completed:

```text id="lyf6jr"
- Order selesai.
- delivery_note bisa diisi manual.
- User bisa melihat delivery_note di halaman status order.
```

---

# 15. Frontend JavaScript Flow

## 15.1 Checkout Page

State:

```js id="btxjpx"
let selectedProduct = null;
let isSubmitting = false;
```

Flow:

```text id="mxp0tu"
1. Ambil slug produk dari URL.
2. Fetch detail produk.
3. Render ringkasan produk.
4. User isi form.
5. User klik Buat Pesanan.
6. Disable tombol saat loading.
7. POST ke /api/checkout.php.
8. Jika sukses, redirect ke payment.php?code=...
9. Jika gagal, tampilkan pesan error singkat.
```

---

## 15.2 Payment Page

Flow:

```text id="kb5xvv"
1. Ambil order_code dari URL.
2. Fetch /api/orders.php?code=...
3. Render detail order.
4. Render QRIS.
5. Render tombol WhatsApp.
6. Render tombol cek status.
```

---

## 15.3 Order Status Page

Flow:

```text id="nkqbni"
1. Jika ada code di URL, fetch detail order.
2. Jika tidak ada code, tampilkan form input kode order.
3. User input kode.
4. Redirect ke /order-status.php?code=...
5. Render status order.
```

---

# 16. UI Copywriting

Gunakan teks pendek.

## Checkout

```text id="oh5z39"
Judul:
Checkout

Subtitle:
Lengkapi data pembelian.

Button:
Buat Pesanan
```

## Payment

```text id="jo66mi"
Judul:
Pembayaran

Subtitle:
Scan QRIS dan konfirmasi ke admin.

Button:
Konfirmasi WhatsApp
Cek Status
```

## Status

```text id="1q3spy"
Judul:
Status Pesanan

Subtitle:
Masukkan kode order untuk cek pesanan.

Button:
Cek Pesanan
```

Error message:

```text id="m9r6h4"
Produk tidak ditemukan.
Produk sedang habis.
Nama wajib diisi.
WhatsApp wajib diisi.
Gagal membuat pesanan.
Order tidak ditemukan.
```

---

# 17. Security Requirement

Requirement wajib:

```text id="ffmg9s"
- Gunakan prepared statement.
- Validasi semua input checkout.
- Jangan percaya harga dari frontend.
- Harga harus diambil dari database.
- Jangan percaya nama produk dari frontend.
- Produk harus diambil dari database berdasarkan product_id.
- Batasi quantity minimal 1.
- Jangan tampilkan error SQL ke user.
- API checkout hanya menerima POST.
- API order detail hanya menerima GET.
```

Poin paling penting:

```text id="ydnvh1"
Frontend hanya mengirim product_id dan data customer.
Harga, nama produk, dan subtotal dihitung ulang di backend.
```

---

# 18. Performance Requirement

```text id="mmpb6h"
- Checkout hanya fetch satu produk.
- Payment hanya fetch satu order.
- Status hanya fetch satu order.
- Tidak menggunakan library berat.
- QRIS dummy pakai image ringan.
- Loading state sederhana.
```

---

# 19. Acceptance Criteria

Fitur ini selesai jika:

```text id="kefpve"
1. Tombol Beli Sekarang mengarah ke checkout produk.
2. Checkout menampilkan data produk dari database.
3. User bisa mengisi nama dan WhatsApp.
4. User bisa klik Buat Pesanan.
5. Order tersimpan ke tabel orders.
6. Produk tersimpan ke tabel order_items.
7. order_code dibuat otomatis.
8. Status awal order adalah pending.
9. Setelah order tersimpan, user diarahkan ke payment.php.
10. QRIS dummy tampil di halaman pembayaran.
11. Total pembayaran tampil sesuai harga dari database.
12. Tombol WhatsApp konfirmasi tampil.
13. User bisa membuka halaman status pesanan.
14. Status pesanan diambil dari database.
15. Order masuk ke dashboard admin.
16. Admin bisa mengubah status order.
17. User bisa melihat status terbaru setelah admin mengubah status.
18. API checkout menolak produk habis.
19. API checkout tidak memakai harga dari frontend.
20. Error ditampilkan singkat dan jelas.
```

---

# 20. Urutan Implementasi

Urutan pengerjaan:

```text id="i93i3m"
1. Tambahkan kolom payment_deadline dan delivery_note ke orders.
2. Tambahkan setting QRIS dummy ke store_settings.
3. Buat /api/checkout.php.
4. Buat /api/orders.php untuk public order detail.
5. Buat checkout.php.
6. Buat payment.php.
7. Buat order-status.php.
8. Hubungkan tombol Beli Sekarang ke checkout.
9. Update dashboard orders agar membaca order nyata.
10. Tambahkan aksi ubah status order di dashboard.
11. Tambahkan delivery_note di dashboard order detail.
12. Test flow dari landing page sampai status order.
```

Prioritas MVP:

```text id="ig3ga1"
1. checkout.php
2. /api/checkout.php
3. payment.php
4. /api/orders.php
5. dashboard orders update status
6. order-status.php
```

---

# 21. Kesimpulan

Fitur ini membuat website mulai berfungsi sebagai toko digital nyata.

Alur minimalnya adalah user memilih produk, mengisi data pembelian, sistem menyimpan order ke database, lalu menampilkan QRIS dummy untuk pembayaran manual.

Pembayaran belum otomatis, tetapi data order sudah tersimpan, bisa dicek, dan bisa dikelola admin melalui dashboard. Ini sudah cukup untuk MVP awal yang bisa digunakan dan dipasarkan secara terbatas.
