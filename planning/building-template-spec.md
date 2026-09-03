# HRD Building Detail Template - Content Spec

## 1. Page goal

Help a renter decide whether a building fits their needs, then move them to current rental options or a contact request.

## 2. Canonical page order

1. Breadcrumbs
2. Hero: building name, district/area, one-line positioning, primary CTA
3. Quick facts
4. Building overview
5. Amenities and practical details
6. Location and map
7. Available rentals in this building
8. Renting notes / fees / eligibility
9. FAQ
10. Contact CTA
11. Related buildings or area links

## 3. Field dictionary

### Required for publish

| Field | Purpose | Example |
| --- | --- | --- |
| `building_name` | Page title and identity | The Monarchy |
| `slug` | Stable URL | the-monarchy |
| `area` | District/neighbourhood context | Son Tra |
| `hero_summary` | One sentence explaining who the building suits | Riverside apartments near central Da Nang |
| `building_overview` | 2-4 short paragraphs of useful context | Building type, atmosphere, renter fit |
| `typical_layouts` | Fast scan of unit types | Studio, 1-3 bedrooms |
| `listing_relation_key` | Match current property inventory | monarchy |
| `primary_cta_label` | Main conversion action | Check available apartments |
| `last_reviewed` | Editorial freshness | 2026-09-01 |

### Strongly recommended

| Field | Purpose |
| --- | --- |
| `cover_image` | Establish visual identity |
| `gallery` | Show building/common areas when rights and source are clear |
| `developer_or_operator` | Useful factual context |
| `year_completed` | Building comparison |
| `total_floors` / `total_units` | Scale and expectation setting |
| `amenities` | Pool, gym, parking, security, reception, etc. |
| `parking_notes` | Practical renter decision |
| `pet_policy` | High-value filter for renters |
| `utility_notes` | What commonly is/is not included |
| `map_url` / `latitude` / `longitude` | Location confidence and directions |
| `nearby_places` | Beach, river, schools, supermarkets, work hubs |
| `renting_notes` | Deposit, lease term, move-in constraints |
| `faq_items` | Resolve recurring questions |

### Optional

| Field | Purpose |
| --- | --- |
| `pros` / `tradeoffs` | Honest comparison |
| `video_url` | Richer proof when available |
| `source_links` | Attribution for building facts |
| `related_building_keys` | Cross-navigation |
| `translations` | Localized editorial fields |

## 4. Section rules

- Every canonical section is always present in the MVP so all building pages share one predictable shape.
- A section without content uses the same short neutral empty state: `More details about this building will be added soon.`
- Never invent a facility, fee, rent, policy, location fact, or availability status to fill an empty section.
- Never claim a facility, fee, rent, or availability status without a verified value.
- Listing section must show a `last checked` or confirmation note and an honest empty state.

## 5. Quick facts shown above the fold

Show no more than six:

- Area
- Typical layouts
- Building type
- Nearby landmark
- Key amenity (only if verified)
- Last reviewed / availability check

## 6. CTA model

- Primary: `Check available apartments`
- Secondary: `Ask about this building`
- Empty inventory: `Ask for the latest availability`

CTA destination may be the filtered listing section or contact form, but label must describe the actual result.

## 7. Minimum publish checklist

- Required fields complete.
- At least one useful image or deliberate no-image treatment.
- Building facts sourced or marked as editorial guidance.
- Listing query key tested.
- Empty state reviewed.
- Mobile order remains Hero -> facts -> overview -> listings -> CTA.
- English copy approved before translating other locales.
