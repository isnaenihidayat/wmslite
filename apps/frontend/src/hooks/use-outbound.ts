import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchOutboundList,
  createOutbound,
  updateOutbound,
  deleteOutbound,
  type OutboundListParams,
  type OutboundFormData,
} from "@/lib/api/outbound.service";
import { toast } from "sonner";

export const outboundKeys = {
  all: ["outbounds"] as const,
  list: (params: OutboundListParams) => ["outbounds", "list", params] as const,
};

export function useOutboundList(params: OutboundListParams) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: outboundKeys.list(params),
    queryFn: () => fetchOutboundList(laravelParams),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateOutbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: OutboundFormData) => createOutbound(form),
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

export function useUpdateOutbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<OutboundFormData> }) =>
      updateOutbound(id, form),
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

export function useDeleteOutbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteOutbound(id),
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
