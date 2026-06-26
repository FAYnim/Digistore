# PRD — Halaman Cek Status Pesanan untuk MVP Katalog Produk Digital

## 1. Ringkasan

Halaman cek status pesanan digunakan pembeli untuk melihat perkembangan order setelah melakukan checkout.

Flow sebelumnya:

```text id="9x8ndu"
User checkout
→ Order tersimpan ke database
→ User diarahkan ke halaman payment.php
→ User membayar QRIS manual
→ User konfirmasi ke admin via WhatsApp
→ Admin mengubah status order dari dashboard
→ User cek status order
```

Halaman ini menjadi tempat user melihat apakah pesanan masih menunggu pembayaran, sudah dibayar, selesai, atau dibatalkan.

Path halaman:

```text id="kr4xr3"
/order-status.php
/order-status.php?code=ORD-20260624-A8K3
```

---

## 2. Tujuan

Tujuan fitur:

```text id="smu5bj"
1. User bisa cek status pesanan menggunakan order_code.
2. User bisa melihat detail order secara ringkas.
3. User bisa melihat status pembayaran.
4. User bisa melihat delivery_note jika pesanan selesai.
5. User bisa kembali ke halaman pembayaran jika status masih pending.
6. Mengurangi pertanyaan manual ke admin.
7. Membuat flow checkout manual terlihat lebih profesional.
```

---

## 3. Scope

### Termasuk

```text id="c80bct"
- Halaman input kode order.
- Halaman detail status order.
- Fetch data order dari database melalui API publik.
- Status badge.
- Ringkasan produk.
- Total pembayaran.
- Tanggal order.
- Instruksi berdasarkan status.
- Tombol kembali ke payment page.
- Tombol konfirmasi WhatsApp.
- Delivery note jika order completed.
- Empty state jika kode belum dimasukkan.
- Error state jika order tidak ditemukan.
```

### Tidak Termasuk

```text id="rvwcpn"
- Login user.
- Riwayat semua order customer.
- Upload bukti pembayaran.
- Payment gateway otomatis.
- Email tracking.
- WhatsApp notification otomatis.
- Invoice PDF.
```

---

## 4. Posisi Fitur dalam Sistem

Halaman publik:

```text id="7i2n31"
/order-status.php
```

API publik:

```text id="wnq96j"
/api/orders.php?code=ORD-20260624-A8K3
```

Dashboard admin:

```text id="t07ow9"
/dashboard/orders.php
```

Relasi data:

```text id="znz4zy"
Checkout membuat order
→ Dashboard admin mengubah status
→ order-status.php membaca status terbaru
```

---

# 5. Flow User

## 5.1 User Membuka Halaman Tanpa Kode

Path:

```text id="f8kt48"
/order-status.php
```

Behavior:

```text id="56dux4"
Tampilkan form input kode order.
```

UI:

```text id="83wkiw"
Judul:
Cek Status Pesanan

Subtitle:
Masukkan kode order kamu.

Input:
Kode Order

Button:
Cek Pesanan
```

Flow:

```text id="e0bhko"
User isi kode order
→ Klik Cek Pesanan
→ Redirect ke /order-status.php?code=ORD-XXXX
```

---

## 5.2 User Membuka Halaman dengan Kode

Path:

```text id="m8rlpe"
/order-status.php?code=ORD-20260624-A8K3
```

Flow:

```text id="n9hzn8"
Halaman mengambil order_code dari URL
→ Fetch /api/orders.php?code=ORD-20260624-A8K3
→ Jika order ditemukan, tampilkan detail
→ Jika tidak ditemukan, tampilkan error
```

---

## 5.3 User dari Halaman Payment

Di halaman payment.php, tersedia tombol:

```text id="9zwnw7"
Cek Status
```

Link:

```text id="6xjrhr"
/order-status.php?code={order_code}
```

Jadi user tidak perlu mengetik ulang kode order.

---

# 6. UI Halaman Status Pesanan

## 6.1 Layout Desktop

```text id="rw8xnv"
+------------------------------------------------------+
| Navbar                                               |
+------------------------------------------------------+
| Status Pesanan                                      |
|                                                      |
| +---------------------------+ +--------------------+ |
| | Detail Pesanan            | | Status & Aksi      | |
| | Kode Order                | | Badge Status       | |
| | Customer                  | | Instruksi          | |
| | Produk                    | | Tombol Aksi        | |
| | Total                     | |                    | |
| +---------------------------+ +--------------------+ |
+------------------------------------------------------+
```

---

