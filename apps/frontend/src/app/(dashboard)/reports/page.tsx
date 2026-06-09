"use client";

import { useState, useMemo, useCallback } from "react";
import { useSession } from "next-auth/react";
import { PaginationState } from "@tanstack/react-table";
import { useReport } from "@/hooks/use-dashboard";
import { DataTable } from "@/components/data-table/data-table";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
  BarChart3,
  Download,
  Search,
  FileText,
  Package,
  Ship,
  PackageCheck,
  PackageOpen,
} from "lucide-react";
import { toast } from "sonner";
import { ColumnDef } from "@tanstack/react-table";
import type { ReportType, ReportRow } from "@/lib/api/dashboard.service";

// ── default dates
function defaultDates() {
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 7);
  return {
    start: yesterday.toISOString().slice(0, 10),
    end: today.toISOString().slice(0, 10),
  };
}

const REPORT_OPTIONS: { value: ReportType; label: string; icon: React.ComponentType<{ className?: string }>; adminOnly?: boolean }[] = [
  { value: "shipment", label: "Shipment Report", icon: Ship },
  { value: "inbound", label: "Inbound Report", icon: PackageOpen, adminOnly: true },
  { value: "outbound", label: "Outbound Report", icon: PackageCheck, adminOnly: true },
  { value: "inventory", label: "Inventory Report", icon: Package, adminOnly: true },
];

function buildColumns(cols: string[]): ColumnDef<ReportRow>[] {
  return cols.map((col) => ({
    accessorKey: col,
    header: col,
    cell: ({ row }) => (
      <span className="text-xs max-w-[180px] truncate block" title={String(row.getValue(col))}>
        {String(row.getValue(col) ?? "") || "—"}
      </span>
    ),
    size: 130,
  }));
}

