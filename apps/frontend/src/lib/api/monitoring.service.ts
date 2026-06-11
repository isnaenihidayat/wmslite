import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse } from "./shipment.service";

// ── Types ─────────────────────────────────────────────────────────────────────

export interface ActivityLog {
  id: number;
  hawb: string;
  hawb_descr: string;
  loc_name: string;
  date_created: string | null;
  status: string;
}

export interface MonitoringListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  date_from?: string;
  date_to?: string;
  sort?: string;
  dir?: "asc" | "desc";
}

// ── API Functions ─────────────────────────────────────────────────────────────

export async function fetchMonitoringList(
  params: MonitoringListParams
): Promise<{ data: ActivityLog[]; total: number; lastPage: number }> {
  const response = await apiClient.get<PaginatedResponse<ActivityLog>>("/monitoring", { params });
  return {
    data:     response.data.data,
    total:    response.data.meta.total,
    lastPage: response.data.meta.last_page,
  };
}
