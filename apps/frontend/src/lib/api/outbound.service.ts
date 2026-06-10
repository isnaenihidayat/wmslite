import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse } from "./shipment.service";

// ── Types ────────────────────────────────────────────────────────────────────

export interface Outbound {
  id: number;
  qty: string | null;
  po: string | null;
  destination: string;
  checker: string;
  docfile: string | null;
  status: string;
  product_category_id: number | null;
  created_by: number | null;
  updated_by: number | null;
  date_created: string | null;
  date_updated: string | null;
  delivery_id: number | null;
  transporter: string | null;
  category?: { id: number; name: string } | null;
}

export interface OutboundFormData {
  po?:                 string;
  destination:         string;
  qty?:                string;
  delivery_id?:        number | null;
  transporter?:        string;
  status?:             string;
  product_category_id?: number | null;
}

export interface OutboundListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  sort?: string;
  dir?: "asc" | "desc";
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchOutboundList(
  params: OutboundListParams
): Promise<{ data: Outbound[]; total: number; lastPage: number }> {
  const response = await apiClient.get<PaginatedResponse<Outbound>>(
    "/outbound",
    { params }
  );
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function fetchOutbound(id: number): Promise<Outbound> {
  const response = await apiClient.get<{ data: Outbound }>(`/outbound/${id}`);
  return response.data.data;
}

export async function createOutbound(form: OutboundFormData): Promise<Outbound> {
  const response = await apiClient.post<{ data: Outbound }>("/outbound", form);
  return response.data.data;
}

export async function updateOutbound(
  id: number,
  form: Partial<OutboundFormData>
): Promise<Outbound> {
  const response = await apiClient.put<{ data: Outbound }>(`/outbound/${id}`, form);
  return response.data.data;
}

export async function deleteOutbound(id: number): Promise<void> {
  await apiClient.delete(`/outbound/${id}`);
}
