# Design: Sembunyikan Manajemen Kategori untuk Fokus Akun Premium

## Tujuan

Proyek difokuskan sebagai toko akun premium. Admin tidak perlu mengelola kategori dan tidak perlu memilih kategori saat membuat atau mengedit produk.

## Keputusan

Kategori tetap dipertahankan di database dan API untuk kompatibilitas data lama serta kemungkinan ekspansi di masa depan. UI kategori disembunyikan dari dashboard. Backend otomatis memakai kategori default `Akun Premium` saat produk dibuat atau diperbarui tanpa `category_id`.

## Scope

### Dashboard

- Sembunyikan menu/akses halaman manajemen kategori.
- Sembunyikan filter kategori di daftar produk.
- Sembunyikan kolom kategori di tabel produk.
- Sembunyikan field kategori di form tambah/edit produk.
- Form produk tidak lagi mengirim `category_id`.

### API Produk Admin

- Saat create produk, jika `category_id` kosong atau tidak dikirim, API menggunakan kategori default `Akun Premium`.
- Saat update produk, jika `category_id` kosong atau tidak dikirim, API tetap menggunakan kategori default `Akun Premium`.
- Jika kategori default belum ada, API membuatnya dengan slug `akun-premium`, status aktif, dan metadata minimal sesuai skema yang tersedia.

### Database

- Tidak menghapus tabel/kolom kategori.
- Tidak melakukan migrasi destruktif.
- Data produk lama tetap valid.

## Data Flow

1. Admin membuka halaman produk.
2. UI hanya menampilkan data produk tanpa kontrol kategori.
3. Admin menyimpan produk tanpa `category_id`.
4. API memastikan kategori default `Akun Premium` tersedia.
5. Produk disimpan dengan `category_id` default.

## Error Handling

- Jika kategori default gagal dibuat/ditemukan, API mengembalikan error simpan produk seperti pola error saat ini.
- UI menampilkan pesan gagal dari API seperti flow produk existing.

## Testing

- Tambah produk baru tanpa memilih kategori → produk tersimpan.
- Edit produk tanpa field kategori → produk tetap tersimpan dengan kategori default.
- Filter/kolom/menu kategori tidak muncul di dashboard admin.
- API tetap kompatibel jika request lama masih mengirim `category_id` valid.

## Non-Goals

- Menghapus sistem kategori dari database.
- Mendesain ulang seluruh flow produk akun premium.
- Menambah tipe produk/subscription baru.
