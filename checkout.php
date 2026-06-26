<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Checkout produk digital DigiStore.">
  <title>Checkout — DigiStore</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark" || (!savedTheme && matchMedia("(prefers-color-scheme: dark)").matches)) document.documentElement.classList.add("dark");
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[var(--bg)] text-[var(--text)] antialiased">
  <header class="sticky top-0 z-50 px-4 py-4 lg:px-8">
    <nav class="mx-auto flex max-w-7xl items-center gap-4" aria-label="Navigasi checkout">
      <a href="index.php#produk" class="brand-pill">
        <span class="brand-mark"><i class="fa-solid fa-cubes-stacked"></i></span>
        <span>DigiStore</span>
      </a>
      <div class="nav-shell hidden md:flex">
        <a class="nav-link" href="index.php#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link active" href="checkout.php"><i class="fa-regular fa-credit-card"></i><span>Checkout</span></a>
        <a class="nav-link" href="order-status.php"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <button id="themeToggle" class="icon-btn" type="button" aria-label="Ganti tema"><i class="fa-regular fa-moon"></i></button>
      </div>
    </nav>
  </header>

  <main class="section">
    <div class="mb-8">
      <a class="text-sm font-bold text-[var(--muted)]" href="index.php#produk">Katalog</a>
      <span class="text-sm text-[var(--muted)]"> / Checkout</span>
      <h1 class="mt-3 font-display text-4xl font-extrabold">Checkout</h1>
      <p class="mt-3 text-[var(--muted)]">Lengkapi data pembelian.</p>
    </div>

    <div id="message" class="mb-6 hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 text-sm font-bold text-[var(--danger)]"></div>

    <div class="grid gap-4 lg:grid-cols-2">
      <form id="checkoutForm" class="modal-card grid w-full max-w-none gap-5">
        <h2 class="font-display text-2xl font-extrabold">Form Pembeli</h2>
        <label class="grid gap-2 text-sm font-bold">Nama
          <input id="customerName" class="control" type="text" placeholder="John Doe" autocomplete="name">
        </label>
        <label class="grid gap-2 text-sm font-bold">Email
          <input id="customerEmail" class="control" type="email" placeholder="john@example.com" autocomplete="email">
        </label>
        <label class="grid gap-2 text-sm font-bold">WhatsApp
          <input id="customerPhone" class="control" type="tel" placeholder="6281234567890" autocomplete="tel">
        </label>
        <label class="grid gap-2 text-sm font-bold">Catatan
          <textarea id="note" class="control min-h-28" placeholder="Opsional"></textarea>
        </label>
        <button id="submitButton" class="primary-btn" type="submit">Buat Pesanan</button>
      </form>

      <aside id="productSummary" class="modal-card w-full max-w-none">
        <h2 class="font-display text-2xl font-extrabold">Memuat produk...</h2>
      </aside>
    </div>
  </main>

  <script src="assets/js/api.js"></script>
  <script>
    const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
    const params = new URLSearchParams(window.location.search);
    const slug = params.get("product");
    let selectedProduct = null;

    function escapeText(value) {
      return String(value ?? "").replace(/[&<>'"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[char]));
    }

    function showMessage(message) {
      document.querySelector("#message").textContent = message;
      document.querySelector("#message").classList.remove("hidden");
    }

    function updateThemeIcon() {
      const isDark = document.documentElement.classList.contains("dark");
      document.querySelector("#themeToggle").innerHTML = isDark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
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

      const image = selectedProduct.image_url || "https://placehold.co/600x400?text=No+Image";
      document.querySelector("#productSummary").innerHTML = `
        <img class="product-img" src="${escapeText(image)}" alt="${escapeText(selectedProduct.name)}">
        <h2 class="mt-4 font-display text-2xl font-extrabold">${escapeText(selectedProduct.name)}</h2>
        <p class="mt-2 text-[var(--muted)]">${escapeText(selectedProduct.description || "")}</p>
        <div class="price mt-4"><strong>${rupiah.format(Number(selectedProduct.price || 0))}</strong></div>
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

    document.querySelector("#themeToggle").addEventListener("click", () => {
      document.documentElement.classList.toggle("dark");
      localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
      updateThemeIcon();
    });
    document.querySelector("#checkoutForm").addEventListener("submit", submitOrder);
    updateThemeIcon();
    loadProduct();
  </script>
</body>
</html>
