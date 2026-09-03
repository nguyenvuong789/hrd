---
title: 'Shared map for apartment building pages'
type: 'feature'
created: '2026-09-02'
status: 'done'
baseline_commit: '968b5b68a78ae62398ae4621925380e710555036'
context:
  - 'AGENTS.md'
  - 'planning/building-template-spec.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Building detail pages explain the location in text but do not provide an immediate spatial reference or a direct route into Google Maps. Adding a one-off Monarchy embed would make later buildings harder to maintain.

**Approach:** Add optional `map_embed_url` and `map_link_url` values to the shared building registry and teach the common Location section to render a responsive, lazy-loaded Google Map only when both values are present. Enable the feature first for The Monarchy using its verified public address; buildings without verified map data retain the current text-only Location section.

## Boundaries & Constraints

**Always:** Keep map configuration in `inc/buildings.php`; render it from `page-apartment-building.php`; preserve the eight-field editorial content model; keep location text before the map on mobile; escape both URLs; use an iframe `title`, `loading="lazy"`, and a restrictive `referrerpolicy`; provide a localized “Open in Google Maps” link for `en`, `vi`, `ko`, `ja`, `ru`, and `zh`; preserve the current section order and quiet-luxury styling.

**Ask First:** Enabling a map for any building whose address or Google Maps destination has not been verified; adding coordinates, an API key, JavaScript map controls, consent tooling, or a new content/meta field; deploying to live after local verification.

**Never:** Guess a pin, derive a location from nearby landmarks, show an empty iframe, hard-code Monarchy-specific markup in the template, expose unescaped registry values, or change listings, schema, editorial facts, locale fallback, and unrelated theme behavior.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Verified map | Building has non-empty embed and link URLs | Location uses a text-led inset-map layout on desktop, stacked text-map layout on mobile, and displays the external Google Maps link | URLs are escaped at output |
| No map data | Building lacks either required URL | Existing text-only Location section renders with no iframe, blank space, or map CTA | Graceful fallback; no warning shown to visitors |
| Localized page | Current locale is one of six supported locales | Map CTA uses the matching locale label | Unknown locale falls back to English |

</frozen-after-approval>

## Code Map

- `/Users/vincent/Local Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/inc/buildings.php` -- shared registry and locale-label lookup; add optional map URLs and six-locale CTA text.
- `/Users/vincent/Local Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/page-apartment-building.php` -- common renderer; separate Location from the generic module loop and conditionally render map markup.
- `/Users/vincent/Local Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/css/building-page.css` -- responsive Location grid, iframe sizing, and understated map-link styling.
- `/Users/vincent/Local Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/css/main.css` -- increment the building-page stylesheet version to avoid stale cached CSS.

## Tasks & Acceptance

**Execution:**
- [x] `inc/buildings.php` -- add verified Monarchy map URLs and the shared localized CTA label without changing the editorial content schema.
- [x] `page-apartment-building.php` -- retain Gallery and Amenities in the simple module loop, then render Location explicitly with an optional map panel.
- [x] `css/building-page.css` -- add a restrained desktop inset at approximately 300px map height and mobile stacked layout at approximately 300px.
- [x] `css/main.css` -- update only the building CSS cache version.

**Acceptance Criteria:**
- Given The Monarchy registry entry has both verified map URLs, when its building page renders, then the Location section shows location copy, a lazy-loaded Google Map, and a localized external map link before apartment listings.
- Given another building has no verified map URLs, when its page renders, then its Location section remains text-only without broken or empty map UI.
- Given a viewport below 768px, when the Location section renders, then text precedes a full-width 300px map and no horizontal overflow occurs.
- Given any supported locale, when a mapped building renders, then the map CTA uses that locale or safely falls back to English.

## Spec Change Log

## Design Notes

Render Location outside the generic module loop. This avoids nested conditionals inside shared module markup and makes the optional-map behavior obvious to future maintainers. Store URLs rather than coordinates because the current product requirement is a verified Google Maps destination, not geographic data modeling. Keep desktop presentation text-led: the map is a framed locator inset rather than a second visual hero.

## Verification

**Commands:**
- `php -l .../inc/buildings.php && php -l .../page-apartment-building.php` -- expected: no syntax errors.
- `curl -sL http://hrd.local/apartment-buildings/the-monarchy/` with targeted assertions -- expected: iframe attributes, map CTA, location text, and section order are present.
- Render a building without map configuration locally -- expected: no iframe or map CTA.

**Manual checks (if no CLI):**
- Inspect The Monarchy at desktop and mobile widths for proportion, overflow, readable text order, keyboard-focus visibility, and correct Google Maps destination.

## Suggested Review Order

**Shared configuration**

- Start with the verified map data and reusable registry contract.
  [`inc/buildings.php:17`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/inc/buildings.php#L17)

- Confirm the CTA label covers all six supported locales.
  [`inc/buildings.php:57`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/inc/buildings.php#L57)

**Rendering and safeguards**

- Review HTTPS sanitization before the map availability decision.
  [`page-apartment-building.php:25`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/page-apartment-building.php#L25)

- Review the explicit Location renderer and text-only fallback.
  [`page-apartment-building.php:94`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/page-apartment-building.php#L94)

**Responsive presentation**

- Check the text-led desktop inset and map interaction styling.
  [`building-page.css:8`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/css/building-page.css#L8)

- Check the mobile single-column layout and 300px map frame.
  [`building-page.css:11`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/css/building-page.css#L11)

- Confirm the stylesheet cache token activates the new rules.
  [`main.css:8`](../../../../Local%20Sites/hrd/app/public/wp-content/themes/HouseRentalDanang-child-455/css/main.css#L8)
