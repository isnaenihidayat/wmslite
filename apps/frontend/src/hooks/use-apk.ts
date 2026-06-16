import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchApkList,
  createApkAccount,
  updateApkAccount,
  resetApkPassword,
  logoutApkAccount,
  deleteApkAccount,
  type ApkListParams,
  type ApkFormData,
} from "@/lib/api/apk.service";
import { toast } from "sonner";

export const apkKeys = {
  all:  ["apk"] as const,
  list: (params: ApkListParams) => ["apk", "list", params] as const,
};

export function useApkList(params: ApkListParams) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: apkKeys.list(params),
    queryFn:  () => fetchApkList(laravelParams),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateApkAccount() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: ApkFormData) => createApkAccount(form),
    onSuccess: () => {
      toast.success("APK account berhasil dibuat.");
      qc.invalidateQueries({ queryKey: apkKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }) => {
      const errors = err?.response?.data?.errors;
      const msg = errors
        ? Object.values(errors).flat()[0]
        : err?.response?.data?.message ?? "Gagal membuat APK account.";
      toast.error(msg);
    },
  });
}

export function useUpdateApkAccount() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: { name?: string; username?: string; table?: "main" | "staging" } }) =>
      updateApkAccount(id, form),
    onSuccess: () => {
      toast.success("APK account berhasil diperbarui.");
      qc.invalidateQueries({ queryKey: apkKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message ?? "Gagal memperbarui APK account.");
    },
  });
}

export function useResetApkPassword() {
  return useMutation({
    mutationFn: ({ id, password, table }: { id: number; password: string; table: "main" | "staging" }) =>
      resetApkPassword(id, password, table),
    onSuccess: (data) => {
      toast.success(data.message || "Password berhasil direset.");
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message ?? "Gagal reset password.");
    },
  });
}

export function useLogoutApkAccount() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, table }: { id: number; table: "main" | "staging" }) =>
      logoutApkAccount(id, table),
    onSuccess: (data) => {
      toast.success(data.message || "Session berhasil di-logout.");
      qc.invalidateQueries({ queryKey: apkKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message ?? "Gagal logout session.");
    },
  });
}

export function useDeleteApkAccount() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, table }: { id: number; table: "main" | "staging" }) =>
      deleteApkAccount(id, table),
    onSuccess: () => {
      toast.success("APK account berhasil dihapus.");
      qc.invalidateQueries({ queryKey: apkKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message ?? "Gagal menghapus APK account.");
    },
  });
}
