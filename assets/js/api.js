const API_BASE = "api";

async function apiGet(endpoint) {
  try {
    const response = await fetch(`${API_BASE}${endpoint}`);

    return await response.json();
  } catch (error) {
    return {
      success: false,
      message: error.message,
      data: null,
      errors: null,
    };
  }
}

async function apiPost(endpoint, payload) {
  try {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    return await response.json();
  } catch (error) {
    return {
      success: false,
      message: error.message,
      data: null,
      errors: null,
    };
  }
}

async function loadStoreName() {
  try {
    const res = await apiGet("/settings");
    if (res.success && res.data) {
      const storeName = res.data.store_name || "DigiStore";
      document.querySelectorAll("[data-store-name]").forEach((el) => {
        el.textContent = storeName;
      });
    }
  } catch (e) {}
}
