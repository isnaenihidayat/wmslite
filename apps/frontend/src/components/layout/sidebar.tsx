"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import { useUIStore } from "@/stores/ui.store";
import {
  LayoutDashboard,
  Ship,
  PackageCheck,
  PackageOpen,
  ArrowLeftRight,
  Activity,
  BarChart3,
  MapPin,
  Tag,
  Mail,
  Smartphone,
  Users,
  Settings,
  ChevronLeft,
  ChevronRight,
  Warehouse,
} from "lucide-react";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import { Separator } from "@/components/ui/separator";
import { useSession } from "next-auth/react";

interface NavItem {
  title: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  adminOnly?: boolean;
  types?: number[];
}

const mainNav: NavItem[] = [
  { title: "Dashboard", href: "/dashboard", icon: LayoutDashboard },
  { title: "Shipment", href: "/shipment", icon: Ship },
  { title: "Inbound", href: "/inbound", icon: PackageOpen },
  { title: "Outbound", href: "/outbound", icon: PackageCheck },
  { title: "Moving", href: "/moving", icon: ArrowLeftRight, types: [1, 3] },
  { title: "Monitoring", href: "/monitoring", icon: Activity },
  { title: "Reports", href: "/reports", icon: BarChart3 },
];

const masterNav: NavItem[] = [
  { title: "Locations", href: "/master/locations", icon: MapPin, types: [1] },
  { title: "Product Category", href: "/master/product-category", icon: Tag, types: [1] },
  { title: "Recipient", href: "/master/recipient", icon: Mail, types: [1] },
  { title: "APK Checker", href: "/master/apk", icon: Smartphone, types: [1] },
];

const adminNav: NavItem[] = [
  { title: "Users", href: "/admin/users", icon: Users, adminOnly: true },
];

export function AppSidebar() {
  const pathname = usePathname();
  const { sidebarCollapsed, toggleSidebar } = useUIStore();
  const { data: session } = useSession();

  const userType = session?.user?.type ?? 0;
  const isAdmin = session?.user?.admin === 1;

  const filterNav = (items: NavItem[]) =>
    items.filter((item) => {
      if (item.adminOnly && !isAdmin) return false;
      if (item.types && !item.types.includes(userType) && !isAdmin) return false;
      return true;
    });

  const renderNavItem = (item: NavItem) => {
    const isActive = pathname === item.href || pathname.startsWith(item.href + "/");
    const Icon = item.icon;

    const linkEl = (
      <Link
        href={item.href}
        className={cn(
          "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-150",
          "hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
          isActive
            ? "bg-sidebar-primary text-sidebar-primary-foreground shadow-sm"
            : "text-sidebar-foreground/80"
        )}
      >
        <Icon className={cn("shrink-0", sidebarCollapsed ? "h-5 w-5" : "h-4 w-4")} />
        {!sidebarCollapsed && <span className="truncate">{item.title}</span>}
      </Link>
    );

    if (sidebarCollapsed) {
      return (
        <Tooltip key={item.href}>
          <TooltipTrigger>{linkEl}</TooltipTrigger>
          <TooltipContent side="right">{item.title}</TooltipContent>
        </Tooltip>
      );
    }

    return <div key={item.href}>{linkEl}</div>;
  };

  return (
    <aside
      className={cn(
        "flex flex-col border-r border-sidebar-border bg-sidebar transition-all duration-300",
        sidebarCollapsed ? "w-16" : "w-60"
      )}
    >
      {/* Logo */}
      <div className={cn(
        "flex h-14 items-center border-b border-sidebar-border px-3",
        sidebarCollapsed ? "justify-center" : "justify-between gap-2"
      )}>
        <Link href="/dashboard" className="flex items-center gap-2 min-w-0">
          <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary">
            <Warehouse className="h-4 w-4 text-primary-foreground" />
          </div>
          {!sidebarCollapsed && (
            <div className="min-w-0">
              <p className="truncate text-sm font-bold text-sidebar-foreground">WMS Lite</p>
              <p className="truncate text-[10px] text-sidebar-foreground/50">Warehouse Manager</p>
            </div>
          )}
        </Link>
        {!sidebarCollapsed && (
          <button
            onClick={toggleSidebar}
            className="rounded-md p-1 text-sidebar-foreground/50 hover:bg-sidebar-accent hover:text-sidebar-foreground"
          >
            <ChevronLeft className="h-4 w-4" />
          </button>
        )}
      </div>

      {/* Collapsed toggle */}
      {sidebarCollapsed && (
        <button
          onClick={toggleSidebar}
          className="mx-auto mt-2 rounded-md p-1 text-sidebar-foreground/50 hover:bg-sidebar-accent hover:text-sidebar-foreground"
        >
          <ChevronRight className="h-4 w-4" />
        </button>
      )}

      {/* Navigation */}
      <nav className="flex-1 overflow-y-auto custom-scrollbar px-2 py-3 space-y-1">
        {/* Main */}
        {filterNav(mainNav).map(renderNavItem)}

        {/* Master Data */}
        {filterNav(masterNav).length > 0 && (
          <>
            <div className="pt-3">
              {!sidebarCollapsed && (
                <p className="mb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/40">
                  Master Data
                </p>
              )}
              <Separator className="mb-2 bg-sidebar-border" />
            </div>
            {filterNav(masterNav).map(renderNavItem)}
          </>
        )}

        {/* Admin */}
        {isAdmin && filterNav(adminNav).length > 0 && (
          <>
            <div className="pt-3">
              {!sidebarCollapsed && (
                <p className="mb-1 px-3 text-[10px] font-semibold uppercase tracking-wider text-sidebar-foreground/40">
                  Admin
                </p>
              )}
              <Separator className="mb-2 bg-sidebar-border" />
            </div>
            {filterNav(adminNav).map(renderNavItem)}
          </>
        )}
      </nav>

      {/* Settings footer */}
      <div className="border-t border-sidebar-border px-2 py-2">
        {renderNavItem({ title: "Settings", href: "/settings", icon: Settings })}
      </div>
    </aside>
  );
}
