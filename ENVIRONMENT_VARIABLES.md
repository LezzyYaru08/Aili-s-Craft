# Environment Variables & Config Reference

## Two ways to configure — pick one

AilisCraft reads settings from **environment variables** first. If a variable is not set, it falls back to a placeholder value written inside a config file.

- **Shared hosting (cPanel, InfinityFree, Hostinger, 000webhost):** most of these hosts do **not** let you set environment variables (InfinityFree has no env editor). **Edit the config-file placeholders directly** — that is the primary method for you. See the "Fallback config file" column below.
- **Vercel / cloud hosts:** set these in the dashboard's Environment Variables section. You do not need to edit any config files.

The four fallback config files (all inside `api/`):

| Config file | Holds placeholders for… |
| --- | --- |
| `api/config.php` | database, app URL, debug, session driver, setup token, source-browser flag |
| `api/email_config.php` | email provider + Brevo key + from address |
| `api/sms_config.php` | SMS provider + keys (TextBelt / Semaphore / Twilio) |
| `api/messenger_config.php` | Facebook Messenger tokens |

**All variables are optional** except where marked "Required" for your platform. On shared hosting, the truly required ones are the database credentials and `AC_APP_URL`; you set them by editing the config files.

---

## Database

| Variable | What it does | Required? | Default | Example | Fallback file |
| --- | --- | --- | --- | --- | --- |
| `AC_DB_HOST` | MySQL server address | Yes (set in config) | `localhost` | `localhost` or `sql123.epizy.com` | `api/config.php` |
| `AC_DB_PORT` | MySQL port | No | `3306` | `3306` | `api/config.php` |
| `AC_DB_NAME` | Database name | Yes | `ailiscraft_db` | `epiz_12345678_ailiscraft_db` | `api/config.php` |
| `AC_DB_USER` | Database username | Yes | (placeholder) | `epiz_12345678` | `api/config.php` |
| `AC_DB_PASS` | Database password | Yes | (placeholder) | `YOUR_DB_PASSWORD` | `api/config.php` |

> On shared hosting, cPanel often prefixes your database and username with your account name (e.g. `myuser_ailiscraft_db`). Copy the exact names cPanel shows you — do not guess.

---

## App behavior

| Variable | What it does | Required? | Default | Example | Fallback file |
| --- | --- | --- | --- | --- | --- |
| `AC_APP_URL` | Your site's full URL, **no trailing slash**. Used for links in emails and redirects. | Yes | (placeholder) | `https://shop.example.com` | `api/config.php` |
| `AC_DEBUG` | `1` shows detailed errors (only for testing); `0` hides them on a live site. | No | `0` | `0` | `api/config.php` |
| `AC_SESSION_DRIVER` | Where login sessions are stored: `files` (normal hosting) or `db` (required on Vercel). | No | `files` | `db` (Vercel only) | `api/config.php` |
| `AC_ENABLE_SOURCE_BROWSER` | A built-in source-code viewer. **Leave off (`0`) on a live site.** | No | `0` | `0` | `api/config.php` |
| `AC_ADMIN_SETUP_TOKEN` | A one-time password that unlocks `setup_accounts.php` on a live server. Set it, use it once, then delete the file and clear this value. | No (one-time) | (unset = locked) | `a-long-random-string-32chars` | `api/config.php` |

---

## Email (Brevo HTTP API)

AilisCraft sends order and password-reset emails through **Brevo's HTTP API** (not SMTP — many cheap hosts block SMTP ports).

| Variable | What it does | Required? | Default | Example | Fallback file |
| --- | --- | --- | --- | --- | --- |
| `AC_EMAIL_PROVIDER` | Which email service to use. | No | `brevo` | `brevo` | `api/email_config.php` |
| `AC_EMAIL_API_KEY` | Your Brevo API key (create free account → SMTP & API → API Keys). | Recommended (password reset needs it) | (placeholder) | `xkeysib-xxxxxxxx…` | `api/email_config.php` |
| `AC_EMAIL_FROM_NAME` | Sender name shown to customers. | No | `AilisCraft` | `AilisCraft Shop` | `api/email_config.php` |
| `AC_EMAIL_FROM_ADDR` | Sender email address. Use a real address on your own domain if you have one. | No | (placeholder) | `no-reply@shop.example.com` | `api/email_config.php` |

