<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-frame app-frame--auth">
  <div class="hero-frame">
    <img class="hero-frame__bg" src="assets/images/bg-bouquet.png" alt=""
      onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
    <div class="hero-frame__bg-fallback" style="display:none;"></div>

    <div class="hero-frame__content">
      <div data-ac-logo style="width:120px;height:120px;margin-top:32px;"></div>
      <h1 style="color:#fff;font-size:20px;letter-spacing:1.5px;margin: 10% 0 0;">CREATE ACCOUNT</h1>

      <form class="auth-form" data-register-form novalidate>
        <div class="field">
          <input type="text" name="fullName" placeholder="FULL NAME" autocomplete="name" required>
          <span class="field-error">Please enter your full name</span>
        </div>
        <div class="field">
          <input type="email" name="email" placeholder="EMAIL" autocomplete="email" required>
          <span class="field-error">Enter a valid email address</span>
        </div>
        <div class="field">
          <input type="password" name="password" placeholder="PASSWORD" autocomplete="new-password" required data-register-password>
          <span class="field-error">Password must be at least 8 characters and include uppercase, lowercase, and a number.</span>
          <div class="password-meter" data-password-meter hidden>
            <div class="password-meter__track"><div class="password-meter__bar" data-password-meter-bar></div></div>
            <span class="password-meter__label" data-password-meter-label></span>
          </div>
        </div>
        <div class="field">
          <input type="password" name="confirmPassword" placeholder="CONFIRM PASSWORD" autocomplete="new-password" required>
          <span class="field-error">Passwords must match</span>
        </div>

        <div class="terms-check-card register-terms-card">
          <label class="terms-check">
            <input type="checkbox" name="termsAccepted" data-register-terms-checkbox required>
            <span>I have read and agree to the <button type="button" class="terms-link" data-register-terms-open>Terms &amp; Conditions</button> and acknowledge the Privacy Notice.</span>
          </label>
          <span class="terms-field-error" data-register-terms-error>Please agree before creating your account.</span>
        </div>

        <button type="submit" class="btn btn-block" data-register-submit disabled>REGISTER</button>
        <a class="btn btn-secondary" style="align-self:center;" href="login.php">ALREADY HAVE AN ACCOUNT?</a>
      </form>
    </div>
  </div>


  <div class="modal-scrim" id="registerTermsModal" aria-hidden="true">
    <div class="modal-card modal-card--wide terms-modal">
      <h2 class="modal-title-neutral">Aili's Craft Terms &amp; Conditions</h2>
      <div class="policy-text policy-scroll">
        <p><strong>1. Acceptance of these Terms.</strong> By creating an Aili's Craft account, you agree to use the website honestly and in accordance with these Terms, applicable transaction rules, and Philippine law. If you do not agree, you cannot create an account.</p>
        <p><strong>2. Account information.</strong> You must provide information that is accurate and reasonably up to date. You are responsible for keeping your account credentials confidential and for activity carried out through your account, subject to applicable law.</p>
        <p><strong>3. Products, customization, and prices.</strong> Product descriptions, available customization choices, quantities, prices, and applicable fees will be presented through the system. Customized products are limited to the options supported by Aili's Craft. The merchant will not use these Terms to remove consumer rights that cannot lawfully be waived.</p>
        <p><strong>4. Orders and payment.</strong> An order request is subject to confirmation and the transaction details shown at checkout. Available payment methods, required payment amounts, delivery charges, and other transaction information should be reviewed before placing an order. Electronic transaction records may be used to document the transaction.</p>
        <p><strong>5. Cancellation, refunds, and remedies.</strong> Cancellation is subject to the order status, the transaction terms, and applicable Philippine law. Under R.A. 11967, online consumers generally cannot cancel a confirmed order in certain stages, including where paid goods are already with a third-party delivery service or in transit, except for circumstances allowed by law or agreed by the parties. Where a refund, replacement, repair, or other remedy is legally or contractually due, it will be handled in accordance with applicable law and the transaction circumstances.</p>
        <p><strong>6. Consumer protection.</strong> Aili's Craft aims to provide clear and accurate information and fair treatment in online transactions consistent with the Consumer Act of the Philippines (R.A. 7394) and the Internet Transactions Act of 2023 (R.A. 11967).</p>
        <p><strong>7. Privacy Notice.</strong> Aili's Craft may process information you provide, such as your name, email address, contact details, delivery information, account information, and transaction records, for account creation, order processing, customer support, delivery, payment-related processing, security, recordkeeping, and other lawful purposes disclosed to you. Personal information will be processed in accordance with R.A. 10173, its implementing rules, and applicable National Privacy Commission issuances. You retain applicable data-subject rights, including rights concerning access and correction and the right to lodge a complaint with the National Privacy Commission.</p>
        <p><strong>8. Electronic transactions and records.</strong> Electronic documents, records, and communications may be used in connection with your account and orders, consistent with the Electronic Commerce Act of 2000 (R.A. 8792) and other applicable laws.</p>
        <p><strong>9. Prohibited use.</strong> You must not use the system for fraud, unauthorized access, abusive activity, false orders, unlawful transactions, or attempts to interfere with the security or operation of the website.</p>
        <p><strong>10. Complaints and redress.</strong> Customers may first contact Aili's Craft through its available support channels regarding transaction concerns. Applicable online-transaction redress mechanisms and remedies under Philippine law remain available.</p>
        <p><strong>11. Changes to these Terms.</strong> Aili's Craft may update these Terms when reasonably necessary, including to reflect changes in the system, business practices, or applicable law. Material changes should be presented to users appropriately. The version accepted at registration will be recorded with the account where supported by the system.</p>
        <p><strong>12. Governing law.</strong> These Terms are intended for transactions governed by Philippine law. If a provision is invalid or unenforceable, the remaining provisions will continue to apply to the extent permitted by law.</p>
        <p class="policy-source-note">Legal references: R.A. 11967 (Internet Transactions Act of 2023), R.A. 7394 (Consumer Act of the Philippines), R.A. 10173 (Data Privacy Act of 2012), R.A. 8792 (Electronic Commerce Act of 2000), and applicable implementing rules and government issuances. This text is a system-use template and should be reviewed by the business's legal/privacy adviser before production use.</p>
      </div>
      <button type="button" class="btn btn-block" data-register-terms-close>CLOSE</button>
    </div>
  </div>

</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
(function () {
  var pwInput = document.querySelector('[data-register-password]');
  var meter = document.querySelector('[data-password-meter]');
  var bar = document.querySelector('[data-password-meter-bar]');
  var label = document.querySelector('[data-password-meter-label]');
  if (!pwInput || !meter || !bar || !label) return;
  function update() {
    var strength = window.ACPasswordStrength ? window.ACPasswordStrength(pwInput.value) : null;
    if (!strength || strength.level === 'empty') { meter.hidden = true; return; }
    meter.hidden = false;
    meter.className = 'password-meter is-' + strength.level;
    bar.style.width = (strength.level === 'weak' ? '33%' : strength.level === 'medium' ? '66%' : '100%');
    label.textContent = strength.label + (strength.message ? ' — ' + strength.message : '');
  }
  pwInput.addEventListener('input', update);
  update();
})();
</script>
</body>
</html>
