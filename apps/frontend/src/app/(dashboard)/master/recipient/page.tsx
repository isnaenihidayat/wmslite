"use client";

import { useQuery } from "@tanstack/react-query";
import { fetchRecipients } from "@/lib/api/master.service";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Users, RefreshCw, Search, Mail } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { useState, useMemo } from "react";
import { formatDate } from "@/lib/utils";

function getInitials(name: string) {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
}

export default function RecipientPage() {
  const [search, setSearch] = useState("");

  const { data: recipients, isLoading, isFetching, refetch } = useQuery({
    queryKey: ["master", "recipients"],
    queryFn: fetchRecipients,
    staleTime: 5 * 60_000,
  });

  const filtered = useMemo(() => {
    if (!search.trim()) return recipients ?? [];
    const q = search.toLowerCase();
    return (recipients ?? []).filter(
      (r) =>
        r.name.toLowerCase().includes(q) ||
        (r.email ?? "").toLowerCase().includes(q)
    );
  }, [recipients, search]);

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10">
            <Users className="h-4 w-4 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Recipients</h1>
            <p className="text-xs text-muted-foreground">
              Master data penerima shipment
              {recipients && (
                <Badge variant="secondary" className="ml-2 text-[10px] px-1.5 py-0">
                  {recipients.length} records
                </Badge>
              )}
            </p>
          </div>
        </div>
        <Button
          variant="outline"
          size="sm"
          className="gap-2"
          onClick={() => refetch()}
          disabled={isFetching}
        >
          <RefreshCw className={`h-3.5 w-3.5 ${isFetching ? "animate-spin" : ""}`} />
          Refresh
        </Button>
      </div>

      {/* Search */}
      <div className="relative max-w-sm">
        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
        <Input
          placeholder="Cari nama atau email..."
          className="pl-8 h-9"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          id="input-recipient-search"
        />
      </div>

      {/* Grid */}
      {isLoading ? (
        <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          {Array.from({ length: 8 }).map((_, i) => (
            <Skeleton key={i} className="h-24 rounded-xl" />
          ))}
        </div>
      ) : filtered.length === 0 ? (
        <Card>
          <CardContent className="flex h-48 items-center justify-center">
            <div className="text-center">
              <Users className="mx-auto h-8 w-8 text-muted-foreground/30 mb-2" />
              <p className="text-sm text-muted-foreground">
                {search ? "Tidak ada penerima yang cocok." : "Belum ada data penerima."}
              </p>
            </div>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          {filtered.map((r) => (
            <Card
              key={r.id}
              className="group hover:shadow-md hover:-translate-y-0.5 transition-all border"
            >
              <CardContent className="flex items-center gap-3 p-4">
                <Avatar className="h-10 w-10 shrink-0">
                  <AvatarFallback className="bg-blue-500/10 text-blue-700 dark:text-blue-300 text-sm font-semibold">
                    {getInitials(r.name)}
                  </AvatarFallback>
                </Avatar>
                <div className="min-w-0">
                  <p className="text-sm font-semibold truncate" title={r.name}>
                    {r.name}
                  </p>
                  {r.email ? (
                    <div className="flex items-center gap-1 mt-0.5">
                      <Mail className="h-3 w-3 text-muted-foreground shrink-0" />
                      <p className="text-[11px] text-muted-foreground truncate">{r.email}</p>
                    </div>
                  ) : (
                    <p className="text-[11px] text-muted-foreground">No email</p>
                  )}
                  {r.created_at && (
                    <p className="text-[10px] text-muted-foreground/60 mt-0.5">
                      Joined {formatDate(r.created_at)}
                    </p>
                  )}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}
