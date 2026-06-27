# Hide Category Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Sembunyikan manajemen kategori dari admin UI dan jadikan `Akun Premium` sebagai kategori default di DB/API untuk create/update produk.

**Architecture:** UI produk tidak lagi menampilkan atau mengirim kategori. Sidebar tidak lagi menampilkan menu kategori. Admin products API tetap mempertahankan relasi kategori dan menyediakan helper kecil untuk mencari/membuat kategori default saat `category_id` kosong.

**Tech Stack:** PHP procedural, PDO/MySQL, vanilla JavaScript dashboard, Tailwind utility classes, existing JSON API helpers.

---

## File Structure

- Modify: `dashboard/components/sidebar.php`
  - Responsibility: navigasi dashboard admin. Hapus item `Kategori` dari `$navItems` agar halaman kategori tersembunyi dari UI utama.
- Modify: `dashboard/products.php`
  - Responsibility: markup halaman manajemen produk. Hapus filter kategori, kolom kategori, dan field kategori dari form produk.
- Modify: `dashboard/assets/js/dashboard.js`
  - Responsibility: behavior dashboard produk. Hapus fetch/cache kategori dari flow produk, hapus filter kategori dari query, hapus kolom kategori dari render table, dan hapus `category_id` dari payload submit.
- Modify: `dashboard/api/products.php`
  - Responsibility: CRUD produk admin. Tambah helper default category dan pakai helper saat create/update produk tanpa `category_id`.

## Manual Test Prerequisites

- Jalankan app via XAMPP/Apache seperti development lokal saat ini.
- Login admin dashboard.
- Buka `http://localhost/faydev/digital-store/dashboard/products.php`.

---

### Task 1: Hide category navigation from sidebar

**Files:**
- Modify: `dashboard/components/sidebar.php:2-10`

- [ ] **Step 1: Inspect current sidebar nav**

Run:

```bash
git diff -- dashboard/components/sidebar.php
```

Expected: no relevant local changes, or only changes you intentionally keep.

- [ ] **Step 2: Remove category nav item**

Replace `dashboard/components/sidebar.php:2-10` with:

```php
$navItems = [
    ['label' => 'Overview', 'href' => 'index.php', 'key' => 'overview', 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Produk', 'href' => 'products.php', 'key' => 'products', 'icon' => 'fa-solid fa-box'],
    ['label' => 'Pesanan', 'href' => 'orders.php', 'key' => 'orders', 'icon' => 'fa-solid fa-receipt'],
    ['label' => 'Pembayaran', 'href' => 'settings-payment.php', 'key' => 'payment-settings', 'icon' => 'fa-solid fa-qrcode'],
    ['label' => 'Testimoni', 'href' => 'testimonials.php', 'key' => 'testimonials', 'icon' => 'fa-solid fa-comments'],
    ['label' => 'Setting', 'href' => 'settings.php', 'key' => 'settings', 'icon' => 'fa-solid fa-gear'],
];
```

- [ ] **Step 3: Validate PHP syntax**

Run:

```bash
php -l dashboard/components/sidebar.php
```

Expected:

```text
No syntax errors detected in dashboard/components/sidebar.php
```

- [ ] **Step 4: Manual verify sidebar**

Open dashboard in browser. Expected: sidebar contains `Overview`, `Produk`, `Pesanan`, `Pembayaran`, `Testimoni`, `Setting`; no `Kategori` item.

- [ ] **Step 5: Commit**

```bash
git add dashboard/components/sidebar.php
git commit -m "chore: hide category navigation"
```

---

### Task 2: Hide category controls from products page markup

**Files:**
- Modify: `dashboard/products.php:12-20`

- [ ] **Step 1: Inspect current products page changes**

Run:

```bash
git diff -- dashboard/products.php
```

Expected: no relevant local changes, or only changes you intentionally keep.

- [ ] **Step 2: Replace products page markup**

Replace `dashboard/products.php:12-20` with:

