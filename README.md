# AilisCraft — Deployment Quick Start

AilisCraft is a PHP + MySQL online shop with three panels that live side-by-side under one website folder:

| Folder | Who uses it | Address after upload |
| --- | --- | --- |
| `AilisCraftUser/` | Your customers (the shop) | `https://your-site.com/AilisCraftUser/` |
| `AilisCraftOwner/` | You (store owner) | `https://your-site.com/AilisCraftOwner/` |
| `AilisCraftAdmin/` | System administrator | `https://your-site.com/AilisCraftAdmin/` |

---

## 1. Which zip do I download?

| If you plan to host on… | Use this zip |
| --- | --- |
| Normal paid/free web hosting (cPanel: InfinityFree, Hostinger, 000webhost, Bluehost, etc.) | **`AilisCraft_shared_hosting.zip`** |
| Vercel (serverless, free tier) | **`AilisCraft_vercel.zip`** |

**Do not mix them.** The Vercel zip contains extra serverless config files that will break on normal hosting, and vice versa.

---

## 2. What's inside the zip

- The three panel folders (`AilisCraftUser/`, `AilisCraftOwner/`, `AilisCraftAdmin/`)
- `api/` — shared config files (database, email, SMS, Messenger). You will edit placeholders here.
- `database/ailiscraft_deploy.sql` — the database file you import once. It is **additive** (safe to run again; it never deletes your data).
- `.htaccess` — URL rules (includes an HTTPS redirect you turn on later).

---

## 3. Three-step quick start (shared hosting)

1. **Upload & extract** the zip into `public_html` so the three panel folders sit directly inside it (not inside an extra folder).
2. **Create a MySQL database** (cPanel → MySQL Wizard) and import `database/ailiscraft_deploy.sql` (cPanel → phpMyAdmin → Import).
3. **Fill in your settings** in the four `api/` config files (database name/user/password, your site URL, Brevo email key). Then open `https://your-site.com/AilisCraftAdmin/setup_accounts.php?token=YOURTOKEN` once to create the admin and owner accounts, then **delete that file**.

That's it. Then run through the **Security Checklist**.

---

## 4. Full guides

- **Shared hosting (cPanel / InfinityFree / Hostinger / 000webhost):** see [`DEPLOY_SHARED_HOSTING.md`](./DEPLOY_SHARED_HOSTING.md)
- **Vercel (serverless):** see [`DEPLOY_VERCEL.md`](./DEPLOY_VERCEL.md)
- **After going live, do every item in:** [`SECURITY_CHECKLIST.md`](./SECURITY_CHECKLIST.md)
- **Reference for all settings:** [`ENVIRONMENT_VARIABLES.md`](./ENVIRONMENT_VARIABLES.md) (shared-hosting users can ignore env vars and edit the config files directly — that guide explains both.)

> **Timezone note:** the app uses Asia/Manila (Philippine time). No action needed from you.
