interface Env {
  DB: D1Database;
  IMAGES: R2Bucket;
  ASSETS: Fetcher;
  ADMIN_TOKEN: string;
  CORS_ORIGIN: string;
}

type Category = "houses" | "apartments";

type NhatotAd = Record<string, unknown> & {
  list_id: number;
  list_time: number;
  subject?: string;
  body?: string;
  price?: number;
  price_string?: string;
  type?: string;
  account_id?: string | number;
  account_name?: string;
  full_name?: string;
  phone?: string;
  images?: string[];
  image?: string;
  area_name?: string;
  ward_name?: string;
  street_name?: string;
  size?: number;
  rooms?: number;
  toilets?: number;
  latitude?: number;
  longitude?: number;
  category_name?: string;
  house_type?: string;
};

const API = "https://gateway.chotot.com/v1/public/ad-listing";
const REGION = "13000";
const PAGE_SIZE = 50;
const MAX_OFFSET = 5000;
const MIN_PRICE = { houses: 10_000_000, apartments: 8_000_000 } as const;
const DAYS = 90;
const FURNISHED = /nội\s*thất|full\s*(?:đồ|nội thất|furniture)?|furnished|furniture|đầy đủ đồ|đủ đồ|sofa|máy lạnh|điều hòa|máy giặt|tủ lạnh|giường|bếp/i;
const UNFURNISHED = /không\s*nội\s*thất|trống\s*nội\s*thất|nhà\s*trống|empty|unfurnished/i;
const COMMERCIAL = /mặt\s*bằng|kinh\s*doanh|shop\s*house|shophouse|văn\s*phòng|office|kho\s*xưởng|mặt\s*tiền\s*kinh\s*doanh/i;
const EXCLUDED_LISTING = /nhà\s*cấp\s*4|sang\s*nhượng|\bsang\s+(?:lại\s+)?(?:tiệm|spa|salon|quán|trọ|dãy\s*trọ|nhà|vị\s*trí|mặt\s*bằng|chdv|căn\s*hộ\s*dịch\s*vụ|hợp\s*đồng(?:\s*nhà)?(?:\s*thuê)?)\b|nhượng\s+(?:lại\s+)?hợp\s*đồng(?:\s*nhà)?(?:\s*thuê)?/i;
const PHONE = /(?:\+?84|0)(?:[\s()._-]*\d){8,10}/g;

export default {
  async fetch(request: Request, env: Env, ctx: ExecutionContext): Promise<Response> {
    const url = new URL(request.url);
    if (url.pathname.startsWith("/images/")) return serveImage(url.pathname.slice(8), env);
    if (!url.pathname.startsWith("/api/")) return env.ASSETS.fetch(request);
    if (request.method === "OPTIONS") return cors(new Response(null, { status: 204 }), env);

    try {
      if (url.pathname === "/api/listings" && request.method === "GET") return cors(await listListings(url, env), env);
      if (/^\/api\/listings\/\d+$/.test(url.pathname) && request.method === "GET") return cors(await listingDetail(url, env), env);
      if (url.pathname === "/api/changes" && request.method === "GET") return cors(await changes(url, env), env);
      if (url.pathname === "/api/runs" && request.method === "GET") return cors(await runs(url, env), env);
      if (url.pathname === "/api/stats" && request.method === "GET") return cors(await stats(env), env);
      if (url.pathname === "/api/subscribe" && request.method === "POST") return cors(await subscribe(request, env), env);
      if (url.pathname === "/api/confirm" && request.method === "GET") return cors(await confirm(url, env), env);
      if (/^\/api\/listings\/\d+\/contact$/.test(url.pathname) && request.method === "GET") return cors(await contact(url, request, env), env);
      if (url.pathname === "/api/crawl" && request.method === "POST") {
        if (request.headers.get("authorization") !== `Bearer ${env.ADMIN_TOKEN}`) return json({ error: "Unauthorized" }, 401, env);
        ctx.waitUntil(crawl(env));
        return json({ accepted: true, message: "HCM crawl started" }, 202, env);
      }
      return json({ error: "Not found" }, 404, env);
    } catch (error) {
      return json({ error: errorMessage(error) }, 500, env);
    }
  },

  async scheduled(_event: ScheduledEvent, env: Env, ctx: ExecutionContext): Promise<void> {
    ctx.waitUntil(crawl(env));
  },
};

