# Alur Pembelian MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build direct checkout from landing page to saved order, QRIS payment page, and public order status.

**Architecture:** Keep public buyer flow in root pages and root `api/`. Reuse existing PDO/JSON helpers from `config/database.php` and frontend patterns from `assets/js/app.js`. Backend owns product validation, pricing, order code generation, order insert, and public order response.

**Tech Stack:** PHP + PDO + MySQL, vanilla JS, Tailwind CDN, existing CSS in `assets/css/style.css`.

---

## File Structure

- Modify: `database/schema.sql` — add `payment_deadline`, `delivery_note` to `orders`.
- Modify: `database/seed.sql` — include new order columns and payment settings seed.
- Modify: `api/products.php` — support `?slug=...` single product lookup.
- Create: `api/checkout.php` — public POST create order endpoint.
- Create: `api/orders.php` — public GET order detail endpoint.
- Create: `checkout.php` — buyer checkout UI.
- Create: `payment.php` — QRIS/manual payment UI.
- Create: `order-status.php` — public order status UI.
- Modify: `assets/js/api.js` — add `apiPost` helper.
- Modify: `assets/js/app.js` — redirect buy button to checkout instead of dummy modal.
- Modify: `index.php` — remove/hide checkout dummy modal dependency if no longer needed.

---

### Task 1: Database Schema + Seed

**Files:**
- Modify: `database/schema.sql:69-81`
- Modify: `database/seed.sql:44-51`
- Modify: `database/seed.sql:79-87`

- [ ] **Step 1: Update `orders` schema**

Replace `orders` table block with:

