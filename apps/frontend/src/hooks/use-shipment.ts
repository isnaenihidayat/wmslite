import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchShipmentList,
  createShipment,
  updateShipment,
  deleteShipment,
  pushToInbound,
  type ShipmentListParams,
  type ShipmentFormData,
} from "@/lib/api/shipment.service";
import { inboundKeys } from "@/hooks/use-inbound";
import { toast } from "sonner";

export const shipmentKeys = {
  all: ["shipments"] as const,
  list: (params: ShipmentListParams) => ["shipments", "list", params] as const,
};

export function useShipmentList(params: ShipmentListParams, token: string | undefined) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: shipmentKeys.list(params),
    queryFn: () => fetchShipmentList(laravelParams, token as string),
    enabled: !!token,
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateShipment(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: ShipmentFormData) => createShipment(form, token as string),
    onSuccess: () => {
      toast.success("Shipment berhasil ditambahkan.");
      qc.invalidateQueries({ queryKey: shipmentKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal menambahkan shipment.";
      toast.error(msg);
    },
  });
}

export function useUpdateShipment(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<ShipmentFormData> }) =>
      updateShipment(id, form, token as string),
    onSuccess: () => {
      toast.success("Shipment berhasil diperbarui.");
      qc.invalidateQueries({ queryKey: shipmentKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal memperbarui shipment.";
      toast.error(msg);
    },
  });
}

export function useDeleteShipment(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteShipment(id, token as string),
    onSuccess: () => {
      toast.success("Shipment berhasil dihapus.");
      qc.invalidateQueries({ queryKey: shipmentKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      const msg = err?.response?.data?.message || "Gagal menghapus shipment.";
      toast.error(msg);
    },
  });
}

export function usePushToInbound(token: string | undefined) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => pushToInbound(id, token as string),
    onSuccess: (data) => {
      toast.success(data.message, {
        description: `Inbound ID: ${data.inbound_id}`,
        duration: 5000,
      });
      // Invalidate both shipments and inbounds list
      qc.invalidateQueries({ queryKey: shipmentKeys.all });
      qc.invalidateQueries({ queryKey: inboundKeys.all });
    },
    onError: (err: { response?: { data?: { message?: string; inbound_id?: number } } }) => {
      const msg = err?.response?.data?.message || "Gagal push ke Inbound.";
      const inboundId = err?.response?.data?.inbound_id;
      toast.warning(msg, {
        description: inboundId ? `Lihat Inbound ID: ${inboundId}` : undefined,
        duration: 6000,
      });
    },
  });
}
