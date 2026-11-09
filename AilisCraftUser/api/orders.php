<?php
/**
 * Aili's Craft — Orders API
 *
 *   POST orders.php?action=create   Place an order (orders + order_items + order_payments)
 *   GET  orders.php?action=list     List the logged-in customer's orders (?email=...)
 *   POST orders.php?action=cancel   Cancel an order  {orderCode, email}
 *
 * NOTE: The prototype front end has no server-side login/session yet, so the customer is
 * identified by the email stored in the browser profile. Put these endpoints behind real
 * authentication (session or token) before going to production.
 */
declare(strict_types=1);

require __DIR__ . '/config.php';
require_once __DIR__ . '/sms.php';
require_once __DIR__ . '/messenger.php';

/** Order statuses a customer may still cancel from (matches history.html + features.js). */
const CANCELLABLE_STATUSES = [
    'Preparing', 'Payment Pending', 'Downpayment Paid', 'Paid', 'Order Confirmed',
    'Pending', 'Processing',
];

/** Payment states that mean money was actually received (so a cancel needs a refund review). */
const PAID_PAYMENT_STATES = ['Downpayment Paid', 'Paid', 'Partially Paid'];

try {
    $action = (string) ($_GET['action'] ?? '');
    $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');

    switch ($action) {
        case 'create':
            require_method($method, 'POST');
            handle_create();
            break;
        case 'list':
            require_method($method, 'GET');
            handle_list();
            break;
        case 'cancel':
            require_method($method, 'POST');
            handle_cancel();
            break;
        case 'owner-list':
            require_method($method, 'GET');
            handle_owner_list();
            break;
        case 'owner-status':
            require_method($method, 'POST');
            handle_owner_status();
            break;
        default:
            throw new ApiError('Unknown action.', 404);
    }
} catch (ApiError $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    error_log('[AilisCraft] ' . $e);
    $message = 'Something went wrong while saving to the database. Please try again.';
    if (AC_DEBUG) {
        $message .= ' (' . $e->getMessage() . ')';
    }
    json_out(['ok' => false, 'error' => $message], 500);
}

/* ======================================================================
   create
   ====================================================================== */