async function crawl(env: Env): Promise<void> {
  const started = Date.now();
  const now = new Date().toISOString();
  const run = await env.DB.prepare(
    "INSERT INTO crawl_runs (city, started_at, status) VALUES ('hcm', ?, 'running')",
  ).bind(now).run();
  const runId = Number(run.meta.last_row_id);
  let rawCount = 0;
  let apiErrors = 0;
  let imageErrors = 0;
  const errors: string[] = [];

  try {
    const all: Array<NhatotAd & { _category: Category }> = [];
    const cutoffMs = Date.now() - DAYS * 86_400_000;
    for (const [category, cg] of [["houses", "1020"], ["apartments", "1010"]] as const) {
      for (let offset = 0; offset <= MAX_OFFSET; offset += PAGE_SIZE) {
        try {
          const response = await fetch(`${API}?region_v2=${REGION}&cg=${cg}&f=p&st=u,h&limit=${PAGE_SIZE}&o=${offset}&w=1&key_param_included=true`, {
            headers: { accept: "application/json", "user-agent": "HouseRentalDanang HCM daily crawler/1.0" },
          });
          if (!response.ok) throw new Error(`Nhatot ${category} offset ${offset}: HTTP ${response.status}`);
          const payload = await response.json() as { ads?: NhatotAd[] };
          const ads = Array.isArray(payload.ads) ? payload.ads : [];
          rawCount += ads.length;
          if (!ads.length) break;
          for (const ad of ads) if (ad.list_time >= cutoffMs) all.push({ ...ad, _category: category });
          const oldest = Math.min(...ads.map((ad) => Number(ad.list_time || 0)).filter(Boolean));
          if (oldest && oldest < cutoffMs) break;
        } catch (error) {
          apiErrors += 1;
          errors.push(errorMessage(error));
          break;
        }
      }
    }

    const candidates = dedupe(all).filter(validListing);
    const accountCounts = new Map<string, number>();
    const phoneCounts = new Map<string, number>();
    for (const ad of candidates) {
      const account = sellerKey(ad);
      const phone = extractPhone(ad);
      accountCounts.set(account, (accountCounts.get(account) || 0) + 1);
      if (phone) phoneCounts.set(phone, (phoneCounts.get(phone) || 0) + 1);
    }
    const filtered = candidates.filter((ad) => accountCounts.get(sellerKey(ad))! <= 5 && (!extractPhone(ad) || phoneCounts.get(extractPhone(ad))! <= 5));
    const previous = await env.DB.prepare("SELECT list_id, price, is_active FROM listings WHERE city = 'hcm'").all<{ list_id: number; price: number; is_active: number }>();
    const previousById = new Map(previous.results.map((row) => [Number(row.list_id), row]));
    const currentIds = new Set(filtered.map((ad) => Number(ad.list_id)));
    let newCount = 0;
    let newHouses = 0;
    let newApartments = 0;
    let priceChanges = 0;
    let reactivated = 0;

    for (let index = 0; index < filtered.length; index += 40) {
      const batch = filtered.slice(index, index + 40);
      const statements: D1PreparedStatement[] = [];
      for (const ad of batch) {
        const old = previousById.get(Number(ad.list_id));
        const row = normalize(ad, now);
        const isNew = !old;
        if (isNew) {
          newCount += 1;
          if (ad._category === "houses") newHouses += 1; else newApartments += 1;
        } else if (Number(old.price) !== row.price) {
          priceChanges += 1;
          statements.push(env.DB.prepare("INSERT INTO listing_price_history (list_id, old_price, new_price, changed_at, run_id) VALUES (?, ?, ?, ?, ?)").bind(row.list_id, old.price, row.price, now, runId));
        }
        if (old && !old.is_active) reactivated += 1;
        statements.push(env.DB.prepare(`INSERT INTO listings (
          list_id, city, category, subject, body, price, price_string, list_time, address, district, size, rooms, toilets,
          latitude, longitude, account_id, account_name, phone, source_url, image_urls, image_paths, is_furnished,
          seller_type, first_seen_at, last_seen_at, last_checked_at, is_active
        ) VALUES (?, 'hcm', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'u', ?, ?, ?, 1)
        ON CONFLICT(list_id) DO UPDATE SET category=excluded.category, subject=excluded.subject, body=excluded.body,
          price=excluded.price, price_string=excluded.price_string, list_time=excluded.list_time, address=excluded.address,
          district=excluded.district, size=excluded.size, rooms=excluded.rooms, toilets=excluded.toilets,
          latitude=excluded.latitude, longitude=excluded.longitude, account_id=excluded.account_id,
          account_name=excluded.account_name, phone=CASE WHEN excluded.phone <> '' THEN excluded.phone ELSE listings.phone END, source_url=excluded.source_url,
          image_urls=excluded.image_urls, last_seen_at=excluded.last_seen_at, last_checked_at=excluded.last_checked_at, is_active=1`
        ).bind(row.list_id, row.category, row.subject, row.body, row.price, row.priceString, row.listTime, row.address,
          row.district, row.size, row.rooms, row.toilets, row.latitude, row.longitude, row.accountId, row.accountName,
          row.phone, row.sourceUrl, JSON.stringify(row.imageUrls), JSON.stringify(row.imagePaths), now, now, now));
        statements.push(env.DB.prepare("INSERT OR IGNORE INTO run_listings (run_id, list_id) VALUES (?, ?)").bind(runId, row.list_id));
        if (isNew || (old && !old.is_active)) {
          statements.push(env.DB.prepare("INSERT INTO listing_events (list_id, event_type, occurred_at, run_id, subject_snapshot, price_snapshot) VALUES (?, ?, ?, ?, ?, ?)").bind(row.list_id, isNew ? "new" : "reactivated", now, runId, row.subject, row.price));
        }
      }
      await env.DB.batch(statements);
    }

    const disappeared = previous.results.filter((row) => row.is_active && !currentIds.has(Number(row.list_id)));
    for (let index = 0; index < disappeared.length; index += 50) {
      const statements = disappeared.slice(index, index + 50).flatMap((row) => [
        env.DB.prepare("UPDATE listings SET is_active = 0, last_checked_at = ? WHERE list_id = ?").bind(now, row.list_id),
        env.DB.prepare("INSERT INTO listing_events (list_id, event_type, occurred_at, run_id, subject_snapshot, price_snapshot) SELECT list_id, 'disappeared', ?, ?, subject, price FROM listings WHERE list_id = ?").bind(now, runId, row.list_id),
      ]);
      await env.DB.batch(statements);
    }

    const newAds = filtered.filter((ad) => !previousById.has(Number(ad.list_id)));
    for (const ad of newAds.slice(0, 40)) {
      try { await mirrorFirstImage(ad, env, now); } catch (error) { imageErrors += 1; errors.push(`Image ${ad.list_id}: ${errorMessage(error)}`); }
    }

    const status = apiErrors || imageErrors ? "partial" : "success";
    await env.DB.prepare(`UPDATE crawl_runs SET completed_at=?, status=?, raw_count=?, filtered_count=?, total_found=?,
      new_count=?, new_houses_count=?, new_apartments_count=?, disappeared_count=?, price_change_count=?,
      api_error_count=?, image_error_count=?, error_message=?, duration_ms=? WHERE id=?`
    ).bind(new Date().toISOString(), status, rawCount, filtered.length, filtered.length, newCount, newHouses, newApartments,
      disappeared.length, priceChanges, apiErrors, imageErrors, errors.slice(0, 20).join("\n"), Date.now() - started, runId).run();
    console.log(JSON.stringify({ runId, rawCount, filtered: filtered.length, newCount, disappeared: disappeared.length, priceChanges, reactivated }));
  } catch (error) {
    errors.push(errorMessage(error));
    await env.DB.prepare("UPDATE crawl_runs SET completed_at=?, status='failed', raw_count=?, api_error_count=?, processing_error_count=1, error_message=?, duration_ms=? WHERE id=?")
      .bind(new Date().toISOString(), rawCount, apiErrors, errors.slice(0, 20).join("\n"), Date.now() - started, runId).run();
    throw error;
  }
}

