import { useQuery } from "@tanstack/react-query";
import { fetchDashboardStats, fetchReport, type ReportParams } from "@/lib/api/dashboard.service";

export function useDashboardStats() {
  return useQuery({
    queryKey: ["dashboard", "stats"],
    queryFn: fetchDashboardStats,
    staleTime: 60_000,      // refresh every 60s
    refetchInterval: 120_000, // auto-refetch every 2 min
  });
}

export function useReport(params: ReportParams, enabled = true) {
  return useQuery({
    queryKey: ["report", params],
    queryFn: () => fetchReport(params),
    enabled,
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}
