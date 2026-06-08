"use client";

import * as React from "react";
import {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
  flexRender,
  getCoreRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
  PaginationState,
} from "@tanstack/react-table";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Search,
  SlidersHorizontal,
  RefreshCw,
} from "lucide-react";

// ── Types ──────────────────────────────────────────────────────────────────────
export interface DataTableProps<TData, TValue> {
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
  isLoading?: boolean;
  totalRows?: number;              // for server-side pagination
  pagination?: PaginationState;
  onPaginationChange?: (p: PaginationState) => void;
  globalFilter?: string;
  onGlobalFilterChange?: (v: string) => void;
  onRefresh?: () => void;
  toolbar?: React.ReactNode;      // custom toolbar slot
  emptyMessage?: string;
  searchPlaceholder?: string;
  pageCount?: number;
}

const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];
const SKELETON_ROWS = 8;

// ── Component ─────────────────────────────────────────────────────────────────
export function DataTable<TData, TValue>({
  columns,
  data,
  isLoading = false,
  totalRows,
  pagination,
  onPaginationChange,
  globalFilter = "",
  onGlobalFilterChange,
  onRefresh,
  toolbar,
  emptyMessage = "No data found.",
  searchPlaceholder = "Search...",
  pageCount,
}: DataTableProps<TData, TValue>) {
  const [sorting, setSorting] = React.useState<SortingState>([]);
  const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([]);
  const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({});
  const [rowSelection, setRowSelection] = React.useState({});

  // Support both server-side and client-side pagination
  const isServerSide = !!onPaginationChange && !!pagination;

  const table = useReactTable({
    data,
    columns,
    pageCount: pageCount ?? Math.ceil((totalRows ?? data.length) / (pagination?.pageSize ?? 25)),
    state: {
      sorting,
      columnFilters,
      columnVisibility,
      rowSelection,
      ...(isServerSide && { pagination }),
      ...(!isServerSide && {}),
    },
    manualPagination: isServerSide,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onColumnVisibilityChange: setColumnVisibility,
    onRowSelectionChange: setRowSelection,
    ...(isServerSide && {
      onPaginationChange: (updater) => {
        const next = typeof updater === "function" ? updater(pagination!) : updater;
        onPaginationChange?.(next);
      },
    }),
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFacetedRowModel: getFacetedRowModel(),
    getFacetedUniqueValues: getFacetedUniqueValues(),
  });

  const currentPage = isServerSide
    ? (pagination?.pageIndex ?? 0) + 1
    : table.getState().pagination.pageIndex + 1;
  const totalPages = isServerSide
    ? (pageCount ?? 1)
    : table.getPageCount();
  const pageSize = isServerSide
    ? (pagination?.pageSize ?? 25)
    : table.getState().pagination.pageSize;
  const displayTotal = totalRows ?? data.length;

  return (
    <div className="flex flex-col gap-3">
      {/* ── Toolbar ── */}
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        {/* Search */}
        <div className="relative flex-1 max-w-sm">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder={searchPlaceholder}
            value={globalFilter}
            onChange={(e) => onGlobalFilterChange?.(e.target.value)}
            className="pl-9 h-9 text-sm"
            id="datatable-search"
          />
        </div>

        {/* Right side: custom toolbar + column visibility + refresh */}
        <div className="flex items-center gap-2">
          {toolbar}

          {/* Column visibility toggle */}
          <DropdownMenu>
            <DropdownMenuTrigger>
              <Button
                variant="outline"
                size="sm"
                className="h-9 gap-1.5 text-xs"
                id="btn-columns-visibility"
              >
                <SlidersHorizontal className="h-3.5 w-3.5" />
                Columns
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-40">
              {table
                .getAllColumns()
                .filter((col) => col.getCanHide())
                .map((col) => (
                  <DropdownMenuCheckboxItem
                    key={col.id}
                    className="text-xs capitalize"
                    checked={col.getIsVisible()}
                    onCheckedChange={(v) => col.toggleVisibility(!!v)}
                  >
                    {col.id}
                  </DropdownMenuCheckboxItem>
                ))}
            </DropdownMenuContent>
          </DropdownMenu>

          {/* Refresh */}
          {onRefresh && (
            <Button
              variant="outline"
              size="sm"
              className="h-9 gap-1.5 text-xs"
              onClick={onRefresh}
              disabled={isLoading}
              id="btn-table-refresh"
            >
              <RefreshCw className={`h-3.5 w-3.5 ${isLoading ? "animate-spin" : ""}`} />
              Refresh
            </Button>
          )}
        </div>
      </div>

      {/* ── Table ── */}
      <div className="rounded-lg border bg-card overflow-hidden">
        <div className="overflow-x-auto">
          <Table>
            <TableHeader>
              {table.getHeaderGroups().map((hg) => (
                <TableRow key={hg.id} className="bg-muted/40 hover:bg-muted/40">
                  {hg.headers.map((header) => (
                    <TableHead
                      key={header.id}
                      className="text-xs font-semibold uppercase tracking-wide text-muted-foreground whitespace-nowrap"
                      style={{ width: header.getSize() !== 150 ? header.getSize() : undefined }}
                    >
                      {header.isPlaceholder
                        ? null
                        : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  ))}
                </TableRow>
              ))}
            </TableHeader>
            <TableBody>
              {isLoading ? (
                Array.from({ length: SKELETON_ROWS }).map((_, i) => (
                  <TableRow key={i}>
                    {columns.map((_, ci) => (
                      <TableCell key={ci}>
                        <Skeleton className="h-4 w-full" />
                      </TableCell>
                    ))}
                  </TableRow>
                ))
              ) : table.getRowModel().rows.length ? (
                table.getRowModel().rows.map((row) => (
                  <TableRow
                    key={row.id}
                    data-state={row.getIsSelected() && "selected"}
                    className="text-sm hover:bg-muted/30 transition-colors"
                  >
                    {row.getVisibleCells().map((cell) => (
                      <TableCell key={cell.id} className="py-2.5 whitespace-nowrap">
                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                      </TableCell>
                    ))}
                  </TableRow>
                ))
              ) : (
                <TableRow>
                  <TableCell colSpan={columns.length} className="h-32 text-center text-muted-foreground text-sm">
                    {emptyMessage}
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </div>
      </div>

      {/* ── Pagination ── */}
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        {/* Info */}
        <p className="text-xs text-muted-foreground">
          Showing{" "}
          <span className="font-medium">
            {displayTotal === 0
              ? 0
              : (currentPage - 1) * pageSize + 1}
          </span>{" "}
          to{" "}
          <span className="font-medium">
            {Math.min(currentPage * pageSize, displayTotal)}
          </span>{" "}
          of <span className="font-medium">{displayTotal}</span> records
        </p>

        <div className="flex items-center gap-3">
          {/* Page size */}
          <div className="flex items-center gap-2">
            <span className="text-xs text-muted-foreground">Rows per page</span>
            <Select
              value={String(pageSize)}
              onValueChange={(v) => {
                if (isServerSide) {
                  onPaginationChange?.({ pageIndex: 0, pageSize: Number(v) });
                } else {
                  table.setPageSize(Number(v));
                }
              }}
            >
              <SelectTrigger className="h-8 w-16 text-xs" id="select-page-size">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {PAGE_SIZE_OPTIONS.map((s) => (
                  <SelectItem key={s} value={String(s)} className="text-xs">
                    {s}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {/* Navigation */}
          <div className="flex items-center gap-1">
            <Button
              variant="outline"
              size="icon"
              className="h-8 w-8"
              onClick={() => {
                isServerSide
                  ? onPaginationChange?.({ ...pagination!, pageIndex: 0 })
                  : table.setPageIndex(0);
              }}
              disabled={currentPage <= 1 || isLoading}
              id="btn-page-first"
              aria-label="First page"
            >
              <ChevronsLeft className="h-3.5 w-3.5" />
            </Button>
            <Button
              variant="outline"
              size="icon"
              className="h-8 w-8"
              onClick={() => {
                isServerSide
                  ? onPaginationChange?.({ ...pagination!, pageIndex: pagination!.pageIndex - 1 })
                  : table.previousPage();
              }}
              disabled={currentPage <= 1 || isLoading}
              id="btn-page-prev"
              aria-label="Previous page"
            >
              <ChevronLeft className="h-3.5 w-3.5" />
            </Button>

            <span className="text-xs text-muted-foreground px-2">
              Page {currentPage} / {totalPages}
            </span>

            <Button
              variant="outline"
              size="icon"
              className="h-8 w-8"
              onClick={() => {
                isServerSide
                  ? onPaginationChange?.({ ...pagination!, pageIndex: pagination!.pageIndex + 1 })
                  : table.nextPage();
              }}
              disabled={currentPage >= totalPages || isLoading}
              id="btn-page-next"
              aria-label="Next page"
            >
              <ChevronRight className="h-3.5 w-3.5" />
            </Button>
            <Button
              variant="outline"
              size="icon"
              className="h-8 w-8"
              onClick={() => {
                isServerSide
                  ? onPaginationChange?.({ ...pagination!, pageIndex: totalPages - 1 })
                  : table.setPageIndex(table.getPageCount() - 1);
              }}
              disabled={currentPage >= totalPages || isLoading}
              id="btn-page-last"
              aria-label="Last page"
            >
              <ChevronsRight className="h-3.5 w-3.5" />
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}
