const state = {
  category: "all",
  query: "",
  sort: "newest",
  products: [],
  featured: [],
  categories: [],
  // testimonials: [], // disembunyikan sementara
  settings: {},
};
const fallbackImage = "https://placehold.co/600x400?text=No+Image";
const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

function formatRupiah(value) {
  return rupiah.format(Number(value || 0));
}

function escapeText(value) {
  return String(value ?? "").replace(/[&<>'"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[char]));
}

function stockInfo(product) {
  if (product.status === "out_of_stock" || Number(product.stock) <= 0) return { label: "Habis", className: "out", disabled: true };
  if (Number(product.stock) <= 5) return { label: "Stok Terbatas", className: "low", disabled: false };
  return { label: "Tersedia", className: "ok", disabled: false };
}

function normalizeProduct(product) {
  return {
    id: Number(product.id),
    name: product.name || "Produk",
    slug: product.slug || "",
    category: product.category_name || "Tanpa Kategori",
    categorySlug: product.category_slug || "uncategorized",
    price: Number(product.price || 0),
    originalPrice: product.original_price ? Number(product.original_price) : 0,
    image: product.image_url || fallbackImage,
    description: product.description || "",
    rating: Number(product.rating || 0),
    sold: Number(product.sold_count || 0),
    stock: Number(product.stock || 0),
    status: product.status || "active",
    isFeatured: Boolean(product.is_featured),
    createdAt: product.created_at || "",
  };
}

function productCard(product) {
  const stock = stockInfo(product);
  const safeName = escapeText(product.name);
  const image = escapeText(product.image || fallbackImage);
  const originalPrice = product.originalPrice ? `<del>${formatRupiah(product.originalPrice)}</del>` : "";
  return `
    <article class="product-card">
      <img class="product-img" src="${image}" alt="${safeName}" loading="lazy" onerror="this.onerror=null;this.src='${fallbackImage}'">
      <div class="product-body">
        <div class="flex flex-wrap items-center gap-2">
          <span class="badge">${escapeText(product.category)}</span>
          <span class="status ${stock.className}">${stock.label}</span>
        </div>
        <h3 class="product-title">${safeName}</h3>
        <p class="product-desc">${escapeText(product.description).slice(0,100)}${product.description.length>100?'...':''}</p>
        <div class="product-meta"><span><i class="fa-solid fa-star text-[var(--warning)]"></i> ${product.rating}</span><span>${product.sold}+ terjual</span><span>Stok ${product.stock}</span></div>
        <div class="price"><strong>${formatRupiah(product.price)}</strong>${originalPrice}</div>
        <div class="card-actions">
          <button class="small-btn" type="button" data-detail="${escapeText(product.slug)}">Lihat Detail</button>
          <button class="small-btn buy-btn" type="button" ${stock.disabled ? "disabled" : ""} data-buy="${escapeText(product.slug)}">${stock.disabled ? "Habis" : "Beli Sekarang"}</button>
        </div>
      </div>
    </article>`;
}

function getFilteredProducts() {
  return [...state.products]
    .filter((product) => state.category === "all" || product.categorySlug === state.category)
    .filter((product) => `${product.name} ${product.description}`.toLowerCase().includes(state.query.toLowerCase()))
    .sort((a, b) => {
      if (state.sort === "price-low") return a.price - b.price;
      if (state.sort === "price-high") return b.price - a.price;
      if (state.sort === "rating") return b.rating - a.rating;
      if (state.sort === "sold") return b.sold - a.sold;
      return new Date(b.createdAt) - new Date(a.createdAt);
    });
}

function renderCategories() {
  const categories = [{ slug: "all", name: "Semua Produk", product_count: state.products.length }, ...state.categories];
  $("#categorySelect").innerHTML = categories.map((category) => `<option value="${escapeText(category.slug)}">${escapeText(category.name)} (${Number(category.product_count || 0)})</option>`).join("");
  $("#categorySelect").value = state.category;
}

function renderProducts() {
  const filtered = getFilteredProducts();
  $("#productGrid").innerHTML = filtered.map(productCard).join("");
  $("#emptyState").classList.toggle("hidden", filtered.length > 0);
}

function renderFeatured() {
  $("#featuredGrid").innerHTML = state.featured.map(productCard).join("") || '<div class="empty-state lg:col-span-4"><h3>Produk unggulan belum tersedia.</h3></div>';
}

// Testimoni disembunyikan sementara
// function renderTestimonials() {
//   $("#testimonialGrid").innerHTML = state.testimonials.map((item) => {
//     const stars = Array.from({ length: 5 }, (_, i) => i < Number(item.rating || 0) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>').join("");
//     return `
//     <article class="testimonial-card">
//       <div class="text-[var(--warning)]">${stars}</div>
//       <p>${escapeText(item.message)}</p>
//       <b class="mt-5 block">${escapeText(item.name)}</b>
//       <span class="text-sm font-bold text-[var(--muted)]">${escapeText(item.role || "Pelanggan")}</span>
//     </article>`;
//   }).join("") || '<div class="empty-state lg:col-span-3"><h3>Testimoni belum tersedia.</h3></div>';
// }

function renderSettings() {
  const settings = state.settings;
  const storeName = settings.store_name || "DigiStore";
  const description = settings.store_description || "Platform katalog produk digital sederhana.";
  $$('[data-store-name]').forEach((el) => { el.textContent = storeName; });
  $$('[data-store-description]').forEach((el) => { el.textContent = description; });
  $$('[data-store-headline]').forEach((el) => { el.textContent = settings.store_tagline || "Dapatkan Akun Premium Terpercaya"; });
  $('[data-store-email]').textContent = settings.store_email || "Email belum tersedia";
  $('[data-store-whatsapp]').textContent = settings.store_whatsapp ? `WhatsApp ${settings.store_whatsapp}` : "WhatsApp belum tersedia";
  $('[data-store-whatsapp]').href = settings.store_whatsapp ? `https://wa.me/${settings.store_whatsapp}` : "#";
  $('[data-store-instagram]').textContent = settings.store_instagram ? `Instagram @${settings.store_instagram}` : "Instagram belum tersedia";
  $('[data-store-instagram]').href = settings.store_instagram ? `https://instagram.com/${settings.store_instagram.replace('@', '')}` : "#";
  document.title = `${storeName} — Katalog Produk Digital Premium`;
}

function setLoading() {
  $("#productGrid").innerHTML = '<div class="empty-state xl:col-span-4"><h3>Memuat produk...</h3></div>';
  $("#featuredGrid").innerHTML = '<div class="empty-state lg:col-span-4"><h3>Memuat produk unggulan...</h3></div>';
  // $("#testimonialGrid").innerHTML = '<div class="empty-state lg:col-span-3"><h3>Memuat testimoni...</h3></div>'; // disembunyikan sementara
}

function setError() {
  $("#productGrid").innerHTML = '<div class="empty-state xl:col-span-4"><h3>Gagal memuat data.</h3><button class="small-btn mt-4" type="button" onclick="loadLandingData()">Coba Lagi</button></div>';
}

function applyTheme(defaultTheme = "light") {
  const saved = localStorage.getItem("theme");
  const theme = saved || defaultTheme || "light";
  document.documentElement.classList.toggle("dark", theme === "dark");
  updateThemeIcon();
}

function updateThemeIcon() {
  const isDark = document.documentElement.classList.contains("dark");
  $("#themeToggle").innerHTML = isDark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
}

function setActiveNav(hash) {
  $$('.nav-link[href^="#"]').forEach((link) => link.classList.toggle("active", link.getAttribute("href") === hash));
}

function initNavSpy() {
  const links = [...$$('.nav-link[href^="#"]')];
  const sections = links.map((link) => $(link.getAttribute("href"))).filter(Boolean);

  links.forEach((link) => {
    link.addEventListener("click", () => {
      setActiveNav(link.getAttribute("href"));
      $("#mobileMenu")?.classList.add("hidden");
    });
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) setActiveNav(`#${entry.target.id}`);
    });
  }, { rootMargin: "-35% 0px -55% 0px", threshold: 0 });

  sections.forEach((section) => observer.observe(section));
}

