# BUILDING-001: Building detail page MVP

## User story

As a renter, I want to understand an apartment building and see matching rentals in one place so I can decide whether to contact HouseRentalDanang.

## Implementation

- Refine the existing `page-apartment-building.php` template in the Local WP child theme.
- Replace pilot/placeholder language with concise production-safe copy.
- Add basic `ApartmentComplex` JSON-LD from known registry facts.
- Preserve the existing property query, card partial, 404 behavior, and responsive CSS.

## Acceptance criteria

- Building title and facts are visible.
- Related listings are queried by `hrd_building_key`.
- Empty and non-empty listing states both work.
- Contact CTA remains visible.
- No unsupported rent, facility, or availability claims are introduced.
