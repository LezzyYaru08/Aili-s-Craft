<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Summary · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>

  <header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <span class="topbar__title">Order Summary</span>
    <a class="topbar__logo-link" href="home.php" aria-label="Go to home" data-ac-logo></a>
  </header>

  <nav class="nav-drawer" data-drawer aria-hidden="true">
    <div class="nav-drawer__scrim"></div>
    <div class="nav-drawer__panel">
      <button class="nav-drawer__close" data-drawer-close aria-label="Close menu">&times;</button>
      <div class="nav-drawer__brand"><img src="assets/images/logo.png" alt="Aili's Crochet PH" class="drawer__logo-img"><span class="nav-drawer__brand-name">Aili's Crochet PH</span></div>
      <div class="nav-drawer__section-label">Main</div>
      <a class="nav-drawer__link" href="home.php"><span aria-hidden="true">⌂</span> Home</a>
      <a class="nav-drawer__link" href="order.php"><span aria-hidden="true">✿</span> Shop</a>
      <a class="nav-drawer__link" href="customize.php"><span aria-hidden="true">✎</span> Customize</a>
      <a class="nav-drawer__link" href="order-summary.php"><span aria-hidden="true">▣</span> Bag</a>
      <div class="nav-drawer__section-label">Account</div>
      <a class="nav-drawer__link" href="profile.php"><span aria-hidden="true">♙</span> Profile</a>
      <a class="nav-drawer__link" href="history.php">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        History
      </a>
      <a class="nav-drawer__link" href="about.php">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><circle cx="12" cy="7.5" r=".5" fill="currentColor"/></svg>
        About Us
      </a>
      <a class="nav-drawer__link danger" href="logout.php">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        Logout
      </a>
    </div>
  </nav>
  
<body>
<div class="app-frame">



  <main class="page-main">

    <section class="section">
      <div class="section__head"><h2 class="section__title">Your bag</h2></div>
      <div data-cart-list></div>

      <a class="add-more-card" href="home.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Add another item
      </a>
    </section>

    <section class="section">
      <div class="section__head"><h2 class="section__title">Delivery details</h2></div>
      <div class="summary-card" style="display:flex; flex-direction:column; gap:12px;">
        <div class="field">
          <input type="text" placeholder="Delivery address" required>
        </div>
        <div class="field">
          <input type="tel" placeholder="Contact number" maxlength="11" data-numeric="true" required inputmode="numeric">
        </div>
        <div class="field">
          <input type="date" id="delivery-date" name="deliveryDate" required aria-describedby="delivery-date-error">
          <small id="delivery-date-error" class="delivery-date-error" role="alert" style="display:none;color:#b42318;"></small>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="section__head"><h2 class="section__title">Payment details</h2></div>
      <div class="summary-card ac-payment-card" style="display:flex; flex-direction:column; gap:12px;">
        <div class="field">
          <input type="text" name="payerFullName" placeholder="Full name on payment">
          <span class="payment-note"><span class="dot"></span>Auto-filled from your account when available — feel free to edit.</span>
        </div>

        <div class="payment-option-list" role="radiogroup" aria-label="Payment method">
          <label class="payment-option" for="pay-cod">
            <input type="radio" name="payMethod" id="pay-cod" value="cod_30_gcash_70_cod" checked>
            <span class="payment-option__radio" aria-hidden="true"></span>
            <span class="payment-option__content">
              <strong>COD (30% GCash + 70% COD)</strong>
              <small>Pay 30% upfront via GCash, then the remaining 70% in cash upon delivery.</small>
            </span>
          </label>

          <label class="payment-option" for="pay-gcash-full">
            <input type="radio" name="payMethod" id="pay-gcash-full" value="gcash_full">
            <span class="payment-option__radio" aria-hidden="true"></span>
            <span class="payment-option__content">
              <strong>Full Payment via GCash</strong>
              <small>Pay the complete order total upfront through GCash.</small>
            </span>
          </label>
        </div>

        <div class="gcash-payment-box ac-payment-summary-box" id="gcash-payment-box" data-gcash-payment-summary>
          <div class="gcash-payment-box__title">GCash payment required</div>
          <p class="gcash-payment-box__hint" data-payment-instruction>
            A 30% GCash down payment is required to confirm this COD order.
          </p>

          <div class="ac-payment-amount-highlight">
            <span>Required upfront GCash</span>
            <strong data-upfront-gcash-amount>₱0</strong>
          </div>

          <div class="ac-down-payment-grid ac-down-payment-grid--fixed">
            <div class="ac-payment-stat">
              <span>GCash upfront</span>
              <strong data-upfront-gcash-percent>30%</strong>
            </div>
            <div class="ac-payment-stat">
              <span>Remaining on delivery</span>
              <strong data-remaining-balance>₱0</strong>
            </div>
          </div>

          <p class="gcash-payment-box__note" data-payment-note>
            The GCash transaction will be initialized by the payment service in the next checkout step.
          </p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="terms-check-card">
        <label class="terms-check">
          <input type="checkbox" data-terms-checkbox>
          <span>I agree to the <button type="button" class="terms-link" data-terms-open>Terms &amp; Conditions</button> and Cancellation &amp; Refund Policy.</span>
        </label>
      </div>
    </section>

    <section class="section">
      <div class="section__head"><h2 class="section__title">Total</h2></div>
      <div class="summary-card">
        <div class="summary-row"><span>Subtotal</span><span data-sum-subtotal>₱0</span></div>
        <div class="summary-row"><span>Delivery fee</span><span data-sum-delivery>₱0</span></div>
        <div class="summary-row total"><span>Total</span><span data-sum-total>₱0</span></div>
      </div>
    </section>

  </main>

  <div class="sticky-cta">
    <div class="sticky-cta__total">
      <span class="sticky-cta__total-label">Total</span>
      <span class="sticky-cta__total-value" data-sum-total>₱0</span>
    </div>
    <button class="btn" data-place-order>PLACE ORDER</button>
  </div>

  <div class="modal-scrim" id="termsModal" aria-hidden="true">
    <div class="modal-card modal-card--wide terms-modal">
      <h2 class="modal-title-neutral">Terms &amp; Conditions</h2>
      <div class="policy-text policy-scroll">
        <p><strong>Consumer protection.</strong> Aili's Crochet PH aims to provide clear product descriptions, prices, order information, and fair treatment in consumer transactions consistent with the Consumer Act of the Philippines (R.A. 7394).</p>
        <p><strong>Online transactions.</strong> Orders placed through this system are internet transactions and are handled with the objective of providing clear transaction information, consumer remedies, and a responsive redress process consistent with the Internet Transactions Act of 2023 (R.A. 11967) and its implementing rules.</p>
        <p><strong>Order cancellation.</strong> Customers should cancel promptly when cancellation is permitted. Under R.A. 11967, an online consumer generally may not cancel a confirmed order when the goods have already been paid for and are in a prohibited cancellation stage, including when goods are perishable and already with a third-party delivery service or in transit, except for the circumstances allowed by law or when the parties otherwise agree.</p>
        <p><strong>Refunds.</strong> Cancellation does not automatically create an immediate refund. Where a refund is legally or contractually due, it will be processed using the merchant's approved payment/refund process, subject to applicable law, transaction terms, and any lawful delivery or third-party charges. Defective, non-conforming, or otherwise legally remediable transactions may be subject to repair, replacement, refund, or other remedies under applicable law.</p>
        <p><strong>Fair and accurate information.</strong> Product descriptions, prices, customization options, and payment requirements should be presented accurately. The system does not waive rights or remedies that cannot lawfully be waived.</p>
        <p><strong>DTI policy reference.</strong> This system is intended to observe applicable DTI consumer/e-commerce rules, including the DTI Administrative Order No. 21-01 identified for this project, to the extent applicable. The exact issuance referenced by this project should be confirmed against the current official DTI issuance before production deployment.</p>
        <p class="policy-source-note">Legal references: R.A. 7394 (Consumer Act of the Philippines), R.A. 11967 (Internet Transactions Act of 2023), and applicable DTI issuances and IRRs.</p>
      </div>
      <button type="button" class="btn btn-block" data-terms-close>CLOSE</button>
    </div>
  </div>

  <!-- Success pop-up — blurred backdrop per spec -->
  <div class="modal-scrim" id="successModal">
    <div class="modal-card">
      <div class="modal-card__icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none"><path d="M4 12.5L9 17.5L20 6.5" stroke="#58b06a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <h2>Order Saved</h2>
      <p>Your order details have been saved. GCash payment confirmation will be completed in the payment step.</p>
      <button class="btn btn-block" data-order-continue data-modal-close>CONTINUE</button>
    </div>
  </div>
