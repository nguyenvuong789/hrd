#!/bin/sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
LOCK_DIR="$ROOT_DIR/data/.nhatot-crawl.lock"
if ! mkdir "$LOCK_DIR" 2>/dev/null; then
  echo "another Nhatot crawl is already running" >&2
  exit 75
fi
trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT INT TERM

LOG_DIR="$ROOT_DIR/data/crawl-logs"
mkdir -p "$LOG_DIR"
STAMP=$(date -u +%Y%m%dT%H%M%SZ)
LOG_FILE="$LOG_DIR/nhatot-$STAMP.log"
cd "$ROOT_DIR"

python3 scraper/nhatot_rentals.py \
  --days "${DAYS:-90}" \
  --download-workers "${DOWNLOAD_WORKERS:-6}" \
  --db data/nhatot-da-nang.sqlite \
  --csv data/nhatot-da-nang.csv \
  --images-dir data/nhatot-images \
  --dashboard output/nhatot-da-nang-dashboard.html 2>&1 | tee "$LOG_FILE"
