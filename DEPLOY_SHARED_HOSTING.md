# Deploying AilisCraft on Shared Hosting (cPanel)

This guide is for **InfinityFree, Hostinger, 000webhost, Bluehost, or any host with cPanel**. No command line needed — everything is done by clicking in your browser.

**You will need:**
- `AilisCraft_shared_hosting.zip` (do **not** use the Vercel zip)
- A free [Brevo](https://www.brevo.com) account (for emails; SMTP ports are blocked on many cheap hosts)
- About 30 minutes

---

## Step 1 — Unzip the zip on your computer

1. Right-click `AilisCraft_shared_hosting.zip` → **Extract All** (Windows) or double-click (Mac).
2. Open the extracted folder. You should see three folders named `AilisCraftUser`, `AilisCraftOwner`, `AilisCraftAdmin`, plus `api`, `database`, and `.htaccess`.
3. **If those items are inside one extra wrapper folder** (e.g. `AilisCraft_shared_hosting/AilisCraftUser/...`), open that wrapper and select the items inside it for upload in Step 4.

---

## Step 2 — Create your MySQL database (cPanel MySQL Wizard)

1. Log into your hosting **cPanel** (your host emailed you the link, usually `https://your-site.com/cpanel`).
2. Find **MySQL® Database Wizard** (under "Databases") and click it.
3. **Step 1: Create a Database.** Enter `ailiscraft_db` → cPanel will prefix it, e.g. `youraccount_ailiscraft_db`. **Write down the full name exactly as shown.** Click Next Step.
4. **Step 2: Create Database Users.** Username: enter `ailiscraft` (becomes `youraccount_ailiscraft`). Password: click **Password Generator**, save it somewhere safe (this is `YOUR_DB_PASSWORD`). **Write down the full username and password.** Click Create User.
5. **Step 3: Add user to database.** Tick **ALL PRIVILEGES** (or at minimum: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX). Click Next Step / Make Changes.

> **Write these down now — you need them in Step 5:**
> - DB host: usually `localhost` (InfinityFree: `sqlXXX.epizy.com`, shown in your client area)
> - DB name: `__________`
> - DB user: `__________`
> - DB pass: `__________`

**InfinityFree note:** InfinityFree has no SSH and no remote MySQL by default — that's fine, you do everything through their online control panel (VistaPanel) and phpMyAdmin. Your MySQL host is shown under "Account Details".

---

## Step 3 — Import the database (phpMyAdmin)

1. In cPanel, open **phpMyAdmin** (under "Databases").
2. On the left, click your database name (e.g. `youraccount_ailiscraft_db`).
3. Click the **Import** tab at the top.
4. Click **Choose File** → select `database/ailiscraft_deploy.sql` from the extracted zip.
5. Leave all settings default → click **Go** (bottom of page).
6. You should see "Import has been successfully finished".

> **This file is additive and safe to re-run.** It creates tables and applies migrations 001–007 only if they are missing. It never deletes orders, customers, or products. If the import fails partway, fix the issue and import again — no harm done.

---

## Step 4 — Fill in your settings (edit the config files)

**On shared hosting, edit the config files directly** — most hosts (including InfinityFree) have no environment-variable editor. If your host *does* support env vars (some premium cPanel plans have an "Environment Variables" or Node.js app section), you may set them there instead and skip editing. Either way, the values are the same.

Open these four files in a plain text editor (Notepad on Windows, TextEdit on Mac — **not** Word):

### 4a. `api/config.php` — fill in:

```
AC_DB_HOST   = localhost          (or your host's SQL server, e.g. sql123.epizy.com)
AC_DB_PORT   = 3306               (leave as-is unless your host says otherwise)
AC_DB_NAME   = youraccount_ailiscraft_db
AC_DB_USER   = youraccount_ailiscraft
AC_DB_PASS   = YOUR_DB_PASSWORD
AC_APP_URL   = https://your-site.com     ← NO trailing slash, use https once SSL is on
AC_DEBUG     = 0                    (leave 0 on a live site)
AC_SESSION_DRIVER = files           (leave as "files" on shared hosting)
```

> **If you get `AC_APP_URL` wrong (trailing slash, or http instead of https), login redirects and email links will break. Double-check it.**

### 4b. `api/email_config.php` — fill in:

```
AC_EMAIL_PROVIDER   = brevo
AC_EMAIL_API_KEY    = your Brevo API key  (Brevo dashboard → SMTP & API → API Keys → Generate)
AC_EMAIL_FROM_NAME  = AilisCraft Shop
AC_EMAIL_FROM_ADDR  = no-reply@your-site.com
```

**Why Brevo (HTTP API) and not normal SMTP?** InfinityFree, 000webhost, and many budget hosts block SMTP ports (25/465/587). Brevo's HTTP API sends email over the normal web port (443), which is never blocked. Brevo's free tier sends 300 emails/day — enough for a small shop.

### 4c. `api/sms_config.php` — optional. Leave as default (TextBelt, free but limited) unless you want reliable PH SMS. For the Philippines, **Semaphore** is recommended:

```
AC_SMS_PROVIDER = semaphore
AC_SMS_API_KEY  = your Semaphore API key
AC_SMS_SENDER   = AilisCraft (your approved sender name)
```

### 4d. `api/messenger_config.php` — optional. Leave blank if you don't use Facebook Messenger notifications.

Save all four files.

---

## Step 5 — Upload via cPanel File Manager

1. In cPanel, open **File Manager**.
2. Navigate to **`public_html`** (this is your website's root folder).
3. Click **Upload** → choose `AilisCraft_shared_hosting.zip` → wait for the green "Finished".
4. Go back to File Manager (the tab says "Back to…"), find the zip inside `public_html`, **right-click → Extract**. Extract into `public_html` (the default path shown should end in `/public_html` — confirm that).
5. **Critical check:** after extracting, `public_html` should contain `AilisCraftUser/`, `AilisCraftOwner/`, `AilisCraftAdmin/`, `api/`, `database/`, `.htaccess` **directly**. If instead you see a single folder (e.g. `AilisCraft_shared_hosting/`) containing them, open that folder, select everything inside, and **Move** it up one level into `public_html`.

> **The three panels must be siblings directly in `public_html`.** Owner and Admin logins call `../AilisCraftUser/api/auth.php`. If the folders are nested one level too deep, **owner and admin login will fail** with a 404.

6. Set folder permissions: right-click `AilisCraftUser/uploads` → **Permissions** → set to **755** (or 775 if 755 doesn't allow uploads). Do the same for `AilisCraftUser/logs`. **Never use 777.**

---

## Step 6 — Create the admin and owner accounts (one-time seed)

The seed script is locked to localhost by default. To run it on your live server, you need a token.

1. In `api/config.php`, set `AC_ADMIN_SETUP_TOKEN` to a long random string, e.g. `k9x2m7q4r8t1v6w3z5y0u7i2o9p4a6s8`. Save & re-upload (or edit it in File Manager).
2. In your browser, visit:
   ```
   https://your-site.com/AilisCraftAdmin/setup_accounts.php?token=k9x2m7q4r8t1v6w3z5y0u7i2o9p4a6s8
   ```
   (replace the token with yours). You should see a success message. This creates:
   - **Admin:** username `admin`, password `AilisAdmin123`
   - **Owner:** username `Owner`, password `AilisCraft123`
3. **Immediately delete `AilisCraftAdmin/setup_accounts.php`** (File Manager → right-click → Delete). Then go back to `api/config.php` and **clear `AC_ADMIN_SETUP_TOKEN`** (set it back to empty).

> **If the token page fails (SQL fallback):** In phpMyAdmin → your database → SQL tab, run the statement below. **Adjust the table/column names to match your `users` table** (click the `users` table → Structure tab to see real column names; common names are `username, password, role, email, created_at`).
>
> ```sql
> INSERT INTO users (username, password, role, email, created_at) VALUES
> ('admin', '$2b$10$.lf.ZqCHFUXRrpDe75fOwuQlRajg.rbTDT5mLeZp3cmcZRY4.5K5e', 'admin', 'admin@your-site.com', NOW()),
> ('Owner', '$2b$10$sOnSNLwbOImnVZ5ZC/lwZ.Pu8heijCZwAvaNfOYR3bNeWHcSNu/I6', 'owner', 'owner@your-site.com', NOW());
> ```
>
> Then log in with `admin` / `AilisAdmin123` and `Owner` / `AilisCraft123` — and **change both passwords right away**.

---

## Step 7 — Smoke test (does it work?)

1. **Customer:** open `https://your-site.com/AilisCraftUser/` → register a test account → browse → place a test order (with a special-request photo if you use that feature).
2. **Owner:** open `https://your-site.com/AilisCraftOwner/` → log in with `Owner` → confirm the test order appears.
3. **Admin:** open `https://your-site.com/AilisCraftAdmin/` → log in with `admin`.
4. **Email:** from the shop, trigger a password reset (or place an order) → confirm the email arrives. If not, re-check the Brevo key and `AC_EMAIL_FROM_ADDR` in Step 4b.
5. **SMS (if enabled):** confirm an order SMS arrives on your phone.

If owner/admin login shows a 404 or "file not found", re-check Step 5 — the panels are probably nested in an extra folder.

---

## Step 8 — Enable free SSL and force HTTPS

1. **Get a free certificate:**
   - **InfinityFree:** client area → your account → **Free SSL** (Let's Encrypt) → follow the steps (they give you CNAME records to add; wait ~1 hour, then click "Install SSL"). InfinityFree also gives you a free subdomain (`youraccount.infinityfreeapp.com`, `.epizy.com`, or `.rf.gd`) if you don't have a domain yet.
   - **cPanel hosts (Hostinger, etc.):** cPanel → **SSL/TLS Status** (or "Let's Encrypt SSL") → select your domain → **Run AutoSSL** / Issue.
2. Wait until `https://your-site.com` loads without a browser warning.
3. **Then** edit `.htaccess` in `public_html` (File Manager → Settings → tick "Show Hidden Files" to see it). Find the commented HTTPS redirect block (lines starting with `#`) and **remove the `#` marks** to activate it. Save.
4. Test: type `http://your-site.com` — it must automatically jump to `https://`.

---

## Step 9 — Final security

Go through every item in **[`SECURITY_CHECKLIST.md`](./SECURITY_CHECKLIST.md)**. The three most important: delete `setup_accounts.php`, change both default passwords, confirm `AC_DEBUG=0`.

---

## Troubleshooting quick reference

| Symptom | Likely cause | Fix |
| --- | --- | --- |
| "Database connection error" | Wrong DB name/user/pass/host | Re-check Step 2 values in `api/config.php`; cPanel prefixes names with your account |
| Owner/Admin login → 404 | Panels nested in extra folder | Re-check Step 5; move folders up to `public_html` |
| Photos don't save / order fails at checkout | `uploads/` not writable | Set `AilisCraftUser/uploads` to 755/775 (Step 5.6) |
| No emails arrive | Brevo key wrong, or SMTP (not HTTP API) chosen | Use Brevo HTTP API; verify key in Brevo dashboard |
| `setup_accounts.php` says "invalid token" | Token mismatch or not set in config | Re-check `AC_ADMIN_SETUP_TOKEN` in `api/config.php` and in the URL |
| Pages show raw PHP code / download instead of rendering | Wrong zip used, or PHP not enabled | You uploaded the Vercel zip; re-upload `AilisCraft_shared_hosting.zip` |
