# PRD — Admin Setup Pembayaran untuk MVP Katalog Produk Digital

## 1. Ringkasan

Fitur ini memungkinkan admin mengatur informasi pembayaran dari dashboard, sehingga halaman pembayaran tidak perlu diedit langsung dari kode.

Sebelumnya, setelah user checkout:

```text id="gtfj80"
User isi form checkout
→ Order tersimpan ke database
→ User diarahkan ke payment.php
→ QRIS dummy tampil
```

Dengan fitur ini, QRIS, instruksi pembayaran, WhatsApp admin, dan rekening manual dapat diatur langsung dari dashboard.

Target utama:

```text id="db3mxt"
Admin membuka dashboard
→ Masuk ke Setting Pembayaran
→ Mengisi QRIS / rekening / instruksi
→ Simpan
→ Data pembayaran tampil otomatis di payment.php
```

Pembayaran tetap manual. Belum ada payment gateway otomatis.

---

## 2. Tujuan

Tujuan fitur:

```text id="f3p7lk"
1. Admin bisa mengatur QRIS dari dashboard.
2. Admin bisa mengatur instruksi pembayaran.
3. Admin bisa mengatur WhatsApp konfirmasi.
4. Admin bisa mengatur rekening bank opsional.
5. Halaman payment.php mengambil data pembayaran dari database.
6. Admin tidak perlu mengedit kode untuk mengganti informasi pembayaran.
7. Sistem siap dipakai untuk checkout manual.
```

---

## 3. Scope

### Termasuk

```text id="9g65uw"
- Halaman Setting Pembayaran di dashboard.
- Form QRIS image URL.
- Preview QRIS.
- Form instruksi pembayaran.
- Form WhatsApp admin.
- Form rekening bank manual.
- Toggle aktif/nonaktif metode pembayaran.
- Simpan data pembayaran ke database.
- API dashboard untuk membaca dan menyimpan payment setting.
- Payment page membaca setting pembayaran dari database.
```

### Tidak Termasuk

```text id="ho9s7v"
- Payment gateway otomatis.
- Cek pembayaran otomatis.
- Upload bukti pembayaran.
- Callback payment.
- Mutasi rekening otomatis.
- Invoice PDF.
- Multi-currency.
```

---

## 4. Posisi Fitur dalam Sistem

Path dashboard:

```text id="ohrx0y"
/dashboard/settings-payment.php
```

API dashboard:

```text id="w7b5fk"
/dashboard/api/payment-settings.php
```

Digunakan oleh public payment page:

```text id="e6aoxf"
/payment.php?code=ORD-20260624-A8K3
```

Relasi flow:

```text id="ybq4fz"
Admin mengatur pembayaran
→ Data disimpan ke database
→ User checkout
→ Order tersimpan
→ payment.php menampilkan QRIS dan instruksi dari database
```

---

## 5. UI Dashboard — Setting Pembayaran

Halaman dibuat sederhana, tidak penuh teks panjang.

Judul halaman:

```text id="pzt9iz"
Setting Pembayaran
```

Subtitle singkat:

```text id="wbwuas"
Atur QRIS dan instruksi pembayaran.
```

Layout desktop:

```text id="t7d5q3"
Kiri:
Form setting pembayaran

Kanan:
Preview tampilan pembayaran
```

Layout mobile:

```text id="j9ocwj"
1 kolom:
Form
Preview
```

---

## 6. Section UI

## 6.1 Section QRIS

Field:

```text id="pkcuhs"
QRIS Image URL
Status QRIS
```

Contoh input:

```text id="beycu5"
https://placehold.co/400x400?text=QRIS+Dummy
```

Preview:

```text id="b3ihvw"
- Menampilkan gambar QRIS.
- Jika kosong, tampilkan fallback QRIS dummy.
```

Fallback:

```text id="b9zklr"
https://placehold.co/400x400?text=QRIS+Dummy
```

Toggle:

```text id="zbsbwu"
Aktif / Nonaktif
```

Behavior:

```text id="oea4co"
Jika QRIS aktif, payment.php menampilkan QRIS.
Jika QRIS nonaktif, payment.php tidak menampilkan QRIS.
```

---

## 6.2 Section Rekening Bank

Field:

```text id="e8z18i"
Nama Bank
Nomor Rekening
Nama Pemilik
Status Bank Transfer
```

Contoh:

```text id="9wuok6"
Bank: BCA
Nomor Rekening: 1234567890
Nama Pemilik: Digital Store
```

Status:

