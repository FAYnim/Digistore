const paymentDefaults = {
  payment_qris_enabled: '0',
  payment_qris_image: '',
  payment_bank_enabled: '0',
  payment_bank_name: '',
  payment_bank_account: '',
  payment_bank_holder: '',
  payment_instruction: '',
  payment_admin_whatsapp: '',
  payment_whatsapp_message: 'Halo admin, saya sudah membuat pesanan {order_code}. Mohon dicek.',
};

let qrisUploading = false;

function paymentEscape(value) {
  return String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));
}

function paymentValue(id) {
  return document.querySelector(id)?.value || '';
}

function qrisShowPlaceholder() {
  document.querySelector('#qrisPlaceholder')?.classList.remove('hidden');
  document.querySelector('#qrisPreview')?.classList.add('hidden');
}

function qrisShowPreview(src, fileName) {
  const img = document.querySelector('#qrisPreviewImg');
  const nameEl = document.querySelector('#qrisFileName');
  if (img) img.src = src;
  if (nameEl) nameEl.textContent = fileName || '';
  document.querySelector('#qrisPlaceholder')?.classList.add('hidden');
  document.querySelector('#qrisPreview')?.classList.remove('hidden');
}

function qrisSetProgress(percent) {
  const bar = document.querySelector('#qrisProgressBar');
  const wrap = document.querySelector('#qrisUploadProgress');
  if (bar) bar.style.width = percent + '%';
  if (wrap) wrap.classList.toggle('hidden', percent <= 0 || percent >= 100);
}

async function qrisUploadFile(file) {
  if (qrisUploading) return;
  qrisUploading = true;

  const reader = new FileReader();
  reader.onload = () => qrisShowPreview(reader.result, file.name);
  reader.readAsDataURL(file);

  qrisSetProgress(10);

  const form = new FormData();
  form.append('qris_image', file);

  try {
    qrisSetProgress(40);
    const res = await api.upload('/dashboard/api/upload-qris', form);
    qrisSetProgress(100);

    if (res.success && res.data?.path) {
      document.querySelector('#paymentQrisImage').value = res.data.path;
      showToast('Gambar QRIS berhasil diupload.');
      renderPaymentPreview();
    } else {
      showToast(res.message || 'Upload gagal.', 'error');
      qrisRemoveImage();
    }
  } catch {
    showToast('Upload gagal.', 'error');
    qrisRemoveImage();
  } finally {
    qrisUploading = false;
    setTimeout(() => qrisSetProgress(0), 500);
  }
}

function qrisRemoveImage() {
  document.querySelector('#paymentQrisImage').value = '';
  document.querySelector('#qrisFileInput').value = '';
  qrisShowPlaceholder();
  renderPaymentPreview();
}

function qrisValidateFile(file) {
  const allowed = ['image/jpeg', 'image/png'];
  if (!allowed.includes(file.type)) {
    showToast('Format gambar hanya JPG, JPEG, atau PNG.', 'error');
    return false;
  }
  if (file.size > 2 * 1024 * 1024) {
    showToast('Ukuran gambar maksimal 2MB.', 'error');
    return false;
  }
  return true;
}

function initQrisDropzone() {
  const dropArea = document.querySelector('#qrisDropArea');
  const fileInput = document.querySelector('#qrisFileInput');
  const removeBtn = document.querySelector('#qrisRemoveBtn');

  if (!dropArea || !fileInput) return;

  ['dragenter', 'dragover'].forEach((evt) => {
    dropArea.addEventListener(evt, (e) => {
      e.preventDefault();
      dropArea.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-950');
    });
  });

  ['dragleave', 'drop'].forEach((evt) => {
    dropArea.addEventListener(evt, (e) => {
      e.preventDefault();
      dropArea.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-950');
    });
  });

  dropArea.addEventListener('drop', (e) => {
    const file = e.dataTransfer?.files?.[0];
    if (file && qrisValidateFile(file)) qrisUploadFile(file);
  });

  fileInput.addEventListener('change', () => {
    const file = fileInput.files?.[0];
    if (file && qrisValidateFile(file)) qrisUploadFile(file);
    else fileInput.value = '';
  });

  removeBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    qrisRemoveImage();
  });
}

