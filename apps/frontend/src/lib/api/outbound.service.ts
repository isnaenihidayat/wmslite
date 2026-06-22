import { createAuthenticatedClient } from "@/lib/api/client";
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
  details?: OutboundDetail[];
}

export interface OutboundDetail {
  id: number;
  hawb: string;
  descr: string;
  loc: string;
  flag: number;
  scan_time: string | null;
  date_created: string | null;
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
  params: OutboundListParams,
  token: string
): Promise<{ data: Outbound[]; total: number; lastPage: number }> {
  const client = createAuthenticatedClient(token);
  const response = await client.get<PaginatedResponse<Outbound>>(
    "/outbound",
    { params }
  );
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function fetchOutbound(id: number, token: string): Promise<Outbound> {
  const client = createAuthenticatedClient(token);
  const response = await client.get<{ data: Outbound }>(`/outbound/${id}`);
  return response.data.data;
}

/** Fetch single outbound with eager-loaded details[] */
export async function fetchOutboundWithDetails(id: number, token: string): Promise<Outbound> {
  const client = createAuthenticatedClient(token);
  const response = await client.get<{ data: Outbound }>(`/outbound/${id}`);
  return response.data.data;
}

export async function createOutbound(form: OutboundFormData, token: string): Promise<Outbound> {
  const client = createAuthenticatedClient(token);
  const response = await client.post<{ data: Outbound }>("/outbound", form);
  return response.data.data;
}

export async function updateOutbound(
  id: number,
  form: Partial<OutboundFormData>,
  token: string
): Promise<Outbound> {
  const client = createAuthenticatedClient(token);
  const response = await client.put<{ data: Outbound }>(`/outbound/${id}`, form);
  return response.data.data;
}

export async function deleteOutbound(id: number, token: string): Promise<void> {
  const client = createAuthenticatedClient(token);
  await client.delete(`/outbound/${id}`);
}
