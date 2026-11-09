# Aili's Craft — Free Email + SMS Setup (2 minutes)

Both password-reset emails and order-status SMS now work with **free, no-credit-card**
services. They use simple HTTP POSTs, so they work on XAMPP/localhost too — no local
SMTP server needed. Logs are ALWAYS kept as a record, even when real sending is on.

---

## 1. Password-reset email (free 300/day) — Brevo

File to edit: `AilisCraftUser/api/email_config.php`

1. Sign up free at **https://app.brevo.com** (no credit card).
2. Top menu → **SMTP & API** → **API Keys** → *Generate a new API key* → copy it.
3. (Recommended) **Senders** → *Add a sender* → enter your email → click the
   verification link in your inbox. This lets you send FROM your own address.
4. Paste the key into `email_config.php`:
   ```php
   define('EMAIL_API_KEY', '<your-brevo-v3-api-key>');
   define('EMAIL_FROM_ADDR', 'you@yourdomain.com');  // your verified sender
   ```
5. Done. Reset links now arrive in the user's inbox. Every link is also recorded in
   `AilisCraftUser/uploads/reset_links.log`.

**Alternative — Resend** (free 100/day): sign up at https://resend.com, set
`EMAIL_PROVIDER='resend'`, paste the key. Note: before verifying a domain (free DNS
TXT record), Resend can only send to your own account email.

**No key?** Leave `EMAIL_API_KEY` empty → log-only mode (link still written to
`reset_links.log`, no real email sent) — perfect for testing.

---

## 2. Order-status SMS (free, no signup) — TextBelt

File to edit: `AilisCraftUser/api/sms_config.php`

**It already works out of the box** — default is `textbelt` with key `textbelt`:
- Free tier: **1 SMS per day per server IP**, to any number (Philippines supported).
- No account, no credit card, nothing to sign up for.
- Every SMS is recorded in `AilisCraftUser/uploads/sms_log.log` + the `sms_log` DB table.

**Need more than 1/day?** Buy a pay-as-you-go TextBelt key at https://textbelt.com
(cheap, ~$0.01–0.02/SMS) and replace `'textbelt'` with your key. Volume jumps to
hundreds/day instantly.

**Alternative — Twilio free trial:** sign up at https://twilio.com/try-twilio (free
trial credit), set `SMS_PROVIDER='twilio'`, and fill `TWILIO_SID`, `TWILIO_TOKEN`,
`TWILIO_FROM`. Trial can only SMS numbers you verify in the Twilio console.

**PH-best paid option (kept):** Semaphore — `SMS_PROVIDER='semaphore'` + API key
from https://semaphore.co (~PHP 0.40/SMS, fastest PH delivery).

---

## What was changed (all additive / backward compatible)

| File | Change |
|---|---|
| `AilisCraftUser/api/email_config.php` | **NEW** — email provider + API key config |
| `AilisCraftUser/api/auth.php` | `ac_send_reset_email()` now uses Brevo/Resend/mail fallbacks; log always kept |
| `AilisCraftUser/api/sms_config.php` | Rewritten — TextBelt (default free) / Twilio / Semaphore / logonly |
| `AilisCraftUser/api/sms.php` | Rewritten — multi-provider; `ac_send_sms()` signature unchanged |
| `AilisCraftUser/style.css` | Merged `style.css` + `styles.css` into one file (main designs preserved) |
| 16× `AilisCraftUser/*.php|html` | CSS link updated `styles.css` → `style.css` |

Test: request a password reset → check inbox AND `uploads/reset_links.log`.
Change an order status in the Owner Dashboard → customer gets SMS AND check
`uploads/sms_log.log`.
