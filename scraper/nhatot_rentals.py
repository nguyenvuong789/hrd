#!/usr/bin/env python3
"""Crawl recent personal Da Nang rentals from Nhatot's public listing API."""

from __future__ import annotations

import argparse
import csv
import html
import os
import re
import sqlite3
import time
import unicodedata
from collections import Counter
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timedelta, timezone
from pathlib import Path
from urllib.parse import urlencode

import requests

API = "https://gateway.chotot.com/v1/public/ad-listing"
DETAIL_API = "https://gateway.chotot.com/v1/public/ad-listing/{}"
BASE_PARAMS = {
    "region_v2": "3017",  # Da Nang
    "cg": "1020",         # Houses / residential property
    "f": "p",             # Personal sellers only
    "st": "u,h",
    "limit": "50",
    "w": "1",
    "key_param_included": "true",
    "video_count_included": "true",
}
WATERMARK_TERMS = re.compile(r"water.?mark|logo|chotot|nha.?tot", re.I)
FURNISHED_TERMS = re.compile(r"nội\s*thất|full\s*(?:đồ|nội thất|furniture)?|furnished|furniture|đầy đủ đồ|đủ đồ|đồ dùng|sofa|máy lạnh|điều hòa|máy giặt|tủ lạnh|giường|bếp", re.I)
UNFURNISHED_TERMS = re.compile(r"không\s*nội\s*thất|trống\s*nội\s*thất|nhà\s*trống|empty|unfurnished", re.I)
COMMERCIAL_TERMS = re.compile(r"mặt\s*bằng|kinh\s*doanh|shop\s*house|shophouse|văn\s*phòng|office|kho\s*xưởng|mặt\s*tiền\s*kinh\s*doanh", re.I)
EXCLUDED_LISTING_TERMS = re.compile(
    r"nhà\s*cấp\s*4|sang\s*nhượng|"
    r"\bsang\s+(?:lại\s+)?(?:tiệm|spa|salon|quán|trọ|dãy\s*trọ|nhà|vị\s*trí|mặt\s*bằng|"
    r"chdv|căn\s*hộ\s*dịch\s*vụ|hợp\s*đồng(?:\s*nhà)?(?:\s*thuê)?)\b|"
    r"nhượng\s+(?:lại\s+)?hợp\s*đồng(?:\s*nhà)?(?:\s*thuê)?",
    re.I,
)
BUILDING_PATTERNS = {
    "The Monarchy": re.compile(r"the\s*monarchy", re.I),
    "Sun Ponte Residence": re.compile(r"sun\s*ponte|the\s*ponte", re.I),
    "Vista Residence": re.compile(r"vista\s*residence", re.I),
    "Panoma": re.compile(r"panoma", re.I),
    "Mường Thanh Sơn Trà": re.compile(r"mường\s*thanh|muong\s*thanh", re.I),
    "FPT Plaza": re.compile(r"fpt\s*plaza", re.I),
    "The Ori Garden": re.compile(r"the\s*ori\s*garden|ori\s*garden", re.I),
    "Golden Bay": re.compile(r"golden\s*bay", re.I),
    "Mia Center Point": re.compile(r"mia\s*center\s*point", re.I),
    "SAM Towers": re.compile(r"sam\s*towers", re.I),
    "Đà Nẵng Times Square": re.compile(r"times\s*square", re.I),
}
PHONE_PATTERN = re.compile(r"(?:\+?84|0)(?:[\s()._-]*\d){8,10}")


def load_private_blocklist(filename: str, env_name: str) -> set[str]:
    """Load local moderation values without publishing them in the repository."""
    values = {value.strip().lower() for value in os.environ.get(env_name, "").split(",") if value.strip()}
    path = Path(__file__).resolve().parents[1] / "data" / filename
    if path.exists():
        values.update(line.strip().lower() for line in path.read_text(encoding="utf-8").splitlines() if line.strip() and not line.lstrip().startswith("#"))
    return values


BLOCKED_PHONES = load_private_blocklist("blocked-phones.txt", "NHATOT_BLOCKED_PHONES")
BLOCKED_SELLERS = load_private_blocklist("blocked-sellers.txt", "NHATOT_BLOCKED_SELLERS")


