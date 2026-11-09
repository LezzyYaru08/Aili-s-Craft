# Feature integration notes

This build adds the six requested features without replacing the existing front-end storage/workflows.

## Current prototype storage
The supplied application is a static HTML/CSS/JavaScript app using `window.ACStore`/localStorage rather than a server API. Therefore:
- GCash down-payment data is captured locally (`paymentStatus`, `downPaymentStatus`, amount, percentage, reference, proof filename).
- Reviews, notifications and reorder/cancel state are persisted locally.
- Real gateway verification, server-side inventory locking, secure file upload, authentication and cross-device synchronization still require the production backend.
- `database/migrations/001_feature_integration.sql` provides additive SQL structures for that backend.

## Social links
Update `CFG.social` in `features.js` with the shop's verified official Facebook, Instagram and TikTok URLs if the handles differ.

## Backward compatibility
Existing `main.js`, product customization, cart, profile/login, checkout, visual preview and styling remain in place. New behavior is layered through `features.js`.

## Product reviews (database-backed)

- Customer-facing product review pages are opened from each available-product card using its `product_code`.
- `api/product_reviews.php` reads and writes the existing `product_reviews` table. Customers can submit one review per purchased order item only after the order is marked `Completed` or `Delivered`.
- The owner panel reads the same table through `action=owner-list`; the dashboard average, rating distribution, product ratings, and Reviews page are based on those database records (not demo/localStorage reviews).
- For an existing database, run `database/migrations/005_product_reviews_catalog.sql` once. It seeds the catalog product codes used by the storefront and links older matching order items so the existing review table's foreign keys can be used. Fresh schema imports include the catalog seed automatically.
