const state = { category: "all", query: "", sort: "newest" };
const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const $ = (selector) => document.querySelector(selector);
const categorySlug = (value) => value.toLowerCase().replaceAll(" ", "-");

function formatRupiah(value) {
  return rupiah.format(value);
}

function stockInfo(stock) {
  if (stock <= 0) return { label: "Habis", className: "out" };
  if (stock <= 5) return { label: "Stok Terbatas", className: "low" };
  return { label: "Tersedia", className: "ok" };
}

function productCard(product) {
  const stock = stockInfo(product.stock);
  return `
    <article class="product-card">
      <img class="product-img" src="${product.image}" alt="${product.name}" loading="lazy">
      <div class="product-body">
        <div class="flex flex-wrap items-center gap-2">
          <span class="badge">${product.category}</span>
          <span class="status ${stock.className}">${stock.label}</span>
        </div>
        <h3 class="product-title">${product.name}</h3>
        <p class="product-desc">${product.description}</p>
        <div class="product-meta"><span><i class="fa-solid fa-star text-[var(--warning)]"></i> ${product.rating}</span><span>${product.sold}+ terjual</span><span>Stok ${product.stock}</span></div>
        <div class="price"><strong>${formatRupiah(product.price)}</strong><del>${formatRupiah(product.originalPrice)}</del></div>
        <div class="card-actions">
          <button class="small-btn" type="button" onclick="showDetail('${product.name.replaceAll("'", "\\'")}')">Lihat Detail</button>
          <button class="small-btn buy-btn" type="button" ${product.stock === 0 ? "disabled" : ""} onclick="openBuyModal('${product.name.replaceAll("'", "\\'")}')">Beli Sekarang</button>
        </div>
      </div>
    </article>`;
}

function getFilteredProducts() {
  return products
    .filter((product) => state.category === "all" || categorySlug(product.category) === state.category)
    .filter((product) => `${product.name} ${product.description}`.toLowerCase().includes(state.query.toLowerCase()))
    .sort((a, b) => {
      if (state.sort === "price-low") return a.price - b.price;
      if (state.sort === "price-high") return b.price - a.price;
      if (state.sort === "rating") return b.rating - a.rating;
      return new Date(b.createdAt) - new Date(a.createdAt);
    });
}

function renderCategories() {
  $("#categorySelect").innerHTML = categories.map((category) => {
    const count = category.id === "all" ? products.length : products.filter((product) => categorySlug(product.category) === category.id).length;
    return `<option value="${category.id}">${category.name} (${count})</option>`;
  }).join("");
  $("#categorySelect").value = state.category;
}

function renderProducts() {
  const filtered = getFilteredProducts();
  $("#productGrid").innerHTML = filtered.map(productCard).join("");
  $("#emptyState").classList.toggle("hidden", filtered.length > 0);
}

function renderFeatured() {
  $("#featuredGrid").innerHTML = products.filter((product) => product.isFeatured).map(productCard).join("");
}

function renderTestimonials() {
  $("#testimonialGrid").innerHTML = testimonials.map((item) => {
    const stars = Array.from({ length: 5 }, (_, i) =>
      i < item.rating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'
    ).join("");
    return `
    <article class="testimonial-card">
      <div class="text-[var(--warning)]">${stars}</div>
      <p>${item.message}</p>
      <b class="mt-5 block">${item.name}</b>
      <span class="text-sm font-bold text-[var(--muted)]">${item.role}</span>
    </article>`;
  }).join("");
}

function initTheme() {
  const saved = localStorage.getItem("theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  document.documentElement.classList.toggle("dark", saved ? saved === "dark" : prefersDark);
  updateThemeIcon();
}

function updateThemeIcon() {
  const isDark = document.documentElement.classList.contains("dark");
  $("#themeToggle").innerHTML = isDark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
}

function openBuyModal(name) {
  $("#modalTitle").textContent = name;
  $("#buyModal").classList.remove("hidden");
  $("#buyModal").classList.add("flex");
}

function closeBuyModal() {
  $("#buyModal").classList.add("hidden");
  $("#buyModal").classList.remove("flex");
}

function showDetail(name) {
  openBuyModal(name);
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
$("#closeModal").addEventListener("click", closeBuyModal);
$("#buyModal").addEventListener("click", (event) => { if (event.target.id === "buyModal") closeBuyModal(); });

initTheme();
renderCategories();
renderFeatured();
renderProducts();
renderTestimonials();
