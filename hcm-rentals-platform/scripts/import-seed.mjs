import { execFileSync } from "node:child_process";
import { existsSync } from "node:fs";
import path from "node:path";

const platform = path.resolve(import.meta.dirname, "..");
if (!existsSync(path.join(platform, "legacy-seed.sql"))) execFileSync("node", [path.join(import.meta.dirname, "export-legacy-seed.mjs")], { stdio: "inherit" });
const local = process.argv.includes("--local");
const args = ["d1", "execute", "hcm-owner-rentals", "--file=legacy-seed.sql"];
if (local) args.push("--local"); else args.push("--remote");
execFileSync("npx", ["wrangler", ...args], { cwd: platform, stdio: "inherit" });
