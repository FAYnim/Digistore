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
const escapeHtml = (v) => String(v ?? '').replace(/[&<>'"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));

function statusLabel(s) {
  const map = { active: 'Aktif', draft: 'Draft', out_of_stock: 'Habis', pending: 'Menunggu Pembayaran', pending_payment: 'Menunggu Pembayaran', pending_verify: 'Menunggu Verifikasi', paid: 'Selesai', processing: 'Selesai', delivered: 'Selesai', completed: 'Selesai', expired: 'Expired', cancelled: 'Batal', visible: 'Tampil', hidden: 'Sembunyi', accepted: 'Diterima', rejected: 'Ditolak' };
  return map[s] ?? s;
}

function badgeClass(s) {
  if (['active', 'paid', 'processing', 'delivered', 'completed', 'visible', 'accepted', 'available'].includes(s)) return 'badge-green';
  if (['pending', 'pending_payment', 'pending_verify', 'draft', 'reserved'].includes(s)) return 'badge-yellow';
  if (['out_of_stock', 'expired', 'cancelled', 'hidden', 'rejected', 'sold'].includes(s)) return 'badge-red';
  return 'badge-gray';
}

function badge(s) {
  return `<span class="badge ${badgeClass(s)}">${statusLabel(s)}</span>`;
}

let incomeChartInstance = null;
let orderStatusChartInstance = null;
let latestOverviewData = null;

function isDarkMode() {
  return document.documentElement.classList.contains('dark');
}

function chartTextColor() {
  return isDarkMode() ? '#cbd5e1' : '#475569';
}

function chartGridColor() {
  return isDarkMode() ? 'rgba(51, 65, 85, .7)' : 'rgba(226, 232, 240, .9)';
}

function destroyChart(chart) {
  if (chart) chart.destroy();
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
    window.dispatchEvent(new Event('dashboard-theme-change'));
  });
  $$('[data-close-modal]').forEach((btn) => btn.addEventListener('click', closeModals));
  $$('.modal').forEach((modal) => modal.addEventListener('click', (e) => { if (e.target === modal) closeModals(); }));
  window.addEventListener('dashboard-theme-change', () => {
    if (latestOverviewData && $('#incomeChart')) renderOverviewCharts(latestOverviewData);
  });
}

/* ----------------------------------------------------------------
 * Overview (index)
 * --------------------------------------------------------------- */
function renderOverviewCharts(data) {
  if (typeof Chart === 'undefined') return;

  const incomeRows = data.income_chart || [];
  const statusRows = data.order_status_chart || [];
  const incomeHasData = incomeRows.some((row) => Number(row.total) > 0);
  const statusHasData = statusRows.some((row) => Number(row.total) > 0);

  $('#incomeChartEmpty')?.classList.toggle('hidden', incomeHasData);
  $('#orderStatusChartEmpty')?.classList.toggle('hidden', statusHasData);

  destroyChart(incomeChartInstance);
  destroyChart(orderStatusChartInstance);

  const incomeEl = $('#incomeChart');
  const statusEl = $('#orderStatusChart');

  if (incomeEl) {
    incomeChartInstance = new Chart(incomeEl, {
      type: 'line',
      data: {
        labels: incomeRows.map((row) => row.label),
        datasets: [{
          label: 'Pendapatan',
          data: incomeRows.map((row) => Number(row.total)),
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, .12)',
          fill: true,
          tension: .35,
          pointRadius: 4,
          pointHoverRadius: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => rupiah(ctx.parsed.y),
            },
          },
        },
        scales: {
          x: {
            ticks: { color: chartTextColor(), font: { weight: '700' } },
            grid: { color: chartGridColor() },
          },
          y: {
            ticks: { color: chartTextColor(), callback: (value) => rupiah(value) },
            grid: { color: chartGridColor() },
            beginAtZero: true,
          },
        },
      },
    });
  }

  if (statusEl) {
    orderStatusChartInstance = new Chart(statusEl, {
      type: 'doughnut',
      data: {
        labels: statusRows.map((row) => row.label),
        datasets: [{
          data: statusRows.map((row) => Number(row.total)),
          backgroundColor: ['#f59e0b', '#2563eb', '#16a34a', '#dc2626', '#64748b'],
          borderColor: isDarkMode() ? '#0f172a' : '#ffffff',
          borderWidth: 3,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: chartTextColor(), font: { weight: '700' } },
          },
        },
        cutout: '62%',
      },
    });
  }
}

