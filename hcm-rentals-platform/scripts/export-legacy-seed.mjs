import { mkdirSync, writeFileSync } from "node:fs";
import { execFileSync } from "node:child_process";
import path from "node:path";

const root = path.resolve(import.meta.dirname, "../..");
const platform = path.resolve(import.meta.dirname, "..");
const sources = [
  ["apartments", path.join(root, "data/nhatot-hcm-apartments-365d.sqlite")],
  ["houses", path.join(root, "data/nhatot-hcm-houses-365d.sqlite")],
];
const rows = sources.flatMap(([category, db]) => JSON.parse(execFileSync("sqlite3", ["-json", db, "SELECT * FROM listings ORDER BY list_time DESC"], { maxBuffer: 32 * 1024 * 1024 }).toString()).map((row) => ({
  ...row, category, city: "hcm", is_furnished: 1, seller_type: "u", first_seen_at: row.list_time, last_seen_at: row.list_time, last_checked_at: row.scraped_at || row.list_time, is_active: 0,
  image_urls: JSON.stringify(String(row.image_urls || "").split("\\n").filter(Boolean)), image_paths: "[]", district: row.area_name || "", address: [row.street_name, row.ward_name, row.area_name].filter(Boolean).join(", "),
})));
mkdirSync(path.join(platform, "public"), { recursive: true });
writeFileSync(path.join(platform, "public/legacy-seed.json"), JSON.stringify(rows));
const escape = (value) => String(value ?? "").replaceAll("'", "''");
const sql = rows.map((row) => `INSERT INTO listings (list_id,city,category,subject,body,price,price_string,list_time,address,district,size,rooms,toilets,latitude,longitude,account_id,account_name,phone,source_url,image_urls,image_paths,is_furnished,seller_type,first_seen_at,last_seen_at,last_checked_at,is_active) VALUES (${Number(row.list_id)},'hcm','${escape(row.category)}','${escape(row.subject)}','${escape(row.body)}',${Number(row.price) || 0},'${escape(row.price_string)}','${escape(row.list_time)}','${escape(row.address)}','${escape(row.district)}',${Number(row.size) || "NULL"},${Number(row.rooms) || "NULL"},${Number(row.toilets) || "NULL"},${Number(row.latitude) || "NULL"},${Number(row.longitude) || "NULL"},'${escape(row.account_id)}','${escape(row.account_name)}','${escape(row.phone)}','${escape(row.source_url)}','${escape(row.image_urls)}','[]',1,'u','${escape(row.first_seen_at)}','${escape(row.last_seen_at)}','${escape(row.last_checked_at)}',0) ON CONFLICT(list_id) DO NOTHING;`).join("\n");
writeFileSync(path.join(platform, "legacy-seed.sql"), sql);
const demo = rows.filter((row) => Number(row.price) >= 8_000_000).slice(0, 24).map((row) => ({ ...row, images: ["134239524", "134370281", "134369957", "134315679"].includes(String(row.list_id)) ? [`/demo-images/${row.list_id}.jpg`] : [] }));
const stats = { counts: { total: rows.length, active: rows.length, houses: rows.filter((row) => row.category === "houses").length, apartments: rows.filter((row) => row.category === "apartments").length }, districts: [...new Set(demo.map((row) => row.district))].map((district) => ({ district, count: demo.filter((row) => row.district === district).length })), latest_run: null };
writeFileSync(path.join(platform, "public/demo-listings.json"), JSON.stringify({ items: demo, total: demo.length, stats }));
console.log(`Exported ${rows.length} legacy listings; ${demo.length} demo listings.`);
