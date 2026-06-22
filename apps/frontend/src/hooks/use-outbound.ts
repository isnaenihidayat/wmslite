import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchOutboundList,
  fetchOutboundWithDetails,
  createOutbound,
  updateOutbound,
  deleteOutbound,
  type OutboundListParams,
  type OutboundFormData,
} from "@/lib/api/outbound.service";
import { toast } from "sonner";

export const outboundKeys = {
  all:     ["outbounds"] as const,
  list:    (params: OutboundListParams) => ["outbounds", "list", params] as const,
  detail:  (id: number) => ["outbounds", "detail", id] as const,
};

export function useOutboundDetails(id: number | null, token: string | undefined) {
  return useQuery({
    queryKey: outboundKeys.detail(id ?? 0),
    queryFn:  () => fetchOutboundWithDetails(id!, token as string),
    enabled:  id !== null && id > 0 && !!token,
    staleTime: 60_000,
  });
}

export function useOutboundList(params: OutboundListParams, token: string | undefined) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: outboundKeys.list(params),
    queryFn: () => fetchOutboundList(laravelParams, token as string),
    enabled: !!token,
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateOutbound(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: OutboundFormData) => createOutbound(form, token as string),
    onSuccess: () => {
      toast.success("Outbound berhasil dibuat.");
      qc.invalidateQueries({ queryKey: outboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal membuat outbound.";
      toast.error(msg);
    },
  });
}

export function useUpdateOutbound(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<OutboundFormData> }) =>
      updateOutbound(id, form, token as string),
    onSuccess: () => {
      toast.success("Outbound berhasil diperbarui.");
      qc.invalidateQueries({ queryKey: outboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal memperbarui outbound.";
      toast.error(msg);
    },
  });
}

export function useDeleteOutbound(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteOutbound(id, token as string),
    onSuccess: () => {
      toast.success("Outbound berhasil dihapus.");
      qc.invalidateQueries({ queryKey: outboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal menghapus outbound.";
      toast.error(msg);
    },
  });
}
