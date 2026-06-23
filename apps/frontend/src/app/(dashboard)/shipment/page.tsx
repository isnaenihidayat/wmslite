"use client";

import { useState, useMemo, useCallback } from "react";
import { useSession } from "next-auth/react";
import { PaginationState } from "@tanstack/react-table";
import { DataTable } from "@/components/data-table/data-table";
import { useResetPageOnFilterChange } from "@/hooks/use-reset-page-on-change";
import { getShipmentColumns } from "./_components/columns";
import { ShipmentFormSheet } from "./_components/shipment-form-sheet";
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import {
  useShipmentList,
  useCreateShipment,
  useUpdateShipment,
  useDeleteShipment,
  usePushToInbound,
} from "@/hooks/use-shipment";
import type { Shipment, ShipmentFormData } from "@/lib/api/shipment.service";
import { Plus, Ship, ArrowDownToLine, Loader2 } from "lucide-react";
import { useDebounce } from "@/hooks/use-debounce";

// Status filter options
const STATUS_OPTIONS = [
  { value: "all",                  label: "All Status" },
  { value: "created",              label: "Created" },
  { value: "started",              label: "Started" },
  { value: "inprogress",           label: "In Progress" },
  { value: "acknowledged",         label: "Acknowledged" },
  { value: "Warehouse in Transit", label: "WH in Transit" },
  { value: "successful",           label: "Successful" },
  { value: "failed",               label: "Failed" },
  { value: "cancelled",            label: "Cancelled" },
];

export default function ShipmentPage() {
  const { data: session } = useSession();
  const token       = session?.user?.accessToken;
  const isAdmin     = session?.user?.admin === 1;
  const userType    = session?.user?.type ?? 0;
  const canEdit     = isAdmin || userType === 2;
  const canDelete   = isAdmin || userType === 2;
  const canPush     = isAdmin || userType === 1 || userType === 2 || userType === 3;

  // ── Pagination & Filter State ─────────────────────────────────────────────
  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch]         = useState("");
  const debouncedSearch             = useDebounce(search, 400);
  const [statusFilter, setStatusFilter] = useState("all");

  useResetPageOnFilterChange(pagination, setPagination, [debouncedSearch, statusFilter]);

  // ── Sheet / Dialog State ──────────────────────────────────────────────────
  const [formOpen, setFormOpen]           = useState(false);
  const [editShipment, setEditShipment]   = useState<Shipment | null>(null);
  const [deleteTarget, setDeleteTarget]   = useState<Shipment | null>(null);
  const [pushTarget, setPushTarget]       = useState<Shipment | null>(null);

  // ── Query & Mutations ─────────────────────────────────────────────────────
  const queryParams = useMemo(
    () => ({
      page:     pagination.pageIndex,
      per_page: pagination.pageSize,
      search:   debouncedSearch,
      status:   statusFilter !== "all" ? statusFilter : undefined,
    }),
    [pagination, debouncedSearch, statusFilter]
  );

  const { data, isLoading, refetch }            = useShipmentList(queryParams, token);
  const { mutate: doCreate, isPending: isCreating } = useCreateShipment(token);
  const { mutate: doUpdate, isPending: isUpdating } = useUpdateShipment(token);
  const { mutate: doDelete, isPending: isDeleting } = useDeleteShipment(token);
  const { mutate: doPush,   isPending: isPushing }  = usePushToInbound(token);

  // ── Handlers ──────────────────────────────────────────────────────────────
  const handleEdit = useCallback((row: Shipment) => {
    setEditShipment(row);
    setFormOpen(true);
  }, []);

  const handleDelete = useCallback((row: Shipment) => {
    setDeleteTarget(row);
  }, []);

  const handlePushOutbound = useCallback((row: Shipment) => {
    setPushTarget(row);
  }, []);

  const handleFormSubmit = useCallback(
    (formData: ShipmentFormData, id?: number) => {
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

  const handleConfirmPush = useCallback(() => {
    if (!pushTarget) return;
    doPush(pushTarget.id, { onSuccess: () => setPushTarget(null), onError: () => setPushTarget(null) });
  }, [pushTarget, doPush]);

  // ── Columns ───────────────────────────────────────────────────────────────
  const columns = useMemo(
    () =>
      getShipmentColumns({
        onEdit:        handleEdit,
        onDelete:      handleDelete,
        onDetails:     () => {/* handled inline */},
        onPushOutbound: handlePushOutbound,
        canEdit,
        canDelete,
      }),
    [handleEdit, handleDelete, handlePushOutbound, canEdit, canDelete]
  );

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
            <Ship className="h-4 w-4 text-primary" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Shipment</h1>
            <p className="text-xs text-muted-foreground">
              {data?.total ?? 0} total records
            </p>
          </div>
        </div>

        {canEdit && (
          <Button
            size="sm"
            className="gap-2"
            onClick={() => { setEditShipment(null); setFormOpen(true); }}
            id="btn-add-shipment"
          >
            <Plus className="h-4 w-4" />
            Add Shipment
          </Button>
        )}
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
        searchPlaceholder="Search HAWB, description, PO..."
        emptyMessage="No shipments found. Try adjusting your search or filters."
        toolbar={
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="h-9 w-40 text-xs" id="select-status-filter">
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
      <ShipmentFormSheet
        open={formOpen}
        onOpenChange={(open) => {
          setFormOpen(open);
          if (!open) setEditShipment(null);
        }}
        shipment={editShipment}
        onSubmit={handleFormSubmit}
        isSubmitting={isCreating || isUpdating}
      />

      {/* ── Push to Inbound Confirm Dialog ── */}
      <AlertDialog open={!!pushTarget} onOpenChange={(o) => !o && setPushTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle className="flex items-center gap-2">
              <ArrowDownToLine className="h-4 w-4 text-primary" />
              Push Shipment → Inbound?
            </AlertDialogTitle>
            <AlertDialogDescription className="space-y-1">
              <span className="block">
                Shipment{" "}
                <span className="font-semibold text-foreground">{pushTarget?.hawb}</span>{" "}
                akan berpindah ke modul <strong>Inbound</strong> — bukan record baru, record yang
                sama akan diubah statusnya.
              </span>
              <span className="block text-xs">
                Semua field (kategori, modality, ETD/ETA/ATA, PO, qty) tetap sama. Setelah ini,
                record akan muncul di modul Inbound dan tidak lagi muncul di modul Shipment.
              </span>
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isPushing}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleConfirmPush}
              disabled={isPushing}
              className="gap-2"
              id="btn-confirm-push-inbound"
            >
              {isPushing ? (
                <><Loader2 className="h-4 w-4 animate-spin" />Memproses...</>
              ) : (
                <><ArrowDownToLine className="h-4 w-4" />Push ke Inbound</>
              )}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      {/* ── Delete Confirm Dialog ── */}
      <AlertDialog open={!!deleteTarget} onOpenChange={(o) => !o && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Shipment?</AlertDialogTitle>
            <AlertDialogDescription>
              Anda akan menghapus shipment{" "}
              <span className="font-semibold text-foreground">{deleteTarget?.hawb}</span>.
              Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleConfirmDelete}
              disabled={isDeleting}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              id="btn-confirm-delete"
            >
              {isDeleting ? "Menghapus..." : "Hapus"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
