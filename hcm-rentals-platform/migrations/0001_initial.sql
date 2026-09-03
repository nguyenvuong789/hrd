PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS listings (
  list_id INTEGER PRIMARY KEY,
  city TEXT NOT NULL DEFAULT 'hcm',
  category TEXT NOT NULL CHECK (category IN ('houses', 'apartments')),
  subject TEXT NOT NULL,
  body TEXT NOT NULL DEFAULT '',
  price INTEGER NOT NULL,
  price_string TEXT NOT NULL DEFAULT '',
  list_time TEXT NOT NULL,
  address TEXT NOT NULL DEFAULT '',
  district TEXT NOT NULL DEFAULT '',
  size REAL,
  rooms INTEGER,
  toilets INTEGER,
  latitude REAL,
  longitude REAL,
  account_id TEXT,
  account_name TEXT NOT NULL DEFAULT '',
  phone TEXT NOT NULL DEFAULT '',
  source_url TEXT NOT NULL,
  image_urls TEXT NOT NULL DEFAULT '[]',
  image_paths TEXT NOT NULL DEFAULT '[]',
  is_furnished INTEGER NOT NULL DEFAULT 1,
  seller_type TEXT NOT NULL DEFAULT 'u',
  first_seen_at TEXT NOT NULL,
  last_seen_at TEXT NOT NULL,
  last_checked_at TEXT NOT NULL,
  is_active INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS listing_price_history (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  list_id INTEGER NOT NULL,
  old_price INTEGER NOT NULL,
  new_price INTEGER NOT NULL,
  changed_at TEXT NOT NULL,
  run_id INTEGER,
  FOREIGN KEY (list_id) REFERENCES listings(list_id)
);

CREATE TABLE IF NOT EXISTS listing_events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  list_id INTEGER NOT NULL,
  event_type TEXT NOT NULL CHECK (event_type IN ('new', 'disappeared', 'reactivated')),
  occurred_at TEXT NOT NULL,
  run_id INTEGER,
  subject_snapshot TEXT NOT NULL DEFAULT '',
  price_snapshot INTEGER,
  FOREIGN KEY (list_id) REFERENCES listings(list_id)
);

CREATE TABLE IF NOT EXISTS crawl_runs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  city TEXT NOT NULL DEFAULT 'hcm',
  started_at TEXT NOT NULL,
  completed_at TEXT,
  status TEXT NOT NULL CHECK (status IN ('running', 'success', 'partial', 'failed')),
  raw_count INTEGER NOT NULL DEFAULT 0,
  filtered_count INTEGER NOT NULL DEFAULT 0,
  total_found INTEGER NOT NULL DEFAULT 0,
  new_count INTEGER NOT NULL DEFAULT 0,
  new_houses_count INTEGER NOT NULL DEFAULT 0,
  new_apartments_count INTEGER NOT NULL DEFAULT 0,
  disappeared_count INTEGER NOT NULL DEFAULT 0,
  price_change_count INTEGER NOT NULL DEFAULT 0,
  api_error_count INTEGER NOT NULL DEFAULT 0,
  image_error_count INTEGER NOT NULL DEFAULT 0,
  processing_error_count INTEGER NOT NULL DEFAULT 0,
  error_message TEXT NOT NULL DEFAULT '',
  duration_ms INTEGER
);

CREATE TABLE IF NOT EXISTS run_listings (
  run_id INTEGER NOT NULL,
  list_id INTEGER NOT NULL,
  PRIMARY KEY (run_id, list_id),
  FOREIGN KEY (run_id) REFERENCES crawl_runs(id),
  FOREIGN KEY (list_id) REFERENCES listings(list_id)
);

CREATE TABLE IF NOT EXISTS subscribers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  filters_json TEXT NOT NULL DEFAULT '{}',
  verify_token_hash TEXT NOT NULL,
  verified_at TEXT,
  alert_enabled INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL,
  last_alert_at TEXT
);

CREATE TABLE IF NOT EXISTS subscriber_contact_tokens (
  token_hash TEXT PRIMARY KEY,
  subscriber_id INTEGER NOT NULL,
  expires_at TEXT NOT NULL,
  FOREIGN KEY (subscriber_id) REFERENCES subscribers(id)
);

CREATE INDEX IF NOT EXISTS idx_listings_active_category ON listings(city, is_active, category);
CREATE INDEX IF NOT EXISTS idx_listings_district_price ON listings(district, price);
CREATE INDEX IF NOT EXISTS idx_listings_seen ON listings(first_seen_at, last_seen_at);
CREATE INDEX IF NOT EXISTS idx_price_history_changed ON listing_price_history(changed_at);
CREATE INDEX IF NOT EXISTS idx_events_occurred ON listing_events(event_type, occurred_at);
CREATE INDEX IF NOT EXISTS idx_runs_started ON crawl_runs(city, started_at DESC);
CREATE INDEX IF NOT EXISTS idx_subscribers_verified ON subscribers(verified_at, alert_enabled);
