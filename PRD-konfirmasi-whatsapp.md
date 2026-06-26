# PRD — Konfirmasi WhatsApp untuk MVP Katalog Produk Digital

## 1. Ringkasan

Fitur **Konfirmasi WhatsApp** digunakan agar pembeli bisa menghubungi admin setelah membuat pesanan dan melakukan pembayaran manual.

Flow utama:

```text
User checkout
→ Order tersimpan ke database
→ User diarahkan ke payment.php
→ QRIS tampil
→ User bayar manual
→ User klik Konfirmasi WhatsApp
→ WhatsApp terbuka dengan pesan otomatis
→ Admin mengecek pembayaran
→ Admin update status order di dashboard
```

Fitur ini tidak mengirim WhatsApp otomatis dari server. Sistem hanya membuat link WhatsApp dengan template pesan yang sudah diisi otomatis.

---

## 2. Tujuan

Tujuan fitur:

```text
1. Memudahkan user konfirmasi pembayaran.
2. Mengurangi salah format pesan ke admin.
3. Membantu admin mengenali order berdasarkan order_code.
4. Membuat flow pembayaran manual lebih rapi.
5. Menghubungkan payment page dan order status page dengan WhatsApp admin.
6. Menggunakan nomor WhatsApp dan template pesan dari dashboard setting pembayaran.
```

---

## 3. Scope

### Termasuk

```text
- Tombol Konfirmasi WhatsApp di payment.php.
- Tombol Hubungi Admin di order-status.php.
- Template pesan WhatsApp dari database.
- Nomor WhatsApp admin dari database.
- Auto-fill order_code.
- Auto-fill customer_name.
- Auto-fill total_amount.
- Encode pesan agar aman digunakan di URL.
- Fallback pesan default jika template kosong.
```

### Tidak Termasuk

```text
- Kirim WhatsApp otomatis dari backend.
- WhatsApp Business API.
- Bot WhatsApp.
- Tracking apakah user benar-benar mengirim pesan.
- Upload bukti pembayaran.
- Auto update status order.
- Auto cek mutasi pembayaran.
```

---

## 4. Posisi Fitur dalam Sistem

Halaman yang memakai fitur ini:

```text
/payment.php?code=ORD-20260624-A8K3
/order-status.php?code=ORD-20260624-A8K3
```

Data berasal dari:

```text
store_settings
orders
order_items
```

Setting yang dipakai:

```text
payment_admin_whatsapp
payment_whatsapp_message
```

---

# 5. Flow User

## 5.1 Dari Halaman Payment

Flow:

```text
User berhasil checkout
→ payment.php tampil
→ User melihat QRIS
→ User bayar manual
→ User klik Konfirmasi WhatsApp
→ WhatsApp terbuka
→ Pesan otomatis sudah berisi kode order
```

Tombol:

```text
Konfirmasi WhatsApp
```

Contoh pesan:

```text
Halo admin, saya sudah membuat pesanan ORD-20260624-A8K3. Mohon dicek.
```

---

## 5.2 Dari Halaman Status Pesanan

Flow:

```text
User buka order-status.php
→ Status order tampil
→ User klik Hubungi Admin / Konfirmasi WhatsApp
→ WhatsApp terbuka dengan pesan otomatis
```

Tombol berdasarkan status:

```text
pending   → Konfirmasi WhatsApp
paid      → Hubungi Admin
completed → Hubungi Admin
cancelled → Hubungi Admin
```

---

# 6. Data yang Dibutuhkan

## 6.1 Dari orders

```text
order_code
customer_name
customer_phone
total_amount
status
created_at
```

## 6.2 Dari store_settings

```text
payment_admin_whatsapp
payment_whatsapp_message
```

---

# 7. Store Settings

Tambahkan atau pastikan key berikut tersedia:

```text
payment_admin_whatsapp
payment_whatsapp_message
```

Seed default:

```sql
INSERT INTO store_settings (setting_key, setting_value) VALUES
('payment_admin_whatsapp', '6281234567890'),
('payment_whatsapp_message', 'Halo admin, saya sudah membuat pesanan {order_code}. Nama: {customer_name}. Total: {total_amount}. Mohon dicek.');
```

Jika key sudah ada, jangan insert ulang. Gunakan update atau upsert.

---

# 8. Template Pesan WhatsApp

Template pesan dapat diedit admin dari dashboard Setting Pembayaran.

Default template:

```text
Halo admin, saya sudah membuat pesanan {order_code}. Nama: {customer_name}. Total: {total_amount}. Mohon dicek.
```

Variable yang didukung:

```text
{order_code}
{customer_name}
{total_amount}
{status}
```

