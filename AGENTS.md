# House Rental Danang Project Rules

## Local WordPress Runtime

- Make active WordPress code changes directly in `/Users/vincent/Local Sites/hrd/app/public/` (the running Local WP site).
- The repository at `/Users/vincent/WEBSITE/1 Websites (ME)/houserentaldanang` is for project files, reports and controlled synchronization; do not assume it is the active runtime copy.
- When a task changes the child theme, verify both the Local WP copy and any explicitly requested production upload.

## Homepage Localization Quality Gate

- Supported homepage locales are `en`, `vi`, `ko`, `ja`, `ru`, and `zh`.
- Homepage copy may come from RealHomes post meta, child-theme PHP, shortcodes, widgets, or partials; never assume editing page content alone localizes the rendered page.
- When adding or changing a homepage section, add and review every locale mapping for its title, description, CTA labels, FAQ, footer labels, links, and machine-readable metadata.
- Before committing or deploying homepage/localization changes, run:

  ```bash
  php scripts/check-homepage-localization.php
  ```

- Do not commit or deploy if the guard fails. Add the missing locale mapping or update the required-key list when a deliberately new homepage field is introduced.
- After live changes, render all six homepage URLs and check for source-language leaks, missing sections, incorrect links, canonical tags, and `index, follow` robots directives.
- Keep the guard script and the live/local child-theme copies synchronized.

## Live Uploads Synchronization

- Use SSH + `rsync` for the large uploads directory; the cPanel SSH endpoint is `osault1.hostarmada.net` on port `19199` and the cPanel user is `vinnguye`.
- Local uploads path: `/Users/vincent/Local Sites/hrd/app/public/wp-content/uploads/`.
- Live uploads path: `/home2/vinnguye/houserentaldanang.com/wp-content/uploads/`.
- Check for new or size-changed files first with this non-mutating command:

  ```bash
  rsync -azn --size-only --stats \
    --exclude='.DS_Store' \
    -e "ssh -p 19199 -i ~/.ssh/hrd_cpanel/hrd_mac/hrd_mac" \
    "/Users/vincent/Local Sites/hrd/app/public/wp-content/uploads/" \
    vinnguye@osault1.hostarmada.net:/home2/vinnguye/houserentaldanang.com/wp-content/uploads/
  ```

- To upload after reviewing the dry-run, remove only the `-n` flag. Never use `--delete` by default; preserve files already present on live.

## Live Theme Synchronization

- Use SSH + `rsync` for the child theme directory; the cPanel SSH endpoint is `osault1.hostarmada.net` on port `19199` and the cPanel user is `vinnguye`.
- Local child-theme path: `/Users/vincent/WEBSITE/1 Websites (ME)/houserentaldanang/wp-content/themes/HouseRentalDanang-child-455/`.
- Live child-theme path: `/home2/vinnguye/houserentaldanang.com/wp-content/themes/HouseRentalDanang-child-455/`.
- Check changed files first with this non-mutating command:

  ```bash
  rsync -azn --size-only --stats \
    --exclude='.DS_Store' \
    -e "ssh -p 19199 -i ~/.ssh/hrd_cpanel/hrd_mac/hrd_mac" \
    "/Users/vincent/WEBSITE/1 Websites (ME)/houserentaldanang/wp-content/themes/HouseRentalDanang-child-455/" \
    vinnguye@osault1.hostarmada.net:/home2/vinnguye/houserentaldanang.com/wp-content/themes/HouseRentalDanang-child-455/
  ```

- After reviewing the dry-run, remove only `-n` to upload. Never use `--delete` by default; preserve files already present on live.

## Taxonomy Editorial Descriptions

- Every taxonomy with `show_ui` enabled must provide the Visual/Text editor for formatted descriptions.
- Location hub copy for `property-city` is managed from the WordPress taxonomy description in each locale: `en`, `vi`, `ko`, `ja`, `ru`, and `zh`.
- Do not add locale-specific hard-coded overrides that replace a non-empty admin description.
- Frontend rendering must preserve safe HTML formatting from the admin description. If a translated location description is empty, fall back to the English guide.
- After changing this flow, verify the Visual/Text editor, HTML rendering, locale fallback, PHP syntax, local runtime, and live theme copy.

## Building Detail Pages

- Building pages use the shared child-theme template `page-apartment-building.php`.
- Building registry, locale labels, content fields and listing query are centralized in `inc/buildings.php`.
- Building-specific editorial content is stored per locale in `hrd_building_content_{locale}` post meta.
- Supported locales are `en`, `vi`, `ko`, `ja`, `ru`, and `zh`.
- Locale content falls back field-by-field to English when a translation is missing.
- Use the shared fields: `hero_summary`, `overview`, `gallery`, `amenities`, `location`, `renting_notes`, `faq`, and `related`.
- Do not hard-code building copy or locale labels inside the page template.
- After changing the building template or content layer, verify PHP syntax and render at least one building page locally.
- Building research and content workflow files live in `buldings/`; use `buldings/WORKFLOW.md` for the required order.
- Never translate raw research: approve `content-en.json` before creating locale JSON files.
- Each building folder uses `research.md`, `content-en.json`, one file per locale, and `review.md`.

## Nhà Tốt City Dataset Inventory

- TP.HCM and Hà Nội already have historical Nhà Tốt datasets. Treat both cities as incremental-update tasks; never initialize a replacement dataset, start a fresh full download, or overwrite an existing dashboard without first locating and inspecting the old artifacts.
- TP.HCM uses two existing 365-day datasets:
  - Houses: `data/nhatot-hcm-houses-365d.csv`, `data/nhatot-hcm-houses-365d.sqlite`, and `data/nhatot-hcm-houses-365d-images/`.
  - Apartments: `data/nhatot-hcm-apartments-365d.csv`, `data/nhatot-hcm-apartments-365d.sqlite`, and `data/nhatot-hcm-apartments-365d-images/`.
  - Dashboards: `output/nhatot-hcm-houses-365d-dashboard.html`, `output/nhatot-hcm-apartments-365d-dashboard.html`, and the combined `output/nhatot-hcm-dashboard.html`.
  - Daily updater: `scraper/run_hcm_daily.sh`; it must update the two datasets above and rebuild the combined dashboard.
- Hà Nội also has an existing dataset, but its current paths have not yet been located in this workspace. Before any Hà Nội crawl, search for and confirm its existing CSV, SQLite, image directory, and dashboard with the user. Do not create `data/nhatot-ha-noi.*`, a new image tree, or a replacement dashboard merely because the old paths are not immediately visible.
- For every city, preserve phone numbers previously revealed manually in the browser. An empty or masked API value must never overwrite a stored phone number.
- Download only missing images. Do not duplicate an existing city image collection under a new directory name.
- Exclude `nhà cấp 4` and transfer/takeover listings, including sang nhượng tiệm, spa, salon, quán, trọ/dãy trọ, nhà, vị trí kinh doanh, mặt bằng, CHDV, căn hộ dịch vụ, sang hợp đồng nhà thuê, or nhượng hợp đồng thuê.
- TP.HCM price thresholds are city/category specific: houses must be at least VND 10,000,000 per month; apartments remain at least VND 8,000,000 per month.
- Persist price history in CSV and SQLite. When a listing changes price, every dashboard must show the previous price struck through, the current price beside it, and the date the change was detected; later crawls must preserve this history.
- Every Nhà Tốt dashboard must include an `Đã đổi giá` control that filters the cards down to listings with stored price-change history.
