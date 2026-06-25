let products = [...dashboardProducts];
let categories = [...dashboardCategories];
let deleteProductId = null;

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => [...document.querySelectorAll(selector)];
const rupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);

function badgeClass(status) {
  if (['Aktif', 'Dibayar', 'Selesai', 'Tampil'].includes(status)) return 'badge-green';
  if (['Menunggu', 'Draft'].includes(status)) return 'badge-yellow';
  if (['Habis', 'Batal', 'Sembunyi'].includes(status)) return 'badge-red';
  return 'badge-gray';
}

function badge(status) {
  return `<span class="badge ${badgeClass(status)}">${status}</span>`;
}

function openModal(id) { $(id)?.classList.add('open'); }
function closeModals() { $$('.modal').forEach((modal) => modal.classList.remove('open')); }

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
  $$('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModals));
  $$('.modal').forEach((modal) => modal.addEventListener('click', (event) => { if (event.target === modal) closeModals(); }));
}

function renderOverview() {
  const active = products.filter((p) => p.status === 'Aktif').length;
  const stats = [
    ['Total Produk', products.length, 'fa-solid fa-box'],
    ['Produk Aktif', active, 'fa-solid fa-circle-check'],
    ['Pesanan Hari Ini', dashboardOrders.length, 'fa-solid fa-receipt'],
    ['Rating Toko', '4.9', 'fa-solid fa-star']
  ];
  $('#statsGrid').innerHTML = stats.map(([label, value, icon]) => `<div class="card p-5"><div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-blue-600 text-white"><i class="${icon}"></i></div><p class="text-sm font-bold text-slate-500 dark:text-slate-400">${label}</p><p class="mt-1 text-3xl font-black">${value}</p></div>`).join('');
  $('#recentOrders').innerHTML = dashboardOrders.map((order) => `<tr><td class="font-black">${order.code}</td><td>${order.customer}</td><td>${order.product}</td><td class="font-bold">${rupiah(order.total)}</td><td>${badge(order.status)}</td></tr>`).join('');
  $('#popularProducts').innerHTML = products.filter((p) => p.featured).map((product) => `<div class="flex items-center gap-3 rounded-2xl border border-slate-200 p-3 dark:border-slate-800"><img class="h-14 w-14 rounded-xl object-cover" src="${product.image}" loading="lazy" alt=""><div class="min-w-0 flex-1"><p class="truncate font-black">${product.name}</p><p class="text-sm text-slate-500 dark:text-slate-400">${rupiah(product.price)}</p></div>${badge(product.status)}</div>`).join('');
}

function fillCategoryOptions() {
  const options = categories.map((cat) => `<option value="${cat.name}">${cat.name}</option>`).join('');
  if ($('#productCategoryFilter')) $('#productCategoryFilter').innerHTML = `<option value="">Semua kategori</option>${options}`;
  if ($('#productCategory')) $('#productCategory').innerHTML = options;
}

function renderProducts() {
  fillCategoryOptions();
  const keyword = ($('#productSearch')?.value || '').toLowerCase();
  const category = $('#productCategoryFilter')?.value || '';
  const filtered = products.filter((p) => (!category || p.category === category) && (p.name.toLowerCase().includes(keyword) || p.description.toLowerCase().includes(keyword)));
  $('#productsTable').innerHTML = filtered.map((product) => `<tr><td><div class="flex items-center gap-3"><img class="h-12 w-12 rounded-xl object-cover" src="${product.image}" loading="lazy" alt=""><div><p class="font-black">${product.name}</p><p class="text-xs text-slate-500 dark:text-slate-400">${product.badge}</p></div></div></td><td>${product.category}</td><td class="font-bold">${rupiah(product.price)}</td><td>${product.stock}</td><td>${badge(product.status)}</td><td><div class="flex gap-2"><button class="btn-soft" onclick="editProduct(${product.id})">Edit</button><button class="btn-soft" onclick="askDeleteProduct(${product.id})">Hapus</button><button class="btn-soft" onclick="alert('Preview: ${product.name}')">Preview</button></div></td></tr>`).join('');
  $('#productsEmpty')?.classList.toggle('hidden', filtered.length > 0);
}

function resetProductForm() {
  $('#productForm').reset();
  $('#productId').value = '';
  $('#productImage').value = 'https://placehold.co/600x400';
  $('#productModalTitle').textContent = 'Tambah Produk';
}

window.editProduct = (id) => {
  const product = products.find((p) => p.id === id);
  if (!product) return;
  $('#productModalTitle').textContent = 'Edit Produk';
  $('#productId').value = product.id;
  $('#productName').value = product.name;
  $('#productCategory').value = product.category;
  $('#productPrice').value = product.price;
  $('#productOriginalPrice').value = product.originalPrice;
  $('#productStock').value = product.stock;
  $('#productStatus').value = product.status;
  $('#productBadge').value = product.badge;
  $('#productImage').value = product.image;
  $('#productDescription').value = product.description;
  $('#productFeatured').checked = product.featured;
  openModal('#productModal');
};

