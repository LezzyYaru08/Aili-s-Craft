<?php
/**
 * SMS configuration for Aili's Craft order-status notifications.
 *
 * ALL values are read from environment variables (see .env.example). The
 * default 'textbelt' key is a PUBLIC free-tier key (1 SMS/day per server IP)
 * and may be shipped as-is; replace with a paid key via AC_SMS_API_KEY for
 * higher volume.
 *
 * Leave AC_SMS_API_KEY empty (or AC_SMS_PROVIDER=logonly) → log-only mode:
 * no real SMS is sent, but every message is written to the sms_log DB table
 * and logs/sms_log.log — perfect for localhost testing. The log is ALWAYS
 * kept as a record even when real sending is enabled.
 *
 * FREE METHODS (no credit card):
 *
 *   1. TextBelt (default free) — https://textbelt.com
 *        NO SIGNUP NEEDED. AC_SMS_PROVIDER=textbelt + AC_SMS_API_KEY=textbelt
 *        — the free tier sends 1 SMS per day per server IP, to any
 *        international number (PH supported). Works out of the box. For
 *        higher volume, buy a TextBelt key (cheap, pay-as-you-go).
 *
 *   2. Twilio free trial — https://twilio.com/try-twilio
 *        Sign up (free, gets trial credit). AC_SMS_PROVIDER=twilio and set
 *        AC_TWILIO_SID, AC_TWILIO_TOKEN, AC_TWILIO_FROM (your trial +1 number).
 *        Trial can only SMS numbers you verify in the Twilio console; upgrade
 *        to send to any number.
 *
 *   3. Semaphore (Philippines — paid, most reliable PH delivery)
 *        https://semaphore.co  ~PHP 0.40–0.50/SMS. AC_SMS_PROVIDER=semaphore
 *        and set AC_SMS_API_KEY.
 */
require_once __DIR__ . '/config.php';
define('SMS_PROVIDER',  ac_env('AC_SMS_PROVIDER', 'textbelt')); // 'textbelt' | 'twilio' | 'semaphore' | 'logonly'
define('SMS_API_KEY',   ac_env('AC_SMS_API_KEY', 'textbelt'));  // textbelt free key, or paid TextBelt/Semaphore key
define('SMS_SENDER',    ac_env('AC_SMS_SENDER', 'AilisCraft')); // 3-11 alphanumeric sender ID (Semaphore only)
define('SMS_TIMEOUT',   10);                                    // seconds — keep low so status updates never hang
// --- Twilio (only needed if AC_SMS_PROVIDER=twilio) ---
define('TWILIO_SID',    ac_env('AC_TWILIO_SID', ''));           // Account SID from Twilio console
define('TWILIO_TOKEN',  ac_env('AC_TWILIO_TOKEN', ''));         // Auth token
define('TWILIO_FROM',   ac_env('AC_TWILIO_FROM', ''));          // Your Twilio phone number, e.g. +1234567890
define('SMS_ENABLED',   SMS_PROVIDER !== 'logonly' && SMS_API_KEY !== '');