async function renderOverview() {
  const res = await api.get('/dashboard/api/stats');
  if (!res.success) { showToast(res.message, 'error'); return; }
  const d = res.data;
  latestOverviewData = d;

  const stats = [
    ['Produk Tersedia',       d.available_products ?? d.active_products, 'fa-solid fa-circle-check'],
    ['Produk Habis',          d.out_of_stock_products,                   'fa-solid fa-box-open'],
    ['Produk Diproses',       d.processing_products,                     'fa-solid fa-spinner'],
    ['Penghasilan Hari Ini',  rupiah(d.today_income),                    'fa-solid fa-money-bill-wave'],
    ['Pesanan Hari Ini',      d.today_orders,                            'fa-solid fa-receipt'],
    ['Total Produk',          d.total_products,                          'fa-solid fa-box'],
    ['Total Penghasilan',     rupiah(d.total_income),                    'fa-solid fa-chart-line'],
    ['Total Penjualan',      d.total_sales ?? 0,                              'fa-solid fa-bag-shopping'],
  ];
  $('#statsGrid').innerHTML = stats.map(([label, value, icon]) =>
    `<div class="card p-5">
       <div class="mb-8 flex items-start justify-between gap-4">
         <p class="text-[13px] font-black text-slate-500 dark:text-slate-400">${label}</p>
          <div class="stat-icon flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-xl text-white"><i class="${icon}"></i></div>
        </div>
        <p class="stat-value text-3xl font-black leading-none text-slate-950 dark:text-white">${value}</p>
     </div>`
  ).join('');

  renderOverviewCharts(d);

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
       <img class="h-14 w-14 rounded-xl object-cover" src="${p.image_url ? '../' + p.image_url : 'https://placehold.co/100'}" loading="lazy" alt="">
       <div class="min-w-0 flex-1">
         <p class="truncate font-black">${p.name}</p>
         <p class="text-sm text-slate-500 dark:text-slate-400">${rupiah(p.price)}</p>
       </div>
       <span class="text-xs font-bold text-slate-500">${p.sold_count} terjual</span>
     </div>`
  ).join('') || '<p class="text-sm text-slate-400">Belum ada produk unggulan.</p>';
}

/* ----------------------------------------------------------------
 * Products (products)
 * --------------------------------------------------------------- */
async function renderProducts() {
  const keyword = ($('#productSearch')?.value || '').trim();
  const params  = new URLSearchParams();
  if (keyword) params.set('search', keyword);

  const res = await api.get(`/dashboard/api/products?${params}`);
  if (!res.success) { showToast(res.message, 'error'); return; }

  const products = res.data;

  $('#productsTable').innerHTML = products.map((p) =>
    `<tr>
       <td>
         <div class="flex items-center gap-3">
           <img class="h-12 w-12 rounded-xl object-cover" src="${p.image_url ? '../' + p.image_url : 'https://placehold.co/100'}" loading="lazy" alt="">
            <div>
              <p class="font-black">${p.name}</p>
            </div>
         </div>
       </td>
       <td class="font-bold">${rupiah(p.price)}</td>
       <td>${p.stock}</td>
       <td>${badge(p.status)}</td>
       <td>
         <div class="flex gap-2">
           <button class="btn-soft" onclick="editProduct(${p.id})">Edit</button>
            <button class="btn-soft" onclick="askDeleteProduct(${p.id}, '${p.name.replace(/'/g, "\\'")}')">Arsipkan</button>
         </div>
       </td>
     </tr>`
  ).join('');
  $('#productsEmpty')?.classList.toggle('hidden', products.length > 0);
}

let productAccounts = [];
let tempProductAccountId = -1;

function renderProductAccounts() {
  const accounts = productAccounts || [];
  const available = accounts.filter((account) => account.status === 'available').length;
  const reserved = accounts.filter((account) => account.status === 'reserved').length;
  const sold = accounts.filter((account) => account.status === 'sold').length;
  $('#productAccountsSummary').textContent = `Tersedia: ${available} | Reserved: ${reserved} | Terjual: ${sold}`;
  $('#productAccountsTable').innerHTML = accounts.map((account) =>
    `<tr>
       <td><pre class="max-w-md whitespace-pre-wrap text-xs font-bold text-slate-600 dark:text-slate-300">${escapeHtml(account.account_data)}</pre></td>
       <td>${badge(account.status)}</td>
       <td>
         <div class="flex flex-wrap gap-2">
           <button class="btn-soft" type="button" onclick="editProductAccount(${account.id})">Edit</button>
           <button class="btn-soft" type="button" onclick="deleteProductAccount(${account.id})">Hapus</button>
           <button class="btn-soft" type="button" onclick="duplicateProductAccount(${account.id})">Duplikat</button>
         </div>
       </td>
     </tr>`
  ).join('');
  $('#productAccountsEmpty')?.classList.toggle('hidden', accounts.length > 0);
}

async function loadProductAccounts(productId) {
  if (!productId) {
    productAccounts = [];
    renderProductAccounts();
    return;
  }

  const res = await api.get(`/dashboard/api/product-accounts?product_id=${productId}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  productAccounts = res.data || [];
  renderProductAccounts();
  renderProducts();
}

