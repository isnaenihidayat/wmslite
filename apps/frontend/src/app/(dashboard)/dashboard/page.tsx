"use client";

import { useMemo } from "react";
import { useSession } from "next-auth/react";
import Link from "next/link";
import { useDashboardStats } from "@/hooks/use-dashboard";
import { StatusBadge } from "@/components/data-table/status-badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Button } from "@/components/ui/button";
import {
  Ship,
  PackageOpen,
  PackageCheck,
  CheckCircle2,
  TrendingUp,
  Clock,
  Warehouse,
  RefreshCw,
  ArrowRight,
  AlertCircle,
} from "lucide-react";

// ── Animated counter card
function StatsCard({
  title,
  value,
  description,
  icon: Icon,
  href,
  accent,
  isLoading,
}: {
  title: string;
  value: number | string;
  description?: string;
  icon: React.ComponentType<{ className?: string }>;
  href?: string;
  accent: string;
  isLoading?: boolean;
}) {
  const inner = (
    <Card className={`group relative overflow-hidden border transition-all hover:shadow-lg hover:-translate-y-0.5 ${accent}`}>
      {/* Subtle gradient accent bar */}
      <div className="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent via-current to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium text-muted-foreground">{title}</CardTitle>
        <div className="rounded-lg p-2 bg-current/5">
          <Icon className="h-4 w-4 opacity-70" />
        </div>
      </CardHeader>
      <CardContent>
        {isLoading ? (
          <Skeleton className="h-8 w-20" />
        ) : (
          <div className="text-2xl font-bold tabular-nums">{value}</div>
        )}
        {description && (
          <p className="mt-1 text-xs text-muted-foreground">{description}</p>
        )}
        {href && (
          <div className="mt-2 flex items-center gap-1 text-xs text-muted-foreground group-hover:text-foreground transition-colors">
            View all <ArrowRight className="h-3 w-3" />
          </div>
        )}
      </CardContent>
    </Card>
  );

  return href ? <Link href={href}>{inner}</Link> : inner;
}

// ── Recent activity row
function RecentRow({ label, sublabel, status, date }: {
  label: string;
  sublabel?: string;
  status: string;
  date: string;
}) {
  return (
    <div className="flex items-center justify-between gap-3 py-2.5 border-b last:border-0">
      <div className="min-w-0">
        <p className="truncate text-sm font-mono font-semibold text-foreground">{label || "—"}</p>
        {sublabel && (
          <p className="truncate text-xs text-muted-foreground">{sublabel}</p>
        )}
      </div>
      <div className="flex shrink-0 items-center gap-2">
        <span className="text-[10px] text-muted-foreground whitespace-nowrap hidden sm:block">
          {date?.slice(0, 10) || ""}
        </span>
        <StatusBadge status={status} />
      </div>
    </div>
  );
}

