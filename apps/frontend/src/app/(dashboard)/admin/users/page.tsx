"use client";

import { useState, useMemo, useCallback, useEffect } from "react";
import { useSession } from "next-auth/react";
import { PaginationState, ColumnDef } from "@tanstack/react-table";
import { DataTable } from "@/components/data-table/data-table";
import { SortableHeader } from "@/components/data-table/sortable-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
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
import { useUserList, useCreateUser, useUpdateUser, useDeleteUser, useResetPassword } from "@/hooks/use-users";
import type { AppUser, UserFormData } from "@/lib/api/user.service";
import { Plus, Users, Pencil, Trash2, KeyRound, MoreHorizontal, Shield } from "lucide-react";
import { useDebounce } from "@/hooks/use-debounce";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { formatDate } from "@/lib/utils";

// ── Types & Options ───────────────────────────────────────────────────────────

const TYPE_LABELS: Record<number, string> = {
  0: "Guest",
  1: "Staff",
  2: "Checker",
  3: "Supervisor",
  4: "Manager",
};

const MODULE_LABELS: Record<number, string> = {
  1: "Shipment",
  2: "Inbound",
  3: "Outbound",
  4: "All Modules",
};

const STATUS_FILTER_OPTIONS = [
  { value: "all",      label: "All Status" },
  { value: "active",   label: "Active" },
  { value: "inactive", label: "Inactive" },
];

// ── Form Schema ───────────────────────────────────────────────────────────────

const userSchema = z.object({
  first_name:    z.string().min(1, "First name wajib diisi"),
  last_name:     z.string().min(1, "Last name wajib diisi"),
  email_address: z.string().email("Email tidak valid"),
  mobile_number: z.string().optional(),
  password:      z.union([z.string().min(6, "Min 6 karakter"), z.literal("")]).optional(),
  status:        z.enum(["active", "inactive"]),
  admin:         z.number(),
  module:        z.number(),
  type:          z.number(),
});

type UserFormValues = z.infer<typeof userSchema>;

const resetPasswordSchema = z.object({
  password:        z.string().min(6, "Min 6 karakter"),
  confirmPassword: z.string(),
}).refine((d) => d.password === d.confirmPassword, {
  message: "Password tidak cocok",
  path: ["confirmPassword"],
});

type ResetPasswordValues = z.infer<typeof resetPasswordSchema>;

// ── Columns ───────────────────────────────────────────────────────────────────

function getUserColumns(opts: {
  onEdit:         (u: AppUser) => void;
  onDelete:       (u: AppUser) => void;
  onResetPw:      (u: AppUser) => void;
  currentUserId?: number;
}): ColumnDef<AppUser>[] {
  return [
    {
      accessorKey: "user_id",
      header: ({ column }) => <SortableHeader column={column} label="ID" />,
      cell: ({ row }) => (
        <span className="text-xs font-mono text-muted-foreground">{row.getValue("user_id")}</span>
      ),
      size: 60,
    },
    {
      id: "name",
      header: ({ column }) => <SortableHeader column={column} label="Name" />,
      cell: ({ row }) => {
        const u = row.original;
        return (
          <div className="flex items-center gap-2">
            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
              {u.first_name[0]}{u.last_name[0]}
            </div>
            <div>
              <p className="text-sm font-medium">{u.first_name} {u.last_name}</p>
              <p className="text-xs text-muted-foreground">{u.email_address}</p>
            </div>
          </div>
        );
      },
      size: 220,
    },
    {
      accessorKey: "type",
      header: "Type",
      cell: ({ row }) => (
        <span className="text-xs">{TYPE_LABELS[row.getValue("type") as number] ?? `Type ${row.getValue("type")}`}</span>
      ),
      size: 100,
    },
    {
      accessorKey: "module",
      header: "Module",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">{MODULE_LABELS[row.getValue("module") as number] ?? `Module ${row.getValue("module")}`}</span>
      ),
      size: 110,
    },
    {
      accessorKey: "admin",
      header: "Role",
      cell: ({ row }) =>
        row.getValue("admin") ? (
          <Badge variant="default" className="gap-1 text-xs px-1.5 py-0.5">
            <Shield className="h-3 w-3" /> Admin
          </Badge>
        ) : (
          <span className="text-xs text-muted-foreground">User</span>
        ),
      size: 80,
    },
    {
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => {
        const s = row.getValue("status") as string;
        return (
          <Badge variant={s === "active" ? "default" : "secondary"} className="text-xs">
            {s === "active" ? "Active" : "Inactive"}
          </Badge>
        );
      },
      size: 80,
    },
    {
      accessorKey: "last_login",
      header: ({ column }) => <SortableHeader column={column} label="Last Login" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {formatDate(row.getValue("last_login")) || "Never"}
        </span>
      ),
      size: 110,
    },
    {
      id: "actions",
      header: "Actions",
      enableHiding: false,
      cell: ({ row }) => {
        const u = row.original;
        const isSelf = u.user_id === opts.currentUserId;
        return (
          <DropdownMenu>
            <DropdownMenuTrigger
              className="inline-flex h-7 w-7 items-center justify-center rounded-md hover:bg-accent focus-visible:outline-none"
              id={`btn-action-user-${u.user_id}`}
            >
              <MoreHorizontal className="h-4 w-4" />
              <span className="sr-only">Open menu</span>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
              <DropdownMenuItem onClick={() => opts.onEdit(u)}>
                <Pencil className="mr-2 h-4 w-4" />
                Edit
              </DropdownMenuItem>
              <DropdownMenuItem onClick={() => opts.onResetPw(u)}>
                <KeyRound className="mr-2 h-4 w-4" />
                Reset Password
              </DropdownMenuItem>
              {!isSelf && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    onClick={() => opts.onDelete(u)}
                    className="text-destructive focus:text-destructive"
                  >
                    <Trash2 className="mr-2 h-4 w-4" />
                    Delete
                  </DropdownMenuItem>
                </>
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        );
      },
      size: 60,
    },
  ];
}