## 6.2 Layout Mobile

```text id="dfacnn"
Navbar
Status Badge
Detail Pesanan
Item Pesanan
Instruksi
Tombol Aksi
```

Mobile menggunakan 1 kolom.

---

## 6.3 Komponen UI

Komponen yang dibutuhkan:

```text id="o513oz"
- Navbar sederhana
- Order search form
- Status badge
- Order summary card
- Customer info card
- Product item list
- Payment summary card
- Delivery note card
- Action buttons
- Loading state
- Error state
- Empty state
```

---

# 7. Data yang Ditampilkan

Data utama:

```text id="zlva1l"
- Kode order
- Status order
- Nama customer
- Produk
- Quantity
- Harga
- Subtotal
- Total pembayaran
- Metode pembayaran
- Tanggal order
- Deadline pembayaran
- Delivery note jika tersedia
```

Data yang tidak perlu ditampilkan:

```text id="dqsoyk"
- ID internal database
- Detail admin
- Query error
- Data sensitif backend
```

Email dan nomor WhatsApp boleh ditampilkan sebagian atau penuh. Untuk MVP, boleh tampil penuh karena user mengakses menggunakan order_code.

---

# 8. Status Order

Status dari database:

```text id="9gjaxz"
pending
paid
completed
cancelled
```

Mapping UI:

```text id="opw6o4"
pending   → Menunggu Pembayaran
paid      → Pembayaran Diterima
completed → Selesai
cancelled → Dibatalkan
```

Warna badge:

```text id="n8fx8x"
pending   → Kuning
paid      → Biru
completed → Hijau
cancelled → Merah / Abu-abu
```

---

# 9. Tampilan Berdasarkan Status

## 9.1 Status Pending

Kondisi:

```text id="pgzhck"
status = pending
```

UI menampilkan:

```text id="nwi3ar"
Badge:
Menunggu Pembayaran

Instruksi:
Selesaikan pembayaran lalu konfirmasi ke admin.

Tombol:
Lanjut ke Pembayaran
Konfirmasi WhatsApp
```

Link tombol:

```text id="s5vl60"
Lanjut ke Pembayaran → /payment.php?code={order_code}
Konfirmasi WhatsApp → wa.me admin
```

---

## 9.2 Status Paid

Kondisi:

```text id="87ivrs"
status = paid
```

UI menampilkan:

```text id="yi3d6e"
Badge:
Pembayaran Diterima

Instruksi:
Pembayaran sudah diterima. Pesanan sedang diproses.

Tombol:
Hubungi Admin
Kembali ke Katalog
```

Delivery note belum wajib tampil jika status masih `paid`.

---

## 9.3 Status Completed

Kondisi:

```text id="j3p78t"
status = completed
```

UI menampilkan:

```text id="cc1s4n"
Badge:
Selesai

Instruksi:
Pesanan selesai.

Delivery Note:
Tampilkan delivery_note dari admin.

Tombol:
Hubungi Admin
Kembali ke Katalog
```

Delivery note hanya tampil jika:

```text id="mxz4pa"
status = completed
dan delivery_note tidak kosong
```

---

## 9.4 Status Cancelled

Kondisi:

```text id="9yxslo"
status = cancelled
```

UI menampilkan:

```text id="ro0o13"
Badge:
Dibatalkan

Instruksi:
Pesanan ini dibatalkan. Hubungi admin jika butuh bantuan.

Tombol:
Hubungi Admin
Kembali ke Katalog
```

---

# 10. API Publik Order Detail

File:

```text id="ejcgv2"
/api/orders.php
```

Method:

```text id="1q4g8k"
GET
```

Endpoint:

```text id="hpdjcn"
/api/orders.php?code=ORD-20260624-A8K3
```

Fungsi:

```text id="pg4xem"
Mengambil detail order berdasarkan order_code.
```

Response sukses:

```json id="lx626t"
{
  "success": true,
  "message": "Order berhasil dimuat",
  "data": {
    "order_code": "ORD-20260624-A8K3",
    "customer_name": "Faris",
    "customer_email": "faris@email.com",
    "customer_phone": "6281234567890",
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
      "qris_enabled": true,
      "qris_image": "https://placehold.co/400x400?text=QRIS+Dummy",
      "bank_enabled": false,
      "bank_name": "",
      "bank_account": "",
      "bank_holder": "",
      "instruction": "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.",
      "admin_whatsapp": "6281234567890",
      "whatsapp_message": "Halo admin, saya sudah membuat pesanan ORD-20260624-A8K3. Mohon dicek."
    }
  }
}
```

