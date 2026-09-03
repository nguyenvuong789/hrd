# Building Production Workflow

Use this staged workflow for every building. Do not generate English and translations in the same production run.

## Required order

```text
PROMPT-1-RESEARCH.md
-> research.md
-> research approval gate
-> owner approves research
-> English draft
-> load English into Local building page
-> fact-safety review
-> renter-UX review
-> desktop and mobile English render review
-> owner approves rendered English
-> locale translations
-> locale review
-> load all locales into Local WordPress
-> locale desktop and mobile render QA
-> production publish
```

## 1. Research

Run `PROMPT-1-RESEARCH.md` with the building identity and destination path.

Save the dossier as `research.md`. Resolve any failed approval checks, then have the owner approve the dossier. Only the approved dossier becomes the factual source for English.

## 2. English

Run `PROMPT-2-EN.md` in a fresh chat with the building name and absolute path to the approved `research.md`.

Save the JSON object as `content-en.json`. Record omitted facts and claims requiring confirmation in `review.md`.

## 3. Load and review English on Local

Follow `LOCAL-EN-REVIEW.md` to validate the JSON, load it into `hrd_building_content_en` on the exact Local building page, and open the rendered URL.

Do not approve English by reading JSON alone. Review the shared building template on desktop and mobile. If the page fails, revise `content-en.json`, reload it and repeat the rendered review.

This step changes Local only. It does not publish or synchronize production.

## 4. English quality gate

Review English against `review.template.md`.

English must pass both gates:

- **Fact safety:** every claim comes from approved research; uncertainty, price, availability, fees and policies remain qualified.
- **Renter UX:** each field has a clear renter-facing purpose; related information is grouped; paragraphs are short and visibly separated; decision-relevant information comes before project background; no internal or dossier-style wording remains.

There is no hard word limit. Detailed content may pass when it remains clearly grouped and easy to scan. A short wall of text may still fail.

Set `Local English loaded`, `Local English desktop reviewed`, `Local English mobile reviewed`, `Fact-safety approved` and `Renter-UX approved` to `yes` after their respective gates pass. Set `English approved` to `yes` only after the owner accepts the rendered Local English page. Use the exact lowercase values `yes` or `no`, with each status appearing once.

## 5. Translation

Run `PROMPT-3-TRANSLATE.md` only after all six Local English and approval fields are `yes` in `review.md`.

Translations must preserve facts, uncertainty, paragraph separation, information groups and link structure. Save them as `content-vi.json`, `content-ko.json`, `content-ja.json`, `content-ru.json` and `content-zh.json`.

For an older building whose `review.md` lacks the new approval fields, copy the current `review.template.md` structure and review English again. Do not infer or backfill approval.

## 6. Locale load, render QA and production publish

- Load each approved locale JSON into the matching Local WordPress building page meta field.
- Render `en`, `vi`, `ko`, `ja`, `ru` and `zh` on desktop and mobile.
- Check source-language leaks, broken links, empty states, field fallback and unsafe availability wording.
- Mark translations and local rendering approved in `review.md`.
- Do not synchronize or publish production content until `Translations approved: yes` and `Page rendered locally: yes` are recorded.

## Per-building files

```text
buldings/<building-slug>/
  research.md
  content-en.json
  content-vi.json
  content-ko.json
  content-ja.json
  content-ru.json
  content-zh.json
  review.md
```
