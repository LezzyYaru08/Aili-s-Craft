<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-frame app-frame--auth">
  <div class="hero-frame">
    <img class="hero-frame__bg" src="assets/images/bg-bouquet.png" alt=""
      onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
    <div class="hero-frame__bg-fallback" style="display:none;"></div>

    <div class="hero-frame__content">
      <div data-ac-logo style="width:160px;height:160px;"></div>

      <form class="auth-form" data-login-form novalidate>
        <div class="field">
          <input type="email" name="email" placeholder="EMAIL" autocomplete="email" required>
          <span class="field-error">Enter a valid email address</span>
        </div>
        <div class="field">
          <input type="password" name="password" placeholder="PASSWORD" autocomplete="current-password" required>
          <span class="field-error">Password must be at least 6 characters</span>
        </div>

        <div class="link-row">
          <a class="text-link on-hero" href="forgot-password.php">FORGOT PASSWORD?</a>
        </div>

        <button type="submit" class="btn btn-block">LOGIN</button>
        <a class="btn btn-secondary" style="align-self:center;" href="register.php">CREATE NEW ACCOUNT</a>
      </form>
    </div>
  </div>
</div>
<script src="main.js"></script>
<script src="features.js"></script>
</body>
</html>
