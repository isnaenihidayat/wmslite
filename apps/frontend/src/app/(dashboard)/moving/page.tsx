"use client";

import { useState, useMemo, useCallback, useEffect } from "react";
import { useSession } from "next-auth/react";
import { PaginationState } from "@tanstack/react-table";
import { DataTable } from "@/components/data-table/data-table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";
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
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { useMovingList, useCreateMoving, useDeleteMoving } from "@/hooks/use-moving";
import type { Moving, MovingFormData } from "@/lib/api/moving.service";
import { ColumnDef } from "@tanstack/react-table";
import { SortableHeader } from "@/components/data-table/sortable-header";
import { formatDateTime } from "@/lib/utils";
import { Plus, ArrowLeftRight, Trash2, ArrowRight } from "lucide-react";
import { useDebounce } from "@/hooks/use-debounce";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";

// ── Form Schema ───────────────────────────────────────────────────────────────

const movingSchema = z.object({
  hawb:       z.string().min(1, "HAWB wajib diisi"),
  hawb_descr: z.string().optional(),
  loc_before: z.string().min(1, "Lokasi asal wajib diisi"),
  loc_after:  z.string().min(1, "Lokasi tujuan wajib diisi"),
}).refine((d) => d.loc_before !== d.loc_after, {
  message: "Lokasi asal dan tujuan tidak boleh sama",
  path: ["loc_after"],
});

type MovingFormValues = z.infer<typeof movingSchema>;

// ── Columns ───────────────────────────────────────────────────────────────────

function getMovingColumns(opts: {
  onDelete?: (row: Moving) => void;
  canDelete?: boolean;
}): ColumnDef<Moving>[] {
  return [
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
      id: "movement",
      header: "Movement",
      cell: ({ row }) => (
        <span className="flex items-center gap-1 text-xs">
          <span className="font-mono bg-muted px-1.5 py-0.5 rounded">{row.original.loc_before}</span>
          <ArrowRight className="h-3 w-3 text-muted-foreground flex-shrink-0" />
          <span className="font-mono bg-primary/10 text-primary px-1.5 py-0.5 rounded">{row.original.loc_after}</span>
        </span>
      ),
      size: 220,
    },
    {
      accessorKey: "users",
      header: "Moved By",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">{row.getValue("users") || "—"}</span>
      ),
      size: 130,
    },
    {
      accessorKey: "date_created",
      header: ({ column }) => <SortableHeader column={column} label="Date" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {formatDateTime(row.getValue("date_created"))}
        </span>
      ),
      size: 150,
    },
    ...(opts.canDelete
      ? [{
          id: "actions",
          header: "",
          enableHiding: false,
          cell: ({ row }: { row: { original: Moving } }) => (
            <button
              onClick={() => opts.onDelete?.(row.original)}
              className="inline-flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors"
              title="Hapus"
            >
              <Trash2 className="h-3.5 w-3.5" />
            </button>
          ),
          size: 45,
        } as ColumnDef<Moving>]
      : []),
  ];
}

// ── Page ──────────────────────────────────────────────────────────────────────

