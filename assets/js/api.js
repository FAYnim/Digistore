const API_BASE = "api";

async function apiGet(endpoint) {
  try {
    const response = await fetch(`${API_BASE}${endpoint}`);

    if (!response.ok) {
      throw new Error("Request gagal");
    }

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
