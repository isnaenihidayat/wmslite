"use client";

import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet";
import { StatusBadge } from "@/components/data-table/status-badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import type { OutboundHeader } from "@/types";
import {
  Package,
  Truck,
  MapPin,
  Calendar,
  Hash,
  User,
  Clock,
  CheckCircle2,
  Circle,
  Printer,
} from "lucide-react";
import { Button } from "@/components/ui/button";

interface OutboundDetailSheetProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  outbound: OutboundHeader | null;
  onPrint?: (outbound: OutboundHeader) => void;
  isLoading?: boolean;
}

function InfoRow({
  label,
  value,
  icon: Icon,
}: {
  label: string;
  value?: string | number | null;
  icon?: React.ComponentType<{ className?: string }>;
}) {
  return (
    <div className="flex items-start gap-3 py-2.5 border-b last:border-b-0">
      {Icon && (
        <div className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center text-muted-foreground">
          <Icon className="h-3.5 w-3.5" />
        </div>
      )}
      <div className="flex-1 min-w-0">
        <p className="text-[10px] font-medium uppercase tracking-wide text-muted-foreground">
          {label}
        </p>
        <p className="mt-0.5 text-sm font-medium break-words">{value || "—"}</p>
      </div>
    </div>
  );
}

export function OutboundDetailSheet({
  open,
  onOpenChange,
  outbound,
  onPrint,
  isLoading,
}: OutboundDetailSheetProps) {
  if (!outbound && !isLoading) return null;

  const isSuccessful = outbound?.status === "successful";

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
        <SheetHeader className="mb-5">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10">
              <Package className="h-4 w-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div className="flex-1 min-w-0">
              <SheetTitle className="text-base">
                {isLoading ? (
                  <Skeleton className="h-5 w-32" />
                ) : (
                  outbound?.po || "Outbound Detail"
                )}
              </SheetTitle>
              <SheetDescription className="mt-0.5">
                {isLoading ? (
                  <Skeleton className="h-4 w-48" />
                ) : (
                  <span className="flex items-center gap-2 flex-wrap">
                    Outbound Record #{outbound?.id}
                    {outbound?.status && <StatusBadge status={outbound.status} />}
                  </span>
                )}
              </SheetDescription>
            </div>
          </div>
        </SheetHeader>

        {isLoading ? (
          <div className="space-y-3">
            {Array.from({ length: 8 }).map((_, i) => (
              <Skeleton key={i} className="h-10 w-full" />
            ))}
          </div>
        ) : outbound ? (
          <div className="space-y-5">
            {/* ── Quick Stats ── */}
            <div className="grid grid-cols-2 gap-3">
              <div className="rounded-lg border bg-card p-3">
                <p className="text-[10px] uppercase tracking-wide text-muted-foreground">Total Qty</p>
                <p className="text-2xl font-bold tabular-nums text-blue-600 dark:text-blue-400">
                  {outbound.qty > 0 ? outbound.qty : "—"}
                </p>
              </div>
              <div className={`rounded-lg border p-3 ${isSuccessful ? "bg-emerald-50 dark:bg-emerald-900/20" : "bg-card"}`}>
                <p className="text-[10px] uppercase tracking-wide text-muted-foreground">Status</p>
                <div className="mt-1">
                  <StatusBadge status={outbound.status} />
                </div>
              </div>
            </div>

            {/* ── Print Picking List Button ── */}
            {onPrint && (
              <Button
                variant="outline"
                className="w-full gap-2"
                onClick={() => onPrint(outbound)}
                id="btn-print-picking"
              >
                <Printer className="h-4 w-4" />
                Print Picking List
              </Button>
            )}

            {/* ── Identity ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Outbound Info
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="GON / PO Number" value={outbound.po} icon={Hash} />
                <InfoRow label="Destination" value={outbound.destination} icon={MapPin} />
                <InfoRow label="PSO Delivery ID" value={outbound.delivery_id?.toString()} icon={Hash} />
                <InfoRow label="Transporter" value={outbound.transporter} icon={Truck} />
                <InfoRow label="Checker" value={outbound.checker} icon={User} />
              </div>
            </div>

            {/* ── Timeline ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Timeline
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="Date Created" value={outbound.date_created} icon={Calendar} />
                <InfoRow label="Scan Time" value={outbound.scan_time} icon={Clock} />
                <InfoRow label="Last Modified" value={outbound.date_updated} icon={Calendar} />
              </div>
            </div>

            {/* ── Status ── */}
            <div className="flex items-center gap-2 rounded-lg border p-3">
              {isSuccessful ? (
                <CheckCircle2 className="h-4 w-4 text-emerald-500 shrink-0" />
              ) : (
                <Circle className="h-4 w-4 text-muted-foreground shrink-0" />
              )}
              <div>
                <p className="text-xs text-muted-foreground">Current Status</p>
                <StatusBadge status={outbound.status} />
              </div>
              {isSuccessful && (
                <Badge className="ml-auto bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 text-[10px]">
                  Completed
                </Badge>
              )}
            </div>
          </div>
        ) : null}
      </SheetContent>
    </Sheet>
  );
}
