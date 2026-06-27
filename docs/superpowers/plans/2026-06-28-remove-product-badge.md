# Remove Product Badge Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove product badge data and UI completely from admin, public pages, APIs, seed/static data, and database schema.

**Architecture:** Badge becomes a removed product attribute. Existing status badges in dashboard stay unchanged because they are UI status labels, not product marketing badges. Product CRUD and public product responses no longer accept, persist, select, or render `badge`.

**Tech Stack:** PHP, MySQL SQL schema/migrations, vanilla JavaScript, Tailwind-style markup.

---

## File Structure

- Modify: `dashboard/products.php` — remove admin product modal badge field.
- Modify: `dashboard/assets/js/dashboard.js` — remove product badge rendering/form binding/payload only; keep status badge helper.
- Modify: `dashboard/api/products.php` — remove product badge validation, INSERT, UPDATE.
- Modify: `api/products.php` — remove public `p.badge` select field.
- Modify: `assets/js/app.js` — remove landing page badge mapping/rendering.
- Modify: `product.php` — remove product detail badge rendering.
- Modify: `assets/js/data.js` — remove static product badge fields.
- Modify: `dashboard/assets/js/dashboard-data.js` — remove dashboard static product badge fields.
- Modify: `database/schema.sql` — remove `badge` column from products table.
- Create: `database/migrate-remove-product-badge.sql` — drop existing `products.badge` column.

### Task 1: Remove badge from admin product form

**Files:**
- Modify: `dashboard/products.php`

- [ ] **Step 1: Remove Badge label**

Delete this field from `dashboard/products.php`:

```html
<label>Badge<input class="input mt-1" id="productBadge" placeholder="Popular"></label>
```

- [ ] **Step 2: Verify PHP syntax**

Run:

```bash
php -l dashboard/products.php
```

Expected:

```text
No syntax errors detected in dashboard/products.php
```

### Task 2: Remove badge from admin products JavaScript

**Files:**
- Modify: `dashboard/assets/js/dashboard.js`

- [ ] **Step 1: Remove product badge table text**

Replace this product subtitle block:

```js
<p class="text-xs text-slate-500 dark:text-slate-400">${p.badge || ''}</p>
```

with an empty string or remove the line entirely from the template.

- [ ] **Step 2: Remove edit form binding**

Delete this line:

```js
$('#productBadge').value           = p.badge || '';
```

- [ ] **Step 3: Remove submit payload field**

Delete this payload entry:

```js
badge:          $('#productBadge').value,
```

- [ ] **Step 4: Verify no productBadge references remain**

Run:

```bash
rg "productBadge|p\.badge|badge:" dashboard/assets/js/dashboard.js
```

Expected: no `productBadge`, no `p.badge`, no product payload `badge:` matches. Status badge helper/function matches are allowed if searching broader `badge`.

### Task 3: Remove badge from admin products API

**Files:**
- Modify: `dashboard/api/products.php`

- [ ] **Step 1: Remove validation**

Delete:

```php
if (array_key_exists('badge', $body) && strlen(trim((string) $body['badge'])) > 50) $errors[] = 'badge maksimal 50 karakter';
```

- [ ] **Step 2: Remove INSERT column and value**

Change INSERT columns from:

```sql
(category_id, name, slug, description, price, original_price, stock, image_url, badge, status, is_featured)
```

to:

```sql
(category_id, name, slug, description, price, original_price, stock, image_url, status, is_featured)
```

Delete this bound value:

```php
$body['badge']          ?? null,
```

- [ ] **Step 3: Remove UPDATE badge assignment**

Change:

```sql
stock=?, image_url=?, badge=?, status=?, is_featured=?
```

to:

```sql
stock=?, image_url=?, status=?, is_featured=?
```

Delete this bound value:

```php
array_key_exists('badge', $body)     ? $body['badge']         : $current['badge'],
```

- [ ] **Step 4: Verify PHP syntax**

Run:

```bash
php -l dashboard/api/products.php
```

Expected:

```text
No syntax errors detected in dashboard/api/products.php
```

### Task 4: Remove badge from public API and pages

**Files:**
- Modify: `api/products.php`
- Modify: `assets/js/app.js`
- Modify: `product.php`

- [ ] **Step 1: Remove public API select field**

Delete `p.badge,` from `api/products.php` SELECT list.

- [ ] **Step 2: Remove landing page badge mapping/rendering**

In `assets/js/app.js`, delete:

```js
badge: product.badge || "",
```

Delete this render line:

```js
${product.badge ? `<span class="badge">${escapeText(product.badge)}</span>` : ""}
```

- [ ] **Step 3: Remove product detail badge rendering**

Delete this expression from `product.php`:

```js
${p.badge ? '<span class="mt-1 inline-block rounded bg-blue-500/10 px-2 py-0.5 text-xs font-semibold text-blue-500">' + escapeText(p.badge) + '</span>' : ''}
```

- [ ] **Step 4: Verify PHP syntax**

Run:

```bash
php -l api/products.php && php -l product.php
```

Expected:

```text
No syntax errors detected in api/products.php
No syntax errors detected in product.php
```

### Task 5: Remove badge from static product data

**Files:**
- Modify: `assets/js/data.js`
- Modify: `dashboard/assets/js/dashboard-data.js`

- [ ] **Step 1: Remove badge properties**

Delete every product object property matching:

```js
badge: "...",
```

from both files.

- [ ] **Step 2: Verify no static badge data remains**

Run:

```bash
rg "badge:" assets/js/data.js dashboard/assets/js/dashboard-data.js
```

Expected: no matches.

### Task 6: Remove badge from database schema and add migration

**Files:**
- Modify: `database/schema.sql`
- Create: `database/migrate-remove-product-badge.sql`

- [ ] **Step 1: Remove schema column**

Delete this line from `database/schema.sql`:

```sql
badge          VARCHAR(50) DEFAULT NULL,
```

- [ ] **Step 2: Create migration**

Create `database/migrate-remove-product-badge.sql` with:

```sql
ALTER TABLE products DROP COLUMN badge;
```

- [ ] **Step 3: Verify SQL references**

Run:

```bash
rg "badge" database
```

Expected: only historical docs/plans may mention badge; active schema and migrations should only include `migrate-remove-product-badge.sql`.

### Task 7: Final verification

**Files:**
- Verify all modified files

- [ ] **Step 1: Search active code for product badge**

Run:

```bash
rg "productBadge|p\.badge|product\.badge|badge:" dashboard products.php product.php api assets database --glob "!docs/**"
```

Expected: no product badge references. Dashboard status badge helper/classes may remain if matched by plain `badge`.

- [ ] **Step 2: PHP syntax check**

Run:

```bash
php -l dashboard/products.php && php -l dashboard/api/products.php && php -l api/products.php && php -l product.php
```

Expected: all report `No syntax errors detected`.

- [ ] **Step 3: Manual browser check**

Open:

```text
http://localhost/faydev/digital-store/dashboard/products.php
http://localhost/faydev/digital-store/
```

Expected: admin product modal has no Badge field; product cards/detail show no marketing badge; product create/edit still works.

## Self-Review

- Spec coverage: admin UI, admin API, public API, landing/detail UI, static data, schema, migration covered.
- Placeholder scan: no TBD/TODO/fill-later placeholders.
- Type consistency: removed property name is consistently `badge`; status badge helpers intentionally remain.