Contoh data:

```text
order_code: ORD-20260624-A8K3
customer_name: Faris
total_amount: Rp25.000
status: Menunggu Pembayaran
```

Hasil pesan:

```text
Halo admin, saya sudah membuat pesanan ORD-20260624-A8K3. Nama: Faris. Total: Rp25.000. Mohon dicek.
```

---

# 9. Format Nomor WhatsApp

Nomor WhatsApp harus disimpan dalam format internasional tanpa simbol.

Format benar:

```text
6281234567890
```

Format salah:

```text
081234567890
+62 812-3456-7890
62 812 3456 7890
```

Validasi:

```text
- Hanya angka.
- Minimal 10 digit.
- Maksimal 15 digit.
- Disarankan diawali 62 untuk Indonesia.
```

Jika admin memasukkan `08xxxxxxxx`, sistem boleh otomatis mengubah menjadi `628xxxxxxxx`.

---

# 10. Format Link WhatsApp

Format link:

```text
https://wa.me/{nomor}?text={pesan}
```

Contoh:

```text
https://wa.me/6281234567890?text=Halo%20admin%2C%20saya%20sudah%20membuat%20pesanan%20ORD-20260624-A8K3.
```

Pesan wajib di-encode dengan:

```js
encodeURIComponent(message)
```

---

# 11. Public API Impact

API order detail perlu mengirim data payment termasuk nomor dan template WhatsApp.

Endpoint:

```text
GET /api/orders.php?code=ORD-20260624-A8K3
```

Response bagian payment:

```json
{
  "payment": {
    "admin_whatsapp": "6281234567890",
    "whatsapp_message": "Halo admin, saya sudah membuat pesanan ORD-20260624-A8K3. Nama: Faris. Total: Rp25.000. Mohon dicek."
  }
}
```

Catatan:

```text
API sebaiknya sudah mengirim pesan yang final, bukan template mentah.
Namun frontend juga boleh membangun pesan sendiri jika data lengkap tersedia.
```

Rekomendasi MVP:

```text
Backend mengirim template mentah dan data order.
Frontend membentuk link WhatsApp.
```

Alasan:

```text
Lebih fleksibel untuk payment.php dan order-status.php.
```

---

# 12. Helper JavaScript

Buat helper di:

```text
assets/js/whatsapp.js
```

Fungsi utama:

```js
function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0
  }).format(value);
}

function normalizeWhatsAppNumber(number) {
  if (!number) return "";

  let cleaned = String(number).replace(/\D/g, "");

  if (cleaned.startsWith("0")) {
    cleaned = "62" + cleaned.substring(1);
  }

  return cleaned;
}

function buildWhatsAppMessage(template, order) {
  const fallbackTemplate = "Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.";

  const selectedTemplate = template || fallbackTemplate;

  return selectedTemplate
    .replaceAll("{order_code}", order.order_code || "")
    .replaceAll("{customer_name}", order.customer_name || "")
    .replaceAll("{total_amount}", formatRupiah(order.total_amount || 0))
    .replaceAll("{status}", order.status_label || order.status || "");
}

function buildWhatsAppLink(number, message) {
  const cleanNumber = normalizeWhatsAppNumber(number);
  const encodedMessage = encodeURIComponent(message);

  return `https://wa.me/${cleanNumber}?text=${encodedMessage}`;
}
```

---

# 13. UI di Payment Page

Halaman:

```text
/payment.php?code=ORD-20260624-A8K3
```

Tombol utama:

```text
Konfirmasi WhatsApp
```

Letak:

```text
Di bawah instruksi pembayaran.
Di dekat QRIS.
```

Copywriting singkat:

```text
Sudah bayar? Konfirmasi ke admin.
```

Tombol:

```text
Konfirmasi WhatsApp
```

Behavior:

```text
Klik tombol
→ Buka link WhatsApp di tab baru
```

HTML behavior:

```html
<a href="{whatsapp_link}" target="_blank" rel="noopener">
  Konfirmasi WhatsApp
