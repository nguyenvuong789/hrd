# Building Review

- Building: Hiyori Garden Tower Da Nang
- Slug: hiyori-garden-tower-da-nang
- Research file: `buldings/hiyori-garden-tower-da-nang-research.md`
- Local URL: `http://hrd.local/apartment-buildings/hiyori-garden-tower/`
- Local English loaded: yes
- Local English desktop reviewed: no
- Local English mobile reviewed: no
- Fact-safety approved: no
- Renter-UX approved: no
- English approved: no
- Translations approved: no
- Page rendered locally: no
- Last reviewed: 2026-09-02

## Decisions

- Facts intentionally omitted: Exact apartment total and two-bedroom unit count; exact completion month; current operator; current availability and current rental rate; undated USD 600 and USD 1,500 offers; management fees, utilities, deposits and lease policies; unsupported renter-fit claims; unlicensed project imagery and schema coordinates.
- Claims requiring confirmation: Specific apartment availability; asking rent and inclusions; deposit and lease duration; management fees and utilities; layout, measurement basis, usable area, floor, orientation, balcony, view, condition and furnishing inventory; parking eligibility, allocation, vehicle limits and charges; facility access, hours, charges and condition; kindergarten operation or enrolment; pet, guest and move-in rules.
- Renter questions prioritised: Published layouts and sizes; documented facilities and parking capacity; measured routes to practical destinations; dated asking-price evidence and inclusions; completion, floor structure and developer.
- Locale-specific concerns: English only at this stage. Do not create locale files until the owner approves this package and the English status changes to `yes`.
- Link checks: `/apartments/` and `mailto:hello@houserentaldanang.com` render on the local page. Local preview: `http://hrd.local/apartment-buildings/hiyori-garden-tower/`.

## Editorial review

- Facts intentionally omitted: Conflicting unit totals, exact completion month, unverified operator and services, current rental figures or inventory, undated price examples, unsupported fees and policies, lifestyle labels, and media without established reuse rights.
- Claims requiring confirmation before publishing: All apartment-level availability, price, area, furnishing, orientation, view and condition details; lease economics and rules; parking arrangements; current amenity operation and access.
- Fact-safety review: fail — the revised editorial fields follow the Public Fact Pack, but the rendered listing grid includes a 2-bedroom card showing 250 sqm, which conflicts with the published 2-bedroom schedule and requires data review.
- Renter-UX review: fail — the rewritten fields are clearer and evidence-led, but the contradictory listing card prevents the complete Local page from giving renters a coherent comparison.

## English fact-safety gate

- [x] Every public claim comes from an `INCLUDE_CONFIDENTLY`, `INCLUDE_WITH_CONTEXT` or `INCLUDE_AS_DATED_REFERENCE` row in the approved Public Fact Pack.
- [x] `INCLUDE_CONFIDENTLY` facts are stated directly without unnecessary hedging.
- [x] `INCLUDE_WITH_CONTEXT` facts retain only the material qualification supported by research.
- [x] The 2021 USD 700/month offer preserves its dates and apartment-specific scope; route-check dates remain in research rather than public copy.
- [x] Availability, asking rent, fees, lease terms, pet policy and facility access are not presented as current facts.
- [x] Omitted, conflicting, not-found, low-confidence and internal evidence has not been converted into public copy.
- [x] Confirmation wording is limited to renter-critical checks explicitly assigned by the approved dossier.
- [x] No unsupported availability, rent, fee, deposit, policy or lease claim appears.
- [x] Names, addresses, numbers, units and dates are preserved.
- [x] Source URLs, source IDs and internal research labels are absent from publishable fields.

## English renter-UX gate

- [x] Every field answers a clear renter-facing question.
- [x] Decision-relevant information appears before developer or project-history detail.
- [x] Related facts are grouped into short, visibly separated paragraphs or labelled groups.
- [x] No field is presented as a dossier-style wall of text.
- [x] Detailed content is judged by scanability and renter relevance, not a hard word limit.
- [x] Gallery copy uses a short renter-facing empty state.
- [x] Amenities separate documented facilities from details that still require confirmation.
- [x] Location prioritises the public address and practical road orientation.
- [x] Location states approximate distances and walking times directly without map-provider attribution, check dates or research narration.
- [x] Renting notes lead with the dated USD 700/month asking example and its recorded inclusions.
- [x] Older rental evidence is retained as a clearly dated comparison.
- [x] Generic confirmation advice does not replace available price, inclusion, parking or route evidence.
- [x] Renting notes are specific to Hiyori Garden Tower.
- [x] Every visible FAQ provides a direct building-specific answer in its first sentence.
- [x] No FAQ exists solely to tell the renter to confirm, check or contact the local team.
- [x] Questions without supported answers were omitted rather than retained for keyword coverage.
- [x] Related destinations are visibly distinct and understandable.
- [x] Internal instructions, source language and dossier-style phrases have been removed.
- [x] Public copy states supported facts directly rather than using source-led phrases such as `project documents include`, `the reviewed documents` or `the source states`.

## Local English render gate

- [x] `content-en.json` was loaded into the exact Local building page using the shared template.
- [ ] The rendered desktop page passes the complete fact-safety and renter-UX checks.
- [ ] The rendered mobile page passes the complete fact-safety and renter-UX checks.
- [x] No horizontal overflow was detected at 1512 px or 390 px viewport widths.
- [x] Seven FAQ pairs render as separate semantic items.
- [ ] Quick facts, editorial content and listing cards work together without contradiction.
- [x] Dated references are visibly different from current facts and do not imply current inventory.

Blocking Local issue: the listing grid contains a 2-bedroom card labelled `250 sqm`, outside the project-published 2-bedroom range of 63.3–71.93 m². Review that listing record before approving the complete page.

## English approval

The revised English fields are loaded on Local, but approval remains blocked by the contradictory listing-card area. Do not translate until the Local page passes and all English approval values are `yes`.

## Translation and publishing gate

- [ ] Each locale preserves the English facts, uncertainty and information grouping.
- [ ] All six locale files contain exactly the eight shared string fields and valid JSON.
- [ ] WordPress fields are updated only with approved locale content.
- [ ] All six pages are reviewed on desktop and mobile.
- [ ] No source-language leak, broken link, misleading empty state or unsafe availability wording remains.
