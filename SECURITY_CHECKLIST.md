# Post-Deployment Security Checklist

Go through this list **right after** your site goes live. Check each box. A skipped item can let someone take over your shop.

---

### Accounts & setup files

- [ ] **1. Delete `AilisCraftAdmin/setup_accounts.php`.** You used it once to create admin/owner. Leaving it lets anyone re-create accounts. If you cannot delete it, at minimum clear `AC_ADMIN_SETUP_TOKEN` — but deleting is safer.
- [ ] **2. Change the default admin password immediately.** Seeded login is `admin` / `AilisAdmin123`. Log in → change password to something long and unique.
- [ ] **3. Change the default owner password immediately.** Seeded login is `Owner` / `AilisCraft123`. Change it right after first login.
- [ ] **4. Delete any backup/old files** you may have uploaded: anything ending in `.bak`, `.old`, `.orig`, `.zip`, `.sql`, or `config.php~`. Attackers scan for these. (You can keep a copy on your own computer — just not on the server.)

### Error exposure & sensitive URLs

- [ ] **5. Confirm debug is off.** `AC_DEBUG` must be `0`. Test: visit a made-up URL like `https://your-site.com/AilisCraftUser/nonexistent.php`. You should see a plain error page — **not** a stack trace or file paths. If you see code paths, debug is still on.
- [ ] **6. Confirm the password-reset log is not downloadable.** Visit `https://your-site.com/AilisCraftUser/logs/reset_links.log`. It must return **403 Forbidden** (or 404). If it downloads or shows text, fix folder permissions / `.htaccess`.
- [ ] **7. Confirm the source-code viewer is off.** Visit `https://your-site.com/AilisCraftAdmin/source-code.php`. It must return **403 Forbidden**. If it shows your PHP source, set `AC_ENABLE_SOURCE_BROWSER=0`.
- [ ] **8. Confirm directory listing is off.** Visit `https://your-site.com/AilisCraftUser/assets/`. You must **not** see a list of files. If you do, add `Options -Indexes` to `.htaccess` (or ask your host to disable it).

### Domain, HTTPS & sessions

- [ ] **9. `AC_APP_URL` is your real `https://` domain** with no trailing slash (e.g. `https://shop.example.com`, not `http://…` and not ending in `/`).
- [ ] **10. HTTPS is enabled and enforced.** Free SSL via Let's Encrypt (InfinityFree: client area → Free SSL; cPanel: SSL/TLS Status → Run AutoSSL). Then turn on the HTTPS redirect in `.htaccess` (uncomment the block). Test that `http://` automatically jumps to `https://`.
- [ ] **11. Session cookies are secure.** With HTTPS active, ask your host (or set in `.htaccess` / `php.ini`) `session.cookie_secure = On` and `session.cookie_httponly = On`. This stops login cookies from being sent over plain HTTP.

### Keys, files & backups

- [ ] **12. Rotate any key you ever pasted publicly.** If a Brevo, SMS, Messenger, Cloudinary, or database key was ever in a screenshot, chat, public GitHub repo, or shared file — **regenerate it** in that provider's dashboard and update the new value. Assume the old one is compromised.
- [ ] **13. File permissions are correct.** Config files (`api/config.php`, `api/email_config.php`, `api/sms_config.php`, `api/messenger_config.php`) should be **644**. Folders `AilisCraftUser/uploads/` and `AilisCraftUser/logs/` should be writable (**755** or **775**). Never set anything to 777.
- [ ] **14. Back up the database regularly.** In phpMyAdmin → Export, download a `.sql` copy at least once a month (or weekly if you get many orders). Store it on your own computer, not on the server.

### Cleanup & verification

- [ ] **15. Remove install/docs `.md` files from `public_html`** if you uploaded them (README, deploy guides, this checklist). They are harmless but reveal your setup. Keep copies on your own computer. Also **lock down phpMyAdmin** (strong cPanel password, never share it) and **verify a password-reset email actually arrives** in a customer inbox (test with your own email) — if it doesn't, your Brevo key or sender address is wrong.

> **Bonus (strongly recommended):** if you keep `setup_accounts.php` for any reason, set `AC_ADMIN_SETUP_TOKEN` to a long random string (32+ characters). The better choice is still to delete the file after first use.
