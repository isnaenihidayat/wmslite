import { apiClient } from "@/lib/api/client";

export interface DashboardStats {
  totalShipments: number;
  inboundInTransit: number;
  activeOutbound: number;
  successfulOutbound: number;
  recentInbound: RecentItem[];
  recentOutbound: RecentItem[];
  statusBreakdown: StatusCount[];
}

export interface RecentItem {
  id: string | number;
  label: string;
  sublabel?: string;
  status: string;
  date: string;
}

export interface StatusCount {
  status: string;
  count: number;
}

/**
 * Fetch dashboard summary stats by hitting the list endpoints with small page size.
 * The Yii backend doesn't have a dedicated summary/stats endpoint, so we aggregate from list responses.
 */
export async function fetchDashboardStats(): Promise<DashboardStats> {
  const baseParams = "sEcho=1&iDisplayStart=0&iDisplayLength=5";

  const [shipRes, inboundRes, outboundRes] = await Promise.allSettled([
    apiClient.get(`/ajax/shlist?${baseParams}`),
    apiClient.get(`/ajax/inlist?${baseParams}`),
    apiClient.get(`/ajax/outlist?${baseParams}`),
  ]);

  // ── Shipment
  const shipData = shipRes.status === "fulfilled" ? shipRes.value.data : null;
  const totalShipments = Number(shipData?.iTotalRecords ?? 0);
  const recentInbound: RecentItem[] = (shipData?.aaData ?? []).slice(0, 5).map((row: unknown[]) => ({
    id: String(row[0] ?? ""),
    label: String(row[1] ?? "—"),           // hawb
    sublabel: String(row[2] ?? ""),          // descr
    status: String(row[6] ?? "").replace(/<[^>]+>/g, "").trim(), // status
    date: String(row[7] ?? ""),              // date_created
  }));

  // ── Inbound
  const inboundData = inboundRes.status === "fulfilled" ? inboundRes.value.data : null;
  const inboundTotal = Number(inboundData?.iTotalRecords ?? 0);

  // ── Outbound
  const outboundData = outboundRes.status === "fulfilled" ? outboundRes.value.data : null;
  const activeOutbound = Number(outboundData?.iTotalRecords ?? 0);
  const recentOutbound: RecentItem[] = (outboundData?.aaData ?? []).slice(0, 5).map((row: unknown[]) => ({
    id: Number(row[0] ?? 0),
    label: String(row[2] ?? "—"),           // po/GON
    sublabel: String(row[3] ?? ""),          // destination
    status: String(row[9] ?? "").replace(/<[^>]+>/g, "").trim(), // status
    date: String(row[6] ?? ""),
  }));

  // status breakdown from outbound
  const statusCounts: Record<string, number> = {};
  (outboundData?.aaData ?? []).forEach((row: unknown[]) => {
    const s = String(row[9] ?? "").replace(/<[^>]+>/g, "").trim().toLowerCase();
    statusCounts[s] = (statusCounts[s] ?? 0) + 1;
  });
  const statusBreakdown: StatusCount[] = Object.entries(statusCounts).map(([status, count]) => ({
    status,
    count,
  }));

  return {
    totalShipments,
    inboundInTransit: inboundTotal,
    activeOutbound,
    successfulOutbound: statusCounts["successful"] ?? 0,
    recentInbound,
    recentOutbound,
    statusBreakdown,
  };
}

// ── Report endpoints
export type ReportType = "shipment" | "inbound" | "outbound" | "inventory";

export interface ReportParams {
  type: ReportType;
  startDate: string;
  endDate: string;
  page: number;
  pageSize: number;
  search?: string;
}

export interface ReportRow {
  [key: string]: string | number;
}

export interface ReportResult {
  columns: string[];
  data: ReportRow[];
  total: number;
}

const REPORT_ENDPOINT_MAP: Record<ReportType, string> = {
  inbound: "/ajax/getReportsIn",
  shipment: "/ajax/getReportsSh",
  outbound: "/ajax/getReportsOuts",
  inventory: "/ajax/getReportsInv",
};

const REPORT_COLUMNS_MAP: Record<ReportType, string[]> = {
  inbound: ["HAWB", "Description", "PO", "Locator", "Koli", "Loc", "PIB No.", "SPPB Date", "Created", "Updated", "Checker", "Scan Time"],
  shipment: ["HAWB", "Description", "Delivery ID", "Modality", "PO", "Locator", "ETD", "ETA", "ATA", "Status", "Created"],
  outbound: ["HAWB", "Part No.", "Lot No.", "Loc", "GON/PO", "Destination", "Checker", "Created", "Scan Time"],
  inventory: ["HAWB", "Part No.", "Lot No.", "Qty", "Loc", "Locator", "Checker", "Scan Time", "Status"],
};

export async function fetchReport(params: ReportParams): Promise<ReportResult> {
  const endpoint = REPORT_ENDPOINT_MAP[params.type];
  const start = params.page * params.pageSize;
  const qs = new URLSearchParams({
    sEcho: "1",
    iDisplayStart: String(start),
    iDisplayLength: String(params.pageSize),
    start_date: params.startDate,
    end_date: params.endDate,
    ...(params.search ? { sSearch: params.search } : {}),
  }).toString();

  const res = await apiClient.get(`${endpoint}?${qs}`);
  const raw = res.data;
  const cols = REPORT_COLUMNS_MAP[params.type];

  const data: ReportRow[] = (raw.aaData ?? []).map((row: unknown[]) => {
    const obj: ReportRow = {};
    cols.forEach((col, i) => {
      obj[col] = String(row[i] ?? "");
    });
    return obj;
  });

  return {
    columns: cols,
    data,
    total: Number(raw.iTotalRecords ?? data.length),
  };
}
