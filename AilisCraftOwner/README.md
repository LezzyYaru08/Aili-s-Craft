# Aili's Craft — Owner Dashboard

Updated to match the Aili's Craft customer/user system supplied with this project.

## Included
- Working front-end Owner login
- Aili's Craft pink visual theme (Sniglet + Quicksand)
- Product catalog synchronized with the customer system
- Actual product images from `assets/images/orders/`
- Existing dashboard, order, customer, payment, review, report, settings, search, export, modal, and localStorage features retained

## Owner login
- Username: `Owner`
- Password: `AilisCraft123`
- `Owner@ailiscraft.com` is also accepted as the username.

## How to run
1. Extract the ZIP.
2. Open `login.html`.
3. Sign in using the demo Owneristrator account above.
4. The dashboard will then open.

## Important
This is still a browser-only front-end prototype. The login is client-side and is not secure for production because credentials are stored in JavaScript. For a production thesis deployment, connect authentication and data to a PHP/MySQL backend, hash passwords server-side, validate sessions on the server, and protect Owner routes.


## Database-backed order management
The owner order list now synchronizes with `../AilisCraftUser/api/orders.php` and the same `ailiscraft_db` database used by checkout. Apply `AilisCraftUser/database/migrations/002_order_sync_details.sql` to an existing database (or import the updated root schema into a new database). The order detail modal shows saved customization text, letter messages, special requests, delivery/payment details, and saved SVG snapshots where present. Status changes are written to MySQL and are visible on the customer's next history refresh.

**Security note:** this prototype owner login remains browser-side. Before public deployment, add PHP-session owner authentication and enforce it on the owner API endpoints.
