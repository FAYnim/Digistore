/**
 * api.js — Helper untuk semua request ke API dashboard
 * Menggantikan dashboard-data.js (dummy data)
 */

const API_BASE = 'api';

/**
 * Request ke API, return JSON
 * @param {string} url  - Endpoint (relatif dari API_BASE atau path penuh)
 * @param {object} opts - Fetch options (method, body, dll)
 */
async function apiRequest(url, opts = {}) {
  // Ubah absolute path (jika ada) ke relative path agar aman saat diakses dari sub-folder
  if (url.startsWith('/dashboard/api/')) {
    url = url.replace('/dashboard/api/', 'api/');
  }
  
  const fullUrl = url.startsWith('/') || url.startsWith('http') ? url : url;
  try {
    const res = await fetch(fullUrl, {
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      ...opts,
    });
    const json = await res.json();
    return json;
  } catch (err) {
    console.error('[API Error]', err);
    return { success: false, message: 'Gagal terhubung ke server', data: null, errors: null };
  }
}

// Shorthand helpers
const api = {
  get:    (url)          => apiRequest(url),
  post:   (url, body)    => apiRequest(url, { method: 'POST',   body: JSON.stringify(body) }),
  put:    (url, body)    => apiRequest(url, { method: 'PUT',    body: JSON.stringify(body) }),
  delete: (url)          => apiRequest(url, { method: 'DELETE' }),
};

/**
 * Tampilkan toast notifikasi singkat
 */
function showToast(message, type = 'success') {
  const colors = { success: '#16a34a', error: '#dc2626', info: '#2563eb' };
  const toast = document.createElement('div');
  toast.textContent = message;
  toast.style.cssText = `
    position:fixed; bottom:24px; right:24px; z-index:9999;
    padding:12px 20px; border-radius:12px; font-weight:700; font-size:14px;
    color:#fff; background:${colors[type] ?? colors.info};
    box-shadow:0 4px 20px rgba(0,0,0,.25);
    animation: fadeInUp .25s ease;
  `;
  document.head.insertAdjacentHTML('beforeend', `<style>@keyframes fadeInUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}</style>`);
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