```text id="gafcgp"
Aktif / Nonaktif
```

Catatan:

```text id="20z2tj"
Untuk MVP, bank transfer opsional.
QRIS tetap menjadi metode utama.
```

---

## 6.3 Section Instruksi Pembayaran

Field:

```text id="pw14kn"
Instruksi Pembayaran
```

Contoh isi default:

```text id="gdwiig"
Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.
```

Tampilan di payment.php:

```text id="jtzsm6"
1. Scan QRIS.
2. Bayar sesuai total.
3. Simpan bukti pembayaran.
4. Konfirmasi ke admin.
```

Rekomendasi UI:

```text id="lzx751"
Gunakan textarea pendek.
Jangan buat editor panjang.
```

---

## 6.4 Section WhatsApp Konfirmasi

Field:

```text id="vp66eg"
Nomor WhatsApp Admin
Template Pesan
```

Contoh nomor:

```text id="bszs7c"
6281234567890
```

Contoh template:

```text id="zrno1q"
Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.
```

Variable yang bisa dipakai:

```text id="0h1hdy"
{order_code}
{customer_name}
{total_amount}
```

Contoh hasil:

```text id="y40nug"
Halo admin, saya sudah membuat pesanan ORD-20260624-A8K3. Mohon dicek.
```

---

## 6.5 Section Preview

Preview menampilkan simulasi kartu pembayaran.

Isi preview:

```text id="p4fzqu"
- QRIS
- Total dummy
- Instruksi pembayaran
- Tombol Konfirmasi WhatsApp
```

Total dummy:

```text id="27zy1k"
Rp25.000
```

Tujuan preview:

```text id="z0b1ij"
Admin bisa melihat kira-kira tampilan payment page tanpa membuka order asli.
```

---

# 7. Struktur Database

Untuk MVP, ada dua opsi.

## Opsi A — Simpan di store_settings

Lebih cepat dan cocok untuk MVP.

Tabel:

```text id="vfv61t"
store_settings
```

Key yang dibutuhkan:

```text id="s6m2aa"
payment_qris_enabled
payment_qris_image
payment_bank_enabled
payment_bank_name
payment_bank_account
payment_bank_holder
payment_instruction
payment_admin_whatsapp
payment_whatsapp_message
```

Seed default:

```sql id="zrkeuh"
INSERT INTO store_settings (setting_key, setting_value) VALUES
('payment_qris_enabled', '1'),
('payment_qris_image', 'https://placehold.co/400x400?text=QRIS+Dummy'),
('payment_bank_enabled', '0'),
('payment_bank_name', ''),
('payment_bank_account', ''),
('payment_bank_holder', ''),
('payment_instruction', 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.'),
('payment_admin_whatsapp', '6281234567890'),
('payment_whatsapp_message', 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.');
```

Rekomendasi:

```text id="va8tfp"
Gunakan opsi A untuk MVP.
```

---

## Opsi B — Buat tabel payment_methods

Lebih rapi untuk jangka panjang, terutama jika ingin banyak metode pembayaran.

Schema:

