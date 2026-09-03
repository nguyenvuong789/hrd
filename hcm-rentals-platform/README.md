# SG by Owner — HCM rentals platform

Production scaffold for `sgbyowner.com`: an English-first public rental catalog for foreign renters, backed by a Cloudflare Worker daily crawl. Public listing data is free for SEO. Contact fields are intentionally withheld until a visitor completes double opt-in.

## Architecture

- `worker/index.ts`: Nhatot HCM crawler, pagination (`o += 50`), filters, seller/phone frequency exclusion, D1 snapshots/history, R2 image mirror, JSON API and Cron handler.
- `migrations/0001_initial.sql`: listings, price history, listing events, crawl runs, subscribers and verified contact sessions.
- `src/`: customer-facing site. It falls back to `public/demo-listings.json` while the Worker API is not deployed.
- `scripts/export-legacy-seed.mjs`: converts the existing 881 apartment + 179 house SQLite rows into an inactive historical seed. This prevents the first daily crawl from mislabelling the old 365-day baseline as newly published.

## First deploy

1. Create the D1 database and R2 bucket, then put their IDs/name into `wrangler.jsonc`.
2. Run `npx wrangler d1 migrations apply hcm-owner-rentals --remote`.
3. Run `npm run seed:export` and `npm run seed:local` (or `node scripts/import-seed.mjs` for remote).
4. Store the admin token with `npx wrangler secret put ADMIN_TOKEN`; remove the placeholder variable if present.
5. Configure an email provider for `/api/subscribe`. The endpoint creates and logs the double-opt-in URL now; production must deliver that URL via Resend, MailChannels, SES, or another transactional provider before launch.
6. Deploy with `npm run deploy`, then point `sgbyowner.com` at the Worker/Pages custom domain.
7. Optionally mirror the historical local images with `npm run r2:legacy`. For a large backfill, run this once from a machine with Wrangler authenticated rather than from the Cron Worker.

## API

- `GET /api/listings?city=hcm&category=all&district=...&min_price=...&max_price=...&min_size=...`
- `GET /api/listings/:id` (public fields only)
- `GET /api/changes?city=hcm&date=today`
- `GET /api/runs?city=hcm`
- `GET /api/stats`
- `POST /api/subscribe` with `{ email, filters }`
- `GET /api/confirm?token=...` returns a short-lived verified contact token
- `GET /api/listings/:id/contact` with `Authorization: Bearer <contact_token>`

The API intentionally never exposes `phone` or `account_id` through public listing responses.

## Daily report fields

Each crawl records raw API count separately from filtered count, active total, new houses/apartments, disappeared listings, price changes, API/image/processing errors, duration and status (`success`, `partial` or `failed`).

## Local checks

```bash
npm run seed:export
npm run check
npm run build
sqlite3 :memory: < migrations/0001_initial.sql
```
