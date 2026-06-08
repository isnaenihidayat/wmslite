"use client";

import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Loader2 } from "lucide-react";
import type { Shipment, ShipmentFormData } from "@/types";

// ── Validation schema ─────────────────────────────────────────────────────────
const shipmentSchema = z.object({
  hawb: z.string().min(1, "HAWB wajib diisi").max(100),
  hawb_descr: z.string().optional(),
  delivery_id: z.string().optional(),
  modality: z.string().optional(),
  po: z.string().optional(),
  qty: z.number().nonnegative().optional(),
  ship_method: z.string().optional(),
  etd: z.string().optional(),
  eta: z.string().optional(),
  ata: z.string().optional(),
  sppb_date: z.string().optional(),
  warehouse: z.string().min(1, "Warehouse wajib dipilih"),
});

type ShipmentSchema = z.infer<typeof shipmentSchema>;

const MODALITY_OPTIONS = ["Sea", "Air", "Land", "Rail"];
const SHIP_METHOD_OPTIONS = ["FCL", "LCL", "Express", "Courier", "Road Truck"];
const WAREHOUSE_OPTIONS = ["arcadia", "cengkareng", "surabaya", "default"];

// ── Props ─────────────────────────────────────────────────────────────────────
interface ShipmentFormSheetProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  shipment?: Shipment | null;    // null = create, defined = edit
  onSubmit: (data: ShipmentFormData, id?: number) => void;
  isSubmitting?: boolean;
}

export function ShipmentFormSheet({
  open,
  onOpenChange,
  shipment,
  onSubmit,
  isSubmitting,
}: ShipmentFormSheetProps) {
  const isEdit = !!shipment;

  const form = useForm<ShipmentSchema>({
    resolver: zodResolver(shipmentSchema),
    defaultValues: {
      hawb: "",
      hawb_descr: "",
      delivery_id: "",
      modality: "",
      po: "",
      qty: undefined,
      ship_method: "",
      etd: "",
      eta: "",
      ata: "",
      sppb_date: "",
      warehouse: "default",
    },
  });

  // Populate on edit
  useEffect(() => {
    if (shipment) {
      form.reset({
        hawb: shipment.hawb,
        hawb_descr: shipment.descr,
        delivery_id: shipment.delivery_id,
        modality: shipment.modality,
        po: shipment.po,
        qty: shipment.qty,
        ship_method: shipment.ship_method,
        etd: shipment.etd?.split(" ")[0] || "",
        eta: shipment.eta?.split(" ")[0] || "",
        ata: shipment.ata?.split(" ")[0] || "",
        sppb_date: shipment.sppb_date?.split(" ")[0] || "",
        warehouse: "default",
      });
    } else {
      form.reset();
    }
  }, [shipment, form]);

  const handleSubmit = (values: ShipmentSchema) => {
    const data: ShipmentFormData = {
      hawb: values.hawb,
      hawb_descr: values.hawb_descr || "",
      delivery_id_in: values.delivery_id || "",
      modality_in: values.modality || "",
      po_number: values.po || "",
      qty: values.qty || 0,
      ship_method: values.ship_method || "",
      etd: values.etd || "",
      eta: values.eta || "",
      ata: values.ata || "",
      sppb_date: values.sppb_date || "",
      warehouse_in: values.warehouse,
    };
    onSubmit(data, shipment?.id);
  };

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="w-full sm:max-w-xl overflow-y-auto">
        <SheetHeader className="mb-6">
          <SheetTitle>{isEdit ? "Edit Shipment" : "Tambah Shipment Baru"}</SheetTitle>
          <SheetDescription>
            {isEdit
              ? `Mengedit HAWB: ${shipment?.hawb}`
              : "Isi form berikut untuk membuat shipment baru."}
          </SheetDescription>
        </SheetHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(handleSubmit)} className="space-y-4" noValidate>
            {/* HAWB */}
            <FormField
              control={form.control}
              name="hawb"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>HAWB <span className="text-destructive">*</span></FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      placeholder="e.g. 1234567890"
                      disabled={isEdit}
                      id="input-hawb"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Description */}
            <FormField
              control={form.control}
              name="hawb_descr"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Description</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="e.g. Electronic Parts" id="input-descr" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              {/* Delivery ID */}
              <FormField
                control={form.control}
                name="delivery_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Delivery ID</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="SSO Delivery ID" id="input-delivery-id" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              {/* PO Number */}
              <FormField
                control={form.control}
                name="po"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>PO Number</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="PO-XXXX" id="input-po" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              {/* Modality */}
              <FormField
                control={form.control}
                name="modality"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Modality</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value}>
                      <FormControl>
                        <SelectTrigger id="select-modality">
                          <SelectValue placeholder="Select..." />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {MODALITY_OPTIONS.map((o) => (
                          <SelectItem key={o} value={o}>{o}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />

              {/* Ship Method */}
              <FormField
                control={form.control}
                name="ship_method"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Ship Method</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value}>
                      <FormControl>
                        <SelectTrigger id="select-ship-method">
                          <SelectValue placeholder="Select..." />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {SHIP_METHOD_OPTIONS.map((o) => (
                          <SelectItem key={o} value={o}>{o}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            {/* Qty */}
            <FormField
              control={form.control}
              name="qty"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Quantity</FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      type="number"
                      min={0}
                      placeholder="0"
                      id="input-qty"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Date fields */}
            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="etd"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>ETD</FormLabel>
                    <FormControl>
                      <Input {...field} type="date" id="input-etd" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="eta"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>ETA</FormLabel>
                    <FormControl>
                      <Input {...field} type="date" id="input-eta" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="ata"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>ATA</FormLabel>
                    <FormControl>
                      <Input {...field} type="date" id="input-ata" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="sppb_date"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>SPPB Date</FormLabel>
                    <FormControl>
                      <Input {...field} type="date" id="input-sppb" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            {/* Warehouse */}
            <FormField
              control={form.control}
              name="warehouse"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Warehouse <span className="text-destructive">*</span></FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger id="select-warehouse">
                        <SelectValue placeholder="Select warehouse..." />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      {WAREHOUSE_OPTIONS.map((o) => (
                        <SelectItem key={o} value={o} className="capitalize">{o}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Buttons */}
            <div className="flex gap-2 pt-2">
              <Button
                type="submit"
                disabled={isSubmitting}
                className="flex-1"
                id="btn-shipment-submit"
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Menyimpan...
                  </>
                ) : isEdit ? (
                  "Simpan Perubahan"
                ) : (
                  "Tambah Shipment"
                )}
              </Button>
              <Button
                type="button"
                variant="outline"
                onClick={() => onOpenChange(false)}
                id="btn-shipment-cancel"
              >
                Batal
              </Button>
            </div>
          </form>
        </Form>
      </SheetContent>
    </Sheet>
  );
}