async function loadLandingData() {
  setLoading();
  const [settings, categories, products, featured] = await Promise.all([
    apiGet("/settings"),
    apiGet("/categories"),
    apiGet("/products"),
    apiGet("/products?featured=true&limit=4"),
    // apiGet("/testimonials?limit=3"), // disembunyikan sementara
  ]);

  if (![settings, categories, products, featured].every((res) => res.success)) {
    setError();
    return;
  }

  state.settings = settings.data || {};
  state.categories = categories.data || [];
  state.products = (products.data || []).map(normalizeProduct);
  state.featured = (featured.data || []).map(normalizeProduct);
  // state.testimonials = testimonials.data || []; // disembunyikan sementara

  renderSettings();
  applyTheme(state.settings.default_theme);
  renderCategories();
  renderFeatured();
  renderProducts();
  // renderTestimonials(); // disembunyikan sementara
}

$("#themeToggle").addEventListener("click", () => {
  document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
  updateThemeIcon();
});
$("#menuToggle").addEventListener("click", () => $("#mobileMenu").classList.toggle("hidden"));
$("#searchInput").addEventListener("input", (event) => { state.query = event.target.value; renderProducts(); });
$("#categorySelect").addEventListener("change", (event) => { state.category = event.target.value; renderProducts(); });
$("#sortSelect").addEventListener("change", (event) => { state.sort = event.target.value; renderProducts(); });
document.addEventListener("click", (event) => {
  const detail = event.target.closest("[data-detail]");
  const buy = event.target.closest("[data-buy]");
  if (detail && detail.dataset.detail) window.location.href = `product?slug=${encodeURIComponent(detail.dataset.detail)}`;
  if (buy && buy.dataset.buy) window.location.href = `checkout?product=${encodeURIComponent(buy.dataset.buy)}`;
});

applyTheme();
initNavSpy();
loadLandingData();
