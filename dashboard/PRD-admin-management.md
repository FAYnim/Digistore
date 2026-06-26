# PRD — Order Management Dashboard untuk MVP Katalog Produk Digital

## 1. Ringkasan

Fitur **Order Management** digunakan admin untuk melihat, mengecek, dan mengelola pesanan yang dibuat user melalui halaman checkout.

Sebelumnya, flow pembelian sudah dirancang:

```text
User pilih produk
→ User isi form checkout
→ Order tersimpan ke database
→ User diarahkan ke halaman pembayaran QRIS
→ User konfirmasi pembayaran manual via WhatsApp
```

Pada tahap ini, dashboard harus bisa membaca data order dari database dan memungkinkan admin mengubah status pesanan.

Target utama:

```text
Admin login
→ Buka halaman Pesanan
→ Lihat daftar order
→ Buka detail order
→ Cek pembayaran manual
→ Ubah status order
→ Isi catatan delivery
→ User bisa melihat status terbaru di halaman order-status.php
```

---

## 2. Tujuan

Tujuan fitur ini:

```text
1. Admin bisa melihat semua pesanan masuk.
2. Admin bisa mencari pesanan berdasarkan kode order atau nama customer.
3. Admin bisa memfilter pesanan berdasarkan status.
4. Admin bisa melihat detail pesanan.
5. Admin bisa mengubah status pesanan.
6. Admin bisa menambahkan catatan delivery.
7. Admin bisa membatalkan pesanan.
8. Data status terbaru bisa dilihat user di halaman status pesanan.
```

---

## 3. Scope

### Termasuk

```text
- Halaman daftar pesanan.
- Search order.
- Filter status order.
- Detail order.
- Ubah status order.
- Tambah/edit delivery note.
- Batalkan order.
- Badge status.
- API dashboard untuk order management.
- Update data orders di database.
- Integrasi dengan order-status.php.
```

### Tidak Termasuk

```text
- Payment gateway otomatis.
- Cek mutasi otomatis.
- Upload bukti pembayaran.
- Invoice PDF.
- Email otomatis.
- WhatsApp otomatis.
- Auto-delivery penuh.
- Refund management.
- Multi-admin approval.
```

---

## 4. Posisi Fitur dalam Sistem

Path dashboard:

```text
/dashboard/orders.php
```

API dashboard:

```text
/dashboard/api/orders.php
```

Public order status:

```text
/order-status.php?code=ORD-20260624-A8K3
```

Relasi flow:

```text
Checkout
→ Membuat order di database

Dashboard Orders
→ Membaca dan mengubah status order

Order Status Page
→ Menampilkan status terbaru ke user
```

---

# 5. Status Order

Status utama:

```text
pending
paid
completed
cancelled
```

Mapping UI:

```text
pending   → Menunggu Pembayaran
paid      → Dibayar
completed → Selesai
cancelled → Batal
```

Penjelasan:

```text
pending:
Order baru dibuat. User belum dikonfirmasi membayar.

paid:
Admin sudah mengecek pembayaran dan menandai pembayaran diterima.

completed:
Produk sudah dikirim atau akses sudah diberikan.

cancelled:
Order dibatalkan.
```

---

## 6. Flow Admin

## 6.1 Melihat Daftar Pesanan

Flow:

```text
Admin login
→ Buka /dashboard/orders.php
→ Sistem mengambil data order dari API
→ Tabel pesanan tampil
```

Data tabel:

```text
Kode
Customer
Produk
Total
Status
Tanggal
Aksi
```

Aksi:

```text
Detail
Ubah Status
Batalkan
```

---

## 6.2 Melihat Detail Order

Flow:

```text
Admin klik Detail
→ Modal/detail panel terbuka
→ Sistem mengambil detail order
→ Data order dan item tampil
```

Data detail:

```text
Kode order
Nama customer
Email customer
WhatsApp customer
Produk dibeli
Quantity
Harga
Subtotal
Total
Metode pembayaran
Status
Tanggal order
Catatan customer
Catatan delivery
```