Response gagal:

```json id="hqxja8"
{
  "success": false,
  "message": "Order tidak ditemukan",
  "data": null
}
```

---

# 11. Query Database

SQL utama:

```sql id="1hbuv1"
SELECT 
  id,
  order_code,
  customer_name,
  customer_email,
  customer_phone,
  total_amount,
  payment_method,
  payment_deadline,
  status,
  note,
  delivery_note,
  created_at
FROM orders
WHERE order_code = ?
LIMIT 1;
```

SQL item:

```sql id="ql5hqp"
SELECT
  product_name,
  quantity,
  price,
  subtotal
FROM order_items
WHERE order_id = ?;
```

Setting pembayaran:

```sql id="no43pj"
SELECT setting_key, setting_value
FROM store_settings
WHERE setting_key IN (
  'payment_qris_enabled',
  'payment_qris_image',
  'payment_bank_enabled',
  'payment_bank_name',
  'payment_bank_account',
  'payment_bank_holder',
  'payment_instruction',
  'payment_admin_whatsapp',
  'payment_whatsapp_message'
);
```

---

# 12. Frontend JavaScript Flow

File:

```text id="8xoaie"
assets/js/order-status.js
```

State:

```js id="eh2fh7"
let orderData = null;
let orderCode = null;
```

Flow:

```text id="2jzvis"
1. Ambil code dari URL parameter.
2. Jika code kosong, tampilkan form cek order.
3. Jika code ada, tampilkan loading state.
4. Fetch /api/orders.php?code={code}.
5. Jika sukses, render detail order.
6. Jika gagal, render error state.
```

Submit form:

```text id="1me29d"
User input kode order
→ Trim input
→ Jika kosong, tampilkan error
→ Jika ada, redirect ke /order-status.php?code={kode}
```

---

# 13. UI Copywriting

Gunakan teks pendek dan jelas.

## Halaman tanpa kode

```text id="af3a27"
Judul:
Cek Status Pesanan

Subtitle:
Masukkan kode order kamu.

Input:
Kode Order

Button:
Cek Pesanan
```

## Loading

```text id="ztbkrx"
Memuat pesanan...
```

## Error

```text id="4dad3y"
Order tidak ditemukan.
```

## Pending

```text id="rypi37"
Menunggu Pembayaran

Selesaikan pembayaran lalu konfirmasi ke admin.
```

## Paid

```text id="eo2td7"
Pembayaran Diterima

Pesanan sedang diproses.
```

## Completed

```text id="mlvhuq"
Selesai

Pesanan sudah selesai.
```

## Cancelled

```text id="q4zl45"
Dibatalkan

Pesanan ini dibatalkan.
```

---

# 14. Tombol Aksi

Tombol berdasarkan status:

## Pending

```text id="i118zj"
- Lanjut ke Pembayaran
- Konfirmasi WhatsApp
- Kembali ke Katalog
```

## Paid

```text id="4bcbed"
- Hubungi Admin
- Kembali ke Katalog
```

## Completed

```text id="kibjup"
- Hubungi Admin
- Kembali ke Katalog
```

## Cancelled

```text id="5xzi5n"
- Hubungi Admin
- Kembali ke Katalog
```

---

# 15. WhatsApp Message

Template dari database:

```text id="lxfym0"
payment_whatsapp_message
```

Variable:

```text id="0va1hl"
{order_code}
{customer_name}
{total_amount}
```

Contoh template:

```text id="md0sti"
Halo admin, saya ingin mengecek pesanan {order_code}.
```

Contoh hasil:

```text id="zhle9h"
Halo admin, saya ingin mengecek pesanan ORD-20260624-A8K3.
```

Link:

```text id="92koiy"
https://wa.me/{payment_admin_whatsapp}?text={encoded_message}
```

---

# 16. Security Requirement

Wajib:

```text id="1wlrfy"
- API hanya menerima GET.
- Validasi order_code.
- Gunakan prepared statement.
- Jangan tampilkan error SQL.
- Jangan tampilkan ID internal.
- Escape output HTML.
- Batasi data yang dikirim ke publik.
```

Validasi order_code:

```text id="aflndv"
- Tidak kosong.
- Panjang wajar, misalnya maksimal 50 karakter.
- Format hanya huruf, angka, dan tanda hubung.
```

Regex sederhana:

```text id="n0xbbf"
^[A-Z0-9-]+$
```

Catatan keamanan:

