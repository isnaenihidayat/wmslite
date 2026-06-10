import { apiClient } from "@/lib/api/client";

// ── Types ────────────────────────────────────────────────────────────────────

export interface Location {
  loc_id: string;
  loc_name: string;
  loc_descr: string | null;
}

export interface ProductCategory {
  id: number;
  name: string;
}

export interface Recipient {
  id: number;
  name: string;
  email: string | null;
  created_at: string | null;
}

// ── Fetchers ─────────────────────────────────────────────────────────────────

export async function fetchLocations(): Promise<Location[]> {
  const res = await apiClient.get<{ data: Location[] }>("/master/locations");
  return res.data.data;
}

export async function fetchCategories(): Promise<ProductCategory[]> {
  const res = await apiClient.get<{ data: ProductCategory[] }>("/master/categories");
  return res.data.data;
}

export async function fetchRecipients(): Promise<Recipient[]> {
  const res = await apiClient.get<{ data: Recipient[] }>("/master/recipients");
  return res.data.data;
}
