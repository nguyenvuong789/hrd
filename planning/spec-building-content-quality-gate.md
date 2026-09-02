---
title: 'Reusable renter-first building content quality gate'
type: 'chore'
created: '2026-09-02'
status: 'done'
baseline_commit: 'c2e615ff12ab53c6a3ed68b02357e32b99c4dbd5'
context:
  - 'buldings/WORKFLOW.md'
  - 'planning/building-pages-prd.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The current building workflow protects factual accuracy but can still produce long dossier-style paragraphs that are difficult for renters to scan. The combined English-and-translation prompt can also propagate an unapproved English structure into every locale.

**Approach:** Add one reusable renter-first quality gate to the existing workflow. English must pass separate fact-safety and UX reviews before owner approval unlocks translation; UX review judges information grouping, short separated paragraphs, renter relevance and scanability without hard word-count limits.

## Boundaries & Constraints

**Always:** Keep the existing eight-field schema. Use only approved research facts. Preserve uncertainty and cautious availability language. Evaluate each field by whether renters can identify its purpose, scan distinct information groups and find decision-relevant details. Keep workflow instructions compact and avoid duplicating the same rules across several files when a short reference is sufficient.

**Ask First:** Changing the eight-field schema, adding or removing supported locales, deleting approved building content, or introducing a mandatory numerical length limit.

**Never:** Translate before English owner approval; generate English and locales as one production step; impose hard word limits; treat one long paragraph as acceptable merely because its total word count is low; add research facts, prices, availability, policies or renter claims not supported by the approved dossier; change the shared WordPress building template as part of this task.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| English draft | Approved `research.md` | Create only `content-en.json`, followed by fact-safety and renter-UX review | Do not create locale JSON files |
| UX-ready English | Facts are safe and information is grouped into short, clearly separated renter-facing sections | Owner may mark English approved and start translation | Record approval in `review.md` |
| Dense but factually correct English | A field is a wall of text, uses dossier language, or buries renter decisions | English remains unapproved | Rewrite structure without adding or removing facts |
| Naturally detailed field | Content is longer but separated into clear information groups and remains easy to scan | May pass UX review | Do not fail it solely because of length |
| Translation request before approval | `content-en.json` exists but English approval is not recorded | Translation must stop | Direct the user back to English review |
| Legacy all-in-one command | User invokes `HRD BUILDING WORKFLOW ALL` | Explain that production now uses the staged workflow | Do not generate translations |

</frozen-after-approval>

## Code Map

- `buldings/WORKFLOW.md` -- canonical production order and per-building checklist.
- `buldings/README.md` -- concise folder convention and workflow summary.
- `buldings/PROMPT-2-EN.md` -- English drafting plus fact-safety and renter-UX self-review instructions.
- `buldings/PROMPT-3-TRANSLATE.md` -- translation gate requiring recorded English approval.
- `buldings/PROMPT-2-3-ALL.md` -- legacy combined entry point that must redirect to the staged workflow.
- `buldings/review.template.md` -- reusable approval record and visible UX checklist.

## Tasks & Acceptance

**Execution:**
- [x] `buldings/WORKFLOW.md`, `buldings/README.md` -- replace the all-in-one path with one compact staged workflow and make both approval gates explicit.
- [x] `buldings/PROMPT-2-EN.md` -- require renter-first information grouping, short separated paragraphs, field purpose, renter relevance and removal of internal/dossier wording without numerical length limits.
- [x] `buldings/PROMPT-3-TRANSLATE.md` -- require an approved English source and preservation of its information grouping in every locale.
- [x] `buldings/PROMPT-2-3-ALL.md` -- convert the legacy prompt into a short deprecation notice that routes users to English-first production.
- [x] `buldings/review.template.md` -- add separate fact-safety, renter-UX, English approval, translation and render-QA checkpoints.

**Acceptance Criteria:**
- Given approved research, when the English workflow runs, then it produces English only and requires both fact-safety and renter-UX review.
- Given a long but well-separated field, when UX review is performed, then it is judged by scanability and renter relevance rather than a hard word limit.
- Given a dense dossier-style paragraph, when UX review is performed, then English approval remains blocked until the same facts are reorganized into clear information groups.
- Given English approval has not been recorded, when translation is requested, then the workflow stops before creating locale files.
- Given English approval is recorded, when translation runs, then all locales preserve facts, uncertainty and visible information grouping.
- Given the legacy all-in-one command is used, when its instructions are read, then the user is directed to the staged workflow instead of receiving unreviewed translations.

## Spec Change Log

## Verification

**Commands:**
- `rg -n "English approved|fact-safety|renter-UX|scan|paragraph|HRD BUILDING WORKFLOW ALL" buldings/WORKFLOW.md buldings/README.md buldings/PROMPT-2-EN.md buldings/PROMPT-3-TRANSLATE.md buldings/PROMPT-2-3-ALL.md buldings/review.template.md` -- expected: approval order and renter-first gate are explicit in all relevant workflow surfaces.

**Manual checks:**
- Confirm no instruction permits locale generation before English approval.
- Confirm no hard word-count threshold appears.
- Confirm long content may pass when information is clearly grouped and easy to scan.

## Suggested Review Order

**Canonical workflow**

- Starts with the single staged production path and its two English gates.
  [`WORKFLOW.md:5`](../buldings/WORKFLOW.md#L5)

- Separates reviewer gate results from final owner approval.
  [`WORKFLOW.md:25`](../buldings/WORKFLOW.md#L25)

**English quality**

- Defines scanability through information grouping without hard length limits.
  [`PROMPT-2-EN.md:22`](../buldings/PROMPT-2-EN.md#L22)

- Gives every shared field a concrete renter-facing purpose.
  [`PROMPT-2-EN.md:33`](../buldings/PROMPT-2-EN.md#L33)

- Separates editorial review notes from the saveable JSON package.
  [`PROMPT-2-EN.md:47`](../buldings/PROMPT-2-EN.md#L47)

**Translation boundary**

- Makes approved English the sole source and review.md status-only input.
  [`PROMPT-3-TRANSLATE.md:14`](../buldings/PROMPT-3-TRANSLATE.md#L14)

- Blocks missing, duplicate or non-canonical approval states.
  [`PROMPT-3-TRANSLATE.md:16`](../buldings/PROMPT-3-TRANSLATE.md#L16)

- Requires each extracted locale file to pass schema validation.
  [`PROMPT-3-TRANSLATE.md:46`](../buldings/PROMPT-3-TRANSLATE.md#L46)

**Reusable review record**

- Captures renter-first checks independently from factual checks.
  [`review.template.md:29`](../buldings/review.template.md#L29)

- Prevents translation until reviewer and owner approvals are distinct and complete.
  [`review.template.md:44`](../buldings/review.template.md#L44)

**Legacy and orientation**

- Retires the all-in-one path without leaving an alternate production route.
  [`PROMPT-2-3-ALL.md:3`](../buldings/PROMPT-2-3-ALL.md#L3)

- Keeps the folder-level entry point short and consistent with the canonical workflow.
  [`README.md:5`](../buldings/README.md#L5)
