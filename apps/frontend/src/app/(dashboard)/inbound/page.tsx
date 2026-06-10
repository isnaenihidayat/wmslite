"use client";

import { useState, useMemo, useCallback, useEffect } from "react";
import { useSession } from "next-auth/react";
import { PaginationState } from "@tanstack/react-table";
import { DataTable } from "@/components/data-table/data-table";
import { getInboundColumns } from "./_components/columns";
import { InboundFormSheet } from "./_components/inbound-form-sheet";
import { InboundDetailSheet } from "./_components/inbound-detail-sheet";
import { Button } from "@/components/ui/button";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  useInboundList,
  useCreateInbound,
  useUpdateInbound,
  useDeleteInbound,
} from "@/hooks/use-inbound";
import type { InboundFormData, Inbound as InboundRecord } from "@/lib/api/inbound.service";
import { Plus, PackageOpen } from "lucide-react";
import { useDebounce } from "@/hooks/use-debounce";

const STATUS_OPTIONS = [
  { value: "all", label: "All Status" },
  { value: "created", label: "Created" },
  { value: "started", label: "Started" },
  { value: "inprogress", label: "In Progress" },
  { value: "Warehouse in Transit", label: "WH in Transit" },
  { value: "successful", label: "Successful" },
  { value: "failed", label: "Failed" },
  { value: "cancelled", label: "Cancelled" },
];

const WAREHOUSE_OPTIONS = [
  { value: "all", label: "All Warehouses" },
  { value: "arcadia", label: "Arcadia" },
  { value: "cengkareng", label: "Cengkareng" },
  { value: "surabaya", label: "Surabaya" },
];