function resetProductForm() {
  $('#productForm').reset();
  $('#productId').value = '';
  $('#productImage').value = '';
  $('#productModalTitle').textContent = 'Tambah Produk';
  productImageOldPath = '';
  productImageShowPlaceholder();
  productAccounts = [];
  renderProductAccounts();
}

window.editProduct = async (id) => {
  const res = await api.get(`/dashboard/api/products?id=${id}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  const p = res.data;

  $('#productModalTitle').textContent = 'Edit Produk';
  $('#productId').value              = p.id;
  $('#productName').value            = p.name;
  $('#productPrice').value           = p.price;
  $('#productOriginalPrice').value   = p.original_price || '';
  $('#productStatus').value          = p.status;
  $('#productImage').value           = p.image_url || '';
  $('#productDescription').value     = p.description || '';
  $('#productFeatured').checked      = !!p.is_featured;
  productImageSetFromUrl(p.image_url || '');

  productAccounts = p.accounts || [];
  renderProductAccounts();
  openModal('#productModal');
};

window.editProductAccount = (id) => {
  const account = productAccounts.find((item) => Number(item.id) === Number(id));
  if (!account) return;
  $('#accountModalTitle').textContent = 'Edit Akun';
  $('#accountId').value = account.id;
  $('#accountData').value = account.account_data || '';
  $('#accountStatus').value = account.status || 'available';
  openModal('#accountModal');
};

window.deleteProductAccount = async (id) => {
  const account = productAccounts.find((item) => Number(item.id) === Number(id));
  if (!account) return;
  const force = ['reserved', 'sold'].includes(account.status) ? confirm(`Akun status ${account.status}. Tetap hapus?`) : true;
  if (!force) return;

  if (Number(id) < 0) {
    productAccounts = productAccounts.filter((item) => Number(item.id) !== Number(id));
    renderProductAccounts();
    return;
  }

  const res = await api.delete(`/dashboard/api/product-accounts?id=${id}${force ? '&force=1' : ''}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  showToast(res.message);
  loadProductAccounts($('#productId').value);
};

window.duplicateProductAccount = async (id) => {
  const productId = $('#productId').value;
  const account = productAccounts.find((item) => Number(item.id) === Number(id));
  if (!account) return;

  if (!productId || Number(id) < 0) {
    productAccounts.unshift({ ...account, id: tempProductAccountId--, status: 'available' });
    renderProductAccounts();
    return;
  }

  const res = await api.post('/dashboard/api/product-accounts', { product_id: parseInt(productId), source_id: id, account_data: account.account_data });
  if (!res.success) { showToast(res.message, 'error'); return; }
  showToast(res.message);
  loadProductAccounts(productId);
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
  initProductImageDropzone();
  $('#addProductBtn')?.addEventListener('click', () => { resetProductForm(); openModal('#productModal'); });

  $('#productForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('#productId').value;
    const payload = {
      name:           $('#productName').value,
      description:    $('#productDescription').value,
      price:          parseInt($('#productPrice').value),
      original_price: $('#productOriginalPrice').value ? parseInt($('#productOriginalPrice').value) : null,
      accounts_text:  '',
      image_url:      $('#productImage').value,
      status:         $('#productStatus').value,
      is_featured:    $('#productFeatured').checked,
    };

    const res = id
      ? await api.put(`/dashboard/api/products?id=${id}`, payload)
      : await api.post('/dashboard/api/products', payload);

    if (!res.success) {
      showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error');
      return;
    }
    const savedProductId = res.data?.id || id;
    const pendingAccounts = productAccounts.filter((account) => Number(account.id) < 0);
    for (const account of pendingAccounts) {
      const accountRes = await api.post('/dashboard/api/product-accounts', { product_id: parseInt(savedProductId), account_data: account.account_data, status: account.status });
      if (!accountRes.success) { showToast(accountRes.message, 'error'); return; }
    }
    showToast(res.message);
    $('#productId').value = savedProductId;
    closeModals();
    renderProducts();
  });

  $('#addProductAccountBtn')?.addEventListener('click', () => {
    $('#accountModalTitle').textContent = 'Tambah Akun';
    $('#accountForm').reset();
    $('#accountId').value = '';
    $('#accountStatus').value = 'available';
    openModal('#accountModal');
  });

  $('#accountForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const productId = $('#productId').value;
    const accountId = $('#accountId').value;
    const payload = {
      product_id: productId ? parseInt(productId) : 0,
      account_data: $('#accountData').value,
      status: $('#accountStatus').value,
    };

    if (!productId || Number(accountId) < 0) {
      if (accountId) {
        productAccounts = productAccounts.map((account) => Number(account.id) === Number(accountId) ? { ...account, account_data: payload.account_data, status: payload.status } : account);
      } else {
        productAccounts.unshift({ id: tempProductAccountId--, product_id: 0, account_data: payload.account_data, status: payload.status });
      }
      closeModals();
      openModal('#productModal');
      renderProductAccounts();
      return;
    }

    const res = accountId
      ? await api.put(`/dashboard/api/product-accounts?id=${accountId}`, payload)
      : await api.post('/dashboard/api/product-accounts', payload);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    openModal('#productModal');
    loadProductAccounts(productId);
  });

  $('#confirmDelete')?.addEventListener('click', async () => {
    if (!_deleteProductId) return;
    const res = await api.delete(`/dashboard/api/products?id=${_deleteProductId}`);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderProducts();
    _deleteProductId = null;
  });
}