def api_get(session: requests.Session, params: dict[str, object]) -> dict:
    response = session.get(API, params=params, timeout=45)
    response.raise_for_status()
    payload = response.json()
    if not isinstance(payload, dict) or not isinstance(payload.get("ads"), list):
        raise ValueError("unexpected Nhatot API response")
    return payload


def detail_get(session: requests.Session, list_id: int) -> dict:
    """Fetch the detail payload because phone is omitted from search results."""
    for attempt in range(3):
        try:
            response = session.get(DETAIL_API.format(list_id), timeout=(10, 30))
            response.raise_for_status()
            payload = response.json()
            return payload.get("ad") if isinstance(payload, dict) and isinstance(payload.get("ad"), dict) else {}
        except requests.RequestException:
            if attempt == 2:
                return {}
            time.sleep(1.5 * (attempt + 1))


def iso_time(epoch_ms: int | None) -> str:
    if not epoch_ms:
        return ""
    return datetime.fromtimestamp(epoch_ms / 1000, tz=timezone.utc).isoformat()


def watermark_status(ad: dict) -> str:
    text = " ".join(str(ad.get(key) or "") for key in ("subject", "body", "image", "images"))
    return "review" if WATERMARK_TERMS.search(text) else "clear"


def extract_phone(ad: dict) -> str:
    """Extract a phone explicitly printed in the public listing text."""
    text = " ".join(str(ad.get(key) or "") for key in ("subject", "body", "phone"))
    text = unicodedata.normalize("NFKD", text)
    text = "".join(char for char in text if not unicodedata.combining(char))
    text = text.translate(str.maketrans({"O": "0", "o": "0", "I": "1", "l": "1"}))
    for match in PHONE_PATTERN.finditer(text):
        digits = re.sub(r"\D", "", match.group())
        if digits.startswith("84") and len(digits) >= 10:
            digits = "0" + digits[2:]
        if len(digits) == 10 and digits.startswith("0"):
            return digits
    return ""


def is_furnished(ad: dict) -> bool:
    text = " ".join(str(ad.get(key) or "") for key in ("subject", "body"))
    return bool(FURNISHED_TERMS.search(text)) and not UNFURNISHED_TERMS.search(text)


def is_commercial(ad: dict) -> bool:
    text = " ".join(str(ad.get(key) or "") for key in ("subject", "body", "house_type", "category_name"))
    return bool(COMMERCIAL_TERMS.search(text) or EXCLUDED_LISTING_TERMS.search(text))


def infer_building(ad: dict) -> str:
    explicit = str(ad.get("pty_project_name") or ad.get("project_name") or "").strip()
    text = " ".join(str(ad.get(key) or "") for key in ("subject", "body", "area_name"))
    for name, pattern in BUILDING_PATTERNS.items():
        if pattern.search(explicit) or pattern.search(text):
            return name
    return explicit


def is_whole_building_rental(ad: dict) -> bool:
    text = " ".join(str(ad.get(key) or "") for key in ("subject", "body"))
    return bool(re.search(r"tòa\s*(?:căn hộ|nhà)|toà\s*(?:căn hộ|nhà)|căn hộ dịch vụ|apartment building|whole building|nguyên tòa", text, re.I))


def is_blocked_seller(ad: dict) -> bool:
    name = unicodedata.normalize("NFKD", str(ad.get("account_name") or ad.get("full_name") or ""))
    name = "".join(char for char in name if not unicodedata.combining(char))
    name = re.sub(r"[^a-z0-9]", "", name.lower())
    return name in BLOCKED_SELLERS or extract_phone(ad) in BLOCKED_PHONES


def seller_key(ad: dict) -> str:
    account_id = str(ad.get("account_id") or "").strip()
    if account_id:
        return "id:" + account_id
    name = unicodedata.normalize("NFKD", str(ad.get("account_name") or ad.get("full_name") or ""))
    name = "".join(char for char in name if not unicodedata.combining(char))
    return "name:" + re.sub(r"[^a-z0-9]", "", name.lower())


def source_url(ad: dict, list_id: int) -> str:
    district = unicodedata.normalize("NFD", str(ad.get("area_name") or "da nang"))
    district = "".join(char for char in district if unicodedata.category(char) != "Mn")
    district = re.sub(r"[^a-zA-Z0-9]+", "-", district.lower()).strip("-")
    city_slug = str(ad.get("_city_slug") or "da-nang")
    return f"https://www.nhatot.com/thue-nha-dat-{district}-{city_slug}/{list_id}.htm"


