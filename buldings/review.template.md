# Building Review

- Building:
- Slug:
- Research file:
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

- [ ] Every public claim appears in approved research.
- [ ] Reported, conflicting and estimated information remains qualified.
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
- [ ] Renting notes separate costs, apartment details and lease or move-in questions where relevant.
- [ ] FAQ prioritises availability, layouts, facilities, location and rental terms before project background.
- [ ] Related destinations are visibly distinct and understandable.
- [ ] Internal instructions, source language and dossier-style phrases have been removed.

## English approval

Use the exact lowercase values `yes` or `no`, with each status line appearing once. Set `Fact-safety approved` and `Renter-UX approved` to `yes` after their respective gates pass. Set `English approved` to `yes` only after the owner accepts the reviewed `content-en.json`. Do not translate before all three values are `yes`.

## Translation and publishing gate

- [ ] Each locale preserves the English facts, uncertainty and information grouping.
- [ ] All six locale files contain exactly the eight shared string fields and valid JSON.
- [ ] WordPress fields are updated only with approved locale content.
- [ ] All six pages are reviewed on desktop and mobile.
- [ ] No source-language leak, broken link, misleading empty state or unsafe availability wording remains.