---

## SMS (optional)

Order SMS notifications. Defaults to **TextBelt** (free tier, limited per day). In the Philippines, **Semaphore** is the common local choice; **Twilio** works worldwide.

| Variable | What it does | Required? | Default | Example | Fallback file |
| --- | --- | --- | --- | --- | --- |
| `AC_SMS_PROVIDER` | `textbelt`, `semaphore`, or `twilio`. | No | `textbelt` | `semaphore` | `api/sms_config.php` |
| `AC_SMS_API_KEY` | API key for TextBelt or Semaphore. | If using those | (placeholder) | `YOUR_SEMAPHORE_KEY` | `api/sms_config.php` |
| `AC_SMS_SENDER` | Sender name (Semaphore allows an approved sender name). | No | `AilisCraft` | `AilisCraft` | `api/sms_config.php` |
| `AC_TWILIO_SID` | Twilio Account SID. | If using Twilio | (placeholder) | `ACxxxxxxxx…` | `api/sms_config.php` |
| `AC_TWILIO_TOKEN` | Twilio Auth Token. | If using Twilio | (placeholder) | `your_auth_token` | `api/sms_config.php` |
| `AC_TWILIO_FROM` | Twilio phone number (with + country code). | If using Twilio | (placeholder) | `+14155551234` | `api/sms_config.php` |

---

## Facebook Messenger notifications (optional)

Sends order updates to customers via Messenger. Requires a Facebook Page and app.

| Variable | What it does | Required? | Default | Example | Fallback file |
| --- | --- | --- | --- | --- | --- |
| `AC_FB_PAGE_ACCESS_TOKEN` | Long-lived Page access token. | If using Messenger | (placeholder) | `EAAxxxxx…` | `api/messenger_config.php` |
| `AC_FB_VERIFY_TOKEN` | A string you invent, used when Facebook verifies your webhook. | If using Messenger | (placeholder) | `make-up-a-random-word` | `api/messenger_config.php` |
| `AC_FB_APP_ID` | Facebook App ID. | If using Messenger | (placeholder) | `1234567890` | `api/messenger_config.php` |
| `AC_FB_PAGE_ID` | Your Facebook Page ID. | If using Messenger | (placeholder) | `9876543210` | `api/messenger_config.php` |

---

## Cloudinary (required on Vercel for photo uploads)

Special-request product photos are uploaded to Cloudinary. **On normal shared hosting you can leave this blank** (files save locally). **On Vercel it is required** — without it, checkout with a special-request photo will fail.

| Variable | What it does | Required? | Default | Example | Fallback file |
| --- | --- | --- | --- | --- | --- |
| `AC_CLOUDINARY_CLOUD_NAME` | Your Cloudinary cloud name. | Required on Vercel | (placeholder) | `mycloud` | `api/config.php` |
| `AC_CLOUDINARY_API_KEY` | Cloudinary API key. | Required on Vercel | (placeholder) | `123456789012345` | `api/config.php` |
| `AC_CLOUDINARY_API_SECRET` | Cloudinary API secret. | Required on Vercel | (placeholder) | `xxxxxxxxxxxxxxxx` | `api/config.php` |
| `AC_CLOUDINARY_FOLDER` | Folder inside Cloudinary where photos are stored. | No | `ailiscraft` | `ailiscraft_prod` | `api/config.php` |

---

## Quick fill-in checklist (shared hosting)

If you are editing config files directly, these are the only values you **must** change:

1. `api/config.php` — `AC_DB_HOST`, `AC_DB_NAME`, `AC_DB_USER`, `AC_DB_PASS`, `AC_APP_URL`
2. `api/email_config.php` — `AC_EMAIL_API_KEY` (Brevo), `AC_EMAIL_FROM_ADDR`
3. Everything else can stay as default until you need it.
