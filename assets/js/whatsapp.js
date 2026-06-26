function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(Number(value || 0));
}

function normalizeWhatsAppNumber(number) {
  if (!number) return "";

  let cleaned = String(number).replace(/\D/g, "");

  if (cleaned.startsWith("0")) {
    cleaned = "62" + cleaned.substring(1);
  }

  return cleaned;
}

function isValidWhatsAppNumber(number) {
  const cleaned = normalizeWhatsAppNumber(number);
  return /^\d{10,15}$/.test(cleaned);
}

function buildWhatsAppMessage(template, order) {
  const fallbackTemplate = "Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.";
  const selectedTemplate = template || fallbackTemplate;

  return selectedTemplate
    .replaceAll("{order_code}", order.order_code || "")
    .replaceAll("{customer_name}", order.customer_name || "")
    .replaceAll("{total_amount}", formatRupiah(order.total_amount || 0))
    .replaceAll("{status}", order.status_label || order.status || "");
}

function buildWhatsAppLink(number, message) {
  const cleanNumber = normalizeWhatsAppNumber(number);
  if (!isValidWhatsAppNumber(cleanNumber)) return "";

  return `https://wa.me/${cleanNumber}?text=${encodeURIComponent(message)}`;
}
