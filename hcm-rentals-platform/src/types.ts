export interface Listing {
  list_id: number;
  category: "houses" | "apartments";
  subject: string;
  body?: string;
  price: number;
  price_string: string;
  list_time: string;
  address: string;
  district: string;
  size?: number | null;
  rooms?: number | null;
  toilets?: number | null;
  source_url: string;
  images: string[];
  first_seen_at: string;
  last_seen_at: string;
  is_active: number;
}

export interface Run {
  id: number;
  started_at: string;
  completed_at?: string;
  status: "running" | "success" | "partial" | "failed";
  raw_count: number;
  filtered_count: number;
  total_found: number;
  new_count: number;
  new_houses_count: number;
  new_apartments_count: number;
  disappeared_count: number;
  price_change_count: number;
  error_message?: string;
  duration_ms?: number;
}

export interface Stats {
  counts: { total: number; active: number; houses: number; apartments: number };
  districts: Array<{ district: string; count: number }>;
  latest_run: Run | null;
}

export interface ChangeEvent {
  id: number;
  list_id: number;
  event_type: "new" | "disappeared" | "reactivated";
  occurred_at: string;
  subject_snapshot: string;
  price_snapshot: number;
  category: Listing["category"];
  district: string;
  source_url: string;
}

export interface PriceChange {
  id: number;
  list_id: number;
  old_price: number;
  new_price: number;
  changed_at: string;
  subject: string;
  category: Listing["category"];
  district: string;
  source_url: string;
}