/* ----------------------------------------------------------------
 * Categories (categories)
 * --------------------------------------------------------------- */
async function renderCategories() {
  const res = await api.get('/dashboard/api/categories');
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
  const res = await api.get(`/dashboard/api/categories?id=${id}`);
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
      ? await api.put(`/dashboard/api/categories?id=${id}`, payload)
      : await api.post('/dashboard/api/categories', payload);
    if (!res.success) { showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderCategories();
  });

  $('#confirmDeleteCategory')?.addEventListener('click', async () => {
    if (!_deleteCategoryId) return;
    const res = await api.delete(`/dashboard/api/categories?id=${_deleteCategoryId}`);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderCategories();
    _deleteCategoryId = null;
  });
}

/* ----------------------------------------------------------------
 * Orders (orders)
 * --------------------------------------------------------------- */
let activeOrderTab = 'pending_payment';
let orderQueueCounts = {};

function getQueueFromStatus(status, pendingConfirmations = 0) {
  if (pendingConfirmations > 0) return 'pending_verify';
  const map = {
    pending_payment: 'pending_payment',
    pending: 'pending_payment',
    pending_verify: 'pending_verify',
    paid: 'completed',
    processing: 'completed',
    delivered: 'completed',
    completed: 'completed',
    expired: 'expired',
    cancelled: 'cancelled',
  };
  return map[status] || 'pending_payment';
}

function isOrderUrgent(createdAt, status) {
  if (['pending_payment', 'pending_verify', 'pending'].includes(status)) {
    const created = new Date(createdAt);
    const now = new Date();
    const hours = (now - created) / (1000 * 60 * 60);
    return hours > 24;
  }
  return false;
}

