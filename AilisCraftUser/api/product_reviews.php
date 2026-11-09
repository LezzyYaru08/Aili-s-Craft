<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}

try {
    $pdo = db();
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $action = (string)($_GET['action'] ?? 'list');

    if ($action === 'owner-list') {
        if (empty($_SESSION['ac_staff']['id']) || strtolower((string)($_SESSION['ac_staff']['role'] ?? '')) !== 'owner') {
            throw new ApiError('Owner sign-in is required.', 401);
        }
        require_method($method, 'GET');
        $stmt = $pdo->query(
            'SELECT r.id, r.rating, r.review_text, r.created_at,
                    p.name AS product_name, p.product_code,
                    u.full_name AS customer_name, u.email AS customer_email,
                    o.order_code
               FROM product_reviews r
               JOIN products p ON p.id = r.product_id
               JOIN users u ON u.id = r.user_id
               JOIN order_items oi ON oi.id = r.order_item_id
               JOIN orders o ON o.id = oi.order_id
              ORDER BY r.created_at DESC, r.id DESC'
        );
        json_out(['ok' => true, 'reviews' => array_map(static function (array $r): array {
            return [
                'id' => (int)$r['id'], 'rating' => (int)$r['rating'],
                'text' => (string)($r['review_text'] ?? ''),
                'product' => (string)$r['product_name'], 'productCode' => (string)$r['product_code'],
                'customer' => (string)$r['customer_name'], 'email' => (string)$r['customer_email'],
                'orderCode' => (string)$r['order_code'], 'date' => (string)$r['created_at']
            ];
        }, $stmt->fetchAll())]);
    }

    $code = clean_str($_GET['product_code'] ?? '', 60);
    if ($code === '') throw new ApiError('A product code is required.');
    $productStmt = $pdo->prepare('SELECT id, product_code, name FROM products WHERE product_code = ? AND is_active = 1 LIMIT 1');
    $productStmt->execute([$code]);
    $product = $productStmt->fetch();
    if (!$product) throw new ApiError('This product is not available in the database yet.', 404);

    if ($method === 'GET') {
        if ($action !== 'list') throw new ApiError('Unknown review action.', 404);
        $reviewsStmt = $pdo->prepare(
            'SELECT r.rating, r.review_text, r.created_at, u.full_name AS customer_name
               FROM product_reviews r JOIN users u ON u.id = r.user_id
              WHERE r.product_id = ? ORDER BY r.created_at DESC, r.id DESC'
        );
        $reviewsStmt->execute([(int)$product['id']]);
        $reviews = array_map(static function (array $r): array {
            $name = trim((string)$r['customer_name']);
            $parts = preg_split('/\s+/', $name) ?: [];
            $displayName = count($parts) > 1 ? $parts[0] . ' ' . strtoupper(substr((string)end($parts), 0, 1)) . '.' : $name;
            return ['rating' => (int)$r['rating'], 'text' => (string)($r['review_text'] ?? ''), 'customer' => $displayName, 'date' => (string)$r['created_at']];
        }, $reviewsStmt->fetchAll());
        $eligible = [];
        if (!empty($_SESSION['ac_user']['id'])) {
            $eligibleStmt = $pdo->prepare(
                "SELECT oi.id AS order_item_id, o.order_code, o.status
                   FROM order_items oi JOIN orders o ON o.id = oi.order_id
                  WHERE oi.product_id = ? AND o.user_id = ?
                   AND LOWER(o.status) IN ('delivered','preparing','shipped','out for delivery')
                    AND NOT EXISTS (SELECT 1 FROM product_reviews r WHERE r.order_item_id = oi.id)
                  ORDER BY o.created_at DESC"
            );
            $eligibleStmt->execute([(int)$product['id'], (int)$_SESSION['ac_user']['id']]);
            $eligible = array_map(static fn(array $r): array => ['orderItemId' => (int)$r['order_item_id'], 'orderCode' => (string)$r['order_code']], $eligibleStmt->fetchAll());
        }
        $count = count($reviews);
        $average = $count ? array_sum(array_column($reviews, 'rating')) / $count : 0;
        json_out(['ok' => true, 'product' => ['id' => (int)$product['id'], 'code' => (string)$product['product_code'], 'name' => (string)$product['name']], 'reviews' => $reviews, 'count' => $count, 'average' => round($average, 1), 'eligible' => $eligible, 'loggedIn' => !empty($_SESSION['ac_user']['id'])]);
    }

    if ($method === 'POST' && $action === 'submit') {
        if (empty($_SESSION['ac_user']['id'])) throw new ApiError('Please log in to submit a review.', 401);
        $in = read_json_body();
        $orderItemId = filter_var($in['orderItemId'] ?? null, FILTER_VALIDATE_INT);
        $rating = filter_var($in['rating'] ?? null, FILTER_VALIDATE_INT);
        $reviewText = clean_str($in['reviewText'] ?? '', 2000);
        if (!$orderItemId || !$rating || $rating < 1 || $rating > 5) throw new ApiError('Choose a rating from 1 to 5 stars.');
        $eligibleStmt = $pdo->prepare(
            "SELECT oi.id FROM order_items oi JOIN orders o ON o.id = oi.order_id
              WHERE oi.id = ? AND oi.product_id = ? AND o.user_id = ?
                AND LOWER(o.status) IN ('delivered','preparing','shipped','out for delivery') LIMIT 1"
        );
        $eligibleStmt->execute([$orderItemId, (int)$product['id'], (int)$_SESSION['ac_user']['id']]);
        if (!$eligibleStmt->fetchColumn()) throw new ApiError('Reviews are available only for products from your completed or delivered orders.', 403);
        $save = $pdo->prepare(
            'INSERT INTO product_reviews (order_item_id, product_id, user_id, rating, review_text)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text), updated_at = CURRENT_TIMESTAMP'
        );
        $save->execute([$orderItemId, (int)$product['id'], (int)$_SESSION['ac_user']['id'], $rating, $reviewText]);
        json_out(['ok' => true, 'message' => 'Your product review has been saved.']);
    }
    throw new ApiError('Method not allowed.', 405);
} catch (ApiError $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    error_log('[AiliCraft product reviews] ' . $e);
    json_out(['ok' => false, 'error' => AC_DEBUG ? $e->getMessage() : 'The review service is temporarily unavailable.'], 500);
}
