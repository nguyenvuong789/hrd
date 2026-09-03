# Building Detail Pages - MVP PRD

## Goal

Create useful, honest detail pages for apartment buildings on HouseRentalDanang.

## Scope

- Route: `/apartment-buildings/{building}/` via the existing WordPress page template.
- Show building name, area, nearby landmarks, typical layouts, a short guide, and matching rental listings.
- Keep availability language cautious: listings must be confirmed before viewing or payment.
- Include a clear contact CTA and basic building-page structured data.
- Use the existing building registry and `hrd_building_key` property meta; no new plugin or database system.

## Out of scope

- Booking, payments, tenant accounts, or a building CMS.
- Automatic availability verification.
- Full translated editorial content in this MVP.

## Acceptance criteria

1. Unknown building keys return a normal 404.
2. Known building pages render a useful H1, facts, guide, matching property cards, count, empty state, and contact CTA.
3. Copy does not claim unverified availability or building facilities.
4. Basic `ApartmentComplex` JSON-LD is emitted using only registry facts.
5. Layout remains readable on mobile.
6. PHP syntax passes and the active Local WP template is updated.