---

## 6.3 Mengubah Status Order

Flow:

```text
Admin buka detail order
→ Pilih status baru
→ Klik Simpan
→ API update status order
→ Tabel diperbarui
→ User bisa cek status terbaru
```

Status yang bisa dipilih:

```text
Menunggu Pembayaran
Dibayar
Selesai
Batal
```

Aturan:

```text
pending → paid
pending → cancelled
paid → completed
paid → cancelled
completed → tidak disarankan diubah
cancelled → tidak disarankan diubah
```

Untuk MVP, sistem boleh tetap mengizinkan admin mengubah status apa pun, tetapi tampilkan konfirmasi jika status sudah `completed` atau `cancelled`.

---

## 6.4 Menambahkan Delivery Note

Flow:

```text
Admin buka detail order
→ Isi delivery note
→ Klik Simpan
→ delivery_note tersimpan ke database
→ User melihat delivery_note di halaman status pesanan
```

Isi delivery note bisa berupa:

```text
- Link download
- Akun/password
- Instruksi klaim
- Pesan bahwa produk sudah dikirim via WhatsApp
```

Contoh:

```text
Produk sudah dikirim melalui WhatsApp. Silakan cek pesan dari admin.
```

Atau:

```text
Link download: https://example.com/file.zip
Password ZIP: 12345
```

Catatan keamanan:

```text
Jika delivery_note berisi akun/password, tampilkan hanya di halaman status order dengan order_code yang benar.
```

---

## 6.5 Membatalkan Order

Flow:

```text
Admin klik Batalkan
→ Modal konfirmasi tampil
→ Admin klik Ya, Batalkan
→ Status order berubah menjadi cancelled
```

Pesan konfirmasi:

```text
Batalkan pesanan ini?
```

Tidak perlu teks panjang.

---

# 7. UI Halaman Orders

## 7.1 Layout Desktop

Desktop-first.

```text
+------------------------------------------------------+
| Sidebar | Topbar                                     |
|         |--------------------------------------------|
|         | Pesanan                                    |
|         | [Search] [Filter Status]                   |
|         |                                            |
|         | +----------------------------------------+ |
|         | | Tabel Order                            | |
|         | +----------------------------------------+ |
|         |                                            |
+------------------------------------------------------+
```

---

## 7.2 Layout Mobile

Mobile tetap usable.

```text
Topbar
Filter
Search
Tabel scroll horizontal
Detail order sebagai modal full screen
```

---

## 7.3 Header Halaman

Judul:

```text
Pesanan
```

Subtitle:

```text
Kelola order masuk.
```

Aksi kanan:

```text
Refresh
```

Tidak perlu tombol tambah order karena order dibuat dari checkout publik.

---

## 7.4 Filter dan Search

Search input:

```text
Cari order...
```

Search berdasarkan:

```text
order_code
customer_name
customer_phone
customer_email
```

Filter status:

```text
Semua Status
Menunggu
Dibayar
Selesai
Batal
```

---

## 7.5 Tabel Order

Kolom:

```text
Kode
Customer
Produk
Total
Status
Tanggal
Aksi
```

Contoh row:

```text
ORD-20260624-A8K3
Faris
Google AI Pro 12 Bulan
Rp25.000
Menunggu
24 Jun 2026
Detail
```

Jika order memiliki lebih dari satu produk, tampilkan:

```text
3 produk
```

Namun untuk MVP direct checkout, biasanya satu order berisi satu produk.

---

# 8. Detail Order UI

Detail order bisa dibuat sebagai modal atau side panel.

Rekomendasi MVP:

```text
Gunakan modal besar.
```

Isi modal:

```text
1. Header detail
2. Info customer
3. Item pesanan
4. Pembayaran
5. Status order
6. Delivery note
7. Aksi admin
```

---

## 8.1 Header Detail

Tampilan:

```text
Order Detail
ORD-20260624-A8K3
```

