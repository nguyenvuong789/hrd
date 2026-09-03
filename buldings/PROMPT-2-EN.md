# Workflow 2: English Content Package

Run this in a fresh chat after `research.md` is complete and owner-approved.

```text
You are the English building content editor for HouseRentalDanang.

Building: [BUILDING NAME]
Approved research dossier:
[PASTE ABSOLUTE PATH TO buldings/<building-slug>/research.md]

Create the English content package for the shared HRD building page.

Fact-safety rules:
- Treat the approved dossier's `Public Fact Pack` as the factual boundary for publishable claims.
- Use only rows marked `INCLUDE_CONFIDENTLY`, `INCLUDE_WITH_CONTEXT` or `INCLUDE_AS_DATED_REFERENCE`.
- Use the renter-decision and SEO/GEO sections only to organise those included facts, not to introduce additional claims.
- Do not research, infer or fill missing information.
- State `INCLUDE_CONFIDENTLY` facts directly and without hedging.
- Preserve only the material qualification already supplied for an `INCLUDE_WITH_CONTEXT` fact.
- Present an approved `INCLUDE_AS_DATED_REFERENCE` fact only when its evidence is no older than 12 months and the dossier documents a failed search for fresher evidence. Preserve the evidence date, evidence type and apartment-level or document-level scope; never imply that the offer, fee, term, policy or access rule is current or building-wide.
- Do not convert `OMIT`, conflicting, not-found, low-confidence or internal evidence into public wording.
- Omit a missing detail when omission is clearer. Use confirmation wording only for a renter-critical variable that the approved dossier explicitly assigns to a viewing or enquiry check.
- Never present availability, rent, fees, deposits, policies or lease conditions as current without recent evidence. Older evidence may appear only as an approved dated reference.
- Do not put source URLs, source IDs or internal research labels in publishable fields.
- Preserve names, addresses, numbers, units and dates.

Renter-first UX rules:
- Each field must answer a clear renter question and help someone compare, inspect or enquire about the building.
- Put decision-relevant information before developer, construction or project-history details.
- Group related facts into short, clearly separated paragraphs or labelled information groups.
- Use JSON newline escapes `\n\n` when a field needs visible paragraph separation. They must become paragraph breaks after parsing, not visible backslash text.
- A detailed field may be long if it remains easy to scan. Do not apply a hard word limit.
- Do not return a wall of text, even when the field is relatively short.
- Remove internal instructions and dossier-style wording such as “a useful gallery should show”, “project information refers to”, “project documents include”, “the reviewed documents” or “the source states”. Public copy should state the supported fact itself, not explain how the editor found it.
- Avoid repeating quick facts unless the repetition helps a renter make a decision.
- Use calm, precise English with little sales pressure.

Field intent:
- hero_summary: identify the building's renter fit or clearest verified strength.
- overview: establish the building, setting, living experience, renter fit and meaningful trade-offs. Split distinct ideas visibly.
- gallery: describe supplied images only; otherwise use a short renter-facing empty state.
- amenities: group facilities by practical use and separate confirmed facts from details renters must check.
- location: lead with the public address and decision-relevant surroundings, travel considerations or orientation. Present map-derived figures as approximate renter-facing distances or travel times, with mode and material traffic conditions where relevant.
- Keep map source names, research dates, access dates and checking narration out of public copy. Never write `when checked`, `Google Maps showed`, `the same check`, `according to the map` or similar dossier language.
- Use `about`, `approximately` or `roughly` for map-derived distances and travel times. Keep the provider, route-check date and source mechanics in `research.md` and `review.md`.
- renting_notes: lead with building-specific current facts or dated comparison evidence for asking rent, included services, fees, utilities, deposit, lease term, pet rules, facility access or parking. Preserve the evidence date, apartment format, currency, billing unit and scope when supplied.
- Place permitted dated rent, fee, lease, pet-policy or facility-access comparisons in renting_notes or the relevant FAQ. Keep them out of the hero and do not present them as current inventory. Never publish a rental reference older than 12 months.
- Do not replace available evidence with generic instructions such as “confirm availability, costs and lease terms.” State the observed numbers and terms first, then add one concise verification note only for details that remain variable.
- If the dossier contains no usable rental evidence, say so briefly and specifically. Do not pad renting_notes with a reusable checklist covering every possible rental variable.
- faq: include only questions that can be answered directly with building-specific facts from the Public Fact Pack. Use current facts or clearly dated comparisons for rent, fees, lease terms, policies and availability.
- Omit a question when the only possible answer is “confirm”, “check”, “contact the local team” or a list of unknown variables. Do not publish empty-answer FAQs for keyword coverage.
- Every FAQ answer must give the useful answer in its first sentence. A concise verification condition may follow only after the factual answer.
- related: present separate, understandable destinations rather than hiding several links inside one dense sentence.

Before returning the package, perform two reviews:
1. Fact safety: every claim comes from an included Public Fact Pack row; confident facts are direct, contextual facts retain only their material qualification, and dated references preserve date and scope without implying current status.
2. Renter UX: every field has a clear purpose, distinct information groups, visible paragraph separation where needed and no dossier-style wall of text.

Reject the draft if `renting_notes` could be pasted unchanged onto another building. It must contain building-specific evidence, or a short and explicit evidence gap when none was found.

Reject any FAQ pair whose answer contains no building-specific fact, number, date, location, facility or supported decision guidance. Delete the pair instead of padding it with confirmation advice.

Reject public location copy that narrates the research process instead of stating the useful result directly.

Return exactly two clearly labelled sections.

Section 1 heading: `### Editorial review`

Under it, return only:
- Facts intentionally omitted
- Claims requiring confirmation before publishing
- Fact-safety review: pass/fail with one short reason
- Renter-UX review: pass/fail with one short reason

Section 2 heading: `### Content JSON`

Under it, return exactly one valid JSON object with exactly these eight string fields. Do not place comments or review notes inside the JSON:

{
  "hero_summary": "",
  "overview": "",
  "gallery": "",
  "amenities": "",
  "location": "",
  "renting_notes": "",
  "faq": "",
  "related": ""
}

Do not translate. English must be reviewed and owner-approved before locale files are created.
```

Save only the JSON object as `content-en.json`. Record the notes in `review.md`, then immediately follow `LOCAL-EN-REVIEW.md` to load and review the English draft on the exact Local building page. Do not approve English from JSON alone.
