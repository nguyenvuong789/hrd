# Building Review

- Building:
- Slug:
- Research file:
- Local URL:
- Local English loaded: no
- Local English desktop reviewed: no
- Local English mobile reviewed: no
- Fact-safety approved: no
- Renter-UX approved: no
- English approved: no
- Translations approved: no
- Page rendered locally: no
- Last reviewed:

## Decisions

- Facts intentionally omitted:
- Claims requiring confirmation:
- Renter questions prioritised:
- Locale-specific concerns:
- Link checks:

## English fact-safety gate

- [ ] Every public claim comes from an `INCLUDE_CONFIDENTLY`, `INCLUDE_WITH_CONTEXT` or `INCLUDE_AS_DATED_REFERENCE` row in the approved Public Fact Pack.
- [ ] `INCLUDE_CONFIDENTLY` facts are stated directly without unnecessary hedging.
- [ ] `INCLUDE_WITH_CONTEXT` facts retain only the material qualification supported by research.
- [ ] `INCLUDE_AS_DATED_REFERENCE` facts are no older than 12 months, follow a documented search for fresher evidence, and preserve date, evidence type and limited scope.
- [ ] Availability, asking rent, fees, lease terms, pet policy and facility access were actively researched through current Google/web searches before any fallback evidence was considered.
- [ ] Omitted, conflicting, not-found, low-confidence and internal evidence has not been converted into public copy.
- [ ] Confirmation wording is limited to renter-critical checks explicitly assigned by the approved dossier.
- [ ] No unsupported availability, rent, fee, deposit, policy or lease claim appears.
- [ ] Names, addresses, numbers, units and dates are preserved.
- [ ] Source URLs, source IDs and internal research labels are absent from publishable fields.

## English renter-UX gate

- [ ] Every field answers a clear renter-facing question.
- [ ] Decision-relevant information appears before developer or project-history detail.
- [ ] Related facts are grouped into short, visibly separated paragraphs or labelled groups.
- [ ] No field is presented as a dossier-style wall of text.
- [ ] Detailed content is judged by scanability and renter relevance, not a hard word limit.
- [ ] Gallery copy is based on supplied images or uses a renter-facing empty state.
- [ ] Amenities separate practical facilities from details that still require confirmation.
- [ ] Location prioritises the public address and information useful for choosing a home.
- [ ] Location copy states approximate distances and travel times directly without `when checked`, map-provider attribution, research dates or access dates.
- [ ] Public copy states supported facts directly rather than using source-led phrases such as `project documents include`, `the reviewed documents`, `the source states` or `during research`.
- [ ] Map providers, route-check dates, access dates and research mechanics remain internal unless a date is materially necessary to understand a historical price, legal change or other time-bound fact.
- [ ] Renting notes separate costs, apartment details and lease or move-in questions where relevant.
- [ ] Renting notes lead with building-specific values, dates and terms where research found them.
- [ ] Dated rental evidence is used only within the 12-month fallback window; older material remains internal and unpublished.
- [ ] Generic confirmation advice does not replace available asking-rent, fee, deposit, lease, policy, parking or facility-access evidence.
- [ ] Renting notes could not be pasted unchanged onto an unrelated building.
- [ ] Every visible FAQ provides a direct building-specific answer in its first sentence.
- [ ] No FAQ exists solely to tell the renter to confirm, check or contact the local team.
- [ ] Questions without supported answers were omitted rather than retained for keyword coverage.
- [ ] Related destinations are visibly distinct and understandable.
- [ ] Internal instructions, source language and dossier-style phrases have been removed.

## Local English render gate

- [ ] `content-en.json` was loaded into the exact Local building page using the shared template.
- [ ] The rendered desktop page passes the fact-safety and renter-UX checks.
- [ ] The rendered mobile page remains readable, scannable and free of overflow.
- [ ] Quick facts, editorial content, FAQ, related links, gallery state and listing cards work together without contradiction.
- [ ] Dated references are visibly different from current facts and do not imply current inventory.
- [ ] Any correction made after rendering was saved back to `content-en.json` and reloaded.

## English approval

Use the exact lowercase values `yes` or `no`, with each status line appearing once. Set the three Local English fields, `Fact-safety approved` and `Renter-UX approved` to `yes` only after the rendered Local page passes. Set `English approved` to `yes` only after the owner accepts that rendered page. Do not translate before all six values are `yes`.

## Translation and publishing gate

- [ ] Each locale preserves the English facts, uncertainty and information grouping.
- [ ] All six locale files contain exactly the eight shared string fields and valid JSON.
- [ ] WordPress fields are updated only with approved locale content.
- [ ] All six pages are reviewed on desktop and mobile.
- [ ] No source-language leak, broken link, misleading empty state or unsafe availability wording remains.