function handle_create(): void
{
    $in  = read_json_body();
    $pdo = db();

    $customer = is_array($in['customer'] ?? null) ? $in['customer'] : [];
    $delivery = is_array($in['delivery'] ?? null) ? $in['delivery'] : [];

    $email    = normalize_email($customer['email'] ?? '');
    $fullName = clean_str($customer['fullName'] ?? '', 150);

    $payer   = clean_str($delivery['payerFullName'] ?? '', 150);
    $address = clean_str($delivery['address'] ?? '', 255);
    $contact = preg_replace('/\D+/', '', (string) ($delivery['contact'] ?? '')) ?? '';
    $date    = clean_str($delivery['date'] ?? '', 10);

    if ($payer === '' || $address === '') {
        throw new ApiError('Please complete your delivery and payment details.');
    }
    if (strlen($contact) < 7 || strlen($contact) > 15) {
        throw new ApiError('Please enter a valid contact number.');
    }
    $deliveryDate = validate_delivery_date($date);

    $paymentMethod = (string) ($in['paymentMethod'] ?? '');
    if (!in_array($paymentMethod, ['cod_30_gcash_70_cod', 'gcash_full'], true)) {
        throw new ApiError('Please choose a valid payment method.');
    }
    if (($in['termsAccepted'] ?? false) !== true) {
        throw new ApiError('Please agree to the Terms & Conditions before placing your order.');
    }

    $rawItems = $in['items'] ?? null;
    if (!is_array($rawItems) || count($rawItems) === 0) {
        throw new ApiError('Your bag is empty.');
    }
    if (count($rawItems) > 50) {
        throw new ApiError('Too many items in one order (maximum 50).');
    }

    // Category slug -> id
    $categories = [];
    foreach ($pdo->query('SELECT id, slug FROM product_categories')->fetchAll() as $row) {
        $categories[(string) $row['slug']] = (int) $row['id'];
    }

    $productLookup = $pdo->prepare('SELECT id FROM products WHERE product_code = ? LIMIT 1');

    $items    = [];
    $subtotal = 0.0;
    foreach (array_values($rawItems) as $index => $raw) {
        $item = normalize_item(is_array($raw) ? $raw : [], $index + 1, $categories);

        $item['product_id'] = null;
        if ($item['product_code'] !== '') {
            $productLookup->execute([$item['product_code']]);
            $found = $productLookup->fetchColumn();
            if ($found !== false) {
                $item['product_id'] = (int) $found;
            }
        }

        $subtotal += $item['line_total'];
        $items[] = $item;
    }

    // Totals are always recomputed on the server.
    $subtotal     = round($subtotal, 2);
    $deliveryFee  = round((float) DELIVERY_FEE, 2);
    $total        = round($subtotal + $deliveryFee, 2);
    $downPercent  = $paymentMethod === 'gcash_full' ? 100.0 : 30.0;
    $downAmount   = round($total * $downPercent / 100, 2);
    $remaining    = round(max(0.0, $total - $downAmount), 2);
    $downStatus   = $paymentMethod === 'cod_30_gcash_70_cod' ? 'Pending GCash Payment' : 'Not Applicable';
    $now          = date('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $userId = ensure_user($pdo, $email, $fullName !== '' ? $fullName : $payer, $contact, $now);

        $insertOrder = $pdo->prepare(
            'INSERT INTO orders
               (order_code, user_id, status, payer_full_name, delivery_address, contact_number,
                delivery_date, payment_method, payment_status, down_payment_status,
                down_payment_percent, down_payment_amount, remaining_balance,
                subtotal, delivery_fee, total_amount, terms_accepted, terms_accepted_at)
             VALUES
               (?, ?, \'Preparing\', ?, ?, ?,
                ?, ?, \'Payment Pending\', ?,
                ?, ?, ?,
                ?, ?, ?, 1, ?)'
        );

        $orderId   = 0;
        $orderCode = '';
        for ($attempt = 0; $attempt < 8 && $orderId === 0; $attempt++) {
            $orderCode = 'AC-' . random_int(100000, 999999);
            try {
                $insertOrder->execute([
                    $orderCode, $userId, $payer, $address, $contact,
                    $deliveryDate, $paymentMethod, $downStatus,
                    $downPercent, $downAmount, $remaining,
                    $subtotal, $deliveryFee, $total, $now,
                ]);
                $orderId = (int) $pdo->lastInsertId();
            } catch (PDOException $e) {
                if (!is_duplicate_key($e)) {
                    throw $e;
                }
                // order_code collision: try another code
            }
        }
        if ($orderId === 0) {
            throw new ApiError('Could not generate an order number. Please try again.', 500);
        }

        $insertItem = $pdo->prepare(
            'INSERT INTO order_items
               (order_id, product_id, category_id, item_name, quantity, unit_price, line_total,
                customization_summary, customization_json, snapshot_data, image_url, special_request_images)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            $insertItem->execute([
                $orderId, $item['product_id'], $item['category_id'], $item['name'],
                $item['quantity'], $item['unit_price'], $item['line_total'],
                $item['summary'], $item['json'], $item['snapshot_data'], $item['image_url'],
                $item['special_request_images'],
            ]);
        }

        $pdo->prepare(
            'INSERT INTO order_payments
               (order_id, method, status, down_payment_percent, down_payment_amount, remaining_balance)
             VALUES (?, ?, \'Pending\', ?, ?, ?)'
        )->execute([$orderId, $paymentMethod, $downPercent, $downAmount, $remaining]);

        $pdo->prepare(
            'INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
             VALUES (?, NULL, ?, \'customer\', \'Order placed\')'
        )->execute([$orderId, 'Preparing']);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    json_out(['ok' => true, 'order' => fetch_order_by_id($pdo, $orderId)], 201);
}

/** Validate one cart line and prepare the values that go into order_items. */
function normalize_item(array $raw, int $position, array $categories): array
{
    $slug = strtolower(clean_str($raw['category'] ?? '', 50));
    if (!isset($categories[$slug])) {
        throw new ApiError("Item $position has an unknown category.");
    }

    $name = clean_str($raw['name'] ?? '', 150);
    if ($name === '') {
        throw new ApiError("Item $position is missing a name.");
    }

    $quantity = (int) ($raw['quantity'] ?? 1);
    $quantity = max(1, min(99, $quantity));

    if (!isset($raw['price']) || !is_numeric($raw['price'])) {
        throw new ApiError("Item $position has an invalid price.");
    }
    $unitPrice = round((float) $raw['price'], 2);
    if ($unitPrice < 0 || $unitPrice > 100000) {
        throw new ApiError("Item $position has an invalid price.");
    }

    $options = [];
    if (isset($raw['options']) && is_array($raw['options'])) {
        foreach (array_slice($raw['options'], 0, 40) as $option) {
            $text = clean_str($option, 500);
            if ($text !== '') {
                $options[] = $text;
            }
        }
    }

    $details = (isset($raw['customization']) && is_array($raw['customization'])) ? $raw['customization'] : [];
    if (count($details) === 0) {
        $details = new stdClass();
    }

    $productCode = clean_str($raw['productCode'] ?? '', 60);

    $json = json_encode([
        'cartItemId'  => clean_str($raw['id'] ?? '', 80),
        'productCode' => $productCode,
        'options'     => $options,
        'details'     => $details,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (strlen($json) > 16000) {
        throw new ApiError("Item $position has too much customization data.");
    }

    // PNG snapshot (base64 data URL) can be large — store raw after stripping
    // null bytes; fall back to the legacy SVG innerHTML field when absent.
    $snapshotPng = (string) ($raw['snapshotPng'] ?? '');
    $snapshotPng = str_replace("\0", '', $snapshotPng);
    if (strlen($snapshotPng) > 8000000) {
        $snapshotPng = substr($snapshotPng, 0, 8000000);
    }
    if ($snapshotPng === '') {
        $fallbackUrl = clean_str($raw['imageUrl'] ?? '', 500);
        $snapshotPng = $fallbackUrl !== '' ? $fallbackUrl : clean_str($raw['image'] ?? '', 60000);
    }
    // Final guarantee: the plain product image path always lands in snapshot_data.
    if ($snapshotPng === '') {
        $snapshotPng = clean_str($raw['imageUrl'] ?? '', 500);
    }
    // Special-request reference photos: relative paths joined by comma (<=1000 chars).
    $srImages = isset($raw['specialRequestImages']) && is_array($raw['specialRequestImages'])
        ? $raw['specialRequestImages'] : [];
    $srPaths = [];
    foreach (array_slice($srImages, 0, 10) as $p) {
        $p = clean_str((string) $p, 200);
        if ($p !== '') {
            $srPaths[] = $p;
        }
    }
    $specialRequestImages = $srPaths ? substr(implode(',', $srPaths), 0, 1000) : null;
    return [
        'category_id'            => $categories[$slug],
        'name'                   => $name,
        'quantity'               => $quantity,
        'unit_price'             => $unitPrice,
        'line_total'             => round($unitPrice * $quantity, 2),
        'product_code'           => $productCode,
        'summary'                => $options ? implode('; ', $options) : null,
        'snapshot_data'          => $snapshotPng,
        'image_url'              => clean_str($raw['imageUrl'] ?? '', 500),
        'special_request_images' => $specialRequestImages,
        'json'                   => $json,
    ];
}

function validate_delivery_date(string $value): string
{
    $tz   = new DateTimeZone(APP_TIMEZONE);
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $tz);
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new ApiError('Please choose a valid delivery date.');
    }
    if ($date < new DateTimeImmutable('tomorrow', $tz)) {
        throw new ApiError('Please choose a delivery date starting tomorrow. Today and past dates are not allowed.');
    }
    return $value;
}

/**
 * Orders.user_id is a required foreign key, but registration/login are still browser-only.
 * Find the customer by email, or create a minimal row so the order can be saved.
 * The generated password hash is random and unusable: it is a placeholder until real
 * registration is connected to the users table.
 */
function ensure_user(PDO $pdo, string $email, string $fullName, string $phone, string $now): int
{
    $find = $pdo->prepare('SELECT id, status FROM users WHERE email = ? LIMIT 1');
    $find->execute([$email]);
    $row = $find->fetch();

    if (!$row) {
        $name = $fullName !== '' ? $fullName : (string) strstr($email, '@', true);
        try {
            $pdo->prepare(
                'INSERT INTO users (full_name, email, phone, password_hash, status, terms_accepted_at)
                 VALUES (?, ?, ?, ?, \'Active\', ?)'
            )->execute([
                clean_str($name, 150),
                $email,
                substr($phone, 0, 20),
                password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
                $now,
            ]);
            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            if (!is_duplicate_key($e)) {
                throw $e;
            }
            // Another request created the same customer first: re-read it below.
            $find->execute([$email]);
            $row = $find->fetch();
        }
    }

    if (!$row) {
        throw new ApiError('Could not find or create your customer account.', 500);
    }
    if (($row['status'] ?? 'Active') !== 'Active') {
        throw new ApiError('This account is disabled. Please contact the shop.', 403);
    }
    return (int) $row['id'];
}

function is_duplicate_key(PDOException $e): bool
{
    return (int) ($e->errorInfo[1] ?? 0) === 1062;
}

/* ======================================================================
   list
   ====================================================================== */
function handle_list(): void
{
    $email = normalize_email($_GET['email'] ?? '');
    $pdo   = db();

    $stmt = $pdo->prepare(
        'SELECT o.*
           FROM orders o
           JOIN users u ON u.id = o.user_id
          WHERE u.email = ?
          ORDER BY o.created_at DESC, o.id DESC
          LIMIT 50'
    );
    $stmt->execute([$email]);

    json_out(['ok' => true, 'orders' => format_orders($pdo, $stmt->fetchAll())]);
}

/* ======================================================================
   cancel
   ====================================================================== */
function handle_cancel(): void
{
    $in    = read_json_body();
    $email = normalize_email($in['email'] ?? '');
    $code  = strtoupper(clean_str($in['orderCode'] ?? '', 20));
    if ($code === '') {
        throw new ApiError('Missing order number.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'SELECT o.*
               FROM orders o
               JOIN users u ON u.id = o.user_id
              WHERE o.order_code = ? AND u.email = ?
              FOR UPDATE'
        );
        $stmt->execute([$code, $email]);
        $order = $stmt->fetch();
        if (!$order) {
            throw new ApiError('Order not found.', 404);
        }

        if (strcasecmp((string) $order['status'], 'Cancelled') !== 0) {
            $allowed = array_map('strtolower', CANCELLABLE_STATUSES);
            if (!in_array(strtolower((string) $order['status']), $allowed, true)) {
                throw new ApiError('This order can no longer be cancelled (status: ' . $order['status'] . ').', 409);
            }

            $wasPaid = in_array((string) $order['payment_status'], PAID_PAYMENT_STATES, true)
                || in_array((string) $order['down_payment_status'], PAID_PAYMENT_STATES, true);
            $paymentStatus = $wasPaid ? 'Refund Pending' : 'Cancelled';

            $pdo->prepare(
                'UPDATE orders
                    SET status = \'Cancelled\', payment_status = ?, cancellation_reason = ?
                  WHERE id = ?'
            )->execute([$paymentStatus, 'Cancelled by customer', (int) $order['id']]);

            $pdo->prepare('UPDATE order_payments SET status = ? WHERE order_id = ?')
                ->execute([$paymentStatus, (int) $order['id']]);
            $pdo->prepare(
                'INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
                 VALUES (?, ?, \'Cancelled\', \'customer\', ?)'
            )->execute([(int) $order['id'], (string) $order['status'], 'Cancelled by customer']);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    json_out(['ok' => true, 'order' => fetch_order_by_id($pdo, (int) $order['id'])]);
}

/* ======================================================================
   Response shaping (same shape the front end already keeps in ac_order_history)
   ====================================================================== */
/* Owner dashboard database bridge.
 * IMPORTANT: add server-side owner authentication before deploying this prototype publicly.
 */
function handle_owner_list(): void
{
    $pdo = db();
    $stmt = $pdo->query('SELECT o.* FROM orders o ORDER BY o.created_at DESC, o.id DESC LIMIT 500');
    json_out(['ok' => true, 'orders' => format_orders($pdo, $stmt->fetchAll())]);
}

function handle_owner_status(): void
{
    $in = read_json_body();
    $code = strtoupper(clean_str($in['orderCode'] ?? '', 20));
    $status = clean_str($in['status'] ?? '', 32);
    $allowed = ['Preparing', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled'];
    if ($code === '' || !in_array($status, $allowed, true)) {
        throw new ApiError('Choose a valid order and status.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, status FROM orders WHERE order_code = ? FOR UPDATE');
        $stmt->execute([$code]);
        $order = $stmt->fetch();
        if (!$order) throw new ApiError('Order not found.', 404);
        $old = (string) $order['status'];
        if ($old !== $status) {
            $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, (int) $order['id']]);
            $pdo->prepare(
                'INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([(int) $order['id'], $old, $status, 'owner', 'Updated in owner dashboard']);
            $userStmt = $pdo->prepare('SELECT user_id FROM orders WHERE id = ?');
            $userStmt->execute([(int) $order['id']]);
            $userId = (int) $userStmt->fetchColumn();
            $message = 'Your order ' . $code . ' status is now ' . $status . '.';
            $pdo->prepare(
                'INSERT INTO notifications (user_id, order_id, type, title, message)
                 VALUES (?, ?, \'order_status\', \'Order status updated\', ?)'
            )->execute([$userId, (int) $order['id'], $message]);
        }
        $pdo->commit();

        // SMS notification for the 4 fulfillment statuses (never breaks the update).
        if ($old !== $status && in_array($status, ['Preparing', 'Shipped', 'Out for Delivery', 'Delivered'], true)) {
            try {
                $phoneStmt = $pdo->prepare('SELECT contact_number FROM orders WHERE id = ?');
                $phoneStmt->execute([(int) $order['id']]);
                $custPhone = (string) $phoneStmt->fetchColumn();
                ac_send_sms($custPhone, ac_sms_template($status, $code), (int) $order['id'], $status);
            } catch (Throwable $smsE) {
                error_log('[AilisCraft SMS] ' . $smsE->getMessage());
            }
            // Messenger notification (if customer opted in — PSID stored on order).
            try {
                $psidStmt = $pdo->prepare('SELECT messenger_psid FROM orders WHERE id = ?');
                $psidStmt->execute([(int) $order['id']]);
                $psid = (string) $psidStmt->fetchColumn();
                if ($psid !== '' && function_exists('ac_send_messenger')) {
                    ac_send_messenger($psid, ac_sms_template($status, $code));
                }
            } catch (Throwable $fbE) {
                error_log('[AilisCraft Messenger] ' . $fbE->getMessage());
            }
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    $fresh = $pdo->prepare('SELECT * FROM orders WHERE order_code = ?');
    $fresh->execute([$code]);
    json_out(['ok' => true, 'order' => format_orders($pdo, $fresh->fetchAll())[0] ?? null]);
}

function fetch_order_by_id(PDO $pdo, int $orderId): array
{
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $orders = format_orders($pdo, $stmt->fetchAll());
    if (!$orders) {
        throw new ApiError('Order not found.', 404);
    }
    return $orders[0];
}

function format_orders(PDO $pdo, array $orders): array
{
    if (!$orders) {
        return [];
    }

    $ids          = array_map(static fn(array $o): int => (int) $o['id'], $orders);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $itemStmt = $pdo->prepare(
        "SELECT oi.*, c.slug AS category_slug, p.product_code AS catalog_code
           FROM order_items oi
           JOIN product_categories c ON c.id = oi.category_id
           LEFT JOIN products p ON p.id = oi.product_id
          WHERE oi.order_id IN ($placeholders)
          ORDER BY oi.id"
    );
    $itemStmt->execute($ids);
    $itemsByOrder = [];
    foreach ($itemStmt->fetchAll() as $row) {
        $saved = json_decode((string) ($row['customization_json'] ?? ''), true);
        $saved = is_array($saved) ? $saved : [];

        $itemsByOrder[(int) $row['order_id']][] = [
            'id'            => $saved['cartItemId'] ?? ('item_' . $row['id']),
            'dbId'          => (int) $row['id'],
            'productCode'   => $saved['productCode'] ?? ($row['catalog_code'] ?? ''),
            'category'      => $row['category_slug'],
            'name'          => $row['item_name'],
            'quantity'      => (int) $row['quantity'],
            'price'         => (float) $row['unit_price'],
            'lineTotal'     => (float) $row['line_total'],
            'options'       => is_array($saved['options'] ?? null) ? $saved['options'] : [],
            'customization' => $saved['details'] ?? new stdClass(),
            'snapshot'      => (string) ($row['snapshot_data'] ?? ''),
            'imageUrl'      => (string) ($row['image_url'] ?? ''),
            'specialRequestImages' => (string) ($row['special_request_images'] ?? ''),
            'customizationSummary' => (string) ($row['customization_summary'] ?? ''),
        ];
    }

    $payStmt = $pdo->prepare("SELECT * FROM order_payments WHERE order_id IN ($placeholders) ORDER BY id");
    $payStmt->execute($ids);
    $paymentByOrder = [];
    foreach ($payStmt->fetchAll() as $row) {
        $paymentByOrder[(int) $row['order_id']] = $row; // keep the latest payment row
    }

    $result = [];
    $customerStmt = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE id = ?');
    foreach ($orders as $o) {
        $id      = (int) $o['id'];
        $payment = $paymentByOrder[$id] ?? [];
        $proof   = (string) ($payment['proof_path'] ?? '');
        $customerStmt->execute([(int) $o['user_id']]);
        $customer = $customerStmt->fetch() ?: [];

        $result[] = [
            'id'                  => $o['order_code'],
            'dbId'                => $id,
            'customerName'        => (string) ($customer['full_name'] ?? $o['payer_full_name']),
            'customerEmail'       => (string) ($customer['email'] ?? ''),
            'customerPhone'       => (string) ($customer['phone'] ?? $o['contact_number']),
            'date'                => date(DATE_ATOM, strtotime((string) $o['created_at'])),
            'status'              => $o['status'],
            'paymentMethod'       => $o['payment_method'],
            'paymentStatus'       => $o['payment_status'],
            'downPaymentStatus'   => $o['down_payment_status'] ?? '',
            'downPaymentPercent'  => $o['down_payment_percent'] !== null ? (float) $o['down_payment_percent'] : null,
            'downPaymentAmount'   => $o['down_payment_amount'] !== null ? (float) $o['down_payment_amount'] : null,
            'upfrontGcashAmount'  => $o['down_payment_amount'] !== null ? (float) $o['down_payment_amount'] : 0.0,
            'remainingBalance'    => $o['remaining_balance'] !== null ? (float) $o['remaining_balance'] : 0.0,
            'gcashReference'      => (string) ($payment['transaction_reference'] ?? ''),
            'gcashProofName'      => $proof !== '' ? basename($proof) : '',
            'payerName'           => $o['payer_full_name'],
            'address'             => $o['delivery_address'],
            'contact'             => $o['contact_number'],
            'deliveryDate'        => $o['delivery_date'],
            'items'               => $itemsByOrder[$id] ?? [],
            'subtotal'            => (float) $o['subtotal'],
            'deliveryFee'         => (float) $o['delivery_fee'],
            'total'               => (float) $o['total_amount'],
            'cancellationReason'  => $o['cancellation_reason'] ?? '',
            'termsAccepted'       => (bool) $o['terms_accepted'],
            'termsAcceptedAt'     => $o['terms_accepted_at']
                ? date(DATE_ATOM, strtotime((string) $o['terms_accepted_at']))
                : null,
        ];
    }
    return $result;
}
