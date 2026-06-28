# Product Backlog Final — Digital Store Launch Readiness

Dokumen ini digunakan untuk melacak pekerjaan sebelum produk siap digunakan dan dipasarkan.

## Status Prioritas

- **Critical**: wajib diselesaikan sebelum produk digunakan publik/berbayar.
- **High**: sangat penting untuk MVP siap jual.
- **Medium**: penting untuk operasional production.
- **Low**: peningkatan setelah launch.

---

# Fix

## 1. Fix schema admin login `last_login_at`

- **Status:** Selesai
- **Prioritas:** Critical
- **Kompleksitas:** Small
- **Dependensi:** Tidak ada
- **Tujuan:** Sinkronkan schema DB dengan proses login admin.
- **Alasan:** Login admin bisa gagal karena kode mengupdate kolom yang tidak ada.
- **Manfaat:** Dashboard admin stabil untuk penjual; operasional order tidak terganggu.

## 2. Perbaiki validasi payment settings sebelum go-live

- **Status:** Selesai
- **Prioritas:** Critical
- **Kompleksitas:** Small
- **Dependensi:** Tidak ada
- **Tujuan:** Hilangkan fallback dummy QRIS/payment dan wajibkan konfigurasi pembayaran valid.
- **Alasan:** Data pembayaran dummy berisiko membuat pembeli membayar ke informasi salah.
- **Manfaat:** Mengurangi risiko salah bayar dan meningkatkan trust pembeli.

## 3. Perbaiki manajemen stok saat checkout

- **Status:** Selesai
- **Prioritas:** Critical
- **Kompleksitas:** Medium
- **Dependensi:** Auto-expire order jika memakai reservasi stok
- **Tujuan:** Implementasikan pengurangan atau reservasi stok secara atomik saat order dibuat.
- **Alasan:** Stok saat ini hanya dicek, tidak dikurangi, sehingga rawan oversell.
- **Manfaat:** Stok lebih akurat; pembeli tidak membeli produk yang sudah habis.

## 4. Perbaiki flow tombol “Lihat Detail”

- **Status:** Selesai
- **Prioritas:** High
- **Kompleksitas:** Small
- **Dependensi:** Halaman detail produk
- **Tujuan:** Ubah tombol detail agar menuju halaman detail produk, bukan checkout langsung.
- **Alasan:** Pembeli belum mendapat informasi lengkap sebelum membeli.
- **Manfaat:** Keputusan beli lebih jelas dan konversi meningkat.

## 5. Perbaiki status order agar lebih representatif

- **Status:** Selesai
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Payment confirmation, order timeline
- **Tujuan:** Perluas status order agar menggambarkan proses nyata.
- **Alasan:** Status `pending`, `paid`, `completed`, `cancelled` terlalu kasar.
- **Manfaat:** Admin lebih mudah memproses order; pembeli tahu posisi order.

## 6. Perbaiki keamanan cek status order

- **Mark by Developer:** Belum terlalu penting untuk saat ini!
- **Status:** Belum
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Tidak ada
- **Tujuan:** Tambahkan verifikasi nomor WhatsApp/email sebagian dan masking data personal.
- **Alasan:** Order code saja dapat membuka data pribadi jika tersebar.
- **Manfaat:** Mengurangi risiko kebocoran data pembeli.

## 7. Perbaiki order expired handling

- **Status:** Selesai
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Manajemen stok saat checkout
- **Tujuan:** Batalkan order pending yang melewati deadline dan release stok.
- **Alasan:** Deadline pembayaran sudah ada, tetapi belum diproses sistem.
- **Manfaat:** Stok tidak terkunci dan status order lebih jelas.

## 8. Perbaiki dashboard order agar action-oriented

- **Status:** Sebagian
- **Belum:** Dashboard sudah memiliki filter status dan indikator konfirmasi pembayaran, tetapi belum memiliki tab/queue kerja admin yang jelas seperti perlu verifikasi, perlu delivery, bermasalah, dan selesai; belum ada prioritas aksi harian, bulk action, atau tampilan yang benar-benar memandu admin dari satu tahap proses ke tahap berikutnya.
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Status order baru
- **Tujuan:** Tambahkan filter/tab berdasarkan status kerja admin.
- **Alasan:** Order list belum memandu admin memproses order harian.
- **Manfaat:** Order lebih cepat diverifikasi dan dikirim.

## 9. Perbaiki security headers dan cookie production

- **Status:** Sebagian
- **Belum:** Cookie session sudah memakai konfigurasi lebih aman, tetapi belum ada security headers lengkap seperti Content-Security-Policy, Strict-Transport-Security, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, dan enforcement HTTPS production secara konsisten.
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Deployment production
- **Tujuan:** Tambahkan security headers, secure cookie, HTTPS enforcement, dan proteksi session.
- **Alasan:** Keamanan production belum lengkap.
- **Manfaat:** Risiko serangan dan pencurian session berkurang.