Badge status di kanan:

```text
Menunggu Pembayaran
```

---

## 8.2 Info Customer

Field:

```text
Nama
Email
WhatsApp
Catatan
```

Tombol:

```text
Hubungi WhatsApp
```

Link WhatsApp:

```text
https://wa.me/{customer_phone}
```

---

## 8.3 Item Pesanan

Tabel kecil:

```text
Produk
Qty
Harga
Subtotal
```

Contoh:

```text
Google AI Pro 12 Bulan
1
Rp25.000
Rp25.000
```

---

## 8.4 Pembayaran

Data:

```text
Metode: QRIS
Total: Rp25.000
Tanggal Order
Deadline Pembayaran
```

Untuk MVP, belum ada bukti bayar.

---

## 8.5 Status Order Form

Input:

```text
Status
```

Pilihan:

```text
Menunggu Pembayaran
Dibayar
Selesai
Batal
```

Button:

```text
Simpan Status
```

---

## 8.6 Delivery Note Form

Label:

```text
Delivery Note
```

Placeholder:

```text
Link, akun, atau catatan pengiriman.
```

Button:

```text
Simpan Catatan
```

---

# 9. Database

Fitur ini menggunakan tabel:

```text
orders
order_items
products
```

Tabel utama:

```text
orders
```

Kolom yang dipakai:

