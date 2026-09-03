import { execFileSync } from "node:child_process";
import { readdirSync, statSync } from "node:fs";
import path from "node:path";

const root = path.resolve(import.meta.dirname, "../..");
const platform = path.resolve(import.meta.dirname, "..");
const groups = [["apartments", path.join(root, "data/nhatot-hcm-apartments-365d-images")], ["houses", path.join(root, "data/nhatot-hcm-houses-365d-images")]];
let uploaded = 0;
for (const [, directory] of groups) for (const id of readdirSync(directory)) {
  const folder = path.join(directory, id);
  if (!statSync(folder).isDirectory()) continue;
  for (const file of readdirSync(folder)) if (/\.(jpe?g|png|webp)$/i.test(file)) {
    execFileSync("npx", ["wrangler", "r2", "object", "put", `hcm/${id}/${file}`, `--file=${path.join(folder, file)}`, "--bucket=hcm-owner-rental-images", "--content-type=image/jpeg"], { cwd: platform, stdio: "ignore" });
    uploaded += 1;
  }
}
console.log(`Uploaded ${uploaded} images.`);