function updateTabCounts(data) {
  const counts = {};
  data.forEach(o => {
    const queue = getQueueFromStatus(o.status, o.pending_confirmations || 0);
    counts[queue] = (counts[queue] || 0) + 1;
  });
  counts.all = data.length;
  orderQueueCounts = counts;

  const tabs = ['pending_payment', 'pending_verify', 'completed', 'expired', 'cancelled', 'all'];
  tabs.forEach(tab => {
    const el = $(`#tabCount-${tab}`);
    if (el) el.textContent = counts[tab] || 0;
  });
}

function renderOrders() {
  const search = ($('#orderSearch')?.value.trim() || '');
  const params = new URLSearchParams();
  if (search) params.set('search', search);

  api.get(`/dashboard/api/orders?${params}`).then(res => {
    if (!res.success) { showToast(res.message, 'error'); return; }

    updateTabCounts(res.data);

    const orders = activeOrderTab === 'all'
      ? res.data
      : res.data.filter(o => {
        const queue = getQueueFromStatus(o.status, o.pending_confirmations || 0);
        return queue === activeOrderTab;
      });

    $('#ordersTable').innerHTML = orders.map(o => {
      const urgent = isOrderUrgent(o.created_at, o.status);
      const queue = getQueueFromStatus(o.status, o.pending_confirmations || 0);
      const isActionable = queue === 'pending_verify';
      const rowClass = urgent ? 'order-row-urgent' : (isActionable ? 'order-row-actionable' : '');

      let actions = `<button class="btn-soft" onclick="showOrder(${o.id})">Detail</button>`;

      if (queue === 'pending_verify' && o.pending_confirmations > 0) {
        actions = `
          <button class="btn-soft" onclick="showOrder(${o.id})">Detail</button>
          <button class="btn-primary text-xs py-1 px-2" onclick="showOrderAndVerify(${o.id})">Verifikasi</button>
        `;
      }

      return `<tr class="${rowClass}">
        <td class="font-black">${o.order_code}</td>
        <td>
          <div>${o.customer_name}</div>
          ${o.customer_phone ? `<div class="text-xs text-slate-500 dark:text-slate-400">${o.customer_phone}</div>` : ''}
        </td>
        <td>${o.items_summary || '—'}</td>
        <td class="font-bold">${rupiah(o.total_amount)}</td>
        <td>${badge(o.status)}</td>
        <td>${o.pending_confirmations ? `<span class="badge badge-yellow">${o.pending_confirmations} konfirmasi</span>` : '<span class="text-slate-400">—</span>'}</td>
        <td class="text-sm text-slate-500">${o.created_at?.slice(0, 10) || ''}</td>
        <td class="text-nowrap">${actions}</td>
      </tr>`;
    }).join('') || '<tr><td colspan="8" class="text-center text-slate-400 py-8">Tidak ada pesanan di queue ini.</td></tr>';
  });
}

window.showOrderAndVerify = async (id) => {
  await showOrder(id);
};

window.showOrder = async (id) => {
  const res = await api.get(`/dashboard/api/orders?id=${id}`);
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

  if (o.payment_confirmations?.length) {
    $('#orderDetail').innerHTML += `
      <div class="md:col-span-2 mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
        <p class="mb-3 font-black">Konfirmasi Pembayaran:</p>
        ${o.payment_confirmations.map((c) => `
          <div class="grid gap-2 border-b border-emerald-100 py-3 text-sm last:border-b-0 dark:border-emerald-900">
            <div class="flex justify-between gap-4"><span class="font-bold">Nama Pengirim</span><span>${escapeHtml(c.sender_name)}</span></div>
            <div class="flex justify-between gap-4"><span class="font-bold">Metode</span><span>${escapeHtml(c.payment_method)}</span></div>
            <div class="flex justify-between gap-4"><span class="font-bold">Catatan</span><span>${escapeHtml(c.note || '—')}</span></div>
            <div class="flex justify-between gap-4"><span class="font-bold">Status Verifikasi</span><span>${badge(c.verification_status || 'pending')}</span></div>
            <div class="flex justify-between gap-4"><span class="font-bold">Catatan Admin</span><span>${escapeHtml(c.admin_note || '—')}</span></div>
            <div class="flex flex-wrap gap-2">
              <a class="btn-soft w-fit" href="../${encodeURI(c.proof_path)}" target="_blank" rel="noopener">Lihat Bukti</a>
              ${(c.verification_status || 'pending') === 'pending' ? `
                <button class="btn-soft" onclick="verifyPayment(${c.id}, 'accept')" type="button">Terima</button>
                <button class="btn-soft" onclick="verifyPayment(${c.id}, 'reject')" type="button">Tolak</button>
              ` : ''}
            </div>
          </div>`).join('')}
      </div>`;
  }

  $('#orderId').value = o.id;
  $('#orderDeliveryNote').value = o.delivery_note || '';
  $('#deliveryNoteSection')?.classList.toggle('hidden', o.status !== 'completed');
  openModal('#orderModal');
};

