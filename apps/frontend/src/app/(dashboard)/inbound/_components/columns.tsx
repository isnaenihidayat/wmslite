"use client";

import { ColumnDef } from "@tanstack/react-table";
import type { InboundHeader } from "@/types";
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
import { MoreHorizontal, Pencil, Trash2, Eye, Package } from "lucide-react";

interface ColumnOptions {
  onEdit?: (row: InboundHeader) => void;
  onDelete?: (row: InboundHeader) => void;
  onDetails?: (row: InboundHeader) => void;
  canEdit?: boolean;
  canDelete?: boolean;
}

export function getInboundColumns(opts: ColumnOptions = {}): ColumnDef<InboundHeader>[] {
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
        <span className="font-mono text-xs font-semibold">{row.getValue("hawb")}</span>
      ),
      size: 140,
    },
    {
      accessorKey: "descr",
      header: "Description",
      cell: ({ row }) => (
        <span
          className="max-w-[180px] truncate block text-sm"
          title={String(row.getValue("descr"))}
        >
          {row.getValue("descr") || "—"}
        </span>
      ),
      size: 180,
    },
    {
      accessorKey: "product_category_name",
      header: "Category",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">
          {row.getValue("product_category_name") || "—"}
        </span>
      ),
      size: 110,
    },
    {
      accessorKey: "modality",
      header: "Modality",
      cell: ({ row }) => (
        <span className="text-xs">{row.getValue("modality") || "—"}</span>
      ),
      size: 75,
    },
    {
      accessorKey: "delivery_id",
      header: "Delivery ID",
      cell: ({ row }) => (
        <span className="text-xs font-mono">{row.getValue("delivery_id") || "—"}</span>
      ),
      size: 100,
    },
    {
      accessorKey: "po",
      header: "PO Number",
      cell: ({ row }) => (
        <span className="text-xs">{row.getValue("po") || "—"}</span>
      ),
      size: 100,
    },
    {
      accessorKey: "locator",
      header: "Locator",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground max-w-[120px] truncate block" title={String(row.getValue("locator"))}>
          {row.getValue("locator") || "—"}
        </span>
      ),
      size: 120,
    },
    {
      accessorKey: "etd",
      header: ({ column }) => <SortableHeader column={column} label="ETD" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">{row.getValue("etd") || "—"}</span>
      ),
      size: 85,
    },
    {
      accessorKey: "eta",
      header: ({ column }) => <SortableHeader column={column} label="ETA" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">{row.getValue("eta") || "—"}</span>
      ),
      size: 85,
    },
    {
      accessorKey: "ata",
      header: "ATA",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground">{row.getValue("ata") || "—"}</span>
      ),
      size: 85,
    },
    {
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => <StatusBadge status={row.getValue("status")} />,
      size: 120,
    },
    // ── Qty summary columns
    {
      accessorKey: "totalQtyReceived",
      header: "Qty Recv",
      cell: ({ row }) => {
        const qty = row.getValue("totalQtyReceived") as number;
        return (
          <span className={`text-xs text-right block font-mono ${qty > 0 ? "text-emerald-600 dark:text-emerald-400 font-semibold" : "text-muted-foreground"}`}>
            {qty > 0 ? qty : "—"}
          </span>
        );
      },
      size: 70,
    },
    {
      accessorKey: "itemInDetail",
      header: "Items",
      cell: ({ row }) => {
        const items = row.getValue("itemInDetail") as number;
        return (
          <span className="text-xs text-right block font-mono text-muted-foreground">
            {items > 0 ? items : "—"}
          </span>
        );
      },
      size: 60,
    },
    {
      accessorKey: "totalPick",
      header: "Picked",
      cell: ({ row }) => {
        const picked = row.getValue("totalPick") as number;
        return (
          <span className={`text-xs text-right block font-mono ${picked > 0 ? "text-blue-600 dark:text-blue-400" : "text-muted-foreground"}`}>
            {picked > 0 ? picked : "—"}
          </span>
        );
      },
      size: 65,
    },
    {
      accessorKey: "date_created",
      header: ({ column }) => <SortableHeader column={column} label="Created" />,
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {row.getValue("date_created") || "—"}
        </span>
      ),
      size: 140,
    },
    // ── Actions
    {
      id: "actions",
      header: "Actions",
      enableHiding: false,
      cell: ({ row }) => {
        const inbound = row.original;
        const hasItems = (inbound.itemInDetail ?? 0) > 0;

        return (
          <DropdownMenu>
            <DropdownMenuTrigger>
              <Button
                variant="ghost"
                size="icon"
                className="h-7 w-7"
                id={`btn-action-inbound-${inbound.id}`}
              >
                <MoreHorizontal className="h-4 w-4" />
                <span className="sr-only">Open menu</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
              <DropdownMenuItem onClick={() => opts.onDetails?.(inbound)}>
                <Eye className="mr-2 h-4 w-4" />
                Details
              </DropdownMenuItem>
              {opts.canEdit && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={() => opts.onEdit?.(inbound)}>
                    <Pencil className="mr-2 h-4 w-4" />
                    Edit
                  </DropdownMenuItem>
                </>
              )}
              {opts.canDelete && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    onClick={() => opts.onDelete?.(inbound)}
                    className="text-destructive focus:text-destructive"
                    disabled={hasItems}
                    title={hasItems ? "Tidak bisa dihapus — ada item terkait" : ""}
                  >
                    <Trash2 className="mr-2 h-4 w-4" />
                    {hasItems ? "Delete (locked)" : "Delete"}
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
