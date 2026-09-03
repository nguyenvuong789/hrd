# HouseRentalDanang: One-Person Site and Growth Plan

Updated: 2026-08-30

## Live-site alignment audit (30 Aug 2026)

The plan below is now anchored to the live homepage and the existing WordPress structure, not a hypothetical new marketplace. The live homepage currently has:

- H1: `Find your place in Da Nang with local help`.
- Large featured feeds for houses/villas and apartments, including many premium villa/project listings.
- Existing taxonomy routes for houses, apartments, villas and locations such as Son Tra, Ngu Hanh Son, An Thuong and Hai Chau.
- Existing `FAQs`, `Why Us`, testimonials, contact and renting-request pages.
- Existing guides for coworking, contracts and landlords.
- Six language roots: English, Vietnamese, Korean, Japanese, Russian and Chinese.
- Existing search route `/properties-search/`, GA4 and WordPress/RealHomes property templates.

This is a strong foundation, but the live homepage currently communicates **local rental help and premium inventory**, not the sharper product advantage: **current move-in fit for a foreign renter**. The first release should revise the existing pages and templates before adding a new application.

### Keep, change, add, de-emphasize

| Area | Decision | Change |
|---|---|---|
| Homepage | Keep structure, change promise | Add two entry paths: `I live in Da Nang` and `I'm arriving soon`; make move-in date and current availability prominent above the property feeds |
| Houses/apartments/villas archives | Keep URLs and SEO equity | Add freshness/date filters, availability badges, last-checked date and contact-unlock CTA; prioritize apartments before premium villas for MVP |
| Location archives | Keep | Rewrite intros around who the area suits, rent bands, move-in timing and current checked homes; link to relevant search states |
| Property single template | Change | Add `Available now/soon/from date`, `Last checked`, rent/deposit/term, broad-area preview and locked exact contact block |
| `/properties-search/` | Change first | Add `living in Da Nang` vs `arriving`, move-in date/range, lease length, price, bedrooms and furnished/Wi-Fi filters |
| Renting request form | Keep and reposition | Make it the free fallback for users who do not want credits; add resident/arriving segment and urgency field |
| `/why-us/` | Rewrite | Explain freshness, English-first context, local area knowledge and refund/replacement; reduce agency-style generic claims |
| `/testimonials/` | Keep as trust proof | Do not use testimonials as market research; add dates/context and place below product explanation |
| `/faqs/` | Expand | Add freshness definitions, occupied-but-available-soon, credit refund, contact privacy, deposit safety and no-guarantee language |
| Guides | Refresh selectively | Update coworking/digital-nomad content; add current rental, moving-this-week and availability-by-month guides; remove or update stale 2022 contract framing |
| Villas | De-emphasize from MVP | Keep for SEO/lead capture, but do not spend manual checking capacity on premium villas until apartment conversion works |
| Languages | Keep English as conversion source | Translate core labels/checkout carefully; do not attempt equal editorial output in six languages as a one-person operator |

### Live-site contradiction to fix

The homepage currently features many luxury villas and high-end named projects, while the researched first ICP is a foreign renter looking for a practical furnished apartment or house by move-in date. This does not require deleting the villa inventory. It requires changing feed order and calls to action:

1. `Recently checked apartments` first.
2. `Available this month` second.
3. Houses/family homes next.
4. Villas as a separate premium collection.

Use existing archive/property templates and metadata rather than building a separate dashboard-driven front end.

## 1. Strategic decision

**Current phase note:** If active inventory is not yet large or fresh enough, pause Contact Pass and use the existing site for email lead capture first. The interim funnel is documented in [hrd-email-lead-capture-plan.md](hrd-email-lead-capture-plan.md).

HouseRentalDanang should be an English-first **rental matching and contact-access site for foreigners who live in Da Nang or are arriving soon**. It should not try to become a giant property portal, a full relocation agency, or a general digital-nomad brand.

The product promise is:

> Find a Da Nang rental that fits your move-in date, budget and area, then contact the owner or local representative with less wasted time.

The paid value is not an anonymous phone number. It is a **recently checked contact plus move-in fit, rental terms and enough context to act safely**.

## 2. Audience priority

| Priority | Segment | Trigger | Main job | Confidence |
|---|---|---|---|---|
| 1 | Foreign resident already in Da Nang | Lease ending, bad current home, price increase, urgent move | Find a viewable home this week | Medium (important gap to validate) |
| 2 | Foreign renter arriving in 1-90 days | Relocation, new job, extended stay | Shortlist homes that will be available on arrival | Medium-High |
| 3 | New arrival in hotel/Airbnb | Needs a monthly/long-term home after landing | Move from temporary stay to a suitable rental | Medium |
| 4 | Couple, family or retiree relocating | Scouting trip or planned move | Compare neighborhoods, cost and lease risk | Medium |
| 5 | Remote worker/digital nomad | Flexible 1-3 month stay | Find furnished, internet-ready housing | Low-Medium; test only |
| - | Local rental agency | Needs fast availability filtering | Use HRD as data/radar | Competitive risk, not primary payer |

