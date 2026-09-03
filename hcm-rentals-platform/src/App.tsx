import { useEffect, useMemo, useState } from "react";
import { loadChanges, loadDemo, loadListings, loadRuns, loadStats } from "./api";
import type { ChangeEvent, Listing, PriceChange, Run, Stats } from "./types";

type View = "active" | "new" | "disappeared" | "prices";

const money = (value: number) => new Intl.NumberFormat("vi-VN", { maximumFractionDigits: 0 }).format(value) + " ₫";
const shortMoney = (value: number) => value >= 1_000_000 ? `${(value / 1_000_000).toLocaleString("en", { maximumFractionDigits: 1 })}m` : money(value);
const date = (value?: string) => value ? new Intl.DateTimeFormat("en-GB", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value)) : "—";

export function App() {
  const [view, setView] = useState<View>("active");
  const [category, setCategory] = useState("all");
  const [district, setDistrict] = useState("");
  const [minPrice, setMinPrice] = useState(8);
  const [maxPrice, setMaxPrice] = useState(80);
  const [minSize, setMinSize] = useState(0);
  const [listings, setListings] = useState<Listing[]>([]);
  const [stats, setStats] = useState<Stats | null>(null);
  const [events, setEvents] = useState<ChangeEvent[]>([]);
  const [priceChanges, setPriceChanges] = useState<PriceChange[]>([]);
  const [runs, setRuns] = useState<Run[]>([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [demo, setDemo] = useState(false);
  const [email, setEmail] = useState("");
  const [subscribeMessage, setSubscribeMessage] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    const confirmation = new URLSearchParams(window.location.search).get("contact_token");
    if (confirmation) { localStorage.setItem("sgbyowner_contact_token", confirmation); window.history.replaceState({}, "", window.location.pathname); }
    Promise.all([loadStats(), loadChanges(), loadRuns()]).then(([nextStats, nextChanges, nextRuns]) => {
      setStats(nextStats); setEvents(nextChanges.events); setPriceChanges(nextChanges.price_changes); setRuns(nextRuns.items);
    }).catch(async () => {
      const fallback = await loadDemo();
      setStats(fallback.stats); setListings(fallback.items); setTotal(fallback.total); setDemo(true); setLoading(false);
    });
  }, []);

  useEffect(() => {
    if (demo) return;
    const params = new URLSearchParams({ city: "hcm", category, limit: "48", status: view === "disappeared" ? "disappeared" : "active" });
    if (district) params.set("district", district);
    params.set("min_price", String(minPrice * 1_000_000));
    params.set("max_price", String(maxPrice * 1_000_000));
    params.set("min_size", String(minSize));
    setLoading(true);
    loadListings(params).then((data) => { setListings(data.items); setTotal(data.total); }).finally(() => setLoading(false));
  }, [category, district, minPrice, maxPrice, minSize, view, demo]);

  const visibleListings = useMemo(() => {
    let result = listings;
    if (demo) result = result.filter((item) => (category === "all" || item.category === category) && (!district || item.district === district) && item.price >= minPrice * 1_000_000 && item.price <= maxPrice * 1_000_000 && Number(item.size || 0) >= minSize);
    if (view === "new" && events.length) {
      const ids = new Set(events.filter((item) => item.event_type === "new").map((item) => item.list_id));
      result = result.filter((item) => ids.has(item.list_id));
    }
    return result;
  }, [listings, demo, category, district, minPrice, maxPrice, minSize, view, events]);

  const districts = stats?.districts || [...new Set(listings.map((item) => item.district))].filter(Boolean).map((name) => ({ district: name, count: 0 }));
  const latest = stats?.latest_run;
  const tabs: Array<[View, string, number]> = [
    ["active", "Available", Number(stats?.counts.active || total)],
    ["new", "New today", Number(latest?.new_count || 0)],
    ["disappeared", "Gone", Number(latest?.disappeared_count || 0)],
    ["prices", "Price moves", Number(latest?.price_change_count || 0)],
  ];

  return <div className="site-shell">
    <header className="masthead">
      <a className="brand" href="#top" aria-label="Saigon Homes home"><span>SG</span> Saigon Homes</a>
      <nav><a href="#rentals">Rentals</a><a href="#daily-report">Daily report</a><a href="#method">Method</a></nav>
      <span className="owner-note">Owner-listed. No agents.</span>
    </header>

    <main id="top">
      <section className="hero">
        <div className="hero-copy">
          <h1>Rent directly.<br />Live more <em>Saigon.</em></h1>
          <p>Fresh houses and apartments across Ho Chi Minh City, selected from individual owners and checked every day.</p>
          <a className="primary-action" href="#rentals">Browse {Number(stats?.counts.active || total).toLocaleString("en")} homes <Arrow /></a>
        </div>
        <div className="hero-visual" aria-hidden="true">
          {visibleListings[0]?.images?.[0] ? <img src={visibleListings[0].images[0]} alt="" /> : <div className="city-lines" />}
          <div className="hero-stamp"><strong>Daily</strong><span>90-day window</span></div>
        </div>
      </section>

      <section className="pulse" id="daily-report">
        <div><span>Live inventory</span><strong>{Number(stats?.counts.active || total).toLocaleString("en")}</strong></div>
        <div><span>New today</span><strong>+{latest?.new_count || 0}</strong><small>{latest?.new_houses_count || 0} houses · {latest?.new_apartments_count || 0} apartments</small></div>
        <div><span>Price changes</span><strong>{latest?.price_change_count || 0}</strong></div>
        <div className={`run-state ${latest?.status || "success"}`}><span>Last run</span><strong>{latest?.status || "Historical"}</strong><small>{date(latest?.completed_at)}</small></div>
      </section>

      <section className="alert-signup" id="alerts">
        <div><span className="eyebrow">Your daily shortlist</span><h2>Wake up to<br /><em>better options.</em></h2></div>
        <div className="alert-form-wrap"><p>Choose your filters below, confirm your email, and we’ll send newly matched owner listings every day. Direct contact details unlock after confirmation.</p><form onSubmit={async (event) => { event.preventDefault(); setSubmitting(true); setSubscribeMessage(""); try { const response = await fetch("/api/subscribe", { method:"POST", headers:{"content-type":"application/json"}, body:JSON.stringify({ email, filters:{ category, district, min_price:minPrice * 1_000_000, max_price:maxPrice * 1_000_000, min_size:minSize } }) }); const result = await response.json() as { error?: string }; if (!response.ok) throw new Error(result.error || "Unable to subscribe"); setSubscribeMessage("Check your inbox to confirm your daily alert."); setEmail(""); } catch (error) { setSubscribeMessage(error instanceof Error ? error.message : "Unable to subscribe"); } finally { setSubmitting(false); } }}><label><span>Email address</span><input type="email" required value={email} onChange={(event) => setEmail(event.target.value)} placeholder="you@example.com" /></label><button type="submit" disabled={submitting}>{submitting ? "Sending…" : "Set up my alert"}<Arrow /></button></form>{subscribeMessage && <small className="form-message">{subscribeMessage}</small>}<small className="privacy-note">Double opt-in. Unsubscribe anytime. No agents, no spam.</small></div>
      </section>

      <section className="catalog" id="rentals">
        <div className="catalog-heading">
          <div><h2>Find your place</h2><p>Direct links to the original listing. Photos are mirrored for reliable browsing.</p></div>
          {demo && <div className="demo-notice">Previewing legacy HCM data until the Worker API is deployed.</div>}
        </div>

        <div className="tabs" role="tablist">
          {tabs.map(([key, label, count]) => <button key={key} className={view === key ? "active" : ""} onClick={() => setView(key)}><span>{label}</span><b>{count}</b></button>)}
        </div>

        {view !== "prices" && <div className="workspace">
          <aside className="filters">
            <FilterLabel>Property type</FilterLabel>
            <div className="segmented"><button className={category === "all" ? "active" : ""} onClick={() => setCategory("all")}>All</button><button className={category === "houses" ? "active" : ""} onClick={() => setCategory("houses")}>Houses</button><button className={category === "apartments" ? "active" : ""} onClick={() => setCategory("apartments")}>Apartments</button></div>
            <label><FilterLabel>District</FilterLabel><select value={district} onChange={(event) => setDistrict(event.target.value)}><option value="">All districts</option>{districts.map((item) => <option key={item.district}>{item.district}</option>)}</select></label>
            <FilterLabel>Monthly rent</FilterLabel>
            <div className="range-pair"><label>Min<input type="number" min="8" step="1" value={minPrice} onChange={(event) => setMinPrice(Number(event.target.value))} /></label><label>Max<input type="number" min="8" step="5" value={maxPrice} onChange={(event) => setMaxPrice(Number(event.target.value))} /></label></div>
            <label><FilterLabel>Minimum area</FilterLabel><select value={minSize} onChange={(event) => setMinSize(Number(event.target.value))}><option value="0">Any size</option><option value="30">30+ m²</option><option value="50">50+ m²</option><option value="80">80+ m²</option><option value="120">120+ m²</option></select></label>
            <button className="clear" onClick={() => { setCategory("all"); setDistrict(""); setMinPrice(8); setMaxPrice(80); setMinSize(0); }}>Reset filters</button>
          </aside>

          <div className="results">
            <div className="results-meta"><strong>{(demo ? visibleListings.length : total).toLocaleString("en")} matches</strong><span>Newest first</span></div>
            {loading ? <div className="loading">Checking today's homes…</div> : <div className="listing-grid">{visibleListings.map((item, index) => <ListingCard key={item.list_id} listing={item} priority={index < 4} />)}</div>}
            {!loading && !visibleListings.length && <div className="empty"><strong>No homes match these filters.</strong><span>Try another district or a wider price range.</span></div>}
          </div>
        </div>}

        {view === "prices" && <PriceTable items={priceChanges} />}
      </section>

      <section className="method" id="method">
        <h2>Small batch.<br />Clear rules.</h2>
        <div><p>We scan the most recent 90 days of Ho Chi Minh City rentals, keep furnished homes from personal sellers, and exclude commercial property language.</p><p>Accounts or phone numbers appearing on more than five matching listings in one crawl are removed. The result is a cleaner owner-listed feed — never a claim that identity has been legally verified.</p></div>
        <RunRail runs={runs} />
      </section>
    </main>

    <footer><div className="brand"><span>SG</span> Saigon Homes</div><p>Owner-listed. No agents.</p><p>Source listings remain the responsibility of their publishers.</p></footer>
  </div>;
}

function ListingCard({ listing, priority }: { listing: Listing; priority: boolean }) {
  const image = listing.images?.[0];
  const [contact, setContact] = useState<{ phone?: string; account_name?: string } | null>(null);
  const [contactMessage, setContactMessage] = useState("");
  const revealContact = async () => {
    const token = localStorage.getItem("sgbyowner_contact_token");
    if (!token) { document.getElementById("alerts")?.scrollIntoView({ behavior: "smooth" }); setContactMessage("Confirm your email above to unlock direct contact details."); return; }
    const response = await fetch(`/api/listings/${listing.list_id}/contact`, { headers: { authorization: `Bearer ${token}` } });
    if (response.ok) setContact(await response.json()); else setContactMessage("Your verified session expired. Please confirm your alert again.");
  };
  return <article className="listing-card">
    <a className="listing-image" href={listing.source_url} target="_blank" rel="noreferrer">{image ? <img src={image} alt={listing.subject} loading={priority ? "eager" : "lazy"} /> : <div className="no-photo">No photo</div>}<span>Owner-listed</span></a>
    <div className="listing-body"><div className="listing-top"><strong>{listing.price_string || money(listing.price)}</strong><small>{listing.category === "houses" ? "House" : "Apartment"}</small></div><h3>{listing.subject}</h3><p>{listing.district}{listing.address && listing.address !== listing.district ? ` · ${listing.address}` : ""}</p><div className="facts"><span>{listing.rooms || "—"} bed</span><span>{listing.toilets || "—"} bath</span><span>{listing.size ? `${listing.size} m²` : "Area n/a"}</span></div><button className="contact-button" onClick={revealContact}>{contact ? `${contact.account_name || "Owner"} · ${contact.phone || "Contact available"}` : "Show owner contact"}<Arrow /></button>{contactMessage && <small className="contact-message">{contactMessage}</small>}<a className="source-link" href={listing.source_url} target="_blank" rel="noreferrer">View original <Arrow /></a></div>
  </article>;
}

function PriceTable({ items }: { items: PriceChange[] }) {
  return <div className="price-table"><div className="price-row table-head"><span>Listing</span><span>Previous</span><span>Current</span><span>Changed</span></div>{items.length ? items.map((item) => <a className="price-row" key={item.id} href={item.source_url} target="_blank" rel="noreferrer"><span><strong>{item.subject}</strong><small>ID {item.list_id} · {item.district}</small></span><span>{shortMoney(item.old_price)}</span><span className={item.new_price < item.old_price ? "down" : "up"}>{shortMoney(item.new_price)}</span><span>{date(item.changed_at)}</span></a>) : <div className="empty"><strong>No price changes today.</strong><span>The daily crawl will record every movement here.</span></div>}</div>;
}

function RunRail({ runs }: { runs: Run[] }) {
  if (!runs.length) return <div className="run-rail"><span>Daily run history appears after deployment.</span></div>;
  return <div className="run-rail">{runs.slice(0, 7).map((run) => <div key={run.id} title={run.error_message}><i className={run.status} /><span>{new Date(run.started_at).toLocaleDateString("en-GB", { day: "2-digit", month: "short" })}</span><b>{run.filtered_count}</b></div>)}</div>;
}

function FilterLabel({ children }: { children: React.ReactNode }) { return <span className="filter-label">{children}</span>; }
function Arrow() { return <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M4 10h11M11 5l5 5-5 5" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" /></svg>; }
