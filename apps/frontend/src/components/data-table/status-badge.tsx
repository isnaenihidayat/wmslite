import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

export type WmsStatus =
  | "created"
  | "started"
  | "assigned"
  | "inprogress"
  | "acknowledged"
  | "successful"
  | "Warehouse in Transit"
  | "failed"
  | "canceled"
  | "cancelled"
  | "declined"
  | "suspended"
  | "blocked"
  | string;

const STATUS_CONFIG: Record<
  string,
  { label: string; className: string }
> = {
  created: {
    label: "Created",
    className: "bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300",
  },
  started: {
    label: "Started",
    className: "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300",
  },
  assigned: {
    label: "Assigned",
    className: "bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300",
  },
  inprogress: {
    label: "In Progress",
    className: "bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300",
  },
  acknowledged: {
    label: "Acknowledged",
    className: "bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/30 dark:text-sky-300",
  },
  successful: {
    label: "Successful",
    className: "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300",
  },
  "Warehouse in Transit": {
    label: "WH in Transit",
    className: "bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300",
  },
  failed: {
    label: "Failed",
    className: "bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300",
  },
  canceled: {
    label: "Canceled",
    className: "bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300",
  },
  cancelled: {
    label: "Cancelled",
    className: "bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300",
  },
  declined: {
    label: "Declined",
    className: "bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300",
  },
  suspended: {
    label: "Suspended",
    className: "bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300",
  },
  blocked: {
    label: "Blocked",
    className: "bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300",
  },
};

interface StatusBadgeProps {
  status: WmsStatus;
  className?: string;
}

export function StatusBadge({ status, className }: StatusBadgeProps) {
  const config = STATUS_CONFIG[status] ?? {
    label: status,
    className: "bg-gray-100 text-gray-700 border-gray-200",
  };

  return (
    <Badge
      variant="outline"
      className={cn(
        "text-[10px] font-semibold px-2 py-0.5 capitalize border",
        config.className,
        className
      )}
    >
      {config.label}
    </Badge>
  );
}
