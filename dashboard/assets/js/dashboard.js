/**
 * dashboard.js — Dashboard logic, terhubung ke API backend
 */

/* ----------------------------------------------------------------
 * Utilities
 * --------------------------------------------------------------- */
const $  = (s) => document.querySelector(s);
const $$ = (s) => [...document.querySelectorAll(s)];
const rupiah = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
const slugify = (s) => s.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_]+/g, '-').replace(/^-+|-+$/g, '');
const shortCode = (s) => s && s.length > 12 ? `${s.slice(0, 8)}…${s.slice(-3)}` : s;

function statusLabel(s) {
  const map = { active: 'Aktif', draft: 'Draft', out_of_stock: 'Habis', pending: 'Menunggu', paid: 'Dibayar', completed: 'Selesai', cancelled: 'Batal', visible: 'Tampil', hidden: 'Sembunyi' };
  return map[s] ?? s;
}

function badgeClass(s) {
  if (['active', 'paid', 'completed', 'visible'].includes(s)) return 'badge-green';
  if (['pending', 'draft'].includes(s))                        return 'badge-yellow';
  if (['out_of_stock', 'cancelled', 'hidden'].includes(s))    return 'badge-red';
  return 'badge-gray';
}

function badge(s) {
  return `<span class="badge ${badgeClass(s)}">${statusLabel(s)}</span>`;
}

function openModal(id)  { $(id)?.classList.add('open'); }
function closeModals()  { $$('.modal').forEach((m) => m.classList.remove('open')); }

/* ----------------------------------------------------------------
 * Shell — sidebar, theme toggle, modal close
 * --------------------------------------------------------------- */
function initShell() {
  $('#openSidebar')?.addEventListener('click', () => {
    $('#sidebar')?.classList.remove('-translate-x-full');
    $('#sidebarOverlay')?.classList.remove('hidden');
  });
  $('#sidebarOverlay')?.addEventListener('click', () => {
    $('#sidebar')?.classList.add('-translate-x-full');
    $('#sidebarOverlay')?.classList.add('hidden');
  });
  $('#themeToggle')?.addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('digistore-dashboard-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
  });
  $$('[data-close-modal]').forEach((btn) => btn.addEventListener('click', closeModals));
  $$('.modal').forEach((modal) => modal.addEventListener('click', (e) => { if (e.target === modal) closeModals(); }));
}

/* ----------------------------------------------------------------
 * Overview (index.php)
 * --------------------------------------------------------------- */
async function renderOverview() {
  const res = await api.get('/dashboard/api/stats.php');
  if (!res.success) { showToast(res.message, 'error'); return; }
  const d = res.data;

  const stats = [
    ['Produk Tersedia',       d.available_products ?? d.active_products, 'fa-solid fa-circle-check'],
    ['Produk Habis',          d.out_of_stock_products,                   'fa-solid fa-box-open'],
    ['Produk Diproses',       d.processing_products,                     'fa-solid fa-spinner'],
    ['Penghasilan Hari Ini',  rupiah(d.today_income),                    'fa-solid fa-money-bill-wave'],
    ['Pesanan Hari Ini',      d.today_orders,                            'fa-solid fa-receipt'],
    ['Total Produk',          d.total_products,                          'fa-solid fa-box'],
    ['Total Penghasilan',     rupiah(d.total_income),                    'fa-solid fa-chart-line'],
    ['Rating',                d.average_rating || '-',                   'fa-solid fa-star'],
  ];
  $('#statsGrid').innerHTML = stats.map(([label, value, icon]) =>
    `<div class="card p-5">
       <div class="mb-8 flex items-start justify-between gap-4">
         <p class="text-[13px] font-black text-slate-500 dark:text-slate-400">${label}</p>
         <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-xl text-white"><i class="${icon}"></i></div>
       </div>
       <p class="text-3xl font-black leading-none text-slate-950 dark:text-white">${value}</p>
     </div>`
  ).join('');

  $('#recentOrders').innerHTML = (d.recent_orders || []).map((o) =>
    `<tr>
       <td class="font-black" title="${o.order_code}">${shortCode(o.order_code)}</td>
       <td>${o.customer_name}</td>
       <td class="max-w-[160px] truncate">${o.products ?? '-'}</td>
       <td class="font-bold">${rupiah(o.total_amount)}</td>
       <td>${badge(o.status)}</td>
     </tr>`
  ).join('') || '<tr><td colspan="5" class="text-center text-slate-400">Belum ada pesanan</td></tr>';

  $('#popularProducts').innerHTML = (d.featured_products || []).map((p) =>
    `<div class="flex items-center gap-3 rounded-2xl border border-slate-200 p-3 dark:border-slate-800">
       <img class="h-14 w-14 rounded-xl object-cover" src="${p.image_url || 'https://placehold.co/100'}" loading="lazy" alt="">
       <div class="min-w-0 flex-1">
         <p class="truncate font-black">${p.name}</p>
         <p class="text-sm text-slate-500 dark:text-slate-400">${rupiah(p.price)}</p>
       </div>
       <span class="text-xs font-bold text-slate-500">${p.sold_count} terjual</span>
     </div>`
  ).join('') || '<p class="text-sm text-slate-400">Belum ada produk unggulan.</p>';
}

