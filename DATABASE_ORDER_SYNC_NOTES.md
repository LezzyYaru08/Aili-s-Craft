# AilisCraft database order synchronization update

- New checkouts are posted to `AilisCraftUser/api/orders.php?action=create`; the customer history is reloaded from MySQL.
- Owner orders are loaded from `?action=owner-list`; owner status changes use `?action=owner-status`.
- `order_items.snapshot_data` stores the SVG customization preview, `image_url` stores the source image reference, and `customization_json` stores selected options, special requests, and letter details.
- `order_status_history` audits customer and owner status changes.
- Existing databases: back up first, then run `AilisCraftUser/database/migrations/002_order_sync_details.sql`.
- New databases: use the updated `ailiscraft_database_schema.sql`. **It drops/recreates existing tables; never import it over live data.**
- Serve via Apache/PHP and configure `AilisCraftUser/api/config.php` for your database.
- Authentication limitation: the prototype uses browser-side customer/owner login. Do not deploy publicly until server-side sessions and owner API authorization are implemented.
