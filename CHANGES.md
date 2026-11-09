# Aili's Craft — Change Log (fix & feature round)

Date: 2026-10-30. All changes are additive; no existing functionality or assets were removed.

## v6 (2026-11-02) — Real email + SMS delivery (free methods), CSS merged

### FIX 1 — Password reset now sends real email (free), log kept as record
- **Before:** `ac_send_reset_email()` used PHP `mail()`, which cannot deliver on XAMPP/localhost — reset links only landed in `uploads/reset_links.log`.
- **After:** New `AilisCraftUser/api/email_config.php` + rewritten `ac_send_reset_email()` in `api/auth.php`. Sends via **Brevo HTTP API** (free 300 emails/day, no credit card, no local SMTP needed), with **Resend** (free 100/day) and PHP `mail()` as fallbacks. The reset link is **always** written to `uploads/reset_links.log` as a record, even when real sending is on. Sign up free at brevo.com → paste API key into `email_config.php`. Debug mode still returns `debugResetLink`.
- **Files:** `AilisCraftUser/api/email_config.php` (new), `AilisCraftUser/api/auth.php` (patched).

### FIX 2 — Order-status SMS now sends real SMS (free), log kept as record
- **Before:** Only paid Semaphore worked; empty key = log-only.
- **After:** `api/sms_config.php` + `api/sms.php` now support **TextBelt** (free tier — key `textbelt`, 1 SMS/day per IP, **no signup needed**, PH numbers supported) and **Twilio** (free trial credits) alongside Semaphore. Default provider is `textbelt` with key `textbelt` — works out of the box. Every attempt is always logged to `sms_log` DB table + `uploads/sms_log.log`. SMS failure still never breaks an order-status update.
- **Files:** `AilisCraftUser/api/sms_config.php`, `AilisCraftUser/api/sms.php` (both rewritten, `ac_send_sms` signature unchanged).

### FIX 3 — Two CSS files in AilisCraftUser merged into one `style.css`
- **Before:** `style.css` (orphan dashboard rules, unreferenced) + `styles.css` (active main, 2269 lines, referenced by all 16 PHP/HTML pages).
- **After:** Combined into a single `style.css` (3774 lines). Orphan rules placed first, **active main `styles.css` rules placed LAST** so they win on any selector conflict — main designs untouched. Duplicate Google Fonts `@import` deduplicated to one at the top. All 16 pages updated to `href="style.css"`; old `styles.css` removed (originals preserved as `.orig.bak`).
- **Files:** `AilisCraftUser/style.css` (combined), all 16 `*.php`/`*.html` in `AilisCraftUser` (link tag updated).

## 1. Login / register background white gap — FIXED
**Cause:** `.app-frame` has `padding-top: var(--nav-height)` (reserved for the topbar on interior pages). Auth pages (login/register/forgot-password/order-success/register-success) have no topbar, so the padding exposed a strip of the light body background above `.hero-frame`.
**Fix:** Added modifier class `app-frame--auth` to `.app-frame` in the five auth pages, plus CSS `.app-frame--auth { padding-top: 0; }`. Also removed `margin-bottom: 100px` from `.hero-frame__bg` (it left an unpainted strip at the bottom of the background image since `inset:0` + margin shrinks the image box).
**Files:** `AilisCraftUser/styles.css`, `login.php`, `register.php`, `forgot-password.php`, `order-success.php`, `register-success.php`.

## 2. Special Request + picture upload in every customize category — ADDED
- `customize.php`: Special-request textarea + reference-picture file input (`accept="image/*" multiple`) now present in **all four** category panels (flowers, bags, headwears, keychains). Duplicate `id="special-request"` replaced with class `.js-special-request` (unique ids per panel). Selected images render as thumbnails before upload.
- `main.js`: on "Add to bag", selected pictures are uploaded (multipart) to `api/upload_special_request.php`; returned paths are stored on the cart item as `specialRequestImages[]`. Upload failure never blocks adding to bag (toast + empty array).
- `AilisCraftUser/api/upload_special_request.php` (NEW): validates real images (finfo mime, <2 MB, jpg/png/gif/webp), unique filenames, saves to `AilisCraftUser/uploads/special_requests/`, returns `{ok:true, files:[...]}`.
- `AilisCraftUser/uploads/` (NEW): `special_requests/` + `snapshots/`, each with `index.html` and `.htaccess` (PHP engine off, no execution, no indexes).
- `api/orders.php`: `specialRequestImages` stored comma-joined in new `order_items.special_request_images` column.

