<?php
/**
 * Aili's Craft — SMS sender for order-status notifications.
 *
 * Usage:  $result = ac_send_sms($phone, $message, $orderId, $status);
 *         // returns ['sent' => bool, 'provider' => string, 'error' => string|null]
 *
 * Never throws — an SMS failure must never break an order-status update.
 * Every attempt is logged to the `sms_log` table (migration 004) AND to
 * uploads/sms_log.log as a file fallback. The log is ALWAYS kept as a record.
 *
 * Providers (set in api/sms_config.php):
 *   textbelt  — free tier (key 'textbelt', 1 SMS/day/IP) or paid key; PH supported
 *   twilio    — free trial credits; verify recipient numbers in console
 *   semaphore — paid, best PH reliability (~PHP 0.40/SMS)
 *   logonly   — no real SMS, log only (localhost testing)
 */
require_once __DIR__ . '/sms_config.php';

/** Normalize a PH mobile number to +639XXXXXXXXX. Returns '' if invalid. */
function ac_normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($digits) === 11 && strpos($digits, '09') === 0) {
        return '+63' . substr($digits, 1);          // 0917... -> +63917...
    }
    if (strlen($digits) === 10 && strpos($digits, '9') === 0) {
        return '+63' . $digits;                     // 917...  -> +63917...
    }
    if (strlen($digits) === 12 && strpos($digits, '63') === 0) {
        return '+' . $digits;                       // 63917... -> +63917...
    }
    if (strlen($digits) === 13 && strpos($digits, '639') === 0) {
        return '+' . $digits;
    }
    return '';
}

/** Insert an audit row into sms_log (best-effort; silently skipped if table missing). */
function ac_log_sms(PDO $pdo, ?int $orderId, string $phone, string $status, string $message, string $provider, bool $sent, ?string $error): void
{
    try {
        $pdo->prepare(
            'INSERT INTO sms_log (order_id, phone, status, message, provider, sent, error, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        )->execute([$orderId, $phone, $status, $message, $provider, $sent ? 1 : 0, $error]);
    } catch (Throwable $e) {
        // Table may not exist yet (migration 004 not imported) — ignore.
    }
    // File fallback — always writable location. logs/ is web-denied.
    $logDir = dirname(__DIR__) . '/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents(
            $logDir . '/sms_log.log',
            '[' . date('Y-m-d H:i:s') . '] ' . ($sent ? 'SENT ' : 'QUEUED ') . $provider . ' | ' . $phone . ' | ' . $status . ' | ' . $message . ($error ? ' | ERROR: ' . $error : '') . PHP_EOL,
            FILE_APPEND
        );
    }
}

/** POST form data to a URL with optional basic auth. Returns [body, http_code] or [false, null]. */
function ac_http_post_form(string $url, array $fields, int $timeout, ?string $basicAuth = null): array
{
    $payload = http_build_query($fields);
    $header = "Content-Type: application/x-www-form-urlencoded\r\n";
    if ($basicAuth !== null) {
        $header .= "Authorization: Basic " . base64_encode($basicAuth) . "\r\n";
    }
    $ctx = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => $header,
            'content'       => $payload,
            'timeout'       => $timeout,
            'ignore_errors' => true,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    $code = null;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    return [$resp, $code];
}

