# Product Accounts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace manual product stock with premium account inventory stored in `product_accounts`.

**Architecture:** Products remain the sellable package. A new `product_accounts` table stores free-form account text per product and acts as the stock source. Checkout reserves stock at order creation by moving one account from `available` to `reserved`; payment acceptance converts it to `sold` and copies account data into `orders.delivery_note`.

**Tech Stack:** PHP, PDO/MySQL, vanilla JS dashboard, existing REST-style PHP API files.

---

## File Structure

- Create `database/migrate-product-accounts.sql`: migration for `product_accounts` and stock backfill sync.
- Modify `database/schema.sql`: include `product_accounts` in fresh installs.
- Modify `dashboard/products.php`: remove stock input, add account textarea.
- Modify `dashboard/assets/js/dashboard.js`: submit `accounts_text`, render computed stock, fill account textarea on edit.
- Modify `dashboard/api/products.php`: remove stock validation/input, parse account textarea, return computed stock.
- Modify `api/products.php`: public product stock uses count of available accounts.
- Modify `api/checkout.php`: reserve one available account transactionally instead of decrementing `products.stock`.
- Modify `dashboard/api/orders.php`: accepted payment marks reserved account sold and injects account text into delivery note.
- Modify `api/orders.php`: returns delivery note already present in existing order response.

## Scope Check

This plan touches admin inventory, public product stock, checkout reservation, and payment delivery. They are coupled by one invariant: stock equals accounts available, and a purchase must allocate exactly one account.

---

### Task 1: Database table and fresh schema

**Files:**
- Create: `database/migrate-product-accounts.sql`
- Modify: `database/schema.sql`

- [ ] **Step 1: Create migration file**

Create `database/migrate-product-accounts.sql`:

```sql
CREATE TABLE IF NOT EXISTS product_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    account_data TEXT NOT NULL,
    status ENUM('available', 'reserved', 'sold') NOT NULL DEFAULT 'available',
    order_id INT NULL,
    sold_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_product_accounts_product_status (product_id, status),
    INDEX idx_product_accounts_order (order_id)
);
```

- [ ] **Step 2: Add table to fresh schema**

In `database/schema.sql`, insert the same `CREATE TABLE IF NOT EXISTS product_accounts` block after `order_items` table, because it depends on `products` and `orders`.

- [ ] **Step 3: Syntax check SQL manually**

Run in MySQL client/phpMyAdmin:

```sql
SOURCE database/migrate-product-accounts.sql;
SHOW TABLES LIKE 'product_accounts';
DESCRIBE product_accounts;
```

Expected: table exists with `status` enum `available,reserved,sold`.

- [ ] **Step 4: Commit**

```bash
git add database/migrate-product-accounts.sql database/schema.sql
git commit -m "feat: add product accounts inventory table"
```

---

### Task 2: Admin product API computed stock and account creation

**Files:**
- Modify: `dashboard/api/products.php`

- [ ] **Step 1: Update list/detail SELECT stock source**

Replace product list/detail stock selection with a computed count:

```sql
COALESCE(account_counts.available_stock, 0) AS stock
```

Join this derived table in both list and detail queries:

```sql
LEFT JOIN (
    SELECT product_id, COUNT(*) AS available_stock
    FROM product_accounts
    WHERE status = 'available'
    GROUP BY product_id
) account_counts ON account_counts.product_id = p.id
```

Expected list/detail JSON still contains integer `stock`.

- [ ] **Step 2: Remove manual stock validation**

In `validate_product_payload()`, remove the required numeric `stock` check. Keep validation for `name`, `price`, `original_price`, `status`, `image_url`, `description`, `is_featured`.

- [ ] **Step 3: Add account parser helper**

Add this helper near existing helpers:

```php
function parse_accounts_text(string $text): array
{
    $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));
    if ($normalized === '') {
        return [];
    }

    $chunks = preg_split('/\n\s*\n|\n/', $normalized);
    $accounts = [];

    foreach ($chunks as $chunk) {
        $account = trim($chunk);
        if ($account !== '') {
            $accounts[] = $account;
        }
    }

    return $accounts;
}
```

- [ ] **Step 4: Add insert helper**

Add this helper:

```php
function insert_product_accounts(PDO $pdo, int $product_id, array $accounts): void
{
    if (empty($accounts)) {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO product_accounts (product_id, account_data, status) VALUES (?, ?, "available")');
    foreach ($accounts as $account) {
        $stmt->execute([$product_id, $account]);
    }
}
```

- [ ] **Step 5: Modify create product insert**

Remove `stock` from product INSERT columns and values. After product insert, run:

```php
$accounts = parse_accounts_text((string)($input['accounts_text'] ?? ''));
insert_product_accounts($pdo, (int)$pdo->lastInsertId(), $accounts);
```

- [ ] **Step 6: Modify update product path**

Remove `stock = ?` from product UPDATE. After product update, run:

```php
$accounts = parse_accounts_text((string)($input['accounts_text'] ?? ''));
insert_product_accounts($pdo, $id, $accounts);
```

- [ ] **Step 7: Include available accounts in detail response**