```php
  <div class="card p-4">
    <input class="input" id="productSearch" placeholder="Cari produk" type="search">
  </div>
  <div class="card table-wrap"><table><thead><tr><th>Produk</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="productsTable"></tbody></table><div class="hidden p-8 text-center text-sm font-bold text-slate-500" id="productsEmpty">Produk tidak ditemukan.</div></div>
</section>
<div class="modal fixed inset-0 z-50 place-items-center bg-slate-950/60 p-4" id="productModal"><div class="card max-h-[90vh] w-full max-w-3xl overflow-auto p-5"><div class="mb-4 flex items-center justify-between"><h3 class="text-xl font-black" id="productModalTitle">Tambah Produk</h3><button class="btn-soft" data-close-modal type="button">Tutup</button></div><form class="grid gap-3 md:grid-cols-2" id="productForm"><input type="hidden" id="productId"><label>Nama Produk<input class="input mt-1" id="productName" required placeholder="Google AI Pro"></label><label>Slug<input class="input mt-1" id="productSlug" required placeholder="google-ai-pro"></label><label>Harga<input class="input mt-1" id="productPrice" required type="number" placeholder="25000"></label><label>Harga Coret<input class="input mt-1" id="productOriginalPrice" type="number" placeholder="50000"></label><label>Stok<input class="input mt-1" id="productStock" required type="number" placeholder="12"></label><label>Status<select class="select mt-1" id="productStatus"><option value="active">Aktif</option><option value="draft">Draft</option><option value="out_of_stock">Habis</option></select></label><label>Badge<input class="input mt-1" id="productBadge" placeholder="Popular"></label><label>Gambar URL<input class="input mt-1" id="productImage" placeholder="https://placehold.co/600x400"></label><label class="md:col-span-2">Deskripsi<textarea class="textarea mt-1" id="productDescription" rows="3" placeholder="Lorem ipsum dolor sit amet."></textarea></label><label class="flex items-center gap-2 font-bold"><input id="productFeatured" type="checkbox"> Featured</label><div class="flex justify-end gap-2 md:col-span-2"><button class="btn-soft" data-close-modal type="button">Batal</button><button class="btn-primary" type="submit">Simpan</button></div></form></div></div>
```

- [ ] **Step 3: Validate PHP syntax**

Run:

```bash
php -l dashboard/products.php
```

Expected:

```text
No syntax errors detected in dashboard/products.php
```

- [ ] **Step 4: Manual verify products UI**

Open `dashboard/products.php`. Expected: search input exists; no category filter; product table header has `Produk`, `Harga`, `Stok`, `Status`, `Aksi`; product modal has no `Kategori` field.

- [ ] **Step 5: Commit**

```bash
git add dashboard/products.php
git commit -m "chore: hide product category controls"
```

---

### Task 3: Remove category handling from products JavaScript

**Files:**
- Modify: `dashboard/assets/js/dashboard.js:220-364`

- [ ] **Step 1: Inspect current JS changes**

Run:

```bash
git diff -- dashboard/assets/js/dashboard.js
```

Expected: no relevant local changes, or only changes you intentionally keep.

- [ ] **Step 2: Replace products JS block**

Replace `dashboard/assets/js/dashboard.js:220-364` with:

```js
async function renderProducts() {
  const keyword = ($('#productSearch')?.value || '').trim();
  const params  = new URLSearchParams();
  if (keyword) params.set('search', keyword);

  const res = await api.get(`/dashboard/api/products.php?${params}`);
  if (!res.success) { showToast(res.message, 'error'); return; }

  const products = res.data;

  $('#productsTable').innerHTML = products.map((p) =>
    `<tr>
       <td>
         <div class="flex items-center gap-3">
           <img class="h-12 w-12 rounded-xl object-cover" src="${p.image_url || 'https://placehold.co/100'}" loading="lazy" alt="">
           <div>
             <p class="font-black">${p.name}</p>
             <p class="text-xs text-slate-500 dark:text-slate-400">${p.badge || ''}</p>
           </div>
         </div>
       </td>
       <td class="font-bold">${rupiah(p.price)}</td>
       <td>${p.stock}</td>
       <td>${badge(p.status)}</td>
       <td>
         <div class="flex gap-2">
           <button class="btn-soft" onclick="editProduct(${p.id})">Edit</button>
           <button class="btn-soft" onclick="askDeleteProduct(${p.id}, '${p.name.replace(/'/g, "\\'")}')">Hapus</button>
         </div>
       </td>
     </tr>`
  ).join('');
  $('#productsEmpty')?.classList.toggle('hidden', products.length > 0);
}

function resetProductForm() {
  $('#productForm').reset();
  $('#productId').value = '';
  $('#productImage').value = '';
  $('#productModalTitle').textContent = 'Tambah Produk';
}

window.editProduct = async (id) => {
  const res = await api.get(`/dashboard/api/products.php?id=${id}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  const p = res.data;

  $('#productModalTitle').textContent = 'Edit Produk';
  $('#productId').value              = p.id;
  $('#productName').value            = p.name;
  $('#productSlug').value            = p.slug;
  $('#productPrice').value           = p.price;
  $('#productOriginalPrice').value   = p.original_price || '';
  $('#productStock').value           = p.stock;
  $('#productStatus').value          = p.status;
  $('#productBadge').value           = p.badge || '';
  $('#productImage').value           = p.image_url || '';
  $('#productDescription').value     = p.description || '';
  $('#productFeatured').checked      = !!p.is_featured;

  openModal('#productModal');
};