## 10. Perbaiki validasi dan sanitasi output delivery

- **Status:** Sebagian
- **Belum:** Delivery sudah dibatasi pada status tertentu dan output UI sudah di-escape, tetapi belum ada validasi akses tambahan sebelum data sensitif ditampilkan, belum ada token/verification gate khusus delivery, belum ada struktur field delivery yang aman, dan API masih berpotensi mengembalikan catatan delivery sensitif secara langsung.
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Delivery produk digital terstruktur
- **Tujuan:** Pastikan konten delivery hanya tampil untuk order valid dan aman dari injection.
- **Alasan:** Delivery digital dapat berisi data sensitif.
- **Manfaat:** Produk digital lebih aman diakses pembeli.

## 11. Perbaiki pagination admin product/order

- **Status:** Sebagian
- **Belum:** Search dan filter server-side sudah ada sebagian, tetapi endpoint produk dan order belum menerapkan pagination berbasis limit/offset atau page metadata, belum ada kontrol jumlah data per halaman, dan UI dashboard belum menyediakan navigasi halaman untuk data besar.
- **Prioritas:** Medium
- **Kompleksitas:** Medium
- **Dependensi:** Tidak ada
- **Tujuan:** Tambahkan pagination, search, dan filter server-side untuk produk dan order.
- **Alasan:** Query semua data akan lambat saat data bertambah.
- **Manfaat:** Dashboard tetap cepat dan stabil.

## 12. Perbaiki hard delete produk

- **Status:** Belum
- **Prioritas:** Medium
- **Kompleksitas:** Medium
- **Dependensi:** Tidak ada
- **Tujuan:** Ubah delete produk menjadi soft delete atau archive.
- **Alasan:** Hard delete dapat merusak konteks histori order.
- **Manfaat:** Data transaksi lebih aman dan histori order tetap konsisten.

---

# Feat

## 1. Halaman detail produk

- **Status:** Sebagian
- **Belum:** Halaman detail produk sudah tersedia dengan data dasar seperti nama, harga, stok, deskripsi, dan CTA beli, tetapi belum memiliki struktur konten lengkap untuk benefit, cara pakai, syarat penggunaan, FAQ, garansi/refund, trust element, dan informasi pendukung yang membantu pembeli mengambil keputusan sebelum checkout.
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Tidak ada
- **Tujuan:** Buat halaman detail berisi deskripsi lengkap, benefit, cara pakai, syarat, stok, harga, FAQ, garansi/refund, dan CTA beli.
- **Alasan:** Pembeli butuh informasi lengkap sebelum checkout.
- **Manfaat:** Meningkatkan trust dan konversi.

## 2. Payment confirmation internal

- **Status:** Sudah
- **Prioritas:** Critical
- **Kompleksitas:** Medium
- **Dependensi:** Status order baru
- **Tujuan:** Tambahkan form upload bukti bayar, nama pengirim, metode pembayaran, waktu bayar, dan catatan.
- **Alasan:** Konfirmasi via WhatsApp sulit diaudit dan tidak scalable.
- **Manfaat:** Pembayaran tercatat resmi; admin lebih mudah verifikasi.

## 3. Admin payment verification

- **Status:** Sudah
- **Prioritas:** Critical
- **Kompleksitas:** Medium
- **Dependensi:** Payment confirmation internal
- **Tujuan:** Tambahkan queue admin untuk menerima atau menolak konfirmasi pembayaran.
- **Alasan:** Bukti bayar perlu workflow verifikasi jelas.
- **Manfaat:** Proses pembayaran lebih terkontrol dan transparan.

## 4. Credential inventory produk digital

- **Status:** Sudah
- **Prioritas:** Critical
- **Kompleksitas:** Medium/Large
- **Dependensi:** Admin payment verification
- **Tujuan:** Tambahkan sistem stok berbasis credential/akun manual per produk sebelum delivery otomatis.
- **Alasan:** Produk akun premium perlu dibuat atau disiapkan manual oleh admin sebelum bisa dikirim ke pembeli.
- **Manfaat:** Stok lebih akurat, admin bisa mengelola akun siap jual, dan delivery otomatis punya sumber data yang aman.

## 5. Delivery produk digital terstruktur

