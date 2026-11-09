<?php
/**
 * Email configuration for Aili's Craft (password-reset emails, etc.).
 *
 * ALL values are read from environment variables (see .env.example). No secrets
 * are hardcoded. If AC_EMAIL_API_KEY is empty → log-only mode: no real email is
 * sent, but every message is written to logs/reset_links.log — perfect for
 * localhost testing. The log is ALWAYS kept as a record even when real sending
 * is enabled.
 *
 * FREE METHODS (no credit card, works from any host including XAMPP — these
 * use a simple HTTP POST, so they do NOT need a local SMTP server):
 *
 *   1. Brevo (recommended) — https://app.brevo.com  (300 emails/day FREE forever)
 *        Sign up (free, no card) → SMTP & API → API Keys → "Generate a new API key"
 *        Set AC_EMAIL_PROVIDER=brevo and AC_EMAIL_API_KEY=<key>.
 *        Also add & verify your sender email (Senders → Add a sender) so the
 *        "From" address is trusted. Until verified, Brevo uses a default sender.
 *
 *   2. Resend — https://resend.com  (100 emails/day FREE forever)
 *        Sign up (free, no card) → API Keys → Create API key.
 *        Set AC_EMAIL_PROVIDER=resend. NOTE: on the free plan, before you verify
 *        a domain you can only send FROM onboarding@resend.dev and TO your own
 *        account email — verify a domain (free, DNS TXT record) to email anyone.
 *
 *   3. phpmail — built-in PHP mail() (usually only works on real hosting with
 *      sendmail/postfix; NOT on a default XAMPP install). Kept as last fallback.
 */
require_once __DIR__ . '/config.php';
define('EMAIL_PROVIDER',  ac_env('AC_EMAIL_PROVIDER', 'brevo'));   // 'brevo' | 'resend' | 'phpmail' | 'logonly'
define('EMAIL_API_KEY',   ac_env('AC_EMAIL_API_KEY', ''));         // Brevo v3 or Resend API key (empty = log-only)
define('EMAIL_FROM_NAME', ac_env('AC_EMAIL_FROM_NAME', "Aili's Craft"));
define('EMAIL_FROM_ADDR', ac_env('AC_EMAIL_FROM_ADDR', 'no-reply@ailiscraft.ph')); // verified sender email
define('EMAIL_TIMEOUT',   10);                                      // seconds — keep low so forgot-password never hangs
define('EMAIL_ENABLED',   EMAIL_API_KEY !== '' && EMAIL_PROVIDER !== 'logonly');
