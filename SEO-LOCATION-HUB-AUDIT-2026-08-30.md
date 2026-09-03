# Location Hub SEO Audit

**Scope:** Son Tra, Ngu Hanh Son and Hai Chau location hubs in `en`, `vi`, `ko`, `ja`, `ru` and `zh`.

## Findings

- Technical metadata, canonical URLs, robots, hreflang, OG/Twitter, NAP and agency schema are present after the previous SEO deployment.
- English hubs already had a strong editorial layer (roughly 1,000+ words, decision-focused headings and viewing checklists).
- Non-English hubs were materially thinner (roughly 270–625 words before this update) and repeated generic category copy, creating doorway-page and weak-citability risk.
- H1s are unique by district and match local rental intent. Listing sections provide useful internal links, but the editorial layer needed stronger district-specific context.

## Implemented

- Added localized editorial sections for all three districts in Vietnamese, Korean, Japanese, Russian and Chinese.
- Added intent-led H2s covering district fit, property-type choice, contract/deposit checks, costs and viewing checklists.
- Added contextual cross-links between the three location hubs.
- Kept availability language factual and avoided unsupported “best”, scarcity or guaranteed-price claims.

## Expected Impact

- More unique, useful content per locale and lower doorway-page risk.
- Better matching for “rentals in [district]” and long-term rental comparison queries.
- More self-contained passages for AI search extraction.
- Clearer progression from discovery to property comparison and viewing request.

## Remaining Recommendations

1. Add one genuinely local image per district with descriptive alt text and dimensions.
2. Add a reviewed-date line when the next market-price refresh is completed.
3. Collect district-specific testimonials only when they are real and attributable.
4. Validate translated pages in Search Console after recrawl and monitor CTR by locale.

## Validation

- `php -l` passed for `inc/location-hub.php`.
- Local render shows 9 H2 headings per localized location page after the content layer is added.
- Existing schema/NAP/robots checks remain intact.
