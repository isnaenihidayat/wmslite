import { apiClient } from "@/lib/api/client";

// ── Types ─────────────────────────────────────────────────────────────────────

export interface ApkAccount {
  id: number;
  name: string;
  username: string;
  last_login: string | null;
  is_logged_in: boolean;
  source_table: "main" | "staging";
}

export interface ApkFormData {
  name:     string;
  username: string;
  password: string;
  table?:   "main" | "staging";
}

export interface ApkListParams {
  page?:     number;
  per_page?: number;
  search?:   string;
  table?:    "all" | "main" | "staging";
  sort?:     string;
  dir?:      "asc" | "desc";
}

interface PaginatedApk {
  data: ApkAccount[];
  meta: { current_page: number; per_page: number; total: number; last_page: number };
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchApkList(
  params: ApkListParams
): Promise<{ data: ApkAccount[]; total: number; lastPage: number }> {
  const response = await apiClient.get<PaginatedApk>("/master/apk", { params });
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function createApkAccount(form: ApkFormData): Promise<ApkAccount> {
  const response = await apiClient.post<{ data: ApkAccount }>("/master/apk", form);
  return response.data.data;
}

export async function updateApkAccount(
  id: number,
  form: { name?: string; username?: string; table?: "main" | "staging" }
): Promise<ApkAccount> {
  const response = await apiClient.put<{ data: ApkAccount }>(`/master/apk/${id}`, form);
  return response.data.data;
}

export async function resetApkPassword(
  id: number,
  password: string,
  table: "main" | "staging"
): Promise<{ message: string }> {
  const response = await apiClient.post<{ message: string }>(
    `/master/apk/${id}/reset-password`,
    { password, table }
  );
  return response.data;
}

export async function logoutApkAccount(
  id: number,
  table: "main" | "staging"
): Promise<{ message: string }> {
  const response = await apiClient.post<{ message: string }>(
    `/master/apk/${id}/logout`,
    { table }
  );
  return response.data;
}

export async function deleteApkAccount(
  id: number,
  table: "main" | "staging"
): Promise<void> {
  await apiClient.delete(`/master/apk/${id}`, { data: { table } });
}
