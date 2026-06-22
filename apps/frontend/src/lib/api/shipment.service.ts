import { createAuthenticatedClient } from "@/lib/api/client";

// ── Types ────────────────────────────────────────────────────────────────────

export interface Shipment {
  id: number;
  hawb: string;
  descr: string;
  product_category_id: number | null;
  modality: string | null;
  delivery_id: number | null;
  qty: string | null;
  po: string | null;
  locator: string | null;
  status: string;
  etd: string | null;
  eta: string | null;
  ata: string | null;
  date_created: string | null;
  date_updated: string | null;
  from_shipment: boolean;
  category?: { id: number; name: string } | null;
}

export interface ShipmentFormData {
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

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}

export interface ShipmentListParams {
  page?: number;        // 1-indexed
  per_page?: number;
  search?: string;
  status?: string;
  sort?: string;
  dir?: "asc" | "desc";
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchShipmentList(
  params: ShipmentListParams,
  token: string
): Promise<{ data: Shipment[]; total: number; lastPage: number }> {
  const client = createAuthenticatedClient(token);
  const response = await client.get<PaginatedResponse<Shipment>>(
    "/shipments",
    { params }
  );
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}

export async function fetchShipment(id: number, token: string): Promise<Shipment> {
  const client = createAuthenticatedClient(token);
  const response = await client.get<{ data: Shipment }>(`/shipments/${id}`);
  return response.data.data;
}

export async function createShipment(form: ShipmentFormData, token: string): Promise<Shipment> {
  const client = createAuthenticatedClient(token);
  const response = await client.post<{ data: Shipment }>("/shipments", form);
  return response.data.data;
}

export async function updateShipment(
  id: number,
  form: Partial<ShipmentFormData>,
  token: string
): Promise<Shipment> {
  const client = createAuthenticatedClient(token);
  const response = await client.put<{ data: Shipment }>(`/shipments/${id}`, form);
  return response.data.data;
}

export async function deleteShipment(id: number, token: string): Promise<void> {
  const client = createAuthenticatedClient(token);
  await client.delete(`/shipments/${id}`);
}

export interface PushInboundResponse {
  message: string;
  inbound_id: number;
  data?: {
    id: number;
    hawb: string;
    status: string;
  };
}

export async function pushToInbound(id: number, token: string): Promise<PushInboundResponse> {
  const client = createAuthenticatedClient(token);
  const response = await client.post<PushInboundResponse>(
    `/shipments/${id}/push-inbound`
  );
  return response.data;
}