let _deleteProductId = null;
window.askDeleteProduct = (id, name) => {
  _deleteProductId = id;
  $('#deleteModalName').textContent = name;
  openModal('#deleteModal');
};

function initProducts() {
  renderProducts();
  $('#productSearch')?.addEventListener('input', renderProducts);
  $('#addProductBtn')?.addEventListener('click', () => { resetProductForm(); openModal('#productModal'); });

  $('#productName')?.addEventListener('input', () => {
    if (!$('#productId').value) {
      $('#productSlug').value = slugify($('#productName').value);
    }
  });

  $('#productForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('#productId').value;
    const payload = {
      name:           $('#productName').value,
      slug:           $('#productSlug').value,
      description:    $('#productDescription').value,
      price:          parseInt($('#productPrice').value),
      original_price: $('#productOriginalPrice').value ? parseInt($('#productOriginalPrice').value) : null,
      stock:          parseInt($('#productStock').value),
      image_url:      $('#productImage').value,
      badge:          $('#productBadge').value,
      status:         $('#productStatus').value,
      is_featured:    $('#productFeatured').checked,
    };

    const res = id
      ? await api.put(`/dashboard/api/products.php?id=${id}`, payload)
      : await api.post('/dashboard/api/products.php', payload);

    if (!res.success) {
      showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error');
      return;
    }
    showToast(res.message);
    closeModals();
    renderProducts();
  });
```

- [ ] **Step 3: Search for stale product category selectors**

Run:

```bash
rg "productCategory|productCategoryFilter|loadCategoryOptions|_categories" dashboard/assets/js/dashboard.js dashboard/products.php
```

Expected: no matches for product-specific category UI. Matches inside the categories management block for `_categories` reset are acceptable only if Task 3 removed the top-level `_categories` variable; if `rg` reports `_categories = []` at category save/delete lines, remove those two assignments too because the cache no longer exists.

- [ ] **Step 4: Browser console verification**

Open `dashboard/products.php`, then open browser devtools console. Expected: no JavaScript error mentioning `_categories`, `productCategory`, or `productCategoryFilter`.

- [ ] **Step 5: Commit**

```bash
git add dashboard/assets/js/dashboard.js
git commit -m "chore: remove category handling from products UI"
```

---

### Task 4: Add default category fallback to products API

**Files:**
- Modify: `dashboard/api/products.php:22-54`
- Modify: `dashboard/api/products.php:137-143`
- Modify: `dashboard/api/products.php:198-200`

- [ ] **Step 1: Inspect current API changes**

Run:

```bash
git diff -- dashboard/api/products.php
```

Expected: no relevant local changes, or only changes you intentionally keep.

- [ ] **Step 2: Add default category helper after validation function**

Insert this code after `validate_product_payload()` and before `switch ($method)`:

```php
function default_product_category_id(PDO $pdo): int
{
    $slug = 'akun-premium';

    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if ($row) return (int) $row['id'];

    $insert = $pdo->prepare(
        'INSERT INTO categories (name, slug, icon, status, sort_order)
         VALUES (?, ?, ?, ?, ?)'
    );
    $insert->execute(['Akun Premium', $slug, 'fa-solid fa-crown', 'active', 1]);

    return (int) $pdo->lastInsertId();
}

function product_category_id_from_payload(PDO $pdo, array $body): int
{
    if (empty($body['category_id'])) return default_product_category_id($pdo);

    $catId = (int) $body['category_id'];
    $catChk = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
    $catChk->execute([$catId]);
    if (!$catChk->fetch()) json_error('category_id tidak valid', null, 422);

    return $catId;
}
```

- [ ] **Step 3: Replace POST category validation**

Replace `dashboard/api/products.php:137-143` with:

```php
        $catId = product_category_id_from_payload($pdo, $body);
