import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse } from "./shipment.service";

// ── Types ────────────────────────────────────────────────────────────────────

export interface Inbound {
  id: number;
  hawb: string;
  descr: string;
  product_category_id: number | null;
  modality: string | null;
  delivery_id: number | null;
  qty: string | null;
  po: string | null;
  locator: string | null;
  checker: string;
  status: string;
  etd: string | null;
  eta: string | null;
  ata: string | null;
  date_created: string | null;
  date_updated: string | null;
  items_total?: number;
  items_picked?: number;
  category?: { id: number; name: string } | null;
}

export interface InboundDetail {
  id?: number;
  hawb: string;
  descr: string;
  loc: string;
  weight: number | null;
  long: number | null;
  wide: number | null;
  high: number | null;
  flag: boolean;
  scan_time: string | null;
}

export interface InboundFormData {
  hawb: string;
  descr: string;
  product_category_id?: number | null;
  modality?: string;
  delivery_id?: number | null;
  qty?: string;
  po?: string;
  locator?: string;
  etd?: string;
  eta?: string;
  ata?: string;
  status?: string;
}

export interface InboundListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  warehouse?: string;
  sort?: string;
  dir?: "asc" | "desc";
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchInboundList(
  params: InboundListParams
): Promise<{ data: Inbound[]; total: number; lastPage: number }> {
  const response = await apiClient.get<PaginatedResponse<Inbound>>(
    "/inbound",
    { params }
  );
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function fetchInbound(id: number): Promise<Inbound> {
  const response = await apiClient.get<{ data: Inbound }>(`/inbound/${id}`);
  return response.data.data;
}

export async function fetchInboundDetails(id: number): Promise<InboundDetail[]> {
  const response = await apiClient.get<{ data: InboundDetail[] }>(
    `/inbound/${id}/details`
  );
  return response.data.data;
}

export async function createInbound(form: InboundFormData): Promise<Inbound> {
  const response = await apiClient.post<{ data: Inbound }>("/inbound", form);
  return response.data.data;
}

export async function updateInbound(
  id: number,
  form: Partial<InboundFormData>
): Promise<Inbound> {
  const response = await apiClient.put<{ data: Inbound }>(`/inbound/${id}`, form);
  return response.data.data;
}

export async function deleteInbound(id: number): Promise<void> {
  await apiClient.delete(`/inbound/${id}`);
}