For GET detail by id, after product fetch, add:

```php
$account_stmt = $pdo->prepare('SELECT id, account_data, status, order_id, sold_at, created_at FROM product_accounts WHERE product_id = ? ORDER BY id DESC');
$account_stmt->execute([$id]);
$product['accounts'] = $account_stmt->fetchAll();
```

- [ ] **Step 8: Lint API**

Run:

```bash
php -l dashboard/api/products.php
```

Expected: `No syntax errors detected in dashboard/api/products.php`.

- [ ] **Step 9: Commit**

```bash
git add dashboard/api/products.php
git commit -m "feat: manage product accounts in admin API"
```

---

### Task 3: Admin product modal account textarea

**Files:**
- Modify: `dashboard/products.php`
- Modify: `dashboard/assets/js/dashboard.js`

- [ ] **Step 1: Replace stock input with account textarea**

In `dashboard/products.php`, remove the `stock` form group. Add:

```html
<div class="form-group full-width">
    <label for="productAccounts">Data Akun Premium</label>
    <textarea id="productAccounts" name="accounts_text" rows="8" placeholder="1 akun per baris, contoh:\nemail@example.com | password123 | PIN 1234"></textarea>
    <small>Masukkan akun baru saja. Akun yang sudah tersimpan tidak perlu diinput ulang.</small>
</div>
```

- [ ] **Step 2: Remove stock field from JS submit payload**

In `initProducts()` submit handler, remove:

```js
stock: Number(form.stock.value),
```

Add:

```js
accounts_text: form.accounts_text.value.trim(),
```

- [ ] **Step 3: Clear textarea on add**

In the add product button handler/reset path, ensure:

```js
form.accounts_text.value = '';
```

- [ ] **Step 4: Fill textarea empty on edit**

In `editProduct(id)`, do not preload existing accounts into textarea for resubmission. Set:

```js
form.accounts_text.value = '';
```

- [ ] **Step 5: Show existing accounts summary in modal**

After fetching product detail, render available/sold account counts if an existing helper area exists. If none exists, add under textarea:

```html
<div id="productAccountsSummary" class="muted"></div>
```

Then set in JS:

```js
const summary = document.getElementById('productAccountsSummary');
if (summary) {
    const accounts = product.accounts || [];
    const available = accounts.filter((account) => account.status === 'available').length;
    const reserved = accounts.filter((account) => account.status === 'reserved').length;
    const sold = accounts.filter((account) => account.status === 'sold').length;
    summary.textContent = product.id ? `Tersedia: ${available} | Reserved: ${reserved} | Terjual: ${sold}` : '';
}
```

- [ ] **Step 6: Lint changed PHP**

Run:

```bash
php -l dashboard/products.php
```

Expected: `No syntax errors detected in dashboard/products.php`.

- [ ] **Step 7: Browser smoke test**

Open `dashboard/products.php`, create product with textarea:

```text
akun1@email.com | pass-a | pin 1111
akun2@email.com | pass-b | pin 2222
```

Expected: saved product row shows stock `2`.

- [ ] **Step 8: Commit**

```bash
git add dashboard/products.php dashboard/assets/js/dashboard.js
git commit -m "feat: replace stock input with account textarea"
```

---

### Task 4: Public product stock from accounts

**Files:**
- Modify: `api/products.php`

- [ ] **Step 1: Add computed stock join**

In public product query, replace selected `p.stock` with:

```sql
COALESCE(account_counts.available_stock, 0) AS stock
```

Add join:

```sql
LEFT JOIN (
    SELECT product_id, COUNT(*) AS available_stock
    FROM product_accounts
    WHERE status = 'available'
    GROUP BY product_id
) account_counts ON account_counts.product_id = p.id
```

- [ ] **Step 2: Keep response casting**

Keep existing cast:

```php
$product['stock'] = (int)$product['stock'];
```

- [ ] **Step 3: Lint public API**

Run:

```bash
php -l api/products.php
```

Expected: `No syntax errors detected in api/products.php`.

- [ ] **Step 4: Manual API test**

Open:

```text
/api/products.php
```

Expected: products include `stock` matching available `product_accounts` rows.

- [ ] **Step 5: Commit**

```bash
git add api/products.php
git commit -m "feat: derive public product stock from accounts"
```

---

### Task 5: Checkout reserves one account

**Files:**
- Modify: `api/checkout.php`

- [ ] **Step 1: Replace product stock lock query**

Keep transaction. Replace product lock query with:

```php
$stmt = $pdo->prepare('SELECT id, name, price, status FROM products WHERE id = ? FOR UPDATE');
$stmt->execute([$input['product_id']]);
$product = $stmt->fetch();
```

- [ ] **Step 2: Add account reservation query**

After product active check, add:

```php
$account_stmt = $pdo->prepare('SELECT id FROM product_accounts WHERE product_id = ? AND status = "available" ORDER BY id ASC LIMIT 1 FOR UPDATE');
$account_stmt->execute([$product['id']]);
$account = $account_stmt->fetch();

if (!$account) {
    throw new Exception('Stok akun tidak tersedia');
}
```

- [ ] **Step 3: Remove products stock decrement**