window.askDeleteProduct = (id) => { deleteProductId = id; openModal('#deleteModal'); };

function initProducts() {
  renderProducts();
  $('#productSearch')?.addEventListener('input', renderProducts);
  $('#productCategoryFilter')?.addEventListener('change', renderProducts);
  $('#addProductBtn')?.addEventListener('click', () => { resetProductForm(); openModal('#productModal'); });
  $('#productForm')?.addEventListener('submit', (event) => {
    event.preventDefault();
    const id = Number($('#productId').value) || Date.now();
    const payload = { id, name: $('#productName').value, category: $('#productCategory').value, price: Number($('#productPrice').value), originalPrice: Number($('#productOriginalPrice').value), stock: Number($('#productStock').value), status: $('#productStatus').value, badge: $('#productBadge').value, image: $('#productImage').value, description: $('#productDescription').value, featured: $('#productFeatured').checked };
    products = products.some((p) => p.id === id) ? products.map((p) => p.id === id ? payload : p) : [payload, ...products];
    closeModals(); renderProducts();
  });
  $('#confirmDelete')?.addEventListener('click', () => { products = products.filter((p) => p.id !== deleteProductId); closeModals(); renderProducts(); });
}

function renderCategories() {
  $('#categoriesTable').innerHTML = categories.map((cat) => `<tr><td class="font-black"><i class="${cat.icon} mr-2 text-slate-400"></i>${cat.name}</td><td>${cat.slug}</td><td>${products.filter((p) => p.category === cat.name).length}</td><td>${badge(cat.status)}</td><td><button class="btn-soft" onclick="editCategory(${cat.id})"><i class="fa-solid fa-pen mr-1"></i>Edit</button></td></tr>`).join('');
}

window.editCategory = (id) => {
  const cat = categories.find((c) => c.id === id);
  $('#categoryModalTitle').textContent = 'Edit Kategori';
  $('#categoryId').value = cat.id;
  $('#categoryName').value = cat.name;
  $('#categorySlug').value = cat.slug;
  $('#categoryIcon').value = cat.icon;
  $('#categoryStatus').value = cat.status;
  openModal('#categoryModal');
};

function initCategories() {
  renderCategories();
  $('#addCategoryBtn')?.addEventListener('click', () => { $('#categoryForm').reset(); $('#categoryId').value = ''; $('#categoryModalTitle').textContent = 'Tambah Kategori'; openModal('#categoryModal'); });
  $('#categoryForm')?.addEventListener('submit', (event) => {
    event.preventDefault();
    const id = Number($('#categoryId').value) || Date.now();
    const payload = { id, name: $('#categoryName').value, slug: $('#categorySlug').value, icon: $('#categoryIcon').value || 'fa-solid fa-tag', status: $('#categoryStatus').value };
    categories = categories.some((c) => c.id === id) ? categories.map((c) => c.id === id ? payload : c) : [payload, ...categories];
    closeModals(); renderCategories();
  });
}

function renderOrders() {
  $('#ordersTable').innerHTML = dashboardOrders.map((order) => `<tr><td class="font-black">${order.code}</td><td>${order.customer}</td><td>${order.product}</td><td class="font-bold">${rupiah(order.total)}</td><td>${badge(order.status)}</td><td>${order.date}</td><td><button class="btn-soft" onclick="showOrder(${order.id})">Detail</button></td></tr>`).join('');
}

window.showOrder = (id) => {
  const order = dashboardOrders.find((item) => item.id === id);
  $('#orderDetail').innerHTML = Object.entries({ 'Kode Order': order.code, Customer: order.customer, Email: order.email, 'Nomor HP': order.phone, Produk: order.product, Total: rupiah(order.total), Pembayaran: order.method, Status: order.status, Tanggal: order.date }).map(([key, value]) => `<div class="flex justify-between gap-4 border-b border-slate-200 pb-2 dark:border-slate-800"><span class="font-bold text-slate-500 dark:text-slate-400">${key}</span><span class="text-right font-black">${value}</span></div>`).join('');
  openModal('#orderModal');
};

function renderTestimonials() {
  $('#testimonialsTable').innerHTML = dashboardTestimonials.map((item) => `<tr><td class="font-black">${item.name}</td><td>${item.role}</td><td>${'<i class="fa-solid fa-star text-yellow-400"></i>'.repeat(item.rating)}</td><td>${item.message}</td><td>${badge(item.status)}</td><td><button class="btn-soft" onclick="alert('Edit: ${item.name}')"><i class="fa-solid fa-pen mr-1"></i>Edit</button></td></tr>`).join('');
}

function initSettings() {
  $('#settingsForm')?.addEventListener('submit', (event) => { event.preventDefault(); alert('Setting tersimpan sementara.'); });
}

initShell();
const page = $('[data-page]')?.dataset.page;
if (page === 'overview') renderOverview();
if (page === 'products') initProducts();
if (page === 'categories') initCategories();
if (page === 'orders') renderOrders();
if (page === 'testimonials') renderTestimonials();
if (page === 'settings') initSettings();
