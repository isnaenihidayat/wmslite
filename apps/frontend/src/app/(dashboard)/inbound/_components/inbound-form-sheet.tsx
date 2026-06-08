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
import type { InboundHeader } from "@/types";
import type { InboundFormData } from "@/lib/api/inbound.service";

const inboundSchema = z.object({
  hawb: z.string().min(1, "HAWB wajib diisi").max(100),
  hawb_descr: z.string().optional(),
  delivery_id: z.string().optional(),
  modality: z.string().optional(),
  po: z.string().optional(),
  qty: z.number().nonnegative().optional(),
  locator: z.string().optional(),
  etd: z.string().optional(),
  eta: z.string().optional(),
  ata: z.string().optional(),
  sppb_date: z.string().optional(),
  warehouse: z.string().min(1, "Warehouse wajib dipilih"),
});

type InboundSchema = z.infer<typeof inboundSchema>;

const MODALITY_OPTIONS = ["Sea", "Air", "Land", "Rail"];
const WAREHOUSE_OPTIONS = ["arcadia", "cengkareng", "surabaya", "default"];

interface InboundFormSheetProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  inbound?: InboundHeader | null;
  onSubmit: (data: InboundFormData, id?: number) => void;
  isSubmitting?: boolean;
}

export function InboundFormSheet({
  open,
  onOpenChange,
  inbound,
  onSubmit,
  isSubmitting,
}: InboundFormSheetProps) {
  const isEdit = !!inbound;

  const form = useForm<InboundSchema>({
    resolver: zodResolver(inboundSchema),
    defaultValues: {
      hawb: "",
      hawb_descr: "",
      delivery_id: "",
      modality: "",
      po: "",
      qty: undefined,
      locator: "",
      etd: "",
      eta: "",
      ata: "",
      sppb_date: "",
      warehouse: "default",
    },
  });

  useEffect(() => {
    if (inbound) {
      form.reset({
        hawb: inbound.hawb,
        hawb_descr: inbound.descr,
        delivery_id: inbound.delivery_id?.toString() || "",
        modality: inbound.modality || "",
        po: inbound.po || "",
        locator: inbound.locator || "",
        etd: inbound.etd?.split(" ")[0] || "",
        eta: inbound.eta?.split(" ")[0] || "",
        ata: inbound.ata?.split(" ")[0] || "",
        sppb_date: inbound.sppb_date?.split(" ")[0] || "",
        warehouse: "default",
      });
    } else {
      form.reset();
    }
  }, [inbound, form]);

  const handleSubmit = (values: InboundSchema) => {
    const data: InboundFormData = {
      hawb: values.hawb,
      hawb_descr: values.hawb_descr || "",
      delivery_id_in: values.delivery_id || "",
      modality_in: values.modality || "",
      po_number: values.po || "",
      qty: values.qty,
      locator_number: values.locator || "",
      etd: values.etd || "",
      eta: values.eta || "",
      ata: values.ata || "",
      sppb_date: values.sppb_date || "",
      warehouse_in: values.warehouse,
    };
    onSubmit(data, inbound?.id);
  };

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="w-full sm:max-w-xl overflow-y-auto">
        <SheetHeader className="mb-6">
          <SheetTitle>{isEdit ? "Edit Inbound" : "Tambah Inbound Baru"}</SheetTitle>
          <SheetDescription>
            {isEdit
              ? `Mengedit HAWB: ${inbound?.hawb}`
              : "Isi form untuk membuat inbound baru."}
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
                    <Input {...field} placeholder="e.g. 1234567890" disabled={isEdit} id="input-inb-hawb" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <FormField
              control={form.control}
              name="hawb_descr"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Description</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="e.g. Electronic Parts" id="input-inb-descr" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="delivery_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Delivery ID</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="SSO Delivery ID" id="input-inb-delivery" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="po"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>PO Number</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="PO-XXXX" id="input-inb-po" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="modality"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Modality</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value}>
                      <FormControl>
                        <SelectTrigger id="select-inb-modality">
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
                        onChange={(e) => field.onChange(e.target.value === "" ? undefined : Number(e.target.value))}
                        id="input-inb-qty"
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            <FormField
              control={form.control}
              name="locator"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Oracle Locator</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="e.g. WH-A01-01" id="input-inb-locator" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              <FormField control={form.control} name="etd" render={({ field }) => (
                <FormItem>
                  <FormLabel>ETD</FormLabel>
                  <FormControl><Input {...field} type="date" id="input-inb-etd" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <FormField control={form.control} name="eta" render={({ field }) => (
                <FormItem>
                  <FormLabel>ETA</FormLabel>
                  <FormControl><Input {...field} type="date" id="input-inb-eta" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <FormField control={form.control} name="ata" render={({ field }) => (
                <FormItem>
                  <FormLabel>ATA</FormLabel>
                  <FormControl><Input {...field} type="date" id="input-inb-ata" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
              <FormField control={form.control} name="sppb_date" render={({ field }) => (
                <FormItem>
                  <FormLabel>SPPB Date</FormLabel>
                  <FormControl><Input {...field} type="date" id="input-inb-sppb" /></FormControl>
                  <FormMessage />
                </FormItem>
              )} />
            </div>

            <FormField
              control={form.control}
              name="warehouse"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Warehouse <span className="text-destructive">*</span></FormLabel>
                  <Select onValueChange={field.onChange} value={field.value}>
                    <FormControl>
                      <SelectTrigger id="select-inb-warehouse">
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

            <div className="flex gap-2 pt-2">
              <Button type="submit" disabled={isSubmitting} className="flex-1" id="btn-inb-submit">
                {isSubmitting ? (
                  <><Loader2 className="mr-2 h-4 w-4 animate-spin" />Menyimpan...</>
                ) : isEdit ? "Simpan Perubahan" : "Tambah Inbound"}
              </Button>
              <Button type="button" variant="outline" onClick={() => onOpenChange(false)} id="btn-inb-cancel">
                Batal
              </Button>
            </div>
          </form>
        </Form>
      </SheetContent>
    </Sheet>
  );
}
