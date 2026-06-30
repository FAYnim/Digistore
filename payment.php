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
  <link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
  <link rel="apple-touch-icon" href="assets/favicon/apple-touch-icon.png">
  <link rel="manifest" href="assets/favicon/site.webmanifest">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[var(--bg)] text-[var(--text)] antialiased">
  <header class="sticky top-0 z-50 px-4 py-4 lg:px-8">
    <nav class="mx-auto flex max-w-7xl items-center gap-4" aria-label="Navigasi pembayaran">
      <a href="index#produk" class="brand-pill">
        <span data-store-name>DigiStore</span>
      </a>
      <div class="nav-shell hidden md:flex">
        <a class="nav-link" href="index#produk"><i class="fa-solid fa-box-open"></i><span>Katalog</span></a>
        <a class="nav-link active" href="payment"><i class="fa-regular fa-credit-card"></i><span>Pembayaran</span></a>
        <a class="nav-link" href="order-status"><i class="fa-regular fa-clipboard"></i><span>Status</span></a>
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
      pending_payment: "Menunggu Pembayaran",
      pending_verify: "Menunggu Verifikasi",
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

    function showToast(message, type = "success") {
      const colors = { success: "#16a34a", error: "#dc2626", info: "#2563eb" };
      const toast = document.createElement("div");
      toast.textContent = message;
      toast.style.cssText = `position:fixed; bottom:24px; right:24px; z-index:9999; padding:12px 20px; border-radius:12px; font-weight:700; font-size:14px; color:#fff; background:${colors[type] ?? colors.info}; box-shadow:0 4px 20px rgba(0,0,0,.25); animation: fadeInUp .25s ease;`;
      if (!document.querySelector("#toastAnimStyle")) {
        document.head.insertAdjacentHTML("beforeend", `<style id="toastAnimStyle">@keyframes fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}</style>`);
      }
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
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

    function canSubmitPayment(order) {
      if (order.status !== "pending_payment") return false;
      if (!order.payment_deadline) return true;
      return new Date(order.payment_deadline) > new Date();
    }

    function isPendingWithDeadline(order) {
      return order.status === "pending_payment" && order.payment_deadline;
    }

    function formatCountdown(seconds) {
      if (seconds <= 0) return null;
      const d = Math.floor(seconds / 86400);
      const h = Math.floor((seconds % 86400) / 3600);
      const m = Math.floor((seconds % 3600) / 60);
      const s = seconds % 60;
      const pad = (n) => String(n).padStart(2, "0");
      if (d > 0) return `${d}h ${pad(h)}:${pad(m)}:${pad(s)}`;
      if (h > 0) return `${h}:${pad(m)}:${pad(s)}`;
      return `${pad(m)}:${pad(s)}`;
    }

    function startCountdown(deadline, onExpired) {
      const el = document.querySelector("#countdownTimer");
      if (!el) return;
      function tick() {
        const now = Date.now();
        const target = new Date(deadline).getTime();
        const diff = Math.floor((target - now) / 1000);
        const formatted = formatCountdown(diff);
        if (formatted) {
          el.textContent = formatted;
          el.className = diff <= 300 ? "rounded-xl bg-red-600 px-4 py-2 font-mono text-lg font-extrabold text-white" : "rounded-xl bg-[var(--surface)] px-4 py-2 font-mono text-lg font-extrabold text-[var(--text)] ring-2 ring-[var(--border)]";
          if (diff <= 60) el.className = "rounded-xl bg-red-600 px-4 py-2 font-mono text-lg font-extrabold text-white animate-pulse";
          setTimeout(tick, 1000);
        } else {
          el.textContent = "00:00";
          el.className = "rounded-xl bg-red-600 px-4 py-2 font-mono text-lg font-extrabold text-white animate-pulse";
          if (onExpired) onExpired();
        }
      }
      tick();
    }

    function refreshOrderAndCheckExpired() {
      apiGet(`/orders?code=${encodeURIComponent(code)}`).then((res) => {
        if (res.success) {
          const order = res.data;
          if (["expired", "cancelled"].includes(order.status)) {
            document.querySelector("#paymentSubtitle").textContent = "Waktu pembayaran sudah habis.";
            document.querySelector("#paymentCard").innerHTML = `
              <h2 class="font-display text-2xl font-extrabold">Pembayaran</h2>
              <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800 dark:bg-red-950/30 dark:text-red-200">Waktu pembayaran sudah habis. Order ini expired. Silakan buat pesanan baru.</div>
              <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <a class="small-btn text-center sm:col-span-2" href="index#produk">Kembali ke Katalog</a>
              </div>
            `;
          }
        }
      }).catch(() => {});
    }

    function paymentStatusNotice(order) {
      const status = statusLabels[order.status] || order.status;
      if (order.status === "pending_verify") return "Bukti pembayaran sudah dikirim dan sedang menunggu verifikasi admin.";
      if (order.status === "completed") return `Order ini sudah ${status.toLowerCase()}. Cek halaman status untuk detail pesanan.`;
      if (["expired", "cancelled"].includes(order.status)) return `Order ini sudah ${status.toLowerCase()}. Jangan lakukan pembayaran untuk order ini.`;
      return "";
    }

    function bindPaymentConfirmationForm() {
      const form = document.querySelector("#paymentConfirmationForm");
      if (!form) return;

      form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = "Mengirim...";

        try {
          const res = await fetch("api/payment-confirmations", { method: "POST", body: new FormData(form) });
          const json = await res.json();
          if (!json.success) throw new Error(json.message || "Gagal mengirim konfirmasi.");
          showToast(json.message || "Konfirmasi berhasil dikirim.", "success");
          form.reset();
          button.textContent = "Mengalihkan...";
          setTimeout(() => {
            window.location.href = `order-status?code=${encodeURIComponent(form.dataset.orderCode)}`;
          }, 2000);
        } catch (error) {
          showToast(error.message || "Gagal mengirim konfirmasi.", "error");
          button.disabled = false;
          button.textContent = "Kirim Konfirmasi";
        }
      });
    }

    function renderOrderDetail(order, itemNames) {
      const hasDeadline = isPendingWithDeadline(order);
      const deadlineLabel = hasDeadline
        ? `<div class="mt-4 rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-[var(--muted)]">Batas Pembayaran</p>
            <p id="countdownTimer" class="mt-1 font-mono text-lg font-extrabold">--:--</p>
            <p class="mt-1 text-xs text-[var(--muted)]">${escapeText(order.payment_deadline)}</p>
           </div>`
        : "";
      document.querySelector("#orderDetail").innerHTML = `
        <h2 class="font-display text-2xl font-extrabold">Detail Pesanan</h2>
        ${deadlineLabel}
        <div class="mt-5 grid gap-3 text-sm text-[var(--muted)]">
          <p><b>Kode Order:</b> ${escapeText(order.order_code)}</p>
          <p><b>Produk:</b> ${itemNames}</p>
          <p><b>Status:</b> ${escapeText(statusLabels[order.status] || order.status)}</p>
          <p><b>Total:</b> ${rupiah.format(Number(order.total_amount || 0))}</p>
        </div>
      `;
      if (hasDeadline) {
        startCountdown(order.payment_deadline, () => {
          refreshOrderAndCheckExpired();
        });
      }
    }

    async function loadOrder() {
      if (!code) return showMessage("Order tidak ditemukan.");

      const res = await apiGet(`/orders?code=${encodeURIComponent(code)}`);
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

      renderOrderDetail(order, itemNames);

      const allowPayment = canSubmitPayment(order);
      const notice = paymentStatusNotice(order);

      document.querySelector("#paymentCard").innerHTML = `
        <h2 class="font-display text-2xl font-extrabold">Pembayaran</h2>
        <p class="mt-2 text-sm text-[var(--muted)]">${escapeText(actionText)}</p>
        <p class="mt-5 font-display text-3xl font-extrabold">${rupiah.format(Number(order.total_amount || 0))}</p>
        ${!allowPayment ? `<div class="mt-5 rounded-2xl border border-[var(--warning)] bg-yellow-50 p-4 text-left text-sm font-bold text-yellow-800 dark:bg-yellow-950/30 dark:text-yellow-200">${escapeText(notice)}</div>` : ""}
        ${allowPayment && hasMethod ? "" : allowPayment ? '<p class="mt-5 rounded-2xl border border-[var(--border)] p-4 text-sm font-bold text-[var(--muted)]">Pembayaran belum dikonfigurasi. Hubungi admin.</p>' : ""}
        ${allowPayment && hasMethod && payment.qris_enabled ? `<img class="mx-auto mt-5 h-72 w-72 rounded-3xl object-cover" src="dashboard/${escapeText(payment.qris_image)}" alt="QRIS">` : ""}
        ${allowPayment && hasMethod && payment.bank_enabled ? `<div class="mt-5 rounded-2xl border border-[var(--border)] p-4 text-left text-sm text-[var(--muted)]"><p><b>Bank:</b> ${escapeText(payment.bank_name)}</p><p><b>No. Rekening:</b> ${escapeText(payment.bank_account)}</p><p><b>Nama:</b> ${escapeText(payment.bank_holder)}</p></div>` : ""}
        ${allowPayment && hasMethod ? `<p class="mt-5 text-left text-sm text-[var(--muted)]">${escapeText(instructionText)}</p>` : ""}
        ${allowPayment ? `<form class="payment-confirm-form mt-6 text-left" id="paymentConfirmationForm" data-order-code="${escapeText(order.order_code)}">
          <input type="hidden" name="order_code" value="${escapeText(order.order_code)}">
          <label class="field-label">Nama Pengirim
            <input class="control mt-2" name="sender_name" required maxlength="100" placeholder="Nama pemilik rekening/e-wallet">
          </label>
          <label class="field-label">Metode Pembayaran
            <select class="control mt-2" name="payment_method" required>
              <option value="">Pilih metode</option>
              ${payment.qris_enabled ? '<option value="QRIS">QRIS</option>' : ''}
              ${payment.bank_enabled ? '<option value="Transfer Bank">Transfer Bank</option>' : ''}
              <option value="E-Wallet">E-Wallet</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </label>

          <label class="field-label">Bukti Bayar
            <input class="control file-control mt-2" name="proof" type="file" accept="image/jpeg,image/png,application/pdf" required>
          </label>
          <label class="field-label">Catatan
            <textarea class="control mt-2 min-h-28 resize-y py-3" name="note" placeholder="Opsional"></textarea>
          </label>
          <button class="primary-btn w-full text-center" type="submit">Kirim Konfirmasi</button>
        </form>` : ""}
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          ${hasWhatsapp && allowPayment ? `<a class="small-btn text-center" href="${waLink}" target="_blank" rel="noopener">Konfirmasi WhatsApp</a>` : '<p class="text-center text-sm font-bold text-[var(--muted)]">WhatsApp admin belum tersedia.</p>'}
          <a class="small-btn text-center" href="order-status?code=${encodeURIComponent(order.order_code)}">Cek Status</a>
          <a class="small-btn text-center sm:col-span-2" href="index#produk">Kembali ke Katalog</a>
        </div>
      `;
      bindPaymentConfirmationForm();
    }

    document.querySelector("#themeToggle").addEventListener("click", () => {
      document.documentElement.classList.toggle("dark");
      localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
      updateThemeIcon();
    });
    updateThemeIcon();
    loadStoreName();
    loadOrder();
  </script>
</body>
</html>
