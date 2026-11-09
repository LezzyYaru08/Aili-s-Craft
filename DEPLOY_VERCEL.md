# Deploying AilisCraft on Vercel (Serverless)

Vercel's free tier can host AilisCraft, but it works very differently from normal hosting. Read the "Why things are different" section first — it explains which extra services you must sign up for.

**You will need:**
- `AilisCraft_vercel.zip` (do **not** use the shared-hosting zip)
- A **GitHub** account (free) — Vercel deploys from a GitHub repo
- An **external MySQL database** (Vercel has no built-in MySQL)
- A **Brevo** account (emails)
- A **Cloudinary** account (required — photo uploads cannot work without it on Vercel)

---

## 1. Why things are different (and what breaks if you skip a step)

Vercel runs your PHP as short-lived "serverless functions". Three hard limits shape everything below:

1. **Read-only filesystem.** Your code cannot save files to disk (except a temporary `/tmp` folder that is wiped after each request). → Customer photos for special-request products **must** go to Cloudinary. **Without Cloudinary keys set, checkout with a special-request photo will fail.** (Order snapshots are stored as data URLs inside the database, so they are fine.)
2. **File-based sessions don't persist.** Each request may run on a different server; a session file written on one won't exist on the next. → You **must** set `AC_SESSION_DRIVER=db` so login sessions live in MySQL. If you skip this, you'll be logged out randomly on every page reload.
3. **No local MySQL.** Vercel has no database. → You need an external MySQL provider (below).

Also: the PHP runtime on Vercel is a **community project** (`vercel-php@0.7.3`), not officially supported by Vercel. It works, but it is not guaranteed by Vercel the way Node.js is.

---

## 2. Pick an external MySQL provider

Vercel needs a reachable MySQL database. Compare (all have a web console where you can find host/port/user/password):

| Provider | Cost | Nearest region to PH | Free option? | Notes |
| --- | --- | --- | --- | --- |
| **Clever Cloud** | Free MySQL tier (small); paid tiers from ~€10/mo | **Singapore** | Yes — free MySQL add-on (limited storage/connections) | Best free choice for PH. Create app → add MySQL add-on → "Database" tab shows host, port, user, pass, database name. |
| **Railway** | Trial credit ($5 one-time), then from ~$5/mo | Singapore | Trial credit only (no permanent free tier) | Easy UI. Project → MySQL plugin → "Connect" tab / Variables tab gives `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`. |
| **Aiven** | Free trial (30 days), then from ~$17/mo | Singapore | Trial only | Very reliable. Service → "Overview" tab shows connection details. |
| **PlanetScale** | Paid only (from ~$19/mo) | AWS Singapore (ap-southeast-1) | **No free tier** (ended April 2024) | Note: PlanetScale is MySQL-compatible but uses its own branching system. Only pick it if you already use it. |

**Recommendation for a small PH shop:** **Clever Cloud free tier** (Singapore region = lowest latency from the Philippines). When you outgrow it, move to Railway or Aiven.

**How to get the credentials (all providers):** after creating the database, look in the provider's dashboard for a **"Connect" / "Overview" / "Variables" / "Database"** tab. You need five values: host, port, username, password, database name. Copy them — you'll paste them into Vercel in Step 5.

---

## 3. Create the database schema

Vercel has no phpMyAdmin. Two options:

### Option A — from your own computer (needs the `mysql` command-line client)