## 3. Automatic product snapshot → Order Summary "Your Bag" — ADDED
- `customize.php`: html2canvas 1.4.1 loaded from cdnjs CDN (guarded; falls back gracefully).
- `main.js` (`captureLivePreviewPng`): before navigating to order-summary, captures the live preview as a PNG data URL — flowers rasterize the SVG preview; all categories use html2canvas on the preview box; fallback chain: html2canvas → product imageUrl → (legacy SVG `image` field) → empty. Stored on cart item as `snapshotPng` (survives navigation via `ac_cart` localStorage).
- `main.js` (`initOrderSummary` render): cart-item thumbnail now prefers `snapshotPng` → `imageUrl` → SVG `image` → emoji, replacing the blank pink placeholder. `.cart-item__thumb` gets `overflow:hidden` and img fill styles.
- `api/orders.php`: `snapshotPng` (null-byte stripped, 8 MB cap) persisted to `order_items.snapshot_data` (LONGTEXT, already existed).

## 4. Snapshot in Owner orders page — ADDED
- `AilisCraftOwner/script.js`: orders table gains a **Snapshot** column (48×48 clickable thumbnail). Click opens an enlarged lightbox (reuses existing `#modal`/`#modalBackdrop` pattern; closes on backdrop). The order-detail modal (`viewOrder`) now shows a gallery of every item snapshot (previously it incorrectly wrapped PNG data-URLs in a `data:image/svg+xml` prefix — fixed).
- Data flows from `../AilisCraftUser/api/orders.php?action=owner-list` → `items[].snapshot` (already returned; not truncated).
- `AilisCraftOwner/style.css`: `.order-snapshot-thumb`, wide-modal + large-image styles.

## 5. Direct Messaging — ADDED
**User side:** `AilisCraftUser/about.php` gains a "Direct Message the Shop" card (subject + message + Send). Guests see a "please log in" note; logged-in users POST to `api/send_message.php`. `AilisCraftUser/api/send_message.php` (NEW): requires session login, inserts into `user_messages` (table already existed in schema), returns `{ok:true}`.
**Owner side:** `AilisCraftOwner/script.js` Reviews page now has two sub-tabs — *Product Reviews* (existing, untouched) and *Direct Messages*. Messages list (name/email, date, subject, body, Mark-as-read) with unread highlighting + unread-count badge; prefetched silently at startup. `AilisCraftOwner/api/messages.php` (NEW): `GET ?action=list` (JOIN users, newest first) + `POST ?action=mark-read {id}`. Styles in `AilisCraftOwner/style.css`.

## Database schema (`ailiscraft_database_schema.sql`)
- `order_items` now includes `special_request_images VARCHAR(1000) NULL` in `CREATE TABLE` + a matching `ALTER TABLE` migration note at the end.
- `order_items.snapshot_data` (LONGTEXT) and the `user_messages` table already existed and are reused; no other structural changes.

## Verification & runtime assumptions
- `node --check` passes on `AilisCraftUser/main.js`, `features.js`, and `AilisCraftOwner/script.js`.
- **PHP is not installed in the build sandbox**, so `php -l` could not be run; all new PHP follows the existing `require_once 'config.php'` / `ApiError` / `json_out` / PDO-prepared-statement patterns exactly.
- `AilisCraftUser/uploads/` must be writable by the web server.
- html2canvas loads from CDN; if blocked, snapshot capture falls back to the product image URL (then empty; legacy SVG preview still renders for flowers).
- `config.php` JSON body cap is 4 MB (shared, unchanged) — typical orders fit; very large multi-item orders with many large snapshots could approach it.
- No server-side auth gate was added to `about.php` (matches existing pattern); `send_message.php` enforces login server-side.

---

