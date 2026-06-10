import axios, { AxiosInstance } from "axios";

const LARAVEL_API = process.env.NEXT_PUBLIC_LARAVEL_API_URL || "http://localhost:8000/api";

/**
 * Laravel API Client — primary client for all WMS Lite API calls.
 * Uses Bearer token (Sanctum) from session storage.
 */
export const apiClient: AxiosInstance = axios.create({
  baseURL: LARAVEL_API,
  withCredentials: false, // Using Bearer token, not cookies
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  timeout: 15000,
});

// Request interceptor — inject Bearer token from storage
apiClient.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = sessionStorage.getItem("wms_access_token") 
      || localStorage.getItem("wms_access_token");
    if (token) {
      config.headers["Authorization"] = `Bearer ${token}`;
    }
  }
  return config;
});

// Response interceptor — handle auth + error responses
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      if (typeof window !== "undefined") {
        // Clear stale token
        sessionStorage.removeItem("wms_access_token");
        localStorage.removeItem("wms_access_token");
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  }
);

/**
 * Inject a token programmatically (called after login).
 * Stores in sessionStorage for the current browser session.
 */
export function setAuthToken(token: string): void {
  if (typeof window !== "undefined") {
    sessionStorage.setItem("wms_access_token", token);
  }
}

/**
 * Clear stored token (called on logout).
 */
export function clearAuthToken(): void {
  if (typeof window !== "undefined") {
    sessionStorage.removeItem("wms_access_token");
    localStorage.removeItem("wms_access_token");
  }
}

/**
 * Create an authenticated client with a pre-set token.
 * Use this in Server Components / API Routes where we have the session token.
 */
export function createAuthenticatedClient(token: string): AxiosInstance {
  const client = axios.create({
    baseURL: LARAVEL_API,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      Authorization: `Bearer ${token}`,
    },
    timeout: 15000,
  });

  client.interceptors.response.use(
    (response) => response,
    (error) => Promise.reject(error)
  );

  return client;
}

// Convenience helper to convert object to URLSearchParams (legacy Yii compat)
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
