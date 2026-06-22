import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchMovingList,
  createMoving,
  deleteMoving,
  type MovingListParams,
  type MovingFormData,
} from "@/lib/api/moving.service";
import { toast } from "sonner";

export const movingKeys = {
  all:  ["moving"] as const,
  list: (params: MovingListParams) => ["moving", "list", params] as const,
};

export function useMovingList(params: MovingListParams, token: string | undefined) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: movingKeys.list(params),
    queryFn:  () => fetchMovingList(laravelParams, token as string),
    enabled: !!token,
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateMoving(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: MovingFormData) => createMoving(form, token as string),
    onSuccess: () => {
      toast.success("Moving record berhasil dibuat.");
      qc.invalidateQueries({ queryKey: movingKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message || "Gagal membuat moving record.");
    },
  });
}

export function useDeleteMoving(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteMoving(id, token as string),
    onSuccess: () => {
      toast.success("Moving record berhasil dihapus.");
      qc.invalidateQueries({ queryKey: movingKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      toast.error(err?.response?.data?.message || "Gagal menghapus moving record.");
    },
  });
}