## v2 (2026-10-30) — bug fixes on top of v1

### Bug 1 — Snapshot never showed for non-flower categories (fixed in 3 places)
Root cause: `customize.php` had no `data-product-image` attributes, so `main.js` always read an empty `imageUrl`; when html2canvas CDN is offline (local XAMPP), capture fell back to empty; `api/orders.php` then fell back only to the flower-only SVG `image`, leaving `snapshot_data` empty for bags/headwears/keychains; the owner modal's `snapshotGalleryHtml()` early-returned with no gallery section.
- **(a)** `AilisCraftUser/customize.php`: added a hidden reference `<img data-product-image="1" src="...">` inside each of the four category panels — `assets/images/flower1.png`, `bag1.png`, `headwear1.png`, `keychain1.png` (all verified present) — so `panel.querySelector('[data-product-image]')` resolves per active panel.
- **(b)** `AilisCraftUser/api/orders.php`: snapshot fallback chain is now `snapshotPng` (PNG data URL) → `imageUrl` (product image path, clean_str 500) → legacy SVG `image`. A plain product image is now always stored in `snapshot_data`.
- **(c)** `AilisCraftOwner/script.js`: new `snapshotSrc()`/`orderThumbSrc()` helpers — data/absolute URLs pass through, relative paths are prefixed with `../AilisCraftUser/` (resolves from the owner panel). `snapshotGalleryHtml()` precedence per item: `item.snapshot` → `item.imageUrl` → `o.snapshot`; same fallback applied to the orders-table thumbnail. Gallery section now always renders when any image exists.

### Bug 2 — Literal "\n" text in Owner order modal (fixed)
Root cause: three `.join("\\n")` (escaped backslash + n = literal two chars) where a real newline was intended. Changed all three to `.join("\n")` (actual newline char): customization-summary join (~line 99), Delivery/Payment readonly `<textarea>` in `viewOrder` (~line 453), and CSV export row separator (~line 480). Verified `grep -cF 'join("\\n")'` returns 0.

### Verification (v2)
- `node --check` passes on `AilisCraftUser/main.js` and `AilisCraftOwner/script.js`.
- `customize.php` has exactly 4 `data-product-image` attributes (one per panel).
- PHP still not available in sandbox for `php -l`; changes follow existing patterns.

---

## v3 (2026-10-30) — robustness fixes on top of v2

### Bug A — Broken/invalid snapshot values (hardened)
Older/other checkout code stored placeholder text/HTML in `snapshot_data` (e.g. "Saved customization snapshot"), which produced a broken image icon in the owner modal. Fixed three ways:
- **(a)** `AilisCraftOwner/script.js` `snapshotSrc()` now accepts a relative path ONLY if it matches `/^[a-zA-Z0-9_\/.\-]+\.(png|jpe?g|gif|webp|svg)$/i` (no spaces, angle brackets, or quotes); anything else returns "" (no snapshot).
- **(b)** `onerror="this.style.display='none'"` added to every snapshot `<img>` (orders-table thumbnail, gallery figure, lightbox large image); gallery figures with no valid image render an emoji + name fallback instead.
- **(c)** New `fallbackAssetSrc()` — last-resort image derived from the owner panel's OWN `assets/products/` (bag1/flower1/headwear1/keychain1.png) by keyword-matching the item name/category (keychain→keychain1, bag/sling/purse/baguette→bag1, flower/bouquet/rose/…→flower1, head/bandana/clip/…→headwear1). Path is `assets/products/xxx.png` (no prefix). Applied in both `snapshotGalleryHtml()` and `orderThumbSrc()` — an image always shows.
- **User side:** `AilisCraftUser/main.js` `initOrderSummary` gained `isValidSnapshotSrc()`; both `snapshotPng` and `imageUrl` thumb branches are gated on it, with `onerror` hiding broken imgs; invalid values fall through to SVG preview → emoji.

### Bug B — Literal "\n" inside stored data strings (fixed)
The DB stores literal backslash-n (two chars) *within* data strings, not just between joined fields. New `unescapeNewlines(s)` helper converts `\n`→newline and `\r`→carriage-return; applied to the content of BOTH `viewOrder` textareas: Customization/Special Requests/Letter, and Delivery/Payment Details (wrapping the whole joined string). Not applied in HTML contexts.

