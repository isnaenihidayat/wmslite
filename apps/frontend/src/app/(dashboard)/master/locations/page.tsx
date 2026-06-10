"use client";

import { useQuery } from "@tanstack/react-query";
import { fetchLocations } from "@/lib/api/master.service";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { MapPin, RefreshCw, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useState, useMemo } from "react";

export default function LocationsPage() {
  const [search, setSearch] = useState("");

  const { data: locations, isLoading, isFetching, refetch } = useQuery({
    queryKey: ["master", "locations"],
    queryFn: fetchLocations,
    staleTime: 5 * 60_000,
  });

  const filtered = useMemo(() => {
    if (!search.trim()) return locations ?? [];
    const q = search.toLowerCase();
    return (locations ?? []).filter(
      (l) =>
        l.loc_id.toLowerCase().includes(q) ||
        l.loc_name.toLowerCase().includes(q) ||
        (l.loc_descr ?? "").toLowerCase().includes(q)
    );
  }, [locations, search]);

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
            <MapPin className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Locations</h1>
            <p className="text-xs text-muted-foreground">
              Master data lokasi gudang
              {locations && (
                <Badge variant="secondary" className="ml-2 text-[10px] px-1.5 py-0">
                  {locations.length} records
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
          placeholder="Cari lokasi..."
          className="pl-8 h-9"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          id="input-location-search"
        />
      </div>

      {/* Grid */}
      {isLoading ? (
        <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          {Array.from({ length: 12 }).map((_, i) => (
            <Skeleton key={i} className="h-24 rounded-xl" />
          ))}
        </div>
      ) : filtered.length === 0 ? (
        <Card>
          <CardContent className="flex h-48 items-center justify-center">
            <div className="text-center">
              <MapPin className="mx-auto h-8 w-8 text-muted-foreground/30 mb-2" />
              <p className="text-sm text-muted-foreground">
                {search ? "Tidak ada lokasi yang cocok." : "Belum ada data lokasi."}
              </p>
            </div>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          {filtered.map((loc) => (
            <Card
              key={loc.loc_id}
              className="group hover:shadow-md hover:-translate-y-0.5 transition-all border"
            >
              <CardHeader className="pb-2 pt-4 px-4">
                <div className="flex items-start justify-between gap-2">
                  <CardTitle className="text-sm font-semibold font-mono truncate">
                    {loc.loc_id}
                  </CardTitle>
                  <div className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-emerald-500/10">
                    <MapPin className="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                  </div>
                </div>
              </CardHeader>
              <CardContent className="px-4 pb-4">
                <p className="text-sm font-medium truncate">{loc.loc_name}</p>
                {loc.loc_descr && (
                  <p className="mt-1 text-xs text-muted-foreground line-clamp-2">
                    {loc.loc_descr}
                  </p>
                )}
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}
