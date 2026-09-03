# Building Review

- Building: Azura Da Nang
- Slug: azura
- Research file: `/Users/vincent/WEBSITE/1 Websites (ME)/houserentaldanang/buldings/azura.md`
- Local URL: `http://hrd.local/apartment-buildings/azura/`
- Local English loaded: yes
- Local English desktop reviewed: yes
- Local English mobile reviewed: yes
- Fact-safety approved: yes
- Renter-UX approved: no
- English approved: no
- Translations approved: no
- Page rendered locally: yes
- Last reviewed: 2026-09-02

## Decisions

- Facts intentionally omitted: The September 2026 asking-rent ranges for one-, two- and three-bedroom apartments because the dossier marks them `DO_NOT_PUBLISH`; the management fee because it is absent from the Public Fact Pack; current availability; apartment-specific furnishing, utilities, deposit and negotiated rent; the conflicting 399 Tran Hung Dao address; unsupported view, lifestyle and management-quality claims; parking and spa because they are absent from the Public Fact Pack; all unlicensed media.
- Claims requiring confirmation: Availability and asking rent for a specific apartment; exact layout, furnishing, condition and included services; deposit, utilities and other charges; whether a management fee is included; final lease duration; written pet permission; facility access terms; exact entrance and viewing route.
- Renter questions prioritised: Available apartment formats; core shared facilities; building scale and age; developer; typical advertised minimum lease term; conditional pet policy.
- Locale-specific concerns: English only at this stage. Do not create translations until the owner approves the rendered English page.
- Link checks: `/apartments/` and `/apartments/son-tra/` returned HTTP 200 and render as two separate destinations. The Google Maps link points to the adopted 339 Tran Hung Dao Street address.

## Editorial review

- Facts intentionally omitted: See the decisions above; in particular, all observed asking-price ranges remain unpublished because the approved dossier explicitly marks them `DO_NOT_PUBLISH`.
- Claims requiring confirmation before publishing: Apartment-specific availability, price, inclusions and condition; deposit, utilities and fees; final lease term; pet permission; facility access; entrance and route.
- Fact-safety review: pass — the editorial copy, quick facts and FAQ preserve the approved building facts and qualifications; the three listing cards remain clearly labelled as separate inventory.
- Renter-UX review: fail — the content structure, paragraph separation, FAQ, related links and responsive layout pass, but all three associated listing cards reference missing Local image files and render broken image states.

## English fact-safety gate

- [x] Every editorial claim comes from an `INCLUDE_CONFIDENTLY` or `INCLUDE_WITH_CONTEXT` row in the approved Public Fact Pack; the visible address follows the approved identity table's `INCLUDE_CONFIDENTLY` row.
- [x] `INCLUDE_CONFIDENTLY` facts are stated directly without unnecessary hedging.
- [x] `INCLUDE_WITH_CONTEXT` facts retain only the material qualification supported by research.
- [x] No `INCLUDE_AS_DATED_REFERENCE` fact is available in the Public Fact Pack.
- [x] Availability, asking rent, fees, lease terms, pet policy and facility access were actively researched before fallback evidence was considered.
- [x] Omitted, conflicting, not-found, low-confidence and internal evidence has not been converted into public claims.
- [x] Confirmation wording is limited to renter-critical checks.
- [x] No unsupported availability, rent, fee, deposit, policy or lease claim appears.
- [x] Names, numbers, units and dates used in public claims are preserved.
- [x] Source URLs, source IDs and internal research labels are absent from factual public claims.

## English renter-UX gate

- [x] Every populated field answers a clear renter-facing question.
- [x] Decision-relevant information appears before developer or project-history detail.
- [x] Related facts are grouped into short, visibly separated paragraphs or labelled groups.
- [x] No field is presented as a dossier-style wall of text.
- [x] Detailed content is judged by scanability and renter relevance, not a hard word limit.
- [x] Gallery copy uses a short renter-facing empty state because no approved media was supplied.
- [x] Amenities group facilities by practical use and reserve confirmation for access terms.
- [x] Location uses the dossier's adopted 339 Tran Hung Dao Street address and gives a concise viewing-route check.
- [x] Public copy contains no map-provider attribution, research dates or route-check narration.
- [x] Renting notes lead with Azura-specific lease and pet-policy evidence.
- [x] Dated asking-price evidence marked `DO_NOT_PUBLISH` remains internal.
- [x] Renting notes could not be pasted unchanged onto an unrelated building.
- [x] Every visible FAQ provides a direct building-specific answer in its first sentence.
- [x] No FAQ exists solely to tell the renter to confirm, check or contact the local team.
- [x] Questions without supported answers were omitted rather than retained for keyword coverage.
- [x] Related destinations are distinct and understandable.
- [x] Internal instructions, source language and dossier-style phrases have been removed from factual public claims.

## Local English render gate

- [x] `content-en.json` was loaded into the exact Local building page using the shared template.
- [ ] The rendered desktop page passes the complete renter-UX check; three listing-card image URLs return 404.
- [ ] The rendered mobile page passes the complete renter-UX check; the same three listing-card images are missing.
- [x] The mobile layout remains readable and scannable with no horizontal page overflow at a 390 × 844 viewport.
- [x] Quick facts, editorial content, FAQ, related links, gallery state and listing-card facts work together without contradiction.
- [x] Seven FAQ pairs render as separate items, and the two related links render as distinct destinations.
- [x] No dated rental reference is published; the September 2026 asking ranges marked `DO_NOT_PUBLISH` remain internal and cannot imply current inventory.
- [x] The location map loads after entering the viewport and its external map link uses the adopted address.
- [x] Corrections made after the first render were saved back to `content-en.json`, revalidated and reloaded.

Blocking Local issue: the three Azura property cards reference missing files at `/wp-content/uploads/hrd-rentals/2026/08/hrd-41098-01.jpg`, `/wp-content/uploads/hrd-rentals/2026/08/hrd-31739-01.jpg` and `/wp-content/uploads/hrd-rentals/2026/08/hrd-31227-01.jpg`. Each URL returns HTTP 404. This is an existing Local inventory-media issue, not a `content-en.json` issue.

## English approval

The English package is loaded and reviewed on Local. `English approved` remains `no` until the owner accepts the rendered page. The missing listing-card images should be restored or deliberately waived before `Renter-UX approved` changes to `yes`.

## Translation and publishing gate

- [ ] Each locale preserves the English facts, uncertainty and information grouping.
- [ ] All six locale files contain exactly the eight shared string fields and valid JSON.
- [ ] WordPress fields are updated only with approved locale content.
- [ ] All six pages are reviewed on desktop and mobile.
- [ ] No source-language leak, broken link, misleading empty state or unsafe availability wording remains.
