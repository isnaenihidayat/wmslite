import axios from "axios";

const API_BASE = process.env.NEXT_PUBLIC_API_URL || "/wmslite/ajax";

export const apiClient = axios.create({
  baseURL: API_BASE,
  withCredentials: true,
  headers: {
    "Content-Type": "application/x-www-form-urlencoded",
    Accept: "application/json",
  },
});

// Request interceptor — inject auth token if available
apiClient.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = localStorage.getItem("wms_token");
    if (token) {
      config.headers["Authorization"] = `Bearer ${token}`;
    }
  }
  return config;
});

// Response interceptor — handle Yii error format (code: 2)
apiClient.interceptors.response.use(
  (response) => {
    // Yii returns code=2 for errors, but HTTP 200
    if (response.data && response.data.code === 2) {
      return Promise.reject(new Error(response.data.msg || "Request failed"));
    }
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Session expired — redirect to login
      if (typeof window !== "undefined") {
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  }
);

// ========================
// Laravel API client (will be used in Phase 2)
// ========================
export const laravelClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_LARAVEL_API_URL || "http://localhost:8000/api",
  withCredentials: true,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

laravelClient.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = localStorage.getItem("wms_token");
    if (token) {
      config.headers["Authorization"] = `Bearer ${token}`;
    }
  }
  return config;
});

// Convenience POST helper for Yii AJAX (form-urlencoded)
export function toFormData(data: Record<string, unknown>): URLSearchParams {
  const params = new URLSearchParams();
  Object.entries(data).forEach(([key, val]) => {
    if (val !== undefined && val !== null) {
      params.append(key, String(val));
    }
  });
  return params;
}

export default apiClient;
