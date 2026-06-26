<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Pembayaran QRIS DigiStore.">
  <title>Pembayaran — DigiStore</title>
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
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 lg:px-8" aria-label="Navigasi pembayaran">
      <a href="index.php#produk" class="flex items-center gap-3 font-display text-xl font-extrabold tracking-tight">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-[var(--accent)] text-white shadow-brand">D</span>
        <span>DigiStore</span>
      </a>
      <div class="flex items-center gap-2">
        <button id="themeToggle" class="icon-btn" type="button" aria-label="Ganti tema"><i class="fa-regular fa-moon"></i></button>
        <a class="small-btn" href="order-status.php">Cek Status</a>
      </div>
    </nav>
  </header>

  <main class="section">
    <div class="mb-8">
      <h1 class="font-display text-4xl font-extrabold">Pembayaran</h1>
      <p class="mt-3 text-[var(--muted)]">Scan QRIS dan konfirmasi ke admin.</p>
    </div>

    <div id="message" class="mb-6 hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 text-sm font-bold text-[var(--danger)]"></div>

    <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-2">
      <section id="orderDetail" class="modal-card w-full">
        <h2 class="font-display text-2xl font-extrabold">Memuat pesanan...</h2>
      </section>
      <section id="paymentCard" class="modal-card w-full text-center">
        <h2 class="font-display text-2xl font-extrabold">Memuat QRIS...</h2>
      </section>
    </div>
  </main>

  <script src="assets/js/api.js"></script>
  <script>
    const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
    const code = new URLSearchParams(window.location.search).get("code");
    const statusLabels = {
      pending: "Menunggu Pembayaran",
      paid: "Pembayaran Diterima",
      completed: "Selesai",
      cancelled: "Dibatalkan",
    };

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

    async function loadOrder() {
      if (!code) return showMessage("Order tidak ditemukan.");

      const res = await apiGet(`/orders.php?code=${encodeURIComponent(code)}`);
      if (!res.success) return showMessage(res.message || "Order tidak ditemukan.");

      const order = res.data;
      const itemNames = (order.items || []).map((item) => escapeText(item.product_name)).join(", ");
      const payment = order.payment || {};
      const hasMethod = payment.qris_enabled || payment.bank_enabled;
      const message = payment.whatsapp_message || "Halo admin, saya sudah membuat pesanan " + order.order_code + ". Mohon dicek.";
      const waText = encodeURIComponent(message);
      const waLink = payment.admin_whatsapp ? `https://wa.me/${payment.admin_whatsapp}?text=${waText}` : "#";

      document.querySelector("#orderDetail").innerHTML = `
        <h2 class="font-display text-2xl font-extrabold">Detail Pesanan</h2>
        <div class="mt-5 grid gap-3 text-sm text-[var(--muted)]">
          <p><b>Kode Order:</b> ${escapeText(order.order_code)}</p>
          <p><b>Produk:</b> ${itemNames}</p>
          <p><b>Status:</b> ${escapeText(statusLabels[order.status] || order.status)}</p>
          <p><b>Total:</b> ${rupiah.format(Number(order.total_amount || 0))}</p>
        </div>
      `;

      document.querySelector("#paymentCard").innerHTML = `
        <h2 class="font-display text-2xl font-extrabold">Pembayaran</h2>
        <p class="mt-2 text-sm text-[var(--muted)]">Selesaikan pembayaran manual.</p>
        <p class="mt-5 font-display text-3xl font-extrabold">${rupiah.format(Number(order.total_amount || 0))}</p>
        ${hasMethod ? "" : '<p class="mt-5 rounded-2xl border border-[var(--border)] p-4 text-sm font-bold text-[var(--muted)]">Metode pembayaran belum tersedia. Hubungi admin.</p>'}
        ${payment.qris_enabled ? `<img class="mx-auto mt-5 h-72 w-72 rounded-3xl object-cover" src="${escapeText(payment.qris_image || 'https://placehold.co/400x400?text=QRIS+Dummy')}" alt="QRIS">` : ""}
        ${payment.bank_enabled ? `<div class="mt-5 rounded-2xl border border-[var(--border)] p-4 text-left text-sm text-[var(--muted)]"><p><b>Bank:</b> ${escapeText(payment.bank_name)}</p><p><b>No. Rekening:</b> ${escapeText(payment.bank_account)}</p><p><b>Nama:</b> ${escapeText(payment.bank_holder)}</p></div>` : ""}
        <p class="mt-5 text-left text-sm text-[var(--muted)]">${escapeText(payment.instruction || "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.")}</p>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <a class="primary-btn text-center ${payment.admin_whatsapp ? '' : 'pointer-events-none opacity-50'}" href="${waLink}" target="_blank" rel="noopener">Konfirmasi WhatsApp</a>
          <a class="small-btn text-center" href="order-status.php?code=${encodeURIComponent(order.order_code)}">Cek Status</a>
          <a class="small-btn text-center sm:col-span-2" href="index.php#produk">Kembali ke Katalog</a>
        </div>
      `;
    }

    document.querySelector("#themeToggle").addEventListener("click", () => {
      document.documentElement.classList.toggle("dark");
      localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
      updateThemeIcon();
    });
    updateThemeIcon();
    loadOrder();
  </script>
</body>
</html>
