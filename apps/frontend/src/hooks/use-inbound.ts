import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchInboundList,
  createInbound,
  updateInbound,
  deleteInbound,
  type InboundListParams,
  type InboundFormData,
} from "@/lib/api/inbound.service";
import { toast } from "sonner";

export const inboundKeys = {
  all: ["inbounds"] as const,
  list: (params: InboundListParams) => ["inbounds", "list", params] as const,
};

export function useInboundList(params: InboundListParams) {
  return useQuery({
    queryKey: inboundKeys.list(params),
    queryFn: () => fetchInboundList(params),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateInbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: InboundFormData) => createInbound(form),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Inbound berhasil ditambahkan.");
        qc.invalidateQueries({ queryKey: inboundKeys.all });
      } else toast.error(res.msg || "Gagal menambahkan inbound.");
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}

export function useUpdateInbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<InboundFormData> }) =>
      updateInbound(id, form),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Inbound berhasil diperbarui.");
        qc.invalidateQueries({ queryKey: inboundKeys.all });
      } else toast.error(res.msg || "Gagal memperbarui inbound.");
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}

export function useDeleteInbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ hawb, deliveryId }: { hawb: string; deliveryId: string }) =>
      deleteInbound(hawb, deliveryId),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Inbound berhasil dihapus.");
        qc.invalidateQueries({ queryKey: inboundKeys.all });
      } else toast.error(res.msg || "Gagal menghapus inbound.");
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}
