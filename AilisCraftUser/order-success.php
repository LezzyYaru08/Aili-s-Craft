<?php
// Messenger opt-in is rendered only when both FB_APP_ID and FB_PAGE_ID are
// configured via env (see api/messenger_config.php). Otherwise this page is
// byte-identical to the non-Messenger version (graceful no-op).
require_once __DIR__ . '/api/messenger_config.php';
$fbOptInEnabled = (FB_APP_ID !== '' && FB_PAGE_ID !== '');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php if ($fbOptInEnabled): ?><div id="fb-root"></div><?php endif; ?>
<div class="app-frame app-frame--auth">
  <div class="result-frame">
    <div style="position:absolute; top:20px; right:20px; width:40px; height:40px;">
      <a href="home.php" aria-label="Go to home" data-ac-logo></a>
    </div>
    <div class="result-frame__icon">
      <svg width="46" height="46" viewBox="0 0 24 24" fill="none"><path d="M4 12.5L9 17.5L20 6.5" stroke="#eb6e9b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <h1>Order Confirmation</h1>
    <p data-confirmation-copy>Thank you for shopping with Aili's Crochet PH. Your order has been received and is awaiting payment confirmation.</p>
    <div class="ac-payment-success-message" data-payment-success hidden aria-live="polite">
      <strong>Payment Successful</strong>
      <span>Your GCash payment has been confirmed.</span>
    </div>
    <span class="order-id">Order <span data-order-id>AC-000000</span></span>

<?php if ($fbOptInEnabled): ?>
    <!-- Messenger opt-in — requires live HTTPS site + whitelisted domain in Meta App dashboard -->
    <div style="margin-top:24px; width:100%; max-width:280px; text-align:center;">
      <p style="font-size:12px; color:#6b5c68; margin-bottom:10px;">Get order updates on Messenger (Preparing &rarr; Shipped &rarr; Delivered):</p>
      <div id="fb-messenger-optin"
           class="fb-send-to-messenger"
           messenger_app_id="<?= htmlspecialchars(FB_APP_ID, ENT_QUOTES, 'UTF-8') ?>"
           page_id="<?= htmlspecialchars(FB_PAGE_ID, ENT_QUOTES, 'UTF-8') ?>"
           data-ref=""
           color="blue"
           size="standard"
           cta_text="GET_ORDER_UPDATES"
           style="display:inline-block;"></div>
    </div>
<?php endif; ?>

    <div style="display:flex; flex-direction:column; gap:12px; margin-top:32px; width:100%; max-width:280px;">
      <a class="btn btn-block" href="history.php">VIEW ORDER STATUS</a>
      <a class="btn btn-ghost btn-block" href="home.php">BACK TO HOME</a>
    </div>
  </div>
</div>
<script src="main.js"></script>
<script src="features.js"></script>
<?php if ($fbOptInEnabled): ?>
<script>
/* Facebook SDK + Messenger opt-in. Values come from env via messenger_config.php.
   Only renders on a live HTTPS domain with the domain whitelisted in your
   Meta App dashboard. On localhost it stays hidden (graceful no-op). */
window.fbAsyncInit = function () {
  if (typeof FB !== 'undefined') {
    FB.init({ appId: '<?= htmlspecialchars(FB_APP_ID, ENT_QUOTES, 'UTF-8') ?>', xfbml: true, version: '<?= htmlspecialchars(FB_API_VERSION, ENT_QUOTES, 'UTF-8') ?>' });
    // Set data-ref = order code, then re-render the plugin.
    var orderEl = document.querySelector('[data-order-id]');
    var btn = document.getElementById('fb-messenger-optin');
    if (orderEl && btn) {
      var code = (orderEl.textContent || '').trim();
      if (code && code !== 'AC-000000') {
        btn.setAttribute('data-ref', code);
        FB.XFBML.parse(btn.parentElement);
      }
    }
  }
};
(function (d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk.js';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
<?php endif; ?>
</body>
</html>
