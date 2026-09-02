# HRD Building Content

Each building folder contains approved research, one English source package, five locale packages and a review record.

Follow `WORKFLOW.md`:

```text
research -> English -> fact-safety + renter-UX review -> owner approval -> translations -> WordPress -> render QA
```

Key rules:

- Never translate raw research or unapproved English.
- Use only approved research facts.
- Keep the existing eight string fields in every locale.
- Judge content by renter relevance and scanability, not a hard word limit.
- Break detailed information into short, clearly separated groups instead of one wall of text.
- Keep source URLs in `research.md`, not publishable copy.

Use `PROMPT-2-EN.md` for English, `PROMPT-3-TRANSLATE.md` after English approval, and `review.template.md` for the quality gate.
