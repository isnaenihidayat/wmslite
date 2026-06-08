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
import { Loader2 } from "lucide-react";
import type { OutboundHeader } from "@/types";
import type { OutboundFormData } from "@/lib/api/outbound.service";

const outboundSchema = z.object({
  po_number_o: z.string().min(1, "GON / PO Number wajib diisi"),
  destination: z.string().optional(),
  delivery_id: z.string().optional(),
  transporter: z.string().optional(),
  checker: z.string().optional(),
});

type OutboundSchema = z.infer<typeof outboundSchema>;

interface OutboundFormSheetProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  outbound?: OutboundHeader | null;
  onSubmit: (data: OutboundFormData, id?: number) => void;
  isSubmitting?: boolean;
}

export function OutboundFormSheet({
  open,
  onOpenChange,
  outbound,
  onSubmit,
  isSubmitting,
}: OutboundFormSheetProps) {
  const isEdit = !!outbound;

  const form = useForm<OutboundSchema>({
    resolver: zodResolver(outboundSchema),
    defaultValues: {
      po_number_o: "",
      destination: "",
      delivery_id: "",
      transporter: "",
      checker: "",
    },
  });

  useEffect(() => {
    if (outbound) {
      form.reset({
        po_number_o: outbound.po,
        destination: outbound.destination || "",
        delivery_id: outbound.delivery_id?.toString() || "",
        transporter: outbound.transporter || "",
        checker: outbound.checker || "",
      });
    } else {
      form.reset();
    }
  }, [outbound, form]);

  const handleSubmit = (values: OutboundSchema) => {
    const data: OutboundFormData = {
      po_number_o: values.po_number_o,
      destination: values.destination || "",
      delivery_id: values.delivery_id || "",
      transporter: values.transporter || "",
      checker: values.checker || "",
    };
    onSubmit(data, outbound?.id);
  };

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
        <SheetHeader className="mb-6">
          <SheetTitle>{isEdit ? "Edit Outbound" : "Tambah Outbound Baru"}</SheetTitle>
          <SheetDescription>
            {isEdit
              ? `Mengedit GON/PO: ${outbound?.po}`
              : "Isi form untuk membuat outbound header baru."}
          </SheetDescription>
        </SheetHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(handleSubmit)} className="space-y-4" noValidate>
            {/* GON / PO */}
            <FormField
              control={form.control}
              name="po_number_o"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>GON / PO Number <span className="text-destructive">*</span></FormLabel>
                  <FormControl>
                    <Input
                      {...field}
                      placeholder="e.g. GON-20240101-001"
                      disabled={isEdit}
                      id="input-out-po"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {/* Destination */}
            <FormField
              control={form.control}
              name="destination"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Destination</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="e.g. Jakarta Pusat" id="input-out-dest" />
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
                    <FormLabel>PSO Delivery ID</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="PSO ID" id="input-out-delivery" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              {/* Transporter */}
              <FormField
                control={form.control}
                name="transporter"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Transporter</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="e.g. JNE, TIKI" id="input-out-transporter" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            {/* Checker */}
            <FormField
              control={form.control}
              name="checker"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Checker</FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="Nama checker" id="input-out-checker" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {!isEdit && (
              <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                <p className="text-xs text-amber-700 dark:text-amber-400">
                  ⚠ Untuk menambahkan item detail (koli), gunakan fitur <strong>Push Outbound</strong> dari halaman Shipment setelah outbound header dibuat.
                </p>
              </div>
            )}

            <div className="flex gap-2 pt-2">
              <Button type="submit" disabled={isSubmitting} className="flex-1" id="btn-out-submit">
                {isSubmitting ? (
                  <><Loader2 className="mr-2 h-4 w-4 animate-spin" />Menyimpan...</>
                ) : isEdit ? "Simpan Perubahan" : "Buat Outbound"}
              </Button>
              <Button type="button" variant="outline" onClick={() => onOpenChange(false)} id="btn-out-cancel">
                Batal
              </Button>
            </div>
          </form>
        </Form>
      </SheetContent>
    </Sheet>
  );
}
