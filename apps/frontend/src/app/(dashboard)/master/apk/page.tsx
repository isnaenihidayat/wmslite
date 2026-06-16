"use client";

import { useState, useMemo, useCallback, useEffect } from "react";
import { useSession } from "next-auth/react";
import { PaginationState, ColumnDef } from "@tanstack/react-table";
import { DataTable } from "@/components/data-table/data-table";
import { SortableHeader } from "@/components/data-table/sortable-header";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  useApkList,
  useCreateApkAccount,
  useUpdateApkAccount,
  useResetApkPassword,
  useLogoutApkAccount,
  useDeleteApkAccount,
} from "@/hooks/use-apk";
import type { ApkAccount } from "@/lib/api/apk.service";
import {
  Plus,
  Smartphone,
  MoreHorizontal,
  Pencil,
  Trash2,
  KeyRound,
  LogOut,
  Wifi,
  WifiOff,
} from "lucide-react";
import { useDebounce } from "@/hooks/use-debounce";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { formatDateTime } from "@/lib/utils";

// ── Schemas ───────────────────────────────────────────────────────────────────

const createSchema = z.object({
  name:     z.string().min(1, "Nama wajib diisi"),
  username: z.string().min(1, "Username wajib diisi"),
  password: z.string().min(4, "Min 4 karakter"),
  table:    z.enum(["main", "staging"]),
});

const editSchema = z.object({
  name:     z.string().min(1, "Nama wajib diisi"),
  username: z.string().min(1, "Username wajib diisi"),
});

const resetPwSchema = z.object({
  password:        z.string().min(4, "Min 4 karakter"),
  confirmPassword: z.string(),
}).refine((d) => d.password === d.confirmPassword, {
  message: "Password tidak cocok",
  path: ["confirmPassword"],
});

type CreateValues  = z.infer<typeof createSchema>;
type EditValues    = z.infer<typeof editSchema>;
type ResetPwValues = z.infer<typeof resetPwSchema>;

// ── Table filter options ──────────────────────────────────────────────────────

const TABLE_OPTIONS = [
  { value: "all",     label: "All Tables" },
  { value: "main",    label: "Main (el_apk)" },
  { value: "staging", label: "Staging (el_apk_s)" },
];

// ── Columns ───────────────────────────────────────────────────────────────────

function getColumns(opts: {
  onEdit:    (a: ApkAccount) => void;
  onResetPw: (a: ApkAccount) => void;
  onLogout:  (a: ApkAccount) => void;
  onDelete:  (a: ApkAccount) => void;
  canEdit:   boolean;
}): ColumnDef<ApkAccount>[] {
  return [
    {
      accessorKey: "id",
      header: ({ column }) => <SortableHeader column={column} label="ID" />,
      cell: ({ row }) => (
        <span className="text-xs font-mono text-muted-foreground">{row.getValue("id")}</span>
      ),
      size: 55,
    },
    {
      accessorKey: "name",
      header: ({ column }) => <SortableHeader column={column} label="Name" />,
      cell: ({ row }) => {
        const a = row.original;
        return (
          <div className="flex items-center gap-2">
            <div className={`flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold ${
              a.is_logged_in
                ? "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
                : "bg-muted text-muted-foreground"
            }`}>
              {a.name[0]?.toUpperCase()}
            </div>
            <div>
              <p className="text-sm font-medium">{a.name}</p>
              <p className="text-xs text-muted-foreground font-mono">{a.username}</p>
            </div>
          </div>
        );
      },
      size: 200,
    },
    {
      accessorKey: "source_table",
      header: "Table",
      cell: ({ row }) => {
        const t = row.getValue("source_table") as string;
        return (
          <Badge variant={t === "main" ? "default" : "secondary"} className="text-[10px] px-1.5">
            {t === "main" ? "Main" : "Staging"}
          </Badge>
        );
      },
      size: 80,
    },
    {
      accessorKey: "is_logged_in",
      header: "Status",
      cell: ({ row }) => {
        const online = row.getValue("is_logged_in") as boolean;
        return online ? (
          <span className="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            <Wifi className="h-3 w-3" /> Online
          </span>
        ) : (
          <span className="flex items-center gap-1 text-xs text-muted-foreground">
            <WifiOff className="h-3 w-3" /> Offline
          </span>
        );
      },
      size: 90,
    },
    {
      accessorKey: "last_login",
      header: ({ column }) => <SortableHeader column={column} label="Last Login" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {formatDateTime(row.getValue("last_login")) || "Never"}
        </span>
      ),
      size: 150,
    },
    ...(opts.canEdit
      ? [{
          id: "actions",
          header: "",
          enableHiding: false,
          cell: ({ row }: { row: { original: ApkAccount } }) => {
            const a = row.original;
            return (
              <DropdownMenu>
                <DropdownMenuTrigger
                  className="inline-flex h-7 w-7 items-center justify-center rounded-md hover:bg-accent focus-visible:outline-none"
                  id={`btn-apk-action-${a.id}-${a.source_table}`}
                >
                  <MoreHorizontal className="h-4 w-4" />
                  <span className="sr-only">Open menu</span>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-44">
                  <DropdownMenuItem onClick={() => opts.onEdit(a)}>
                    <Pencil className="mr-2 h-4 w-4" /> Edit
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => opts.onResetPw(a)}>
                    <KeyRound className="mr-2 h-4 w-4" /> Reset Password
                  </DropdownMenuItem>
                  {a.is_logged_in && (
                    <DropdownMenuItem onClick={() => opts.onLogout(a)}>
                      <LogOut className="mr-2 h-4 w-4" /> Force Logout
                    </DropdownMenuItem>
                  )}
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    onClick={() => opts.onDelete(a)}
                    className="text-destructive focus:text-destructive"
                  >
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            );
          },
          size: 45,
        } as ColumnDef<ApkAccount>]
      : []),
  ];
}