window.verifyPayment = async (confirmationId, action) => {
  const needsNote = action !== 'accept';
  const admin_note = needsNote ? prompt('Catatan admin untuk customer/internal:') : '';
  if (needsNote && !admin_note?.trim()) return;
  const res = await api.post('/dashboard/api/orders?action=verify_payment', { confirmation_id: confirmationId, action, admin_note: admin_note?.trim() || null });
  if (!res.success) { showToast(res.message, 'error'); return; }
  showToast(res.message);
  closeModals();
  renderOrders();
  if (res.data?.order_id) showOrder(res.data.order_id);
};

function initOrders() {
  renderOrders();

  $('#orderTabs')?.addEventListener('click', (e) => {
    const tab = e.target.closest('.tab-btn');
    if (!tab) return;
    $$('.tab-btn').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    activeOrderTab = tab.dataset.tab;
    renderOrders();
  });

  let searchTimeout;
  $('#orderSearch')?.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(renderOrders, 300);
  });

}

/* ----------------------------------------------------------------
 * Testimonials
 * --------------------------------------------------------------- */
async function renderTestimonials() {
  const res = await api.get('/dashboard/api/testimonials');
  if (!res.success) { showToast(res.message, 'error'); return; }

  $('#testimonialsTable').innerHTML = res.data.map((t) =>
    `<tr>
       <td class=\"font-black\">${escapeHtml(t.name)}</td>
       <td>${t.role ? escapeHtml(t.role) : '—'}</td>
       <td class=\"text-yellow-500\">${'★'.repeat(t.rating)}${'☆'.repeat(5 - t.rating)}</td>
       <td class=\"max-w-[200px] truncate\">${escapeHtml(t.message)}</td>
       <td>${badge(t.status)}</td>
       <td>
         <div class=\"flex gap-2\">
           <button class=\"btn-soft\" onclick=\"editTestimonial(${t.id})\"><i class=\"fa-solid fa-pen mr-1\"></i>Edit</button>
           <button class=\"btn-soft\" onclick=\"askDeleteTestimonial(${t.id}, '${escapeHtml(t.name).replace(/'/g, "\\'")}')\"><i class=\"fa-solid fa-trash mr-1\"></i>Hapus</button>
         </div>
       </td>
     </tr>`
  ).join('') || '<tr><td colspan=\"6\" class=\"text-center text-slate-400\">Belum ada testimoni.</td></tr>';
}

