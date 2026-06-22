"use client";

import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { useQuery } from "@tanstack/react-query";
import { useSession } from "next-auth/react";
import { fetchCategories } from "@/lib/api/master.service";
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
import type { Outbound as OutboundHeader } from "@/lib/api/outbound.service";
import type { OutboundFormData } from "@/lib/api/outbound.service";

const STATUS_OPTIONS = [
  { value: "inprogress",           label: "In Progress" },
  { value: "created",              label: "Created" },
  { value: "started",              label: "Started" },
  { value: "Warehouse in Transit", label: "WH in Transit" },
  { value: "successful",           label: "Successful" },
  { value: "failed",               label: "Failed" },
  { value: "cancelled",            label: "Cancelled" },
];

const outboundSchema = z.object({
  destination:         z.string().min(1, "Destination wajib diisi"),
  product_category_id: z.number().nullable().optional(),
  po:                  z.string().optional(),
  transporter:         z.string().optional(),
  qty:                 z.string().optional(),
  delivery_id:         z.string().optional(),
  status:              z.string().optional(),
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
  const { data: session } = useSession();
  const token = session?.user?.accessToken;

  const form = useForm<OutboundSchema>({
    resolver: zodResolver(outboundSchema),
    defaultValues: {
      destination: "",
      po: "",
      transporter: "",
      qty: "",
      delivery_id: "",
      status: "inprogress",
    },
  });

  const { data: categories = [] } = useQuery({
    queryKey: ["master", "categories"],
    queryFn: () => fetchCategories(token as string),
    enabled: !!token,
    staleTime: 5 * 60_000,
  });

  useEffect(() => {
    if (open) {
      if (outbound) {
        form.reset({
          destination:         outbound.destination || "",
          product_category_id: outbound.product_category_id ?? null,
          po:                  outbound.po || "",
          transporter:         outbound.transporter || "",
          qty:                 outbound.qty || "",
          delivery_id:         outbound.delivery_id?.toString() || "",
          status:              outbound.status || "inprogress",
        });
      } else {
        form.reset({ destination: "", product_category_id: null, po: "", transporter: "", qty: "", delivery_id: "", status: "inprogress" });
      }
    }
  }, [open, outbound, form]);

  const handleSubmit = (values: OutboundSchema) => {
    const data: OutboundFormData = {
      destination:         values.destination,
      product_category_id: values.product_category_id ?? null,
      po:                  values.po || undefined,
      transporter:         values.transporter || undefined,
      qty:                 values.qty || undefined,
      delivery_id:         values.delivery_id ? Number(values.delivery_id) : undefined,
      status:              values.status || "inprogress",
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
            {/* Destination */}
            <FormField
              control={form.control}
              name="destination"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Destination <span className="text-destructive">*</span></FormLabel>
                  <FormControl>
                    <Input {...field} placeholder="e.g. Jakarta Pusat" id="input-out-dest" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            <div className="grid grid-cols-2 gap-4">
              {/* PO Number */}
              <FormField
                control={form.control}
                name="po"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>GON / PO Number</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="GON-XXXX" disabled={isEdit} id="input-out-po" />
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
                      <Input {...field} placeholder="e.g. JNE" id="input-out-transporter" />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            {/* Category + Delivery ID */}
            <div className="grid grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="product_category_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Category</FormLabel>
                    <Select
                      value={field.value?.toString() ?? ""}
                      onValueChange={(v) => field.onChange(v ? Number(v) : null)}
                    >
                      <FormControl>
                        <SelectTrigger id="select-out-category">
                          <SelectValue placeholder="Pilih kategori..." />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="">— Tanpa kategori —</SelectItem>
                        {categories.map((c) => (
                          <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <FormField
                control={form.control}
                name="delivery_id"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Delivery ID</FormLabel>
                    <FormControl>
                      <Input {...field} placeholder="SSO Delivery ID" id="input-out-delivery" />
                    </FormControl>
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
                    <Input {...field} placeholder="0" id="input-out-qty" />
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

            {isEdit && (
              <FormField
                control={form.control}
                name="status"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Status</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value ?? "inprogress"}>
                      <FormControl>
                        <SelectTrigger id="select-out-status">
                          <SelectValue />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {STATUS_OPTIONS.map((s) => (
                          <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
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
