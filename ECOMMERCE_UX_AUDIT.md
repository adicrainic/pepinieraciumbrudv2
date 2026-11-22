# E-commerce UX / CRO Audit — Pepiniera Ciumbrud (storefront)

**Date:** 22 Nov 2026 · **Scope:** customer experience of `pepinieraciumbrud-storefront` (Nuxt 3) against the local WooCommerce Store API · **Type:** analysis only (no source files were modified)

> **Note on the brief:** the request text ended mid-sentence in section 8 ("Distinguish essential…"). I interpreted it as *essential vs. nice-to-have* and added section 9 (prioritised roadmap).

---

## 0. Method, evidence and limitations

| What | How | Status |
|---|---|---|
| Source review | Read home, catalog, PDP, cart, checkout, confirmation, footer and legal/info pages | Done |
| Catalog data | Queried the Store API: 98 products, 4 root categories, all attributes, images, stock, reviews | Done |
| Live UI | Headless Chrome against a dev instance of the storefront (port 3100). Screenshots at 390 px (mobile) for home, catalog, category, empty search, 3 PDPs, cart drawer, cart page, checkout, about/contact/shipping/terms, 404. 1440 px (desktop) screenshots only for the earlier pass | Mobile: mostly done. Desktop: partial |
| Add to cart | Done on mobile: drawer opens immediately with item, qty controls, subtotal, "Vezi coșul / Finalizează" | Works |
| **Order submission** | **Not performed** (it would create a real order in the local DB; no payment gateway exists anyway) | **Untested** |
| Transactional e-mails, courier/shipping-rate integration | Not testable here | **Untested** |
| Automated measurement (headless Chrome via DevTools protocol, **dev-mode build**, 14 pages × 2 viewports) | **No horizontal scroll** on any page at 390 px or 1440 px (`scrollWidth` = viewport width). **Small touch targets** (<44 px in either dimension) at 390 px: 74 of 105 interactive elements on the catalog, 68/99 on the rose category, 36/54 on home, 33/40 on PDP, 23/26 in the cart (footer links count too). **Smallest text:** 10 px on home/catalog/PDP, 11 px elsewhere. **Console:** repeated Vue *hydration mismatch* errors on `/produse` (server renders "21 afișate din 24"; client briefly expects "0 afișate din 0" and an empty grid). Dev mode exaggerates warnings, so re-check on a production build. The unknown-URL 404 is a plain default page | Measured |
| Competitors | See §5. Several sites return HTTP 403 to automated requests. **I did not attempt to bypass this.** Those entries rely on search-result summaries | **Partly unverified** |

**Environment caveats**
- The catalog is **local/seed data** (descriptions of ~100 characters, one stock photo per product). Some content findings may reflect seed data, not the real catalog. They still show what a customer would see if it were published unchanged.
- The already-running server on `:3000` serves a stale `.output` build: `/produse` returned **HTTP 500 ("Cannot find module …produse-*.mjs")**. This is an environment issue, but verify that production deploys rebuild cleanly. The catalog is the heart of the shop.

---

## 1. Executive summary

Pepiniera Ciumbrud has a **distinctive, credible brand** (real logo, burgundy/cream palette, serif typography, a hero built around the rose village of Ciumbrud) and a **technically clean shopping flow**: instant add-to-cart drawer, in-cart indicators, URL-synced search/filters, sticky mobile add-to-cart bar, guest-only single-page checkout and honest shipping/packaging copy. For a small nursery this is a solid base.

It converts poorly for **living plants** for four reasons:

1. **Product pages don't answer the plant-buyer's questions.** There is no height, size, age, pot or bare-root format, colour, fragrance, bloom period or hardiness for roses. A rose page has two sentences and one photo. Zero reviews anywhere.
2. **Price uncertainty at the moment of commitment.** Shipping is "~25–35 lei, confirmed by phone". The cart/checkout total is labelled "Total produse" and excludes delivery. There is no free-shipping threshold.
3. **Payment is cash-on-delivery only.** No card payment, which is a real barrier for a first-time buyer from an unknown shop and for orders above ~300 lei. The checkout also requires a phone call before dispatch.
4. **Trust and legal gaps.** No company identity (CUI/J-number), no ANPC/SAL-SOL links, no withdrawal-rights text, no reviews, a personal Gmail contact address, and "ACSOFT" in the footer. The numbers are inconsistent: "30+ years" vs "since 2000", "150+ varieties" vs 98 products.

