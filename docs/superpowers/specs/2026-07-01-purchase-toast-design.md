# Purchase Toast Notification — Design Spec

**Tanggal:** 2026-07-01
**Status:** Approved

## Overview

Menambahkan notifikasi toast di landing page yang menampilkan pembeli terbaru dari database. Tujuan: meningkatkan trust/social proof agar pengunjung lebih percaya untuk membeli.

## Data Source

Data real dari tabel `orders` + `order_items` di database. Hanya order dengan status `completed` yang ditampilkan.

## API Endpoint

**File:** `api/recent-purchases.php`
**Method:** `GET`
**Query Params:** `limit` (default 10)

**SQL:**
```sql
SELECT
  o.customer_name,
  oi.product_name,
  o.created_at
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.status = 'completed'
ORDER BY o.created_at DESC
LIMIT ?
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "customer_name": "R***",
      "product_name": "ChatGPT Plus",
      "created_at": "2026-07-01 10:30:00"
    }
  ]
}
```

**Privacy:** Nama pelanggan di-mask di backend — huruf pertama + `***` (contoh: `Rudi Hartono` → `R***`). Masking dilakukan di PHP sebelum response dikirim.

## Frontend

### Container

- `position: fixed; bottom: 24px; left: 24px; z-index: 999`
- Hanya muncul di `index.php`

### Toast Card

- Background: `var(--surface)`, border: `var(--border)`, border-radius: 14px
- Box shadow: `0 8px 30px rgba(0,0,0,0.12)`
- Max-width: 340px, mobile: `max-width: 90vw`
- Layout: flex row — avatar + text + close button

### Avatar

- 40px circle, gradient `linear-gradient(135deg, #2563eb, #7c3aed)` (light) / `#74a7ff` → `#a78bfa` (dark)
- Inisial nama (huruf pertama sebelum masking) berwarna putih, font-weight 800

### Text

- Baris 1: `R*** membeli <product_name>` — nama muted, produk accent color, bold
- Baris 2: waktu relatif (contoh: "5 menit yang lalu") — muted, font-size 11px, dengan ikon jam kecil

### Close Button

- Tombol `✕` di kanan, bg none, color muted
- Menghentikan cycle dan menghapus toast

### Animation

1. **Entry:** slide up `translateY(100%)` → `translateY(0)`, opacity 0→1, duration 400ms, ease-out
2. **Display:** tampil 5 detik
3. **Exit:** class `.toast-exit` → `translateY(100%)`, opacity 0, duration 300ms
4. **Jeda:** 8 detik sebelum toast berikutnya muncul
5. Loop dari awal jika array habis

### Accessibility

- `@media (prefers-reduced-motion: reduce)` → animasi dinonaktifkan, show/hide langsung
- Close button punya `aria-label="Tutup notifikasi"`

## JavaScript

### Lokasi

Fungsi baru di `app.js`, dipanggil setelah `loadLandingData()` berhasil.

### Fungsi: `initPurchaseToast()`

```
1. Fetch GET /api/recent-purchases?limit=10
2. Jika success && data.length > 0:
   a. Simpan array di state.purchases
   b. Mulai cycle(index = 0)
3. Jika gagal atau data kosong: tidak melakukan apa-apa
```

### Fungsi: `showPurchaseToast(purchase)`

```
1. Buat elemen toast (container, avatar, text, close button)
2. Append ke container
3. Tambah event listener close button → remove toast, hentikan cycle
4. Set timeout 5 detik → tambah class .toast-exit → setTimeout 300ms → remove elemen
```

### Fungsi: `cycleToasts(purchases)`

```
1. Tampilkan purchases[currentIndex] via showPurchaseToast()
2. Setelah toast dismissed (5s + 300ms), jeda 8 detik
3. Increment currentIndex, wrap ke 0 jika habis
4. Rekursif panggil cycleToasts()
```

### Helper: `timeAgo(dateString)`

Konversi `created_at` ke format relatif: "X menit yang lalu", "X jam yang lalu", "X hari yang lalu".

### Reuse

- `escapeText()` sudah ada di app.js — reuse untuk sanitize output
- `apiGet()` sudah ada di api.js — reuse untuk fetch

## CSS Additions

Tambahkan ke `style.css`:

```css
.toast-container { position: fixed; bottom: 24px; left: 24px; z-index: 999; }
.purchase-toast { ... }
.purchase-toast .toast-avatar { ... }
.purchase-toast .toast-body { ... }
.purchase-toast .toast-close { ... }
.purchase-toast.toast-exit { ... }
@media (prefers-reduced-motion: reduce) { ... }
@media (max-width: 640px) { .toast-container { ... } }
```

## Edge Cases

| Scenario | Behavior |
|----------|----------|
| Tidak ada order completed | Toast tidak muncul |
| API error | Toast tidak muncul, silent fail |
| User close manual | Cycle berhenti, toast di-remove |
| Dark mode | Menggunakan existing `var()` tokens |
| Mobile (< 640px) | Max-width 90vw, tetap di kiri bawah |
| Reduced motion | No animation, show/hide instant |

## Files Changed

| File | Change |
|------|--------|
| `api/recent-purchases.php` | **Baru** — endpoint GET |
| `assets/js/app.js` | Tambah `initPurchaseToast()`, `showPurchaseToast()`, `cycleToasts()`, `timeAgo()` |
| `assets/css/style.css` | Tambah toast styles |

## Out of Scope

- Admin toggle untuk enable/disable toast
- Polling real-time (future enhancement)
- Toast di halaman selain index.php