// ── Page ──────────────────────────────────────────────────────────────────────

export default function ApkPage() {
  const { data: session } = useSession();
  const isAdmin  = session?.user?.admin === 1;
  const userType = session?.user?.type ?? 0;
  const canEdit  = isAdmin || userType === 1 || userType === 3;

  const [pagination, setPagination]     = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch]             = useState("");
  const debouncedSearch                 = useDebounce(search, 400);
  const [tableFilter, setTableFilter]   = useState<"all" | "main" | "staging">("all");

  const [createOpen, setCreateOpen]     = useState(false);
  const [editTarget, setEditTarget]     = useState<ApkAccount | null>(null);
  const [resetPwTarget, setResetPwTarget] = useState<ApkAccount | null>(null);
  const [logoutTarget, setLogoutTarget] = useState<ApkAccount | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<ApkAccount | null>(null);

  useEffect(() => {
    setPagination((p) => ({ ...p, pageIndex: 0 }));
  }, [debouncedSearch, tableFilter]);

  const queryParams = useMemo(() => ({
    page:     pagination.pageIndex,
    per_page: pagination.pageSize,
    search:   debouncedSearch || undefined,
    table:    tableFilter,
  }), [pagination, debouncedSearch, tableFilter]);

  const { data, isLoading, refetch }                        = useApkList(queryParams);
  const { mutate: doCreate,   isPending: isCreating }       = useCreateApkAccount();
  const { mutate: doUpdate,   isPending: isUpdating }       = useUpdateApkAccount();
  const { mutate: doResetPw,  isPending: isResettingPw }    = useResetApkPassword();
  const { mutate: doLogout,   isPending: isLoggingOut }     = useLogoutApkAccount();
  const { mutate: doDelete,   isPending: isDeleting }       = useDeleteApkAccount();

  const handleEdit    = useCallback((a: ApkAccount) => setEditTarget(a), []);
  const handleResetPw = useCallback((a: ApkAccount) => setResetPwTarget(a), []);
  const handleLogout  = useCallback((a: ApkAccount) => setLogoutTarget(a), []);
  const handleDelete  = useCallback((a: ApkAccount) => setDeleteTarget(a), []);

  const columns = useMemo(
    () => getColumns({ onEdit: handleEdit, onResetPw: handleResetPw, onLogout: handleLogout, onDelete: handleDelete, canEdit }),
    [handleEdit, handleResetPw, handleLogout, handleDelete, canEdit]
  );

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  // Online count
  const onlineCount = useMemo(
    () => data?.data.filter((a) => a.is_logged_in).length ?? 0,
    [data?.data]
  );

  // ── Create Form ──────────────────────────────────────────────────────────────
  const createForm = useForm<CreateValues>({
    resolver: zodResolver(createSchema),
    defaultValues: { name: "", username: "", password: "", table: "main" },
  });

  useEffect(() => {
    if (!createOpen) createForm.reset({ name: "", username: "", password: "", table: "main" });
  }, [createOpen, createForm]);

  const handleCreate = (values: CreateValues) => {
    doCreate(values, { onSuccess: () => setCreateOpen(false) });
  };

  // ── Edit Form ────────────────────────────────────────────────────────────────
  const editForm = useForm<EditValues>({
    resolver: zodResolver(editSchema),
    defaultValues: { name: "", username: "" },
  });

  useEffect(() => {
    if (editTarget) {
      editForm.reset({ name: editTarget.name, username: editTarget.username });
    }
  }, [editTarget, editForm]);

  const handleEditSubmit = (values: EditValues) => {
    if (!editTarget) return;
    doUpdate(
      { id: editTarget.id, form: { ...values, table: editTarget.source_table } },
      { onSuccess: () => setEditTarget(null) }
    );
  };

  // ── Reset PW Form ────────────────────────────────────────────────────────────
  const resetPwForm = useForm<ResetPwValues>({
    resolver: zodResolver(resetPwSchema),
    defaultValues: { password: "", confirmPassword: "" },
  });

  useEffect(() => {
    if (!resetPwTarget) resetPwForm.reset();
  }, [resetPwTarget, resetPwForm]);

  const handleResetPwSubmit = (values: ResetPwValues) => {
    if (!resetPwTarget) return;
    doResetPw(
      { id: resetPwTarget.id, password: values.password, table: resetPwTarget.source_table },
      { onSuccess: () => setResetPwTarget(null) }
    );
  };

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
            <Smartphone className="h-4 w-4 text-primary" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">APK Checker</h1>
            <p className="text-xs text-muted-foreground">
              {data?.total ?? 0} scanner accounts
              {onlineCount > 0 && (
                <span className="ml-2 text-emerald-600 dark:text-emerald-400 font-medium">
                  · {onlineCount} online
                </span>
              )}
            </p>
          </div>
        </div>

        {canEdit && (
          <Button size="sm" className="gap-2" onClick={() => setCreateOpen(true)} id="btn-add-apk">
            <Plus className="h-4 w-4" />
            Add Account
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
        searchPlaceholder="Search name, username..."
        emptyMessage="Belum ada APK account."
        toolbar={
          <Select value={tableFilter} onValueChange={(v) => setTableFilter(v as "all" | "main" | "staging")}>
            <SelectTrigger className="h-9 w-44 text-xs" id="select-apk-table">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {TABLE_OPTIONS.map((opt) => (
                <SelectItem key={opt.value} value={opt.value} className="text-xs">
                  {opt.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        }
      />

      {/* ── Create Sheet ── */}
      <Sheet open={createOpen} onOpenChange={setCreateOpen}>
        <SheetContent className="w-full sm:max-w-md overflow-y-auto">
          <SheetHeader className="mb-6">
            <SheetTitle className="flex items-center gap-2">
              <Smartphone className="h-4 w-4 text-primary" />
              Add APK Account
            </SheetTitle>
            <SheetDescription>
              Buat akun baru untuk aplikasi scanner mobile.
            </SheetDescription>
          </SheetHeader>

          <Form {...createForm}>
            <form onSubmit={createForm.handleSubmit(handleCreate)} className="space-y-4" noValidate>
              <FormField control={createForm.control} name="name" render={({ field }) => (
                <FormItem>
                  <FormLabel>Nama <span className="text-destructive">*</span></FormLabel>
                  <FormControl><Input {...field} placeholder="Nama perangkat/user" id="input-apk-name" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              <FormField control={createForm.control} name="username" render={({ field }) => (
                <FormItem>
                  <FormLabel>Username <span className="text-destructive">*</span></FormLabel>
                  <FormControl><Input {...field} placeholder="username login APK" id="input-apk-username" autoComplete="off" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              <FormField control={createForm.control} name="password" render={({ field }) => (
                <FormItem>
                  <FormLabel>Password <span className="text-destructive">*</span></FormLabel>
                  <FormControl><Input {...field} type="password" placeholder="Min 4 karakter" id="input-apk-password" autoComplete="new-password" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              <FormField control={createForm.control} name="table" render={({ field }) => (
                <FormItem>
                  <FormLabel>Database Table</FormLabel>
                  <Select value={field.value} onValueChange={field.onChange}>
                    <FormControl>
                      <SelectTrigger id="select-apk-table-create"><SelectValue /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="main">Main (el_apk) — Production</SelectItem>
                      <SelectItem value="staging">Staging (el_apk_s) — Testing</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )} />

              <div className="flex gap-2 pt-2">
                <Button type="submit" disabled={isCreating} className="flex-1" id="btn-apk-create-submit">
                  {isCreating ? "Menyimpan..." : "Create Account"}
                </Button>
                <Button type="button" variant="outline" onClick={() => setCreateOpen(false)}>Batal</Button>
              </div>
            </form>
          </Form>
        </SheetContent>
      </Sheet>

      {/* ── Edit Sheet ── */}
      <Sheet open={!!editTarget} onOpenChange={(o) => !o && setEditTarget(null)}>
        <SheetContent className="w-full sm:max-w-md overflow-y-auto">
          <SheetHeader className="mb-6">
            <SheetTitle>Edit APK Account</SheetTitle>
            <SheetDescription>
              {editTarget?.name} ({editTarget?.source_table})
            </SheetDescription>
          </SheetHeader>

          <Form {...editForm}>
            <form onSubmit={editForm.handleSubmit(handleEditSubmit)} className="space-y-4" noValidate>
              <FormField control={editForm.control} name="name" render={({ field }) => (
                <FormItem>
                  <FormLabel>Nama <span className="text-destructive">*</span></FormLabel>
                  <FormControl><Input {...field} id="input-apk-edit-name" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              <FormField control={editForm.control} name="username" render={({ field }) => (
                <FormItem>
                  <FormLabel>Username <span className="text-destructive">*</span></FormLabel>
                  <FormControl><Input {...field} id="input-apk-edit-username" autoComplete="off" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              {/* Read-only table info */}
              <div className="rounded-md border px-3 py-2 text-xs text-muted-foreground flex items-center gap-2">
                <Badge variant={editTarget?.source_table === "main" ? "default" : "secondary"} className="text-[10px]">
                  {editTarget?.source_table === "main" ? "Main" : "Staging"}
                </Badge>
                Tabel tidak dapat diubah setelah dibuat.
              </div>

              <div className="flex gap-2 pt-2">
                <Button type="submit" disabled={isUpdating} className="flex-1" id="btn-apk-edit-submit">
                  {isUpdating ? "Menyimpan..." : "Simpan Perubahan"}
                </Button>
                <Button type="button" variant="outline" onClick={() => setEditTarget(null)}>Batal</Button>
              </div>
            </form>
          </Form>
        </SheetContent>
      </Sheet>

      {/* ── Reset Password Dialog ── */}
      <AlertDialog open={!!resetPwTarget} onOpenChange={(o) => !o && setResetPwTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle className="flex items-center gap-2">
              <KeyRound className="h-4 w-4" />
              Reset Password APK
            </AlertDialogTitle>
            <AlertDialogDescription>
              Reset password untuk <span className="font-semibold text-foreground">{resetPwTarget?.name}</span>{" "}
              <Badge variant="secondary" className="text-[10px]">{resetPwTarget?.source_table}</Badge>
              <br />Password baru akan memaksa login ulang di perangkat.
            </AlertDialogDescription>
          </AlertDialogHeader>

          <Form {...resetPwForm}>
            <form onSubmit={resetPwForm.handleSubmit(handleResetPwSubmit)} className="space-y-3">
              <FormField control={resetPwForm.control} name="password" render={({ field }) => (
                <FormItem>
                  <FormLabel>Password Baru</FormLabel>
                  <FormControl><Input {...field} type="password" placeholder="Min 4 karakter" id="input-apk-reset-pw" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <FormField control={resetPwForm.control} name="confirmPassword" render={({ field }) => (
                <FormItem>
                  <FormLabel>Konfirmasi Password</FormLabel>
                  <FormControl><Input {...field} type="password" placeholder="Ulangi password" id="input-apk-reset-pw-confirm" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <AlertDialogFooter className="pt-2">
                <AlertDialogCancel type="button" disabled={isResettingPw}>Batal</AlertDialogCancel>
                <Button type="submit" disabled={isResettingPw} id="btn-apk-confirm-reset">
                  {isResettingPw ? "Mereset..." : "Reset Password"}
                </Button>
              </AlertDialogFooter>
            </form>
          </Form>
        </AlertDialogContent>
      </AlertDialog>

      {/* ── Force Logout Dialog ── */}
      <AlertDialog open={!!logoutTarget} onOpenChange={(o) => !o && setLogoutTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle className="flex items-center gap-2">
              <LogOut className="h-4 w-4" />
              Force Logout?
            </AlertDialogTitle>
            <AlertDialogDescription>
              Paksa logout{" "}
              <span className="font-semibold text-foreground">{logoutTarget?.name}</span>{" "}
              dari perangkat scanner. Token sesi akan dihapus.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isLoggingOut}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => {
                if (logoutTarget) doLogout(
                  { id: logoutTarget.id, table: logoutTarget.source_table },
                  { onSuccess: () => setLogoutTarget(null) }
                );
              }}
              disabled={isLoggingOut}
              id="btn-apk-confirm-logout"
            >
              {isLoggingOut ? "Logging out..." : "Force Logout"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      {/* ── Delete Dialog ── */}
      <AlertDialog open={!!deleteTarget} onOpenChange={(o) => !o && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus APK Account?</AlertDialogTitle>
            <AlertDialogDescription>
              Anda akan menghapus akun{" "}
              <span className="font-semibold text-foreground">{deleteTarget?.name}</span>{" "}
              <Badge variant="secondary" className="text-[10px]">{deleteTarget?.source_table}</Badge>.
              Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => {
                if (deleteTarget) doDelete(
                  { id: deleteTarget.id, table: deleteTarget.source_table },
                  { onSuccess: () => setDeleteTarget(null) }
                );
              }}
              disabled={isDeleting}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              id="btn-apk-confirm-delete"
            >
              {isDeleting ? "Menghapus..." : "Hapus Account"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