### Overall score: **5.5 / 10**

| Area | Score | | Area | Score |
|---|---|---|---|---|
| Brand / first impression | 7.5 | | Cart | 6.5 |
| Navigation & discovery | 6 | | Checkout | 5 |
| Product cards | 6.5 | | Trust & legal | 4 |
| Product detail page | 4.5 | | Mobile | 6.5 |
| Content depth for plants | 3.5 | | Visual consistency | 7 |

The good news: most of the high-impact fixes are **content, copy and configuration** (attributes, photos, shipping rules, a card gateway, legal pages), not redesign.

---

## 2. Strengths (Pros)

1. **Clear value proposition and strong identity.** The hero ("Puieți care prind rădăcini și rodesc ani la rând"), the real-place story (Ciumbrud, the "village of roses") and the two CTAs (*Explorează Catalogul* / *Colecția de Trandafiri*) make clear what is sold within seconds. The logo, colours and typography are consistent across all pages.
2. **Category entry points on the home page.** Four category cards (fruit trees, roses, fruit shrubs, ornamentals) with example plants. The top bar also carries phone and "Livrare în toată țara" on every page.
3. **Instant, clear add-to-cart feedback.** The drawer opens immediately with image, qty stepper, remove icon and subtotal. The card badge "✓ În coș (n)" and the PDP banner "Aveți deja n buc. în coș" prevent accidental duplicates. Stock limits are enforced (recent commit).
4. **Honest, useful packaging and delivery copy.** "Rădăcini în hidrogel și rumeguș umed", 24–48 h transit, seasonal windows (autumn after first frosts, spring Feb–Apr) and what happens on damaged parcels. This is better than many competitors' hidden policies.
5. **Search and filters work and are shareable.** Debounced search, category → subcategory, sort (newest/price/name), "only in stock" and "load more", all kept in the URL. The empty-result state is friendly ("Nu am găsit produse… resetați filtrele").
6. **Mobile fundamentals are right.** Hamburger + search + cart icon with a badge in the header, a sticky "Adaugă în coș" bar on PDP showing name + price, a minimal distraction-free checkout layout, and form fields with correct `autocomplete`/`type="tel"`/`type="email"`.
7. **Guest checkout, single page, 8 required fields,** with a county dropdown for all 41 counties + București. Short for Romania.
8. **Good SEO and technical foundations:** SSR, JSON-LD on products, sitemap route, breadcrumbs.
9. **Fair "operational" promises on PDP:** a guarantee block, packaging block and delivery block sit directly under the CTA.

---

## 3. Weaknesses (Cons)

Severity: 🔴 blocks or loses sales · 🟠 reduces confidence/conversion · 🟡 polish.