function renderPaymentPreview() {
  const qrisEnabled = paymentValue('#paymentQrisEnabled') === '1';
  const bankEnabled = paymentValue('#paymentBankEnabled') === '1';
  const qrisImage = paymentValue('#paymentQrisImage');
  const instruction = paymentValue('#paymentInstruction');
  const defaultInstruction = qrisEnabled && bankEnabled
    ? 'Scan QRIS atau transfer bank, lalu konfirmasi ke admin.'
    : qrisEnabled
      ? 'Scan QRIS, bayar sesuai total, lalu konfirmasi ke admin.'
      : bankEnabled
        ? 'Transfer bank sesuai total, lalu konfirmasi ke admin.'
        : 'Aktifkan minimal satu metode pembayaran.';
  const whatsapp = normalizeWhatsAppNumber(paymentValue('#paymentAdminWhatsapp'));
  const message = buildWhatsAppMessage(paymentValue('#paymentWhatsappMessage') || paymentDefaults.payment_whatsapp_message, {
    order_code: 'ORD-20260624-A8K3',
    customer_name: 'Budi',
    total_amount: 25000,
    status: 'pending',
    status_label: 'Menunggu Pembayaran',
  });
  const waLink = buildWhatsAppLink(whatsapp, message);
  const warning = document.querySelector('#paymentWhatsappWarning');
  const instructionField = document.querySelector('#paymentInstruction');
  if (instructionField) instructionField.placeholder = defaultInstruction;
  if (warning) warning.classList.toggle('hidden', (paymentValue('#paymentWhatsappMessage') || paymentDefaults.payment_whatsapp_message).includes('{order_code}'));

  document.querySelector('#paymentPreview').innerHTML = `
    ${qrisEnabled ? `<img class="mx-auto h-64 w-64 rounded-3xl object-cover" src="${paymentEscape(qrisImage ? qrisImage : '')}" alt="Preview QRIS">` : '<div class="rounded-2xl bg-slate-100 p-6 text-sm font-bold text-slate-500 dark:bg-slate-900 dark:text-slate-400">QRIS nonaktif</div>'}
    <p class="mt-5 text-3xl font-black">Rp25.000</p>
    ${bankEnabled ? `<div class="mt-5 rounded-2xl border border-slate-200 p-4 text-left text-sm dark:border-slate-800"><p><b>${paymentEscape(paymentValue('#paymentBankName'))}</b></p><p>${paymentEscape(paymentValue('#paymentBankAccount'))}</p><p>${paymentEscape(paymentValue('#paymentBankHolder'))}</p></div>` : ''}
    <p class="mt-5 text-left text-sm text-slate-500 dark:text-slate-400">${paymentEscape(instruction || defaultInstruction)}</p>
    ${waLink ? `<a class="btn-primary mt-5 inline-flex" href="${paymentEscape(waLink)}" target="_blank" rel="noopener">Konfirmasi WhatsApp</a>` : '<p class="mt-5 text-sm font-bold text-slate-500 dark:text-slate-400">WhatsApp admin belum tersedia.</p>'}
  `;
}

async function initPaymentSettings() {
  initQrisDropzone();

  const res = await api.get('/dashboard/api/payment-settings');
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

  if (data.payment_qris_image) {
    qrisShowPreview(data.payment_qris_image, data.payment_qris_image.split('/').pop());
  }

  renderPaymentPreview();

  document.querySelectorAll('#paymentSettingsForm input:not(#qrisFileInput), #paymentSettingsForm textarea, #paymentSettingsForm select').forEach((el) => {
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

    const update = await api.put('/dashboard/api/payment-settings', payload);
    if (!update.success) {
      showToast(Array.isArray(update.errors) ? update.errors.join(', ') : update.message, 'error');
      return;
    }
    showToast('Setting tersimpan.');
  });
}

initPaymentSettings();
