<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password · Aili's Crochet PH</title>
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
      <h1 style="color:#fff;font-size:18px;letter-spacing:1.5px;margin-top:12px;text-align:center;">RESET YOUR PASSWORD</h1>
      <p style="color:#fff;opacity:.9;font-size:13px;text-align:center;max-width:260px;margin-top:4px;">Enter the email on your account and we'll send you a reset link.</p>

      <form class="auth-form" data-forgot-form novalidate>
        <div class="field">
          <input type="email" name="email" placeholder="EMAIL" autocomplete="email" required>
        </div>
        <button type="submit" class="btn btn-block" data-forgot-submit>SEND RESET LINK</button>
        <a class="text-link on-hero" style="align-self:center;" href="login.php">BACK TO LOGIN</a>
      </form>

      <div data-forgot-success style="display:none;text-align:center;color:#fff;max-width:280px;">
        <p style="font-size:14px;line-height:1.6;">If an account exists for that email, a reset link has been sent. Check your inbox (and spam folder) — the link expires in 60 minutes.</p>
        <a class="text-link on-hero" href="login.php">BACK TO LOGIN</a>
      </div>
    </div>
  </div>
</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
(function () {
  var form = document.querySelector('[data-forgot-form]');
  var successBox = document.querySelector('[data-forgot-success]');
  var btn = form ? form.querySelector('[data-forgot-submit]') : null;
  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var email = (form.querySelector('input[name="email"]').value || '').trim();
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      if (window.ACToast) { ACToast('Please enter a valid email address.', 'error'); }
      else { alert('Please enter a valid email address.'); }
      return;
    }
    if (btn) { btn.disabled = true; btn.textContent = 'SENDING…'; }

    fetch('api/auth.php?action=forgot', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email }),
      cache: 'no-store'
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok && d.ok !== false, data: d }; }); })
      .then(function (res) {
        if (!res.ok) throw new Error(res.data && res.data.error ? res.data.error : 'Could not send reset link. Please try again.');
        form.style.display = 'none';
        successBox.style.display = 'block';
        // Localhost convenience: if debug mode is on, the API returns the link directly.
        if (res.data && res.data.debugResetLink) {
          var hint = document.createElement('p');
          hint.style.cssText = 'font-size:11px;opacity:.85;margin-top:12px;word-break:break-all;';
          hint.innerHTML = 'Debug mode — reset link: <a href="' + res.data.debugResetLink + '" style="color:#fff;text-decoration:underline;">' + res.data.debugResetLink + '</a>';
          successBox.appendChild(hint);
        }
      })
      .catch(function (err) {
        if (window.ACToast) { ACToast(err.message || 'Could not send reset link.', 'error'); }
        else { alert(err.message || 'Could not send reset link.'); }
      })
      .finally(function () {
        if (btn) { btn.disabled = false; btn.textContent = 'SEND RESET LINK'; }
      });
  });
})();
</script>
</body>
</html>
