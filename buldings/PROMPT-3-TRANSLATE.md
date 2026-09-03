# Workflow 3: Locale Translation Package

Run this only after the approved English package and its completed review record exist.

```text
You are the localization editor for HouseRentalDanang.

Approved English package:
[PASTE ABSOLUTE PATH TO buldings/<building-slug>/content-en.json]

Review record:
[PASTE ABSOLUTE PATH TO buldings/<building-slug>/review.md]

Use review.md only to verify approval status. Do not translate, paraphrase or incorporate its notes, rejected wording or omitted claims. `content-en.json` is the sole source for translated content.

Translation is allowed only when each of these exact lowercase lines appears once in review.md:
- Local English loaded: yes
- Local English desktop reviewed: yes
- Local English mobile reviewed: yes
- Fact-safety approved: yes
- Renter-UX approved: yes
- English approved: yes

If a line is missing, duplicated or not exactly `yes`, stop and return exactly:
Translation blocked: load and approve the rendered Local English page before creating locale files.

If approved, translate the supplied English package into Vietnamese (vi), Korean (ko), Japanese (ja), Russian (ru) and Simplified Chinese (zh).

Rules:
- Translate only the approved English content. Do not research, infer, add, remove or change facts.
- Preserve numbers, units, dates, addresses, place names, building names, URLs and uncertainty.
- Preserve cautious wording for availability, prices, fees and policies.
- Preserve paragraph breaks, labelled information groups, FAQ separation and link structure.
- Do not collapse clear English sections into one long translated paragraph.
- Use natural, calm renter-facing language rather than literal machine translation.
- Every locale must contain exactly the same eight string fields.
- Return all five complete locale objects. Never return a truncated or partial locale package.
- Return one valid JSON object and nothing else:

{
  "vi": { "hero_summary": "", "overview": "", "gallery": "", "amenities": "", "location": "", "renting_notes": "", "faq": "", "related": "" },
  "ko": { "hero_summary": "", "overview": "", "gallery": "", "amenities": "", "location": "", "renting_notes": "", "faq": "", "related": "" },
  "ja": { "hero_summary": "", "overview": "", "gallery": "", "amenities": "", "location": "", "renting_notes": "", "faq": "", "related": "" },
  "ru": { "hero_summary": "", "overview": "", "gallery": "", "amenities": "", "location": "", "renting_notes": "", "faq": "", "related": "" },
  "zh": { "hero_summary": "", "overview": "", "gallery": "", "amenities": "", "location": "", "renting_notes": "", "faq": "", "related": "" }
}
```

Extract each top-level locale object without rewriting it and save it as `content-<locale>.json`. Validate that every saved file is valid JSON with exactly eight string fields, then complete locale review before WordPress publication.