export default function MovingPage() {
  const { data: session } = useSession();
  const token     = session?.user?.accessToken;
  const isAdmin   = session?.user?.admin === 1;
  const userType  = session?.user?.type ?? 0;
  const canCreate = isAdmin || userType === 1 || userType === 3;
  const canDelete = isAdmin;

  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch]         = useState("");
  const debouncedSearch             = useDebounce(search, 400);
  const [formOpen, setFormOpen]     = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Moving | null>(null);

  useEffect(() => {
    setPagination((p) => ({ ...p, pageIndex: 0 }));
  }, [debouncedSearch]);

  const queryParams = useMemo(
    () => ({ page: pagination.pageIndex, per_page: pagination.pageSize, search: debouncedSearch }),
    [pagination, debouncedSearch]
  );

  const { data, isLoading, refetch }                = useMovingList(queryParams, token);
  const { mutate: doCreate, isPending: isCreating } = useCreateMoving(token);
  const { mutate: doDelete, isPending: isDeleting } = useDeleteMoving(token);

  const handleDelete    = useCallback((row: Moving) => setDeleteTarget(row), []);
  const handleConfirmDelete = useCallback(() => {
    if (!deleteTarget) return;
    doDelete(deleteTarget.id, { onSuccess: () => setDeleteTarget(null) });
  }, [deleteTarget, doDelete]);

  const columns = useMemo(
    () => getMovingColumns({ onDelete: handleDelete, canDelete }),
    [handleDelete, canDelete]
  );

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  // ── Form ────────────────────────────────────────────────────────────────────
  const form = useForm<MovingFormValues>({
    resolver: zodResolver(movingSchema),
    defaultValues: { hawb: "", hawb_descr: "", loc_before: "", loc_after: "" },
  });

  useEffect(() => {
    if (!formOpen) form.reset();
  }, [formOpen, form]);

  const handleFormSubmit = (values: MovingFormValues) => {
    const payload: MovingFormData = {
      hawb:       values.hawb,
      hawb_descr: values.hawb_descr || undefined,
      loc_before: values.loc_before,
      loc_after:  values.loc_after,
    };
    doCreate(payload, { onSuccess: () => setFormOpen(false) });
  };

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
            <ArrowLeftRight className="h-4 w-4 text-primary" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Moving</h1>
            <p className="text-xs text-muted-foreground">
              {data?.total ?? 0} total records — riwayat perpindahan lokasi barang
            </p>
          </div>
        </div>

        {canCreate && (
          <Button size="sm" className="gap-2" onClick={() => setFormOpen(true)} id="btn-add-moving">
            <Plus className="h-4 w-4" />
            Create Moving
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
        searchPlaceholder="Search HAWB, description, lokasi..."
        emptyMessage="Belum ada moving record."
      />

      {/* ── Create Form Sheet ── */}
      <Sheet open={formOpen} onOpenChange={setFormOpen}>
        <SheetContent className="w-full sm:max-w-md overflow-y-auto">
          <SheetHeader className="mb-6">
            <SheetTitle className="flex items-center gap-2">
              <ArrowLeftRight className="h-4 w-4 text-primary" />
              Create Moving Record
            </SheetTitle>
            <SheetDescription>
              Catat perpindahan barang dari satu lokasi ke lokasi lain.
            </SheetDescription>
          </SheetHeader>

          <Form {...form}>
            <form onSubmit={form.handleSubmit(handleFormSubmit)} className="space-y-4" noValidate>
              <FormField
                control={form.control}
                name="hawb"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>HAWB <span className="text-destructive">*</span></FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="Nomor HAWB" id="input-mov-hawb" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="hawb_descr"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Description</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="Deskripsi barang (opsional)" id="input-mov-descr" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="grid grid-cols-2 gap-4">
                <FormField
                  control={form.control}
                  name="loc_before"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Lokasi Asal <span className="text-destructive">*</span></FormLabel>
                      <FormControl>
                        <Input {...field} placeholder="e.g. R01-A1" id="input-mov-before" />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="loc_after"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Lokasi Tujuan <span className="text-destructive">*</span></FormLabel>
                      <FormControl>
                        <Input {...field} placeholder="e.g. R02-B3" id="input-mov-after" />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <div className="flex gap-2 pt-2">
                <Button type="submit" disabled={isCreating} className="flex-1" id="btn-mov-submit">
                  {isCreating ? "Menyimpan..." : "Create Moving"}
                </Button>
                <Button type="button" variant="outline" onClick={() => setFormOpen(false)}>
                  Batal
                </Button>
              </div>
            </form>
          </Form>
        </SheetContent>
      </Sheet>

      {/* ── Delete Dialog ── */}
      <AlertDialog open={!!deleteTarget} onOpenChange={(o) => !o && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Moving Record?</AlertDialogTitle>
            <AlertDialogDescription>
              Anda akan menghapus record moving HAWB{" "}
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
              id="btn-confirm-delete-moving"
            >
              {isDeleting ? "Menghapus..." : "Hapus"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