/** Send one SMS. Never throws. */
function ac_send_sms(string $phone, string $message, ?int $orderId, string $status): array
{
    $provider = SMS_ENABLED ? SMS_PROVIDER : 'logonly';
    try {
        $normalized = ac_normalize_phone($phone);
        if ($normalized === '') {
            ac_log_sms(db(), $orderId, $phone, $status, $message, $provider, false, 'Invalid phone number: ' . $phone);
            return ['sent' => false, 'provider' => $provider, 'error' => 'Invalid phone number.'];
        }
        // Keep messages within 1 SMS (160 GSM chars) where possible.
        $message = trim($message);
        if (!SMS_ENABLED) {
            ac_log_sms(db(), $orderId, $normalized, $status, $message, 'logonly', true, null);
            return ['sent' => true, 'provider' => 'logonly', 'error' => null];
        }

        $error = null;
        $sent = false;

        if (SMS_PROVIDER === 'textbelt') {
            // Free key 'textbelt' = 1 SMS/day per IP; paid keys remove the limit.
            list($resp, $code) = ac_http_post_form(
                'https://textbelt.com/text',
                ['key' => SMS_API_KEY, 'phone' => $normalized, 'message' => $message],
                SMS_TIMEOUT
            );
            if ($resp === false) {
                $error = 'Network/HTTP error reaching TextBelt.';
            } else {
                $data = json_decode((string)$resp, true);
                if (is_array($data) && !empty($data['success'])) {
                    $sent = true;
                } else {
                    $quota = (is_array($data) && isset($data['quotaRemaining'])) ? ' (quota remaining: ' . $data['quotaRemaining'] . ')' : '';
                    $errMsg = (is_array($data) && !empty($data['error'])) ? $data['error'] : substr((string)$resp, 0, 200);
                    $error = 'TextBelt rejected: ' . $errMsg . $quota;
                }
            }
        } elseif (SMS_PROVIDER === 'twilio') {
            if (TWILIO_SID === '' || TWILIO_TOKEN === '' || TWILIO_FROM === '') {
                $error = 'Twilio selected but TWILIO_SID / TWILIO_TOKEN / TWILIO_FROM are empty in sms_config.php.';
            } else {
                $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_SID . '/Messages.json';
                list($resp, $code) = ac_http_post_form(
                    $url,
                    ['To' => $normalized, 'From' => TWILIO_FROM, 'Body' => $message],
                    SMS_TIMEOUT,
                    TWILIO_SID . ':' . TWILIO_TOKEN
                );
                if ($resp === false) {
                    $error = 'Network/HTTP error reaching Twilio.';
                } else {
                    $data = json_decode((string)$resp, true);
                    if (is_array($data) && !empty($data['sid']) && (int)$code < 400) {
                        $sent = true;
                    } else {
                        $errMsg = (is_array($data) && !empty($data['message'])) ? $data['message'] : substr((string)$resp, 0, 200);
                        $error = 'Twilio rejected (HTTP ' . ($code ?? '?') . '): ' . $errMsg;
                    }
                }
            }
        } elseif (SMS_PROVIDER === 'semaphore') {
            list($resp, $code) = ac_http_post_form(
                'https://api.semaphore.co/api/v4/messages',
                ['apikey' => SMS_API_KEY, 'number' => $normalized, 'message' => $message, 'sendername' => SMS_SENDER],
                SMS_TIMEOUT
            );
            if ($resp === false) {
                $error = 'Network/HTTP error reaching Semaphore.';
            } else {
                $data = json_decode((string)$resp, true);
                // Semaphore returns array of message objects on success, or {"message_id":null,...} on failure.
                if (is_array($data) && isset($data[0]['message_id'])) {
                    $sent = true;
                } else {
                    $error = 'Semaphore rejected: ' . substr((string)$resp, 0, 200);
                }
            }
        } else {
            $error = 'Unknown SMS provider: ' . SMS_PROVIDER;
        }

        ac_log_sms(db(), $orderId, $normalized, $status, $message, $provider, $sent, $error);
        return ['sent' => $sent, 'provider' => $provider, 'error' => $error];
    } catch (Throwable $e) {
        try {
            ac_log_sms(db(), $orderId, $phone, $status, $message, $provider, false, $e->getMessage());
        } catch (Throwable $ignore) {}
        return ['sent' => false, 'provider' => $provider, 'error' => $e->getMessage()];
    }
}

/** Build the customer-facing SMS body for a given order status. */
function ac_sms_template(string $status, string $orderCode): string
{
    $code = strtoupper($orderCode);
    switch ($status) {
        case 'Preparing':
            return "Aili's Craft: Hi! Your order {$code} is now being prepared. We'll text you once it ships. Thank you!";
        case 'Shipped':
            return "Aili's Craft: Your order {$code} has shipped and is with our courier. We'll update you before delivery. Thank you!";
        case 'Out for Delivery':
            return "Aili's Craft: Your order {$code} is OUT FOR DELIVERY today! Please keep your line open. Thank you!";
        case 'Delivered':
            return "Aili's Craft: Your order {$code} has been delivered. We hope you love it! Thank you for shopping with us.";
        default:
            return "Aili's Craft: Your order {$code} status is now {$status}. Thank you!";
    }
}