- **Status:** Sebagian
- **Belum:** Sistem sudah dapat mengirim data akun dari inventory setelah pembayaran diterima, tetapi delivery masih berupa catatan/plain content, belum memiliki model delivery terstruktur per tipe produk, belum ada halaman akses delivery khusus yang aman, belum ada riwayat pengiriman/resend, dan belum ada kontrol akses tambahan untuk melindungi credential digital.
- **Prioritas:** Critical
- **Kompleksitas:** Medium/Large
- **Dependensi:** Credential inventory produk digital, Admin payment verification, validasi delivery
- **Tujuan:** Tambahkan sistem pengiriman produk digital setelah pembayaran diverifikasi.
- **Alasan:** Produk digital masih dikirim manual lewat catatan.
- **Manfaat:** Mengurangi kerja manual admin dan mempercepat pengiriman.

## 6. Order timeline pembeli

- **Status:** Sebagian
- **Belum:** Pembeli sudah melihat status order dan instruksi dasar, tetapi belum ada timeline event yang mencatat urutan kejadian seperti order dibuat, bukti bayar dikirim, pembayaran diverifikasi/ditolak, order diproses, produk dikirim, dan order selesai lengkap dengan waktu setiap event.
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Status order baru
- **Tujuan:** Tampilkan timeline order dari dibuat sampai produk dikirim.
- **Alasan:** Pembeli butuh visibilitas setelah checkout dan pembayaran.
- **Manfaat:** Mengurangi pertanyaan status order ke admin.

## 7. Auto-expire pending order

- **Status:** Selesai
- **Prioritas:** High
- **Kompleksitas:** Medium
- **Dependensi:** Manajemen stok saat checkout
- **Tujuan:** Tambahkan proses otomatis/manual terjadwal untuk membatalkan order melewati deadline.
- **Alasan:** Pending order tidak boleh menggantung.
- **Manfaat:** Stok dan laporan lebih bersih.

## 8. Setup checklist admin

- **Status:** Belum
- **Prioritas:** High
- **Kompleksitas:** Small/Medium
- **Dependensi:** Validasi payment settings
- **Tujuan:** Tambahkan checklist kesiapan toko: payment, WhatsApp, policy, produk aktif, kategori, delivery setting.
- **Alasan:** Admin perlu tahu toko sudah siap menerima order.
- **Manfaat:** Mengurangi kesalahan konfigurasi sebelum launch.

## 9. Policy pages

- **Status:** Belum
- **Prioritas:** High
- **Kompleksitas:** Small
- **Dependensi:** Tidak ada
- **Tujuan:** Tambahkan halaman refund policy, terms, privacy policy, dan support SLA.
- **Alasan:** Produk digital perlu aturan jelas untuk trust dan dispute handling.
- **Manfaat:** Pembeli paham hak dan batasan sebelum membeli.

## 10. Admin notification sederhana

- **Status:** Sebagian
- **Belum:** Dashboard sudah memiliki beberapa indikator seperti pending confirmation/recent orders, tetapi belum ada sistem notifikasi jelas untuk order baru, bukti bayar masuk, order perlu delivery, stok credential habis, atau order bermasalah; belum ada badge global, daftar notifikasi, atau mekanisme mark-as-read.
- **Prioritas:** Medium
- **Kompleksitas:** Medium
- **Dependensi:** Payment confirmation, order status
- **Tujuan:** Tambahkan indikator order baru, bukti bayar masuk, dan order perlu delivery.
- **Alasan:** Admin bisa melewatkan order jika cek manual.
- **Manfaat:** Order diproses lebih cepat.

## 11. Email/WhatsApp notification template

- **Status:** Sebagian
- **Belum:** Template WhatsApp dasar untuk pembayaran sudah ada, tetapi belum tersedia template lengkap untuk semua event transaksi seperti order dibuat, bukti bayar diterima, pembayaran diverifikasi, pembayaran ditolak, produk dikirim, order expired, dan order selesai; email template juga belum tersedia.
- **Prioritas:** Medium
- **Kompleksitas:** Medium
- **Dependensi:** Order timeline, delivery digital
- **Tujuan:** Tambahkan template notifikasi untuk order dibuat, bukti diterima, pembayaran diverifikasi, dan produk dikirim.
- **Alasan:** Komunikasi transaksi perlu konsisten.
- **Manfaat:** Mengurangi pesan manual dan meningkatkan trust pembeli.

## 12. Audit log admin

- **Status:** Belum
- **Prioritas:** Medium
- **Kompleksitas:** Medium
- **Dependensi:** Auth admin stabil
- **Tujuan:** Catat aktivitas penting admin.
- **Alasan:** Dibutuhkan untuk investigasi kesalahan operasional.
- **Manfaat:** Kontrol internal dan dispute handling lebih baik.

## 13. Export laporan order