export default function InboundPage() {
  const { data: session } = useSession();
  const isAdmin = session?.user?.admin === 1;
  const userType = session?.user?.type ?? 0;
  const canEdit = isAdmin || userType === 1 || userType === 3;
  const canDelete = isAdmin || userType === 1 || userType === 3;

  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch] = useState("");
  const debouncedSearch = useDebounce(search, 400);
  const [statusFilter, setStatusFilter] = useState("all");
  const [warehouseFilter, setWarehouseFilter] = useState("all");

  useEffect(() => {
    setPagination((p) => ({ ...p, pageIndex: 0 }));
  }, [debouncedSearch, statusFilter, warehouseFilter]);

  const [formOpen, setFormOpen] = useState(false);
  const [editInbound, setEditInbound] = useState<InboundRecord | null>(null);
  const [detailInbound, setDetailInbound] = useState<InboundRecord | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<InboundRecord | null>(null);

  const queryParams = useMemo(
    () => ({
      page: pagination.pageIndex,
      per_page: pagination.pageSize,
      search: debouncedSearch,
      status: statusFilter !== "all" ? statusFilter : undefined,
      warehouse: warehouseFilter !== "all" ? warehouseFilter : undefined,
    }),
    [pagination, debouncedSearch, statusFilter, warehouseFilter]
  );

  const { data, isLoading, refetch } = useInboundList(queryParams);
  const { mutate: doCreate, isPending: isCreating } = useCreateInbound();
  const { mutate: doUpdate, isPending: isUpdating } = useUpdateInbound();
  const { mutate: doDelete, isPending: isDeleting } = useDeleteInbound();

  const handleEdit = useCallback((row: InboundRecord) => {
    setEditInbound(row);
    setFormOpen(true);
  }, []);

  const handleDelete = useCallback((row: InboundRecord) => {
    setDeleteTarget(row);
  }, []);

  const handleDetails = useCallback((row: InboundRecord) => {
    setDetailInbound(row);
  }, []);

  const handleFormSubmit = useCallback(
    (formData: InboundFormData, id?: number) => {
      if (id) {
        doUpdate({ id, form: formData }, {
          onSuccess: () => setFormOpen(false),
        });
      } else {
        doCreate(formData, {
          onSuccess: () => setFormOpen(false),
        });
      }
    },
    [doCreate, doUpdate]
  );

  const handleConfirmDelete = useCallback(() => {
    if (!deleteTarget) return;
    doDelete(deleteTarget.id, { onSuccess: () => setDeleteTarget(null) });
  }, [deleteTarget, doDelete]);

  const columns = useMemo(
    () =>
      getInboundColumns({
        onEdit: handleEdit,
        onDelete: handleDelete,
        onDetails: handleDetails,
        canEdit,
        canDelete,
      }),
    [handleEdit, handleDelete, handleDetails, canEdit, canDelete]
  );

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  // ── Aggregate stats from current page data
  const stats = useMemo(() => {
    const list = data?.data ?? [];
    return {
      totalItems:      list.reduce((s, r) => s + (r.items_total ?? 0), 0),
      totalQtyReceived: list.reduce((s, r) => s + Number(r.qty ?? 0), 0),
      totalPicked:     list.reduce((s, r) => s + (r.items_picked ?? 0), 0),
    };
  }, [data?.data]);

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
            <PackageOpen className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Inbound</h1>
            <p className="text-xs text-muted-foreground">
              {data?.total ?? 0} total records
            </p>
          </div>
        </div>
        {canEdit && (
          <Button
            size="sm"
            className="gap-2"
            onClick={() => { setEditInbound(null); setFormOpen(true); }}
            id="btn-add-inbound"
          >
            <Plus className="h-4 w-4" />
            Add Inbound
          </Button>
        )}
      </div>

      {/* ── Aggregate stats strip ── */}
      {(data?.data?.length ?? 0) > 0 && (
        <div className="grid grid-cols-3 gap-3">
          {[
            { label: "Total Items (page)", value: stats.totalItems, color: "text-foreground" },
            { label: "Total Qty Received", value: stats.totalQtyReceived, color: "text-emerald-600 dark:text-emerald-400" },
            { label: "Total Picked", value: stats.totalPicked, color: "text-blue-600 dark:text-blue-400" },
          ].map((s) => (
            <div key={s.label} className="rounded-lg border bg-card p-3">
              <p className="text-[10px] text-muted-foreground uppercase tracking-wide">{s.label}</p>
              <p className={`text-xl font-bold tabular-nums ${s.color}`}>{s.value}</p>
            </div>
          ))}
        </div>
      )}

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
        searchPlaceholder="Search HAWB, description, PO, locator..."
        emptyMessage="No inbound records found."
        toolbar={
          <div className="flex items-center gap-2">
            {/* Warehouse filter */}
            <Select value={warehouseFilter} onValueChange={(v) => setWarehouseFilter(v ?? "all")}>
              <SelectTrigger className="h-9 w-36 text-xs" id="select-wh-filter">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {WAREHOUSE_OPTIONS.map((opt) => (
                  <SelectItem key={opt.value} value={opt.value} className="text-xs">
                    {opt.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            {/* Status filter */}
            <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
              <SelectTrigger className="h-9 w-40 text-xs" id="select-status-filter-inb">
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
        }
      />

      {/* ── Form Sheet ── */}
      <InboundFormSheet
        open={formOpen}
        onOpenChange={(open) => {
          setFormOpen(open);
          if (!open) setEditInbound(null);
        }}
        inbound={editInbound}
        onSubmit={handleFormSubmit}
        isSubmitting={isCreating || isUpdating}
      />

      {/* ── Detail Sheet ── */}
      <InboundDetailSheet
        open={!!detailInbound}
        onOpenChange={(open) => !open && setDetailInbound(null)}
        inbound={detailInbound}
      />

      {/* ── Delete Dialog ── */}
      <AlertDialog open={!!deleteTarget} onOpenChange={(o) => !o && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Inbound?</AlertDialogTitle>
            <AlertDialogDescription>
              Anda akan menghapus inbound{" "}
              <span className="font-semibold text-foreground">{deleteTarget?.hawb}</span>.
              {(deleteTarget?.items_total ?? 0) > 0 && (
                <span className="block mt-1 text-destructive font-medium">
                  ⚠ Record ini memiliki {deleteTarget?.items_total} item detail.
                </span>
              )}
              Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleConfirmDelete}
              disabled={isDeleting}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              id="btn-confirm-delete-inbound"
            >
              {isDeleting ? "Menghapus..." : "Hapus"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
