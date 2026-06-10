"use client";

import { useQuery } from "@tanstack/react-query";
import { fetchCategories } from "@/lib/api/master.service";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Skeleton } from "@/components/ui/skeleton";
import { Tag, RefreshCw, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useState, useMemo } from "react";

// Cycle through accent colors
const ACCENT_COLORS = [
  "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  "bg-violet-500/10 text-violet-600 dark:text-violet-400",
  "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
  "bg-amber-500/10 text-amber-600 dark:text-amber-400",
  "bg-pink-500/10 text-pink-600 dark:text-pink-400",
  "bg-cyan-500/10 text-cyan-600 dark:text-cyan-400",
];

export default function ProductCategoryPage() {
  const [search, setSearch] = useState("");

  const { data: categories, isLoading, isFetching, refetch } = useQuery({
    queryKey: ["master", "categories"],
    queryFn: fetchCategories,
    staleTime: 5 * 60_000,
  });

  const filtered = useMemo(() => {
    if (!search.trim()) return categories ?? [];
    const q = search.toLowerCase();
    return (categories ?? []).filter((c) => c.name.toLowerCase().includes(q));
  }, [categories, search]);

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
            <Tag className="h-4 w-4 text-violet-600 dark:text-violet-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold leading-tight">Product Category</h1>
            <p className="text-xs text-muted-foreground">
              Master data kategori produk
              {categories && (
                <Badge variant="secondary" className="ml-2 text-[10px] px-1.5 py-0">
                  {categories.length} kategori
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
          placeholder="Cari kategori..."
          className="pl-8 h-9"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          id="input-category-search"
        />
      </div>

      {/* Grid */}
      {isLoading ? (
        <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
          {Array.from({ length: 10 }).map((_, i) => (
            <Skeleton key={i} className="h-20 rounded-xl" />
          ))}
        </div>
      ) : filtered.length === 0 ? (
        <Card>
          <CardContent className="flex h-48 items-center justify-center">
            <div className="text-center">
              <Tag className="mx-auto h-8 w-8 text-muted-foreground/30 mb-2" />
              <p className="text-sm text-muted-foreground">
                {search ? "Tidak ada kategori yang cocok." : "Belum ada data kategori."}
              </p>
            </div>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
          {filtered.map((cat, idx) => {
            const accent = ACCENT_COLORS[idx % ACCENT_COLORS.length];
            return (
              <Card
                key={cat.id}
                className="group hover:shadow-md hover:-translate-y-0.5 transition-all border"
              >
                <CardContent className="flex items-center gap-3 p-4">
                  <div className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${accent.split(" ").slice(0,1).join(" ")}`}>
                    <Tag className={`h-4 w-4 ${accent.split(" ").slice(1).join(" ")}`} />
                  </div>
                  <div className="min-w-0">
                    <p className="text-sm font-medium truncate" title={cat.name}>
                      {cat.name}
                    </p>
                    <p className="text-[10px] text-muted-foreground font-mono">
                      ID: {cat.id}
                    </p>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>
      )}
    </div>
  );
}