- **Status:** Belum
- **Prioritas:** Medium
- **Kompleksitas:** Small/Medium
- **Dependensi:** Pagination/filter order
- **Tujuan:** Tambahkan export CSV untuk order, pembayaran, dan produk terjual.
- **Alasan:** Penjual butuh laporan untuk pembukuan.
- **Manfaat:** Rekonsiliasi bisnis lebih mudah.

## 14. Product SEO metadata

- **Status:** Sebagian
- **Belum:** Produk sudah memiliki URL berbasis slug dan title dinamis sebagian, tetapi belum ada meta description server-side, Open Graph/Twitter Card metadata, canonical URL, structured data produk, dan fallback SEO yang benar ketika halaman dibagikan sebelum JavaScript berjalan.
- **Prioritas:** Medium
- **Kompleksitas:** Medium
- **Dependensi:** Halaman detail produk
- **Tujuan:** Tambahkan URL detail produk SEO-friendly, meta title, description, dan Open Graph.
- **Alasan:** Produk perlu mudah dibagikan dan ditemukan.
- **Manfaat:** Akuisisi organik dan shareability meningkat.

## 15. Cart sederhana / multi-item checkout

- **Status:** Belum
- **Prioritas:** Low
- **Kompleksitas:** Large
- **Dependensi:** Stok atomik, checkout stabil
- **Tujuan:** Pembeli dapat membeli lebih dari satu produk dalam satu order.
- **Alasan:** Dapat meningkatkan nilai transaksi setelah checkout stabil.
- **Manfaat:** AOV lebih tinggi dan checkout lebih praktis.

## 16. Kupon/diskon sederhana

- **Status:** Belum
- **Prioritas:** Low
- **Kompleksitas:** Medium
- **Dependensi:** Checkout stabil
- **Tujuan:** Tambahkan kode promo diskon nominal atau persentase.
- **Alasan:** Berguna untuk campaign setelah sistem transaksi stabil.
- **Manfaat:** Promosi lebih fleksibel dan meningkatkan konversi.

---

# Roadmap Implementasi

## Milestone 1 — Foundation

**Tujuan:** Menstabilkan sistem dasar, menghapus bug kritis, dan memastikan admin dapat mengelola toko dengan aman.

### Item

- Fix schema admin login `last_login_at`
- Perbaiki validasi payment settings sebelum go-live
- Perbaiki security headers dan cookie production
- Perbaiki keamanan cek status order
- Perbaiki hard delete produk
- Setup checklist admin
- Policy pages

### Exit Criteria

- Admin login stabil.
- Tidak ada payment dummy di production.
- Data pembeli lebih aman.
- Toko punya dasar trust dan konfigurasi minimum.

## Milestone 2 — MVP Ready

**Tujuan:** Membuat flow pembelian layak digunakan: produk jelas, checkout aman, pembayaran tercatat, dan order bisa diverifikasi.

### Item

- Perbaiki flow tombol “Lihat Detail”
- Perbaiki manajemen stok saat checkout
- Perbaiki status order agar lebih representatif
- Perbaiki order expired handling
- Halaman detail produk
- Payment confirmation internal
- Admin payment verification
- Auto-expire pending order
- Order timeline pembeli

### Exit Criteria

- Pembeli bisa memahami produk sebelum beli.
- Stok tidak oversell.
- Bukti bayar masuk ke sistem.
- Admin punya workflow verifikasi.
- Pembeli bisa melihat progress order.

## Milestone 3 — Production Ready

**Tujuan:** Menyelesaikan inti toko produk digital: delivery aman, dashboard efisien, dan operasional harian tidak sepenuhnya manual.

### Item

- Perbaiki dashboard order agar action-oriented
- Perbaiki pagination admin product/order
- Perbaiki validasi dan sanitasi output delivery
- Credential inventory produk digital
- Delivery produk digital terstruktur
- Admin notification sederhana
- Email/WhatsApp notification template
- Audit log admin
- Export laporan order
- Product SEO metadata

### Exit Criteria

- Produk digital bisa dikirim terstruktur.
- Admin tahu order mana yang harus diproses.
- Dashboard tetap cepat saat data bertambah.
- Ada jejak aktivitas admin.
- Penjual bisa menarik laporan dasar.
- Produk bisa dibagikan dengan URL detail yang baik.

## Milestone 4 — Post Launch Enhancement

**Tujuan:** Meningkatkan konversi, repeat purchase, dan fleksibilitas penjualan setelah transaksi utama stabil.

### Item

- Cart sederhana / multi-item checkout
- Kupon/diskon sederhana

### Exit Criteria

- Pembeli bisa membeli beberapa produk sekaligus.
- Penjual bisa menjalankan promo sederhana.
- Fitur growth ditambahkan tanpa mengganggu fondasi transaksi.
