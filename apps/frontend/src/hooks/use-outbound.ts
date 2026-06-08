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
  return useQuery({
    queryKey: outboundKeys.list(params),
    queryFn: () => fetchOutboundList(params),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateOutbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: OutboundFormData) => createOutbound(form),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Outbound berhasil dibuat.");
        qc.invalidateQueries({ queryKey: outboundKeys.all });
      } else toast.error(res.msg || "Gagal membuat outbound.");
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}

export function useUpdateOutbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<OutboundFormData> }) =>
      updateOutbound(id, form),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Outbound berhasil diperbarui.");
        qc.invalidateQueries({ queryKey: outboundKeys.all });
      } else toast.error(res.msg || "Gagal memperbarui outbound.");
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}

export function useDeleteOutbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteOutbound(id),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Outbound berhasil dihapus.");
        qc.invalidateQueries({ queryKey: outboundKeys.all });
      } else toast.error(res.msg || "Gagal menghapus outbound.");
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}
