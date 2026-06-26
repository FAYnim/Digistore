<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Cek status pesanan DigiStore.">
  <title>Status Pesanan — DigiStore</title>
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
  <header class="sticky top-0 z-50 border-b border-[var(--border)] bg-[color:var(--surface-glass)] backdrop-blur-xl">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 lg:px-8" aria-label="Navigasi status pesanan">
      <a href="index.php#produk" class="flex items-center gap-3 font-display text-xl font-extrabold tracking-tight">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-[var(--accent)] text-white shadow-brand">D</span>
        <span>DigiStore</span>
      </a>
      <div class="flex items-center gap-2">
        <button id="themeToggle" class="icon-btn" type="button" aria-label="Ganti tema"><i class="fa-regular fa-moon"></i></button>
        <a class="small-btn" href="index.php#produk">Katalog</a>
      </div>
    </nav>
  </header>

  <main class="section">
    <div class="mx-auto max-w-3xl text-center">
      <h1 class="font-display text-4xl font-extrabold">Status Pesanan</h1>
      <p class="mt-3 text-[var(--muted)]">Masukkan kode order untuk cek pesanan.</p>
      <form id="statusForm" class="mt-8 grid gap-3 sm:grid-cols-[1fr_auto]">
        <input id="orderCode" class="control" type="text" placeholder="ORD-20260624-A8K3">
        <button class="primary-btn" type="submit">Cek Pesanan</button>
      </form>
    </div>

    <div id="message" class="mx-auto mt-6 hidden max-w-3xl rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 text-sm font-bold text-[var(--danger)]"></div>
    <div id="statusResult" class="mt-8"></div>
  </main>

  <script src="assets/js/api.js"></script>
  <script>
    const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
    const statusLabels = { pending: "Menunggu Pembayaran", paid: "Pembayaran Diterima", completed: "Selesai", cancelled: "Dibatalkan" };
    const code = new URLSearchParams(window.location.search).get("code");

    function escapeText(value) {
      return String(value ?? "").replace(/[&<>'"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[char]));
    }

    function showMessage(message) {
      document.querySelector("#message").textContent = message;
      document.querySelector("#message").classList.remove("hidden");
    }

    function hideMessage() {
      document.querySelector("#message").classList.add("hidden");
      document.querySelector("#message").textContent = "";
    }

    function updateThemeIcon() {
      const isDark = document.documentElement.classList.contains("dark");
      document.querySelector("#themeToggle").innerHTML = isDark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
    }

    async function loadStatus(orderCode) {
      hideMessage();
      document.querySelector("#statusResult").innerHTML = "";
      const res = await apiGet(`/orders.php?code=${encodeURIComponent(orderCode)}`);
      if (!res.success) return showMessage(res.message || "Order tidak ditemukan.");

      const order = res.data;
      const items = (order.items || []).map((item) => `<li>${escapeText(item.product_name)} x${Number(item.quantity || 0)}</li>`).join("");
      document.querySelector("#statusResult").innerHTML = `
        <div class="modal-card mx-auto max-w-2xl">
          <p class="badge">${escapeText(statusLabels[order.status] || order.status)}</p>
          <h2 class="mt-4 font-display text-2xl font-extrabold">${escapeText(order.order_code)}</h2>
          <div class="mt-5 grid gap-3 text-sm text-[var(--muted)]">
            <p><b>Nama:</b> ${escapeText(order.customer_name)}</p>
            <p><b>Total:</b> ${rupiah.format(Number(order.total_amount || 0))}</p>
            <p><b>Tanggal:</b> ${escapeText(order.created_at)}</p>
            <ul class="list-disc pl-5">${items}</ul>
            ${order.delivery_note ? `<p><b>Catatan Admin:</b> ${escapeText(order.delivery_note)}</p>` : ""}
          </div>
        </div>
      `;
    }

    document.querySelector("#themeToggle").addEventListener("click", () => {
      document.documentElement.classList.toggle("dark");
      localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
      updateThemeIcon();
    });
    document.querySelector("#statusForm").addEventListener("submit", (event) => {
      event.preventDefault();
      const value = document.querySelector("#orderCode").value.trim();
      if (!value) return showMessage("Kode order wajib diisi.");
      history.replaceState(null, "", `order-status.php?code=${encodeURIComponent(value)}`);
      loadStatus(value);
    });
    updateThemeIcon();

    if (code) {
      document.querySelector("#orderCode").value = code;
      loadStatus(code);
    }
  </script>
</body>
</html>
