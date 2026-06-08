import { apiClient } from "@/lib/api/client";
import type {
  InboundHeader,
  LegacyListResponse,
  LegacyResponse,
} from "@/types";

export interface InboundListParams {
  page: number;
  pageSize: number;
  search?: string;
  status?: string;
  warehouse?: string;
}

function buildLegacyParams(params: InboundListParams) {
  const start = params.page * params.pageSize;
  const qs: Record<string, string | number> = {
    sEcho: 1,
    iDisplayStart: start,
    iDisplayLength: params.pageSize,
  };
  if (params.search) qs["sSearch"] = params.search;
  if (params.warehouse) qs["warehouse"] = params.warehouse;
  return qs;
}

/**
 * GET inbound list from `actioninlist`
 * Data comes from view_schenker_inbound_combine VIEW
 * aaData field order:
 *  0:id, 1:hawb, 2:descr, 3:product_category_name, 4:modality,
 *  5:delivery_id, 6:po, 7:locator, 8:etd, 9:eta, 10:ata,
 *  11:sppb_date, 12:date_created, 13:date_updated, 14:status(HTML),
 *  15:totalQtyReceived, 16:itemInDetail, 17:totalPick, 18:action(HTML)
 */
export async function fetchInboundList(
  params: InboundListParams
): Promise<{ data: InboundHeader[]; total: number }> {
  const qs = buildLegacyParams(params);
  const queryString = new URLSearchParams(
    Object.entries(qs).map(([k, v]) => [k, String(v)])
  ).toString();

  const response = await apiClient.get<LegacyListResponse>(
    `/ajax/inlist?${queryString}`
  );
  const raw = response.data;

  const mapped: InboundHeader[] = (raw.aaData ?? []).map((row: unknown[]) => ({
    id: Number(row[0]),
    hawb: String(row[1] ?? ""),
    descr: String(row[2] ?? ""),
    product_category_name: String(row[3] ?? ""),
    modality: String(row[4] ?? ""),
    delivery_id: String(row[5] ?? ""),
    po: String(row[6] ?? ""),
    locator: String(row[7] ?? ""),
    etd: String(row[8] ?? ""),
    eta: String(row[9] ?? ""),
    ata: String(row[10] ?? ""),
    sppb_date: String(row[11] ?? ""),
    date_created: String(row[12] ?? ""),
    date_updated: String(row[13] ?? ""),
    status: String(row[14] ?? "").replace(/<[^>]+>/g, "").trim(),
    totalQtyReceived: Number(row[15] ?? 0),
    itemInDetail: Number(row[16] ?? 0),
    totalPick: Number(row[17] ?? 0),
  }));

  return { data: mapped, total: Number(raw.iTotalRecords ?? mapped.length) };
}

export interface InboundFormData {
  hawb: string;
  hawb_descr?: string;
  delivery_id_in?: string;
  modality_in?: string;
  product_category_in?: number;
  po_number?: string;
  qty?: number;
  locator_number?: string;
  etd?: string;
  eta?: string;
  ata?: string;
  sppb_date?: string;
  warehouse_in: string;
  filename?: string;
}

export async function createInbound(form: InboundFormData): Promise<LegacyResponse> {
  const res = await apiClient.post<LegacyResponse>("/ajax/addIn", form);
  return res.data;
}

export async function updateInbound(id: number, form: Partial<InboundFormData>): Promise<LegacyResponse> {
  const res = await apiClient.post<LegacyResponse>("/ajax/addIn", { ...form, idInb: id });
  return res.data;
}

export async function deleteInbound(hawb: string, deliveryId: string): Promise<LegacyResponse> {
  const res = await apiClient.post<LegacyResponse>("/ajax/delInbound", {
    hawb,
    deliveryId,
  });
  return res.data;
}

/** Fetch detail koli for a specific HAWB */
export async function fetchInboundDetails(hawb: string) {
  const res = await apiClient.post("/ajax/getInboundDetails", { hawb });
  return res.data;
}
