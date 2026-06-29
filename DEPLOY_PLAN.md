# DEPLOY_PLAN.md — DigiStore

---

## Ringkasan

| Item | Detail |
|------|--------|
| **Tech Stack** | PHP Native 8.0+, MySQL, Apache/Nginx |
| **Build Step** | Tidak ada (zero-build) |
| **Storage** | Local filesystem |
| **Cron** | PHP CLI script untuk expire orders |
| **Port Deployment** | Shared hosting / VPS Linux |

---

## Prasyarat Hosting

- PHP **8.0+** dengan ekstensi: `pdo`, `pdo_mysql`, `gd`, `mbstring`, `json`, `fileinfo`
- MySQL **5.7+** atau MariaDB **10.3+**
- Apache dengan `mod_rewrite` **atau** Nginx
- Akses SSH (VPS) atau File Manager + phpMyAdmin (Shared Hosting)
- `php.ini`: `allow_url_fopen = On`, `upload_max_filesize >= 10M`

---

## STEP 1 — Persiapan Lokal

> **Strategi: Fresh Deploy** — Database di hosting dimulai dari **kosong (clean)**, hanya berisi schema dan 1 admin baru. Data dummy & test dari lokal TIDAK ikut.

### 1.1. Buat File Kompresi

Hapus file yang tidak perlu di-upload. Pilih salah satu metode:

**Opsi A — ZIP (Windows/cross-platform):**

```bash
zip -r digital_store_deploy.zip . \
  -x "*.git*" \
  -x "*.zip" \
  -x "*.tar*" \
  -x "*.sql" \
  -x "node_modules/*" \
  -x "*.log" \
  -x ".DS_Store" \
  -x "Thumbs.db" \
  -x "uploads/payment-proofs/*" \
  -x "uploads/qris/*" \
  -x "dashboard/uploads/qris/*" \
  -x "database/seed-admin.php"
```

**Opsi B — TAR.GZ (Linux/macOS, lebih cepat & ukuran lebih kecil):**

```bash
tar --exclude='.git' \
    --exclude='*.sql' \
    --exclude='node_modules' \
    --exclude='*.log' \
    --exclude='.DS_Store' \
    --exclude='Thumbs.db' \
    --exclude='uploads/payment-proofs/*' \
    --exclude='uploads/qris/*' \
    --exclude='dashboard/uploads/qris/*' \
    --exclude='database/seed-admin.php' \
    --exclude='*.zip' \
    --exclude='*.tar.gz' \
    -czf digital_store_deploy.tar.gz .
```

**Opsi C — TAR.GZ dengan pattern file (`.tarignore`):**

```bash
# Buat file .tarignore
cat > .tarignore << 'EOF'
.git/
*.sql
node_modules/
*.log
.DS_Store
Thumbs.db
uploads/payment-proofs/*
uploads/qris/*
dashboard/uploads/qris/*
database/seed-admin.php
*.zip
*.tar.gz
EOF

# Compress
tar -czf digital_store_deploy.tar.gz -X .tarignore .
```

> **Catatan:** File `seed-admin.php` TIDAK di-include karena mengandung credential default. Hapus jika ada.

### 1.2. Buat .env dari .env.example

```bash
cp .env.example .env
```

Edit `.env` sesuai konfigurasi hosting:

```env
DB_HOST=127.0.0.1
DB_NAME=digital_store_live
DB_USER=digital_store_user
DB_PASS=YOUR_STRONG_PASSWORD
DB_CHARSET=utf8mb4
```

---

## STEP 2 — Setup Database di Hosting

### 2.1. Buat Database & User (via phpMyAdmin atau SSH)

**Via phpMyAdmin:**
1. Login ke phpMyAdmin hosting
2. Klik **SQL** tab
3. Eksekusi:

```sql
CREATE DATABASE digital_store_live CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'digital_store_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON digital_store_live.* TO 'digital_store_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2.2. Import Schema

1. Buka phpMyAdmin → pilih `digital_store_live`
2. Klik tab **Import**
3. Upload file `database/schema.sql` → **Go**

### 2.3. Skip Sample Data (Fresh Database)

> **JANGAN import `seed.sql`!** Database dimulai kosong tanpa data dummy.

### 2.4. Buat Admin User Baru (Fresh)

Karena database fresh, buat admin user baru via SQL:

```sql
-- Password default: admin123 (GANTI SESUDAH LOGIN PERTAMA!)
INSERT INTO admin_users (username, password, created_at) VALUES 
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());
```

**Atau** buat via PHP script lokal lalu generate hash baru:

```bash
# Generate hash password baru
php -r "echo password_hash('PASSWORD_BARU_ANDA', PASSWORD_BCRYPT);"

