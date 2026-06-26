PRD — Fitur Auth Admin untuk Dashboard Katalog Produk Digital

1. Ringkasan

Fitur auth admin digunakan untuk melindungi seluruh halaman dashboard dan API dashboard.

Sebelumnya:

Dashboard bisa dibuka sebagai UI.
API dashboard bisa digunakan untuk CRUD data.

Masalahnya:

Jika tidak ada auth, siapa pun bisa membuka /dashboard/ dan mengubah produk, order, kategori, testimoni, serta setting toko.

Maka fitur ini wajib dibuat sebelum proyek dideploy ke hosting.

Target utama:

- Admin bisa login.
- Admin bisa logout.
- Dashboard hanya bisa diakses setelah login.
- API dashboard hanya bisa diakses setelah login.
- Password disimpan secara aman dengan password_hash().
- Login diverifikasi dengan password_verify().

---

2. Tujuan

Tujuan fitur ini:

1. Melindungi halaman dashboard.
2. Melindungi endpoint /dashboard/api/.
3. Membuat sistem login admin sederhana.
4. Membuat session admin.
5. Membuat logout admin.
6. Menyimpan password secara aman.
7. Menyiapkan fondasi keamanan sebelum deployment.

---

3. Scope

Termasuk

- Halaman login admin.
- Proses login.
- Proses logout.
- Session PHP.
- Proteksi halaman dashboard.
- Proteksi API dashboard.
- Seeder akun admin pertama.
- Validasi input login.
- Error login sederhana.
- Redirect otomatis jika belum login.

Tidak Termasuk

- Register admin publik.
- Multi-role permission.
- Reset password via email.
- OTP.
- Login Google.
- Audit log lengkap.
- Rate limit kompleks.

Catatan:

Untuk MVP, cukup satu atau beberapa akun admin dari database.
Admin baru bisa ditambahkan manual lewat database atau fitur dashboard versi berikutnya.

---

4. Struktur Folder

Struktur folder yang disarankan:

digital-store/
├── dashboard/
│   ├── login.php
│   ├── logout.php
│   ├── index.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   ├── testimonials.php
│   ├── settings.php
│   │
│   ├── api/
│   │   ├── products.php
│   │   ├── categories.php
│   │   ├── orders.php
│   │   ├── testimonials.php
│   │   ├── settings.php
│   │   └── stats.php
│   │
│   ├── auth/
│   │   ├── login-process.php
│   │   ├── check-auth.php
│   │   └── csrf.php
│   │
│   └── components/
│       ├── sidebar.php
│       ├── topbar.php
│       └── layout.php
│
├── config/
│   ├── database.php
│   └── response.php
│
└── database/
    ├── schema.sql
    └── seed.sql

---

5. Database

Fitur auth menggunakan tabel:

admin_users

Schema:

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

Penjelasan kolom:

id            → ID admin
username      → username login
password      → password hasil password_hash()
name          → nama admin
status        → active / inactive
last_login_at → waktu login terakhir
created_at    → waktu dibuat
updated_at    → waktu diperbarui

---

6. Seeder Admin Pertama

Admin pertama dibuat melalui file seeder atau script manual.

File:

database/seed-admin.php

Flow:

1. Buat username admin.
2. Hash password dengan password_hash().
3. Insert ke admin_users.
4. Hapus file seeder setelah berhasil.

Contoh konsep:

$passwordHash = password_hash("admin123", PASSWORD_DEFAULT);

Catatan penting:

Password default seperti admin123 hanya untuk development.
Saat production, wajib ganti password kuat.
File seed-admin.php wajib dihapus setelah digunakan.

---

7. UI Halaman Login

Path:

/dashboard/login.php

Tampilan harus sederhana dan selaras dengan tema dashboard.

Elemen UI:

- Logo / nama toko
- Judul: Admin Login
- Input username
- Input password
- Tombol Login
- Error message
- Link kembali ke katalog

Copywriting singkat:

Judul:
Admin Login

Subtitle:
Masuk untuk mengelola katalog.

Input:
Username
Password

Button:
Login

Error:
Username atau password salah.
Akun tidak aktif.

Layout desktop:

Halaman penuh
Card login di tengah
Background mengikuti tema light/dark

Layout mobile:

Card login full width dengan padding
Input dan tombol mudah ditekan

---

8. Flow Login

Flow utama:

