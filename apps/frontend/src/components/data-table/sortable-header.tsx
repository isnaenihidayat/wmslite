import { Column } from "@tanstack/react-table";
import { Button } from "@/components/ui/button";
import { ArrowDown, ArrowUp, ArrowUpDown } from "lucide-react";
import { cn } from "@/lib/utils";

interface SortableHeaderProps<T> {
  column: Column<T, unknown>;
  label: string;
  className?: string;
}

export function SortableHeader<T>({ column, label, className }: SortableHeaderProps<T>) {
  const sorted = column.getIsSorted();
  return (
    <Button
      variant="ghost"
      size="sm"
      className={cn("-ml-3 h-8 gap-1 text-xs font-semibold", className)}
      onClick={() => column.toggleSorting(sorted === "asc")}
    >
      {label}
      {sorted === "asc" ? (
        <ArrowUp className="h-3 w-3" />
      ) : sorted === "desc" ? (
        <ArrowDown className="h-3 w-3" />
      ) : (
        <ArrowUpDown className="h-3 w-3 text-muted-foreground" />
      )}
    </Button>
  );
}