</main>
</div>
<script>
/* Keep the order confirmation modal attached to the viewport, not page wrappers. */
(function () {
  var successModal = document.getElementById('successModal');
  if (successModal && successModal.parentNode !== document.body) {
    document.body.appendChild(successModal);
  }
})();
</script>

<script src="main.js"></script>
<script src="features.js"></script>

<script>
(function () {
  var dateInput = document.getElementById('delivery-date');
  var error = document.getElementById('delivery-date-error');
  if (!dateInput) return;

  function localISODate(date) {
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
  }
  function tomorrowISODate() {
    var tomorrow = new Date();
    tomorrow.setHours(0, 0, 0, 0);
    tomorrow.setDate(tomorrow.getDate() + 1);
    return localISODate(tomorrow);
  }
  function validateDeliveryDate(clearInvalid) {
    var minDate = tomorrowISODate();
    dateInput.min = minDate;
    if (dateInput.value && dateInput.value < minDate) {
      error.textContent = 'Please choose a delivery date starting tomorrow. Today and past dates are not allowed.';
      error.style.display = 'block';
      dateInput.setCustomValidity(error.textContent);
      if (clearInvalid) dateInput.value = '';
      return false;
    }
    error.textContent = '';
    error.style.display = 'none';
    dateInput.setCustomValidity('');
    return true;
  }

  validateDeliveryDate(false);
  dateInput.addEventListener('change', function () { validateDeliveryDate(false); });
  dateInput.addEventListener('input', function () { validateDeliveryDate(false); });
  dateInput.addEventListener('blur', function () { validateDeliveryDate(true); });
  dateInput.addEventListener('focus', function () { dateInput.min = tomorrowISODate(); });

  var placeOrder = document.querySelector('[data-place-order]');
  if (placeOrder) {
    placeOrder.addEventListener('click', function (event) {
      if (!validateDeliveryDate(false) || !dateInput.value) {
        event.preventDefault();
        dateInput.reportValidity();
        dateInput.focus();
      }
    }, true);
  }
})();
</script>

</body>
</html>