def init_db(path: Path) -> sqlite3.Connection:
    connection = sqlite3.connect(path)
    connection.execute(
        """CREATE TABLE IF NOT EXISTS listings (
            list_id INTEGER PRIMARY KEY, subject TEXT, body TEXT, list_time TEXT,
            price REAL, price_string TEXT, previous_price_string TEXT, price_changed_at TEXT,
            size REAL, rooms INTEGER, toilets INTEGER,
            street_name TEXT, ward_name TEXT, area_name TEXT, building TEXT, whole_building INTEGER, region_name TEXT,
            latitude REAL, longitude REAL, account_id INTEGER, account_name TEXT,
            phone TEXT, phone_hidden INTEGER,
            seller_type TEXT, category TEXT, source_url TEXT, image_urls TEXT, image_dir TEXT,
            watermark_status TEXT, scraped_at TEXT
        )"""
    )
    columns = {row[1] for row in connection.execute("PRAGMA table_info(listings)")}
    if "phone" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN phone TEXT")
    if "phone_hidden" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN phone_hidden INTEGER")
    if "building" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN building TEXT")
    if "category" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN category TEXT")
    if "whole_building" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN whole_building INTEGER")
    if "previous_price_string" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN previous_price_string TEXT")
    if "price_changed_at" not in columns:
        connection.execute("ALTER TABLE listings ADD COLUMN price_changed_at TEXT")
    return connection


def normalize(ad: dict, image_dir: Path) -> dict:
    list_id = int(ad["list_id"])
    images = ad.get("images") or ([ad["image"]] if ad.get("image") else [])
    return {
        "list_id": list_id,
        "subject": ad.get("subject") or "",
        "body": ad.get("body") or "",
        "list_time": iso_time(ad.get("list_time")),
        "price": ad.get("price") or "",
        "price_string": ad.get("price_string") or "",
        "previous_price_string": "",
        "price_changed_at": "",
        "size": ad.get("size") or "",
        "rooms": ad.get("rooms") or "",
        "toilets": ad.get("toilets") or "",
        "street_name": ad.get("street_name") or "",
        "ward_name": ad.get("ward_name") or "",
        "area_name": ad.get("area_name") or "",
        "building": infer_building(ad),
        "whole_building": 1 if is_whole_building_rental(ad) else 0,
        "region_name": ad.get("region_name") or "Đà Nẵng",
        "latitude": ad.get("latitude") or "",
        "longitude": ad.get("longitude") or "",
        "account_id": ad.get("account_id") or "",
        "account_name": ad.get("account_name") or ad.get("full_name") or "",
        "phone": extract_phone(ad),
        "phone_hidden": 1 if ad.get("phone_hidden") else 0,
        "seller_type": ad.get("type") or "u",
        "category": ad.get("_category") or "houses",
        "source_url": source_url(ad, list_id),
        "image_urls": images,
        "image_dir": str(image_dir),
        "watermark_status": watermark_status(ad),
        "scraped_at": datetime.now(timezone.utc).isoformat(),
    }


def save(connection: sqlite3.Connection, row: dict) -> None:
    columns = [
        "list_id", "subject", "body", "list_time", "price", "price_string", "previous_price_string", "price_changed_at", "size", "rooms", "toilets",
        "street_name", "ward_name", "area_name", "building", "whole_building", "region_name", "latitude", "longitude", "account_id",
        "account_name", "phone", "phone_hidden", "seller_type", "category", "source_url", "image_urls", "image_dir", "watermark_status", "scraped_at",
    ]
    values = [row[key] if key != "image_urls" else "\n".join(row[key]) for key in columns]
    updates = ", ".join(f"{key}=excluded.{key}" for key in columns if key != "list_id")
    connection.execute(
        f"INSERT INTO listings ({','.join(columns)}) VALUES ({','.join('?' for _ in columns)}) "
        f"ON CONFLICT(list_id) DO UPDATE SET {updates}", values
    )


