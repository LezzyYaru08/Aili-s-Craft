<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us · Aili's Crochet PH</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu"><span></span><span></span><span></span></button>
    <span class="topbar__title">About Us</span>
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
  
  <main class="page-main" data-about-page>
    <section class="section">
      <div class="summary-card ac-about-card">
        <img src="assets/images/logo.png" class="ac-about-logo" alt="Aili's Crochet PH">
        <h1>About Aili's Crochet PH</h1>
        <p>Crochet and a Fuzzy Wire crafter!
Keychains, Bouquets, Wearable and Bags!</p>
        <p class="ac-muted">Have any questions, want to directly customize your wants, or need help with an existing order? Message the shop through its social channels.</p>
        <div class="ac-social-grid">
          <a class="btn" data-social="facebook" href="#" target="_blank" rel="noopener noreferrer">
            <i class="fa-brands fa-facebook-f"></i> Facebook
          </a>
          <a class="btn" data-social="instagram" href="#" target="_blank" rel="noopener noreferrer">
            <i class="fa-brands fa-instagram"></i> Instagram
          </a>
          <a class="btn" data-social="tiktok" href="#" target="_blank" rel="noopener noreferrer">
            <i class="fa-brands fa-tiktok"></i> TikTok
          </a>
        </div>

        <div class="summary-card ac-message-card" style="margin-top:20px;text-align:left;">
          <h2 style="font-size:16px;margin-bottom:4px;color:var(--ink);">Direct Message the Shop</h2>
          <p class="ac-muted" style="font-size:12px;margin-bottom:12px;">Have a custom idea or an order question? Send us a note and we'll get back to you.</p>
          <p class="ac-message-guest-note" style="display:none;font-size:12px;color:var(--ink-soft);margin-bottom:4px;">
            Please <a href="login.php" style="color:var(--pink-button);font-weight:700;">log in</a> to message the shop.
          </p>
          <form class="ac-message-form" data-message-form novalidate>
            <div class="field" style="margin-bottom:10px;">
              <input type="text" name="subject" placeholder="SUBJECT" maxlength="150" required autocomplete="off">
            </div>
            <div class="field" style="margin-bottom:12px;">
              <textarea name="message" placeholder="YOUR MESSAGE" maxlength="5000" rows="4" required
                style="width:100%;box-sizing:border-box;background:#fff8fb;border:1px solid var(--line,#f0d5e2);border-radius:10px;padding:10px 12px;font-size:13px;color:var(--ink,#2b2230);resize:vertical;font-family:inherit;"></textarea>
            </div>
            <button type="submit" class="btn btn-block">SEND MESSAGE</button>
          </form>
        </div>

        <p class="copyright">© 2023 Aili's Crochet PH. All rights reserved.</p>
      </div>
    </section>
  </main>
</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
(function () {
  var form = document.querySelector('[data-message-form]');
  var guestNote = document.querySelector('.ac-message-guest-note');
  if (!form) return;
  var profile = null;
  try { profile = JSON.parse(window.localStorage.getItem('ac_profile') || 'null'); } catch (e) {}
  var isAuthed = !!(profile && profile.authenticated);
  if (!isAuthed) {
    form.style.display = 'none';
    if (guestNote) guestNote.style.display = 'block';
    return;
  }
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var subjectEl = form.querySelector('[name="subject"]');
    var messageEl = form.querySelector('[name="message"]');
    var btn = form.querySelector('button[type="submit"]');
    var subject = (subjectEl.value || '').trim();
    var message = (messageEl.value || '').trim();
    if (!subject || !message) {
      if (window.ACToast) window.ACToast('Please fill in both the subject and your message.');
      return;
    }
    if (btn) { btn.disabled = true; btn.textContent = 'SENDING…'; }
    fetch('api/send_message.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify({subject: subject, message: message}),
      cache: 'no-store'
    }).then(function (r) { return r.json(); }).then(function (d) {
      if (!d || !d.ok) throw new Error((d && d.error) || 'Message could not be sent.');
      if (window.ACToast) window.ACToast('Message sent! We will get back to you soon.');
      form.reset();
    }).catch(function (err) {
      if (window.ACToast) window.ACToast(err.message || 'Message could not be sent. Please try again.');
    }).finally(function () {
      if (btn) { btn.disabled = false; btn.textContent = 'SEND MESSAGE'; }
    });
  });
})();
</script>
</body>
</html>
