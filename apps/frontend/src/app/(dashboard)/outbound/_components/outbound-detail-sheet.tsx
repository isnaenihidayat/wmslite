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
import { Button } from "@/components/ui/button";
import type { Outbound as OutboundHeader } from "@/lib/api/outbound.service";
import { useOutboundDetails } from "@/hooks/use-outbound";
import { formatDate, formatDateTime } from "@/lib/utils";
import {
  Package,
  Truck,
  MapPin,
  Calendar,
  Hash,
  User,
  CheckCircle2,
  Circle,
  Printer,
  Scan,
  Layers,
} from "lucide-react";

interface OutboundDetailSheetProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  outbound: OutboundHeader | null;
}

// ── Sub-components ────────────────────────────────────────────────────────────

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

// ── Print Picking List ────────────────────────────────────────────────────────

function handlePrint(outbound: OutboundHeader) {
  const details = (outbound as OutboundHeader & { details?: Array<{ hawb: string; descr: string; loc: string; flag: number; scan_time: string | null }> }).details ?? [];
  const scanned  = details.filter((d) => d.flag).length;
  const total    = details.length;

  const rows = details
    .map(
      (d, i) => `
        <tr class="${d.flag ? "scanned" : ""}">
          <td>${i + 1}</td>
          <td class="mono">${d.hawb}</td>
          <td>${d.descr || "—"}</td>
          <td class="mono">${d.loc || "—"}</td>
          <td class="center">${d.flag ? "✓" : ""}</td>
          <td>${d.scan_time ? new Date(d.scan_time).toLocaleString("id-ID") : "—"}</td>
        </tr>`
    )
    .join("");

  const html = `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Picking List — ${outbound.po || `OB-${outbound.id}`}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #111; padding: 20px; }
    h2 { font-size: 16px; margin-bottom: 4px; }
    .meta { display: flex; gap: 32px; margin-bottom: 16px; color: #555; font-size: 10px; }
    .meta span strong { color: #111; }
    .progress { margin-bottom: 16px; padding: 8px 12px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; font-size: 10px; }
    .progress strong { font-size: 13px; color: #0369a1; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #1e293b; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
    tr.scanned td { background: #f0fdf4; }
    .mono { font-family: monospace; font-size: 10px; }
    .center { text-align: center; color: #16a34a; font-weight: bold; font-size: 13px; }
    .footer { margin-top: 24px; font-size: 9px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 8px; display: flex; justify-content: space-between; }
    @media print { body { padding: 10mm; } }
  </style>
</head>
<body>
  <h2>Picking List — ${outbound.po || `OB-${outbound.id}`}</h2>
  <div class="meta">
    <span>Destination: <strong>${outbound.destination}</strong></span>
    <span>Transporter: <strong>${outbound.transporter || "—"}</strong></span>
    <span>Checker: <strong>${outbound.checker}</strong></span>
    <span>Status: <strong>${outbound.status}</strong></span>
    <span>Date: <strong>${outbound.date_created ? new Date(outbound.date_created).toLocaleDateString("id-ID") : "—"}</strong></span>
  </div>
  <div class="progress">
    Picking progress: <strong>${scanned}</strong> / ${total} items scanned
    ${total > 0 ? ` — ${Math.round((scanned / total) * 100)}%` : ""}
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>HAWB</th>
        <th>Description</th>
        <th>Location</th>
        <th>Picked</th>
        <th>Scan Time</th>
      </tr>
    </thead>
    <tbody>${rows || "<tr><td colspan='6' style='text-align:center;color:#999;padding:16px'>Tidak ada item detail.</td></tr>"}</tbody>
  </table>
  <div class="footer">
    <span>WMS Lite — Picking List</span>
    <span>Dicetak: ${new Date().toLocaleString("id-ID")}</span>
  </div>
</body>
</html>`;

  const win = window.open("", "_blank", "width=900,height=700");
  if (!win) return;
  win.document.write(html);
  win.document.close();
  win.focus();
  setTimeout(() => win.print(), 400);
}

// ── Main Component ────────────────────────────────────────────────────────────

