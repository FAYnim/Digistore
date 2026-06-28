# Setup Cron Order Expiration

Jalankan runner ini secara berkala agar order `pending` / `pending_payment` yang melewati `payment_deadline` otomatis menjadi `expired` dan stok reserved dilepas.

## Command

```bash
php /path/to/digital-store/scripts/expire-orders.php
```

## Linux cron

Buka crontab:

```bash
crontab -e
```

Tambahkan jadwal tiap 1 menit:

```cron
* * * * * php /path/to/digital-store/scripts/expire-orders.php >> /path/to/digital-store/storage/order-expiration.log 2>&1
```

Ganti `/path/to/digital-store` dengan path project production.

## Windows Task Scheduler

- Program/script: `php`
- Arguments: `C:\path\to\digital-store\scripts\expire-orders.php`
- Trigger: every 1 minute atau sesuai kebutuhan

## Verifikasi

Jalankan manual:

```bash
php scripts/expire-orders.php
```

Output sukses contoh:

```text
Expired orders: 0
Released accounts: 0
```
