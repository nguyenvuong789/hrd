# Legacy Combined Workflow

`HRD BUILDING WORKFLOW ALL` is no longer used for production content.

Generating English and all translations in one run can propagate factual or renter-UX problems into every locale before the owner reviews the source package.

Use the staged workflow instead:

1. Run `PROMPT-2-EN.md` from approved `research.md`.
2. Complete fact-safety and renter-UX review in `review.md`.
3. Obtain owner approval for English.
4. Run `PROMPT-3-TRANSLATE.md` from the approved `content-en.json`.

Do not generate locale JSON files from this legacy entry point.
