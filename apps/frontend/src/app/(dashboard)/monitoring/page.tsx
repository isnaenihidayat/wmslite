"use client";

import { useState, useMemo, useEffect } from "react";
import { PaginationState, ColumnDef } from "@tanstack/react-table";
import { useQuery } from "@tanstack/react-query";
import { fetchMonitoringList } from "@/lib/api/monitoring.service";
import type { ActivityLog, MonitoringListParams } from "@/lib/api/monitoring.service";
import { DataTable } from "@/components/data-table/data-table";
import { StatusBadge } from "@/components/data-table/status-badge";
import { SortableHeader } from "@/components/data-table/sortable-header";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Activity } from "lucide-react";
import { formatDateTime } from "@/lib/utils";
import { useDebounce } from "@/hooks/use-debounce";

// ── Status filter options ─────────────────────────────────────────────────────
const STATUS_OPTIONS = [
  { value: "all",     label: "All Status" },
  { value: "in",      label: "In" },
  { value: "out",     label: "Out" },
  { value: "transit", label: "Transit" },
  { value: "hold",    label: "Hold" },
];

// ── Columns ───────────────────────────────────────────────────────────────────
const columns: ColumnDef<ActivityLog>[] = [
  {
    accessorKey: "id",
    header: ({ column }) => <SortableHeader column={column} label="ID" />,
    cell: ({ row }) => (
      <span className="text-xs font-mono text-muted-foreground">{row.getValue("id")}</span>
    ),
    size: 60,
  },
  {
    accessorKey: "hawb",
    header: ({ column }) => <SortableHeader column={column} label="HAWB" />,
    cell: ({ row }) => (
      <span className="font-mono text-xs font-semibold">{row.getValue("hawb")}</span>
    ),
    size: 150,
  },
  {
    accessorKey: "hawb_descr",
    header: "Description",
    cell: ({ row }) => (
      <span className="text-sm max-w-[200px] truncate block" title={row.getValue("hawb_descr")}>
        {row.getValue("hawb_descr") || "—"}
      </span>
    ),
    size: 200,
  },
  {
    accessorKey: "loc_name",
    header: "Location",
    cell: ({ row }) => (
      <span className="text-xs font-mono bg-muted px-1.5 py-0.5 rounded">
        {row.getValue("loc_name") || "—"}
      </span>
    ),
    size: 120,
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => <StatusBadge status={row.getValue("status")} />,
    size: 110,
  },
  {
    accessorKey: "date_created",
    header: ({ column }) => <SortableHeader column={column} label="Timestamp" />,
    cell: ({ row }) => (
      <span className="text-xs text-muted-foreground whitespace-nowrap">
        {formatDateTime(row.getValue("date_created"))}
      </span>
    ),
    size: 160,
  },
];

// ── Page ──────────────────────────────────────────────────────────────────────

export default function MonitoringPage() {
  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 50 });
  const [search, setSearch]         = useState("");
  const debouncedSearch             = useDebounce(search, 400);
  const [statusFilter, setStatusFilter] = useState("all");

  // Date range — default last 30 days
  const [dateFrom, setDateFrom] = useState(() => {
    const d = new Date(); d.setDate(d.getDate() - 30);
    return d.toISOString().slice(0, 10);
  });
  const [dateTo, setDateTo] = useState(() => new Date().toISOString().slice(0, 10));

  useEffect(() => {
    setPagination((p) => ({ ...p, pageIndex: 0 }));
  }, [debouncedSearch, statusFilter, dateFrom, dateTo]);

  const queryParams = useMemo<MonitoringListParams>(
    () => ({
      page:      pagination.pageIndex + 1,
      per_page:  pagination.pageSize,
      search:    debouncedSearch || undefined,
      status:    statusFilter !== "all" ? statusFilter : undefined,
      date_from: dateFrom || undefined,
      date_to:   dateTo   || undefined,
    }),
    [pagination, debouncedSearch, statusFilter, dateFrom, dateTo]
  );

  const { data, isLoading, refetch } = useQuery({
    queryKey: ["monitoring", queryParams],
    queryFn:  () => fetchMonitoringList(queryParams),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex items-center gap-2">
        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
          <Activity className="h-4 w-4 text-primary" />
        </div>
        <div>
          <h1 className="text-xl font-bold leading-tight">Monitoring</h1>
          <p className="text-xs text-muted-foreground">
            {data?.total ?? 0} activity log entries
          </p>
        </div>
      </div>

      {/* ── Filters ── */}
      <div className="flex flex-wrap items-end gap-3">
        <div className="space-y-1">
          <Label className="text-xs text-muted-foreground">Status</Label>
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="h-9 w-36 text-xs" id="select-monitor-status">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {STATUS_OPTIONS.map((opt) => (
                <SelectItem key={opt.value} value={opt.value} className="text-xs">
                  {opt.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        <div className="space-y-1">
          <Label className="text-xs text-muted-foreground">Date From</Label>
          <Input
            type="date"
            value={dateFrom}
            onChange={(e) => setDateFrom(e.target.value)}
            className="h-9 w-36 text-xs"
            id="input-monitor-from"
          />
        </div>

        <div className="space-y-1">
          <Label className="text-xs text-muted-foreground">Date To</Label>
          <Input
            type="date"
            value={dateTo}
            onChange={(e) => setDateTo(e.target.value)}
            className="h-9 w-36 text-xs"
            id="input-monitor-to"
          />
        </div>
      </div>

      {/* ── Table ── */}
      <DataTable
        columns={columns}
        data={data?.data ?? []}
        isLoading={isLoading}
        totalRows={data?.total}
        pagination={pagination}
        onPaginationChange={setPagination}
        pageCount={pageCount}
        globalFilter={search}
        onGlobalFilterChange={setSearch}
        onRefresh={refetch}
        searchPlaceholder="Search HAWB, description, lokasi..."
        emptyMessage="Tidak ada activity log dalam rentang waktu ini."
      />
    </div>
  );
}
