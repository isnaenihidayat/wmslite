import { createAuthenticatedClient } from "@/lib/api/client";

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

export async function fetchLocations(token: string): Promise<Location[]> {
  const client = createAuthenticatedClient(token);
  const res = await client.get<{ data: Location[] }>("/master/locations");
  return res.data.data;
}

export async function fetchCategories(token: string): Promise<ProductCategory[]> {
  const client = createAuthenticatedClient(token);
  const res = await client.get<{ data: ProductCategory[] }>("/master/categories");
  return res.data.data;
}

export async function fetchRecipients(token: string): Promise<Recipient[]> {
  const client = createAuthenticatedClient(token);
  const res = await client.get<{ data: Recipient[] }>("/master/recipients");
  return res.data.data;
}