def download_images(session: requests.Session, row: dict, root: Path) -> None:
    target = root / str(row["list_id"])
    target.mkdir(parents=True, exist_ok=True)
    row["image_dir"] = str(target)
    for index, url in enumerate(row["image_urls"], 1):
        suffix = Path(url.split("?", 1)[0]).suffix.lower() or ".jpg"
        if suffix not in {".jpg", ".jpeg", ".png", ".webp"}:
            suffix = ".jpg"
        destination = target / f"{index:02d}{suffix}"
        if destination.exists():
            continue
        for attempt in range(3):
            try:
                response = session.get(url, timeout=(10, 30))
                response.raise_for_status()
                destination.write_bytes(response.content)
                break
            except requests.RequestException:
                if attempt == 2:
                    break
                time.sleep(1.5 * (attempt + 1))


def esc(value: object) -> str:
    return html.escape(str(value or ""), quote=True)


def build_dashboard(path: Path, rows: list[dict], days: int, city_label: str = "Đà Nẵng") -> None:
    cards = []
    districts = sorted({str(row.get("area_name") or "").strip() for row in rows if str(row.get("area_name") or "").strip()})
    apartment_buildings = Counter(
        str(row.get("building") or "").strip()
        for row in rows
        if str(row.get("category") or "") == "apartments" and str(row.get("building") or "").strip()
    )
    buildings = sorted(building for building, count in apartment_buildings.items() if count >= 5)
    has_other_buildings = any(
        str(row.get("category") or "") == "apartments"
        and apartment_buildings.get(str(row.get("building") or "").strip(), 0) < 5
        for row in rows
    )
    for row in rows:
        image_dir = Path(row["image_dir"])
        images = sorted(p for p in image_dir.glob("*") if p.suffix.lower() in {".jpg", ".jpeg", ".png", ".webp"})
        # Dashboard lives under output/, while downloaded images live under data/.
        import os
        rel = Path(os.path.relpath(image_dir, path.parent))
        gallery = "".join(f'<button class="thumb" type="button" data-src="{esc((rel / p.name).as_posix())}" aria-label="Xem ảnh {i + 1}"><img loading="lazy" src="{esc((rel / p.name).as_posix())}" alt="{esc(row["subject"])}"></button>' for i, p in enumerate(images))
        if images:
            cover = f'<div class="cover-open" data-images="{esc("|".join((rel / p.name).as_posix() for p in images))}"><button class="slide-prev" type="button" aria-label="Ảnh trước">‹</button><img src="{esc((rel / images[0].name).as_posix())}" alt="{esc(row["subject"])}"><span class="brand-watermark">HouseRentalDanang.com</span><button class="slide-next" type="button" aria-label="Ảnh sau">›</button></div>'
        else:
            cover = '<span class="no-image">Tin không có ảnh</span>'
        address = ", ".join(x for x in (row["street_name"], row["ward_name"], row["area_name"]) if x)
        phone = row.get("phone") or ("Ẩn trên Nhatot" if row.get("phone_hidden") else "Không có")
        kind = "building" if str(row.get("whole_building")) == "1" else str(row.get("category") or "houses")
        building = str(row.get("building") or "").strip()
        building_filter = building if building in buildings else ("Tòa khác" if str(row.get("category") or "") == "apartments" else "")
        old_price = str(row.get("previous_price_string") or "").strip()
        price_display = f'<del class="old-price">{esc(old_price)}</del> <span class="new-price">{esc(row["price_string"])}</span>' if old_price and old_price != str(row.get("price_string") or "") else esc(row["price_string"])
        changed_note = f' <span class="price-changed">Đổi giá {esc(str(row.get("price_changed_at") or "")[:10])}</span>' if old_price else ""
        price_changed = "changed" if old_price else ""
        cards.append(f'''<article class="card" data-kind="{esc(kind)}" data-district="{esc(row["area_name"])}" data-building="{esc(building_filter)}" data-price-changed="{price_changed}"><div class="cover">{cover}</div><div class="body"><div class="meta">{esc(row["list_time"][:16].replace('T',' '))} UTC · ID {esc(row["list_id"])}</div><h2>{esc(row["subject"])} </h2><p class="facts">{price_display}{changed_note} · {esc(row["rooms"])} PN · {esc(row["size"])} m²</p><p class="address">{esc(address)}</p><p class="seller">Chính chủ · {esc(row["account_name"])} · SĐT: {esc(phone)}</p><a class="source" href="{esc(row["source_url"])}" target="_blank" rel="noreferrer">Mở tin gốc ↗</a><div class="gallery">{gallery}</div></div></article>''')
    generated = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M UTC")
    district_options = ''.join(f'<option value="{esc(district)}">{esc(district)}</option>' for district in districts)
    document = f'''<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nhà Tốt · Đà Nẵng · Chính chủ</title><style>@import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;700;800&display=swap');:root{{--ink:#18221f;--muted:#64716b;--card:#fff;--accent:#ea6f3d;--line:#d4ddd5}}*{{box-sizing:border-box}}body{{margin:0;background:linear-gradient(135deg,#dfe9e3 0,#f4f0e8 55%,#f7cba8 100%);color:var(--ink);font-family:Manrope,sans-serif}}header{{max-width:1400px;margin:auto;padding:52px 24px 28px;display:flex;justify-content:space-between;gap:24px;align-items:end}}h1{{font-size:clamp(2rem,5vw,4.8rem);letter-spacing:-.08em;line-height:.92;margin:0;max-width:780px}}header p{{max-width:380px;color:var(--muted);font-size:.85rem;line-height:1.6;margin:0}}main{{max-width:1400px;margin:auto;padding:10px 24px 70px}}.grid{{display:grid;grid-template-columns:repeat(auto-fit,minmax(310px,1fr));gap:20px}}.card{{background:var(--card);border:1px solid var(--line);box-shadow:7px 7px 0 #cdd8cf;overflow:hidden;animation:rise .45s both}}.cover{{display:block;width:100%;padding:0;border:0;aspect-ratio:4/3;background:#d8ded9;overflow:hidden}}.cover-open{{position:relative;width:100%;height:100%;display:flex;align-items:center;justify-content:center}}.cover-open img,.cover img{{width:100%;height:100%;object-fit:cover}}.slide-prev,.slide-next{{position:absolute;top:50%;transform:translateY(-50%);z-index:1;width:34px;height:34px;border:0;border-radius:50%;background:rgba(10,17,15,.72);color:#fff;font-size:1.6rem;line-height:1;cursor:pointer}}.slide-prev{{left:10px}}.slide-next{{right:10px}}.no-image{{font:500 .75rem 'DM Mono',monospace;text-transform:uppercase;color:var(--muted)}}.body{{padding:17px 18px 20px}}.meta,.facts{{font:500 .7rem 'DM Mono',monospace;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)}}h2{{font-size:1.15rem;line-height:1.17;letter-spacing:-.04em;margin:10px 0}}.facts{{color:var(--accent);margin:0 0 12px}}.address{{font-size:.86rem;line-height:1.4;margin:8px 0;color:#303d37}}.seller{{font-size:.78rem;color:var(--muted);margin:8px 0 15px}}.source{{font-size:.78rem;font-weight:800;color:var(--ink);text-decoration:none;border-bottom:2px solid var(--accent);padding-bottom:2px}}.gallery{{display:flex;gap:5px;overflow:auto;margin-top:17px}}.thumb{{padding:0;border:0;background:none;cursor:pointer}}.gallery img{{width:58px;height:45px;object-fit:cover;display:block;border:1px solid var(--line)}}@keyframes rise{{from{{opacity:0;transform:translateY(10px)}}to{{opacity:1;transform:none}}}}@media(max-width:650px){{header{{display:block;padding-top:32px}}header p{{margin-top:18px}}.grid{{grid-template-columns:1fr}}}}@media(prefers-reduced-motion:reduce){{*{{animation:none!important;transition:none!important}}}}</style></head><body><header><div><div class="meta">Nhà Tốt / Đà Nẵng / Cá nhân</div><h1>Tin nhà mới, địa chỉ rõ.</h1></div><p>{len(cards)} tin cá nhân trong {days} ngày gần nhất, có street + tọa độ. Cập nhật {esc(generated)}. Dùng nút trái/phải ngay trên ảnh để xem ảnh local.</p></header><main><div class="grid">{"".join(cards)}</div></main><script>document.querySelectorAll('.cover-open').forEach(box=>{{const images=box.dataset.images.split('|');let index=0;const image=box.querySelector('img');const render=()=>image.src=images[index];box.querySelector('.slide-prev')?.addEventListener('click',e=>{{e.preventDefault();e.stopPropagation();index=(index+images.length-1)%images.length;render()}});box.querySelector('.slide-next')?.addEventListener('click',e=>{{e.preventDefault();e.stopPropagation();index=(index+1)%images.length;render()}})}});document.querySelectorAll('.thumb').forEach(t=>t.addEventListener('click',()=>{{const box=t.closest('.card').querySelector('.cover-open'),images=box.dataset.images.split('|');box.querySelector('img').src=t.dataset.src}}));</script></body></html>'''
    chips = ''.join(f'<label class="filter-chip"><input type="checkbox" data-filter="district" value="{esc(district)}"><span>{esc(district.replace("Quận ", ""))}</span></label>' for district in districts)
    building_options = buildings + (["Tòa khác"] if has_other_buildings else [])
    building_chips = ''.join(f'<label class="filter-chip"><input type="checkbox" data-filter="building" value="{esc(building)}"><span>{esc(building)}</span></label>' for building in building_options)
    controls = f'<div class="district-filter"><strong>Loại</strong><label class="filter-chip all-chip"><input type="checkbox" data-filter="kind" value="" checked><span>Tất cả</span></label><label class="filter-chip"><input type="checkbox" data-filter="kind" value="houses"><span>Nhà</span></label><label class="filter-chip"><input type="checkbox" data-filter="kind" value="apartments"><span>Căn hộ</span></label><label class="filter-chip"><input type="checkbox" data-filter="kind" value="building"><span>Tòa căn hộ cho thuê</span></label></div><div class="district-filter"><strong>Lịch sử giá</strong><label class="filter-chip"><input type="checkbox" data-filter="history" value="changed"><span>Đã đổi giá</span></label></div><div class="district-filter"><strong>Lọc quận</strong><label class="filter-chip all-chip"><input type="checkbox" data-filter="district" value="" checked><span>Tất cả</span></label>{chips}</div><div class="district-filter building-filter"><strong>Lọc tòa</strong>{building_chips or "<span class=\"filter-empty\">Chưa có tên tòa trong dữ liệu</span>"}</div>'
    document = document.replace('<main>', f'<main>{controls}')
    document = document.replace('</style>', '.district-filter{display:flex;flex-wrap:wrap;align-items:center;gap:8px;max-width:1400px;margin:0 auto 18px;padding:0 24px;font:700 .85rem Manrope,sans-serif}.filter-chip{cursor:pointer}.filter-chip input{position:absolute;opacity:0}.filter-chip span{display:block;padding:9px 13px;border:1px solid #cbd6ce;border-radius:999px;background:#fff;color:#42514a;transition:.2s}.filter-chip input:checked+span{background:#18221f;color:#fff;border-color:#18221f}.filter-chip input:focus-visible+span{outline:3px solid #f7cba8}.card.is-hidden{display:none}.old-price{color:#8a9690;text-decoration-thickness:2px}.new-price{color:#c94f20;font-weight:800}.price-changed{display:inline-block;margin-left:5px;padding:3px 6px;border-radius:999px;background:#fff0e8;color:#a63e18;font-size:.62rem}.brand-watermark{position:absolute;left:50%;top:50%;z-index:2;transform:translate(-50%,-50%) rotate(-12deg);padding:7px 12px;border:1px solid rgba(255,255,255,.28);border-radius:5px;background:rgba(24,34,31,.2);color:rgba(255,255,255,.62);font:800 .72rem Manrope,sans-serif;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;pointer-events:none;text-shadow:0 1px 2px rgba(0,0,0,.35)}\n</style>')
    document = document.replace('</script></body>', "const filterInputs=[...document.querySelectorAll('.district-filter input')];function applyFilters(){const selected=f=>filterInputs.filter(i=>i.checked&&i.dataset.filter===f&&i.value).map(i=>i.value),kinds=selected('kind'),history=selected('history'),districts=selected('district'),buildings=selected('building');document.querySelectorAll('.card').forEach(c=>c.classList.toggle('is-hidden',(kinds.length&&!kinds.includes(c.dataset.kind))||(history.length&&!history.includes(c.dataset.priceChanged))||(districts.length&&!districts.includes(c.dataset.district))||(buildings.length&&!buildings.includes(c.dataset.building))))}filterInputs.forEach(i=>i.addEventListener('change',()=>{if(!i.value)document.querySelectorAll('[data-filter='+i.dataset.filter+']:not([value=\\\"\\\"])').forEach(x=>x.checked=false);applyFilters()}));</script></body>")
    document = document.replace("Đà Nẵng", city_label)
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(document, encoding="utf-8")