```

- [ ] **Step 4: Replace PUT category selection**

Replace `dashboard/api/products.php:198-200` with:

```php
        $catId = array_key_exists('category_id', $body)
            ? product_category_id_from_payload($pdo, $body)
            : default_product_category_id($pdo);
```

This intentionally moves products edited from the new UI to default category because the UI no longer sends `category_id`.

- [ ] **Step 5: Validate PHP syntax**

Run:

```bash
php -l dashboard/api/products.php
```

Expected:

```text
No syntax errors detected in dashboard/api/products.php
```

- [ ] **Step 6: Manual API create test without category_id**

From browser UI, add product:

```text
Nama Produk: Test Premium Account
Slug: test-premium-account
Harga: 10000
Stok: 1
Status: Draft
```

Expected: product saved successfully. In DB, product row has `category_id` pointing to categories.slug `akun-premium`.

- [ ] **Step 7: Manual API update test without category_id**

Edit `Test Premium Account`, change badge to:

```text
Plan Test
```

Expected: product saved successfully. In DB, product still has `category_id` pointing to categories.slug `akun-premium`.

- [ ] **Step 8: Manual compatibility test with explicit category_id**

Use an API client or browser console authenticated in dashboard session to send a PUT/POST payload with valid `category_id`. Expected: API accepts valid category id. Send invalid `category_id: 999999`; expected HTTP 422 response with message `category_id tidak valid`.

- [ ] **Step 9: Commit**

```bash
git add dashboard/api/products.php
git commit -m "fix: default products to premium account category"
```

---

### Task 5: Final verification

**Files:**
- Verify: `dashboard/components/sidebar.php`
- Verify: `dashboard/products.php`
- Verify: `dashboard/assets/js/dashboard.js`
- Verify: `dashboard/api/products.php`

- [ ] **Step 1: Run PHP syntax checks**

Run:

```bash
php -l dashboard/components/sidebar.php && php -l dashboard/products.php && php -l dashboard/api/products.php
```

Expected:

```text
No syntax errors detected in dashboard/components/sidebar.php
No syntax errors detected in dashboard/products.php
No syntax errors detected in dashboard/api/products.php
```

- [ ] **Step 2: Search for hidden category UI remnants**

Run:

```bash
rg "productCategory|productCategoryFilter|<th>Kategori</th>|<label>Kategori|Semua kategori" dashboard/products.php dashboard/assets/js/dashboard.js
```

Expected: no output.

- [ ] **Step 3: Confirm category page still exists but is not linked**

Run:

```bash
rg "categories.php" dashboard/components/sidebar.php dashboard
```

Expected: no match in `dashboard/components/sidebar.php`; matches in `dashboard/categories.php`, `dashboard/api/categories.php`, or category JS block can remain.

- [ ] **Step 4: Browser smoke test**

In dashboard browser:

```text
1. Sidebar has no Kategori menu.
2. Products page has no category filter/table column/form field.
3. Add product succeeds without choosing category.
4. Edit product succeeds without choosing category.
5. Browser console has no product category selector errors.
```

- [ ] **Step 5: Review final diff**

Run:

```bash
git diff --stat HEAD~4..HEAD
```

Expected: only these files changed:

```text
dashboard/components/sidebar.php
dashboard/products.php
dashboard/assets/js/dashboard.js
dashboard/api/products.php
```

- [ ] **Step 6: Final commit if any uncommitted verification fixes exist**

Run:

```bash
git status --short
```

Expected: clean working tree. If files remain modified due to verification fixes:

```bash
git add dashboard/components/sidebar.php dashboard/products.php dashboard/assets/js/dashboard.js dashboard/api/products.php
git commit -m "chore: verify hidden category management flow"
```

---

## Self-Review

- Spec coverage: dashboard menu/filter/column/form field hidden in Tasks 1-3; frontend no longer sends `category_id` in Task 3; API default category fallback in Task 4; DB is non-destructive because no schema migration is planned.
- Placeholder scan: no `TBD`, no incomplete tasks, no deferred error handling.
- Type consistency: PHP helper names `default_product_category_id` and `product_category_id_from_payload` are used consistently. JS selectors removed match hidden markup.