window.editTestimonial = async (id) => {
  const res = await api.get(`/dashboard/api/testimonials?id=${id}`);
  if (!res.success) { showToast(res.message, 'error'); return; }
  const t = res.data;
  $('#testimonialModalTitle').textContent = 'Edit Testimoni';
  $('#testimonialId').value      = t.id;
  $('#testimonialName').value    = t.name;
  $('#testimonialRole').value    = t.role || '';
  $('#testimonialRating').value  = t.rating;
  $('#testimonialMessage').value = t.message;
  $('#testimonialStatus').value  = t.status;
  $('#testimonialImagePath').value = t.image_path || '';
  testimonialImageSetFromUrl(t.image_path);
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
    $('#testimonialImagePath').value = '';
    testimonialImageShowPlaceholder();
    openModal('#testimonialModal');
  });

  $('#testimonialForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $('#testimonialId').value;
    const payload = {
      name:       $('#testimonialName').value,
      role:       $('#testimonialRole').value || null,
      rating:     parseInt($('#testimonialRating').value),
      message:    $('#testimonialMessage').value,
      status:     $('#testimonialStatus').value,
      image_path: $('#testimonialImagePath').value || null,
    };
    const res = id
      ? await api.put(`/dashboard/api/testimonials?id=${id}`, payload)
      : await api.post('/dashboard/api/testimonials', payload);
    if (!res.success) { showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderTestimonials();
  });

  $('#confirmDeleteTestimonial')?.addEventListener('click', async () => {
    if (!_deleteTestimonialId) return;
    const res = await api.delete(`/dashboard/api/testimonials?id=${_deleteTestimonialId}`);
    if (!res.success) { showToast(res.message, 'error'); return; }
    showToast(res.message);
    closeModals();
    renderTestimonials();
    _deleteTestimonialId = null;
  });

  initTestimonialImageUpload();
}
/* ----------------------------------------------------------------
 * Testimonial Image Upload
 * --------------------------------------------------------------- */
let testimonialImageUploading = false;

function testimonialImageShowPlaceholder() {
  $('#testimonialImagePlaceholder')?.classList.remove('hidden');
  $('#testimonialImagePreview')?.classList.add('hidden');
  $('#testimonialImageRemoveBtn')?.classList.add('hidden');
}

function testimonialImageShowPreview(src, fileName, fileSize) {
  const img = $('#testimonialImagePreviewImg');
  const nameEl = $('#testimonialImageFileName');
  const sizeEl = $('#testimonialImageFileSize');
  if (img) img.src = src;
  if (nameEl) nameEl.textContent = fileName || '';
  if (sizeEl) sizeEl.textContent = fileSize ? (fileSize / 1024).toFixed(1) + ' KB' : '';
  $('#testimonialImagePlaceholder')?.classList.add('hidden');
  $('#testimonialImagePreview')?.classList.remove('hidden');
  $('#testimonialImageRemoveBtn')?.classList.remove('hidden');
}

function testimonialImageSetProgress(percent) {
  const bar = $('#testimonialImageProgressBar');
  const wrap = $('#testimonialImageUploadProgress');
  if (bar) bar.style.width = percent + '%';
  if (wrap) wrap.classList.toggle('hidden', percent <= 0 || percent >= 100);
}

async function testimonialImageUploadFile(file) {
  if (testimonialImageUploading) return;
  testimonialImageUploading = true;

  const reader = new FileReader();
  reader.onload = () => testimonialImageShowPreview(reader.result, file.name, file.size);
  reader.readAsDataURL(file);

  testimonialImageSetProgress(10);

  const form = new FormData();
  form.append('image', file, file.name);

  try {
    testimonialImageSetProgress(50);
    const res = await api.upload('api/upload-testimonial-image', form);
    testimonialImageSetProgress(100);

    if (res.success && res.data?.path) {
      $('#testimonialImagePath').value = res.data.path;
      showToast('Gambar berhasil diupload.');
    } else {
      showToast(res.message || 'Upload gagal.', 'error');
      testimonialImageRemoveImage();
    }
  } catch {
    showToast('Upload gagal.', 'error');
    testimonialImageRemoveImage();
  } finally {
    testimonialImageUploading = false;
    setTimeout(() => testimonialImageSetProgress(0), 500);
  }
}

function testimonialImageRemoveImage() {
  $('#testimonialImagePath').value = '';
  $('#testimonialImageFileInput').value = '';
  testimonialImageShowPlaceholder();
}

function testimonialImageSetFromUrl(url) {
  if (url && url.startsWith('uploads/testimonials/')) {
    testimonialImageShowPreview('../' + url, url.split('/').pop(), 0);
  } else {
    testimonialImageShowPlaceholder();
  }
}

