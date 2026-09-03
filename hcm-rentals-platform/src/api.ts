import type { ChangeEvent, Listing, PriceChange, Run, Stats } from "./types";

const json = async <T,>(url: string): Promise<T> => {
  const response = await fetch(url);
  if (!response.ok) throw new Error(`${response.status} ${response.statusText}`);
  return response.json() as Promise<T>;
};

export async function loadListings(params: URLSearchParams) {
  return json<{ items: Listing[]; total: number }>(`/api/listings?${params}`);
}

export async function loadStats() {
  return json<Stats>("/api/stats");
}

export async function loadChanges() {
  return json<{ events: ChangeEvent[]; price_changes: PriceChange[] }>("/api/changes?city=hcm&date=today");
}

export async function loadRuns() {
  return json<{ items: Run[] }>("/api/runs?city=hcm&limit=14");
}

export async function loadDemo(): Promise<{ items: Listing[]; total: number; stats: Stats }> {
  return json("/demo-listings.json");
}
