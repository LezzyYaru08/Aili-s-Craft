# Aili's Craft — Forgot Password Fix (working self-service reset)

## What was broken
`AilisCraftUser/forgot-password.php` was a UI stub only — its form had
`onsubmit="event.preventDefault(); ACToast('Reset link sent…'); this.reset();"`
so it never sent anything. No backend, no tokens, no email, no reset page existed.

## What this fix adds (4 files)
| File | Action |
|---|---|
| `AilisCraftUser/database/migrations/003_password_resets.sql` | **Run first** — creates `password_resets` table (email, sha256(token), expires_at) |
| `AilisCraftUser/api/auth.php` | Adds 3 actions: `forgot` (request link), `verify-reset-token` (validate link), `reset` (set new password). All existing actions (register/login/staff-login/logout) are unchanged. |
| `AilisCraftUser/forgot-password.php` | Replaced stub — real fetch to `api/auth.php?action=forgot`, loading state, success screen. |
| `AilisCraftUser/reset-password.php` | **New page** — reads `?token=`, verifies it, shows new-password form (with confirm), calls `action=reset`. |

## Install steps
1. Import `003_password_resets.sql` into `ailiscraft_db` (phpMyAdmin → Import, or `mysql ailiscraft_db < 003_password_resets.sql`). It's additive — no existing tables touched.
2. Replace `AilisCraftUser/api/auth.php`, `AilisCraftUser/forgot-password.php`.
3. Add `AilisCraftUser/reset-password.php` to the `AilisCraftUser` folder.
4. The "FORGOT PASSWORD?" link on `login.php` already points to `forgot-password.php` — no change needed.

## How it works
- User enters email → `forgot` action generates a 64-char random token, stores `sha256(token)` + 60-min expiry in `password_resets`, emails a link to `reset-password.php?token=…`.
- Always responds "sent" (even for unknown emails) to prevent account enumeration. Rate-limited to 3 requests/email/hour. Earlier tokens for the same email are invalidated.
- `reset-password.php` verifies the token before showing the form; on submit, `reset` action validates token + expiry, updates `users.password_hash` (same `password_hash()` as register), and deletes all tokens for that email (single-use).

## Localhost / XAMPP note
PHP `mail()` usually can't deliver on a local XAMPP install (no SMTP). Two fallbacks are built in:
- The reset link is always written to `AilisCraftUser/uploads/reset_links.log` and to the PHP error log — open that file to get the link during testing.
- If `AC_DEBUG=1` (set in `api/config.php` or env), the `forgot` API response also includes `debugResetLink` directly, and the forgot page shows it as a clickable link.

To send real email, configure SMTP (e.g. PHPMailer + Gmail/Hostinger) in `ac_send_reset_email()` inside `api/auth.php`.

## Security
- Raw token never stored — only its SHA-256 hash. Token is 256-bit (32 random bytes).
- 60-minute expiry, single-use, rate-limited, invalidates older tokens.
- Reset only allowed for `status = 'Active'` accounts.
- Tokens passed via URL query (standard for email links); short-lived + single-use mitigates history/log leakage.
