import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  fetchShipmentList,
  createShipment,
  updateShipment,
  deleteShipment,
  type ShipmentListParams,
} from "@/lib/api/shipment.service";
import type { ShipmentFormData } from "@/types";
import { toast } from "sonner";

// ── Query keys ────────────────────────────────────────────────────────────────
export const shipmentKeys = {
  all: ["shipments"] as const,
  list: (params: ShipmentListParams) => ["shipments", "list", params] as const,
};

// ── Hooks ─────────────────────────────────────────────────────────────────────

export function useShipmentList(params: ShipmentListParams) {
  return useQuery({
    queryKey: shipmentKeys.list(params),
    queryFn: () => fetchShipmentList(params),
    staleTime: 30_000,
    placeholderData: (prev) => prev, // keep previous data while loading
  });
}

export function useCreateShipment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (form: ShipmentFormData) => createShipment(form),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Shipment berhasil ditambahkan.");
        qc.invalidateQueries({ queryKey: shipmentKeys.all });
      } else {
        toast.error(res.msg || "Gagal menambahkan shipment.");
      }
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}

export function useUpdateShipment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, form }: { id: number; form: Partial<ShipmentFormData> }) =>
      updateShipment(id, form),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Shipment berhasil diperbarui.");
        qc.invalidateQueries({ queryKey: shipmentKeys.all });
      } else {
        toast.error(res.msg || "Gagal memperbarui shipment.");
      }
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}

export function useDeleteShipment() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ hawb, deliveryId }: { hawb: string; deliveryId: string }) =>
      deleteShipment(hawb, deliveryId),
    onSuccess: (res) => {
      if (res.code === 1) {
        toast.success("Shipment berhasil dihapus.");
        qc.invalidateQueries({ queryKey: shipmentKeys.all });
      } else {
        toast.error(res.msg || "Gagal menghapus shipment.");
      }
    },
    onError: () => toast.error("Terjadi kesalahan. Coba lagi."),
  });
}
