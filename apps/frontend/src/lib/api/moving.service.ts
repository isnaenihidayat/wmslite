import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse } from "./shipment.service";

// ── Types ─────────────────────────────────────────────────────────────────────

export interface Moving {
  id: number;
  hawb: string;
  hawb_descr: string;
  loc_before: string;
  loc_after: string;
  date_created: string | null;
  users: string;
}

export interface MovingFormData {
  hawb: string;
  hawb_descr?: string;
  loc_before: string;
  loc_after: string;
}

export interface MovingListParams {
  page?: number;
  per_page?: number;
  search?: string;
  date_from?: string;
  date_to?: string;
  sort?: string;
  dir?: "asc" | "desc";
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchMovingList(
  params: MovingListParams
): Promise<{ data: Moving[]; total: number; lastPage: number }> {
  const response = await apiClient.get<PaginatedResponse<Moving>>("/moving", { params });
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function createMoving(form: MovingFormData): Promise<Moving> {
  const response = await apiClient.post<{ data: Moving }>("/moving", form);
  return response.data.data;
}

export async function deleteMoving(id: number): Promise<void> {
  await apiClient.delete(`/moving/${id}`);
}