Delete the `UPDATE products SET stock = stock - ?` block and rowCount guard.

- [ ] **Step 4: Reserve account after order insert**

After `$order_id = $pdo->lastInsertId();`, add:

```php
$reserve_stmt = $pdo->prepare('UPDATE product_accounts SET status = "reserved", order_id = ? WHERE id = ? AND status = "available"');
$reserve_stmt->execute([$order_id, $account['id']]);

if ($reserve_stmt->rowCount() !== 1) {
    throw new Exception('Gagal mengalokasikan akun');
}
```

- [ ] **Step 5: Lint checkout API**

Run:

```bash
php -l api/checkout.php
```

Expected: `No syntax errors detected in api/checkout.php`.

- [ ] **Step 6: Manual checkout test**

Checkout product with one available account.

Expected:
- order created
- one `product_accounts` row changes `available` → `reserved`
- public product stock decreases by 1

- [ ] **Step 7: Commit**

```bash
git add api/checkout.php
git commit -m "feat: reserve product account at checkout"
```

---

### Task 6: Payment acceptance delivers reserved account

**Files:**
- Modify: `dashboard/api/orders.php`

- [ ] **Step 1: Load reserved account during accepted verification**

Inside `verify_payment`, after confirmation/order fetch and before updating order status, add when `$status === 'accepted'`:

```php
$account_stmt = $pdo->prepare('SELECT id, account_data FROM product_accounts WHERE order_id = ? AND status = "reserved" LIMIT 1 FOR UPDATE');
$account_stmt->execute([$confirmation['order_id']]);
$reserved_account = $account_stmt->fetch();

if (!$reserved_account) {
    throw new Exception('Tidak ada akun yang direservasi untuk order ini');
}
```

- [ ] **Step 2: Mark account sold and set delivery note**

When `$status === 'accepted'`, replace the order status-only update with:

```php
$sold_stmt = $pdo->prepare('UPDATE product_accounts SET status = "sold", sold_at = NOW() WHERE id = ? AND status = "reserved"');
$sold_stmt->execute([$reserved_account['id']]);

if ($sold_stmt->rowCount() !== 1) {
    throw new Exception('Gagal mengirim akun premium');
}

$order_stmt = $pdo->prepare('UPDATE orders SET status = "delivered", delivery_note = ? WHERE id = ?');
$order_stmt->execute([$reserved_account['account_data'], $confirmation['order_id']]);
```

For rejected verification, keep order status as `pending_payment` and do not release account in this plan.

- [ ] **Step 3: Preserve confirmation update**

Keep existing `payment_confirmations` update logic so accepted/rejected/admin note still saves.

- [ ] **Step 4: Lint dashboard orders API**

Run:

```bash
php -l dashboard/api/orders.php
```

Expected: `No syntax errors detected in dashboard/api/orders.php`.

- [ ] **Step 5: Manual delivery test**

Create checkout, upload payment proof, accept payment in admin.

Expected:
- order status becomes `delivered`
- `orders.delivery_note` contains account text
- reserved account becomes `sold`
- order status page shows delivery note if current UI renders it

- [ ] **Step 6: Commit**

```bash
git add dashboard/api/orders.php
git commit -m "feat: deliver reserved account on payment approval"
```

---

### Task 7: Final verification

**Files:**
- Verify: `dashboard/api/products.php`
- Verify: `api/products.php`
- Verify: `api/checkout.php`
- Verify: `dashboard/api/orders.php`
- Verify: `dashboard/products.php`

- [ ] **Step 1: Run PHP lint batch**

Run:

```bash
php -l dashboard/api/products.php && php -l api/products.php && php -l api/checkout.php && php -l dashboard/api/orders.php && php -l dashboard/products.php
```

Expected: every file prints `No syntax errors detected`.

- [ ] **Step 2: End-to-end manual test**

Use one product with two account rows:

```text
buyer-one@example.com | pass1
buyer-two@example.com | pass2
```

Expected flow:
- dashboard stock shows `2`
- public product stock shows `2`
- checkout first order → stock `1`, account `reserved`
- accept payment → order `delivered`, first account `sold`, delivery note has account text
- checkout second order → stock `0` after reservation
- third checkout fails with `Stok akun tidak tersedia`

- [ ] **Step 3: Check git diff**

Run:

```bash
git diff --stat
```

Expected: only planned files changed.

- [ ] **Step 4: Commit verification fixes if any**

```bash
git add database/migrate-product-accounts.sql database/schema.sql dashboard/products.php dashboard/assets/js/dashboard.js dashboard/api/products.php api/products.php api/checkout.php dashboard/api/orders.php
git commit -m "chore: verify product account inventory flow"
```

Skip this commit if there are no new changes since Task 6.

---

## Self-Review

- Spec coverage: admin textarea, `product_accounts`, computed stock, checkout allocation, delivery on payment success, empty stock handling covered by Tasks 1-7.
- Placeholder scan: no placeholder-only implementation steps remain.
- Type consistency: request field is `accounts_text`; table uses `account_data`; statuses are `available`, `reserved`, `sold`; order link column is `order_id`.