// ── Page ──────────────────────────────────────────────────────────────────────

export default function AdminUsersPage() {
  const { data: session } = useSession();
  const isAdmin       = session?.user?.admin === 1;
  const currentUserId = session?.user?.user_id as number | undefined;

  const [pagination, setPagination] = useState<PaginationState>({ pageIndex: 0, pageSize: 25 });
  const [search, setSearch]         = useState("");
  const debouncedSearch             = useDebounce(search, 400);
  const [statusFilter, setStatusFilter] = useState("all");

  const [formOpen, setFormOpen]           = useState(false);
  const [editUser, setEditUser]           = useState<AppUser | null>(null);
  const [deleteTarget, setDeleteTarget]   = useState<AppUser | null>(null);
  const [resetPwTarget, setResetPwTarget] = useState<AppUser | null>(null);

  useEffect(() => {
    setPagination((p) => ({ ...p, pageIndex: 0 }));
  }, [debouncedSearch, statusFilter]);

  const queryParams = useMemo(
    () => ({
      page:     pagination.pageIndex,
      per_page: pagination.pageSize,
      search:   debouncedSearch || undefined,
      status:   statusFilter !== "all" ? statusFilter : undefined,
    }),
    [pagination, debouncedSearch, statusFilter]
  );

  const { data, isLoading, refetch }                    = useUserList(queryParams);
  const { mutate: doCreate, isPending: isCreating }     = useCreateUser();
  const { mutate: doUpdate, isPending: isUpdating }     = useUpdateUser();
  const { mutate: doDelete, isPending: isDeleting }     = useDeleteUser();
  const { mutate: doResetPw, isPending: isResettingPw } = useResetPassword();

  const handleEdit    = useCallback((u: AppUser) => { setEditUser(u); setFormOpen(true); }, []);
  const handleDelete  = useCallback((u: AppUser) => setDeleteTarget(u), []);
  const handleResetPw = useCallback((u: AppUser) => setResetPwTarget(u), []);

  const columns = useMemo(
    () => getUserColumns({ onEdit: handleEdit, onDelete: handleDelete, onResetPw: handleResetPw, currentUserId }),
    [handleEdit, handleDelete, handleResetPw, currentUserId]
  );

  const pageCount = useMemo(
    () => Math.ceil((data?.total ?? 0) / pagination.pageSize),
    [data?.total, pagination.pageSize]
  );

  // ── User Form ────────────────────────────────────────────────────────────────
  const form = useForm<UserFormValues>({
    resolver: zodResolver(userSchema),
    defaultValues: {
      first_name: "", last_name: "", email_address: "", mobile_number: "",
      password: "", status: "active", admin: 0, module: 4, type: 1,
    },
  });

  useEffect(() => {
    if (formOpen) {
      if (editUser) {
        form.reset({
          first_name:    editUser.first_name,
          last_name:     editUser.last_name,
          email_address: editUser.email_address,
          mobile_number: editUser.mobile_number || "",
          password:      "",
          status:        (editUser.status as "active" | "inactive") ?? "active",
          admin:         editUser.admin as 0 | 1,
          module:        editUser.module,
          type:          editUser.type,
        });
      } else {
        form.reset({ first_name: "", last_name: "", email_address: "", mobile_number: "", password: "", status: "active", admin: 0, module: 4, type: 1 });
      }
    }
  }, [formOpen, editUser, form]);

  const handleFormSubmit = (values: UserFormValues) => {
    if (editUser) {
      const { password, ...rest } = values;
      doUpdate(
        { id: editUser.user_id, form: rest as Partial<UserFormData> },
        { onSuccess: () => { setFormOpen(false); setEditUser(null); } }
      );
    } else {
      if (!values.password) {
        form.setError("password", { message: "Password wajib diisi untuk user baru" });
        return;
      }
      doCreate(
        values as UserFormData & { password: string },
        { onSuccess: () => { setFormOpen(false); } }
      );
    }
  };

  // ── Reset Password Form ───────────────────────────────────────────────────────
  const resetPwForm = useForm<ResetPasswordValues>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: { password: "", confirmPassword: "" },
  });

  useEffect(() => {
    if (!resetPwTarget) resetPwForm.reset();
  }, [resetPwTarget, resetPwForm]);

  const handleResetPwSubmit = (values: ResetPasswordValues) => {
    if (!resetPwTarget) return;
    doResetPw(
      { id: resetPwTarget.user_id, password: values.password },
      { onSuccess: () => setResetPwTarget(null) }
    );
  };

  if (!isAdmin) {
    return (
      <div className="flex flex-col items-center justify-center h-64 gap-3 text-center">
        <Shield className="h-12 w-12 text-muted-foreground/40" />
        <p className="text-muted-foreground">Anda tidak memiliki akses ke halaman ini.</p>
      </div>
    );
  }

  return (
    <div className="space-y-5">
      {/* ── Header ── */}
      <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
            <Users className="h-4 w-4 text-primary" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">User Management</h1>
            <p className="text-xs text-muted-foreground">{data?.total ?? 0} users terdaftar</p>
          </div>
        </div>

        <Button size="sm" className="gap-2" onClick={() => { setEditUser(null); setFormOpen(true); }} id="btn-add-user">
          <Plus className="h-4 w-4" />
          Add User
        </Button>
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
        searchPlaceholder="Search nama, email..."
        emptyMessage="Tidak ada user ditemukan."
        toolbar={
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="h-9 w-36 text-xs" id="select-user-status">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {STATUS_FILTER_OPTIONS.map((opt) => (
                <SelectItem key={opt.value} value={opt.value} className="text-xs">
                  {opt.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        }
      />

      {/* ── User Form Sheet ── */}
      <Sheet open={formOpen} onOpenChange={(o) => { setFormOpen(o); if (!o) setEditUser(null); }}>
        <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
          <SheetHeader className="mb-6">
            <SheetTitle>{editUser ? "Edit User" : "Add User Baru"}</SheetTitle>
            <SheetDescription>
              {editUser ? `Mengedit: ${editUser.first_name} ${editUser.last_name}` : "Isi form untuk membuat user baru."}
            </SheetDescription>
          </SheetHeader>

          <Form {...form}>
            <form onSubmit={form.handleSubmit(handleFormSubmit)} className="space-y-4" noValidate>
              <div className="grid grid-cols-2 gap-4">
                <FormField control={form.control} name="first_name" render={({ field }) => (
                  <FormItem>
                    <FormLabel>First Name <span className="text-destructive">*</span></FormLabel>
                    <FormControl><Input {...field} id="input-user-fname" /></FormControl>
                    <FormMessage />
                  </FormItem>
                )} />
                <FormField control={form.control} name="last_name" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Last Name <span className="text-destructive">*</span></FormLabel>
                    <FormControl><Input {...field} id="input-user-lname" /></FormControl>
                    <FormMessage />
                  </FormItem>
                )} />
              </div>

              <FormField control={form.control} name="email_address" render={({ field }) => (
                <FormItem>
                  <FormLabel>Email <span className="text-destructive">*</span></FormLabel>
                  <FormControl><Input {...field} type="email" id="input-user-email" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              <FormField control={form.control} name="mobile_number" render={({ field }) => (
                <FormItem>
                  <FormLabel>Mobile Number</FormLabel>
                  <FormControl><Input {...field} placeholder="+62..." id="input-user-mobile" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />

              {!editUser && (
                <FormField control={form.control} name="password" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Password <span className="text-destructive">*</span></FormLabel>
                    <FormControl><Input {...field} type="password" placeholder="Min 6 karakter" id="input-user-pw" /></FormControl>
                    <FormMessage />
                  </FormItem>
                )} />
              )}

              <div className="grid grid-cols-2 gap-4">
                <FormField control={form.control} name="type" render={({ field }) => (
                  <FormItem>
                    <FormLabel>User Type</FormLabel>
                    <Select value={String(field.value)} onValueChange={(v) => field.onChange(Number(v))}>
                      <FormControl>
                        <SelectTrigger id="select-user-type"><SelectValue /></SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {Object.entries(TYPE_LABELS).map(([k, v]) => (
                          <SelectItem key={k} value={k}>{v}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )} />

                <FormField control={form.control} name="module" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Module Access</FormLabel>
                    <Select value={String(field.value)} onValueChange={(v) => field.onChange(Number(v))}>
                      <FormControl>
                        <SelectTrigger id="select-user-module"><SelectValue /></SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {Object.entries(MODULE_LABELS).map(([k, v]) => (
                          <SelectItem key={k} value={k}>{v}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )} />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <FormField control={form.control} name="status" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Status</FormLabel>
                    <Select value={field.value} onValueChange={field.onChange}>
                      <FormControl>
                        <SelectTrigger id="select-user-status-form"><SelectValue /></SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )} />

                <FormField control={form.control} name="admin" render={({ field }) => (
                  <FormItem>
                    <FormLabel>Admin Role</FormLabel>
                    <Select value={String(field.value)} onValueChange={(v) => field.onChange(Number(v))}>
                      <FormControl>
                        <SelectTrigger id="select-user-admin"><SelectValue /></SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="0">Regular User</SelectItem>
                        <SelectItem value="1">Administrator</SelectItem>
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )} />
              </div>

              <div className="flex gap-2 pt-2">
                <Button type="submit" disabled={isCreating || isUpdating} className="flex-1" id="btn-user-submit">
                  {(isCreating || isUpdating) ? "Menyimpan..." : editUser ? "Simpan Perubahan" : "Buat User"}
                </Button>
                <Button type="button" variant="outline" onClick={() => setFormOpen(false)}>Batal</Button>
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
              Reset Password
            </AlertDialogTitle>
            <AlertDialogDescription>
              Reset password untuk <span className="font-semibold text-foreground">{resetPwTarget?.first_name} {resetPwTarget?.last_name}</span>.
            </AlertDialogDescription>
          </AlertDialogHeader>

          <Form {...resetPwForm}>
            <form onSubmit={resetPwForm.handleSubmit(handleResetPwSubmit)} className="space-y-3">
              <FormField control={resetPwForm.control} name="password" render={({ field }) => (
                <FormItem>
                  <FormLabel>Password Baru</FormLabel>
                  <FormControl><Input {...field} type="password" placeholder="Min 6 karakter" id="input-reset-pw" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <FormField control={resetPwForm.control} name="confirmPassword" render={({ field }) => (
                <FormItem>
                  <FormLabel>Konfirmasi Password</FormLabel>
                  <FormControl><Input {...field} type="password" placeholder="Ulangi password" id="input-reset-pw-confirm" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <AlertDialogFooter className="pt-2">
                <AlertDialogCancel type="button" disabled={isResettingPw}>Batal</AlertDialogCancel>
                <Button type="submit" disabled={isResettingPw} id="btn-confirm-reset-pw">
                  {isResettingPw ? "Mereset..." : "Reset Password"}
                </Button>
              </AlertDialogFooter>
            </form>
          </Form>
        </AlertDialogContent>
      </AlertDialog>

      {/* ── Delete Dialog ── */}
      <AlertDialog open={!!deleteTarget} onOpenChange={(o) => !o && setDeleteTarget(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus User?</AlertDialogTitle>
            <AlertDialogDescription>
              Anda akan menghapus user{" "}
              <span className="font-semibold text-foreground">{deleteTarget?.first_name} {deleteTarget?.last_name}</span>.
              Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => {
                if (deleteTarget) doDelete(deleteTarget.user_id, { onSuccess: () => setDeleteTarget(null) });
              }}
              disabled={isDeleting}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              id="btn-confirm-delete-user"
            >
              {isDeleting ? "Menghapus..." : "Hapus User"}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