</a>
```

---

# 14. UI di Order Status Page

Halaman:

```text
/order-status.php?code=ORD-20260624-A8K3
```

Tombol:

```text
Hubungi Admin
```

Untuk status pending, label tombol lebih spesifik:

```text
Konfirmasi WhatsApp
```

Untuk status lain:

```text
Hubungi Admin
```

Pesan bisa disesuaikan:

## Pending

```text
Halo admin, saya sudah membuat pesanan {order_code}. Nama: {customer_name}. Total: {total_amount}. Mohon dicek.
```

## Paid

```text
Halo admin, saya ingin menanyakan pesanan {order_code}.
```

## Completed

```text
Halo admin, saya ingin menanyakan pesanan {order_code}.
```

## Cancelled

```text
Halo admin, saya ingin menanyakan pesanan {order_code} yang dibatalkan.
```

Rekomendasi MVP:

```text
Gunakan satu template global dari setting pembayaran.
```

Lebih sederhana dan cukup.

---

# 15. Dashboard Impact

Fitur ini bergantung pada halaman:

```text
/dashboard/settings-payment.php
```

Field yang wajib ada:

```text
Nomor WhatsApp Admin
Template Pesan
```

Validasi saat admin menyimpan:

```text
- Nomor WhatsApp wajib.
- Nomor WhatsApp hanya angka.
- Template pesan wajib.
- Template sebaiknya mengandung {order_code}.
```

Jika template tidak mengandung `{order_code}`, tampilkan warning ringan:

```text
Disarankan memakai {order_code}.
```

Namun tetap boleh disimpan.

---

# 16. Error Handling

## Nomor WhatsApp Kosong

Jika nomor admin kosong:

```text
Tombol WhatsApp disembunyikan.
```

Atau tampilkan:

```text
WhatsApp admin belum tersedia.
```

Rekomendasi:

```text
Sembunyikan tombol dan tampilkan teks kecil.
```

## Template Kosong

Gunakan fallback:

```text
Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.
```

## Order Tidak Ditemukan

Jangan tampilkan tombol WhatsApp berbasis order.

Tampilkan:

```text
Order tidak ditemukan.
```

---

# 17. Security Requirement

Requirement:

```text
- Jangan mengeksekusi isi template sebagai HTML.
- Tampilkan template sebagai teks biasa.
- Encode pesan dengan encodeURIComponent.
- Escape output saat render ke HTML.
- Jangan masukkan data sensitif ke pesan WhatsApp.
- Jangan kirim ID internal database.
```

Data yang aman masuk pesan:

```text
order_code
customer_name
total_amount
status
```

Data yang tidak perlu masuk pesan:

```text
admin_id
internal order id
session data
query error
```

---

# 18. Acceptance Criteria

Fitur dianggap selesai jika:

```text
1. Admin bisa mengatur nomor WhatsApp dari dashboard.
2. Admin bisa mengatur template pesan dari dashboard.
3. Nomor WhatsApp tersimpan ke store_settings.
4. Template pesan tersimpan ke store_settings.
5. payment.php menampilkan tombol Konfirmasi WhatsApp.
6. order-status.php menampilkan tombol WhatsApp.
7. Link WhatsApp menggunakan nomor admin dari database.
8. Pesan otomatis berisi order_code.
9. Pesan otomatis bisa berisi customer_name dan total_amount.
10. Pesan di-encode dengan benar.
11. Nomor 08 bisa dinormalisasi menjadi 62.
12. Jika template kosong, fallback dipakai.
13. Jika nomor WhatsApp kosong, tombol tidak error.
14. Link terbuka di tab baru.
15. Tidak ada data internal database dalam pesan.
```

---

# 19. Urutan Implementasi

Urutan pengerjaan:

```text
1. Pastikan store_settings punya payment_admin_whatsapp.
2. Pastikan store_settings punya payment_whatsapp_message.
3. Tambahkan field di dashboard/settings-payment.php.
4. Update dashboard/api/payment-settings.php agar bisa simpan nomor dan template.
5. Buat helper assets/js/whatsapp.js.
6. Update payment.php untuk membuat tombol Konfirmasi WhatsApp.
7. Update order-status.php untuk membuat tombol Hubungi Admin.
8. Test nomor format 62.
9. Test nomor format 08.
10. Test template dengan {order_code}.
11. Test template kosong.
12. Test order pending, paid, completed, cancelled.
```

---

# 20. Prioritas MVP

Minimum yang harus ada:

```text
- Nomor WhatsApp admin
- Template pesan
- Tombol Konfirmasi WhatsApp di payment.php
- Tombol Hubungi Admin di order-status.php
- Pesan otomatis berisi order_code
```

Yang bisa menyusul:

```text
- Template berbeda per status
- Upload bukti pembayaran
- WhatsApp Business API
- Bot admin
- Auto update status dari webhook
```

---

# 21. Kesimpulan

Fitur konfirmasi WhatsApp membuat flow pembayaran manual lebih siap dipakai. User tidak perlu mengetik format pesan sendiri, admin lebih mudah mengenali order, dan sistem tetap sederhana tanpa integrasi WhatsApp Business API.

Untuk MVP, cukup gunakan link `wa.me` dengan nomor admin dari database dan pesan otomatis berisi `order_code`.
