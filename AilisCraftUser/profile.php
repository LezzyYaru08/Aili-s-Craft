<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>

  <header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <span class="topbar__title">Profile</span>
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
    <div class="profile-card">
      <div class="profile-card__avatar" data-profile-initial>A</div>
      <div>
        <div style="font-family:var(--font-display); font-size:16px;" data-profile-name>Your Name</div>
        <div style="font-size:12px; color:var(--ink-soft);" data-profile-email>you@email.com</div>
      </div>
    </div>

    <section class="section">
      <div class="section__head"><h2 class="section__title">Account details</h2></div>
      <form class="summary-card" style="display:flex; flex-direction:column; gap:14px;" data-profile-form>
        <div class="field">
          <label style="font-size:12px; color:var(--ink-soft);" for="profileFullName">Full name</label>
          <input type="text" id="profileFullName" placeholder="Full name">
        </div>
        <div class="field">
          <label style="font-size:12px; color:var(--ink-soft);" for="profileEmail">Email</label>
          <input type="email" id="profileEmail" placeholder="Email" disabled>
        </div>
        <button type="submit" class="btn btn-block">SAVE CHANGES</button>
      </form>
    </section>

    <section class="section">
      <div class="section__head"><h2 class="section__title">Quick links</h2></div>
      <a class="history-item" href="history.php"><span class="history-item__id">Order history</span><span>›</span></a>
      <a class="history-item" href="order-summary.php"><span class="history-item__id">My bag</span><span>›</span></a>
      <a class="history-item" href="logout.php"><span class="history-item__id" style="color:var(--danger);">Log out</span><span>›</span></a>
    </section>
  </main>

</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
  (function () {
    var profile = window.ACStore.get('ac_profile', { fullName: '', email: '' });
    var nameEl = document.querySelector('[data-profile-name]');
    var emailEl = document.querySelector('[data-profile-email]');
    var initialEl = document.querySelector('[data-profile-initial]');
    var nameInput = document.getElementById('profileFullName');
    var emailInput = document.getElementById('profileEmail');

    function paint() {
      var display = profile.fullName || 'Add your name';
      nameEl.textContent = display;
      emailEl.textContent = profile.email || '—';
      initialEl.textContent = (profile.fullName || 'A').trim().charAt(0).toUpperCase();
      nameInput.value = profile.fullName || '';
      emailInput.value = profile.email || '';
    }
    paint();

    document.querySelector('[data-profile-form]').addEventListener('submit', function (e) {
      e.preventDefault();
      profile.fullName = nameInput.value.trim();
      window.ACStore.set('ac_profile', profile);
      paint();
      window.ACToast('Profile updated');
    });
  })();
</script>
</body>
</html>
