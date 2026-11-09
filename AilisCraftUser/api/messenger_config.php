<?php
/**
 * Facebook Messenger notification configuration.
 *
 * ALL values are read from environment variables (see .env.example). Leave
 * AC_FB_PAGE_ACCESS_TOKEN empty to disable Messenger (SMS still works).
 *
 * Setup (requires a live HTTPS site — NOT testable on localhost):
 *   1. Create a Facebook Page for your shop (facebook.com/pages/create).
 *   2. Go to developers.facebook.com → Create App → "Business" type.
 *   3. Add the "Messenger" product → select your Page → generate a
 *      long-lived Page Access Token (set AC_FB_PAGE_ACCESS_TOKEN).
 *   4. Set AC_FB_VERIFY_TOKEN to any random string you choose — you'll enter
 *      the same string in the Facebook App dashboard → Messenger → Webhooks.
 *   5. Webhook URL: https://YOURSITE.com/AilisCraftUser/api/messenger.php
 *      Subscribe to the "messaging_optins" and "messages" fields.
 *   6. Whitelist your domain: App dashboard → Messenger → Settings →
 *      "Whitelisted Domains" → add your HTTPS domain.
 *   7. AC_FB_APP_ID and AC_FB_PAGE_ID are shown in your App dashboard / Page
 *      "About" page (Page ID is a long number).
 */
require_once __DIR__ . '/config.php';
define('FB_PAGE_ACCESS_TOKEN', ac_env('AC_FB_PAGE_ACCESS_TOKEN', '')); // long-lived Page Access Token
define('FB_VERIFY_TOKEN',      ac_env('AC_FB_VERIFY_TOKEN', ''));      // any random string, same as in App dashboard
define('FB_APP_ID',            ac_env('AC_FB_APP_ID', ''));            // your Meta App ID
define('FB_PAGE_ID',           ac_env('AC_FB_PAGE_ID', ''));           // your Facebook Page ID (numeric)
define('FB_API_VERSION',       'v18.0');