function exportCSV(columns: string[], data: ReportRow[], filename: string) {
  const header = columns.join(",");
  const rows = data.map((row) =>
    columns.map((col) => `"${String(row[col] ?? "").replace(/"/g, '""')}"`).join(",")
  );
  const csv = [header, ...rows].join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

export default function ReportsPage() {
  const { data: session } = useSession();
  const isAdmin = session?.user?.admin === 1;
  const userType = session?.user?.type ?? 0;

  const { start, end } = defaultDates();
  const [reportType, setReportType] = useState<ReportType>("shipment");
  const [startDate, setStartDate] = useState(start);
  const [endDate, setEndDate] = useState(end);
  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch] = useState("");
  const [triggered, setTriggered] = useState(false);

  const availableReports = REPORT_OPTIONS.filter(
    (o) => !o.adminOnly || isAdmin || userType === 1 || userType === 3
  );

  const reportParams = useMemo(() => ({
    type: reportType,
    startDate,
    endDate,
    page: pagination.pageIndex,
    pageSize: pagination.pageSize,
    search,
  }), [reportType, startDate, endDate, pagination, search]);

  const { data, isLoading, isFetching, refetch } = useReport(reportParams, triggered);

  const columns = useMemo(() => buildColumns(data?.columns ?? []), [data?.columns]);

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  const handleLoad = useCallback(() => {
    if (!startDate || !endDate) {
      toast.error("Start date dan End date wajib diisi.");
      return;
    }
    if (startDate > endDate) {
      toast.error("Start date tidak boleh lebih dari End date.");
      return;
    }
    setPagination((p) => ({ ...p, pageIndex: 0 }));
    if (triggered) refetch();
    else setTriggered(true);
  }, [startDate, endDate, triggered, refetch]);

  const handleExport = useCallback(() => {
    if (!data?.data?.length) {
      toast.warning("Tidak ada data untuk diekspor.");
      return;
    }
    exportCSV(
      data.columns,
      data.data,
      `wms_report_${reportType}_${startDate}_${endDate}.csv`
    );
    toast.success(`Report ${reportType} berhasil diekspor.`);
  }, [data, reportType, startDate, endDate]);

  const currentReport = REPORT_OPTIONS.find((o) => o.value === reportType);

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex items-center gap-2">
        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
          <BarChart3 className="h-4 w-4 text-violet-600 dark:text-violet-400" />
        </div>
        <div>
          <h1 className="text-xl font-bold leading-tight">Reports</h1>
          <p className="text-xs text-muted-foreground">
            Export data Shipment, Inbound, Outbound, dan Inventory
          </p>
        </div>
      </div>

      {/* ── Filter Panel ── */}
      <Card>
        <CardHeader className="pb-3">
          <CardTitle className="text-sm font-semibold flex items-center gap-2">
            <FileText className="h-4 w-4" />
            Parameter Laporan
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {/* Report Type */}
            <div className="space-y-1.5">
              <Label className="text-xs">Jenis Laporan</Label>
              <Select
                value={reportType}
                onValueChange={(v) => { setReportType(v as ReportType); setTriggered(false); }}
              >
                <SelectTrigger id="select-report-type" className="h-9">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {availableReports.map((opt) => (
                    <SelectItem key={opt.value} value={opt.value} className="text-sm">
                      <span className="flex items-center gap-2">
                        <opt.icon className="h-3.5 w-3.5 opacity-70" />
                        {opt.label}
                      </span>
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Start Date */}
            <div className="space-y-1.5">
              <Label className="text-xs">Start Date</Label>
              <Input
                type="date"
                value={startDate}
                onChange={(e) => setStartDate(e.target.value)}
                className="h-9"
                id="input-report-start"
              />
            </div>

            {/* End Date */}
            <div className="space-y-1.5">
              <Label className="text-xs">End Date</Label>
              <Input
                type="date"
                value={endDate}
                onChange={(e) => setEndDate(e.target.value)}
                className="h-9"
                id="input-report-end"
              />
            </div>

            {/* Actions */}
            <div className="flex items-end gap-2">
              <Button
                onClick={handleLoad}
                disabled={isLoading || isFetching}
                className="flex-1 h-9 gap-2"
                id="btn-report-load"
              >
                <Search className="h-3.5 w-3.5" />
                {isLoading || isFetching ? "Memuat..." : "Load"}
              </Button>
              <Button
                variant="outline"
                onClick={handleExport}
                disabled={!data?.data?.length}
                className="h-9 gap-2"
                id="btn-report-export"
              >
                <Download className="h-3.5 w-3.5" />
                CSV
              </Button>
            </div>
          </div>

          {/* Current selection info */}
          {triggered && data && (
            <div className="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
              {currentReport && <currentReport.icon className="h-3.5 w-3.5" />}
              <span>
                <strong>{currentReport?.label}</strong> · {startDate} → {endDate} ·{" "}
                <Badge variant="secondary" className="text-[10px] px-1.5 py-0">
                  {data.total} records
                </Badge>
              </span>
            </div>
          )}
        </CardContent>
      </Card>

      {/* ── Results Table ── */}
      {!triggered ? (
        <Card>
          <CardContent className="flex h-48 items-center justify-center">
            <div className="text-center">
              <BarChart3 className="mx-auto h-8 w-8 text-muted-foreground/30 mb-2" />
              <p className="text-sm text-muted-foreground">
                Pilih parameter laporan dan klik <strong>Load</strong> untuk menampilkan data.
              </p>
            </div>
          </CardContent>
        </Card>
      ) : (
        <DataTable
          columns={columns}
          data={data?.data ?? []}
          isLoading={isLoading || isFetching}
          totalRows={data?.total}
          pagination={pagination}
          onPaginationChange={setPagination}
          pageCount={pageCount}
          globalFilter={search}
          onGlobalFilterChange={setSearch}
          onRefresh={() => refetch()}
          searchPlaceholder={`Search ${currentReport?.label ?? "report"}...`}
          emptyMessage="Tidak ada data untuk parameter yang dipilih."
        />
      )}
    </div>
  );
}