```sql
CREATE TABLE orders (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  order_code       VARCHAR(50) NOT NULL UNIQUE,
  customer_name    VARCHAR(100) NOT NULL,
  customer_email   VARCHAR(150) DEFAULT NULL,
  customer_phone   VARCHAR(30) DEFAULT NULL,
  total_amount     INT NOT NULL DEFAULT 0,
  payment_method   VARCHAR(50) DEFAULT NULL,
  payment_deadline DATETIME DEFAULT NULL,
  status           ENUM('pending', 'paid', 'completed', 'cancelled') DEFAULT 'pending',
  note             TEXT DEFAULT NULL,
  delivery_note    TEXT DEFAULT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 2: Update seed order insert columns**

Replace order seed insert header with:

```sql
INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, total_amount, payment_method, payment_deadline, status, note, delivery_note) VALUES
```

Update each row to include `payment_deadline` and `delivery_note`:

```sql
('ORD-20260624-001', 'Raka Pratama',  'raka@mail.test',   '6281234567890', 25000, 'QRIS',     '2026-06-24 23:59:00', 'paid',      NULL, NULL),
('ORD-20260624-002', 'Nadia Putri',   'nadia@mail.test',  '6281234567891', 45000, 'Transfer', '2026-06-24 23:59:00', 'pending',   NULL, NULL),
('ORD-20260623-003', 'Dimas Arya',    'dimas@mail.test',  '6281234567892', 35000, 'E-Wallet', '2026-06-23 23:59:00', 'completed', NULL, 'Produk sudah dikirim oleh admin.'),
('ORD-20260623-004', 'Sari Dewi',     'sari@mail.test',   '6281234567893', 55000, 'QRIS',     '2026-06-23 23:59:00', 'paid',      'Tolong kirim cepat', NULL),
('ORD-20260622-005', 'Budi Santoso',  'budi@mail.test',   '6281234567894', 20000, 'Transfer', '2026-06-22 23:59:00', 'cancelled', 'Dibatalkan pembeli', NULL),
('ORD-20260622-006', 'Lina Susanti',  'lina@mail.test',   '6281234567895', 79000, 'QRIS',     '2026-06-22 23:59:00', 'completed', NULL, 'Akses produk sudah dikirim.'),
('ORD-20260621-007', 'Eko Prasetyo',  'eko@mail.test',    '6281234567896', 99000, 'Transfer', '2026-06-21 23:59:00', 'paid',      NULL, NULL);
```

- [ ] **Step 3: Add payment settings seed**

Append these rows to `store_settings` insert:

```sql
('default_theme',     'light'),
('accent_color',      '#2563EB'),
('payment_qris_image', 'https://placehold.co/400x400?text=QRIS+Dummy'),
('payment_instruction', 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.'),
('payment_whatsapp_message', 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.');
```

- [ ] **Step 4: Run SQL manually in local DB**

For existing DB, run:

```sql
ALTER TABLE orders
ADD COLUMN payment_deadline DATETIME DEFAULT NULL AFTER payment_method,
ADD COLUMN delivery_note TEXT DEFAULT NULL AFTER note;

INSERT INTO store_settings (setting_key, setting_value) VALUES
('payment_qris_image', 'https://placehold.co/400x400?text=QRIS+Dummy'),
('payment_instruction', 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.'),
('payment_whatsapp_message', 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
```

Expected: columns/settings exist; old dashboard orders still load.

---

### Task 2: Product Slug Lookup

**Files:**
- Modify: `api/products.php:15-67`

- [ ] **Step 1: Add slug filter path**

After `$featured` line, add:

```php
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug !== '') {
    $conditions[] = 'p.slug = ?';
    $params[] = $slug;
}
```

- [ ] **Step 2: Return one product for slug**

After `$rows = $stmt->fetchAll();`, keep normalization loop, then before current `json_success`, add:

```php
if ($slug !== '') {
    if (!$rows) {
        json_error('Produk tidak ditemukan.', null, 404);
    }
    json_success('Produk berhasil dimuat', $rows[0]);
}
```

- [ ] **Step 3: Verify endpoint**

Open:

```text
http://localhost/faydev/digital-store/api/products.php?slug=google-ai-pro-12-bulan
```

Expected: JSON `success: true`, `data.slug = google-ai-pro-12-bulan`.

---

### Task 3: Public Checkout API

**Files:**
- Create: `api/checkout.php`

- [ ] **Step 1: Create `api/checkout.php`**

```php
<?php

require_once __DIR__ . '/../config/database.php';

require_method('POST');

function generate_order_code(PDO $pdo): string
{
    $date = date('Ymd');

    do {
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $code = "ORD-$date-$random";
        $stmt = $pdo->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
        $stmt->execute([$code]);
    } while ($stmt->fetch());

    return $code;
}

$body = json_body();
$productId = isset($body['product_id']) ? (int) $body['product_id'] : 0;
$quantity = isset($body['quantity']) ? (int) $body['quantity'] : 1;
$customerName = trim($body['customer_name'] ?? '');
$customerEmail = trim($body['customer_email'] ?? '');
$customerPhone = trim($body['customer_phone'] ?? '');
$note = trim($body['note'] ?? '');

if ($productId <= 0) json_error('Produk wajib dipilih.', null, 422);
if ($quantity < 1) json_error('Jumlah minimal 1.', null, 422);
if ($customerName === '') json_error('Nama wajib diisi.', null, 422);
if ($customerPhone === '') json_error('WhatsApp wajib diisi.', null, 422);
if ($customerEmail !== '' && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) json_error('Email tidak valid.', null, 422);

try {
    $stmt = $pdo->prepare('SELECT id, name, price, stock, status FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product || $product['status'] !== 'active' || (int) $product['stock'] <= 0) {
        json_error('Produk tidak tersedia', null, 422);
    }

    if ((int) $product['stock'] < $quantity) {
        json_error('Stok produk tidak cukup', null, 422);
    }

    $price = (int) $product['price'];
    $subtotal = $price * $quantity;
    $orderCode = generate_order_code($pdo);
    $deadline = date('Y-m-d 23:59:00', strtotime('+1 day'));

    $pdo->beginTransaction();

    $order = $pdo->prepare('INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, total_amount, payment_method, payment_deadline, status, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $order->execute([
        $orderCode,
        $customerName,
        $customerEmail !== '' ? $customerEmail : null,
        $customerPhone,
        $subtotal,
        'QRIS',
        $deadline,
        'pending',
        $note !== '' ? $note : null,
    ]);

    $orderId = (int) $pdo->lastInsertId();
    $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    $item->execute([$orderId, $productId, $product['name'], $quantity, $price, $subtotal]);

    $pdo->commit();

    json_success('Pesanan berhasil dibuat', [
        'order_code' => $orderCode,
        'redirect_url' => '/payment.php?code=' . rawurlencode($orderCode),
    ], 201);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_error('Gagal membuat pesanan.', null, 500);
}
```

- [ ] **Step 2: Verify POST success**

Use browser devtools/fetch or API client:

```js
fetch('api/checkout.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ product_id: 1, quantity: 1, customer_name: 'Faris', customer_phone: '6281234567890' })
}).then(r => r.json()).then(console.log)
```

Expected: `success: true`, `data.order_code` starts with `ORD-`.

---

### Task 4: Public Order Detail API

**Files:**
- Create: `api/orders.php`

- [ ] **Step 1: Create `api/orders.php`**

```php
<?php

require_once __DIR__ . '/../config/database.php';

require_method('GET');

$code = trim($_GET['code'] ?? '');
if ($code === '') json_error('Kode order wajib diisi.', null, 422);

try {
    $stmt = $pdo->prepare('SELECT order_code, customer_name, total_amount, payment_method, payment_deadline, status, note, delivery_note, created_at FROM orders WHERE order_code = ? LIMIT 1');
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if (!$order) json_error('Order tidak ditemukan.', null, 404);

    $items = $pdo->prepare('SELECT product_name, quantity, price, subtotal FROM order_items WHERE order_id = (SELECT id FROM orders WHERE order_code = ? LIMIT 1)');
    $items->execute([$code]);
    $orderItems = $items->fetchAll();

    foreach ($orderItems as &$item) {
        $item['quantity'] = (int) $item['quantity'];
        $item['price'] = (int) $item['price'];
        $item['subtotal'] = (int) $item['subtotal'];
    }
    unset($item);

    $settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM store_settings WHERE setting_key IN ('payment_qris_image', 'payment_instruction', 'payment_whatsapp_message', 'store_whatsapp')");
    $settingsStmt->execute();
    $settings = [];
    foreach ($settingsStmt->fetchAll() as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    $order['total_amount'] = (int) $order['total_amount'];
    $order['items'] = $orderItems;
    $order['payment'] = [
        'qris_image' => $settings['payment_qris_image'] ?? 'https://placehold.co/400x400?text=QRIS+Dummy',
        'instruction' => $settings['payment_instruction'] ?? 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.',
        'whatsapp' => $settings['store_whatsapp'] ?? '',
        'whatsapp_message' => $settings['payment_whatsapp_message'] ?? 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.',
    ];

    json_success('Order berhasil dimuat', $order);
} catch (Throwable $e) {
    json_error('Gagal memuat order.', null, 500);
}
```

- [ ] **Step 2: Verify GET detail**

Open:

```text
http://localhost/faydev/digital-store/api/orders.php?code=ORD-20260624-001
```

Expected: `success: true`, `data.items` array, `data.payment.qris_image` present.

---

### Task 5: API JS Helper

**Files:**
- Modify: `assets/js/api.js`

- [ ] **Step 1: Add `apiPost`**

Append:

```js
async function apiPost(endpoint, payload) {
  try {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    return await response.json();
  } catch (error) {
    return {
      success: false,
      message: error.message,
      data: null,
      errors: null,
    };
  }
}
```

- [ ] **Step 2: Verify global function exists**

Open landing page console:

```js
typeof apiPost
```

Expected: `"function"`.

---

### Task 6: Checkout Page

**Files:**
- Create: `checkout.php`

- [ ] **Step 1: Create `checkout.php`**

Implement a full HTML page matching existing CDN/font/style imports. Required IDs: `message`, `productSummary`, `checkoutForm`, `customerName`, `customerEmail`, `customerPhone`, `note`, `submitButton`.

Use this script behavior:

```js
const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const params = new URLSearchParams(window.location.search);
const slug = params.get("product");
let selectedProduct = null;

function showMessage(message) {
  document.querySelector("#message").textContent = message;
  document.querySelector("#message").classList.remove("hidden");
}

async function loadProduct() {
  if (!slug) {
    showMessage("Produk tidak ditemukan.");
    return;
  }

  const res = await apiGet(`/products.php?slug=${encodeURIComponent(slug)}`);
  if (!res.success) {
    showMessage(res.message || "Produk tidak ditemukan.");
    return;
  }

  selectedProduct = res.data;
  if (selectedProduct.status !== "active" || Number(selectedProduct.stock) <= 0) {
    showMessage("Produk sedang habis.");
    return;
  }

  document.querySelector("#productSummary").innerHTML = `
    <img class="product-img" src="${selectedProduct.image_url || 'https://placehold.co/600x400?text=No+Image'}" alt="${selectedProduct.name}">
    <h2 class="mt-4 font-display text-2xl font-extrabold">${selectedProduct.name}</h2>
    <p class="mt-2 text-[var(--muted)]">${selectedProduct.description || ''}</p>
    <div class="price mt-4"><strong>${rupiah.format(Number(selectedProduct.price || 0))}</strong></div>
    <p class="mt-3 text-sm text-[var(--muted)]">Total pembayaran</p>
    <p class="font-display text-3xl font-extrabold">${rupiah.format(Number(selectedProduct.price || 0))}</p>
  `;
}

async function submitOrder(event) {
  event.preventDefault();
  if (!selectedProduct) return;

  const customerName = document.querySelector("#customerName").value.trim();
  const customerPhone = document.querySelector("#customerPhone").value.trim();
  if (!customerName) return showMessage("Nama wajib diisi.");
  if (!customerPhone) return showMessage("WhatsApp wajib diisi.");

  const button = document.querySelector("#submitButton");
  button.disabled = true;
  button.textContent = "Memproses...";

  const res = await apiPost("/checkout.php", {
    product_id: Number(selectedProduct.id),
    quantity: 1,
    customer_name: customerName,
    customer_email: document.querySelector("#customerEmail").value.trim(),
    customer_phone: customerPhone,
    note: document.querySelector("#note").value.trim(),
  });

  if (res.success) {
    window.location.href = `payment.php?code=${encodeURIComponent(res.data.order_code)}`;
    return;
  }

  showMessage(res.message || "Gagal membuat pesanan.");
  button.disabled = false;
  button.textContent = "Buat Pesanan";
}

document.querySelector("#checkoutForm").addEventListener("submit", submitOrder);
loadProduct();
```

- [ ] **Step 2: Verify checkout load**

Open:

```text
http://localhost/faydev/digital-store/checkout.php?product=google-ai-pro-12-bulan
```

Expected: product summary visible; form visible.

---

### Task 7: Payment Page

**Files:**
- Create: `payment.php`

- [ ] **Step 1: Create `payment.php`**

Required IDs: `message`, `orderDetail`, `paymentCard`.

Use status labels:

```js
const statusLabels = {
  pending: "Menunggu Pembayaran",
  paid: "Pembayaran Diterima",
  completed: "Selesai",
  cancelled: "Dibatalkan",
};
```

Use main load logic:

```js
const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const code = new URLSearchParams(window.location.search).get("code");

function showMessage(message) {
  document.querySelector("#message").textContent = message;
  document.querySelector("#message").classList.remove("hidden");
}

async function loadOrder() {
  if (!code) return showMessage("Order tidak ditemukan.");

  const res = await apiGet(`/orders.php?code=${encodeURIComponent(code)}`);
  if (!res.success) return showMessage(res.message || "Order tidak ditemukan.");

  const order = res.data;
  const itemNames = (order.items || []).map((item) => item.product_name).join(", ");
  const waText = encodeURIComponent((order.payment.whatsapp_message || "Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.").replace("{order_code}", order.order_code));
  const waLink = order.payment.whatsapp ? `https://wa.me/${order.payment.whatsapp}?text=${waText}` : "#";

  document.querySelector("#orderDetail").innerHTML = `
    <h2 class="font-display text-2xl font-extrabold">Detail Pesanan</h2>
    <div class="mt-5 grid gap-3 text-sm">
      <p><b>Kode Order:</b> ${order.order_code}</p>
      <p><b>Produk:</b> ${itemNames}</p>
      <p><b>Status:</b> ${statusLabels[order.status] || order.status}</p>
      <p><b>Total:</b> ${rupiah.format(Number(order.total_amount || 0))}</p>
    </div>
  `;

  document.querySelector("#paymentCard").innerHTML = `
    <h2 class="font-display text-2xl font-extrabold">QRIS</h2>
    <img class="mx-auto mt-5 h-72 w-72 rounded-3xl object-cover" src="${order.payment.qris_image}" alt="QRIS Dummy">
    <p class="mt-5 font-display text-3xl font-extrabold">${rupiah.format(Number(order.total_amount || 0))}</p>
    <ol class="mt-5 list-decimal space-y-2 pl-5 text-sm text-[var(--muted)]">
      <li>Scan QRIS.</li><li>Bayar sesuai total.</li><li>Simpan bukti pembayaran.</li><li>Hubungi admin untuk konfirmasi.</li>
    </ol>
    <div class="mt-6 grid gap-3 sm:grid-cols-2">
      <a class="primary-btn text-center" href="${waLink}" target="_blank" rel="noopener">Konfirmasi WhatsApp</a>
      <a class="small-btn text-center" href="order-status.php?code=${encodeURIComponent(order.order_code)}">Cek Status</a>
      <a class="small-btn text-center sm:col-span-2" href="index.php#produk">Kembali ke Katalog</a>
    </div>
  `;
}

loadOrder();
```

- [ ] **Step 2: Verify payment page**

Open:

```text
http://localhost/faydev/digital-store/payment.php?code=ORD-20260624-001
```

Expected: detail order, QRIS image, WhatsApp button, status button.

---

### Task 8: Order Status Page

**Files:**
- Create: `order-status.php`

- [ ] **Step 1: Create `order-status.php`**

Required IDs: `statusForm`, `orderCode`, `message`, `statusResult`.

Use logic:

```js
const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const statusLabels = { pending: "Menunggu Pembayaran", paid: "Pembayaran Diterima", completed: "Selesai", cancelled: "Dibatalkan" };
const code = new URLSearchParams(window.location.search).get("code");

function showMessage(message) {
  document.querySelector("#message").textContent = message;
  document.querySelector("#message").classList.remove("hidden");
}

async function loadStatus(orderCode) {
  const res = await apiGet(`/orders.php?code=${encodeURIComponent(orderCode)}`);
  if (!res.success) return showMessage(res.message || "Order tidak ditemukan.");
  const order = res.data;
  const items = (order.items || []).map((item) => `<li>${item.product_name} x${item.quantity}</li>`).join("");
  document.querySelector("#statusResult").innerHTML = `
    <div class="modal-card mx-auto max-w-2xl">
      <p class="badge">${statusLabels[order.status] || order.status}</p>
      <h2 class="mt-4 font-display text-2xl font-extrabold">${order.order_code}</h2>
      <div class="mt-5 grid gap-3 text-sm text-[var(--muted)]">
        <p><b>Nama:</b> ${order.customer_name}</p>
        <p><b>Total:</b> ${rupiah.format(Number(order.total_amount || 0))}</p>
        <p><b>Tanggal:</b> ${order.created_at}</p>
        <ul class="list-disc pl-5">${items}</ul>
        ${order.delivery_note ? `<p><b>Catatan Admin:</b> ${order.delivery_note}</p>` : ""}
      </div>
    </div>
  `;
}

document.querySelector("#statusForm").addEventListener("submit", (event) => {
  event.preventDefault();
  const value = document.querySelector("#orderCode").value.trim();
  if (value) window.location.href = `order-status.php?code=${encodeURIComponent(value)}`;
});

if (code) {
  document.querySelector("#orderCode").value = code;
  loadStatus(code);
}
```

- [ ] **Step 2: Verify status page**

Open:

```text
http://localhost/faydev/digital-store/order-status.php?code=ORD-20260624-001
```

Expected: status badge and order detail visible.

---

### Task 9: Landing Buy Button Redirect

**Files:**
- Modify: `assets/js/app.js:50-74`
- Modify: `assets/js/app.js:156-165`
- Modify: `assets/js/app.js:207-212`
- Modify: `index.php:108-115`

- [ ] **Step 1: Include product slug in normalized product**

Add to `normalizeProduct`:

```js
slug: product.slug || "",
```

- [ ] **Step 2: Change buy button data**

Replace buy button line with:

```js
<button class="small-btn buy-btn" type="button" ${stock.disabled ? "disabled" : ""} data-buy="${escapeText(product.slug)}">${stock.disabled ? "Habis" : "Beli Sekarang"}</button>
```

- [ ] **Step 3: Replace buy click behavior**

Replace:

```js
if (buy) openBuyModal(buy.dataset.buy);
```

with:

```js
if (buy && buy.dataset.buy) window.location.href = `checkout.php?product=${encodeURIComponent(buy.dataset.buy)}`;
```

- [ ] **Step 4: Remove dummy modal handlers**

Remove these event listeners if modal markup is removed:

```js
$("#closeModal").addEventListener("click", closeBuyModal);
$("#buyModal").addEventListener("click", (event) => { if (event.target.id === "buyModal") closeBuyModal(); });
```

- [ ] **Step 5: Remove dummy modal from `index.php`**

Delete lines for `#buyModal` dummy checkout block.

- [ ] **Step 6: Verify landing flow**

Open landing page and click active product `Beli Sekarang`.

Expected: browser navigates to `checkout.php?product=<slug>`. Out-of-stock product button says `Habis` and is disabled.

---

### Task 10: End-to-End Verification

**Files:**
- No code changes unless bugs found.

- [ ] **Step 1: Syntax check PHP files**

Run:

```bash
php -l api/checkout.php && php -l api/orders.php && php -l checkout.php && php -l payment.php && php -l order-status.php
```

Expected: `No syntax errors detected` for all files.

- [ ] **Step 2: Manual buyer flow**

Run in browser:

```text
index.php#produk → Beli Sekarang → checkout.php → Buat Pesanan → payment.php → Cek Status
```

Expected: order created, payment page loads, status page shows pending.

- [ ] **Step 3: Verify database rows**

Run SQL:

```sql
SELECT order_code, customer_name, total_amount, payment_method, status FROM orders ORDER BY id DESC LIMIT 1;
SELECT product_name, quantity, price, subtotal FROM order_items ORDER BY id DESC LIMIT 1;
```

Expected: latest order status `pending`, payment `QRIS`, item subtotal equals product price.

- [ ] **Step 4: Verify unavailable product rejection**

Try checkout with out-of-stock product slug:

```text
checkout.php?product=canva-pro-edu
```

Expected: `Produk sedang habis.` and no order created.

- [ ] **Step 5: Verify dashboard status impact**

Use existing dashboard API/UI to change latest order status to `paid` or `completed`, then refresh:

```text
order-status.php?code=<latest-code>
```

Expected: public status updates.

---

## Self-Review

- Spec coverage: DB, public APIs, checkout/payment/status pages, landing redirect, security validation, QRIS fallback, status mapping, manual verification covered.
- No placeholders: plan uses exact files, snippets, commands, expected results.
- Consistency: API names match PRD and design: `api/checkout.php`, `api/orders.php`, `checkout.php`, `payment.php`, `order-status.php`.
