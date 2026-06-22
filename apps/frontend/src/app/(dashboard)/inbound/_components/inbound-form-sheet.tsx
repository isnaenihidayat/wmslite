"use client";

import { useEffect } from "react";
import { useQuery } from "@tanstack/react-query";
import { useSession } from "next-auth/react";
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
import type { Inbound as InboundHeader, InboundFormData } from "@/lib/api/inbound.service";
import { fetchCategories } from "@/lib/api/master.service";

// ── Schema ────────────────────────────────────────────────────────────────────

const inboundSchema = z.object({
  hawb:                z.string().min(1, "HAWB wajib diisi").max(100),
  descr:               z.string().optional(),
  delivery_id:         z.string().optional(),
  product_category_id: z.number().nullable().optional(),
  modality:            z.string().optional(),
  po:                  z.string().optional(),
  qty:                 z.number().nonnegative().optional(),
  locator:             z.string().optional(),
  etd:                 z.string().optional(),
  eta:                 z.string().optional(),
  ata:                 z.string().optional(),
  status:              z.string().optional(),
});

type InboundSchema = z.infer<typeof inboundSchema>;

const MODALITY_OPTIONS = ["Sea", "Air", "Land", "Rail"];
const STATUS_OPTIONS   = [
  { value: "inprogress",           label: "In Progress" },
  { value: "created",              label: "Created" },
  { value: "started",              label: "Started" },
  { value: "Warehouse in Transit", label: "WH in Transit" },
  { value: "successful",           label: "Successful" },
  { value: "failed",               label: "Failed" },
  { value: "cancelled",            label: "Cancelled" },
];

// ── Helper: strip ISO datetime to date-only for <input type="date">
function toDateInput(val: string | null | undefined): string {
  if (!val) return "";
  return val.split("T")[0].split(" ")[0]; // handles "2024-01-15" or "2024-01-15T00:00:00Z"
}

// ── Props ─────────────────────────────────────────────────────────────────────

type ServerErrors = Record<string, string[]>;

interface InboundFormSheetProps {
  open:          boolean;
  onOpenChange:  (open: boolean) => void;
  inbound?:      InboundHeader | null;
  onSubmit:      (data: InboundFormData, id?: number) => void;
  isSubmitting?: boolean;
}

function applyServerErrors(
  errors: ServerErrors,
  setError: (field: keyof InboundSchema, err: { type: string; message: string }) => void
) {
  const fieldMap: Record<string, keyof InboundSchema> = {
    hawb: "hawb", descr: "descr", delivery_id: "delivery_id",
    product_category_id: "product_category_id", modality: "modality",
    po: "po", qty: "qty", locator: "locator",
    etd: "etd", eta: "eta", ata: "ata", status: "status",
  };
  Object.entries(errors).forEach(([field, messages]) => {
    const rhfField = fieldMap[field];
    if (rhfField) setError(rhfField, { type: "server", message: messages[0] });
  });
}

// ── Component ─────────────────────────────────────────────────────────────────

export function InboundFormSheet({
  open,
  onOpenChange,
  inbound,
  onSubmit,
  isSubmitting,
}: InboundFormSheetProps) {
  const isEdit = !!inbound;
  const { data: session } = useSession();
  const token = session?.user?.accessToken;

  // Fetch categories for dropdown
  const { data: categories = [] } = useQuery({
    queryKey: ["master", "categories"],
    queryFn: () => fetchCategories(token as string),
    enabled: !!token,
    staleTime: 5 * 60_000,
  });

  const form = useForm<InboundSchema>({
    resolver: zodResolver(inboundSchema),
    defaultValues: {
      hawb:                "",
      descr:               "",
      delivery_id:         "",
      product_category_id: null,
      modality:            "",
      po:                  "",
      qty:                 undefined,
      locator:             "",
      etd:                 "",
      eta:                 "",
      ata:                 "",
      status:              "inprogress",
    },
  });

  useEffect(() => {
    if (open) {
      if (inbound) {
        form.reset({
          hawb:                inbound.hawb,
          descr:               inbound.descr || "",
          delivery_id:         inbound.delivery_id?.toString() || "",
          product_category_id: inbound.product_category_id ?? null,
          modality:            inbound.modality || "",
          po:                  inbound.po || "",
          qty:                 inbound.qty ? Number(inbound.qty) : undefined,
          locator:             inbound.locator || "",
          etd:                 toDateInput(inbound.etd),
          eta:                 toDateInput(inbound.eta),
          ata:                 toDateInput(inbound.ata),
          status:              inbound.status || "inprogress",
        });
      } else {
        form.reset({
          hawb: "", descr: "", delivery_id: "",
          product_category_id: null, modality: "", po: "",
          qty: undefined, locator: "", etd: "", eta: "", ata: "", status: "inprogress",
        });
      }
    }
  }, [open, inbound, form]);

  const handleSubmit = (values: InboundSchema) => {
    const data: InboundFormData = {
      hawb:                values.hawb,
      descr:               values.descr || values.hawb,
      product_category_id: values.product_category_id ?? null,
      modality:            values.modality || undefined,
      delivery_id:         values.delivery_id ? Number(values.delivery_id) : undefined,
      po:                  values.po || undefined,
      qty:                 values.qty !== undefined ? String(values.qty) : undefined,
      locator:             values.locator || undefined,
      etd:                 values.etd || undefined,
      eta:                 values.eta || undefined,
      ata:                 values.ata || undefined,
      status:              values.status || "inprogress",
    };
    try {
      onSubmit(data, inbound?.id);
    } catch (err: unknown) {
      const axiosErr = err as { response?: { data?: { errors?: ServerErrors } } };
      const serverErrors = axiosErr?.response?.data?.errors;
      if (serverErrors) applyServerErrors(serverErrors, form.setError);
    }
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

            {/* Description */}
            <FormField
              control={form.control}
              name="descr"
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

            {/* Category + Modality */}
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
                        <SelectTrigger id="select-inb-category">
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
                name="modality"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Modality</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value ?? ""}>
                      <FormControl>
                        <SelectTrigger id="select-inb-modality">
                          <SelectValue placeholder="Select..." />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        <SelectItem value="">— None —</SelectItem>
                        {MODALITY_OPTIONS.map((o) => (
                          <SelectItem key={o} value={o}>{o}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </div>

            {/* Delivery ID + PO */}
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

            {/* Qty + Locator */}
            <div className="grid grid-cols-2 gap-4">
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
                        value={field.value ?? ""}
                        onChange={(e) => field.onChange(e.target.value === "" ? undefined : Number(e.target.value))}
                        id="input-inb-qty"
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
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
            </div>

            {/* ETD / ETA / ATA */}
            <div className="grid grid-cols-3 gap-3">
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
            </div>

            {/* Status (edit only) */}
            {isEdit && (
              <FormField
                control={form.control}
                name="status"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Status</FormLabel>
                    <Select onValueChange={field.onChange} value={field.value ?? "inprogress"}>
                      <FormControl>
                        <SelectTrigger id="select-inb-status">
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

            {/* Buttons */}
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