### Verification (v3)
- `node --check` passes on `AilisCraftUser/main.js` and `AilisCraftOwner/script.js`.
- `snapshotSrc` regex confirmed; `onerror` present on all snapshot imgs; both `viewOrder` textareas wrapped in `unescapeNewlines()`.

---

## v4 (2026-10-30) — offline snapshot guarantee

### html2canvas bundled locally (offline XAMPP support)
User runs offline XAMPP on localhost with no internet, so the CDN html2canvas was blocked — real PNG snapshots were never captured, only the plain product-photo fallback (confirmed: `snapshot_data` stored relative paths like `assets/images/keychain1.png`, no `data:` URLs). Fix:
- Downloaded html2canvas 1.4.1 minified and bundled at `AilisCraftUser/assets/js/html2canvas.min.js` (195 KB, MIT, verified).
- `customize.php` now loads `<script src="assets/js/html2canvas.min.js"></script>` locally — no CDN dependency; `window.html2canvas` is defined offline, so real live-preview PNG snapshots (data:image/png;base64) are captured into `snapshotPng` and persisted.

### Owner panel: guaranteed image always renders
- `snapshotSrc()` rewritten: ONLY `data:` / `http(s)://` / protocol-relative `//` URLs pass through (real captured snapshots). ANY relative path returns `""` — cross-panel relative paths are unreliable when folders are renamed/nested (the `../AilisCraftUser/` prefixing was removed; the only remaining `../AilisCraftUser` reference is `ORDER_API`, which is correct).
- Both `snapshotGalleryHtml()` and `orderThumbSrc()` now fall through `item.snapshot → item.imageUrl → o.snapshot → fallbackAssetSrc(...)` — `fallbackAssetSrc` (owner's own guaranteed `assets/products/*.png`, keyword-matched to category) is always the last resort, so the thumbnail and gallery never render empty/broken.
- `.snapshot-fallback` span hidden by default (CSS `display:none` + inline `style="display:none"`); gallery img `onerror` nullifies itself, hides the img, and reveals the emoji fallback. `node --check` passes on both JS files.

### User-side belt-and-braces
- `main.js` cart-push stores `snapshotPng: snapshotPng || imageUrl || ''` (never empty when a product image exists); order-create payload includes both `snapshotPng` and `imageUrl`. `api/orders.php` guarantees `imageUrl` lands in `snapshot_data` even if prior branches were empty. `isValidSnapshotSrc` gating + `onerror` hiding retained in "Your Bag".

---

## v5 (2026-10-30) — owner modal: special-request photos + snapshot rendering

### FIX 1 — Special Request Reference Photos now display in owner modal
- `AilisCraftUser/api/orders.php` owner-list already returned `specialRequestImages` per item (added in v1, line 562; stored comma-joined in `order_items.special_request_images`). Confirmed untruncated; the 4 MB cap in config.php applies only to incoming POST bodies, not outgoing JSON.
- `AilisCraftOwner/script.js`: new `refPhotoSrc()` helper (prefixes `../AilisCraftUser/` for relative upload paths; passes through data/http URLs) and `specialRequestSectionHtml()`. viewOrder now renders a **"Special Request Reference Photos"** section between the snapshot gallery and the Delivery/Payment textarea — thumbnails (click to enlarge, `onerror` hides broken imgs) plus the special-request TEXT extracted from `item.customization.specialRequest`. Shows muted "No reference photos uploaded." when empty.

### FIX 2 — Real PNG snapshot (data: URL) rendering hardened
- `snapshotSrc()` already passes `data:`/absolute URLs through (v4). Snapshot gallery imgs and orders-table thumbnails now use `onerror="this.onerror=null;this.src=fallbackAssetSrc(...)"` — a bad/huge snapshot swaps to the owner's own guaranteed product photo, never a broken icon. Fallback chain remains `item.snapshot → item.imageUrl → o.snapshot → fallbackAssetSrc`. `node --check` passes on both JS files.