```text
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

Tabel detail:

```text
order_items
```

Kolom yang dipakai:

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

---

## 9.1 Tambahan Kolom Jika Belum Ada

Pastikan tabel `orders` memiliki:

```sql
ALTER TABLE orders
ADD COLUMN payment_deadline DATETIME DEFAULT NULL AFTER payment_method,
ADD COLUMN delivery_note TEXT DEFAULT NULL AFTER note;
```

Opsional untuk tracking admin:

```sql
ALTER TABLE orders
ADD COLUMN paid_at DATETIME DEFAULT NULL AFTER status,
ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER paid_at,
ADD COLUMN cancelled_at DATETIME DEFAULT NULL AFTER completed_at;
```

Rekomendasi MVP:

```text
payment_deadline dan delivery_note wajib.
paid_at, completed_at, cancelled_at opsional tapi bagus.
```

---

# 10. API Dashboard Orders

File:

```text
/dashboard/api/orders.php
```

API ini wajib diproteksi login admin.

---

## 10.1 GET Orders

Endpoint:

```text
GET /dashboard/api/orders.php
```

Query opsional:

```text
search
status
```

Contoh:

```text
/dashboard/api/orders.php?search=ORD-20260624&status=pending
```

Response:

```json
{
  "success": true,
  "message": "Pesanan berhasil dimuat",
  "data": [
    {
      "id": 1,
      "order_code": "ORD-20260624-A8K3",
      "customer_name": "Faris",
      "customer_email": "faris@email.com",
      "customer_phone": "6281234567890",
      "total_amount": 25000,
      "payment_method": "QRIS",
      "status": "pending",
      "items_summary": "Google AI Pro 12 Bulan",
      "items_count": 1,
      "created_at": "2026-06-24 12:00:00"
    }
  ]
}
```

SQL logic:

```text
Ambil order dari orders.
LEFT JOIN order_items.
Filter berdasarkan search jika ada.
Filter berdasarkan status jika ada.
Urutkan dari order terbaru.
```

---

## 10.2 GET Order Detail

Endpoint:

```text
GET /dashboard/api/orders.php?id=1
```

Atau:

```text
GET /dashboard/api/orders.php?code=ORD-20260624-A8K3
```

Response:

```json
{
  "success": true,
  "message": "Detail pesanan berhasil dimuat",
  "data": {
    "id": 1,
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
        "id": 1,
        "product_id": 1,
        "product_name": "Google AI Pro 12 Bulan",
        "quantity": 1,
        "price": 25000,
        "subtotal": 25000
      }
    ]
  }
}
```

---

## 10.3 PUT Update Order

Endpoint:

```text
PUT /dashboard/api/orders.php?id=1
```

Fungsi:

```text
Update status order dan delivery_note.
```

Body:

```json
{
  "status": "completed",
  "delivery_note": "Produk sudah dikirim melalui WhatsApp."
}
```

Response:

```json
{
  "success": true,
  "message": "Pesanan berhasil diperbarui",
  "data": null
}
```

Validasi:

```text
status hanya pending, paid, completed, cancelled
delivery_note opsional
order harus ditemukan
```

---

## 10.4 PATCH Update Status Saja

Opsional. Untuk MVP bisa cukup pakai PUT.

Endpoint opsional:

```text
PATCH /dashboard/api/orders.php?id=1
```

Body:

```json
{
  "status": "paid"
}
```

Rekomendasi:

```text
Untuk MVP, gunakan PUT saja agar sederhana.
```

---

# 11. Pengurangan Stok

Ini keputusan penting.

Rekomendasi MVP:

```text
Stok dikurangi saat status berubah menjadi paid.
```

Alasan:

```text
User pending belum tentu membayar.
Jika stok dikurangi saat pending, stok bisa habis oleh order palsu.
```

Flow:

```text
Admin ubah status pending → paid
→ Sistem mengurangi stok produk berdasarkan order_items
```

Aturan:

```text
- Jika status sudah pernah paid/completed, jangan kurangi stok lagi.
- Jika status dari pending ke paid, kurangi stok.
- Jika produk sudah dihapus, lewati pengurangan stok.
```

Agar aman, tambahkan kolom:

```sql
ALTER TABLE orders
ADD COLUMN stock_reduced TINYINT(1) DEFAULT 0 AFTER delivery_note;
```

Logic:

```text
Jika status baru paid atau completed
dan stock_reduced = 0
→ kurangi stok
→ set stock_reduced = 1
```

Catatan:

```text
Ini mencegah stok berkurang dua kali.
```

---

# 12. Integrasi dengan Halaman Status Pesanan

Saat admin mengubah status order, halaman publik:

```text
/order-status.php?code=ORD-20260624-A8K3
```

Harus menampilkan status terbaru.

Mapping:

```text
pending   → Menunggu Pembayaran
paid      → Pembayaran Diterima
completed → Selesai
cancelled → Dibatalkan
```

Jika `delivery_note` tersedia dan status `completed`:

```text
Tampilkan delivery_note.
```

Jika status belum completed:

```text
Jangan tampilkan delivery_note.
```

---

# 13. UI State

## 13.1 Loading State

Saat memuat order:

```text
Memuat pesanan...
```

Saat update status:

```text
Menyimpan...
```

---

## 13.2 Empty State

Jika tidak ada order:

```text
Belum ada pesanan.
```

Jika filter tidak menemukan hasil:

```text
Pesanan tidak ditemukan.
```

---

## 13.3 Error State

Jika API gagal:

```text
Gagal memuat pesanan.
```

Jika update gagal:

```text
Gagal menyimpan perubahan.
```

---

# 14. Badge Status

Gunakan warna konsisten.

```text
pending   → Kuning
paid      → Biru
completed → Hijau
cancelled → Merah / Abu-abu
```

Label UI:

```text
Menunggu
Dibayar
Selesai
Batal
```

---

# 15. Security Requirement

Wajib:

```text
- API orders dashboard wajib cek session admin.
- Gunakan prepared statement.
- Validasi status.
- Jangan percaya data dari frontend.
- Update order berdasarkan id valid.
- Jangan tampilkan error SQL.
- Escape output HTML.
- Batasi method API.
```

Poin penting:

```text
Hanya admin login yang boleh mengubah status order.
```

---

# 16. File yang Perlu Dibuat / Diubah

File utama:

```text
dashboard/orders.php
dashboard/api/orders.php
dashboard/assets/js/orders.js
```

File yang mungkin perlu diubah:

```text
dashboard/components/sidebar.php
api/orders.php
order-status.php
database/schema.sql
database/seed.sql
```

Tambahkan menu sidebar:

```text
Pesanan
```

---

# 17. JavaScript Dashboard Flow

State:

```js
let orders = [];
let selectedOrder = null;
let currentStatus = "all";
let currentSearch = "";
```

Flow load order:

```text
1. Fetch /dashboard/api/orders.php.
2. Simpan response ke state orders.
3. Render tabel.
4. Render empty state jika kosong.
```

Flow filter:

```text
1. Admin pilih status.
2. Fetch ulang dengan query status.
3. Render tabel.
```

Flow search:

```text
1. Admin mengetik keyword.
2. Fetch ulang dengan query search.
3. Render tabel.
```

Flow detail:

```text
1. Admin klik Detail.
2. Fetch /dashboard/api/orders.php?id=...
3. Buka modal.
4. Render detail order.
```

Flow update:

```text
1. Admin pilih status.
2. Admin isi delivery_note jika perlu.
3. Klik Simpan.
4. PUT /dashboard/api/orders.php?id=...
5. Jika sukses, tutup modal atau refresh detail.
6. Refresh tabel order.
```

---

# 18. Acceptance Criteria

Fitur dianggap selesai jika:

```text
1. Admin bisa membuka halaman Pesanan setelah login.
2. User non-login tidak bisa mengakses order API dashboard.
3. Tabel order menampilkan data dari database.
4. Admin bisa mencari order.
5. Admin bisa filter order berdasarkan status.
6. Admin bisa membuka detail order.
7. Detail order menampilkan customer, item, total, status, dan catatan.
8. Admin bisa mengubah status order.
9. Admin bisa mengisi delivery_note.
10. Status order tersimpan ke database.
11. delivery_note tersimpan ke database.
12. User bisa melihat status terbaru di order-status.php.
13. delivery_note tampil ke user jika order selesai.
14. Jika status berubah ke paid, stok produk berkurang satu kali.
15. API menggunakan prepared statement.
16. Error API tidak membocorkan query SQL.
17. Badge status tampil sesuai status.
18. Empty state tampil jika belum ada order.
19. Loading state tampil saat request berjalan.
20. Layout tetap usable di desktop dan mobile.
```

---

# 19. Urutan Implementasi

Urutan pengerjaan:

```text
1. Pastikan tabel orders dan order_items sudah siap.
2. Tambahkan kolom delivery_note jika belum ada.
3. Tambahkan kolom stock_reduced.
4. Buat /dashboard/api/orders.php.
5. Buat GET list orders.
6. Buat GET order detail.
7. Buat PUT update status dan delivery_note.
8. Tambahkan logic pengurangan stok saat paid.
9. Buat dashboard/orders.php.
10. Buat dashboard/assets/js/orders.js.
11. Render tabel order.
12. Buat filter dan search.
13. Buat modal detail order.
14. Buat form update status.
15. Buat form delivery note.
16. Integrasikan dengan order-status.php.
17. Test flow checkout → payment → dashboard order → status user.
```

---

# 20. Prioritas MVP

Minimum yang harus selesai:

```text
- Tabel order
- Detail order
- Update status
- Delivery note
- Proteksi API
- Status terbaru tampil di halaman user
```

Yang bisa menyusul:

```text
- Export order
- Print invoice
- Upload bukti pembayaran
- Auto WhatsApp notification
- Refund flow
- Filter tanggal kompleks
- Analytics order
```

---

## 21. Kesimpulan

Order management adalah pusat operasional MVP toko digital. Tanpa fitur ini, order memang bisa masuk, tetapi admin tidak punya tempat yang rapi untuk memprosesnya.

Untuk MVP siap jual, order management minimal harus bisa menampilkan daftar order, melihat detail, mengubah status, dan mengisi delivery note. Itu sudah cukup untuk menjalankan sistem checkout manual dengan QRIS dan konfirmasi WhatsApp.