The first segment is currently under-researched, not low priority. Public forums overrepresent people planning a move; residents already in Da Nang are more likely to search silently, use Facebook groups or ask private contacts.

## 3. Product architecture

### Free discovery layer

Show enough value to create SEO, repeat visits and trust:

- Photos, broad area, property type, bedrooms, size, price range and key amenities.
- `Available now`, `Available soon` or `Available from [date/month]`.
- `Last checked` date and a plain warning that availability and price can change.
- Search by area, price, bedrooms and move-in window.
- Save search and email alert when a matching listing is added or updated.

Do not publish exact address, phone number, private owner details or unverified availability in the free layer.

### Paid Contact Pass

Start with manual fulfilment and no subscription requirement:

| Offer | Test price | Use |
|---|---:|---|
| 1 contact | $2.99 | Trial for residents with one urgent need |
| 5 contacts | $9.99 | Default bundle |
| 15 contacts | $19.99 | Active searcher |
| 30-day access | $29-$39 | Only after repeat usage is proven |

Each unlock should contain exact address where appropriate, owner/representative contact, last-check date, rent/deposit/lease notes and an English message template. Use credit replacement/refund when the number is invalid, the home is confirmed rented or the quoted price is materially wrong. Do not promise that an owner will answer or that a home will remain available.

### Optional service layer (later)

- Fresh re-check for an aging listing: $5-$10 manual request.
- English/Vietnamese message help as a small add-on.
- Viewing coordination only after demand and time capacity justify it.
- Do not launch freelancer-led viewing/relocation service in MVP; one operator cannot support it reliably.

## 4. Availability and data operating system

Freshness is the moat and the bottleneck. Every listing needs:

`last_checked`, `freshness_status`, `available_from`, `rent`, `deposit`, `lease_term`, `contact_unlock_enabled`, `contact_source`, `expiry_date`, `owner_or_agency`, `notes`.

| Internal rule | Public label | Paid status |
|---|---|---|
| Owner confirmed <=72h | Owner confirmed | Yes |
| HRD checked <=7d | Checked recently | Yes |
| Checked 8-14d | May have changed | Yes, warning |
| >14d or failed check | Not recently checked | No |
| >21-30d | Archived/check requested | No |

Use `Available from October` when a current tenant's departure date makes the listing relevant to an arriving customer. Do not label an occupied unit `available now`.

For one operator, cap the paid inventory at 20-30 active listings. Start with furnished studios and 1-2 bedroom apartments in Son Tra, Ngu Hanh Son and Hai Chau, roughly $250-$1,000/month. Use discovery sources as a radar, but sell access only after the contact and timing are checked.

Weekly loop: select 10-15 candidates, check the strongest 5-8, publish/update, archive aging records, review invalid-contact feedback. AI can prepare messages, normalize data, draft descriptions and flag stale records; the human must make the final availability/contact decision.

## 5. Site structure

### Conversion pages

1. `/` — promise, move-in search, freshness explanation, latest checked homes.
2. `/properties-search/` — filters for `living in Da Nang` vs `arriving soon`, date, area, budget and term.
3. `/contact-pass/` — pricing, what unlock includes, refund/replacement policy.
4. `/apartments/son-tra/` — My Khe, An Hai, Phuoc My.
5. `/apartments/ngu-hanh-son/` — My An, An Thuong, FPT Plaza.
6. `/apartments/hai-chau/` — central living and work access.
7. `/rentals-under-1000-usd-da-nang/` — commercial SEO page.

### Retention pages/features

- Saved searches and availability alerts.
- `Newly checked rentals this week` archive.
- `Available from [month]` collections.
- Neighborhood comparison pages.
- Rental process, deposit, contract and viewing-safety guides.
- Lightweight email digest; no complex app until repeat demand exists.

## 6. Growth backlog (ranked)

Scores: 1 = low, 5 = high. Risk combines legal, privacy, source-rights and data-freshness exposure; higher means more risk.

