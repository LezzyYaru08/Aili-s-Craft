# Aili's Craft — Deployment-Hardening Changes (CHANGES_DEPLOY.md)

Working copy: `deploy_ready/work/base/`. Read-only sources were never modified.
All identifiers are frozen (env vars, constants, migration numbers, filenames).
The three panels (`AilisCraftUser` / `AilisCraftOwner` / `AilisCraftAdmin`) remain
**sibling directories** — cross-panel API calls use `../AilisCraftUser/api/...`
relative paths and were NOT flattened.

---

## 1. Secrets scrub + env coverage
- **Real Brevo key `xkeysib-e6e0c716…` removed** from `AilisCraftUser/api/email_config.php`; replaced with `ac_env('AC_EMAIL_API_KEY', '')`.
- `email_config.php`, `sms_config.php`, new `messenger_config.php` now read **all** values from env via `ac_env()` with placeholder/empty defaults.
- Default `SMS_PROVIDER='textbelt'` / `SMS_API_KEY='textbelt'` kept (public free key).
- Each `*_config.php` now starts with `require_once __DIR__.'/config.php';` so `ac_env()` is always available.
- Env vars wired: `AC_EMAIL_PROVIDER`, `AC_EMAIL_API_KEY`, `AC_EMAIL_FROM_NAME`, `AC_EMAIL_FROM_ADDR`, `AC_SMS_PROVIDER`, `AC_SMS_API_KEY`, `AC_SMS_SENDER`, `AC_TWILIO_SID`, `AC_TWILIO_TOKEN`, `AC_TWILIO_FROM`, `AC_FB_PAGE_ACCESS_TOKEN`, `AC_FB_VERIFY_TOKEN`, `AC_FB_APP_ID`, `AC_FB_PAGE_ID`, `AC_CLOUDINARY_CLOUD_NAME/_API_KEY/_API_SECRET/_FOLDER`.
- `FREE_EMAIL_SMS_SETUP.md` example key (`xkeysib-xxxx…`) replaced with a generic placeholder so `grep xkeysib-` is clean.

## 2. `api/config.php` (applied identically to all 3 panels — byte-identical after edit)
- Added `APP_URL` define: `AC_APP_URL` (trimmed of trailing slash) else auto-detect `scheme://HTTP_HOST`.
- Added `AC_ENABLE_SOURCE_BROWSER` bool define (env, default `0`).
- Added `AC_ADMIN_SETUP_TOKEN` define (env, default `''`).
- Added `AC_SESSION_DRIVER` define (env, default `'files'`).
- Session-cookie hardening via `ini_set`: `use_strict_mode=1`, `cookie_httponly=1`, `cookie_samesite=Lax`, `cookie_secure=1` iff `APP_URL` is `https://`.
- If `AC_SESSION_DRIVER==='db'`: `@include_once session_db.php` + `session_set_save_handler(new AC_DBSessionHandler(), true)` **before any `session_start`**, wrapped in try/catch so a DB/file failure falls back silently to files.

## 3. Logs protection
- Created `AilisCraftUser/logs/` with `.htaccess` (`Require all denied` + `Options -Indexes`) and empty `index.html`.
- `api/auth.php` reset-link log: `uploads/reset_links.log` → `logs/reset_links.log`.
- `api/sms.php` log: `uploads/sms_log.log` → `logs/sms_log.log`.
- `api/messenger.php` log: `uploads/messenger_log.log` → `logs/messenger_log.log`.
- **Deleted** `uploads/reset_links.log` and `uploads/sms_log.log` (contained real reset tokens).
- `uploads/.htaccess`: added `FilesMatch` deny for `.log/.ini/.sql/.md/.env/.bak` (existing PHP-execution disable rules kept).

## 4. Admin source browser + setup gating
- `AilisCraftAdmin/source-code.php`: now `require_once api/config.php` and returns **403** when `!AC_ENABLE_SOURCE_BROWSER` (default off).
- Removed the only UI entry point to the Source Code page: the "Link to Source Code" button in `AilisCraftAdmin/script.js` `renderMaintenance()` (no nav button existed in `index.php`). Endpoint still 403s regardless.
- `AilisCraftAdmin/setup_accounts.php`: kept localhost guard, **added token gate** — if `AC_ADMIN_SETUP_TOKEN` non-empty, a valid `?token=` (timing-safe `hash_equals`) is required and bypasses the localhost guard (production seeding possible); wrong/missing token → 403. Added a large `DELETE THIS FILE AFTER DEPLOY` comment banner.

