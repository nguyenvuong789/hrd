# HRD Contact Pass MVP

Updated: 2026-08-30

## Decision

HouseRentalDanang should sell access to recently checked rental contacts for foreign renters. The site is not trying to out-inventory FazWaz or Dot Property. Its advantage is English-first presentation, local-area context, and a clear freshness status before a renter contacts an owner.

Primary customer: foreign renter in Da Nang.

Initial product:

- Free listing preview.
- Paid contact unlock: exact address + owner contact when available.
- Optional paid re-check for older listings.
- No owner subscription in phase 1.
- No full viewing/freelancer service in phase 1.

## Global model landscape

This is a public-web model scan, not a complete traffic ranking. Pages were checked on 2026-08-30. The relevant comparison is business model and freshness mechanism, not just whether a site lists Da Nang properties.

| Site | Market/model | How access works | Freshness/trust mechanism | HRD lesson |
|---|---|---|---|---|
| [Spotahome](https://www.spotahome.com/how-it-works) | International mid/long-term rentals | Renter reserves online; landlord contact is established after landlord accepts | Professionally verified homes, 24-hour response language, 24-hour move-in issue window | Sell confidence around verification and a clear remedy, not raw contact data |
| [HousingAnywhere](https://housinganywhere.com/) | International student/expat housing marketplace | Booking/reservation flow; platform mediates the transaction | Platform protection and verified accommodation positioning | A paid access model works best when paired with protection or a concrete service promise |
| [Rentola](https://rentola.com/) | Global rental search/lead marketplace | Login unlocks “unique features”; AI notifications and saved-search style retention | Large aggregated database; freshness is less explicit on the public homepage | Subscription/alert retention is a proven pattern, but HRD needs a smaller, more trustworthy inventory |
| [Nestpick](https://www.nestpick.com/) | Meta-search for furnished mid/long-term rentals | Free search across 200+ partners; click-through to providers | “Vetted providers”, updated listings and comparison positioning | Aggregate/compare first, then monetize qualified traffic or partner referrals |
| [SpareRoom](https://www.spareroom.com/) | Room/roommate classifieds | Free browsing and inquiries; strong account/community loop | Large user base, moderation/trust signals and alerts | Repeat visits can come from alerts and saved searches without hiding every contact |
| [Roomgo](https://www.roomgo.net/) | Roommate marketplace | Search, signup and alerts; post a room | Profile/signup and messaging layer | Build a lightweight saved-search loop before complex credits |
| [Da Nang Villa Realty](https://www.danangvillarealty.com/) | Local agency, sale/rent, premium inventory | Direct phone/contact, human agency | Curated inventory and local service | HRD can differentiate with English-first access and transparent freshness |
| [FazWaz](https://www.fazwaz.vn/) | Large Southeast Asia marketplace | Free property submission, broad search and account features | Scale, newest listings, taxonomy and market pages | Learn architecture and SEO; do not compete on volume |
| [Dot Property](https://www.dotproperty.com.vn/) | Vietnam/Asia marketplace | Broad rent/sale categories, projects and broker directories | Category/project breadth | Learn project and building hubs; add HRD-specific checked dates |

### Market read

- The strongest global pattern is not “pay to reveal a phone number”. It is **pay for reduced uncertainty**: verified homes, secure booking, response expectations, protection or a curated shortlist.
- Large meta-search sites monetize breadth and partner traffic. They generally do not prove that every listing is available today.
- Direct-contact classifieds maximize volume and freshness through users, alerts and messaging, but push verification work onto the renter.
- HRD's winnable position is a hybrid: free preview + paid access to a **recently checked contact**, with a replacement/refund if the contact is stale or invalid.
- The biggest barrier is operational freshness, not WordPress or payment technology. A one-person operation must cap active inventory.

| Site | Observed model | Learn | Do not copy |
|---|---|---|---|
| FazWaz | Large marketplace, many property types, free property submission, account/search inventory | Search taxonomy, location and property-type landing pages, newest listings | Competing on listing count; HRD cannot maintain that inventory alone |
| Dot Property | Vietnamese/international marketplace with rent/sale, project and broker directories | Localized categories, project/building discovery, broad query coverage | Generic database positioning without a clear freshness promise |
| Da Nang Villa Realty | Local agency, direct phone number, curated/luxury positioning, rent and buy | Trust, local service, direct human contact, quality over volume | Copying luxury-only positioning; HRD needs an accessible expat rental wedge |
| Asia Villas | Curated luxury vacation-rental inventory, strict quality language, concierge and rate guarantee | Curated inventory, explicit service promise, premium add-ons | Claiming strict verification or guarantees that HRD cannot operationally support |

Existing HRD assets that support the model:

- English-first homepage and six language roots.
- `/apartments/`, `/houses/`, `/villas/` archives.
- `/location/son-tra/`, `/location/ngu-hanh-son/`, `/location/hai-chau/`.
- `/properties-search/`.
- `/renting-requests-form/`.
- Existing local-help positioning and GA4.

## Product offer

### Contact Pass

Test prices, shown in USD:

| Offer | Price | Purpose |
|---|---:|---|
| 1 contact | $2.99 | Low-friction trial |
| 5 contacts | $9.99 | Main offer |
| 15 contacts | $19.99 | Serious search |
| 30-day access | $29-$39 | Test only after enough fresh inventory exists |

An unlock contains:

- Exact address when safe and available.
- Phone number or messaging contact provided for rental contact.
- Building/street name where relevant.
- Last checked date.
- Short English message template for contacting the owner.

The product copy should say `Unlock contact` or `Contact owner directly`, not `Buy a phone number`.

### Re-check add-on

For listings checked 8-14 days ago, offer `Request a fresh check` for $5-$10. This is a small manual add-on, not a promise of a full relocation service.

### Refund rule

Refund or replace one credit when the number is invalid, the listing is confirmed rented, or the owner says the quoted price is materially wrong. Do not promise that every contact will answer.

## Freshness system

Every listing needs an internal status and a public label.

| Internal status | Rule | Public label | Unlock? |
|---|---|---|---|
| owner_confirmed | Owner confirmed within 72 hours | Owner confirmed | Yes |
| recently_checked | HRD checked within 7 days | Checked recently | Yes |
| aging | Last check 8-14 days | May have changed | Yes, with warning |
| unverified | No successful check or >14 days | Not recently checked | No |
| expired | >21-30 days without confirmation | Check requested | No |

Public listing copy:

> Last checked: 28 Aug 2026. Price and availability may have changed. Please confirm with the owner before visiting.

Do not use `available now` unless the owner has confirmed it within the chosen window.

## Inventory rules for one operator

Start with 20-30 listings, not all 140 Nhà Tốt records.

Prioritize:

1. Son Tra: My Khe, An Hai, Phuoc My and nearby beach areas.
2. Ngu Hanh Son: My An, An Thuong and FPT Plaza.
3. Hai Chau: central apartments near offices, universities and services.
4. Furnished studios and 1-2 bedroom apartments.
5. Roughly $250-$1,000/month, plus a small premium selection.

Use Nhà Tốt as a discovery/radar source. Publish or sell access only after the contact has been checked and the listing can be attributed to a legitimate rental source.

Weekly operating rhythm:

- Select 10-15 candidate records.
- Contact owners and confirm price, availability, exact address and foreign-renter contact method.
- Publish 5-8 strongest listings.
- Archive listings older than 21-30 days without a successful re-check.
- Review invalid-contact and already-rented feedback once per week.

## Funnel

Google, expat communities or social posts
-> English area/budget landing page
-> Free listing preview
-> Unlock contact
-> Payment
-> Exact address + contact + message template
-> Feedback: number works / no answer / rented / price changed

Secondary CTA:

> Need help writing to the owner in Vietnamese? Get a message template.

## First pages

Use existing pages where possible; avoid creating a separate custom app.

1. `/apartments/son-tra/` - add the contact-unlock value proposition.
2. `/apartments/ngu-hanh-son/` - focus on My An, An Thuong and FPT Plaza.
3. `/rentals-under-1000-usd-da-nang/` - new English commercial landing page.
4. `/contact-pass/` - pricing, what unlock includes, freshness and refund policy.

Each page should show preview fields and a consistent CTA:

> See the exact address and contact details for recently checked rentals.

## MVP implementation sequence

### Days 1-3: offer and data

- Add listing metadata: `last_checked`, `freshness_status`, `contact_unlock_enabled`, `contact_source`, `expiry_date`.
- Select the first 20 candidate listings.
- Create the refund and freshness copy.
- Decide payment method available to the business.

### Days 4-7: manual checkout

- Create `/contact-pass/` with the three offers.
- Use a payment link or simple WooCommerce product.
- Deliver the unlock manually by email during the test.
- Track each purchase and credit in a spreadsheet.

### Days 8-14: test demand

- Publish 10-15 checked listings.
- Add unlock CTA to the three highest-intent pages.
- Post one weekly `Newly checked rentals in Da Nang` update.
- Ask every buyer for a one-click contact-quality result.

Automate account balance and unlock delivery only after there are repeat purchases and fewer than 10% invalid/rented complaints.

## Metrics and kill criteria

Track:

- Preview views.
- Unlock CTA clicks.
- Checkout starts and completed payments.
- Paid unlock rate per listing view.
- Contact success rate.
- Invalid/rented refund rate.
- Repeat purchase rate within 30 days.
- Hours per week spent checking inventory.

Continue if, after the first 20-30 paid orders:

- At least 5% of qualified listing viewers click unlock.
- At least 2% of qualified viewers purchase a pass.
- Invalid/rented refund rate stays below 15%.
- Manual operation stays below 5 hours/week for the initial inventory.

Pause or change the offer if buyers mainly want a human search service, or if freshness cannot be maintained with the available time.

## Main risk controls

- Do not expose a contact unless it is intended for rental enquiries and has been checked.
- Do not present old photos as proof of current condition.
- Keep a source and check date for every contact.
- Never guarantee that a property remains available after payment.
- Offer credit replacement/refund for stale or invalid information.
- Keep the public preview useful enough that the paid unlock feels like convenience, not an information trap.

## Recommended first experiment

Offer `5 recently checked contacts for $9.99` to English-speaking visitors on Son Tra and Ngu Hanh Son pages. Start with 10-15 listings and manual fulfillment for two weeks. This tests willingness to pay, data freshness and operational load before building a full credit system.
