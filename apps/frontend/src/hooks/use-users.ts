import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchUserList,
  createUser,
  updateUser,
  resetUserPassword,
  deleteUser,
  type UserListParams,
  type UserFormData,
} from "@/lib/api/user.service";
import { toast } from "sonner";

export const userKeys = {
  all:  ["users"] as const,
  list: (params: UserListParams) => ["users", "list", params] as const,
};

export function useUserList(params: UserListParams, token: string | undefined) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: userKeys.list(params),
    queryFn:  () => fetchUserList(laravelParams, token as string),
    enabled: !!token,
    staleTime: 60_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateUser(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: UserFormData & { password: string }) => createUser(form, token as string),
    onSuccess: () => {
      toast.success("User berhasil dibuat.");
      qc.invalidateQueries({ queryKey: userKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }) => {
      const msg = err?.response?.data?.message || "Gagal membuat user.";
      toast.error(msg);
    },
  });
}

export function useUpdateUser(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<UserFormData> }) => updateUser(id, form, token as string),
    onSuccess: () => {
      toast.success("User berhasil diperbarui.");
      qc.invalidateQueries({ queryKey: userKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message || "Gagal memperbarui user.");
    },
  });
}

export function useResetPassword(token: string | undefined) {
  return useMutation({
    mutationFn: ({ id, password }: { id: number; password: string }) => resetUserPassword(id, password, token as string),
    onSuccess: (data) => {
      toast.success(data.message || "Password berhasil direset.");
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message || "Gagal reset password.");
    },
  });
}

export function useDeleteUser(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteUser(id, token as string),
    onSuccess: () => {
      toast.success("User berhasil dihapus.");
      qc.invalidateQueries({ queryKey: userKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message || "Gagal menghapus user.");
    },
  });
}