## 5. Messenger port (was not in fresh)
- Added `AilisCraftUser/api/messenger.php` (sender + webhook; never throws).
- Added env-driven `AilisCraftUser/api/messenger_config.php`.
- Patched `AilisCraftUser/api/orders.php`: `require_once messenger.php` at top; added messenger-send block in the status-update loop (SELECTs `messenger_psid`, calls `ac_send_messenger($psid, ac_sms_template($status,$code))`, wrapped in try/catch) — exact lines from the messenger-notification branch.
- Ported Send-to-Messenger button into `AilisCraftUser/order-success.php`: `#fb-root`, opt-in div, FB SDK. **Preserved fresh-only `style.css`** (messenger branch's `styles.css` typo rejected). `FB_APP_ID`/`FB_PAGE_ID`/`FB_API_VERSION` are echoed from env constants and the whole block is gated to render only when both IDs are set (graceful no-op otherwise).
- Added migration `006_messenger_psid.sql`: additive + idempotent via `information_schema` + `PREPARE/EXECUTE` (MySQL 5.7+/8/MariaDB), adds `orders.messenger_psid VARCHAR(50) NULL`.

## 6. DB sessions adapter
- New `AilisCraftUser/api/session_db.php` (copied to all 3 panels' `api/` so the identical config.php include resolves): `AC_DBSessionHandler implements SessionHandlerInterface` against `ac_sessions`.
  - `read` returns `''` when missing/expired; `write` uses `INSERT … ON DUPLICATE KEY UPDATE` with `expires_at = now + gc_maxlifetime`; `gc` deletes expired; `destroy` removes row.
  - **Never throws** — all PDO errors caught + `error_log`, degrades to empty session.
- Migration `007_sessions.sql`: `CREATE TABLE IF NOT EXISTS ac_sessions` (`id VARCHAR(128) PK`, `data MEDIUMTEXT`, `created_at`/`updated_at` TIMESTAMP, `expires_at INT UNSIGNED` indexed). Default driver stays `files` so XAMPP/shared hosting unchanged.

## 7. Upload storage adapter
- New `AilisCraftUser/api/upload_store.php`: `ac_store_image(string $tmpPath, string $ext, string $mime): string`.
  - If `AC_CLOUDINARY_CLOUD_NAME` + `_API_KEY` + `_API_SECRET` all set → signed upload to Cloudinary REST API (`timestamp` + `sha1` signature, multipart POST via `stream_context`, optional `folder=AC_CLOUDINARY_FOLDER`), returns `secure_url`. Failure throws `ApiError` cleanly.
  - Else local fallback: `move_uploaded_file` to `uploads/special_requests/` with `uniqid('sr_', true)` name, returns the same relative path as before (byte-identical behavior).
- Refactored `upload_special_request.php` to call `ac_store_image()` **after** all existing validation (mime/size/`is_uploaded_file`/finfo/ext checks preserved). Local-dir writability gate is skipped when Cloudinary env is set (read-only hosts like Vercel).

## 8. Root security files (in `base/`, parent of the 3 panels)
- `.htaccess`: `Options -Indexes`, `ServerSignature Off`, security headers (`X-Content-Type-Options nosniff`, `X-Frame-Options SAMEORIGIN`, `Referrer-Policy strict-origin-when-cross-origin`, `Permissions-Policy`), `FilesMatch` deny for `log/ini/sql/md/env/bak/sh/phar/zip/tar/gz` + dotfiles, and a **commented-out** HTTPS redirect block.
- `.user.ini` (shared hosting): `display_errors=Off`, `log_errors=On`, `html_errors=Off`, `upload_max_filesize=8M`, `post_max_size=10M`, `memory_limit=256M`, `session.use_strict_mode=1`, `session.cookie_httponly=1`, `session.cookie_samesite="Lax"`, `cookie_secure` commented.
- `vercel.user.ini` (Vercel): same + `session.save_path="/tmp"`.
- `.env.example`: every env var with placeholder value + one-line comment.

## 9. Vercel routing
- `vercel.json`: runtime `vercel-php@0.7.3`, `functions **/*.php` memory 256 / maxDuration 10, route `/` → `/AilisCraftUser/index.php`, pass-through everything else. 3-panel sibling structure preserved.

## 10. Junk removed
- Deleted: `*.bak` (`AilisCraftAdmin/index.html.bak`, `script.js.bak`), all `.kilo/` dirs, `AilisCraftOwner/.vscode/`, `AilisCraftUser/.hintrc`. Kept all `.md` notes and `ailiscraft_database_schema.sql`.

---

## Validation results
- `grep -rn "xkeysib-" base/` → **0 hits** (real Brevo key `xkeysib-e6e0c716…` gone).
- `grep -rn "localhost" --include=*.php --include=*.js base/` → only allowed hits (comments; `setup_accounts.php` localhost guard list/HTML; config.php auto-detect `?? 'localhost'` CLI fallback — 3 copies, intentional, reported below). `auth.php` no longer has a localhost fallback (now uses `APP_URL`).
- **`php -l` unavailable** (no php binary in PATH, `/tmp/php` and `/tmp/static-php` absent). Ran a Python brace/paren/bracket-balance check on all 43 `.php` files: 42 clean; `AilisCraftUser/history.php` flagged as a **false positive** (regex literal `/[&<>"']/g` in inline JS confused the crude string stripper; raw counts `()[]{}` are perfectly balanced and the file is byte-identical to untouched fresh).
- `node --check` on all 8 `.js` files (including the modified `AilisCraftAdmin/script.js`) → **all pass**.
- Preserved-fixes audit (all confirmed present): "Out for Delivery" status in `orders.php` + `AilisCraftOwner/script.js`; `object-fit:contain` in `AilisCraftOwner/style.css`; Brevo HTTP-API path `api.brevo.com/v3/smtp/email` in `auth.php`; Semaphore provider option in `sms_config.php`/`sms.php`; snapshot data-URL fallback chain in `orders.php` untouched.

## Notes / intentional deviations
- `session_db.php` is present in all 3 panels' `api/` (frozen spec names it under `AilisCraftUser/api/`; copies keep the 3 `config.php` byte-identical and make `AC_SESSION_DRIVER=db` actually work from any panel).
- `config.php` APP_URL auto-detect retains `?? 'localhost'` for CLI/migration contexts where `HTTP_HOST` is absent — the only non-comment, non-setup-guard `localhost` code hit; it is never used in production web requests.
- `order-success.php` Messenger block is gated on `FB_APP_ID` + `FB_PAGE_ID` being non-empty (env), so on a default deploy it renders nothing — same graceful no-op as the messenger branch, but without emitting broken SDK markup.
