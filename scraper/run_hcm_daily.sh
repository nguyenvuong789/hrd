#!/bin/sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
LOCK_DIR="$ROOT_DIR/data/.hcm-crawl.lock"
if ! mkdir "$LOCK_DIR" 2>/dev/null; then
  echo "another HCM crawl is already running" >&2
  exit 75
fi
trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT INT TERM

LOG_DIR="$ROOT_DIR/data/crawl-logs"
mkdir -p "$LOG_DIR"
STAMP=$(date -u +%Y%m%dT%H%M%SZ)
cd "$ROOT_DIR"

python3 scraper/nhatot_rentals.py \
  --days "${DAYS:-365}" \
  --category houses \
  --min-price 10000000 \
  --region-v2 13000 \
  --city-slug ho-chi-minh \
  --city-label "TP.HCM" \
  --db data/nhatot-hcm-houses-365d.sqlite \
  --csv data/nhatot-hcm-houses-365d.csv \
  --images-dir data/nhatot-hcm-houses-365d-images \
  --dashboard output/nhatot-hcm-houses-365d-dashboard.html \
  --download-workers "${DOWNLOAD_WORKERS:-6}" 2>&1 | tee "$LOG_DIR/hcm-houses-$STAMP.log"

python3 scraper/nhatot_rentals.py \
  --days "${DAYS:-365}" \
  --category apartments \
  --min-price 8000000 \
  --region-v2 13000 \
  --city-slug ho-chi-minh \
  --city-label "TP.HCM" \
  --db data/nhatot-hcm-apartments-365d.sqlite \
  --csv data/nhatot-hcm-apartments-365d.csv \
  --images-dir data/nhatot-hcm-apartments-365d-images \
  --dashboard output/nhatot-hcm-apartments-365d-dashboard.html \
  --download-workers "${DOWNLOAD_WORKERS:-6}" 2>&1 | tee "$LOG_DIR/hcm-apartments-$STAMP.log"

python3 - <<'PY'
import csv
from pathlib import Path
from scraper.nhatot_rentals import build_dashboard

rows = []
for csv_path in (Path("data/nhatot-hcm-houses-365d.csv"), Path("data/nhatot-hcm-apartments-365d.csv")):
    with csv_path.open(encoding="utf-8-sig", newline="") as handle:
        rows.extend(csv.DictReader(handle))
rows.sort(key=lambda row: row.get("list_time", ""), reverse=True)
build_dashboard(Path("output/nhatot-hcm-dashboard.html"), rows, 365, "TP.HCM")
print(f"Combined HCM dashboard: {len(rows)} listings")
PY