On Windows, install [MySQL Shell](https://dev.mysql.com/downloads/shell/) or use the MySQL that comes with XAMPP (it's at `C:\xampp\mysql\bin\mysql.exe`). On Mac, `brew install mysql`. Then run:

```bash
mysql -h YOUR_DB_HOST -P YOUR_DB_PORT -u YOUR_DB_USER -p YOUR_DB_NAME < database/ailiscraft_deploy.sql
```

It will ask for the database password. The file is **additive** (migrations 001–007, never deletes data) — safe to run again if it fails midway.

### Option B — provider console import

Clever Cloud, Railway, and Aiven all offer a web-based database console or an "Import" feature in their dashboard. Upload `database/ailiscraft_deploy.sql` there and run it. (Clever Cloud: "Database" tab → "PhpMyAdmin" link is provided on some plans.)

---

## 4. Push code to GitHub and import into Vercel

1. Create a **private** GitHub repository (name it e.g. `ailiscraft`). **Do not make it public** — it will contain your config files.
2. Unzip `AilisCraft_vercel.zip` and push its contents to that repo (GitHub Desktop is the easiest way if you don't use git commands: File → Add Local Repository → Publish).
3. Go to [vercel.com](https://vercel.com) → **Add New… → Project** → import your GitHub repo.
4. Framework Preset: **Other**. Root Directory: `./` (leave default). Build/Output settings: leave default (the zip's `vercel.json` handles PHP routing). Click **Deploy**.

The first deploy will fail or show errors — that's expected, because the environment variables (next step) aren't set yet.

---

## 5. Set ALL environment variables (Vercel dashboard)

In Vercel: **Project → Settings → Environment Variables**. Add each one below (apply to Production, Preview, and Development unless noted). Click **Save**, then **Redeploy** (Deployments → … → Redeploy).

| Variable | Value | Required? |
| --- | --- | --- |
| `AC_DB_HOST` | your external MySQL host | **Required** |
| `AC_DB_PORT` | your MySQL port (usually 3306) | **Required** |
| `AC_DB_NAME` | your database name | **Required** |
| `AC_DB_USER` | your database user | **Required** |
| `AC_DB_PASS` | your database password | **Required** |
| `AC_APP_URL` | your Vercel domain, e.g. `https://ailiscraft.vercel.app` — **no trailing slash**. After you add a custom domain, update this. | **Required** |
| `AC_SESSION_DRIVER` | `db` (exactly this — not `files`) | **Required** |
| `AC_DEBUG` | `0` | Required (leave 0) |
| `AC_EMAIL_PROVIDER` | `brevo` | Recommended |
| `AC_EMAIL_API_KEY` | Brevo API key | **Required** (password-reset emails need it) |
| `AC_EMAIL_FROM_NAME` | `AilisCraft Shop` | Recommended |
| `AC_EMAIL_FROM_ADDR` | `no-reply@your-domain.com` | Recommended |
| `AC_CLOUDINARY_CLOUD_NAME` | Cloudinary cloud name | **Required** (photos fail without) |
| `AC_CLOUDINARY_API_KEY` | Cloudinary API key | **Required** |
| `AC_CLOUDINARY_API_SECRET` | Cloudinary API secret | **Required** |
| `AC_CLOUDINARY_FOLDER` | `ailiscraft` | Optional |
| `AC_SMS_PROVIDER` | `textbelt` (default) or `semaphore` / `twilio` | Optional |
| `AC_SMS_API_KEY` | TextBelt or Semaphore key | If using SMS |
| `AC_SMS_SENDER` | sender name | Optional |
| `AC_TWILIO_SID` / `AC_TWILIO_TOKEN` / `AC_TWILIO_FROM` | Twilio credentials | If using Twilio |
| `AC_FB_PAGE_ACCESS_TOKEN` / `AC_FB_VERIFY_TOKEN` / `AC_FB_APP_ID` / `AC_FB_PAGE_ID` | Facebook Messenger values | Optional |
| `AC_ENABLE_SOURCE_BROWSER` | `0` | Required (leave off) |
| `AC_ADMIN_SETUP_TOKEN` | a long random string — **only for first deploy**, then remove | One-time |

> **If `AC_SESSION_DRIVER` is not `db`, logins will silently break** — you'll log in and be logged out on the next click. This is the #1 Vercel misstep.

---

## 6. Seed admin/owner, then smoke test

1. With `AC_ADMIN_SETUP_TOKEN` set (Step 5), visit:
   ```
   https://ailiscraft.vercel.app/AilisCraftAdmin/setup_accounts.php?token=YOUR_TOKEN
   ```
   This creates `admin` / `AilisAdmin123` and `Owner` / `AilisCraft123`.
2. **Remove `AC_ADMIN_SETUP_TOKEN`** from Vercel env vars and **delete `setup_accounts.php`** from your GitHub repo (push the deletion; Vercel auto-redeploys).
3. **Smoke test — the critical one:** log in as `Owner`, then **refresh the page several times and click around**. You must stay logged in. If you get logged out, `AC_SESSION_DRIVER` is not `db` or the `ac_sessions` table (migration 007) is missing — re-import the SQL.
4. Register a customer, place a test order **with a special-request photo**. If checkout fails at the photo step, Cloudinary keys are wrong.
5. Trigger a password reset → confirm the email arrives (Brevo).

---

## 7. Honest limitations of the Vercel setup

Read this before choosing Vercel for a live shop:

- **Cold-start latency:** the first request after idle (or after each deploy) takes a few extra seconds while the PHP function boots. Subsequent requests are fast.
- **No scheduled/cron jobs:** anything that needs to run on a timer (e.g. nightly reports) must be triggered externally (e.g. a free cron-job service pinging a URL). AilisCraft core features don't require cron, but be aware.
- **`maxDuration` 10s:** a single request can't run longer than ~10 seconds on the free tier. Large imports or very heavy admin operations may time out.
- **Community PHP runtime (`vercel-php@0.7.3`):** not officially supported by Vercel. If Vercel changes its platform, you may need to update this runtime version.
- **Uploads require Cloudinary** (or any S3-compatible adapter you add yourself). Local file writes outside `/tmp` fail.
- **External MySQL cold-start + latency:** the database is in Singapore (best case); each serverless invocation opens a new connection. Expect slightly slower page loads than shared hosting, and connection limits on free database tiers can be hit under traffic.
- **What works well:** HTTPS is automatic (so the **Facebook Messenger webhook works** — Messenger requires HTTPS, and Vercel gives it to you for free), deploys are instant from GitHub, and the free tier handles low traffic comfortably.

**If you expect steady customer traffic, shared hosting (see `DEPLOY_SHARED_HOSTING.md`) will be faster and simpler.** Use Vercel if you want zero-cost hosting and are comfortable managing three external services (MySQL, Brevo, Cloudinary).

---

## 8. After going live

Run through **[`SECURITY_CHECKLIST.md`](./SECURITY_CHECKLIST.md)** — especially: delete `setup_accounts.php`, change both default passwords, confirm `AC_DEBUG=0`, and rotate any key that was ever visible in a screenshot or shared file.