# Lalu insert ke database
INSERT INTO admin_users (username, password, created_at) VALUES 
('admin', 'HASH_DARI_PHP_DI_ATAS', NOW());
```

> **Security Note:** Ganti `admin123` dengan password kuat sebelum go-live!

---

## STEP 3 — Upload File ke Hosting

### 3.1. Metode Upload

**Opsi A — File Manager Hosting:**
1. Buka File Manager hosting (cPanel, Plesk, dll)
2. Navigate ke `public_html/` atau `htdocs/`
3. Upload file `.zip`
4. Extract via File Manager

**Opsi B — FTP/SFTP:**
```bash
# Upload via SCP
scp digital_store_deploy.zip user@host:/home/user/public_html/

# Extract via SSH
ssh user@host
cd /home/user/public_html
unzip digital_store_deploy.zip
rm digital_store_deploy.zip
```

**Opsi C — Git Deploy (Jika hosting support):**
```bash
# Clone repo ke server
git clone https://github.com/your-repo/digital-store.git .
```

### 3.2. Set Permissions

```bash
# Direktori yang perlu write access
chmod 755 includes/
chmod 755 uploads/
chmod 755 uploads/payment-proofs/
chmod 755 uploads/qris/
chmod 755 dashboard/uploads/
chmod 755 dashboard/uploads/qris/

# File PHP tidak perlu execute
chmod 644 *.php
chmod 644 -R api/
chmod 644 -R dashboard/
chmod 644 -R config/
chmod 644 -R database/*.sql
```

---

## STEP 4 — Konfigurasi Environment

### 4.1. Verifikasi .env

Pastikan `.env` sudah ada di root dengan konfigurasi benar:

```env
DB_HOST=127.0.0.1
DB_NAME=digital_store_live
DB_USER=digital_store_user
DB_PASS=YOUR_STRONG_PASSWORD
DB_CHARSET=utf8mb4
```

### 4.2. Konfigurasi PHP (php.ini)

Jika hosting memberikan akses ke `php.ini` atau `.user.ini`:

```ini
; php.ini atau .user.ini
allow_url_fopen = On
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 60
memory_limit = 256M
```

Atau via `.htaccess` (Apache):

```apache
php_value upload_max_filesize 20M
php_value post_max_size 25M
php_value max_execution_time 60
php_value memory_limit 256M
```

---

## STEP 5 — Konfigurasi Web Server

### 5.1. Apache (.htaccess sudah termasuk)

Pastikan `.htaccess` di root sudah ada dan contains:

```apache
RewriteEngine On
RewriteBase /

# Redirect semua request ke index.php jika file/dir tidak ada
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 5.2. Nginx Config

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/digital-store;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## STEP 6 — Setup Cron Job

### 6.1. Apa yang Dilakukan Cron

Script `scripts/expire-orders.php` expired orders yang tidak dibayar dalam 24 jam.

### 6.2. Setup via Cron Tab

```bash
# Edit crontab
crontab -e

# Tambah baris (jalankan setiap 15 menit)
*/15 * * * * /usr/bin/php /home/user/public_html/scripts/expire-orders.php >> /home/user/logs/cron.log 2>&1
```

### 6.3. Setup via cPanel (Shared Hosting)

1. Login ke cPanel → **Cron Jobs**
2. Common Settings: `*/15 * * * *`
3. Command:

```
/usr/bin/php /home/user/public_html/scripts/expire-orders.php
```

---

## STEP 7 — Verifikasi Deployment

### 7.1. Checklist

| No | Item | Status |
|----|------|--------|
| 1 | Buka `https://domain.com` — halaman storefront tampil | ☐ |
| 2 | Klik product → halaman detail berfungsi | ☐ |
| 3 | Checkout → form submit berhasil | ☐ |
| 4 | `https://domain.com/dashboard/` → halaman login tampil | ☐ |
| 5 | Login admin dengan credential baru | ☐ |
| 6 | Dashboard dapat menambah/edit produk | ☐ |
| 7 | Upload QRIS image berfungsi | ☐ |
| 8 | Cron job berjalan (cek cron log) | ☐ |
| 9 | Upload payment proof customer berfungsi | ☐ |
| 10 | `.env` tidak accessible dari browser (cek: `yourdomain.com/.env`) | ☐ |