```sql id="k2rily"
CREATE TABLE payment_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  method_type ENUM('qris', 'bank') NOT NULL,
  name VARCHAR(100) NOT NULL,
  account_number VARCHAR(100) DEFAULT NULL,
  account_holder VARCHAR(100) DEFAULT NULL,
  image_url VARCHAR(255) DEFAULT NULL,
  instruction TEXT DEFAULT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Catatan:

```text id="x9pym2"
Opsi B lebih fleksibel, tetapi lebih banyak pekerjaan.
Untuk MVP awal, belum wajib.
```

---

# 8. API Dashboard

## 8.1 Get Payment Settings

Endpoint:

```text id="3hqbym"
GET /dashboard/api/payment-settings.php
```

Fungsi:

```text id="9pf2rn"
Mengambil setting pembayaran untuk form dashboard.
```

Response:

```json id="qz70av"
{
  "success": true,
  "message": "Setting pembayaran berhasil dimuat",
  "data": {
    "payment_qris_enabled": "1",
    "payment_qris_image": "https://placehold.co/400x400?text=QRIS+Dummy",
    "payment_bank_enabled": "0",
    "payment_bank_name": "",
    "payment_bank_account": "",
    "payment_bank_holder": "",
    "payment_instruction": "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.",
    "payment_admin_whatsapp": "6281234567890",
    "payment_whatsapp_message": "Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek."
  }
}
```

---

## 8.2 Update Payment Settings

Endpoint:

```text id="rs6ujw"
PUT /dashboard/api/payment-settings.php
```

Body:

```json id="i6zsro"
{
  "payment_qris_enabled": "1",
  "payment_qris_image": "https://placehold.co/400x400?text=QRIS+Dummy",
  "payment_bank_enabled": "0",
  "payment_bank_name": "",
  "payment_bank_account": "",
  "payment_bank_holder": "",
  "payment_instruction": "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.",
  "payment_admin_whatsapp": "6281234567890",
  "payment_whatsapp_message": "Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek."
}
```

Response sukses:

```json id="kg43di"
{
  "success": true,
  "message": "Setting pembayaran berhasil disimpan",
  "data": null
}
```

---

## 8.3 Validasi API

Validasi:

```text id="dtns6p"
- payment_qris_enabled hanya 0 atau 1.
- payment_bank_enabled hanya 0 atau 1.
- payment_qris_image wajib jika QRIS aktif.
- payment_admin_whatsapp wajib.
- payment_admin_whatsapp hanya angka.
- payment_instruction wajib.
- payment_bank_name wajib jika bank aktif.
- payment_bank_account wajib jika bank aktif.
- payment_bank_holder wajib jika bank aktif.
```

Penting:

```text id="b8u82e"
API ini wajib diproteksi auth admin.
User publik tidak boleh mengubah setting pembayaran.
```

---

# 9. Public API Impact

Payment page membutuhkan data pembayaran.

Ada dua pendekatan:

## Opsi A — Gabung di API order detail

Endpoint:

```text id="k3wb38"
/api/orders.php?code=ORD-20260624-A8K3
```

Response order menyertakan data payment:

```json id="rlezt9"
{
  "success": true,
  "message": "Order berhasil dimuat",
  "data": {
    "order_code": "ORD-20260624-A8K3",
    "total_amount": 25000,
    "status": "pending",
    "items": [],
    "payment": {
      "qris_enabled": true,
      "qris_image": "https://placehold.co/400x400?text=QRIS+Dummy",
      "bank_enabled": false,
      "bank_name": "",
      "bank_account": "",
      "bank_holder": "",
      "instruction": "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.",
      "admin_whatsapp": "6281234567890",
      "whatsapp_message": "Halo admin, saya sudah membuat pesanan ORD-20260624-A8K3. Mohon dicek."
    }
  }
}
```

Rekomendasi:

```text id="y7odjp"
Gunakan Opsi A untuk MVP.
```

Alasan:

```text id="4ayc1a"
payment.php cukup fetch satu endpoint.
Lebih sederhana.
```

---

## Opsi B — Buat API payment public terpisah

Endpoint:

```text id="yshil2"
/api/payment-settings.php
```

Catatan:

```text id="5dc02p"
Bisa digunakan nanti jika payment setting dibutuhkan di banyak halaman.
Untuk MVP belum wajib.
```

---

# 10. UI Payment Page Setelah Setting Aktif

Halaman:

```text id="1jh7ic"
/payment.php?code=ORD-20260624-A8K3
```

Komponen:

```text id="t1bq9u"
- Kode order
- Status order
- Total pembayaran
- QRIS jika aktif
- Rekening bank jika aktif
- Instruksi pembayaran
- Tombol Konfirmasi WhatsApp
- Tombol Cek Status
```

Jika QRIS aktif:

```text id="9hdh18"
Tampilkan QRIS image.
```

Jika bank aktif:

```text id="010zmg"
Tampilkan nama bank, nomor rekening, dan nama pemilik.
```

Jika semua metode pembayaran nonaktif:

```text id="zxpou9"
Tampilkan pesan:
Metode pembayaran belum tersedia. Hubungi admin.
```

---

# 11. UI Copywriting

Gunakan teks pendek.

## Dashboard

```text id="s4mskm"
Judul:
Setting Pembayaran

Subtitle:
Atur QRIS dan konfirmasi pembayaran.

Button:
Simpan

Success:
Setting tersimpan.

Error:
Gagal menyimpan setting.
```

## Payment Page

```text id="d46j8u"
Judul:
Pembayaran

Subtitle:
Selesaikan pembayaran manual.