function dedupe(ads: Array<NhatotAd & { _category: Category }>): Array<NhatotAd & { _category: Category }> {
  return [...new Map(ads.map((ad) => [Number(ad.list_id), ad])).values()];
}

function validListing(ad: NhatotAd & { _category: Category }): boolean {
  const text = `${ad.subject || ""} ${ad.body || ""} ${ad.house_type || ""} ${ad.category_name || ""}`;
  return ad.type === "u" && Number(ad.price || 0) >= MIN_PRICE[ad._category] && FURNISHED.test(text) && !UNFURNISHED.test(text) && !COMMERCIAL.test(text) && !EXCLUDED_LISTING.test(text);
}

function sellerKey(ad: NhatotAd): string {
  if (ad.account_id) return `id:${ad.account_id}`;
  return `name:${String(ad.account_name || ad.full_name || "unknown").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z0-9]/g, "")}`;
}

function extractPhone(ad: NhatotAd): string {
  const text = `${ad.phone || ""} ${ad.subject || ""} ${ad.body || ""}`.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  const matches = text.match(PHONE) || [];
  for (const match of matches) {
    let digits = match.replace(/\D/g, "");
    if (digits.startsWith("84")) digits = `0${digits.slice(2)}`;
    if (digits.length === 10 && digits.startsWith("0")) return digits;
  }
  return "";
}