| # | Issue | Impact |
|---|---|---|
| 1 | 🔴 **Rose and plant pages lack decision data.** Of 98 products, only 14 have attributes (fruit trees: rootstock, ripening, pollinators, spacing). **None of the 24 roses has height, colour, fragrance, bloom period, format, pot size, hardiness, group.** Descriptions are 76–350 chars, 1 image each. | The customer can't tell what arrives. Searches for "red fragrant climbing rose" are impossible. High pre-purchase hesitation. |
| 2 | 🔴 **Shipping cost unknown until a phone call.** Cart and checkout show "~25–35 lei (confirmată telefonic)". The total row says **"Total produse"** and excludes delivery. | The classic cart-abandonment trigger: surprise cost / unclear total. A 26 lei rose + ~30 lei shipping doubles the cost. |
| 3 | 🔴 **Cash-on-delivery is the only payment method** (bank transfer only "mention in notes"). No card/online payment. | Excludes buyers who don't want to pay a courier in cash, lowers trust for a new shop. Also raises refusal risk: COD parcels of live plants are refused and wasted more often. |
| 4 | 🔴 **Order depends on a phone call** ("Vă vom contacta telefonic…"). | Friction and delay. Fine for a micro-nursery, but customers expect an order e-mail and a tracking link. |
| 5 | 🟠 **No reviews or social proof.** Every product shows 0 reviews; no testimonials, ratings, photos from customers or order counts. | The biggest missing trust lever. Competitors (Famous Roses) show hundreds of photo reviews. |
| 6 | 🟠 **Legal/trust gaps:** no company legal name/CUI/Reg. Com., no ANPC / SAL / SOL links, thin terms (3 short sections), no withdrawal / "drept de retragere" text, privacy page doesn't name the data controller, no cookie notice seen. Contact is `adriancrainic@gmail.com`; footer credit "ACSOFT". | Romanian shoppers look for ANPC badges and company data. Also a compliance risk for an e-shop. *(Have a lawyer or accountant review before publishing; this is not legal advice.)* |
| 7 | 🟠 **Inconsistent claims.** "30+ ani de experiență" vs "Din 2000" (≈26 yrs) vs about-page "peste un secol"; "150+ soiuri" vs 98 products live; "Producător" vs "Material certificat" without any certificate shown. | Contradictions are noticed and cost credibility. |
| 8 | 🟠 **Catalog images look generic/mismatched.** Several ornamental/fruit images are stock-looking (e.g. a butterfly on Spiraea; red blossom for "Măr Florina"). Related apple cards rendered **empty beige image boxes** on PDP (missing images). One photo per product, no scale reference. | Customers buy plants by picture; poor/mismatched pictures breed distrust. |
| 9 | 🟠 **No seasonal availability logic.** The shipping page explains seasons, but products don't say "ships from…", there are no pre-orders and no "back in season" notification. One product (Photinia Red Robin) is simply "Stoc epuizat". | Lost demand in the off-season; unclear expectations. |
| 10 | 🟠 **No upsell/cross-sell beyond "same category".** No fertilizer/planting-kit, no "plant together with…", no bundles, no AOV nudge or free-shipping bar. | Low average order value against a fixed ~30 lei shipping cost. |
| 11 | 🟠 **Checkout defaults and guidance.** County pre-selected to **Alba** (risk of wrong county being submitted); the postcode is free text; note field is useful but there is no delivery-date choice. No order e-mail promise, no estimate of dispatch date. | Wrong-county errors → extra support calls. |
| 12 | 🟡 **Mobile catalog is a long single column** (21 cards, each large image). The header on mobile spends 4 lines on tagline "TRADIȚIE & CALITATE DIN 2000". About 70% of interactive elements on mobile catalog pages are under 44 px (qty +/- buttons, category chips, card "Adaugă" buttons); text as small as 10 px (badges, category labels). | Slower browsing, mis-taps, readability. |
| 12b | 🟡 **Hydration mismatch errors on `/produse`** (dev build): the grid/count flash from server-rendered "21 din 24" to an empty "0 din 0" state on the client. | Possible visible flicker or layout jump on the most-used page; verify in production. |
| 12c | 🟡 **404 page is the framework default** (no branded page, no search or category links). | Lost visitors from dead links. |
| 13 | 🟡 **Mixed price presentation:** "Preț / buc." label on every card, price in lei with no per-unit/per-bundle logic, "TVA inclus" only in the footer/PDP. | Minor. |
| 14 | 🟡 Emojis (🛡️📦🚚) as icons in trust blocks; slightly "template-like" next to the premium hero. | Minor polish. |
| 15 | 🟡 Production-readiness: stale build on `:3000` returned 500 on the catalog page (environment). | Verify deploy pipeline. |

---

## 4. Customer journey analysis

