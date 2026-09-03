# SEO Audit – Important Pages

**Domain:** https://houserentaldanang.com
**Audited:** 2026-08-30 (live HTTPS HTML, sitemap and robots checks)
**Scope:** Homepage, conversion pages, rental category pages, district/building pages, trust pages, FAQ hub, and high-value guide content. Individual property URLs and low-priority archive pages are excluded from this strategic pass.

## Executive Summary

The site is technically crawlable and unusually well prepared for multilingual discovery: production pages return HTTP 200, are server-rendered, expose self-canonicals, `index, follow`, and six-language hreflang links. The homepage has a clear rental proposition, substantial copy, and a roughly 150-word answer block suitable for AI extraction. The canonical public NAP is now set to `201 Chương D., Ngũ Hành Sơn, Đà Nẵng 550000`, with no fixed phone displayed; schema and output normalization should keep all templates aligned.

## Important-Page Scorecard

| Page group | URLs sampled | On-page | Content | Technical | Schema | Images | Priority |
|---|---:|---:|---:|---:|---:|---:|---|
| Homepage | 1 | 92 | 88 | 91 | 62 | 76 | High |
| Rental category hubs | 3 | 90 | 86 | 91 | 64 | 76 | High |
| Search/conversion | 2 | 84 | 72 | 90 | 60 | 72 | High |
| District/location pages | 4 | 88 | 84 | 90 | 63 | 75 | Critical |
| Apartment buildings | 2 | 89 | 84 | 90 | 68 | 78 | High |
| Trust/contact/FAQ | 5 | 86 | 79 | 90 | 63 | 72 | High |
| Guide articles | 7 | 82 | 84 | 88 | 60 | 74 | Medium |

**Portfolio score: 82/100.** This is an evidence-based HTML score, not a ranking prediction; no Search Console, live SERP positions, geo-grid or field CWV data was available.

## Cross-Page Findings

### Critical

1. **NAP/entity conflict.** The footer shows `Ngu Hanh Son, Da Nang 550000, Vietnam` and `hello@houserentaldanang.com`; agent content exposes `House Rental Danang Agency, 201 Chuong Duong, 0936023079`. Choose one public business address/phone policy, then align footer, contact page, Organization/RealEstateAgent schema, GBP, Bing Places, Apple Business Connect and directories.
2. **No detectable local entity schema.** Sampled pages emit `WebPage` plus publisher `Organization`, but no `LocalBusiness`, `RealEstateAgent`, `RealEstateListing`, `PostalAddress`, `geo`, `openingHoursSpecification`, `telephone`, or `sameAs` graph. Add one canonical Organization/RealEstateAgent graph on the site and listing-level `RealEstateListing`/`Offer` data where factual.
3. **Location-page governance.** District and building pages are valuable, but maintain unique neighbourhood evidence (local photos, commute/amenity facts, local FAQs and testimonials) and an explicit last-reviewed date to avoid doorway-page patterns as inventory scales.

### High

4. **Social metadata is incomplete.** Most sampled pages lack `og:url`, Twitter card/title/description; several category/location pages also lack `og:image`. Add a consistent per-page OG/Twitter set with absolute canonical URLs and a representative image.
5. **Image accessibility/CLS debt.** Across sampled pages, roughly 8 images per page are missing `alt` (often theme chrome) and 12–15 lack both explicit dimensions; reserve dimensions and descriptive alt text for listing/editorial images, and lazy-load below-fold media.
6. **Conversion affordances.** Contact and search pages have clear forms, but phone/WhatsApp is not consistently a `tel:` link. Add a single visible click-to-call/WhatsApp CTA and business hours where applicable.

### Medium

7. **Guide freshness.** Several guide titles/descriptions still reference 2019 or July 2024. Add `dateModified`, a “prices/availability checked” note, and update outdated claims or redirect thin legacy posts into stronger hubs.
8. **Citation-ready passages.** Keep direct answers within the first 40–60 words of each section and add 134–167-word self-contained blocks with sources for rent ranges, lease rules, neighbourhood comparisons and transport times.
9. **Internal-link consistency.** Preserve hub-and-spoke links from homepage → type → district → building/property, and add contextual links from guide articles back to relevant rental hubs (2–5 per 1,000 words).

## Page-Level Notes

- **Homepage:** 2,436 words, one H1, canonical/robots/hreflang present, six valid language roots; strong local intent and FAQ-style blocks. Fix empty WebPage `description`, entity schema, OG/Twitter completeness and image dimensions.
- **Houses / Apartments / Villas:** 1,479 / 3,514 / 2,341 words; strong category intent and useful qualification copy. Add type-specific `ItemList`/`Offer` only for visible factual listings; keep availability caveats.
- **Search / Contact:** 959 / 525 words; conversion-ready and SSR. Add stronger trust proof, consistent NAP, `tel:`/WhatsApp links, and noindex/filter policy for parameter URLs if not already enforced.
- **District pages:** Son Tra, Ngu Hanh Son, Hai Chau and An Thuong pages have 865–4,171 words and one H1. They are the highest local-organic opportunity; strengthen local proof and schema per district.
- **Buildings:** The Filmore and The Monarchy pages have 630–774 words and one H1. Add building facts, map/coordinates, developer/source attribution, current availability timestamp and unique `ApartmentComplex`/`Place` relationship where accurate.
- **Trust/FAQ:** Why Us, Testimonials, Agents and FAQs provide conversion support. Add author/reviewer identity, review source links and non-FAQ structured answer blocks; do not add restricted FAQ rich-result schema for commercial pages.
- **Guides:** Cost of living, Son Tra area, studio, hostel, My Khe, Ba Na Hills and Da Nang–Hoi An support topical authority. Separate travel intent from rental intent with explicit “how this helps renters” links and update old dates.

## Implementation Order

1. Resolve canonical NAP and publish a single `Organization` + `RealEstateAgent` JSON-LD graph with verified `sameAs` profiles.
2. Add complete OG/Twitter metadata and representative image handling in the shared SEO layer.
3. Add local schema and unique evidence modules to Son Tra, Ngu Hanh Son, Hai Chau, An Thuong and building pages.
4. Fix image alt/dimension/lazy-loading defaults in the child theme and audit the largest hero/listing assets.
5. Refresh dated guides and create source-backed, citation-ready answer blocks.
6. Validate all six homepage/locales with `php scripts/check-homepage-localization.php`, then render them and check language leaks, links, canonicals and robots.

## Limitations

This audit did not measure Google/Bing positions, geo-grid visibility, GBP Insights, review velocity, complete backlinks/citations, real-user Core Web Vitals, or live ChatGPT/Perplexity citations. Those require Search Console/GA4, GBP access, a maps/grid provider, field performance data and paid/connected AI visibility tools.