User buka /dashboard/
→ Sistem cek session
→ Jika belum login, redirect ke /dashboard/login.php
→ User isi username dan password
→ Sistem validasi input
→ Sistem cari username di database
→ Sistem cek status active
→ Sistem verifikasi password dengan password_verify()
→ Jika valid, buat session admin
→ Redirect ke /dashboard/index.php

Session yang disimpan:

admin_id
admin_username
admin_name
admin_logged_in

Contoh session:

$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_username'] = 'admin';
$_SESSION['admin_name'] = 'Admin Store';

---

9. Flow Logout

Path:

/dashboard/logout.php

Flow:

User klik logout
→ Sistem hapus semua session admin
→ session_destroy()
→ Redirect ke /dashboard/login.php

Menu logout ditaruh di:

Topbar profile/admin menu

---

10. Proteksi Halaman Dashboard

Semua halaman dashboard wajib memanggil file check-auth.

File:

/dashboard/auth/check-auth.php

Digunakan di:

/dashboard/index.php
/dashboard/products.php
/dashboard/categories.php
/dashboard/orders.php
/dashboard/testimonials.php
/dashboard/settings.php

Contoh penggunaan:

require_once __DIR__ . '/auth/check-auth.php';

Logic check-auth:

Jika session admin_logged_in tidak ada atau false:
→ redirect ke /dashboard/login.php

---

11. Proteksi API Dashboard

Semua endpoint di "/dashboard/api/" wajib cek session.

Digunakan di:

/dashboard/api/products.php
/dashboard/api/categories.php
/dashboard/api/orders.php
/dashboard/api/testimonials.php
/dashboard/api/settings.php
/dashboard/api/stats.php

Behavior jika belum login:

{
  "success": false,
  "message": "Unauthorized",
  "data": null
}

HTTP status:

401 Unauthorized

Catatan penting:

Jangan hanya proteksi halaman dashboard.
API dashboard juga harus diproteksi.

---

12. CSRF Protection

Untuk MVP, CSRF protection disarankan terutama pada login dan request yang mengubah data.

Minimal implementasi:

- Generate csrf_token di session.
- Sertakan token di form login.
- Validasi token saat login-process.

Untuk API dashboard:

- Token bisa dikirim lewat header X-CSRF-Token.
- API POST/PUT/DELETE wajib validasi token.

Jika ingin MVP lebih cepat:

Minimal CSRF untuk form login dulu.
CSRF API bisa ditambahkan setelah CRUD stabil.

Namun untuk production, CSRF API tetap disarankan.

---

13. Login Process

File:

/dashboard/auth/login-process.php

Method:

POST

Input:

username
password
csrf_token

Validasi:

username wajib
password wajib
csrf_token valid

Proses:

1. Start session.
2. Validasi CSRF.
3. Ambil username dan password.
4. Cari admin berdasarkan username.
5. Jika tidak ditemukan, gagal.
6. Jika status inactive, gagal.
7. Verifikasi password.
8. Jika valid, regenerate session ID.
9. Simpan data admin ke session.
10. Update last_login_at.
11. Redirect ke dashboard.

Poin penting:

session_regenerate_id(true);

Alasan:

Mencegah session fixation.

---

14. Session Security

Setting session yang disarankan sebelum "session_start()":

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

Jika hosting sudah HTTPS:

ini_set('session.cookie_secure', 1);

Catatan:

cookie_secure hanya aktif jika website sudah menggunakan HTTPS.

---

15. Error Handling Login

Pesan error harus singkat dan tidak membocorkan detail.

Gunakan:

Username atau password salah.

Jangan gunakan:

Username tidak ditemukan.
Password salah.

Alasan:

Agar username tidak mudah ditebak.

Untuk akun inactive, boleh tampilkan:

Akun tidak aktif.

---

16. Redirect Behavior

Jika belum login dan membuka:

/dashboard/products.php

Maka redirect ke:

/dashboard/login.php

Setelah login berhasil:

Redirect ke halaman dashboard utama.

MVP sederhana:

/dashboard/index.php

Versi lebih baik:

Simpan intended_url, lalu redirect ke halaman yang awalnya ingin dibuka.

Untuk MVP:

Redirect ke /dashboard/index.php sudah cukup.

---

17. Integrasi dengan Dashboard UI

Topbar dashboard menampilkan admin yang sedang login.

Data dari session:

admin_name
admin_username

Contoh UI:

