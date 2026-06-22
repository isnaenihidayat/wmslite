"use client";

import { useState, useMemo, useCallback, useEffect } from "react";
import { useSession } from "next-auth/react";
import { PaginationState } from "@tanstack/react-table";
import { DataTable } from "@/components/data-table/data-table";
import { getOutboundColumns } from "./_components/columns";
import { OutboundFormSheet } from "./_components/outbound-form-sheet";
import { OutboundDetailSheet } from "./_components/outbound-detail-sheet";
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
  useOutboundList,
  useCreateOutbound,
  useUpdateOutbound,
  useDeleteOutbound,
} from "@/hooks/use-outbound";
import type { Outbound as OutboundHeader } from "@/lib/api/outbound.service";
import type { OutboundFormData } from "@/lib/api/outbound.service";
import { Plus, PackageCheck, TrendingUp } from "lucide-react";
import { useDebounce } from "@/hooks/use-debounce";

const STATUS_OPTIONS = [
  { value: "all", label: "All Status" },
  { value: "created", label: "Created" },
  { value: "inprogress", label: "In Progress" },
  { value: "successful", label: "Successful" },
  { value: "failed", label: "Failed" },
  { value: "cancelled", label: "Cancelled" },
];

export default function OutboundPage() {
  const { data: session } = useSession();
  const token = session?.user?.accessToken;
  const isAdmin = session?.user?.admin === 1;
  const userType = session?.user?.type ?? 0;
  const canEdit = isAdmin;
  const canDelete = isAdmin || userType === 1 || userType === 3;

  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch] = useState("");
  const debouncedSearch = useDebounce(search, 400);
  const [statusFilter, setStatusFilter] = useState("all");

  useEffect(() => {
    setPagination((p) => ({ ...p, pageIndex: 0 }));
  }, [debouncedSearch, statusFilter]);

  const [formOpen, setFormOpen] = useState(false);
  const [editOutbound, setEditOutbound] = useState<OutboundHeader | null>(null);
  const [detailOutbound, setDetailOutbound] = useState<OutboundHeader | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<OutboundHeader | null>(null);

  const queryParams = useMemo(
    () => ({
      page: pagination.pageIndex,
      per_page: pagination.pageSize,
      search: debouncedSearch,
      status: statusFilter !== "all" ? statusFilter : undefined,
    }),
    [pagination, debouncedSearch, statusFilter]
  );

  const { data, isLoading, refetch } = useOutboundList(queryParams, token);
  const { mutate: doCreate, isPending: isCreating } = useCreateOutbound(token);
  const { mutate: doUpdate, isPending: isUpdating } = useUpdateOutbound(token);
  const { mutate: doDelete, isPending: isDeleting } = useDeleteOutbound(token);

  const handleEdit = useCallback((row: OutboundHeader) => {
    setEditOutbound(row);
    setFormOpen(true);
  }, []);

  const handleDelete = useCallback((row: OutboundHeader) => {
    setDeleteTarget(row);
  }, []);

  const handleDetails = useCallback((row: OutboundHeader) => {
    setDetailOutbound(row);
  }, []);

  const handleFormSubmit = useCallback(
    (formData: OutboundFormData, id?: number) => {
      if (id) {
        doUpdate({ id, form: formData }, { onSuccess: () => setFormOpen(false) });
      } else {
        doCreate(formData, { onSuccess: () => setFormOpen(false) });
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
      getOutboundColumns({
        onEdit: handleEdit,
        onDelete: handleDelete,
        onDetails: handleDetails,
        onPrint: () => {},  // Print handled inside OutboundDetailSheet
        canEdit,
        canDelete,
      }),
    [handleEdit, handleDelete, handleDetails, canEdit, canDelete]
  );

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  // Aggregate stats from current page
  const stats = useMemo(() => {
    const list = data?.data ?? [];
    const totalQty = list.reduce((s, r) => s + Number(r.qty ?? 0), 0);
    const successCount = list.filter((r) => r.status === "successful").length;
    const inProgressCount = list.filter((r) => r.status === "inprogress").length;
    return { totalQty, successCount, inProgressCount };
  }, [data?.data]);

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10">
            <PackageCheck className="h-4 w-4 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Outbound</h1>
            <p className="text-xs text-muted-foreground">
              {data?.total ?? 0} total records
            </p>
          </div>
        </div>
        {canEdit && (
          <Button
            size="sm"
            className="gap-2"
            onClick={() => { setEditOutbound(null); setFormOpen(true); }}
            id="btn-add-outbound"
          >
            <Plus className="h-4 w-4" />
            Add Outbound
          </Button>
        )}
      </div>

      {/* ── Stats strip ── */}
      {(data?.data?.length ?? 0) > 0 && (
        <div className="grid grid-cols-3 gap-3">
          {[
            { label: "Total Qty (page)", value: stats.totalQty, color: "text-blue-600 dark:text-blue-400" },
            { label: "Successful", value: stats.successCount, color: "text-emerald-600 dark:text-emerald-400" },
            { label: "In Progress", value: stats.inProgressCount, color: "text-amber-600 dark:text-amber-400" },
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
        searchPlaceholder="Search GON/PO, destination, transporter..."
        emptyMessage="No outbound records found."
        toolbar={
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="h-9 w-40 text-xs" id="select-status-filter-out">
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
        }
      />

      {/* ── Form Sheet ── */}
      <OutboundFormSheet
        open={formOpen}
        onOpenChange={(open) => {
          setFormOpen(open);
          if (!open) setEditOutbound(null);
        }}
        outbound={editOutbound}
        onSubmit={handleFormSubmit}
        isSubmitting={isCreating || isUpdating}
      />

      {/* ── Detail Sheet ── */}
      <OutboundDetailSheet
        open={!!detailOutbound}
        onOpenChange={(open) => !open && setDetailOutbound(null)}
        outbound={detailOutbound}
      />

      {/* ── Delete Dialog ── */}
      <AlertDialog open={!!deleteTarget} onOpenChange={(o) => !o && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Outbound?</AlertDialogTitle>
            <AlertDialogDescription>
              Anda akan menghapus outbound{" "}
              <span className="font-semibold text-foreground">{deleteTarget?.po}</span>.
              {deleteTarget?.status === "successful" && (
                <span className="block mt-1 text-destructive font-medium">
                  ⚠ Outbound ini sudah berstatus Successful.
                </span>
              )}
              Semua item detail terkait akan dikembalikan ke stok inbound. Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleConfirmDelete}
              disabled={isDeleting || deleteTarget?.status === "successful"}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              id="btn-confirm-delete-out"
            >
              {isDeleting ? "Menghapus..." : "Hapus"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
