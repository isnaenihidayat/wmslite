import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse } from "./shipment.service";

// ── Types ────────────────────────────────────────────────────────────────────

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

export interface LaravelDashboardResponse {
  data: {
    total_shipments: number;
    total_inbound: number;
    outbound_active: number;
    outbound_done: number;
    recent_shipments: {
      id: number;
      hawb: string;
      descr: string;
      status: string;
      date_created: string;
      eta: string | null;
    }[];
    recent_outbound: {
      id: number;
      po: string | null;
      destination: string;
      qty: string | null;
      status: string;
      date_created: string;
    }[];
    status_breakdown: { status: string; count: number }[];
  };
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchDashboardStats(): Promise<DashboardStats> {
  const response = await apiClient.get<LaravelDashboardResponse>(
    "/dashboard/stats"
  );

  const d = response.data.data;

  const recentInbound: RecentItem[] = d.recent_shipments.map((s) => ({
    id:       s.id,
    label:    s.hawb,
    sublabel: s.descr,
    status:   s.status,
    date:     s.date_created,
  }));

  const recentOutbound: RecentItem[] = d.recent_outbound.map((o) => ({
    id:       o.id,
    label:    o.po ?? `#${o.id}`,
    sublabel: o.destination,
    status:   o.status,
    date:     o.date_created,
  }));

  const statusBreakdown: StatusCount[] = d.status_breakdown.map((s) => ({
    status: s.status,
    count:  s.count,
  }));

  return {
    totalShipments:   d.total_shipments,
    inboundInTransit: d.total_inbound,
    activeOutbound:   d.outbound_active,
    successfulOutbound: d.outbound_done,
    recentInbound,
    recentOutbound,
    statusBreakdown,
  };
}

// ── Report endpoints ──────────────────────────────────────────────────────────

export type ReportType = "shipment" | "inbound" | "outbound" | "inventory";

export interface ReportParams {
  type: ReportType;
  startDate: string;
  endDate: string;
  page?: number;
  per_page?: number;
}

export interface ReportRow {
  [key: string]: string | number | null;
}

export interface ReportResult {
  columns: ReportColumn[];
  data: ReportRow[];
  total: number;
  lastPage: number;
}

export interface ReportColumn {
  key: string;
  label: string;
}

const REPORT_COLUMNS_MAP: Record<ReportType, ReportColumn[]> = {
  shipment: [
    { key: "id",           label: "ID" },
    { key: "hawb",         label: "HAWB" },
    { key: "descr",        label: "Description" },
    { key: "modality",     label: "Modality" },
    { key: "qty",          label: "Qty" },
    { key: "po",           label: "PO Number" },
    { key: "locator",      label: "Locator" },
    { key: "status",       label: "Status" },
    { key: "etd",          label: "ETD" },
    { key: "eta",          label: "ETA" },
    { key: "ata",          label: "ATA" },
    { key: "date_created", label: "Created" },
  ],
  inbound: [
    { key: "id",           label: "ID" },
    { key: "hawb",         label: "HAWB" },
    { key: "descr",        label: "Description" },
    { key: "modality",     label: "Modality" },
    { key: "qty",          label: "Qty" },
    { key: "po",           label: "PO Number" },
    { key: "locator",      label: "Locator" },
    { key: "status",       label: "Status" },
    { key: "etd",          label: "ETD" },
    { key: "eta",          label: "ETA" },
    { key: "ata",          label: "ATA" },
    { key: "date_created", label: "Created" },
  ],
  outbound: [
    { key: "id",           label: "ID" },
    { key: "po",           label: "GON / PO" },
    { key: "destination",  label: "Destination" },
    { key: "transporter",  label: "Transporter" },
    { key: "qty",          label: "Qty" },
    { key: "status",       label: "Status" },
    { key: "date_created", label: "Created" },
    { key: "date_updated", label: "Updated" },
  ],
  inventory: [
    { key: "hawb",      label: "HAWB" },
    { key: "descr",     label: "Description" },
    { key: "loc",       label: "Location" },
    { key: "weight",    label: "Weight (kg)" },
    { key: "flag",      label: "Picked" },
    { key: "scan_time", label: "Scan Time" },
  ],
};

export async function fetchReport(params: ReportParams): Promise<ReportResult> {
  const response = await apiClient.get<PaginatedResponse<ReportRow>>(
    `/reports/${params.type}`,
    {
      params: {
        start_date: params.startDate,
        end_date:   params.endDate,
        page:       params.page ?? 1,
        per_page:   params.per_page ?? 100,
      },
    }
  );

  return {
    columns:  REPORT_COLUMNS_MAP[params.type],
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}
