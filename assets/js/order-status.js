const rupiah = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 });
const statusLabels = { pending_payment: "Menunggu Pembayaran", pending_verify: "Menunggu Verifikasi", completed: "Selesai", expired: "Expired", cancelled: "Dibatalkan" };
const statusStyles = { pending_payment: "bg-yellow-100 text-yellow-800", pending_verify: "bg-blue-100 text-blue-800", completed: "bg-green-100 text-green-800", expired: "bg-red-100 text-red-800", cancelled: "bg-slate-200 text-slate-700" };
const code = new URLSearchParams(window.location.search).get("code");

function escapeText(value) {
  return String(value ?? "").replace(/[&<>'"]/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[char]));
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

function startCountdown(deadline, orderCode) {
  const el = document.querySelector("#statusCountdownTimer");
  if (!el) return;
  function tick() {
    const now = Date.now();
    const target = new Date(deadline).getTime();
    const diff = Math.floor((target - now) / 1000);
    const formatted = formatCountdown(diff);
    if (formatted) {
      el.textContent = formatted;
      el.className = diff <= 300 ? "font-mono text-2xl font-extrabold text-red-600" : "font-mono text-2xl font-extrabold text-[var(--text)]";
      if (diff <= 60) el.className = "font-mono text-2xl font-extrabold text-red-600 animate-pulse";
      setTimeout(tick, 1000);
    } else {
      el.textContent = "00:00";
      el.className = "font-mono text-2xl font-extrabold text-red-600 animate-pulse";
      loadStatus(orderCode);
    }
  }
  tick();
}

function showMessage(message) {
  document.querySelector("#message").textContent = message;
  document.querySelector("#message").classList.remove("hidden");
}

function hideMessage() {
  document.querySelector("#message").classList.add("hidden");
  document.querySelector("#message").textContent = "";
}

function renderEmpty() {
  document.querySelector("#statusResult").innerHTML = `
    <div class="modal-card mx-auto max-w-3xl text-center">
      <h2 class="font-display text-2xl font-extrabold">Cek Status Pesanan</h2>
      <p class="mt-3 text-sm text-[var(--muted)]">Masukkan kode order untuk cek pesanan.</p>
    </div>
  `;
}

function renderLoading() {
  document.querySelector("#statusResult").innerHTML = `
    <div class="modal-card mx-auto max-w-3xl text-center">
      <h2 class="font-display text-2xl font-extrabold">Memuat pesanan...</h2>
    </div>
  `;
}

function renderError(message) {
  showMessage(message || "Order tidak ditemukan.");
  document.querySelector("#statusResult").innerHTML = `
    <div class="modal-card mx-auto max-w-3xl text-center">
      <h2 class="font-display text-2xl font-extrabold">Order tidak ditemukan.</h2>
      <p class="mt-3 text-sm text-[var(--muted)]">Periksa kembali kode order kamu.</p>
    </div>
  `;
}

function updateThemeIcon() {
  const isDark = document.documentElement.classList.contains("dark");
  document.querySelector("#themeToggle").innerHTML = isDark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
}

function getStatusInstruction(order) {
  if (order.status === "pending_payment") return "Selesaikan pembayaran lalu upload bukti bayar.";
  if (order.status === "pending_verify") return "Bukti pembayaran sedang menunggu verifikasi admin.";
  if (order.status === "completed") return "Pesanan selesai. Credentials tersedia di bawah.";
  if (order.status === "expired") return "Deadline pembayaran sudah lewat. Buat pesanan baru jika masih ingin membeli.";
  if (order.status === "cancelled") return "Pesanan ini dibatalkan. Hubungi admin jika butuh bantuan.";
  return "Status pesanan berhasil dimuat.";
}

function buildWhatsappLink(order) {
  const payment = order.payment || {};
  const orderForMessage = { ...order, status_label: statusLabels[order.status] || order.status };
  const message = buildWhatsAppMessage(payment.whatsapp_message, orderForMessage);
  return buildWhatsAppLink(payment.admin_whatsapp, message) || "#";
}

function renderActions(order) {
  const waLink = buildWhatsappLink(order);
  const hasWhatsapp = waLink !== "#";
  const isPendingPayment = order.status === "pending_payment";
  const whatsappLabel = isPendingPayment ? "Konfirmasi WhatsApp" : "Hubungi Admin";
  const whatsappButton = hasWhatsapp ? `<a class="primary-btn text-center" href="${waLink}" target="_blank" rel="noopener">${whatsappLabel}</a>` : '<p class="text-center text-sm font-bold text-[var(--muted)]">WhatsApp admin belum tersedia.</p>';
  const catalogButton = '<a class="small-btn text-center" href="index.php#produk">Kembali ke Katalog</a>';

  if (isPendingPayment) {
    return `
      <a class="small-btn text-center" href="payment.php?code=${encodeURIComponent(order.order_code)}">Lanjut ke Pembayaran</a>
      ${whatsappButton}
      ${catalogButton}
    `;
  }

  return `${whatsappButton}${catalogButton}`;
}

function renderItems(items) {
  if (!items || !items.length) return '<p class="text-sm text-[var(--muted)]">Item pesanan tidak tersedia.</p>';
  return items.map((item) => `
    <div class="rounded-2xl border border-[var(--border)] p-4">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="font-bold">${escapeText(item.product_name)}</p>
          <p class="mt-1 text-sm text-[var(--muted)]">${Number(item.quantity || 0)} x ${rupiah.format(Number(item.price || 0))}</p>
        </div>
        <p class="font-bold">${rupiah.format(Number(item.subtotal || 0))}</p>
      </div>
    </div>
  `).join("");
}

function renderDeliveryNote(order) {
  if (order.status !== "completed") return "";
  const note = order.delivery_note ? escapeText(order.delivery_note) : "Pesanan selesai. Hubungi admin jika credentials belum tampil.";
  return `
    <section class="modal-card lg:col-span-2">
      <h3 class="font-display text-xl font-extrabold">Delivery Note</h3>
      <p class="mt-3 whitespace-pre-line text-sm text-[var(--muted)]">${note}</p>
    </section>
  `;
}

function renderOrder(order) {
  const statusClass = statusStyles[order.status] || "bg-slate-100 text-slate-700";
  const hasDeadline = isPendingWithDeadline(order);
  const deadlineSection = hasDeadline
    ? `<div class="mt-4 rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 text-center">
        <p class="text-xs font-bold uppercase tracking-wider text-[var(--muted)]">Batas Pembayaran</p>
        <p id="statusCountdownTimer" class="mt-1 font-mono text-2xl font-extrabold">--:--</p>
        <p class="mt-1 text-xs text-[var(--muted)]">${escapeText(order.payment_deadline)}</p>
       </div>`
    : "";
  document.querySelector("#statusResult").innerHTML = `
    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1.4fr_.9fr]">
      <section class="modal-card">
        <h2 class="font-display text-2xl font-extrabold">Detail Pesanan</h2>
        ${deadlineSection}
        <div class="mt-5 grid gap-3 text-sm text-[var(--muted)]">
          <p><b>Kode Order:</b> ${escapeText(order.order_code)}</p>
          <p><b>Nama:</b> ${escapeText(order.customer_name)}</p>
          ${order.customer_email ? `<p><b>Email:</b> ${escapeText(order.customer_email)}</p>` : ""}
          ${order.customer_phone ? `<p><b>WhatsApp:</b> ${escapeText(order.customer_phone)}</p>` : ""}
          <p><b>Tanggal Order:</b> ${escapeText(order.created_at)}</p>
          <p><b>Deadline Pembayaran:</b> ${escapeText(order.payment_deadline || "-")}</p>
        </div>
      </section>

      <aside class="modal-card">
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold ${statusClass}">${escapeText(statusLabels[order.status] || order.status)}</span>
        <h3 class="mt-4 font-display text-xl font-extrabold">Status & Aksi</h3>
        <p class="mt-3 text-sm text-[var(--muted)]">${escapeText(getStatusInstruction(order))}</p>
        <div class="mt-6 grid gap-3">${renderActions(order)}</div>
      </aside>

      <section class="modal-card">
        <h3 class="font-display text-xl font-extrabold">Item Pesanan</h3>
        <div class="mt-5 grid gap-3">${renderItems(order.items || [])}</div>
      </section>

      <section class="modal-card">
        <h3 class="font-display text-xl font-extrabold">Ringkasan Pembayaran</h3>
        <div class="mt-5 grid gap-3 text-sm text-[var(--muted)]">
          <p><b>Metode:</b> ${escapeText(order.payment_method || "-")}</p>
          <p><b>Total:</b> <span class="text-lg font-extrabold text-[var(--text)]">${rupiah.format(Number(order.total_amount || 0))}</span></p>
        </div>
      </section>

      ${renderDeliveryNote(order)}
    </div>
  `;
  if (hasDeadline) {
    startCountdown(order.payment_deadline, order.order_code);
  }
}

async function loadStatus(orderCode) {
  hideMessage();
  renderLoading();
  try {
    const res = await apiGet(`/orders.php?code=${encodeURIComponent(orderCode)}`);
    if (!res.success) return renderError(res.message || "Order tidak ditemukan.");
    renderOrder(res.data);
  } catch (error) {
    renderError("Gagal memuat pesanan.");
  }
}

document.querySelector("#themeToggle").addEventListener("click", () => {
  document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", document.documentElement.classList.contains("dark") ? "dark" : "light");
  updateThemeIcon();
});

document.querySelector("#statusForm").addEventListener("submit", (event) => {
  event.preventDefault();
  const value = document.querySelector("#orderCode").value.trim().toUpperCase();
  if (!value) return showMessage("Kode order wajib diisi.");
  history.replaceState(null, "", `order-status.php?code=${encodeURIComponent(value)}`);
  loadStatus(value);
});

updateThemeIcon();

if (code) {
  const normalizedCode = code.toUpperCase();
  document.querySelector("#orderCode").value = normalizedCode;
  loadStatus(normalizedCode);
} else {
  renderEmpty();
}