```text id="kscogr"
Order status page bersifat publik berbasis kode order.
Maka order_code harus sulit ditebak.
Gunakan format tanggal + random, bukan urutan murni.
```

Contoh aman:

```text id="5vgjxm"
ORD-20260624-A8K3
```

Kurang aman:

```text id="o6n1ik"
ORD-001
```

---

# 17. Error dan Empty State

## Tanpa kode

```text id="rpv8xl"
Masukkan kode order untuk cek pesanan.
```

## Kode kosong saat submit

```text id="8ppsk3"
Kode order wajib diisi.
```

## Order tidak ditemukan

```text id="851g95"
Order tidak ditemukan.
```

## API gagal

```text id="6zn4sr"
Gagal memuat pesanan.
```

## Delivery note kosong

Jika status completed tapi delivery_note kosong:

```text id="jgotqa"
Pesanan selesai. Hubungi admin jika produk belum diterima.
```

---

# 18. File yang Perlu Dibuat / Diubah

File baru:

```text id="wk6gu8"
order-status.php
assets/js/order-status.js
```

File yang perlu diubah:

```text id="iaqvfb"
api/orders.php
payment.php
dashboard/api/orders.php
dashboard/orders.php
```

Alasan perubahan:

```text id="f15kyf"
api/orders.php:
Mengirim data order untuk halaman status.

payment.php:
Menambahkan tombol Cek Status.

dashboard/orders.php:
Pastikan status dan delivery_note yang diubah admin tampil ke user.
```

---

# 19. Integrasi dengan Dashboard

Saat admin mengubah status di:

```text id="5iazn7"
/dashboard/orders.php
```

Halaman status user otomatis membaca data terbaru dari:

```text id="8a0nlq"
/api/orders.php?code={order_code}
```

Tidak perlu push notification untuk MVP.

User cukup refresh atau buka ulang halaman status.

---

# 20. Acceptance Criteria

Fitur dianggap selesai jika:

```text id="ehx7ar"
1. User bisa membuka /order-status.php.
2. Jika tidak ada code, form input kode order tampil.
3. User bisa memasukkan kode order.
4. User diarahkan ke /order-status.php?code=...
5. Jika kode valid, detail order tampil.
6. Jika kode tidak ditemukan, error tampil.
7. Status order tampil dengan badge.
8. Item pesanan tampil.
9. Total pembayaran tampil.
10. Payment method tampil.
11. Tanggal order tampil.
12. Jika status pending, tombol lanjut pembayaran tampil.
13. Jika status pending, tombol konfirmasi WhatsApp tampil.
14. Jika status paid, instruksi pesanan diproses tampil.
15. Jika status completed, delivery_note tampil jika tersedia.
16. Jika status cancelled, pesan dibatalkan tampil.
17. Tombol kembali ke katalog tampil.
18. Data berasal dari database.
19. API menggunakan prepared statement.
20. Error SQL tidak pernah tampil ke user.
```

---

# 21. Urutan Implementasi

Urutan pengerjaan:

```text id="h1nmgd"
1. Pastikan /api/orders.php bisa mengambil order berdasarkan order_code.
2. Pastikan response API menyertakan items dan payment setting.
3. Buat order-status.php.
4. Buat assets/js/order-status.js.
5. Buat form input kode order.
6. Buat render detail order.
7. Buat mapping badge status.
8. Buat tombol lanjut pembayaran.
9. Buat tombol WhatsApp.
10. Buat delivery_note section.
11. Tambahkan tombol Cek Status di payment.php.
12. Test order pending.
13. Test order paid.
14. Test order completed + delivery_note.
15. Test order cancelled.
16. Test order tidak ditemukan.
```

---

# 22. Prioritas MVP

Minimum yang wajib ada:

```text id="tgcnrh"
- Input kode order
- Fetch order by code
- Tampil status
- Tampil detail produk
- Tampil total
- Tombol lanjut pembayaran
- Tombol WhatsApp
- Delivery note untuk order selesai
```

Yang bisa menyusul:

```text id="fp84xt"
- Timeline status
- Progress stepper
- Invoice PDF
- Upload bukti pembayaran
- Email status order
- Notifikasi otomatis
```

---

# 23. Kesimpulan

Halaman cek status pesanan adalah fitur wajib untuk MVP checkout manual. Tanpa halaman ini, user tidak punya tempat untuk memantau pesanan setelah membayar.

Untuk MVP, halaman ini cukup dibuat sederhana: input kode order, tampilkan status, tampilkan detail order, tampilkan tombol aksi, dan tampilkan delivery note jika pesanan selesai.
