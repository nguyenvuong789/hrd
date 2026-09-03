# Keyword–URL Ownership Map

## Homepage role

`/` is the broad brand and local-rental entry page. Its primary topic is **Da Nang rentals** (broad mixed intent), not “houses for rent” or “apartments for rent”. It should introduce the market, explain how the local team helps, and route visitors to the dedicated type and district hubs.

## Ownership rules

| URL | Primary keyword | Search intent | Keep off this page |
|---|---|---|---|
| `/` | Da Nang rentals | Broad commercial/navigation | Exact “houses for rent”, “apartments for rent”, “villas for rent” targeting |
| `/houses/` | houses for rent in Da Nang | Transactional/commercial | Apartment-specific rent guidance |
| `/apartments/` | apartments for rent in Da Nang | Transactional/commercial | Generic homepage language as the main target |
| `/villas/` | villas for rent in Da Nang | Transactional/commercial | House/apartment hub copy |
| `/properties-search/` | Da Nang rental properties search | Utility/transactional | Competing with `/properties/` all-inventory archive |
| `/houses/{district}/` | houses for rent in {district} | Local transactional | Generic city-wide house copy |
| `/apartments/{district}/` | apartments for rent in {district} | Local transactional | Generic city-wide apartment copy |
| `/apartment-buildings/{building}/` | {building} apartments for rent | Building transactional | District-wide apartment keyword |
| Guides | One informational question/topic | Informational | Exact commercial hub keyword as the title/H1 target |

## Cannibalization safeguards

- Homepage links to all three type hubs, but uses “Da Nang rentals” as its own repeated topic.
- Type hubs link to district pages with descriptive anchors and retain unique type-specific copy.
- District pages combine one property type + one district; do not create interchangeable city-name templates.
- Building pages target the building name + apartments and include verified building facts.
- `/properties/` (all inventory) and `/properties-search/` (filter utility) must retain different titles, descriptions and page purposes.

## Current decision

Homepage metadata was changed to:

- **Title:** `Da Nang Rentals | Local Help Finding a Home`
- **Description:** `Find your next home in Da Nang with local help. Compare rental areas, current houses, apartments and villas, then confirm details before viewing.`

This is intentionally broad and should reduce direct title-level competition with the three rental hubs.

## Validation limitation

This map is architecture- and intent-based. A fresh SERP-overlap matrix requires live SERP/API access; the previous cluster cache records that this dependency was unavailable. Validate with Search Console query/page data before creating additional near-duplicate landing pages.
