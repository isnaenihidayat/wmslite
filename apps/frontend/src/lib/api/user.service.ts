import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse } from "./shipment.service";

// ── Types ─────────────────────────────────────────────────────────────────────

export interface AppUser {
  user_id: number;
  first_name: string;
  last_name: string;
  email_address: string;
  mobile_number: string | null;
  status: string;
  admin: number;
  module: number;
  type: number;
  date_created: string | null;
  date_modified: string | null;
  last_login: string | null;
}

export interface UserFormData {
  first_name: string;
  last_name: string;
  email_address: string;
  mobile_number?: string;
  password?: string;
  status?: "active" | "inactive";
  admin?: 0 | 1;
  module?: number;
  type?: number;
}

export interface UserListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  sort?: string;
  dir?: "asc" | "desc";
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchUserList(
  params: UserListParams
): Promise<{ data: AppUser[]; total: number; lastPage: number }> {
  const response = await apiClient.get<PaginatedResponse<AppUser>>("/admin/users", { params });
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function createUser(form: UserFormData & { password: string }): Promise<AppUser> {
  const response = await apiClient.post<{ data: AppUser }>("/admin/users", form);
  return response.data.data;
}

export async function updateUser(id: number, form: Partial<UserFormData>): Promise<AppUser> {
  const response = await apiClient.put<{ data: AppUser }>(`/admin/users/${id}`, form);
  return response.data.data;
}

export async function resetUserPassword(id: number, password: string): Promise<{ message: string }> {
  const response = await apiClient.post<{ message: string }>(`/admin/users/${id}/reset-password`, { password });
  return response.data;
}

export async function deleteUser(id: number): Promise<void> {
  await apiClient.delete(`/admin/users/${id}`);
}
