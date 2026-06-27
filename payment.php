<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Pembayaran DigiStore.">
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
  <header class="sticky top-0 z-50 px-4 py-4 lg:px-8">
    <nav class="mx-auto flex max-w-7xl items-center gap-4" aria-label="Navigasi pembayaran">
      <a href="index.php#produk" class="brand-pill">
        <span class="brand-mark"><i class="fa-solid fa-cubes-stacked"></i></span>
        <span>DigiStore</span>
      </a>
      <div class="nav-shell hidden md:flex">
        <a class="nav-link" href="index.php#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link active" href="payment.php"><i class="fa-regular fa-credit-card"></i><span>Pembayaran</span></a>
        <a class="nav-link" href="order-status.php"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
      </div>
      <div class="ml-auto flex items-center gap-2">
        <button id="themeToggle" class="icon-btn" type="button" aria-label="Ganti tema"><i class="fa-regular fa-moon"></i></button>
      </div>
    </nav>
  </header>

  <main class="section">
    <div class="mb-8">
      <h1 class="font-display text-4xl font-extrabold">Pembayaran</h1>
      <p id="paymentSubtitle" class="mt-3 text-[var(--muted)]">Selesaikan pembayaran dan konfirmasi ke admin.</p>
    </div>

    <div id="message" class="mb-6 hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 text-sm font-bold text-[var(--danger)]"></div>

    <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-2">
      <section id="orderDetail" class="modal-card w-full">
        <h2 class="font-display text-2xl font-extrabold">Memuat pesanan...</h2>
      </section>
      <section id="paymentCard" class="modal-card w-full text-center">
        <h2 class="font-display text-2xl font-extrabold">Memuat pembayaran...</h2>
      </section>
    </div>
  </main>

  <script src="assets/js/api.js"></script>
  <script src="assets/js/whatsapp.js"></script>
  <script>
    const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
    const code = new URLSearchParams(window.location.search).get("code");
    const statusLabels = {
      pending: "Menunggu Pembayaran",
      pending_payment: "Menunggu Pembayaran",
      paid: "Pembayaran Diterima",
      processing: "Diproses",
      delivered: "Dikirim",
      completed: "Selesai",
      expired: "Expired",
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

    function paymentActionText(payment) {
      if (!payment.available) return "Hubungi admin untuk instruksi pembayaran.";
      if (payment.qris_enabled && payment.bank_enabled) return "Scan QRIS atau transfer bank, lalu konfirmasi ke admin.";
      if (payment.qris_enabled) return "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.";
      if (payment.bank_enabled) return "Transfer bank sesuai total, lalu konfirmasi ke admin.";
      return "Selesaikan pembayaran dan konfirmasi ke admin.";
    }

    async function loadOrder() {
      if (!code) return showMessage("Order tidak ditemukan.");

      const res = await apiGet(`/orders.php?code=${encodeURIComponent(code)}`);
      if (!res.success) return showMessage(res.message || "Order tidak ditemukan.");

      const order = res.data;
      const itemNames = (order.items || []).map((item) => escapeText(item.product_name)).join(", ");
      const payment = order.payment || {};
      const hasMethod = payment.available && (payment.qris_enabled || payment.bank_enabled);
      const orderForMessage = { ...order, status_label: statusLabels[order.status] || order.status };
      const message = buildWhatsAppMessage(payment.whatsapp_message, orderForMessage);
      const waLink = buildWhatsAppLink(payment.admin_whatsapp, message);
      const hasWhatsapp = waLink !== "";
      const actionText = paymentActionText(payment);
      const instructionText = payment.instruction === "Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin." ? actionText : (payment.instruction || actionText);
      document.querySelector("#paymentSubtitle").textContent = actionText;

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
        <p class="mt-2 text-sm text-[var(--muted)]">${escapeText(actionText)}</p>
        <p class="mt-5 font-display text-3xl font-extrabold">${rupiah.format(Number(order.total_amount || 0))}</p>
        ${hasMethod ? "" : '<p class="mt-5 rounded-2xl border border-[var(--border)] p-4 text-sm font-bold text-[var(--muted)]">Pembayaran belum dikonfigurasi. Hubungi admin.</p>'}
        ${hasMethod && payment.qris_enabled ? `<img class="mx-auto mt-5 h-72 w-72 rounded-3xl object-cover" src="${escapeText(payment.qris_image)}" alt="QRIS">` : ""}
        ${hasMethod && payment.bank_enabled ? `<div class="mt-5 rounded-2xl border border-[var(--border)] p-4 text-left text-sm text-[var(--muted)]"><p><b>Bank:</b> ${escapeText(payment.bank_name)}</p><p><b>No. Rekening:</b> ${escapeText(payment.bank_account)}</p><p><b>Nama:</b> ${escapeText(payment.bank_holder)}</p></div>` : ""}
        ${hasMethod ? `<p class="mt-5 text-left text-sm text-[var(--muted)]">${escapeText(instructionText)}</p>` : ""}
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          ${hasWhatsapp ? `<a class="primary-btn text-center" href="${waLink}" target="_blank" rel="noopener">Konfirmasi WhatsApp</a>` : '<p class="text-center text-sm font-bold text-[var(--muted)]">WhatsApp admin belum tersedia.</p>'}
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
