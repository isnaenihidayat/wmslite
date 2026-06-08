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
import type { InboundHeader } from "@/types";
import {
  Package,
  MapPin,
  Calendar,
  Hash,
  Truck,
  BarChart3,
  CheckCircle2,
  Circle,
} from "lucide-react";

interface InboundDetailSheetProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  inbound: InboundHeader | null;
  isLoading?: boolean;
}

function InfoRow({ label, value, icon: Icon }: {
  label: string;
  value?: string | number | null;
  icon?: React.ComponentType<{ className?: string }>;
}) {
  return (
    <div className="flex items-start gap-3 py-2.5 border-b last:border-b-0">
      {Icon && (
        <div className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded text-muted-foreground">
          <Icon className="h-3.5 w-3.5" />
        </div>
      )}
      <div className="flex-1 min-w-0">
        <p className="text-[10px] font-medium uppercase tracking-wide text-muted-foreground">{label}</p>
        <p className="mt-0.5 text-sm font-medium truncate">{value || "—"}</p>
      </div>
    </div>
  );
}

function StatCard({ label, value, color = "default" }: {
  label: string;
  value: number;
  color?: "default" | "green" | "blue";
}) {
  const colorMap = {
    default: "bg-muted/50 text-foreground",
    green: "bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300",
    blue: "bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300",
  };
  return (
    <div className={`rounded-lg p-3 ${colorMap[color]}`}>
      <p className="text-[10px] uppercase tracking-wide opacity-70 mb-1">{label}</p>
      <p className="text-2xl font-bold tabular-nums">{value}</p>
    </div>
  );
}

export function InboundDetailSheet({
  open,
  onOpenChange,
  inbound,
  isLoading,
}: InboundDetailSheetProps) {
  if (!inbound && !isLoading) return null;

  const qtyReceived = inbound?.totalQtyReceived ?? 0;
  const items = inbound?.itemInDetail ?? 0;
  const picked = inbound?.totalPick ?? 0;
  const pickProgress = items > 0 ? Math.round((picked / items) * 100) : 0;

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="w-full sm:max-w-lg overflow-y-auto">
        <SheetHeader className="mb-5">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10">
              <Package className="h-4 w-4 text-primary" />
            </div>
            <div>
              <SheetTitle className="text-base">
                {isLoading ? <Skeleton className="h-5 w-32" /> : (inbound?.hawb || "Inbound Detail")}
              </SheetTitle>
              <SheetDescription className="mt-0.5">
                {isLoading ? (
                  <Skeleton className="h-4 w-48" />
                ) : (
                  <span className="flex items-center gap-2">
                    Inbound Record
                    {inbound?.status && <StatusBadge status={inbound.status} />}
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
        ) : inbound ? (
          <div className="space-y-5">
            {/* ── Summary Stats ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-2">
                Summary
              </p>
              <div className="grid grid-cols-3 gap-2">
                <StatCard label="Qty Received" value={qtyReceived} color="green" />
                <StatCard label="Items" value={items} />
                <StatCard label="Picked" value={picked} color="blue" />
              </div>

              {/* Pick progress */}
              {items > 0 && (
                <div className="mt-3 rounded-lg border p-3">
                  <div className="flex items-center justify-between mb-1.5">
                    <span className="text-xs text-muted-foreground flex items-center gap-1">
                      <BarChart3 className="h-3.5 w-3.5" /> Pick Progress
                    </span>
                    <span className="text-xs font-semibold">{pickProgress}%</span>
                  </div>
                  <div className="h-2 rounded-full bg-muted overflow-hidden">
                    <div
                      className={`h-full rounded-full transition-all ${
                        pickProgress === 100
                          ? "bg-emerald-500"
                          : "bg-primary"
                      }`}
                      style={{ width: `${pickProgress}%` }}
                    />
                  </div>
                  <p className="text-[10px] text-muted-foreground mt-1">
                    {picked} of {items} items picked
                  </p>
                </div>
              )}
            </div>

            {/* ── Identity ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Identity
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="HAWB" value={inbound.hawb} icon={Hash} />
                <InfoRow label="Description" value={inbound.descr} icon={Package} />
                <InfoRow label="Category" value={inbound.product_category_name} icon={Package} />
                <InfoRow label="Delivery ID" value={inbound.delivery_id?.toString()} icon={Truck} />
                <InfoRow label="PO Number" value={inbound.po} icon={Hash} />
                <InfoRow label="Modality" value={inbound.modality} icon={Truck} />
              </div>
            </div>

            {/* ── Location ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Location
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="Oracle Locator" value={inbound.locator} icon={MapPin} />
              </div>
            </div>

            {/* ── Dates ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Timeline
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="ETD" value={inbound.etd} icon={Calendar} />
                <InfoRow label="ETA" value={inbound.eta} icon={Calendar} />
                <InfoRow label="ATA" value={inbound.ata} icon={Calendar} />
                <InfoRow label="SPPB Date" value={inbound.sppb_date} icon={Calendar} />
                <InfoRow label="Date Created" value={inbound.date_created} icon={Calendar} />
                <InfoRow label="Last Modified" value={inbound.date_updated} icon={Calendar} />
              </div>
            </div>

            {/* ── Status indicator ── */}
            <div className="flex items-center gap-2 rounded-lg border p-3">
              {inbound.status === "successful" ? (
                <CheckCircle2 className="h-4 w-4 text-emerald-500 shrink-0" />
              ) : (
                <Circle className="h-4 w-4 text-muted-foreground shrink-0" />
              )}
              <div>
                <p className="text-xs text-muted-foreground">Current Status</p>
                <StatusBadge status={inbound.status} />
              </div>
            </div>
          </div>
        ) : null}
      </SheetContent>
    </Sheet>
  );
}
