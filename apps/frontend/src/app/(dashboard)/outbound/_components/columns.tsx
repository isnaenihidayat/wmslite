"use client";

import { ColumnDef } from "@tanstack/react-table";
import type { OutboundHeader } from "@/types";
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
import { MoreHorizontal, Pencil, Trash2, Eye, Printer } from "lucide-react";

interface ColumnOptions {
  onEdit?: (row: OutboundHeader) => void;
  onDelete?: (row: OutboundHeader) => void;
  onDetails?: (row: OutboundHeader) => void;
  onPrint?: (row: OutboundHeader) => void;
  canEdit?: boolean;
  canDelete?: boolean;
}

export function getOutboundColumns(opts: ColumnOptions = {}): ColumnDef<OutboundHeader>[] {
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
      accessorKey: "po",
      header: ({ column }) => <SortableHeader column={column} label="GON / PO" />,
      cell: ({ row }) => (
        <span className="font-mono text-xs font-semibold">{row.getValue("po") || "—"}</span>
      ),
      size: 140,
    },
    {
      accessorKey: "destination",
      header: "Destination",
      cell: ({ row }) => (
        <span className="text-sm max-w-[160px] truncate block" title={String(row.getValue("destination"))}>
          {row.getValue("destination") || "—"}
        </span>
      ),
      size: 160,
    },
    {
      accessorKey: "delivery_id",
      header: "PSO Delivery ID",
      cell: ({ row }) => (
        <span className="text-xs font-mono">{row.getValue("delivery_id") || "—"}</span>
      ),
      size: 120,
    },
    {
      accessorKey: "transporter",
      header: "Transporter",
      cell: ({ row }) => (
        <span className="text-xs">{row.getValue("transporter") || "—"}</span>
      ),
      size: 110,
    },
    {
      accessorKey: "qty",
      header: ({ column }) => <SortableHeader column={column} label="Qty" />,
      cell: ({ row }) => {
        const qty = row.getValue("qty") as number;
        return (
          <span className={`text-xs font-mono font-semibold text-right block ${qty > 0 ? "text-blue-600 dark:text-blue-400" : "text-muted-foreground"}`}>
            {qty > 0 ? qty : "—"}
          </span>
        );
      },
      size: 65,
    },
    {
      accessorKey: "scan_time",
      header: "Scan Time",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {row.getValue("scan_time") || "—"}
        </span>
      ),
      size: 120,
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
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {row.getValue("date_created") || "—"}
        </span>
      ),
      size: 140,
    },
    {
      accessorKey: "date_updated",
      header: "Last Modified",
      cell: ({ row }) => (
        <span className="text-xs text-muted-foreground whitespace-nowrap">
          {row.getValue("date_updated") || "—"}
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
        const outbound = row.original;
        const isSuccessful = outbound.status === "successful";

        return (
          <DropdownMenu>
            <DropdownMenuTrigger>
              <Button
                variant="ghost"
                size="icon"
                className="h-7 w-7"
                id={`btn-action-out-${outbound.id}`}
              >
                <MoreHorizontal className="h-4 w-4" />
                <span className="sr-only">Open menu</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
              <DropdownMenuItem onClick={() => opts.onDetails?.(outbound)}>
                <Eye className="mr-2 h-4 w-4" />
                Details
              </DropdownMenuItem>
              <DropdownMenuItem onClick={() => opts.onPrint?.(outbound)}>
                <Printer className="mr-2 h-4 w-4" />
                Print Picking List
              </DropdownMenuItem>
              {opts.canEdit && !isSuccessful && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={() => opts.onEdit?.(outbound)}>
                    <Pencil className="mr-2 h-4 w-4" />
                    Edit
                  </DropdownMenuItem>
                </>
              )}
              {opts.canDelete && !isSuccessful && (
                <>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    onClick={() => opts.onDelete?.(outbound)}
                    className="text-destructive focus:text-destructive"
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