def export_csv(path: Path, rows: list[dict]) -> None:
    columns = ["list_id", "subject", "list_time", "price_string", "previous_price_string", "price_changed_at", "size", "rooms", "toilets", "street_name", "ward_name", "area_name", "building", "whole_building", "category", "latitude", "longitude", "account_name", "phone", "phone_hidden", "source_url", "image_dir", "watermark_status"]
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=columns)
        writer.writeheader()
        writer.writerows({key: row.get(key, "") for key in columns} for row in rows)


def previous_listing_ids(path: Path) -> dict[int, dict]:
    if not path.exists():
        return {}
    with path.open(encoding="utf-8-sig", newline="") as handle:
        return {int(row["list_id"]): row for row in csv.DictReader(handle) if row.get("list_id")}


def record_rented_followups(path: Path, disappeared: list[dict]) -> None:
    if not disappeared:
        return
    path.parent.mkdir(parents=True, exist_ok=True)
    existing = {}
    if path.exists():
        with path.open(encoding="utf-8-sig", newline="") as handle:
            existing = {int(row["list_id"]): row for row in csv.DictReader(handle) if row.get("list_id")}
    checked = datetime.now(timezone.utc).date()
    due = checked + timedelta(days=90)
    for row in disappeared:
        existing.setdefault(str(row["list_id"]), {
            "list_id": row["list_id"], "subject": row.get("subject", ""),
            "price_string": row.get("price_string", ""), "source_url": row.get("source_url", ""),
            "status": "rented", "checked_at": checked.isoformat(), "recheck_due": due.isoformat(),
        })
    columns = ["list_id", "subject", "price_string", "source_url", "status", "checked_at", "recheck_due"]
    with path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=columns)
        writer.writeheader()
        writer.writerows(existing.values())


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--days", type=int, default=90)
    parser.add_argument("--category", choices=("houses", "apartments", "all"), default="all")
    parser.add_argument("--min-price", type=float, default=8_000_000)
    parser.add_argument("--region-v2", default="3017")
    parser.add_argument("--city-slug", default="da-nang")
    parser.add_argument("--city-label", default="Đà Nẵng")
    parser.add_argument("--area-contains", default="")
    parser.add_argument("--db", type=Path, default=Path("data/nhatot-da-nang.sqlite"))
    parser.add_argument("--csv", type=Path, default=Path("data/nhatot-da-nang.csv"))
    parser.add_argument("--images-dir", type=Path, default=Path("data/nhatot-images"))
    parser.add_argument("--dashboard", type=Path, default=Path("output/nhatot-da-nang-dashboard.html"))
    parser.add_argument("--max-listings", type=int, default=0, help="0 means all matching listings")
    parser.add_argument("--download-workers", type=int, default=6)
    args = parser.parse_args()
    cutoff = datetime.now(timezone.utc) - timedelta(days=args.days)
    session = requests.Session()
    session.headers.update({"User-Agent": "HouseRentalDanang Nhatot review crawler/0.1", "Accept": "application/json"})
    all_ads: dict[int, dict] = {}
    categories = ["houses", "apartments"] if args.category == "all" else [args.category]
    for category in categories:
        for offset in range(0, 5000, 50):
            category_params = {"cg": "1010"} if category == "apartments" else {}
            payload = api_get(session, {**BASE_PARAMS, "region_v2": args.region_v2, **category_params, "o": offset})
            ads = payload["ads"]
            if not ads:
                break
            for ad in ads:
                if not ad.get("list_time") or datetime.fromtimestamp(ad["list_time"] / 1000, tz=timezone.utc) < cutoff:
                    continue
                if ad.get("type") != "u" or not ad.get("street_name") or not ad.get("latitude") or not ad.get("longitude"):
                    continue
                if args.area_contains and args.area_contains.lower() not in str(ad.get("area_name") or "").lower():
                    continue
                minimum_price = args.min_price if args.category != "all" else (5_000_000 if category == "apartments" else 8_000_000)
                if float(ad.get("price") or 0) < minimum_price or not is_furnished(ad) or is_commercial(ad) or is_blocked_seller(ad):
                    continue
                ad["_category"] = category
                ad["_city_slug"] = args.city_slug
                all_ads[int(ad["list_id"])] = ad
            oldest = min((ad.get("list_time", 0) for ad in ads), default=0)
            if oldest and datetime.fromtimestamp(oldest / 1000, tz=timezone.utc) < cutoff:
                break
    ordered_ads = sorted(all_ads.values(), key=lambda item: item["list_time"], reverse=True)
    # A seller/account with more than five matching ads is treated as an agent.
    seller_counts = {}
    phone_counts = {}
    for ad in ordered_ads:
        seller_counts[seller_key(ad)] = seller_counts.get(seller_key(ad), 0) + 1
        phone = extract_phone(ad)
        if phone:
            phone_counts[phone] = phone_counts.get(phone, 0) + 1
    ordered_ads = [
        ad for ad in ordered_ads
        if seller_counts[seller_key(ad)] <= 5 and phone_counts.get(extract_phone(ad), 0) <= 5
    ]
    if args.max_listings:
        ordered_ads = ordered_ads[: args.max_listings]
    # Phone is only returned by the per-listing detail endpoint.
    for ad in ordered_ads:
        try:
            detail = detail_get(session, int(ad["list_id"]))
            if detail:
                ad.update({key: detail[key] for key in ("phone", "phone_hidden") if key in detail})
        except requests.RequestException:
            pass
    previous = previous_listing_ids(args.csv)
    connection = init_db(args.db)
    rows = []
    normalized = []
    for ad in ordered_ads:
        row = normalize(ad, args.images_dir / str(ad["list_id"]))
        row["category"] = ad.get("_category") or args.category
        old_row = previous.get(int(row["list_id"]), {})
        old_price = old_row.get("price_string", "")
        if old_price and old_price != row.get("price_string", ""):
            row["previous_price_string"] = old_price
            row["price_changed_at"] = datetime.now(timezone.utc).date().isoformat()
        else:
            row["previous_price_string"] = old_row.get("previous_price_string", "")
            row["price_changed_at"] = old_row.get("price_changed_at", "")
        if not row.get("phone") and old_row.get("phone"):
            # Preserve numbers revealed manually in the browser when the API masks them later.
            row["phone"] = old_row["phone"]
            row["phone_hidden"] = 0
        normalized.append(row)
    with ThreadPoolExecutor(max_workers=max(1, args.download_workers)) as pool:
        futures = {pool.submit(download_images, session, row, args.images_dir): row for row in normalized}
        for future in as_completed(futures):
            future.result()
    for row in normalized:
        save(connection, row)
        rows.append(row)
    connection.commit()
    connection.close()
    export_csv(args.csv, rows)
    build_dashboard(args.dashboard, rows, args.days, args.city_label)
    current_ids = {int(row["list_id"]) for row in rows}
    new_rows = [row for row in rows if int(row["list_id"]) not in previous]
    disappeared = [row for list_id, row in previous.items() if list_id not in current_ids]
    record_rented_followups(args.csv.with_name(f"{args.csv.stem}-rented-followups.csv"), disappeared)
    price_changes = [(row, previous[int(row["list_id"])].get("price_string", ""), row.get("price_string", "")) for row in rows if int(row["list_id"]) in previous and previous[int(row["list_id"])].get("price_string", "") != row.get("price_string", "")]
    print(f"Phone status: {sum(bool(row.get('phone')) for row in rows)} found, {sum(not row.get('phone') for row in rows)} hidden/unavailable")
    print(f"New today: {len(new_rows)} ({sum(row.get('category') == 'houses' for row in new_rows)} houses, {sum(row.get('category') == 'apartments' for row in new_rows)} apartments)")
    print(f"Disappeared since previous run: {len(disappeared)}")
    print(f"Price changes: {len(price_changes)}")
    for row, old_price, new_price in price_changes:
        print(f"Price changed ID {row['list_id']}: {old_price or '(blank)'} -> {new_price or '(blank)'} | {row['subject']}")
    print(f"Saved {len(rows)} {args.category} personal listings from the last {args.days} days")
    print(f"CSV: {args.csv}\nDashboard: {args.dashboard}\nDatabase: {args.db}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
