#!/bin/sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
LOCK_DIR="$ROOT_DIR/data/.nhatrang-crawl.lock"
if ! mkdir "$LOCK_DIR" 2>/dev/null; then
  echo "another Nha Trang crawl is already running" >&2
  exit 75
fi
trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT INT TERM

LOG_DIR="$ROOT_DIR/data/crawl-logs"
mkdir -p "$LOG_DIR"
STAMP=$(date -u +%Y%m%dT%H%M%SZ)
LOG_FILE="$LOG_DIR/nhatrang-$STAMP.log"
cd "$ROOT_DIR"

python3 scraper/nhatot_rentals.py \
  --days "${DAYS:-90}" \
  --region-v2 7044 \
  --city-slug nha-trang \
  --city-label "Nha Trang" \
  --area-contains "Nha Trang" \
  --db data/nhatot-nha-trang.sqlite \
  --csv data/nhatot-nha-trang.csv \
  --images-dir data/nhatot-nha-trang-images \
  --dashboard output/nhatot-nha-trang-dashboard.html \
  --download-workers "${DOWNLOAD_WORKERS:-6}" 2>&1 | tee "$LOG_FILE"
