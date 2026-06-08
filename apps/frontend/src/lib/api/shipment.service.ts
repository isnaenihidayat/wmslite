import { apiClient } from "@/lib/api/client";
import type {
  Shipment,
  ShipmentFormData,
  LegacyListResponse,
  LegacyResponse,
} from "@/types";

// ── Yii DataTables-style params → server query ──────────────────────────────
export interface ShipmentListParams {
  page: number;       // 0-indexed
  pageSize: number;
  search?: string;
  sortBy?: string;
  sortDir?: "asc" | "desc";
  status?: string;
  warehouse?: string;
  dateFrom?: string;
  dateTo?: string;
}

function buildLegacyParams(params: ShipmentListParams) {
  const start = params.page * params.pageSize;
  const qs: Record<string, string | number> = {
    sEcho: 1,
    iDisplayStart: start,
    iDisplayLength: params.pageSize,
  };
  if (params.search) {
    qs["sSearch"] = params.search;
  }
  return qs;
}

// ── API Functions ─────────────────────────────────────────────────────────────

/**
 * GET shipment list from legacy Yii `actionshlist` endpoint
 * Returns Yii DataTables format: { sEcho, iTotalRecords, iTotalDisplayRecords, aaData }
 */
export async function fetchShipmentList(
  params: ShipmentListParams
): Promise<{ data: Shipment[]; total: number }> {
  const qs = buildLegacyParams(params);
  const queryString = new URLSearchParams(
    Object.entries(qs).map(([k, v]) => [k, String(v)])
  ).toString();

  const response = await apiClient.get<LegacyListResponse>(
    `/ajax/shlist?${queryString}`
  );

  const raw = response.data;

  // Map Yii aaData array → Shipment objects
  // Field order from actionshlist: id, hawb, descr, product_category_name,
  // modality, delivery_id, qty, po, ship_method, etd, eta, ata, sppb_date,
  // date_created, date_updated, status (HTML stripped)
  const mapped: Shipment[] = (raw.aaData ?? []).map((row: unknown[]) => ({
    id: Number(row[0]),
    hawb: String(row[1] ?? ""),
    descr: String(row[2] ?? ""),
    product_category_name: String(row[3] ?? ""),
    modality: String(row[4] ?? ""),
    delivery_id: String(row[5] ?? ""),
    qty: Number(row[6] ?? 0),
    po: String(row[7] ?? ""),
    ship_method: String(row[8] ?? ""),
    etd: String(row[9] ?? ""),
    eta: String(row[10] ?? ""),
    ata: String(row[11] ?? ""),
    sppb_date: String(row[12] ?? ""),
    date_created: String(row[13] ?? ""),
    date_updated: String(row[14] ?? ""),
    // Strip HTML tags from status cell
    status: String(row[15] ?? "").replace(/<[^>]+>/g, "").trim(),
  }));

  return {
    data: mapped,
    total: Number(raw.iTotalRecords ?? mapped.length),
  };
}

/**
 * POST add new shipment
 */
export async function createShipment(
  form: ShipmentFormData
): Promise<LegacyResponse> {
  const response = await apiClient.post<LegacyResponse>("/ajax/addSh", form);
  return response.data;
}

/**
 * POST update existing shipment
 */
export async function updateShipment(
  id: number,
  form: Partial<ShipmentFormData>
): Promise<LegacyResponse> {
  const response = await apiClient.post<LegacyResponse>("/ajax/addSh", {
    ...form,
    idSh: id,
  });
  return response.data;
}

/**
 * POST delete shipment
 */
export async function deleteShipment(
  hawb: string,
  deliveryId: string
): Promise<LegacyResponse> {
  const response = await apiClient.post<LegacyResponse>("/ajax/delSh", {
    hawb,
    delivery_id: deliveryId,
  });
  return response.data;
}
