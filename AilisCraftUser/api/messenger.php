<?php
/**
 * Aili's Craft — Facebook Messenger sender + webhook.
 *
 * - When included (by orders.php): provides ac_send_messenger($psid, $text).
 * - When accessed directly (webhook URL): verifies the GET challenge and
 *   processes POST events (messaging_optins → stores PSID against order).
 *
 * Never throws — a Messenger failure must never break an order-status update.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/messenger_config.php';

function fb_enabled(): bool
{
    return FB_PAGE_ACCESS_TOKEN !== '';
}

/** Send one Messenger message (POST_PURCHASE_UPDATE tag — allowed outside 24h window). */
function ac_send_messenger(string $psid, string $text): array
{
    if (!fb_enabled() || $psid === '') {
        return ['sent' => false, 'error' => 'Messenger disabled or no PSID.'];
    }
    try {
        $url = 'https://graph.facebook.com/' . FB_API_VERSION . '/me/messages?access_token=' . urlencode(FB_PAGE_ACCESS_TOKEN);
        $payload = json_encode([
            'messaging_type' => 'MESSAGE_TAG',
            'tag'            => 'POST_PURCHASE_UPDATE',
            'recipient'      => ['id' => $psid],
            'message'        => ['text' => trim($text)],
        ]);
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);
        $resp = @file_get_contents($url, false, $ctx);
        $data = json_decode((string)$resp, true);
        $sent = is_array($data) && isset($data['message_id']);
        $error = $sent ? null : substr((string)$resp, 0, 300);
        fb_log($psid, $text, $sent, $error);
        return ['sent' => $sent, 'error' => $error];
    } catch (Throwable $e) {
        fb_log($psid, $text, false, $e->getMessage());
        return ['sent' => false, 'error' => $e->getMessage()];
    }
}

function fb_log(string $psid, string $text, bool $sent, ?string $error): void
{
    $logDir = dirname(__DIR__) . '/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents(
            $logDir . '/messenger_log.log',
            '[' . date('Y-m-d H:i:s') . '] ' . ($sent ? 'SENT ' : 'FAIL ') . $psid . ' | ' . $text . ($error ? ' | ERROR: ' . $error : '') . PHP_EOL,
            FILE_APPEND
        );
    }
    error_log('[AilisCraft Messenger] ' . ($sent ? 'sent' : 'failed') . ' to ' . $psid . ($error ? ': ' . $error : ''));
}

/** Store a customer's PSID against their order code (from opt-in data-ref). */
function fb_store_psid(string $orderCode, string $psid): void
{
    try {
        $code = strtoupper(trim($orderCode));
        if ($code === '' || $psid === '') return;
        $stmt = db()->prepare('UPDATE orders SET messenger_psid = ? WHERE order_code = ? LIMIT 1');
        $stmt->execute([$psid, $code]);
        fb_log($psid, 'OPT-IN stored for order ' . $code, true, null);
    } catch (Throwable $e) {
        error_log('[AilisCraft Messenger] store_psid: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------------
 * Webhook handling — only runs when this file is accessed directly.
 * ------------------------------------------------------------------ */
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    // GET challenge verification (Facebook App dashboard → Webhooks).
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        $mode  = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';
        if ($mode === 'subscribe' && $token === FB_VERIFY_TOKEN && FB_VERIFY_TOKEN !== '') {
            echo $challenge;
            exit;
        }
        http_response_code(403);
        echo 'Verification failed. Check FB_VERIFY_TOKEN in messenger_config.php.';
        exit;
    }

    // POST event delivery.
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode((string)$raw, true);
        if (is_array($data) && !empty($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                foreach (($entry['messaging'] ?? []) as $event) {
                    $sender = (string)($event['sender']['id'] ?? '');
                    // Opt-in from Send-to-Messenger button: optin.ref = order code.
                    if (isset($event['optin']['ref'])) {
                        fb_store_psid((string)$event['optin']['ref'], $sender);
                    }
                    // Customer sent a message — optional auto-reply.
                    if (isset($event['message']['text'])) {
                        ac_send_messenger($sender, "Hi from Aili's Craft! You'll get order updates here. You can also check your order status anytime on our website.");
                    }
                }
            }
        }
        // Always respond 200 quickly so Facebook doesn't retry.
        http_response_code(200);
        echo 'EVENT_RECEIVED';
        exit;
    }

    http_response_code(405);
    echo 'Messenger webhook. Use GET (verify) or POST (events).';
    exit;
}
