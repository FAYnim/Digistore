# Order Status PRD Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the existing `/order-status.php` feature so it satisfies the MVP acceptance criteria in `PRD-page-check-order.md`.

**Architecture:** Keep `order-status.php` as the page shell, move rendering/flow into `assets/js/order-status.js`, and harden `api/orders.php` as the public order detail API. No broad redesign or unrelated refactor.

**Tech Stack:** PHP, PDO prepared statements, vanilla JavaScript, Tailwind utility classes, existing `assets/js/api.js` helper.

---

### Task 1: Harden Public Order API

**Files:**
- Modify: `api/orders.php`

- [ ] **Step 1: Validate order code**

Add validation after reading `$_GET['code']`:

```php
$code = strtoupper(trim($_GET['code'] ?? ''));
if ($code === '') json_error('Kode order wajib diisi.', null, 422);
if (strlen($code) > 50 || !preg_match('/^[A-Z0-9-]+$/', $code)) json_error('Kode order tidak valid.', null, 422);
```

- [ ] **Step 2: Return public customer fields**

Update the order SELECT to include:

```sql
customer_email,
customer_phone,
```

Keep `id` only internally for item lookup, then `unset($order['id']);` before response.

- [ ] **Step 3: Verify PHP syntax**

Run:

```bash
php -l api/orders.php
```

Expected: `No syntax errors detected in api/orders.php`.

---

### Task 2: Split Order Status JavaScript

**Files:**
- Modify: `order-status.php`
- Create: `assets/js/order-status.js`

- [ ] **Step 1: Simplify page shell**

In `order-status.php`, keep existing HTML/nav/form containers, remove inline rendering script, keep:

```html
<script src="assets/js/api.js"></script>
<script src="assets/js/order-status.js"></script>
```

- [ ] **Step 2: Create JS state and helpers**

Create `assets/js/order-status.js` with helpers:

```js
const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const statusLabels = { pending: "Menunggu Pembayaran", paid: "Pembayaran Diterima", completed: "Selesai", cancelled: "Dibatalkan" };
const statusStyles = { pending: "bg-yellow-100 text-yellow-800", paid: "bg-blue-100 text-blue-800", completed: "bg-green-100 text-green-800", cancelled: "bg-slate-200 text-slate-700" };
const code = new URLSearchParams(window.location.search).get("code");

function escapeText(value) {
  return String(value ?? "").replace(/[&<>'"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[char]));
}
```

- [ ] **Step 3: Add state render functions**

Implement:

```js
function showMessage(message) { ... }
function hideMessage() { ... }
function renderEmpty() { ... }
function renderLoading() { ... }
function renderError(message) { ... }
```

Use copy from PRD: `Memuat pesanan...`, `Order tidak ditemukan.`, `Masukkan kode order untuk cek pesanan.`.

---

### Task 3: Render PRD-Compliant Order Detail

**Files:**
- Modify: `assets/js/order-status.js`

- [ ] **Step 1: Add status instruction mapper**

Implement:

```js
function getStatusInstruction(order) {
  if (order.status === "pending") return "Selesaikan pembayaran lalu konfirmasi ke admin.";
  if (order.status === "paid") return "Pembayaran sudah diterima. Pesanan sedang diproses.";
  if (order.status === "completed") return "Pesanan sudah selesai.";
  if (order.status === "cancelled") return "Pesanan ini dibatalkan. Hubungi admin jika butuh bantuan.";
  return "Status pesanan berhasil dimuat.";
}
```

- [ ] **Step 2: Add WhatsApp/action renderer**

Build WhatsApp URL from `order.payment.admin_whatsapp` and `order.payment.whatsapp_message`.

Render:

- Pending: payment, WhatsApp, catalog.
- Paid/completed/cancelled: WhatsApp, catalog.

- [ ] **Step 3: Add complete detail renderer**

Render desktop two-column layout and mobile one-column naturally via Tailwind grid:

- Detail Pesanan card.
- Customer card.
- Item list card.
- Status & Aksi card.
- Payment summary card.
- Delivery note card for completed status.

- [ ] **Step 4: Add fetch flow**

Implement:

```js
async function loadStatus(orderCode) {
  hideMessage();
  renderLoading();
  try {
    const res = await apiGet(`/orders.php?code=${encodeURIComponent(orderCode)}`);
    if (!res.success) return renderError(res.message || "Order tidak ditemukan.");
    renderOrder(res.data);
  } catch (error) {
    renderError("Gagal memuat pesanan.");
  }
}
```

---

### Task 4: Wire Form, Theme, Initial Load

**Files:**
- Modify: `assets/js/order-status.js`

- [ ] **Step 1: Move theme toggle logic**

Move existing `updateThemeIcon()` and `themeToggle` click listener from inline JS into `assets/js/order-status.js`.

- [ ] **Step 2: Wire form submit**

On submit:

```js
event.preventDefault();
const value = document.querySelector("#orderCode").value.trim().toUpperCase();
if (!value) return showMessage("Kode order wajib diisi.");
history.replaceState(null, "", `order-status.php?code=${encodeURIComponent(value)}`);
loadStatus(value);
```

- [ ] **Step 3: Initial page behavior**

If code exists, set input value and call `loadStatus(code.toUpperCase())`.
If no code, call `renderEmpty()`.

---

### Task 5: Verify Against PRD

**Files:**
- Test: browser/manual and CLI

- [ ] **Step 1: PHP syntax checks**

Run:

```bash
php -l api/orders.php
php -l order-status.php
php -l payment.php
```

Expected: no syntax errors.

- [ ] **Step 2: Check available package scripts**

If `package.json` exists, inspect scripts and run lint/typecheck/build if available.

- [ ] **Step 3: Manual acceptance checks**

Verify:

1. `/order-status.php` shows empty state and form.
2. Empty submit shows `Kode order wajib diisi.`.
3. Invalid code format shows `Kode order tidak valid.`.
4. Unknown order shows `Order tidak ditemukan.`.
5. Pending order shows payment + WhatsApp + catalog buttons.
6. Paid order shows processing instruction + admin/catalog buttons.
7. Completed with delivery note shows note.
8. Completed without note shows fallback note text.
9. Cancelled order shows cancelled message.
10. `payment.php` still links to `order-status.php?code=...`.
