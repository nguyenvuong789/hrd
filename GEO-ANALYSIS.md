# GEO Analysis – House Rental Danang

**GEO Readiness Score: 82/100**
**Audited:** 2026-08-30, live production HTML

## Platform Breakdown

| Platform | Score | Rationale |
|---|---:|---|
| Google AI Overviews | 84 | Strong SSR, indexability, local topic coverage and structured headings; entity schema and source citations are incomplete. |
| ChatGPT Search | 80 | `/llms.txt`, Bing-crawler access and useful rental answers are present; no verified external entity/brand mention audit. |
| Perplexity | 78 | Crawlable, readable pages with local detail; community/third-party citation footprint was not independently verified. |

## Technical Access

- **SSR:** Pass. Key HTML content, headings, forms and listing copy are present in initial responses; no client-only body detected.
- **Robots:** GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot and PerplexityBot explicitly allowed; `/wp-admin/` blocked; sitemap declared. No blanket AI block found.
- **llms.txt:** Present at `https://houserentaldanang.com/llms.txt` with Home, Houses, Apartments, Villas and Da Nang guide links. Add Contact, Why Us, district hubs and a short verified-facts section.
- **RSL:** No RSL 1.0 policy detected. Add licensing only after deciding what content may be reused.
- **Hreflang:** Six locale alternates (`en`, `vi`, `ko`, `ja`, `ru`, `zh`) observed on sampled pages; validate reciprocal links and translated canonicals across every important URL.

## Citability

The homepage and category hubs contain direct, self-contained renter guidance and question-led FAQ blocks. Best citation candidates are: rent ranges and factors on `/apartments/`, lease/deposit guidance on `/faqs/`, district comparisons on Son Tra/Ngu Hanh Son pages, and the “what to confirm before viewing” checklists. Rewrite key blocks to 134–167 words, lead with the answer in 40–60 words, cite official sources for legal/immigration claims, and include a visible review date.

## Authority / Brand Signals

The site exposes Facebook, Instagram and YouTube links in HTML. Wikipedia, Wikidata, Reddit, LinkedIn, Yelp, BBB and Bing Places presence was not independently verified in this run; do not add unverified profiles to `sameAs`. Build third-party “best of” and local-community mentions for AI citation diversity.

## Highest-Impact GEO Changes

1. Publish verified Organization/RealEstateAgent entity data with consistent NAP and genuine `sameAs` links.
2. Expand `/llms.txt` with important district/building/contact URLs and verified business facts.
3. Add source-backed 134–167-word answer blocks to category, district, FAQ and guide pages.
4. Add author/editor credentials, publication and last-reviewed dates to guides and legal/rental explainers.
5. Build citation diversity through Bing Places, Apple Business Connect, reputable local directories, YouTube and community/press mentions.

## Schema Recommendations

Use `Organization` + `RealEstateAgent` on the brand layer; `WebPage`/`CollectionPage` for hubs; `ApartmentComplex`/`Place` for building pages where factual; and `RealEstateListing`/`Offer` only for current listing facts. Include `PostalAddress`, `telephone`, `geo`, `openingHoursSpecification`, `image` and verified `sameAs`. Do not add deprecated `HowTo` or commercial-page FAQ rich-result markup.

## Limitations

No paid AI mention tracker, live ChatGPT/Perplexity query test, Google Search Console, backlink index or independent third-party profile verification was available.
