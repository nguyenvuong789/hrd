# HRD Building Content

Each building folder contains approved research, one English source package, five locale packages and a review record.

Follow `WORKFLOW.md`:

```text
Prompt 1 -> approved research -> English -> Local rendered review -> owner approval -> translations -> Local locale QA -> production
```

Key rules:

- Never translate raw research or unapproved English.
- Use only approved research facts.
- Keep the existing eight string fields in every locale.
- Judge content by renter relevance and scanability, not a hard word limit.
- Break detailed information into short, clearly separated groups instead of one wall of text.
- Keep source URLs in `research.md`, not publishable copy.
- Approve English from the rendered Local building page, not from JSON alone.

Use `PROMPT-1-RESEARCH.md` for research, `PROMPT-2-EN.md` for English, `LOCAL-EN-REVIEW.md` to load and inspect the shared Local template, `PROMPT-3-TRANSLATE.md` after English approval, and `review.template.md` for the quality gate.