### 7.2. Test API Endpoint

```bash
# Test API public
curl https://domain.com/api/products.php
curl https://domain.com/api/categories.php

# Test API dashboard (harus 401 tanpa auth)
curl https://domain.com/dashboard/api/stats.php
```

---

## STEP 8 — Checklist Keamanan

| Item | Action |
|------|--------|
| Ganti default admin password | WAJIB — via dashboard Settings |
| Hapus `seed-admin.php` | Delete jika ada di server |
| `.env` tidak accessible | Verifikasi via browser |
| `database/` folder | Pastikan tidak accessible langsung |
| `uploads/` permission | 755, tidak 777 |
| HTTPS | Pastikan SSL certificate aktif |
| PHP version | Minimum 8.0 |
| `display_errors` | Off di production |

### Aktifkan HTTPS (Lets Encrypt)

```bash
# Via SSH (jika hosting support)
certbot --apache -d yourdomain.com -d www.yourdomain.com

# Atau via cPanel → SSL/TLS → Let's Encrypt
```

---

## Step 9 — Troubleshooting

### Error: "Connection failed: SQLSTATE[HY000]"
- Cek `.env` → pastikan `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` benar
- Verifikasi database user punya privilege ke database

### Error: "Permission denied" saat upload
```bash
chmod 755 uploads/
chmod 755 uploads/payment-proofs/
chmod 755 uploads/qris/
chmod 755 dashboard/uploads/qris/
```

### Error: 500 Internal Server Error
- Cek `php_error_log` atau error log hosting
- Pastikan `mod_rewrite` enable
- Verifikasi PHP version >= 8.0

### Error: "Page not found" untuk semua route
- Pastikan `.htaccess` ada dan `RewriteEngine On`
- Untuk Nginx: cek `try_files` directive

### Blank page / White screen
- Enable `display_errors` temporer di `.env`:
  ```php
  // config/database.php — temporary
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- Hapus setelah fix

### Cron tidak jalan
- Verifikasi path PHP: `which php`
- Cek cron log
- Test manual: `/usr/bin/php /path/to/expire-orders.php`

---

## File yang Harus Ada di Server

```
/
├── index.php
├── checkout.php
├── payment.php
├── order-status.php
├── product.php
├── .htaccess
├── .env                    ← GITIGNORED, buat manual di server
├── api/
├── assets/
├── config/
├── dashboard/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── includes/
├── scripts/
└── uploads/
```

---

## Quick Deploy Commands (VPS)

```bash
# 1. Clone / Upload
cd /var/www/digital-store

# 2. Setup .env
cp .env.example .env
nano .env  # edit DB credentials

# 3. Setup database FRESH (schema saja, tanpa seed)
mysql -u root -p -e "CREATE DATABASE digital_store"
mysql -u root -p digital_store < database/schema.sql

# 4. Buat admin user baru (JANGAN pakai password default!)
HASH=$(php -r "echo password_hash('PASSWORD_KUAT_ANDA', PASSWORD_BCRYPT);")
mysql -u root -p digital_store -e "INSERT INTO admin_users (username, password, created_at) VALUES ('admin', '$HASH', NOW());"

# 5. Set permissions
chmod 755 includes/ uploads/ dashboard/uploads/
chmod 755 uploads/payment-proofs/ uploads/qris/ dashboard/uploads/qris/

# 6. Setup cron
(crontab -l 2>/dev/null; echo "*/15 * * * * /usr/bin/php /var/www/digital-store/scripts/expire-orders.php") | crontab -

# 7. Restart services
sudo systemctl restart php8.0-fpm
sudo systemctl reload apache2
```

---

## Update Deployment (Maintenance)

Untuk update code tanpa kehilangan data:

1. **Backup database:**
   ```sql
   mysqldump -u user -p digital_store_live > backup_$(date +%Y%m%d).sql
   ```

2. **Backup konfigurasi:**
   ```bash
   cp .env .env.backup
   ```

3. **Upload file baru** (exclude `.env`, `uploads/`)

4. **Merge config jika perlu**

5. **Flush cache** (jika ada) — cek `config/cache.php`

6. **Test** sebelum announce