Admin Store
Logout

Sidebar tidak perlu berubah banyak.

Tambahkan menu:

Kembali ke Katalog
Logout

Lebih baik logout ada di topbar agar tidak tercampur dengan navigasi utama.

---

18. Integrasi dengan API CRUD

Setelah auth dibuat, semua JavaScript dashboard yang memanggil API harus menangani response 401.

Jika API mengembalikan 401:

Redirect ke /dashboard/login.php

Contoh behavior frontend:

fetch API
→ response 401
→ window.location.href = "/dashboard/login.php"

---

19. File yang Perlu Dibuat

dashboard/login.php
dashboard/logout.php
dashboard/auth/login-process.php
dashboard/auth/check-auth.php
dashboard/auth/csrf.php
database/seed-admin.php

File yang perlu diubah:

dashboard/index.php
dashboard/products.php
dashboard/categories.php
dashboard/orders.php
dashboard/testimonials.php
dashboard/settings.php

dashboard/api/products.php
dashboard/api/categories.php
dashboard/api/orders.php
dashboard/api/testimonials.php
dashboard/api/settings.php
dashboard/api/stats.php

dashboard/components/topbar.php

---

20. Security Requirement

Wajib:

- Password menggunakan password_hash().
- Login menggunakan password_verify().
- Query menggunakan prepared statement.
- Session ID diganti setelah login.
- Semua halaman dashboard memanggil check-auth.
- Semua API dashboard memanggil check-auth.
- Error login tidak membocorkan username/password.
- File seed admin dihapus setelah production.
- display_errors dimatikan di production.

Disarankan:

- CSRF token.
- HTTPS.
- Password minimal 8 karakter.
- Timeout session.
- Batasi percobaan login.

---

21. Session Timeout

Untuk MVP, bisa dibuat timeout sederhana.

Durasi:

2 jam

Session field:

last_activity

Flow:

Setiap request dashboard
→ cek last_activity
→ jika lebih dari 2 jam, logout otomatis
→ jika belum, update last_activity

Ini opsional, tapi bagus untuk keamanan.

---

22. Acceptance Criteria

Fitur auth admin selesai jika:

1. Admin bisa membuka /dashboard/login.php.
2. Admin bisa login dengan username dan password valid.
3. Password admin tersimpan dalam bentuk hash.
4. Login salah menampilkan error singkat.
5. Akun inactive tidak bisa login.
6. Setelah login, admin diarahkan ke /dashboard/index.php.
7. Setelah login, session admin tersimpan.
8. Admin bisa logout.
9. Setelah logout, admin tidak bisa membuka dashboard.
10. Semua halaman /dashboard/ terlindungi.
11. Semua endpoint /dashboard/api/ terlindungi.
12. API dashboard mengembalikan 401 jika belum login.
13. Halaman dashboard menampilkan nama admin.
14. Session ID diregenerasi setelah login.
15. Query login menggunakan prepared statement.

---

23. Urutan Implementasi

Urutan paling aman:

1. Pastikan tabel admin_users sudah ada.
2. Buat seed-admin.php untuk membuat admin pertama.
3. Buat login.php.
4. Buat csrf.php.
5. Buat login-process.php.
6. Buat check-auth.php.
7. Tambahkan check-auth ke semua halaman dashboard.
8. Buat logout.php.
9. Tambahkan tombol logout di topbar.
10. Tambahkan check-auth ke semua API dashboard.
11. Tes akses dashboard tanpa login.
12. Tes login valid.
13. Tes login salah.
14. Tes logout.
15. Tes API dashboard tanpa login.
16. Tes API dashboard setelah login.
17. Hapus seed-admin.php sebelum production.

---

24. Prioritas MVP

Untuk MVP siap deploy, minimum yang harus selesai:

- Login admin
- Logout admins
- Session admin
- Proteksi halaman dashboard
- Proteksi API dashboard
- Password hash

Yang bisa menyusul:

- Reset password
- Multi-role
- Login attempt limiter
- Audit log

---

25. Kesimpulan

Fitur auth admin adalah bagian wajib sebelum dashboard diupload ke hosting. Tanpa auth, dashboard dan API dashboard terlalu berisiko karena siapa pun bisa mengakses dan mengubah data toko.

Untuk MVP, auth tidak perlu kompleks. Cukup login, logout, session, password hash, dan proteksi semua halaman serta API dashboard.