/* ----------------------------------------------------------------
 * Products (products.php)
 * --------------------------------------------------------------- */
let _categories = []; // cache kategori untuk dropdown

async function loadCategoryOptions() {
  if (_categories.length) return;
  const res = await api.get('/dashboard/api/categories.php');
  if (res.success) _categories = res.data;
}

async function renderProducts() {
  await loadCategoryOptions();

  const keyword    = ($('#productSearch')?.value || '').trim();
  const categoryId = $('#productCategoryFilter')?.value || '';
  const params     = new URLSearchParams();
  if (keyword)    params.set('search', keyword);
  if (categoryId) params.set('category_id', categoryId);

  const res = await api.get(`/dashboard/api/products.php?${params}`);
  if (!res.success) { showToast(res.message, 'error'); return; }

  const products = res.data;

  // Fill category dropdown filter
  const filterEl = $('#productCategoryFilter');
  if (filterEl && filterEl.options.length <= 1) {
    filterEl.innerHTML = `<option value="">Semua kategori</option>` +
      _categories.map((c) => `<option value="${c.id}">${c.name}</option>`).join('');
  }

  // Fill form category dropdown
  const formCat = $('#productCategory');
  if (formCat && formCat.options.length === 0) {
    formCat.innerHTML = `<option value="">-- Pilih Kategori --</option>` +
      _categories.map((c) => `<option value="${c.id}">${c.name}</option>`).join('');
  }

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
       <td>${p.category_name || '—'}</td>
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
  await loadCategoryOptions();

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

  const formCat = $('#productCategory');
  if (formCat) {
    formCat.innerHTML = `<option value="">-- Pilih Kategori --</option>` +
      _categories.map((c) => `<option value="${c.id}" ${c.id == p.category_id ? 'selected' : ''}>${c.name}</option>`).join('');
  }
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
  $('#productCategoryFilter')?.addEventListener('change', renderProducts);
  $('#addProductBtn')?.addEventListener('click', () => { resetProductForm(); openModal('#productModal'); });

  // Auto-slug dari nama
  $('#productName')?.addEventListener('input', () => {
    if (!$('#productId').value) {
      $('#productSlug').value = slugify($('#productName').value);
    }
  });

  $('#productForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('#productId').value;
    const payload = {
      category_id:    $('#productCategory').value ? parseInt($('#productCategory').value) : null,
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

  $('#confirmDelete')?.addEventListener('click', async () => {
    if (!_deleteProductId) return;
    const res = await api.delete(`/dashboard/api/products.php?id=${_deleteProductId}`);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderProducts();
    _deleteProductId = null;
  });
}

/* ----------------------------------------------------------------
 * Categories (categories.php)
 * --------------------------------------------------------------- */
async function renderCategories() {
  const res = await api.get('/dashboard/api/categories.php');
  if (!res.success) { showToast(res.message, 'error'); return; }
  const cats = res.data;

  $('#categoriesTable').innerHTML = cats.map((c) =>
    `<tr>
       <td class="font-black"><i class="${c.icon || 'fa-solid fa-tag'} mr-2 text-slate-400"></i>${c.name}</td>
       <td>${c.slug}</td>
       <td>${c.product_count}</td>
       <td>${badge(c.status)}</td>
       <td>
         <div class="flex gap-2">
           <button class="btn-soft" onclick="editCategory(${c.id})"><i class="fa-solid fa-pen mr-1"></i>Edit</button>
           <button class="btn-soft" onclick="askDeleteCategory(${c.id}, '${c.name.replace(/'/g, "\\'")}')"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
         </div>
       </td>
     </tr>`
  ).join('');
}

window.editCategory = async (id) => {
  const res = await api.get(`/dashboard/api/categories.php?id=${id}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  const c = res.data;
  $('#categoryModalTitle').textContent = 'Edit Kategori';
  $('#categoryId').value     = c.id;
  $('#categoryName').value   = c.name;
  $('#categorySlug').value   = c.slug;
  $('#categoryIcon').value   = c.icon || '';
  $('#categoryStatus').value = c.status;
  openModal('#categoryModal');
};

let _deleteCategoryId = null;
window.askDeleteCategory = (id, name) => {
  _deleteCategoryId = id;
  $('#deleteCategoryModalName').textContent = name;
  openModal('#deleteCategoryModal');
};

function initCategories() {
  renderCategories();
  $('#addCategoryBtn')?.addEventListener('click', () => {
    $('#categoryForm').reset(); $('#categoryId').value = '';
    $('#categoryModalTitle').textContent = 'Tambah Kategori';
    openModal('#categoryModal');
  });

  // Auto-slug
  $('#categoryName')?.addEventListener('input', () => {
    if (!$('#categoryId').value) $('#categorySlug').value = slugify($('#categoryName').value);
  });

  $('#categoryForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('#categoryId').value;
    const payload = {
      name:       $('#categoryName').value,
      slug:       $('#categorySlug').value,
      icon:       $('#categoryIcon').value || null,
      status:     $('#categoryStatus').value,
      sort_order: 0,
    };
    const res = id
      ? await api.put(`/dashboard/api/categories.php?id=${id}`, payload)
      : await api.post('/dashboard/api/categories.php', payload);
    if (!res.success) { showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderCategories();
    _categories = []; // reset cache
  });

  $('#confirmDeleteCategory')?.addEventListener('click', async () => {
    if (!_deleteCategoryId) return;
    const res = await api.delete(`/dashboard/api/categories.php?id=${_deleteCategoryId}`);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderCategories();
    _categories = [];
    _deleteCategoryId = null;
  });
}

/* ----------------------------------------------------------------
 * Orders (orders.php)
 * --------------------------------------------------------------- */
async function renderOrders() {
  const statusFilter = $('#orderStatusFilter')?.value || '';
  const search       = $('#orderSearch')?.value.trim() || '';
  const params       = new URLSearchParams();
  if (statusFilter) params.set('status', statusFilter);
  if (search)       params.set('search', search);

  const res = await api.get(`/dashboard/api/orders.php?${params}`);
  if (!res.success) { showToast(res.message, 'error'); return; }

  $('#ordersTable').innerHTML = res.data.map((o) =>
    `<tr>
       <td class="font-black">${o.order_code}</td>
       <td>${o.customer_name}</td>
       <td>${o.items_summary || '—'}</td>
       <td class="font-bold">${rupiah(o.total_amount)}</td>
       <td>${badge(o.status)}</td>
       <td class="text-sm text-slate-500">${o.created_at?.slice(0, 10) || ''}</td>
       <td><button class="btn-soft" onclick="showOrder(${o.id})">Detail</button></td>
     </tr>`
  ).join('') || '<tr><td colspan="7" class="text-center text-slate-400">Tidak ada pesanan.</td></tr>';
}

window.showOrder = async (id) => {
  const res = await api.get(`/dashboard/api/orders.php?id=${id}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  const o = res.data;

  const fields = [
    ['Kode Order',  o.order_code],
    ['Customer',    o.customer_name],
    ['Email',       o.customer_email || '—'],
    ['Nomor HP',    o.customer_phone || '—'],
    ['Total',       rupiah(o.total_amount)],
    ['Pembayaran',  o.payment_method || '—'],
    ['Status',      badge(o.status)],
    ['Catatan',     o.note || '—'],
    ['Tanggal',     o.created_at?.slice(0, 10) || ''],
  ];
  $('#orderDetail').innerHTML = fields.map(([k, v]) =>
    `<div class="flex justify-between gap-4 border-b border-slate-200 pb-2 dark:border-slate-800">
       <span class="font-bold text-slate-500 dark:text-slate-400">${k}</span>
       <span class="text-right font-black">${v}</span>
     </div>`
  ).join('');

  if (o.items?.length) {
    $('#orderDetail').innerHTML += `
      <div class="md:col-span-2 mt-3">
        <p class="mb-2 font-black">Produk:</p>
        ${o.items.map((i) => `
          <div class="flex justify-between gap-4 border-b border-slate-100 py-1 dark:border-slate-800 text-sm">
            <span>${i.product_name} x${i.quantity}</span>
            <span class="font-bold">${rupiah(i.subtotal)}</span>
          </div>`).join('')}
      </div>`;
  }

  $('#orderStatusSelect').value = o.status;
  $('#orderStatusSelect').dataset.id = o.id;
  $('#orderDeliveryNote').value = o.delivery_note || '';
  openModal('#orderModal');
};

function initOrders() {
  renderOrders();
  $('#orderStatusFilter')?.addEventListener('change', renderOrders);
  $('#orderSearch')?.addEventListener('input', renderOrders);

  $('#saveOrderStatus')?.addEventListener('click', async () => {
    const id = $('#orderStatusSelect').dataset.id;
    const status = $('#orderStatusSelect').value;
    const delivery_note = $('#orderDeliveryNote').value.trim();
    const res = await api.put(`/dashboard/api/orders.php?id=${id}`, { status, delivery_note });
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderOrders();
  });
}

/* ----------------------------------------------------------------
 * Testimonials (testimonials.php)
 * --------------------------------------------------------------- */
async function renderTestimonials() {
  const res = await api.get('/dashboard/api/testimonials.php');
  if (!res.success) { showToast(res.message, 'error'); return; }

  $('#testimonialsTable').innerHTML = res.data.map((t) =>
    `<tr>
       <td class="font-black">${t.name}</td>
       <td>${t.role || '—'}</td>
       <td>${'<i class="fa-solid fa-star text-yellow-400"></i>'.repeat(t.rating)}</td>
       <td class="max-w-[200px] truncate">${t.message}</td>
       <td>${badge(t.status)}</td>
       <td>
         <div class="flex gap-2">
           <button class="btn-soft" onclick="editTestimonial(${t.id})"><i class="fa-solid fa-pen mr-1"></i>Edit</button>
           <button class="btn-soft" onclick="askDeleteTestimonial(${t.id}, '${t.name.replace(/'/g, "\\'")}')"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
         </div>
       </td>
     </tr>`
  ).join('') || '<tr><td colspan="6" class="text-center text-slate-400">Belum ada testimoni.</td></tr>';
}

window.editTestimonial = async (id) => {
  const res = await api.get(`/dashboard/api/testimonials.php?id=${id}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  const t = res.data;
  $('#testimonialModalTitle').textContent = 'Edit Testimoni';
  $('#testimonialId').value      = t.id;
  $('#testimonialName').value    = t.name;
  $('#testimonialRole').value    = t.role || '';
  $('#testimonialRating').value  = t.rating;
  $('#testimonialMessage').value = t.message;
  $('#testimonialStatus').value  = t.status;
  openModal('#testimonialModal');
};

let _deleteTestimonialId = null;
window.askDeleteTestimonial = (id, name) => {
  _deleteTestimonialId = id;
  $('#deleteTestimonialModalName').textContent = name;
  openModal('#deleteTestimonialModal');
};

function initTestimonials() {
  renderTestimonials();
  $('#addTestimonialBtn')?.addEventListener('click', () => {
    $('#testimonialForm').reset();
    $('#testimonialId').value = '';
    $('#testimonialModalTitle').textContent = 'Tambah Testimoni';
    openModal('#testimonialModal');
  });

  $('#testimonialForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('#testimonialId').value;
    const payload = {
      name:    $('#testimonialName').value,
      role:    $('#testimonialRole').value || null,
      rating:  parseInt($('#testimonialRating').value),
      message: $('#testimonialMessage').value,
      status:  $('#testimonialStatus').value,
    };
    const res = id
      ? await api.put(`/dashboard/api/testimonials.php?id=${id}`, payload)
      : await api.post('/dashboard/api/testimonials.php', payload);
    if (!res.success) { showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderTestimonials();
  });

  $('#confirmDeleteTestimonial')?.addEventListener('click', async () => {
    if (!_deleteTestimonialId) return;
    const res = await api.delete(`/dashboard/api/testimonials.php?id=${_deleteTestimonialId}`);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderTestimonials();
    _deleteTestimonialId = null;
  });
}

/* ----------------------------------------------------------------
 * Settings (settings.php)
 * --------------------------------------------------------------- */
async function initSettings() {
  const res = await api.get('/dashboard/api/settings.php');
  if (res.success) {
    const d = res.data;
    $('#settingStoreName').value        = d.store_name        || '';
    $('#settingStoreTagline').value     = d.store_tagline     || '';
    $('#settingStoreDescription').value = d.store_description || '';
    $('#settingWhatsapp').value         = d.store_whatsapp    || '';
    $('#settingEmail').value            = d.store_email       || '';
    $('#settingInstagram').value        = d.store_instagram   || '';
    $('#settingTheme').value            = d.default_theme     || 'light';
    $('#settingAccentColor').value      = d.accent_color      || '#2563EB';
  }

  $('#settingsForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      store_name:        $('#settingStoreName').value,
      store_tagline:     $('#settingStoreTagline').value,
      store_description: $('#settingStoreDescription').value,
      store_whatsapp:    $('#settingWhatsapp').value,
      store_email:       $('#settingEmail').value,
      store_instagram:   $('#settingInstagram').value,
      default_theme:     $('#settingTheme').value,
      accent_color:      $('#settingAccentColor').value,
    };
    const res = await api.put('/dashboard/api/settings.php', payload);
    if (!res.success) { showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error'); return; }
    showToast(res.message);
  });
}

/* ----------------------------------------------------------------
 * Init
 * --------------------------------------------------------------- */
initShell();
const page = $('[data-page]')?.dataset.page;
if (page === 'overview')     renderOverview();
if (page === 'products')     initProducts();
if (page === 'categories')   initCategories();
if (page === 'orders')       initOrders();
if (page === 'testimonials') initTestimonials();
if (page === 'settings')     initSettings();
