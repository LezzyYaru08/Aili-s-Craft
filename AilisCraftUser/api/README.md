# Aili's Craft — Orders API (PHP + MySQL)

Saves orders, customized items, customization snapshots, payment details and order-status history into `ailiscraft_db`.

## Setup
1. Import `ailiscraft_database_schema.sql` into MySQL/MariaDB (XAMPP/Laragon).
   Note: the script drops and recreates the tables, so only import it on a fresh or disposable database.
2. If the database already contains data, back it up and run `database/migrations/002_order_sync_details.sql` once. Fresh imports of the updated root schema already include the new columns and table.
3. Check the `DB_*` values in `config.php` (defaults: `127.0.0.1`, `root`, no password, `ailiscraft_db`).
4. Serve the `AilisCraftUser` folder through Apache/PHP (e.g. `http://localhost/AilisCraft/AilisCraftUser/`).
   Opening the HTML files directly (`file://`) cannot reach the API.
5. Log in on the site first (the order is stored under the logged-in email), then place an order.

Requires PHP 8.1+ with `pdo_mysql`.
Set `AC_DEBUG=1` in the environment to see raw database errors in API responses while developing.

## Endpoints (`orders.php`)
| Action | Method | What it does |
|---|---|---|
| `?action=create` | POST | Inserts `orders`, one `order_items` row per bag item (with `customization_summary` + `customization_json`) and an `order_payments` row, in one transaction. Subtotal, delivery fee, total and the 30% / 100% GCash down payment are recomputed on the server. |
| `?action=list&email=...` | GET | Returns the customer's orders with items, newest first. |
| `?action=cancel` | POST `{orderCode, email}` | Sets cancellation/payment state and records a status-history entry. |
| `?action=owner-list` | GET | Returns database-backed orders and their saved items for the owner dashboard. |
| `?action=owner-status` | POST `{orderCode, status}` | Saves owner status changes, adds an audit entry and creates a customer notification. |

## What is stored in `order_items.customization_json`
```json
{
  "cartItemId": "item_1793158511628",
  "productCode": "",
  "options": ["Flowers: Rose & Tulip", "Wrapping: Full Wrap", "Wrapper Color: Lavender", "Love letter: \"Happy anniversary!\""],
  "details": {
    "flowers": ["Rose", "Tulip"], "flowerCount": 3, "foliage": ["Olive", "Fern"],
    "wrapping": "Full Wrap", "wrapColor": {"name": "Lavender", "hex": "#c9a8e0"},
    "letter": {"type": "Love letter", "message": "Happy anniversary!"},
    "specialRequest": "Deliver before 5pm"
  }
}
```
`customization_summary` holds the same `options` joined with `; `.

## Known limits (not part of this change)
- Registration/login are still browser-only, so the API creates a minimal `users` row (random, unusable
  password hash) the first time an email places an order. Connect real registration to `users` and add
  server-side sessions before production; until then `list`/`cancel` trust the email sent by the browser.
- Custom-item prices are calculated in the browser; the server only recomputes totals from them.
- `products` has no seed rows, so `order_items.product_id` stays `NULL` until catalog products exist
  (they are matched by `product_code`, e.g. `key_bunny`).

## Owner dashboard integration and security
The owner dashboard reads and updates orders through these endpoints, so it must be served through PHP/Apache and be able to reach the same database. This prototype still uses browser-side owner login state; the owner endpoints are **not protected by server-side owner authentication** yet. Do not expose the site publicly until owner login is converted to a PHP session and the owner endpoints enforce that session. Customer login is also browser-only in this build, so customer endpoints require server-side authentication before production.

Order-item customization JSON stores structured details (including letter type/message and special requests), while `snapshot_data` stores the SVG customization snapshot and `image_url` stores a source image reference. `order_status_history` records status changes without replacing the current `orders.status`.