function initTestimonialImageUpload() {
  const dropArea = $('#testimonialImageDropArea');
  const fileInput = $('#testimonialImageFileInput');
  const removeBtn = $('#testimonialImageRemoveBtn');

  if (!dropArea || !fileInput) return;

  ['dragenter', 'dragover'].forEach((evt) => {
    dropArea.addEventListener(evt, (e) => {
      e.preventDefault();
      dropArea.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-950');
    });
  });

  ['dragleave', 'drop'].forEach((evt) => {
    dropArea.addEventListener(evt, (e) => {
      e.preventDefault();
      dropArea.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-950');
    });
  });

  dropArea.addEventListener('drop', (e) => {
    const file = e.dataTransfer?.files?.[0];
    if (file) testimonialImageUploadFile(file);
  });

  fileInput.addEventListener('change', () => {
    const file = fileInput.files?.[0];
    if (file) testimonialImageUploadFile(file);
    else fileInput.value = '';
  });

  removeBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    testimonialImageRemoveImage();
  });
}


// async function renderTestimonials() {
//   const res = await api.get('/dashboard/api/testimonials');
//   if (!res.success) { showToast(res.message, 'error'); return; }
//
//   $('#testimonialsTable').innerHTML = res.data.map((t) =>
//     `<tr>
//        <td class="font-black">${t.name}</td>
//        <td>${t.role || '—'}</td>
//        <td>${'<i class="fa-solid fa-star text-yellow-400"></i>'.repeat(t.rating)}</td>
//        <td class="max-w-[200px] truncate">${t.message}</td>
//        <td>${badge(t.status)}</td>
//        <td>
//          <div class="flex gap-2">
//            <button class="btn-soft" onclick="editTestimonial(${t.id})"><i class="fa-solid fa-pen mr-1"></i>Edit</button>
//            <button class="btn-soft" onclick="askDeleteTestimonial(${t.id}, '${t.name.replace(/'/g, "\\'")}')"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
//          </div>
//        </td>
//      </tr>`
//   ).join('') || '<tr><td colspan="6" class="text-center text-slate-400">Belum ada testimoni.</td></tr>';
// }
//
// window.editTestimonial = async (id) => {
//   const res = await api.get(`/dashboard/api/testimonials?id=${id}`);
//   if (!res.success) { showToast(res.message, 'error'); return; }
//   const t = res.data;
//   $('#testimonialModalTitle').textContent = 'Edit Testimoni';
//   $('#testimonialId').value      = t.id;
//   $('#testimonialName').value    = t.name;
//   $('#testimonialRole').value    = t.role || '';
//   $('#testimonialRating').value  = t.rating;
//   $('#testimonialMessage').value = t.message;
//   $('#testimonialStatus').value  = t.status;
//   openModal('#testimonialModal');
// };
//
// let _deleteTestimonialId = null;
// window.askDeleteTestimonial = (id, name) => {
//   _deleteTestimonialId = id;
//   $('#deleteTestimonialModalName').textContent = name;
//   openModal('#deleteTestimonialModal');
// };
//
// function initTestimonials() {
//   renderTestimonials();
//   $('#addTestimonialBtn')?.addEventListener('click', () => {
//     $('#testimonialForm').reset();
//     $('#testimonialId').value = '';
//     $('#testimonialModalTitle').textContent = 'Tambah Testimoni';
//     openModal('#testimonialModal');
//   });
//
//   $('#testimonialForm')?.addEventListener('submit', async (e) => {
//     e.preventDefault();
//     const id = $('#testimonialId').value;
//     const payload = {
//       name:    $('#testimonialName').value,
//       role:    $('#testimonialRole').value || null,
//       rating:  parseInt($('#testimonialRating').value),
//       message: $('#testimonialMessage').value,
//       status:  $('#testimonialStatus').value,
//     };
//     const res = id
//       ? await api.put(`/dashboard/api/testimonials?id=${id}`, payload)
//       : await api.post('/dashboard/api/testimonials', payload);
//     if (!res.success) { showToast(Array.isArray(res.errors) ? res.errors.join(', ') : res.message, 'error'); return; }
//     showToast(res.message);
//     closeModals();


/* ----------------------------------------------------------------
 * Settings (settings)
 * --------------------------------------------------------------- */
async function initSettings() {
  const res = await api.get('/dashboard/api/settings');
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
    const res = await api.put('/dashboard/api/settings', payload);
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
