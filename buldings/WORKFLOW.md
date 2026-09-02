# Building Production Workflow

Use this staged workflow for every building. Do not generate English and translations in the same production run.

## Required order

```text
approved research.md
-> English draft
-> fact-safety review
-> renter-UX review
-> owner approves English
-> locale translations
-> locale review
-> WordPress
-> desktop and mobile render QA
```

## 1. English

Run `PROMPT-2-EN.md` in a fresh chat with the building name and absolute path to the approved `research.md`.

Save the JSON object as `content-en.json`. Record omitted facts and claims requiring confirmation in `review.md`.

## 2. English quality gate

Review English against `review.template.md`.

English must pass both gates:

- **Fact safety:** every claim comes from approved research; uncertainty, price, availability, fees and policies remain qualified.
- **Renter UX:** each field has a clear renter-facing purpose; related information is grouped; paragraphs are short and visibly separated; decision-relevant information comes before project background; no internal or dossier-style wording remains.

There is no hard word limit. Detailed content may pass when it remains clearly grouped and easy to scan. A short wall of text may still fail.

Set `Fact-safety approved` and `Renter-UX approved` to `yes` after their respective gates pass. Set `English approved` to `yes` only after the owner accepts the reviewed English package. Use the exact lowercase values `yes` or `no`, with each status appearing once.

## 3. Translation

Run `PROMPT-3-TRANSLATE.md` only after all three English approval fields are `yes` in `review.md`.

Translations must preserve facts, uncertainty, paragraph separation, information groups and link structure. Save them as `content-vi.json`, `content-ko.json`, `content-ja.json`, `content-ru.json` and `content-zh.json`.

For an older building whose `review.md` lacks the new approval fields, copy the current `review.template.md` structure and review English again. Do not infer or backfill approval.

## 4. Publish and render QA

- Paste each approved locale JSON into the matching WordPress building page meta field.
- Render `en`, `vi`, `ko`, `ja`, `ru` and `zh` on desktop and mobile.
- Check source-language leaks, broken links, empty states, field fallback and unsafe availability wording.
- Mark translations and local rendering approved in `review.md`.
- Do not publish locale content until `Translations approved: yes` is recorded.

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
