import { useQuery } from "@tanstack/react-query";
import { fetchDashboardStats, fetchReport, type ReportParams } from "@/lib/api/dashboard.service";

export function useDashboardStats(token: string | undefined) {
  return useQuery({
    queryKey: ["dashboard", "stats"],
    queryFn: () => fetchDashboardStats(token as string),
    enabled: !!token,
    staleTime: 60_000,      // refresh every 60s
    refetchInterval: 120_000, // auto-refetch every 2 min
  });
}

export function useReport(params: ReportParams, enabled = true, token?: string) {
  return useQuery({
    queryKey: ["report", params],
    queryFn: () => fetchReport(params, token as string),
    enabled: enabled && !!token,
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}
