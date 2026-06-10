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
  // Convert 0-indexed page to 1-indexed for Laravel
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: inboundKeys.list(params),
    queryFn: () => fetchInboundList(laravelParams),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateInbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: InboundFormData) => createInbound(form),
    onSuccess: () => {
      toast.success("Inbound berhasil ditambahkan.");
      qc.invalidateQueries({ queryKey: inboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal menambahkan inbound.";
      toast.error(msg);
    },
  });
}

export function useUpdateInbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<InboundFormData> }) =>
      updateInbound(id, form),
    onSuccess: () => {
      toast.success("Inbound berhasil diperbarui.");
      qc.invalidateQueries({ queryKey: inboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal memperbarui inbound.";
      toast.error(msg);
    },
  });
}

export function useDeleteInbound() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteInbound(id),
    onSuccess: () => {
      toast.success("Inbound berhasil dihapus.");
      qc.invalidateQueries({ queryKey: inboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal menghapus inbound.";
      toast.error(msg);
    },
  });
}
