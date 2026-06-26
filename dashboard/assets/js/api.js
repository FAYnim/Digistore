/**
 * api.js — Helper untuk semua request ke API dashboard
 * Menggantikan dashboard-data.js (dummy data)
 */

const API_BASE = 'api';

let activeRequests = 0;

function showLoader() {
  activeRequests++;
  let loader = document.getElementById('global-api-loader');
  if (!loader) {
    loader = document.createElement('div');
    loader.id = 'global-api-loader';
    loader.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-slate-50/50 dark:bg-slate-950/50 backdrop-blur-sm transition-opacity duration-200';
    loader.innerHTML = '<div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600 dark:border-slate-700 dark:border-t-blue-500"></div>';
    document.body.appendChild(loader);
  }
  loader.style.opacity = '1';
  loader.style.pointerEvents = 'auto';
}

function hideLoader() {
  activeRequests--;
  if (activeRequests <= 0) {
    activeRequests = 0;
    const loader = document.getElementById('global-api-loader');
    if (loader) {
      loader.style.opacity = '0';
      loader.style.pointerEvents = 'none';
    }
  }
}

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
  
  showLoader();
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = { 'Content-Type': 'application/json', Accept: 'application/json', ...(opts.headers || {}) };
    if (csrfToken && ['POST', 'PUT', 'DELETE'].includes((opts.method || 'GET').toUpperCase())) {
      headers['X-CSRF-Token'] = csrfToken;
    }

    const res = await fetch(fullUrl, {
      ...opts,
      headers,
    });
    if (res.status === 401) {
      window.location.href = 'login.php';
      return { success: false, message: 'Unauthorized', data: null, errors: null };
    }

    const json = await res.json();
    return json;
  } catch (err) {
    console.error('[API Error]', err);
    return { success: false, message: 'Gagal terhubung ke server', data: null, errors: null };
  } finally {
    hideLoader();
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
