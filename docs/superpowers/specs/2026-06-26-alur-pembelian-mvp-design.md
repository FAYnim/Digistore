# Design — MVP Alur Pembelian Landing Page

## Scope
Implementasi mengikuti opsi A: MVP PRD untuk direct checkout produk digital dari landing page sampai status pesanan publik. Dashboard update status tetap memakai endpoint admin yang sudah ada, dengan penyesuaian ringan bila kolom baru diperlukan.

## Data Model
Tabel `orders` ditambah `payment_deadline` dan `delivery_note`. `store_settings` ditambah key `payment_qris_image`, `payment_instruction`, dan `payment_whatsapp_message`. Order baru selalu dibuat dengan `status = pending`, `payment_method = QRIS`, dan total dihitung dari harga produk di database.

## Public API
`api/checkout.php` menerima `POST` JSON berisi `product_id`, `quantity`, `customer_name`, `customer_email`, `customer_phone`, dan `note`. API memvalidasi produk active, stok > 0, input wajib, email jika ada, lalu membuat `order_code` format `ORD-YYYYMMDD-RANDOM`, insert ke `orders` dan `order_items`, kemudian return redirect ke `payment.php`.

`api/orders.php` menerima `GET ?code=...`, mengambil order berdasarkan `order_code`, item order, dan setting pembayaran. Response hanya berisi data yang aman untuk user publik.

## Pages
`checkout.php` membaca slug dari `?product=...`, fetch `api/products.php?slug=...`, render ringkasan produk, form pembeli, total, dan submit ke `api/checkout.php`. Jika sukses redirect ke `payment.php?code=...`.

`payment.php` membaca `?code=...`, fetch `api/orders.php`, render detail pesanan, QRIS dummy/store setting, instruksi, tombol WhatsApp, tombol cek status, dan kembali katalog.

`order-status.php` tanpa code menampilkan form input kode order. Dengan code, halaman fetch detail order lalu menampilkan status, total, item, tanggal, dan `delivery_note` jika ada.

## Landing Page
Modal checkout dummy dihapus/dinonaktifkan. Tombol produk active mengarah ke `checkout.php?product={slug}`. Produk habis menampilkan tombol disabled `Habis`.

## Error Handling
Pesan singkat: produk tidak ditemukan, produk sedang habis, nama wajib diisi, WhatsApp wajib diisi, gagal membuat pesanan, order tidak ditemukan. Error SQL tidak ditampilkan ke user.

## Security
Semua query memakai prepared statement. Frontend tidak mengirim harga/nama produk. Backend menghitung harga, subtotal, total dari database. API checkout hanya POST; API order detail hanya GET.

## Verification
Test manual: landing → checkout → buat pesanan → payment → status. Cek row `orders` dan `order_items`. Cek produk habis ditolak. Cek status order berubah dari dashboard terlihat di halaman status.