import type { Metadata } from "next";
import { auth } from "@/lib/auth/auth";
import {
  Ship,
  PackageCheck,
  PackageOpen,
  ArrowLeftRight,
  Clock,
  CheckCircle2,
  TrendingUp,
  Warehouse,
} from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

export const metadata: Metadata = {
  title: "Dashboard",
};

// Stats card component
function StatsCard({
  title,
  value,
  description,
  icon: Icon,
  colorClass,
  iconColor,
}: {
  title: string;
  value: string | number;
  description?: string;
  icon: React.ComponentType<{ className?: string }>;
  colorClass: string;
  iconColor: string;
}) {
  return (
    <Card className={`border ${colorClass} transition-all hover:shadow-md`}>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium text-muted-foreground">
          {title}
        </CardTitle>
        <div className={`rounded-lg p-2 ${iconColor}`}>
          <Icon className="h-4 w-4" />
        </div>
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{value}</div>
        {description && (
          <p className="mt-1 text-xs text-muted-foreground">{description}</p>
        )}
      </CardContent>
    </Card>
  );
}

export default async function DashboardPage() {
  const session = await auth();
  const name = session?.user?.name?.split(" ")[0] || "User";

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">
            Welcome back, {name} 👋
          </h1>
          <p className="text-sm text-muted-foreground">
            Here&apos;s what&apos;s happening at the warehouse today.
          </p>
        </div>
        <Badge variant="outline" className="hidden sm:flex items-center gap-1">
          <div className="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
          Live
        </Badge>
      </div>

      {/* Stats Grid */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard
          title="Total Shipments"
          value="—"
          description="All active shipments"
          icon={Ship}
          colorClass="stats-blue"
          iconColor="bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
        />
        <StatsCard
          title="Inbound (Transit)"
          value="—"
          description="Warehouse in transit"
          icon={PackageCheck}
          colorClass="stats-orange"
          iconColor="bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400"
        />
        <StatsCard
          title="Active Outbound"
          value="—"
          description="In progress today"
          icon={PackageOpen}
          colorClass="stats-purple"
          iconColor="bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
        />
        <StatsCard
          title="Stock Movements"
          value="—"
          description="This month"
          icon={ArrowLeftRight}
          colorClass="stats-green"
          iconColor="bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400"
        />
      </div>

      {/* Quick Status Overview */}
      <div className="grid gap-4 lg:grid-cols-2">
        {/* Inbound Queue */}
        <Card>
          <CardHeader className="flex flex-row items-center gap-2">
            <Clock className="h-4 w-4 text-muted-foreground" />
            <CardTitle className="text-sm font-semibold">Inbound Queue</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex h-32 items-center justify-center rounded-lg border border-dashed">
              <p className="text-sm text-muted-foreground">
                Loading inbound data...
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Outbound Queue */}
        <Card>
          <CardHeader className="flex flex-row items-center gap-2">
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
            <CardTitle className="text-sm font-semibold">Outbound Queue</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex h-32 items-center justify-center rounded-lg border border-dashed">
              <p className="text-sm text-muted-foreground">
                Loading outbound data...
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Status Summary */}
      <Card>
        <CardHeader className="flex flex-row items-center gap-2">
          <Warehouse className="h-4 w-4 text-muted-foreground" />
          <CardTitle className="text-sm font-semibold">Warehouse Status</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-3 sm:grid-cols-3">
            {[
              { label: "Created", class: "status-created", count: "—" },
              { label: "In Transit", class: "status-transit", count: "—" },
              { label: "Completed", class: "status-successful", count: "—" },
            ].map((item) => (
              <div
                key={item.label}
                className={`flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium ${item.class}`}
              >
                <span>{item.label}</span>
                <span className="font-bold">{item.count}</span>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Recent Activity Placeholder */}
      <Card>
        <CardHeader className="flex flex-row items-center gap-2">
          <CheckCircle2 className="h-4 w-4 text-muted-foreground" />
          <CardTitle className="text-sm font-semibold">Recent Activity</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex h-40 items-center justify-center rounded-lg border border-dashed">
            <p className="text-sm text-muted-foreground">
              Activity feed will appear here once connected to the API.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