| Rank | Idea / backlog item | Impact | Effort | SEO | Monetization | Risk | First implementation |
|---:|---|---:|---:|---:|---:|---:|---|
| 1 | Move-in-date search (`now`, `7 days`, `30 days`, future month) | 5 | 3 | 4 | 5 | 2 | Add date fields and labels to search/cards |
| 2 | Freshness metadata + stale listing archive | 5 | 3 | 3 | 5 | 2 | Add fields, cron reminder and manual checklist |
| 3 | Free preview + 1/5-contact credit checkout | 5 | 3 | 2 | 5 | 3 | Payment link, spreadsheet ledger, manual delivery |
| 4 | Resident-in-Da-Nang landing page | 5 | 2 | 4 | 4 | 2 | Target lease ending, move this week, bad landlord/home |
| 5 | Arriving-in-30/60/90-days landing pages | 4 | 2 | 5 | 4 | 2 | Reuse one template with date-specific copy |
| 6 | Saved search and email alerts | 4 | 3 | 4 | 4 | 2 | Start with simple email capture + weekly digest |
| 7 | Weekly “newly checked rentals” content loop | 4 | 2 | 4 | 3 | 2 | Publish 5-8 verified updates every week |
| 8 | Neighborhood + budget SEO hubs | 4 | 3 | 5 | 3 | 2 | Son Tra, Ngu Hanh Son, Hai Chau; USD/VND pages |
| 9 | Deposit/contract/viewing safety guides | 3 | 2 | 5 | 2 | 1 | English guides with checklist and disclaimers |
| 10 | Contact-quality feedback and automatic credit replacement | 4 | 3 | 1 | 5 | 2 | One-click outcomes after unlock |
| 11 | Remote-worker/flexible-stay test page | 3 | 2 | 4 | 3 | 2 | Test furnished + Wi-Fi + 1-3 month intent |
| 12 | Expat community comment/content distribution | 3 | 2 | 2 | 2 | 3 | Helpful availability posts, no spam or scraping |
| 13 | Referral partnerships with coworking, hotels and relocation helpers | 3 | 3 | 1 | 4 | 2 | One partner at a time, tracked referral code |
| 14 | Owner self-update form / WhatsApp confirmation link | 4 | 4 | 1 | 4 | 3 | Add only after manual process becomes bottleneck |
| 15 | Agent data plan | 3 | 4 | 1 | 4 | 4 | Separate B2B pricing/interface; not MVP |

## 7. 90-day solo-founder roadmap

### Days 1-14: prove the offer

- Select 20-30 active listings only.
- Add freshness and move-in metadata.
- Build `/contact-pass/` and manual credit ledger.
- Add two audiences to search: `already in Da Nang` and `arriving soon`.
- Publish resident and arriving-in-30-days landing pages.
- Ask every unlock buyer whether contact worked and whether the property fit the move-in date.

### Days 15-45: improve repeat visits

- Launch saved-search email alerts.
- Publish weekly checked-inventory digest.
- Add neighborhood/budget landing pages.
- Add one-click stale/invalid feedback and credit replacement.
- Test $2.99 single unlock against $9.99 five-contact bundle.

### Days 46-90: scale only what works

- Automate listing expiry reminders and email delivery.
- Add re-check requests for aging listings.
- Test remote-worker page and one partner channel.
- Add subscription only if users return and consume multiple contacts.
- Consider a separate agent offer only if resident/foreign-renter conversion is not harmed.

## 8. Marketing system for one person + AI

### Weekly cadence (about 5-7 focused hours)

- 2h: check/update priority listings.
- 1h: publish the checked-rentals digest and one area/budget post.
- 1h: reply in relevant expat/community threads with useful guidance.
- 1h: review clicks, unlocks, stale reports and refunds.
- 0.5-2h: improve one page or automate one repetitive task.

AI can draft multilingual copy, turn structured listing data into SEO pages, classify feedback, generate email digests and prepare owner-check messages. AI must not invent availability, quote a contact source it did not receive, or publish private data without a legitimate rental purpose.

### Channel order

1. High-intent Google SEO: area + apartment + price + move-in month.
2. Expat/Facebook/Reddit participation: answer questions and link only when genuinely useful.
3. Email alerts for repeat visits.
4. Coworking/hotel/relocation referrals.
5. Paid search only after contact conversion and freshness economics are proven.

## 9. Metrics and kill criteria

Track weekly:

- Qualified listing views by audience (resident vs arriving).
- Search-to-contact-unlock click rate.
- Checkout completion and revenue per visitor.
- Contact success rate.
- Invalid/rented/price-changed replacement rate.
- Repeat visit and repeat purchase within 30 days.
- Fresh listings checked per operator hour.
- Email alert signup and click rate.

Continue the credit model after the first 20-30 paid orders if: at least 5% of qualified viewers click unlock, 2% purchase, replacement/refund stays below 15%, and manual operations stay below 5h/week. Change the offer if buyers mainly request human search/viewing help or if freshness cannot be maintained.

## 10. Risks and boundaries

- Do not copy or expose owner contact details unless intended for rental enquiries and obtained from a legitimate source.
- Keep source, check date and correction history for every contact.
- Avoid publishing exact addresses publicly; reveal only after a legitimate purchase/use case.
- Give clear price/availability disclaimers and a replacement/refund process.
- Do not claim “verified”, “available now” or “owner confirmed” without an actual rule and timestamp.
- Treat local agencies as a competitive/data-leak risk; do not optimize the public product around them.
- Public groups may prohibit commercial posts or scraping; follow group rules and use manual, helpful participation.

## Final product thesis

`Free discovery -> choose move-in window -> see recently checked matches -> unlock useful contact context -> contact owner -> report outcome -> receive better alerts.`

This loop can be run by one person with AI support because the inventory is intentionally small, the highest-value decisions stay human, and the site earns through reduced search friction rather than pretending to guarantee a volatile rental market.