Button:
Konfirmasi WhatsApp
Cek Status
```

Instruksi default:

```text id="tyzhca"
Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.
```

---

# 12. Flow Admin

```text id="a9h3lk"
Admin login
→ Buka Setting Pembayaran
→ Isi QRIS image URL
→ Isi WhatsApp admin
→ Isi instruksi pembayaran
→ Klik Simpan
→ API menyimpan ke store_settings
→ Preview diperbarui
```

Jika QRIS diganti:

```text id="hzv20s"
Admin update QRIS image URL
→ Simpan
→ Order berikutnya menampilkan QRIS baru
```

Catatan:

```text id="pp7vn4"
Order lama yang dibuka ulang juga akan menampilkan setting pembayaran terbaru.
Ini cukup untuk MVP.
```

---

# 13. Flow User

```text id="uqhwc0"
User checkout
→ Order tersimpan
→ User diarahkan ke payment.php
→ payment.php mengambil detail order
→ QRIS/rekening/instruksi tampil
→ User bayar manual
→ User klik Konfirmasi WhatsApp
```

---

# 14. File yang Perlu Dibuat

```text id="ool2o0"
dashboard/settings-payment.php
dashboard/api/payment-settings.php
dashboard/assets/js/payment-settings.js
```

File yang perlu diubah:

```text id="j86knz"
dashboard/components/sidebar.php
api/orders.php
payment.php
database/seed.sql
```

Tambahkan menu sidebar:

```text id="r8r7x7"
Pembayaran
```

---

# 15. Security Requirement

Wajib:

```text id="aqibn7"
- Endpoint dashboard/api/payment-settings.php wajib cek login admin.
- Gunakan prepared statement.
- Validasi semua input.
- Escape output saat render.
- Jangan tampilkan error SQL.
- Nomor WhatsApp disimpan dalam format angka.
```

Jika ada upload QRIS nanti:

```text id="dnja4q"
- File hanya jpg, jpeg, png, webp.
- Ukuran maksimal 2MB.
- Rename file otomatis.
- Simpan di /uploads/payments/.
- Jangan izinkan file PHP.
```

Untuk MVP cepat:

```text id="l5vbkz"
Gunakan QRIS Image URL dulu.
Upload QRIS bisa menyusul.
```

---

# 16. Acceptance Criteria

Fitur dianggap selesai jika:

```text id="qnk5rr"
1. Admin bisa membuka halaman Setting Pembayaran.
2. Halaman hanya bisa diakses setelah login.
3. Admin bisa mengisi QRIS image URL.
4. Admin bisa melihat preview QRIS.
5. Admin bisa mengaktifkan/nonaktifkan QRIS.
6. Admin bisa mengisi WhatsApp admin.
7. Admin bisa mengisi instruksi pembayaran.
8. Admin bisa mengisi data rekening bank opsional.
9. Setting tersimpan ke store_settings.
10. payment.php menampilkan QRIS dari database.
11. payment.php menampilkan instruksi dari database.
12. Tombol WhatsApp memakai nomor admin dari database.
13. Template pesan WhatsApp otomatis memasukkan order_code.
14. Jika QRIS kosong, tampil fallback QRIS dummy.
15. Jika API gagal, tampil error singkat.
16. Dashboard API pembayaran tidak bisa diakses tanpa login.
```

---

# 17. Urutan Implementasi

Urutan pengerjaan:

```text id="lthvyg"
1. Tambahkan seed setting pembayaran ke store_settings.
2. Buat dashboard/settings-payment.php.
3. Tambahkan menu Pembayaran di sidebar.
4. Buat dashboard/api/payment-settings.php.
5. Buat dashboard/assets/js/payment-settings.js.
6. Render data setting ke form.
7. Buat fitur simpan setting.
8. Buat preview QRIS di dashboard.
9. Update api/orders.php agar menyertakan data payment.
10. Update payment.php agar memakai data payment dari API.
11. Test QRIS aktif/nonaktif.
12. Test tombol WhatsApp.
13. Test akses API tanpa login.
```

---

# 18. Prioritas MVP

Minimum yang harus jadi:

```text id="co8zdy"
- QRIS image URL
- Instruksi pembayaran
- WhatsApp admin
- Simpan ke database
- Tampil di payment.php
```

Yang bisa menyusul:

```text id="s2btbu"
- Upload QRIS image
- Banyak metode pembayaran
- Mutasi otomatis
- Payment gateway
- Bukti pembayaran
```

---

# 19. Kesimpulan

Fitur setup pembayaran membuat admin bisa mengatur QRIS, instruksi, dan kontak konfirmasi tanpa mengubah kode.

Untuk MVP siap jual, cukup gunakan QRIS manual, instruksi singkat, dan tombol WhatsApp konfirmasi. Payment gateway otomatis belum diperlukan.
