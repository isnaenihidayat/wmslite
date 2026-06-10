"use client";

import { ColumnDef } from "@tanstack/react-table";
import type { Shipment } from "@/lib/api/shipment.service";
import { formatDate } from "@/lib/utils";
import { StatusBadge } from "@/components/data-table/status-badge";
import { SortableHeader } from "@/components/data-table/sortable-header";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { MoreHorizontal, Pencil, Trash2, Eye, ArrowRight } from "lucide-react";

interface ColumnOptions {
  onEdit?: (row: Shipment) => void;
  onDelete?: (row: Shipment) => void;
  onDetails?: (row: Shipment) => void;
  onPushOutbound?: (row: Shipment) => void;
  canEdit?: boolean;
  canDelete?: boolean;
}

export function getShipmentColumns(opts: ColumnOptions = {}): ColumnDef<Shipment>[] {
  return [
    {
      accessorKey: "id",
      header: ({ column }) => <SortableHeader column={column} label="ID" />,
      cell: ({ row }) => (
        <span className="text-xs font-mono text-muted-foreground">{row.getValue("id")}</span>
      ),
      size: 60,
    },
    {
      accessorKey: "hawb",
      header: ({ column }) => <SortableHeader column={column} label="HAWB" />,
      cell: ({ row }) => (
        <span className="font-mono text-xs font-medium">{row.getValue("hawb")}</span>
      ),
      size: 140,
    },
    {
      accessorKey: "descr",
      header: ({ column }) => <SortableHeader column={column} label="Description" />,
      cell: ({ row }) => (
        <span className="max-w-[200px] truncate block text-sm" title={row.getValue("descr")}>
          {row.getValue("descr") || "—"}
        </span>
      ),
      size: 200,
    },
    {
      id: "category",
      header: "Category",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">
          {row.original.category?.name ?? "—"}
        </span>
      ),
      size: 120,
    },
    {
      accessorKey: "modality",
      header: "Modality",
      cell: ({ row }) => (
        <span className="text-xs">{row.getValue("modality") || "—"}</span>
      ),
      size: 80,
    },
    {
      accessorKey: "delivery_id",
      header: "Delivery ID",
      cell: ({ row }) => (
        <span className="text-xs font-mono">{row.getValue("delivery_id") || "—"}</span>
      ),
      size: 110,
    },
    {
      accessorKey: "qty",
      header: ({ column }) => <SortableHeader column={column} label="Qty" />,
      cell: ({ row }) => (
        <span className="text-xs text-right block">
          {(row.getValue("qty") as number) > 0 ? row.getValue("qty") : "—"}
        </span>
      ),
      size: 60,
    },
    {
      accessorKey: "po",
      header: "PO Number",
      cell: ({ row }) => (
        <span className="text-xs">{row.getValue("po") || "—"}</span>
      ),
      size: 110,
    },
    {
      accessorKey: "etd",
      header: ({ column }) => <SortableHeader column={column} label="ETD" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">{formatDate(row.getValue("etd"))}</span>
      ),
      size: 95,
    },
    {
      accessorKey: "eta",
      header: ({ column }) => <SortableHeader column={column} label="ETA" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">{formatDate(row.getValue("eta"))}</span>
      ),
      size: 95,
    },
    {
      accessorKey: "ata",
      header: "ATA",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">{formatDate(row.getValue("ata"))}</span>
      ),
      size: 95,
    },
    {
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => <StatusBadge status={row.getValue("status")} />,
      size: 120,
    },
    {
      accessorKey: "date_created",
      header: ({ column }) => <SortableHeader column={column} label="Created" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">{formatDate(row.getValue("date_created"))}</span>
      ),
      size: 100,
    },
    // ── Actions column
    {
      id: "actions",
      header: "Actions",
      enableHiding: false,
      cell: ({ row }) => {
        const shipment = row.original;
        const isTransit = shipment.status === "Warehouse in Transit";
        const canDel = opts.canDelete && !shipment.id; // only if no inbound linked

        return (
          <DropdownMenu>
            <DropdownMenuTrigger>
              <Button
                variant="ghost"
                size="icon"
                className="h-7 w-7"
                id={`btn-action-${shipment.id}`}
              >
                <MoreHorizontal className="h-4 w-4" />
                <span className="sr-only">Open menu</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
              <DropdownMenuItem onClick={() => opts.onDetails?.(shipment)}>
                <Eye className="mr-2 h-4 w-4" />
                Details
              </DropdownMenuItem>
              {opts.canEdit && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={() => opts.onEdit?.(shipment)}>
                    <Pencil className="mr-2 h-4 w-4" />
                    Edit
                  </DropdownMenuItem>
                </>
              )}
              {isTransit && opts.canEdit && (
                <DropdownMenuItem
                  onClick={() => opts.onPushOutbound?.(shipment)}
                  className="text-primary"
                >
                  <ArrowRight className="mr-2 h-4 w-4" />
                  Push Outbound
                </DropdownMenuItem>
              )}
              {opts.canDelete && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    onClick={() => opts.onDelete?.(shipment)}
                    className="text-destructive focus:text-destructive"
                    disabled={!canDel}
                  >
                    <Trash2 className="mr-2 h-4 w-4" />
                    Delete
                  </DropdownMenuItem>
                </>
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        );
      },
      size: 60,
    },
  ];
}