function normalize(ad: NhatotAd & { _category: Category }, now: string) {
  const images = Array.isArray(ad.images) ? ad.images : ad.image ? [ad.image] : [];
  const district = String(ad.area_name || "");
  const address = [ad.street_name, ad.ward_name, district].filter(Boolean).join(", ");
  return {
    list_id: Number(ad.list_id), category: ad._category, subject: String(ad.subject || ""), body: String(ad.body || ""),
    price: Number(ad.price || 0), priceString: String(ad.price_string || formatPrice(Number(ad.price || 0))),
    listTime: new Date(Number(ad.list_time)).toISOString(), address, district, size: numberOrNull(ad.size),
    rooms: numberOrNull(ad.rooms), toilets: numberOrNull(ad.toilets), latitude: numberOrNull(ad.latitude), longitude: numberOrNull(ad.longitude),
    accountId: ad.account_id ? String(ad.account_id) : null, accountName: String(ad.account_name || ad.full_name || ""),
    phone: extractPhone(ad), sourceUrl: sourceUrl(ad), imageUrls: images, imagePaths: [] as string[], now,
  };
}

async function mirrorFirstImage(ad: NhatotAd, env: Env, now: string): Promise<void> {
  const url = Array.isArray(ad.images) ? ad.images[0] : ad.image;
  if (!url) return;
  const response = await fetch(url, { headers: { "user-agent": "HouseRentalDanang image mirror/1.0" } });
  if (!response.ok) throw new Error(`HTTP ${response.status}`);
  const contentType = response.headers.get("content-type") || "image/jpeg";
  const extension = contentType.includes("webp") ? "webp" : contentType.includes("png") ? "png" : "jpg";
  const key = `hcm/${ad.list_id}/01.${extension}`;
  await env.IMAGES.put(key, response.body, { httpMetadata: { contentType, cacheControl: "public, max-age=31536000, immutable" } });
  await env.DB.prepare("UPDATE listings SET image_paths = ?, last_checked_at = ? WHERE list_id = ?").bind(JSON.stringify([key]), now, ad.list_id).run();
}

async function listListings(url: URL, env: Env): Promise<Response> {
  const category = url.searchParams.get("category") || "all";
  const district = url.searchParams.get("district") || "";
  const status = url.searchParams.get("status") || "active";
  const minPrice = clampInt(url.searchParams.get("min_price"), 0, 1_000_000_000, 0);
  const maxPrice = clampInt(url.searchParams.get("max_price"), 0, 1_000_000_000, 1_000_000_000);
  const minSize = clampInt(url.searchParams.get("min_size"), 0, 10_000, 0);
  const limit = clampInt(url.searchParams.get("limit"), 1, 100, 24);
  const offset = clampInt(url.searchParams.get("offset"), 0, 100_000, 0);
  const clauses = ["city = 'hcm'", "price BETWEEN ? AND ?", "COALESCE(size, 0) >= ?"];
  const values: unknown[] = [minPrice, maxPrice, minSize];
  if (category === "houses" || category === "apartments") { clauses.push("category = ?"); values.push(category); }
  if (district) { clauses.push("district = ?"); values.push(district); }
  if (status === "active") clauses.push("is_active = 1");
  if (status === "disappeared") clauses.push("is_active = 0");
  const where = clauses.join(" AND ");
  const [items, count] = await Promise.all([
    env.DB.prepare(`SELECT * FROM listings WHERE ${where} ORDER BY is_active DESC, list_time DESC LIMIT ? OFFSET ?`).bind(...values, limit, offset).all(),
    env.DB.prepare(`SELECT COUNT(*) AS total FROM listings WHERE ${where}`).bind(...values).first<{ total: number }>(),
  ]);
  return Response.json({ items: items.results.map(publicListing), total: Number(count?.total || 0), limit, offset });
}

