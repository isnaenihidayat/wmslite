import axios, { AxiosInstance } from "axios";

const LARAVEL_API = process.env.NEXT_PUBLIC_LARAVEL_API_URL || "http://localhost:8000/api";

/**
 * Unauthenticated Laravel API client — for genuinely public endpoints only
 * (e.g. the login request itself). No token is ever attached here; this app
 * currently has no caller that needs it (login uses a raw `fetch` call in
 * `lib/auth/auth.ts`), but it is kept as a minimal, safe fallback.
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

/**
 * Create an authenticated client with a pre-set token.
 * This is the sole authenticated-call pattern: every service function takes
 * a `token: string` (the NextAuth session's Sanctum access token) and builds
 * its client via this helper. No token is ever read from browser storage.
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
