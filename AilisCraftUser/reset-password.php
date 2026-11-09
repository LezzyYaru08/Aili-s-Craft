<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set New Password · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-frame app-frame--auth">
  <div class="hero-frame">
    <img class="hero-frame__bg" src="assets/images/bg-bouquet.png" alt=""
        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
    <div class="hero-frame__bg-fallback" style="display:none;"></div>
    <div class="hero-frame__content">
      <div data-ac-logo style="width:120px;height:120px;margin-top:40px;"></div>
      <h1 style="color:#fff;font-size:18px;letter-spacing:1.5px;margin-top:12px;text-align:center;">SET A NEW PASSWORD</h1>

      <!-- Invalid / expired token -->
      <div data-reset-error style="display:none;text-align:center;color:#fff;max-width:280px;">
        <p style="font-size:14px;line-height:1.6;" data-reset-error-msg>This reset link is invalid or has expired. Please request a new one.</p>
        <a class="btn btn-secondary" style="align-self:center;margin-top:8px;" href="forgot-password.php">REQUEST NEW RESET LINK</a>
      </div>

      <!-- Checking token -->
      <p data-reset-checking style="color:#fff;opacity:.9;font-size:13px;text-align:center;margin-top:12px;">Verifying your reset link…</p>

      <!-- Set new password form -->
      <form class="auth-form" data-reset-form novalidate style="display:none;">
        <div class="field">
          <input type="password" name="password" placeholder="NEW PASSWORD (min 8, upper+lower+number)" autocomplete="new-password" required minlength="8">
        </div>
        <div class="field">
          <input type="password" name="confirm" placeholder="CONFIRM NEW PASSWORD" autocomplete="new-password" required minlength="8">
        </div>
        <button type="submit" class="btn btn-block" data-reset-submit>RESET PASSWORD</button>
        <a class="text-link on-hero" style="align-self:center;" href="login.php">BACK TO LOGIN</a>
      </form>

      <!-- Success -->
      <div data-reset-success style="display:none;text-align:center;color:#fff;max-width:280px;">
        <p style="font-size:14px;line-height:1.6;">Your password has been reset successfully. You can now log in with your new password.</p>
        <a class="btn btn-block" style="align-self:center;margin-top:10px;max-width:240px;" href="login.php">GO TO LOGIN</a>
      </div>
    </div>
  </div>
</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
(function () {
  var token = new URLSearchParams(window.location.search).get('token') || '';
  var checking = document.querySelector('[data-reset-checking]');
  var errorBox = document.querySelector('[data-reset-error]');
  var errorMsg = document.querySelector('[data-reset-error-msg]');
  var form = document.querySelector('[data-reset-form]');
  var successBox = document.querySelector('[data-reset-success]');
  var btn = form ? form.querySelector('[data-reset-submit]') : null;

  function showError(msg) {
    checking.style.display = 'none';
    if (form) form.style.display = 'none';
    if (msg) errorMsg.textContent = msg;
    errorBox.style.display = 'block';
  }

  if (!token || !/^[0-9a-f]{64}$/.test(token)) {
    showError('This reset link is invalid or has expired. Please request a new one.');
    return;
  }

  // Verify the token before showing the form.
  fetch('api/auth.php?action=verify-reset-token&token=' + encodeURIComponent(token), {
    method: 'GET', headers: { 'Accept': 'application/json' }, cache: 'no-store'
  })
    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok && d.ok !== false, data: d }; }); })
    .then(function (res) {
      checking.style.display = 'none';
      if (!res.ok) {
        showError(res.data && res.data.error ? res.data.error : 'This reset link is invalid or has expired.');
        return;
      }
      form.style.display = 'flex';
    })
    .catch(function () {
      showError('Could not verify this reset link. Please check your connection and try again.');
    });

  if (!form) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var pw = form.querySelector('input[name="password"]').value || '';
    var cf = form.querySelector('input[name="confirm"]').value || '';
    var meetsMin = pw.length >= 8 && /[A-Z]/.test(pw) && /[a-z]/.test(pw) && /[0-9]/.test(pw);
    if (!meetsMin) {
      var msg = 'Password must be at least 8 characters and include uppercase, lowercase, and a number.';
      if (window.ACToast) { ACToast(msg, 'error'); }
      else { alert(msg); }
      return;
    }
    if (pw !== cf) {
      if (window.ACToast) { ACToast('Passwords do not match.', 'error'); }
      else { alert('Passwords do not match.'); }
      return;
    }
    if (btn) { btn.disabled = true; btn.textContent = 'RESETTING…'; }

    fetch('api/auth.php?action=reset', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ token: token, password: pw }),
      cache: 'no-store'
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok && d.ok !== false, data: d }; }); })
      .then(function (res) {
        if (!res.ok) throw new Error(res.data && res.data.error ? res.data.error : 'Could not reset password. Please try again.');
        form.style.display = 'none';
        successBox.style.display = 'block';
      })
      .catch(function (err) {
        var msg = err.message || 'Could not reset password.';
        if (/invalid|expired/i.test(msg)) {
          showError(msg);
        } else if (window.ACToast) {
          ACToast(msg, 'error');
        } else {
          alert(msg);
        }
      })
      .finally(function () {
        if (btn) { btn.disabled = false; btn.textContent = 'RESET PASSWORD'; }
      });
  });
})();
</script>
</body>
</html>