async function listingDetail(url: URL, env: Env): Promise<Response> {
  const id = Number(url.pathname.split("/").pop());
  const [listing, prices, events] = await Promise.all([
    env.DB.prepare("SELECT * FROM listings WHERE list_id = ?").bind(id).first(),
    env.DB.prepare("SELECT old_price, new_price, changed_at FROM listing_price_history WHERE list_id = ? ORDER BY changed_at DESC").bind(id).all(),
    env.DB.prepare("SELECT event_type, occurred_at FROM listing_events WHERE list_id = ? ORDER BY occurred_at DESC").bind(id).all(),
  ]);
  if (!listing) return new Response(JSON.stringify({ error: "Listing not found" }), { status: 404, headers: { "content-type": "application/json" } });
  return Response.json({ listing: publicListing(listing), price_history: prices.results, events: events.results });
}

async function changes(url: URL, env: Env): Promise<Response> {
  const date = url.searchParams.get("date") || "today";
  const from = date === "today" ? new Date(new Date().setUTCHours(0, 0, 0, 0)).toISOString() : `${date}T00:00:00.000Z`;
  const to = date === "today" ? new Date().toISOString() : `${date}T23:59:59.999Z`;
  const [events, prices] = await Promise.all([
    env.DB.prepare(`SELECT e.*, l.category, l.district, l.source_url FROM listing_events e JOIN listings l USING(list_id)
      WHERE e.occurred_at BETWEEN ? AND ? ORDER BY e.occurred_at DESC`).bind(from, to).all(),
    env.DB.prepare(`SELECT p.*, l.subject, l.category, l.district, l.source_url FROM listing_price_history p JOIN listings l USING(list_id)
      WHERE p.changed_at BETWEEN ? AND ? ORDER BY p.changed_at DESC`).bind(from, to).all(),
  ]);
  return Response.json({ from, to, events: events.results, price_changes: prices.results });
}

async function runs(url: URL, env: Env): Promise<Response> {
  const limit = clampInt(url.searchParams.get("limit"), 1, 100, 30);
  const result = await env.DB.prepare("SELECT * FROM crawl_runs WHERE city = 'hcm' ORDER BY started_at DESC LIMIT ?").bind(limit).all();
  return Response.json({ items: result.results });
}

async function stats(env: Env): Promise<Response> {
  const [counts, districts, latest] = await Promise.all([
    env.DB.prepare(`SELECT COUNT(*) total, SUM(is_active) active,
      SUM(CASE WHEN is_active=1 AND category='houses' THEN 1 ELSE 0 END) houses,
      SUM(CASE WHEN is_active=1 AND category='apartments' THEN 1 ELSE 0 END) apartments FROM listings WHERE city='hcm'`).first(),
    env.DB.prepare("SELECT district, COUNT(*) count FROM listings WHERE city='hcm' AND is_active=1 AND district<>'' GROUP BY district ORDER BY count DESC").all(),
    env.DB.prepare("SELECT * FROM crawl_runs WHERE city='hcm' ORDER BY started_at DESC LIMIT 1").first(),
  ]);
  return Response.json({ counts, districts: districts.results, latest_run: latest });
}

async function serveImage(key: string, env: Env): Promise<Response> {
  if (!key || key.includes("..")) return new Response("Not found", { status: 404 });
  const object = await env.IMAGES.get(key);
  if (!object) return new Response("Not found", { status: 404 });
  const headers = new Headers();
  object.writeHttpMetadata(headers);
  headers.set("etag", object.httpEtag);
  headers.set("cache-control", "public, max-age=31536000, immutable");
  return new Response(object.body, { headers });
}

function publicListing(row: Record<string, unknown>) {
  const imagePaths = parseJsonArray(row.image_paths);
  const imageUrls = parseJsonArray(row.image_urls);
  const { phone: _phone, account_id: _accountId, ...safe } = row;
  return { ...safe, image_urls: imageUrls, image_paths: imagePaths, images: imagePaths.length ? imagePaths.map((path) => `/images/${path}`) : imageUrls };
}

