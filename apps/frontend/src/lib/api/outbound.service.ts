import { apiClient } from "@/lib/api/client";
import type { OutboundHeader, LegacyListResponse, LegacyResponse } from "@/types";

export interface OutboundListParams {
  page: number;
  pageSize: number;
  search?: string;
  status?: string;
  warehouse?: string;
}

function buildParams(params: OutboundListParams) {
  const start = params.page * params.pageSize;
  const qs: Record<string, string | number> = {
    sEcho: 1,
    iDisplayStart: start,
    iDisplayLength: params.pageSize,
  };
  if (params.search) qs["sSearch"] = params.search;
  return qs;
}

/**
 * GET /ajax/outlist — actionoutlist
 * aaData field order:
 *  0:id, 1:qty, 2:po, 3:destination, 4:delivery_id,
 *  5:transporter, 6:date_created, 7:scan_time,
 *  8:date_updated, 9:status(HTML), 10:action(HTML)
 */
export async function fetchOutboundList(
  params: OutboundListParams
): Promise<{ data: OutboundHeader[]; total: number }> {
  const qs = buildParams(params);
  const queryString = new URLSearchParams(
    Object.entries(qs).map(([k, v]) => [k, String(v)])
  ).toString();

  const res = await apiClient.get<LegacyListResponse>(`/ajax/outlist?${queryString}`);
  const raw = res.data;

  const mapped: OutboundHeader[] = (raw.aaData ?? []).map((row: unknown[]) => ({
    id: Number(row[0]),
    qty: Number(row[1] ?? 0),
    po: String(row[2] ?? ""),
    destination: String(row[3] ?? ""),
    delivery_id: String(row[4] ?? ""),
    transporter: String(row[5] ?? ""),
    date_created: String(row[6] ?? ""),
    scan_time: String(row[7] ?? ""),
    date_updated: String(row[8] ?? ""),
    status: String(row[9] ?? "").replace(/<[^>]+>/g, "").trim(),
  }));

  return { data: mapped, total: Number(raw.iTotalRecords ?? mapped.length) };
}

export interface OutboundFormData {
  po_number_o: string;
  destination?: string;
  delivery_id?: string;
  transporter?: string;
  checker?: string;
  id_detail?: number[];
  qty?: number[];
  sub_hawb?: string[];
}

export async function createOutbound(form: OutboundFormData): Promise<LegacyResponse> {
  const res = await apiClient.post<LegacyResponse>("/ajax/addOut", form);
  return res.data;
}

export async function updateOutbound(id: number, form: Partial<OutboundFormData>): Promise<LegacyResponse> {
  const res = await apiClient.post<LegacyResponse>("/ajax/addOut", { ...form, idOut: id });
  return res.data;
}

export async function deleteOutbound(id: number): Promise<LegacyResponse> {
  const res = await apiClient.post<LegacyResponse>("/ajax/delOutbound", { id });
  return res.data;
}

/** GET outbound detail items (picking list) */
export async function fetchOutboundDetails(id: number) {
  const res = await apiClient.post("/ajax/getOutboundDetails", { id });
  return res.data;
}
