const paymentDefaults = {
  payment_qris_enabled: '1',
  payment_qris_image: 'https://placehold.co/400x400?text=QRIS+Dummy',
  payment_bank_enabled: '0',
  payment_bank_name: '',
  payment_bank_account: '',
  payment_bank_holder: '',
  payment_instruction: 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin melalui WhatsApp.',
  payment_admin_whatsapp: '6281234567890',
  payment_whatsapp_message: 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.',
};

function paymentEscape(value) {
  return String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));
}

function paymentValue(id) {
  return document.querySelector(id)?.value || '';
}

function renderPaymentPreview() {
  const qrisEnabled = paymentValue('#paymentQrisEnabled') === '1';
  const bankEnabled = paymentValue('#paymentBankEnabled') === '1';
  const qrisImage = paymentValue('#paymentQrisImage') || paymentDefaults.payment_qris_image;
  const instruction = paymentValue('#paymentInstruction') || paymentDefaults.payment_instruction;
  const whatsapp = normalizeWhatsAppNumber(paymentValue('#paymentAdminWhatsapp') || paymentDefaults.payment_admin_whatsapp);
  const message = buildWhatsAppMessage(paymentValue('#paymentWhatsappMessage') || paymentDefaults.payment_whatsapp_message, {
    order_code: 'ORD-20260624-A8K3',
    customer_name: 'Budi',
    total_amount: 25000,
    status: 'pending',
    status_label: 'Menunggu Pembayaran',
  });
  const waLink = buildWhatsAppLink(whatsapp, message);
  const warning = document.querySelector('#paymentWhatsappWarning');
  if (warning) warning.classList.toggle('hidden', (paymentValue('#paymentWhatsappMessage') || paymentDefaults.payment_whatsapp_message).includes('{order_code}'));

  document.querySelector('#paymentPreview').innerHTML = `
    ${qrisEnabled ? `<img class="mx-auto h-64 w-64 rounded-3xl object-cover" src="${paymentEscape(qrisImage)}" alt="Preview QRIS">` : '<div class="rounded-2xl bg-slate-100 p-6 text-sm font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">QRIS nonaktif</div>'}
    <p class="mt-5 text-3xl font-black">Rp25.000</p>
    ${bankEnabled ? `<div class="mt-5 rounded-2xl border border-slate-200 p-4 text-left text-sm dark:border-slate-800"><p><b>${paymentEscape(paymentValue('#paymentBankName'))}</b></p><p>${paymentEscape(paymentValue('#paymentBankAccount'))}</p><p>${paymentEscape(paymentValue('#paymentBankHolder'))}</p></div>` : ''}
    <p class="mt-5 text-left text-sm text-slate-500 dark:text-slate-400">${paymentEscape(instruction)}</p>
    ${waLink ? `<a class="btn-primary mt-5 inline-flex" href="${paymentEscape(waLink)}" target="_blank" rel="noopener">Konfirmasi WhatsApp</a>` : '<p class="mt-5 text-sm font-bold text-slate-500 dark:text-slate-400">WhatsApp admin belum tersedia.</p>'}
  `;
}

async function initPaymentSettings() {
  const res = await api.get('/dashboard/api/payment-settings.php');
  const data = res.success ? { ...paymentDefaults, ...res.data } : paymentDefaults;

  document.querySelector('#paymentQrisEnabled').value = data.payment_qris_enabled;
  document.querySelector('#paymentQrisImage').value = data.payment_qris_image;
  document.querySelector('#paymentBankEnabled').value = data.payment_bank_enabled;
  document.querySelector('#paymentBankName').value = data.payment_bank_name;
  document.querySelector('#paymentBankAccount').value = data.payment_bank_account;
  document.querySelector('#paymentBankHolder').value = data.payment_bank_holder;
  document.querySelector('#paymentInstruction').value = data.payment_instruction;
  document.querySelector('#paymentAdminWhatsapp').value = data.payment_admin_whatsapp;
  document.querySelector('#paymentWhatsappMessage').value = data.payment_whatsapp_message;

  renderPaymentPreview();

  document.querySelectorAll('#paymentSettingsForm input, #paymentSettingsForm textarea, #paymentSettingsForm select').forEach((el) => {
    el.addEventListener('input', renderPaymentPreview);
    el.addEventListener('change', renderPaymentPreview);
  });

  document.querySelector('#paymentSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      payment_qris_enabled: paymentValue('#paymentQrisEnabled'),
      payment_qris_image: paymentValue('#paymentQrisImage'),
      payment_bank_enabled: paymentValue('#paymentBankEnabled'),
      payment_bank_name: paymentValue('#paymentBankName'),
      payment_bank_account: paymentValue('#paymentBankAccount'),
      payment_bank_holder: paymentValue('#paymentBankHolder'),
      payment_instruction: paymentValue('#paymentInstruction'),
      payment_admin_whatsapp: paymentValue('#paymentAdminWhatsapp'),
      payment_whatsapp_message: paymentValue('#paymentWhatsappMessage'),
    };

    const update = await api.put('/dashboard/api/payment-settings.php', payload);
    if (!update.success) {
      showToast(Array.isArray(update.errors) ? update.errors.join(', ') : update.message, 'error');
      return;
    }
    showToast('Setting tersimpan.');
  });
}

initPaymentSettings();