async function subscribe(request: Request, env: Env): Promise<Response> {
  const input = await request.json() as { email?: string; filters?: Record<string, unknown> };
  const email = String(input.email || "").trim().toLowerCase();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return json({ error: "Valid email required" }, 400, env);
  const token = crypto.randomUUID() + crypto.randomUUID();
  const tokenHash = await sha256(token);
  const now = new Date().toISOString();
  await env.DB.prepare(`INSERT INTO subscribers (email, filters_json, verify_token_hash, created_at)
    VALUES (?, ?, ?, ?) ON CONFLICT(email) DO UPDATE SET filters_json=excluded.filters_json, verify_token_hash=excluded.verify_token_hash, created_at=excluded.created_at, verified_at=NULL, alert_enabled=0`)
    .bind(email, JSON.stringify(input.filters || {}), tokenHash, now).run();
  // Delivery is intentionally provider-neutral; wire RESEND/SES/MailChannels here.
  console.log(JSON.stringify({ event: "double_opt_in_required", email, verifyUrl: `/api/confirm?token=${token}` }));
  return Response.json({ accepted: true, message: "Check your email to confirm alerts." });
}

async function confirm(url: URL, env: Env): Promise<Response> {
  const token = url.searchParams.get("token") || "";
  const tokenHash = await sha256(token);
  const subscriber = await env.DB.prepare("SELECT id FROM subscribers WHERE verify_token_hash = ?").bind(tokenHash).first<{ id: number }>();
  if (!subscriber) return json({ error: "Invalid or expired confirmation link" }, 400, env);
  const now = new Date().toISOString();
  await env.DB.prepare("UPDATE subscribers SET verified_at=?, alert_enabled=1, verify_token_hash='' WHERE id=?").bind(now, subscriber.id).run();
  const contactToken = crypto.randomUUID() + crypto.randomUUID();
  await env.DB.prepare("INSERT INTO subscriber_contact_tokens (token_hash, subscriber_id, expires_at) VALUES (?, ?, ?)").bind(await sha256(contactToken), subscriber.id, new Date(Date.now() + 180 * 86_400_000).toISOString()).run();
  const target = new URL("/", url.origin);
  target.searchParams.set("contact_token", contactToken);
  return new Response(`<!doctype html><meta charset="utf-8"><title>Email confirmed</title><p>Email confirmed. Returning you to SG by Owner…</p><script>location.replace(${JSON.stringify(target.toString())})</script>`, { headers: { "content-type": "text/html; charset=utf-8" } });
}

async function contact(url: URL, request: Request, env: Env): Promise<Response> {
  const id = Number(url.pathname.split("/")[3]);
  const token = (request.headers.get("authorization") || "").replace(/^Bearer\s+/i, "");
  if (!token) return json({ error: "Verify email before viewing contact details" }, 401, env);
  const session = await env.DB.prepare("SELECT subscriber_id FROM subscriber_contact_tokens WHERE token_hash=? AND expires_at > ?").bind(await sha256(token), new Date().toISOString()).first<{ subscriber_id: number }>();
  if (!session) return json({ error: "Valid verified subscriber token required" }, 403, env);
  const listing = await env.DB.prepare("SELECT list_id, account_name, phone, source_url FROM listings WHERE list_id=?").bind(id).first();
  if (!listing) return json({ error: "Listing not found" }, 404, env);
  return Response.json(listing);
}

async function sha256(value: string): Promise<string> {
  const digest = await crypto.subtle.digest("SHA-256", new TextEncoder().encode(value));
  return [...new Uint8Array(digest)].map((byte) => byte.toString(16).padStart(2, "0")).join("");
}

function parseJsonArray(value: unknown): string[] {
  try { const parsed = JSON.parse(String(value || "[]")); return Array.isArray(parsed) ? parsed : []; } catch { return []; }
}

function sourceUrl(ad: NhatotAd): string {
  const district = String(ad.area_name || "tp-ho-chi-minh").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
  return `https://www.nhatot.com/thue-nha-dat-${district}-tp-ho-chi-minh/${ad.list_id}.htm`;
}

function numberOrNull(value: unknown): number | null { const number = Number(value); return Number.isFinite(number) && number > 0 ? number : null; }
function formatPrice(price: number): string { return new Intl.NumberFormat("vi-VN").format(price) + " đ/tháng"; }
function clampInt(value: string | null, min: number, max: number, fallback: number): number { const number = Number(value); return Number.isFinite(number) ? Math.min(max, Math.max(min, Math.trunc(number))) : fallback; }
function errorMessage(error: unknown): string { return error instanceof Error ? error.message : String(error); }
function cors(response: Response, env: Env): Response { const next = new Response(response.body, response); next.headers.set("access-control-allow-origin", env.CORS_ORIGIN || "*"); next.headers.set("access-control-allow-headers", "authorization, content-type"); next.headers.set("access-control-allow-methods", "GET, POST, OPTIONS"); return next; }
function json(body: unknown, status: number, env: Env): Response { return cors(Response.json(body, { status }), env); }
