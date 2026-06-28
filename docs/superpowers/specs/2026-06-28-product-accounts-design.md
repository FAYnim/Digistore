# Design: Manajemen Akun Premium per Produk

## Tujuan

Produk mewakili paket akun premium. Stok tidak diinput manual, tetapi berasal dari jumlah akun premium yang tersedia untuk produk tersebut.

## Keputusan

Gunakan tabel baru `product_accounts` untuk menyimpan data akun premium per produk. Admin mengisi akun lewat textarea bebas, sehingga satu baris/blok dapat berisi email, password, PIN, profil, atau format lain sesuai kebutuhan.

## Scope

### Dashboard Produk

- Hapus field input `stock` dari modal tambah/edit produk.
- Tambah textarea `Data Akun Premium` di modal produk.
- Admin dapat menambah banyak akun sekaligus dari textarea.
- Daftar produk tetap menampilkan stok, tetapi nilainya dihitung dari akun berstatus `available`.
- Saat edit produk, modal menampilkan daftar akun yang masih tersedia untuk produk tersebut.

### Database

- Tambah tabel `product_accounts`:
  - `id`
  - `product_id`
  - `account_data`
  - `status` (`available`, `sold`)
  - `order_id` nullable
  - `sold_at` nullable
  - `created_at`, `updated_at`
- `product_id` terhubung ke `products.id`.
- `products.stock` tetap dipertahankan untuk kompatibilitas, tetapi tidak menjadi sumber input admin.

### API Produk Admin

- Create/update produk menerima `accounts_text` opsional.
- Setiap akun dipisahkan dari textarea menjadi item `product_accounts` baru.
- API menghitung stok dari jumlah akun `available`.
- Response list/detail produk menyertakan stok hasil hitung.
- Validasi `stock` manual dihapus dari create/update produk.

### Checkout/Delivery

- Saat pembayaran/order sukses, sistem mengambil satu akun `available` dari `product_accounts` untuk produk yang dibeli.
- Akun ditandai `sold`, dihubungkan ke order, dan `sold_at` diisi.
- Akun yang sudah terjual tidak muncul lagi sebagai stok tersedia.
- Data akun tersedia untuk ditampilkan pada status order atau dikirim lewat flow existing.

## Data Flow

1. Admin membuat produk dan mengisi textarea akun.
2. API menyimpan produk tanpa stok manual.
3. API membuat baris `product_accounts` untuk setiap akun.
4. Stok produk dihitung dari jumlah akun `available`.
5. Pembeli checkout produk.
6. Setelah order sukses, sistem mengambil satu akun tersedia dan menandainya terjual.
7. Pembeli menerima data akun yang dialokasikan.

## Error Handling

- Jika textarea akun kosong, produk tetap bisa disimpan dengan stok 0.
- Jika tidak ada akun `available`, produk dianggap out of stock pada flow pembelian.
- Jika alokasi akun gagal saat order sukses, sistem tidak boleh mengirim akun duplikat.
- Operasi alokasi akun harus memilih hanya akun `available` dan segera menandainya `sold`.

## Testing

- Tambah produk dengan beberapa akun → stok sesuai jumlah akun.
- Tambah produk tanpa akun → stok 0.
- Edit produk tambah akun baru → stok bertambah.
- Checkout sukses → satu akun berubah dari `available` ke `sold`.
- Checkout saat akun habis → tidak ada akun dikirim dan stok 0.
- Produk lama tanpa `product_accounts` tetap tampil tanpa error.

## Non-Goals

- Parsing email/password/PIN menjadi field terpisah.
- Mengubah struktur kategori, slug, atau badge.
- Membuat inventory multi-quantity untuk satu akun.
- Mendesain ulang seluruh halaman order di luar kebutuhan delivery akun.