export default function DashboardPage() {
  const { data: session } = useSession();
  const name = session?.user?.name?.split(" ")[0] || "User";
  const { data: stats, isLoading, isError, refetch, dataUpdatedAt } = useDashboardStats(session?.user?.accessToken);

  const lastUpdated = useMemo(() => {
    if (!dataUpdatedAt) return null;
    return new Date(dataUpdatedAt).toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }, [dataUpdatedAt]);

  return (
    <div className="space-y-6">
      {/* ── Header ── */}
      <div className="flex items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">
            Selamat datang, {name} 👋
          </h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            Berikut ringkasan aktivitas warehouse hari ini.
          </p>
        </div>
        <div className="flex items-center gap-2 shrink-0">
          {lastUpdated && (
            <span className="hidden sm:inline text-[10px] text-muted-foreground">
              Updated {lastUpdated}
            </span>
          )}
          <Badge variant="outline" className="flex items-center gap-1.5">
            <div className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
            Live
          </Badge>
          <Button
            size="icon"
            variant="ghost"
            className="h-8 w-8"
            onClick={() => refetch()}
            title="Refresh"
            id="btn-dashboard-refresh"
          >
            <RefreshCw className="h-3.5 w-3.5" />
          </Button>
        </div>
      </div>

      {/* ── Error state ── */}
      {isError && (
        <div className="flex items-center gap-3 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">
          <AlertCircle className="h-4 w-4 shrink-0" />
          Gagal memuat data. Pastikan Yii backend aktif dan CORS dikonfigurasi.
        </div>
      )}

      {/* ── Stats Grid ── */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard
          title="Total Shipment"
          value={stats?.totalShipments ?? "—"}
          description="Semua shipment aktif"
          icon={Ship}
          href="/shipment"
          accent="border-blue-200/60 dark:border-blue-900/40"
          isLoading={isLoading}
        />
        <StatsCard
          title="Inbound (Total)"
          value={stats?.inboundInTransit ?? "—"}
          description="Semua record inbound"
          icon={PackageOpen}
          href="/inbound"
          accent="border-amber-200/60 dark:border-amber-900/40"
          isLoading={isLoading}
        />
        <StatsCard
          title="Outbound Aktif"
          value={stats?.activeOutbound ?? "—"}
          description="Semua record outbound"
          icon={PackageCheck}
          href="/outbound"
          accent="border-purple-200/60 dark:border-purple-900/40"
          isLoading={isLoading}
        />
        <StatsCard
          title="Outbound Selesai"
          value={stats?.successfulOutbound ?? "—"}
          description="Status successful (halaman ini)"
          icon={CheckCircle2}
          accent="border-emerald-200/60 dark:border-emerald-900/40"
          isLoading={isLoading}
        />
      </div>

      {/* ── Recent lists ── */}
      <div className="grid gap-4 lg:grid-cols-2">
        {/* Recent Shipments */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-3">
            <div className="flex items-center gap-2">
              <Clock className="h-4 w-4 text-muted-foreground" />
              <CardTitle className="text-sm font-semibold">Shipment Terbaru</CardTitle>
            </div>
            <Link href="/shipment" className="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1">
              Lihat semua <ArrowRight className="h-3 w-3" />
            </Link>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <div className="space-y-2">
                {Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-10 w-full" />
                ))}
              </div>
            ) : (stats?.recentInbound ?? []).length > 0 ? (
              <div>
                {(stats?.recentInbound ?? []).map((item) => (
                  <RecentRow
                    key={item.id}
                    label={item.label}
                    sublabel={item.sublabel}
                    status={item.status}
                    date={item.date}
                  />
                ))}
              </div>
            ) : (
              <div className="flex h-28 items-center justify-center rounded-lg border border-dashed">
                <p className="text-sm text-muted-foreground">Belum ada data</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Recent Outbound */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-3">
            <div className="flex items-center gap-2">
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
              <CardTitle className="text-sm font-semibold">Outbound Terbaru</CardTitle>
            </div>
            <Link href="/outbound" className="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1">
              Lihat semua <ArrowRight className="h-3 w-3" />
            </Link>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <div className="space-y-2">
                {Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-10 w-full" />
                ))}
              </div>
            ) : (stats?.recentOutbound ?? []).length > 0 ? (
              <div>
                {(stats?.recentOutbound ?? []).map((item) => (
                  <RecentRow
                    key={item.id}
                    label={item.label}
                    sublabel={item.sublabel}
                    status={item.status}
                    date={item.date}
                  />
                ))}
              </div>
            ) : (
              <div className="flex h-28 items-center justify-center rounded-lg border border-dashed">
                <p className="text-sm text-muted-foreground">Belum ada data</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* ── Warehouse Status breakdown ── */}
      <Card>
        <CardHeader className="flex flex-row items-center gap-2 pb-3">
          <Warehouse className="h-4 w-4 text-muted-foreground" />
          <CardTitle className="text-sm font-semibold">Outbound Status Breakdown</CardTitle>
          <span className="ml-auto text-[10px] text-muted-foreground">(halaman pertama)</span>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="grid gap-2 sm:grid-cols-3">
              {[1, 2, 3].map((i) => <Skeleton key={i} className="h-12 w-full" />)}
            </div>
          ) : (stats?.statusBreakdown ?? []).length > 0 ? (
            <div className="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
              {(stats?.statusBreakdown ?? []).map((item) => (
                <div
                  key={item.status}
                  className="flex items-center justify-between rounded-lg border bg-card/50 px-3 py-2"
                >
                  <StatusBadge status={item.status} />
                  <span className="ml-2 text-sm font-bold tabular-nums">{item.count}</span>
                </div>
              ))}
            </div>
          ) : (
            <div className="flex h-24 items-center justify-center rounded-lg border border-dashed">
              <p className="text-sm text-muted-foreground">Belum ada data</p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* ── Quick Links ── */}
      <Card>
        <CardHeader className="pb-3">
          <CardTitle className="text-sm font-semibold">Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-wrap gap-2">
            {[
              { label: "Buka Reports", href: "/reports", icon: TrendingUp },
              { label: "Daftar Shipment", href: "/shipment", icon: Ship },
              { label: "Daftar Inbound", href: "/inbound", icon: PackageOpen },
              { label: "Daftar Outbound", href: "/outbound", icon: PackageCheck },
            ].map((q) => (
              <Link key={q.href} href={q.href}>
                <Button variant="outline" size="sm" className="gap-1.5" id={`btn-quick-${q.href.replace("/", "")}`}>
                  <q.icon className="h-3.5 w-3.5" />
                  {q.label}
                </Button>
              </Link>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