export function OutboundDetailSheet({
  open,
  onOpenChange,
  outbound,
}: OutboundDetailSheetProps) {
  const { data: fullOutbound, isLoading: detailsLoading } = useOutboundDetails(
    open ? (outbound?.id ?? null) : null
  );

  // Use fetched full data when available, fall back to list-row data
  const record = fullOutbound ?? outbound;
  const details = fullOutbound?.details ?? [];

  if (!outbound && !open) return null;

  const isSuccessful = record?.status === "successful";
  const scanned      = details.filter((d) => d.flag).length;
  const total        = details.length;
  const pickPct      = total > 0 ? Math.round((scanned / total) * 100) : 0;

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent className="w-full sm:max-w-xl overflow-y-auto">
        <SheetHeader className="mb-5">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10">
              <Package className="h-4 w-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div className="flex-1 min-w-0">
              <SheetTitle className="text-base">
                {record?.po || `Outbound #${record?.id}`}
              </SheetTitle>
              <SheetDescription className="mt-0.5">
                <span className="flex items-center gap-2 flex-wrap">
                  Outbound Record #{record?.id}
                  {record?.status && <StatusBadge status={record.status} />}
                </span>
              </SheetDescription>
            </div>
          </div>
        </SheetHeader>

        {record ? (
          <div className="space-y-5">
            {/* ── Quick Stats ── */}
            <div className="grid grid-cols-3 gap-3">
              <div className="rounded-lg border bg-card p-3">
                <p className="text-[10px] uppercase tracking-wide text-muted-foreground">Total Qty</p>
                <p className="text-2xl font-bold tabular-nums text-blue-600 dark:text-blue-400">
                  {record.qty || "—"}
                </p>
              </div>
              <div className="rounded-lg border bg-card p-3">
                <p className="text-[10px] uppercase tracking-wide text-muted-foreground">Items</p>
                <p className="text-2xl font-bold tabular-nums">{total}</p>
              </div>
              <div className={`rounded-lg border p-3 ${isSuccessful ? "bg-emerald-50 dark:bg-emerald-900/20" : "bg-card"}`}>
                <p className="text-[10px] uppercase tracking-wide text-muted-foreground">Scanned</p>
                <p className="text-2xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{scanned}</p>
              </div>
            </div>

            {/* Pick progress */}
            {total > 0 && (
              <div className="rounded-lg border p-3">
                <div className="flex items-center justify-between mb-1.5">
                  <span className="text-xs text-muted-foreground">Pick Progress</span>
                  <span className="text-xs font-semibold">{pickPct}%</span>
                </div>
                <div className="h-2 rounded-full bg-muted overflow-hidden">
                  <div
                    className={`h-full rounded-full transition-all ${pickPct === 100 ? "bg-emerald-500" : "bg-blue-500"}`}
                    style={{ width: `${pickPct}%` }}
                  />
                </div>
                <p className="text-[10px] text-muted-foreground mt-1">{scanned} of {total} items picked</p>
              </div>
            )}

            {/* ── Print Button ── */}
            <Button
              variant="outline"
              className="w-full gap-2"
              onClick={() => record && handlePrint({ ...record, details } as OutboundHeader & { details: typeof details })}
              id="btn-print-picking"
            >
              <Printer className="h-4 w-4" />
              Print Picking List
            </Button>

            {/* ── Identity ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Outbound Info
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="GON / PO Number" value={record.po}                      icon={Hash}    />
                <InfoRow label="Destination"      value={record.destination}             icon={MapPin}  />
                <InfoRow label="PSO Delivery ID"  value={record.delivery_id?.toString()} icon={Hash}    />
                <InfoRow label="Transporter"      value={record.transporter}             icon={Truck}   />
                <InfoRow label="Checker"          value={record.checker}                 icon={User}    />
              </div>
            </div>

            {/* ── Timeline ── */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">
                Timeline
              </p>
              <div className="rounded-lg border divide-y">
                <InfoRow label="Date Created"  value={formatDate(record.date_created)} icon={Calendar} />
                <InfoRow label="Last Modified" value={formatDate(record.date_updated)} icon={Calendar} />
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
                <StatusBadge status={record.status} />
              </div>
              {isSuccessful && (
                <Badge className="ml-auto bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 text-[10px]">
                  Completed
                </Badge>
              )}
            </div>

            {/* ── Item Details ── */}
            <div>
              <div className="flex items-center justify-between mb-2">
                <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground flex items-center gap-1.5">
                  <Layers className="h-3.5 w-3.5" />
                  Picking Items
                </p>
                {!detailsLoading && (
                  <Badge variant="secondary" className="text-xs">
                    {total} items
                  </Badge>
                )}
              </div>

              {detailsLoading ? (
                <div className="space-y-2">
                  {Array.from({ length: 3 }).map((_, i) => (
                    <Skeleton key={i} className="h-14 w-full rounded-lg" />
                  ))}
                </div>
              ) : total > 0 ? (
                <div className="space-y-2">
                  {details.map((item, idx) => (
                    <div
                      key={item.id ?? idx}
                      className={`rounded-lg border p-3 ${
                        item.flag
                          ? "border-emerald-200 bg-emerald-50/50 dark:border-emerald-800 dark:bg-emerald-900/10"
                          : "bg-card"
                      }`}
                    >
                      <div className="flex items-start justify-between gap-2 mb-1.5">
                        <div className="min-w-0">
                          <p className="font-mono text-xs font-semibold truncate">{item.hawb}</p>
                          <p className="text-xs text-muted-foreground truncate">{item.descr || "—"}</p>
                        </div>
                        {item.flag ? (
                          <Badge variant="default" className="gap-1 text-[10px] px-1.5 py-0.5 bg-emerald-600 flex-shrink-0">
                            <Scan className="h-2.5 w-2.5" /> Picked
                          </Badge>
                        ) : (
                          <Badge variant="outline" className="text-[10px] px-1.5 py-0.5 flex-shrink-0">
                            Pending
                          </Badge>
                        )}
                      </div>
                      <div className="flex items-center gap-4 text-[10px] text-muted-foreground">
                        <span className="flex items-center gap-1">
                          <MapPin className="h-2.5 w-2.5" />
                          <span className="font-mono">{item.loc || "—"}</span>
                        </span>
                        {item.scan_time && (
                          <span className="flex items-center gap-1">
                            <Scan className="h-2.5 w-2.5" />
                            {formatDateTime(item.scan_time)}
                          </span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="rounded-lg border border-dashed p-6 text-center">
                  <Package className="h-8 w-8 text-muted-foreground/40 mx-auto mb-2" />
                  <p className="text-xs text-muted-foreground">Belum ada item picking untuk outbound ini.</p>
                </div>
              )}
            </div>
          </div>
        ) : (
          <div className="space-y-3">
            {Array.from({ length: 8 }).map((_, i) => (
              <Skeleton key={i} className="h-10 w-full" />
            ))}
          </div>
        )}
      </SheetContent>
    </Sheet>
  );
}
