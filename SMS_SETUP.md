# Aili's Craft — SMS Order Status Notifications

Sends an SMS to the customer's contact number (entered in order-summary.php)
automatically when the owner changes an order's status in the Owner Dashboard.

## 4 notification stages (with SMS templates)
| Status | SMS sent to customer |
|---|---|
| **Preparing** | "Your order {code} is now being prepared. We'll text you once it ships." |
| **Shipped** | "Your order {code} has shipped and is with our courier." |
| **Out for Delivery** (new) | "Your order {code} is OUT FOR DELIVERY today! Keep your line open." |
| **Delivered** | "Your order {code} has been delivered. Thank you for shopping with us!" |

Cancelled orders do NOT trigger SMS. A new "Out for Delivery" status was added
to the owner dropdown, buttons, validation, and CSS (between Shipped and Delivered).

## Files (6)
| File | Where it goes | Purpose |
|---|---|---|
| `AilisCraftUser/api/sms_config.php` | `AilisCraftUser/api/` | **Edit this** — paste your Semaphore API key |
| `AilisCraftUser/api/sms.php` | `AilisCraftUser/api/` | SMS sender (Semaphore) + PH phone normalization + audit logging |
| `AilisCraftUser/api/orders.php` | `AilisCraftUser/api/` | Patched — triggers SMS on status change (overwrite) |
| `AilisCraftUser/database/migrations/004_sms_log.sql` | Import to DB | `sms_log` audit table |
| `AilisCraftOwner/script.js` | `AilisCraftOwner/` | Patched — adds "Out for Delivery" status (overwrite) |
| `AilisCraftOwner/style.css` | `AilisCraftOwner/` | Adds `.status.out-for-delivery` color (overwrite) |

## Setup (3 minutes)
1. Import `004_sms_log.sql` into `ailiscraft_db` (phpMyAdmin → Import). Additive only.
2. Drop the two `api/` files into `AilisCraftUser/api/`. Overwrite `orders.php`.
3. Overwrite `AilisCraftOwner/script.js` and `style.css`.
4. **Sign up at https://semaphore.co** (Philippines — fastest, most reliable, ~PHP 0.40/SMS). Load credits via GCash/card. Copy your API key from the API Keys tab.
5. Paste it into `api/sms_config.php`: `define('SMS_API_KEY', 'your_key_here');`

## Testing without buying credits
Leave `SMS_API_KEY` empty → **log-only mode**: no real SMS is sent, but every
message is written to `AilisCraftUser/uploads/sms_log.log` and the `sms_log` DB
table. Change an order's status in the Owner Dashboard, then open
`uploads/sms_log.log` — you'll see the exact SMS that would have been sent.

## How it works
Owner changes status → `orders.php?action=owner-status` updates the DB →
after commit, `ac_send_sms()` is called with the matching template →
Semaphore API delivers the SMS. Every attempt is logged to `sms_log`
(order_id, phone, status, message, provider, sent, error). SMS failure never
breaks the status update — it's wrapped in try/catch and logged.

## Troubleshooting
- SMS not sending? Check `uploads/sms_log.log` — last line shows the exact error.
- `Invalid phone number` → customer entered a non-PH number; check `contact_number` in `orders` table.
- Semaphore `apikey` error → wrong/empty key, or no credits loaded.
- Want Twilio/Clickatell instead? Swap the URL + payload in `sms.php` (the `ac_send_sms` function is isolated).