### A. Homepage and first impression — **7.5/10**
- *Works:* the hero immediately says "trees, shrubs, roses", is on-brand and has two CTAs; the benefits band and categories follow logically; the "how it works" 3 steps explain ordering; the About teaser tells the Ciumbrud story.
- *Issues:* the hero is tall (min-height 42–48 rem) and mobile shows the logo + pill + H1 + paragraph + 2 buttons + 3 stats before any product (≈ one full screen). The contradictory stats (see #7). "Produse recomandate pentru sezon" is just the 9 newest in-stock products, not a real bestseller/seasonal curation, with no "new / bestseller" badges. No promotions, no newsletter, no reviews, no "ships in autumn/spring" banner.

### B. Discovery and navigation — **6/10**
- *Works:* root categories and subcategories (Teahibrizi, Urcători, Meri, Peri…), search in header and catalog, sort and "in stock", counts per category, breadcrumbs, search without results handled.
- *Issues:* **no plant-specific filters** (colour, height, fragrance, sun/shade, use: hedge/pot/cut flowers, price range). Roses are a single list of 24. No "rose finder". Product cards show only name, category label, 1-line excerpt, price, stock badge; attribute chips appear only for the 14 fruit-tree products. Pagination is "load more" (OK). Footer links "Meri & Peri / Pruni & Cireși / Caiși & Piersici" point to single subcategories, which may mislead (e.g. "Caiși & Piersici" maps to one slug).

### C. Product page — **4.5/10**
- *Works:* single-column clear hierarchy: breadcrumb → category → stock badge → name → big price ("TVA inclus / buc.") → short description → qty stepper + big **Adaugă în Coș** CTA (burgundy/warm) → guarantee/packaging/delivery block → attributes (only fruit trees) → description → related. A sticky bar on mobile.
- *Missing (hesitation points):* height & spread at maturity and **age/size of the plant sold** (e.g. "1–2 years, 30–40 cm, bare-root, 2 L pot"); colour/fragrance/bloom time; sun, hardiness zone, planting distance for roses; **format** (bare-root vs potted); gallery with several photos (plant as shipped, in bloom, close-up, scale); reviews/ratings; planting & care guide link; **delivery date estimate by season**; real shipping cost; stock level ("only 5 left", the API already exposes a maximum, e.g. 77 for Eiffel Tower); the "guarantee" is a heading with no terms (how long? what is replaced?).
- *Related:* "Soiuri similare" from the same category only; some cards showed blank images.

### D. Cart — **6.5/10**
- *Works:* drawer + full page, qty stepper, trash icon, line totals, "Continuă cumpărăturile", reassurance block (guarantee, packaging), CTA "Continuă spre finalizare".
- *Abandonment points:* shipping not computed; "Total produse" ≠ amount to pay; no free-shipping threshold/progress bar; no promo/discount-code field; no delivery-timing note; no cross-sell; no "save cart / send reminder" (guests only).

### E. Checkout — **5/10** (order placement untested; the flow was reviewed in UI + code)
- *Works:* one page, guest-only, 8 required fields (first/last name, phone, email, address, county, city, postcode), optional note, summary with thumbnails, link back to cart, T&C/privacy acceptance by implication ("Prin plasarea comenzii…"), minimal header (distraction-free), sensible helper texts ("Telefon pentru curier").
- *Issues:*
  - Single payment option (COD) shown as a pre-selected radio with a long paragraph. Nothing about card or transfer details.
  - Shipping line "Confirmată telefonic" and total excludes delivery.
  - Validation is a single generic message at the top ("completați toate câmpurile…"); there is no inline per-field validation (browser-native `required` fires first). The server error text is shown raw after entity cleanup.
  - Default county = Alba; postcode is a plain text field (no format hint beyond placeholder); address is a single line (OK for RO) but there is no autocomplete/geolocation.
  - Phone is required (good for courier) but there is no phone format validation.
  - No explicit T&C checkbox (implicit consent is legally weaker; verify with counsel).
  - No order e-mail/confirmation promise on the checkout page; confirmation page promises a phone call and says "Număr referință comandă" only.
  - No option to choose preferred dispatch/delivery period other than free-text note (positive: the placeholder suggests it).
- *Mobile:* single column, order summary *below* the form with the submit button at the very end. Acceptable, but the total is not visible while filling in; a collapsed "Comanda ta (26,00 lei) ▾" at the top would help.

### F. Trust & credibility — **4/10**
- Present: phone & e-mail & address & opening hours in footer; About, Contact (with map), Shipping & Payment, Terms, Privacy; packaging/guarantee statements; consistent branding.
- Missing: company identity (legal name, CUI, J-number, phytosanitary registration/authorization number), **ANPC/SAL/SOL**, returns/withdrawal text, guarantee terms, reviews, payment-security cues (not applicable until card payment exists), real photos of the nursery/team/packaging, a business e-mail on the own domain, the "Produs în România"/"Certificat" claims with proof (e.g. phytosanitary certificate / plant passport).

### G. Mobile — **6.5/10**
- *Good:* sticky header with cart badge, hamburger menu, 1-column cards, large primary CTAs, sticky PDP bar, inputs with correct keyboards, no horizontal scroll observed, readable base font.
- *To improve:* shorten the header brand block (4-line tagline), 2-column product grid or smaller cards (the long single column of 21 big images), larger qty tap targets, a visible total on checkout, thumb-reachable filter bar (the filters occupy ~½ screen before products).

---

## 5. Competitor research

**Verification legend:** ✅ read directly from the live page by me · 🔎 from search-result summaries only (not opened, treat as "to be re-checked") · ⛔ site returned 403 to automated requests (not bypassed).

### Romania

| # | Store / URL | What was found | Source status |
|---|---|---|---|
| 1 | **Famous Roses** · https://www.famousroses.eu (also famous-roses.ro) | Specialist rose shop. Flat ~29 lei courier fee, no free-shipping tier mentioned; 2–3 days after dispatch. **Season split:** bare-root Oct–Mar, potted Apr–Sep. Rich filters: colour, fragrance, mature height, type (climbing/floribunda/hybrid tea/shrub), pots/part shade, ADR award, nearly thornless, cut flowers. Product pages: breeder history, vigor, disease resistance, bloom, fragrance. Photo/video reviews (Judge.me, reportedly ~4.9★). Planting & care guide. **Strength:** the closest model for us. **Weakness:** no basket-building incentive. | 🔎 ⛔ |
| 2 | **Pepiniera României** · https://www.pepinieraromaniei.ro | Free shipping from **299 lei**, otherwise 24.90 lei; orders in stock before **14:00 ship the same day**. Colour + growth-type filters. Mix packs of 3 roses. **Pattern:** dispatch cut-off time. | 🔎 ⛔ |
| 3 | **Sweet Garden** · https://www.sweetgarden.ro | ✅ Header banner: **"La comenzi de peste 399 LEI transport GRATUIT"** and a **gift for orders over 100 lei**; ANPC links in footer; a dedicated **/garantie** page linked in the menu. 🔎 15-day plant guarantee; shipping by planting window. **Pattern:** threshold banner + gift tier + visible guarantee page. | ✅ + 🔎 |
| 4 | **Pepiniera Sosa** · https://www.pepinierasosa.ro | Free shipping from 299 lei; card (Netopia), COD and bank transfer. **Pattern:** 3 payment options. | 🔎 |
| 5 | **Verdena** · https://www.verdena.ro | 26.50 lei under 399 lei, free above; **separate flat 150 lei pallet class** for plants >200 cm or >15 kg, prepaid. **Pattern:** oversize shipping class. | 🔎 ⛔ |
| 6 | **Doropad** · https://www.doropad.ro | Roses ≈20–45 lei, standards ≈125 lei, quantity discounts 5+/50+. Shipping = real courier cost + packaging, no fixed rate. **Weakness:** opaque shipping, like our current state. | 🔎 |
| 7 | **Pom Fruct** · https://www.pomfruct.ro | Weight-based courier cost (~23 lei first kg + ~1.2 lei/kg), handling fee under 5 pieces; COD/transfer/card; cost confirmed by a representative after the order. **Weakness:** same "confirmed later" problem. | 🔎 |
| 8 | **Dedeman** · https://www.dedeman.ro | Mass-market: potted roses ≈36 lei, bare-root from ≈6.90 lei. Forces store/locality choice; filters only price/availability; no rose attributes. **Weakness for discovery; strength in price perception and brand trust.** | 🔎 |

**Romanian price benchmarks (search summaries, approximate):** potted garden roses 25–45 lei; licensed varieties 35–70 lei; standard roses 90–130 lei. Our roses at **22–28 lei** are at/below the low end, so there is no price problem; the gap is information and trust. **Free-shipping thresholds seen: 299–399 lei; standard courier fee 24.90–29 lei**, which is in line with our "~25–35 lei".

### International

| # | Store / URL | What was found | Source status |
|---|---|---|---|
| 9 | **Meilland Richardier** · https://www.meillandrichardier.com | ✅ Homepage prominently promotes **"Garantie Confiance"** (dedicated /garantie page) and "Livraison offerte dès …" (free-delivery tier). 🔎 Guarantee: roses 2 years, shrubs/perennials 1 year, annuals 30 days; claims need photos + labels; remedy = replacement or credit. Shipping computed in the cart by weight/volume. **Pattern:** guarantee as a headline asset, tiered by plant type. | ✅ + 🔎 |
| 10 | **David Austin Roses** · https://www.davidaustinroses.com (and .co.uk) | **Rose Finder** (placement, height, fragrance, colour); filters by type and conditions (part shade, poor soil, hedge); bare-root, potted and standard formats; **autumn pre-orders for bare-root**, shipped in the planting window; breathable box with damp material; damage claim in ~48 h with photos; gift cards. A 5-year guarantee is quoted by a secondary source only: **verify on the official site.** | 🔎 ⛔ |
| 11 | **Rosen Tantau** · https://www.rosen-tantau.com | German breeder-grower. Free delivery above ~135 € in DE (Standard roses cost more); 3–10 working days; **desired delivery date in the order note**; bare-root ships Oct–Dec and mid-Mar–mid-May, container roses mid-May–Oct; frost pause. Homepage fetched but did not expose these numbers to me, so figures are unverified. | 🔎 (page fetched, ✅ reachable) |
| 12 | **Peter Beales Roses** · https://www.peterbealesroses.com | Three delivery speeds (Economy/Standard/Express) with thresholds (free economy over £75); bare-root Nov–Mar with no guaranteed date, potted year-round; reported 1-year guarantee. **Pattern:** delivery options by speed and by plant format. | 🔎 ⛔ |
| 13 | **Jackson & Perkins** · https://www.jacksonandperkins.com | Shipping timed by **USDA zone** so plants arrive dormant at the right moment; storage in humid coolers; 48 h damage report; seasonal claim deadlines. **Pattern:** "we ship when it's safe to plant in your area". | 🔎 |

**What none of my research could verify:** homepage layouts, product-card visuals, checkout steps and mobile behaviour of the ⛔ sites. I do **not** claim to have tested their cart/checkout.

### Patterns that work (across competitors)
1. **Season-aware delivery** (bare-root vs potted windows, pre-order in autumn).
2. **Free-shipping threshold announced site-wide** (299–399 lei in Romania) and often a **gift tier**.
3. **A named, linkable plant guarantee** with clear terms (Meilland: 2 years for roses; Sweet Garden: 15 days).
4. **Guided discovery** (David Austin's Rose Finder; Famous Roses' attribute filters).
5. **Rich product data**: height, fragrance, bloom, disease resistance, format, breeder, photos, planting guide.
6. **Reviews with photos** (Famous Roses).
7. **Operational clarity**: dispatch cut-off time (14:00), oversize shipping class, damage-claim procedure with photos within ~48 h.

---

## 6. Competitive comparison

| Dimension | Pepiniera Ciumbrud | Competitors | Verdict |
|---|---|---|---|
| Brand/story ("Satul Rozelor", producer since 2000) | Strong, authentic, visually premium | Mostly generic shop templates (RO); premium for DA / Meilland | **We are better than most Romanian shops** |
| Price | Roses 22–28 lei | 25–45 lei typical | Competitive / low |
| Packaging explanation | Detailed (hidrogel, rumeguș, 7–10 days) | Often buried | **Better** |
| Shipping info on PDP | Range + link | Cost in cart (calculated) | **Worse**: no calculated price |
| Free-shipping threshold | None | 299–399 lei common | **Worse** |
| Payment options | COD only | Card + COD + transfer | **Worse** |
| Guarantee | Heading only, no terms | 15 days to 2 years, named page | **Worse** |
| Filters / finder | Category, sort, stock | Colour, height, fragrance, type, finder | **Worse** |
| Product data for roses | Description only | Full sheet (breeder, bloom, vigor…) | **Much worse** |
| Reviews | None | Photo reviews | **Much worse** |
| Season logic | Static page | Per-format windows, pre-orders | **Worse** |
| Legal/trust footprint | Thin, no CUI/ANPC | ANPC, company data | **Worse** |
| Checkout length | 1 page, 8 fields, guest | Similar (Woo-based) | **On par** |
| Mobile | Good basics, sticky bar | Unknown for most | **Likely on par** |
| Cart feedback (drawer) | Instant drawer | Varies | **On par/better** |

**Where we can differentiate (without copying blindly):**
- **"The rose village" story** with real photos of the fields, the team, the grafting and the packaging process. Competitors can't claim this.
- **Producer-direct guarantee** with *simple, generous, honest* terms (e.g. replacement if a plant fails to grow in the first season, photo claim). Easy to beat the 15-day Romanian norm.
- **Seasonal, dispatch-date-aware ordering** ("order now, we dig and ship from 1 Nov / March"), appropriate for a grower who digs to order.
- **Human advice** (phone/WhatsApp, "ask the grower") as a feature, not a fallback. Already in copy ("Consultanță gratuită"); make it a visible button.
- **Local Romanian growing conditions** (frost hardiness, soil, Transylvanian climate) in plant data. Foreign sites can't offer this.

---

## 7. Conversion optimization opportunities

### Why visitors might leave
- "What exactly will I receive, and how big?" (no size/age/format).
- "What is the final price?" (shipping unknown; total excludes it).
- "Can I trust this shop with my card/phone?" (no company data, no reviews, no card payment).
- "When will it arrive?" (no date by season).
- "What if it dies?" (guarantee has no terms).
- "Do I have to wait for a call?" (phone confirmation).

### Quick wins (days, mostly content/config)
1. **Show total with shipping.** Configure a Woo shipping method (flat 29 lei or by weight; the real figure is the nursery's decision) so the cart computes it; rename "Total produse" to "Total de plată".
2. **Free-shipping threshold** (e.g. 300 lei, a level to be set by margin) with a **progress bar** in the drawer/cart ("Mai adaugă 74 lei pentru transport gratuit") and a header banner.
3. **Add a standard plant data sheet to every rose** (colour, height, fragrance, bloom, group, format/size, sun, hardiness). Fill 24 roses first.
4. **Add 3–5 photos per rose** (bloom, whole plant as shipped, root packaging, scale).
5. **Write the guarantee page** and link it from header, PDP, cart and checkout.
6. **Fix contradictions** (years, number of varieties) and replace placeholders/missing images.
7. **Company data + ANPC/SAL/SOL + withdrawal text** in the footer and terms; business e-mail on own domain.
8. **Remove the county default "Alba"** (use "Alege județul…"), add inline field errors.
9. **Sticky "Comanda ta: 26,00 lei" summary on mobile checkout.**

### Medium effort
10. **Online card payment** (Netopia/EuPlătesc/Stripe) in addition to COD; keep a bank-transfer option. Add a small COD fee or a minimum if refusal rate is an issue (to test).
11. **Reviews:** invite buyers by e-mail 30–60 days after delivery (plants need time); show photos. Even seeding with real testimonials and nursery photos helps now.
12. **Rose filters:** colour, height, fragrance, use (pot/hedge/cut), sun. Add a 4-question **"Găsește trandafirul potrivit"** quiz.
13. **Season banner and per-product "se livrează din…"** plus **pre-order for autumn/spring bare-root** and a "notify me" form for out-of-stock (Photinia).
14. **Order e-mail + tracking** and a dispatch-date promise ("Expediem în 2–3 zile lucrătoare") to reduce reliance on phone calls.
15. **Cart reminders** (abandoned-cart e-mail after capturing e-mail at the first checkout field).

### AOV and repeat purchases
- **Bundles:** "Set gard viu 10 buc.", "Colecția roșie 5 trandafiri" at a small discount; quantity discounts (5+/10+), as Doropad does.
- **Cross-sell:** planting kit (compost, rooting hormone, mycorrhiza, stakes) in cart and PDP; "Pomii care se polenizează între ei" (pollinator partners) for fruit trees; "Se potrivește cu…" for roses.
- **Gift tier** (like Sweet Garden) on orders over a threshold; gift cards for Christmas/Mother's Day/Valentine's.
- **Repeat:** seasonal e-mails (autumn planting, spring pruning, "time to feed your rose"), care-calendar reminders after purchase, loyalty/discount for the second order, wish-list / "save for spring".

### Living-plant specifics
- **Show what the plant looks like when shipped** (bare-root/height) next to the in-bloom photo.
- State **format and size** in the title or badge ("Rădăcină liberă · 2 ani · 40–60 cm").
- Put **"Livrăm din noiembrie până la îngheț; din martie până în aprilie"** on the PDP and cart, with an expected date.
- A **"Cum arată coletul"** photo and a 3-step planting guide (PDF/short video) linked in the confirmation e-mail.
- **Damaged-on-arrival procedure** in a single sentence on the PDP (photo within 24–48 h → replacement).

---

## 8. Missing features and functionality

### Essential (should exist before scaling traffic or advertising)
| Feature | Status today |
|---|---|
| Calculated shipping cost in cart/checkout; total includes it | **Missing** (text only) |
| Card payment | **Missing** (COD only) |
| Rose/plant attributes (height, colour, fragrance, bloom, format, size) | **Missing** for roses; present only for some fruit trees |
| Multiple product photos | **Missing** (1 per product; gallery code exists) |
| Guarantee page with terms | **Missing** (claim only) |
| Legal identity, ANPC/SAL/SOL, withdrawal and returns text, cookie/GDPR completeness | **Missing / thin** |
| Order confirmation e-mail with details | **Not testable** here; verify |
| Reviews/testimonials | **Missing** (0 reviews; no UI for submitting) |
| Consistent, truthful stats & catalog content | **Needs fixing** |

### Important (next)
Free-shipping threshold & progress bar · rose-specific filters and quiz · seasonal "ships from" & pre-orders · back-in-stock notification · sticky mobile checkout summary · inline form validation · tracking link · abandoned-cart e-mails · bundles and cross-sell kits · related products by use (not only category) · planting/care guides (blog or PDF).

### Nice to have
Gift cards · wishlist · customer accounts (order history; guest checkout must stay) · WhatsApp chat button · compare feature · loyalty · video of the nursery · size-guide infographic (bare-root vs container) · "delivery date" picker · social feeds with real customer photos · B2B/wholesale price tiers ("comenzi en-gros" is mentioned in the footer but there is no wholesale flow).

---

## 9. Prioritised roadmap

| Priority | Action | Why | Effort |
|---|---|---|---|
| P0 | Calculated shipping + "Total de plată" + free-shipping bar | Removes #1 abandonment trigger | S |
| P0 | Rose data sheet + 3–5 photos per rose | Answers "what will I get?" | M (content) |
| P0 | Legal/identity/ANPC/guarantee pages; fix contradictory claims | Baseline trust and compliance | S |
| P0 | Add card payment | Biggest trust/conversion lever after content | M |
| P1 | Reviews (seed + post-purchase flow) | Social proof | M |
| P1 | Season banner, "ships from", pre-order, notify-me | Fits the plant business model | M |
| P1 | Filters (colour/height/fragrance/use) + rose finder | Discovery for 24+ roses | M |
| P1 | Checkout polish (no county default, inline errors, mobile summary, order e-mail) | Fewer errors/calls | S |
| P2 | Bundles, kits, quantity discounts, gift tier | Higher AOV | M |
| P2 | Care guides, e-mail lifecycle, gift cards | Repeat purchase | M–L |

**Suggested measurements** (to run once there is traffic): cart-to-checkout rate, checkout completion, COD refusal rate, % of PDP with add-to-cart, shipping-cost page exits, search-with-no-results queries, mobile vs desktop conversion.

---

## 10. Appendix

**Catalog snapshot (local API):** 98 products · Pomi fructiferi 29 · Trandafiri 24 · Arbuști fructiferi 23 · Arbuști ornamentali 22 · 1 out of stock · 0 reviews · prices from 6 to 85 lei · all products "simple" (no variations such as pot size/age).

**Screenshot evidence** (headless Chrome, 390 px): saved under `/tmp/cdp/` (`m_home.png`, `m_list.png`, `m_cat_roses.png`, `m_search_none.png`, `m_pdp.png`, `m_pdp_attr.png`, `m_after_add.png`, `m_cart.png`, `m_checkout.png`, …). These are temporary; copy them if you want to keep them.

**Source URLs:** famousroses.eu · pepinieraromaniei.ro · sweetgarden.ro (✅ homepage read) · pepinierasosa.ro · verdena.ro · doropad.ro · pomfruct.ro · dedeman.ro · meillandrichardier.com (✅ homepage read) · davidaustinroses.com · rosen-tantau.com · peterbealesroses.com · jackson­andperkins.com. Details marked 🔎 come from search summaries and **must be re-verified on the live sites before being quoted externally**.

*No source files were modified. The only file added to the repository is this report.*
