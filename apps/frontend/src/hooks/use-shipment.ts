import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchShipmentList,
  createShipment,
  updateShipment,
  deleteShipment,
  type ShipmentListParams,
  type ShipmentFormData,
} from "@/lib/api/shipment.service";
import { toast } from "sonner";

export const shipmentKeys = {
  all: ["shipments"] as const,
  list: (params: ShipmentListParams) => ["shipments", "list", params] as const,
};

export function useShipmentList(params: ShipmentListParams) {
  const laravelParams = {
    ...params,
    page: (params.page ?? 0) + 1,
    per_page: params.per_page ?? 25,
  };
  return useQuery({
    queryKey: shipmentKeys.list(params),
    queryFn: () => fetchShipmentList(laravelParams),
    staleTime: 30_000,
    placeholderData: (prev) => prev,
  });
}

export function useCreateShipment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: ShipmentFormData) => createShipment(form),
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

export function useUpdateShipment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<ShipmentFormData> }) =>
      updateShipment(id, form),
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

export function useDeleteShipment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => deleteShipment(id),
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
