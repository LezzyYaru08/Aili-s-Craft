<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aili's Craft — Owner Login</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="login-card" aria-labelledby="loginTitle">
      <div class="login-brand">
        <div class="brand-mark">A</div>
        <div><strong>Aili's Craft</strong><small>Owner Panel</small></div>
      </div>
      <div class="login-heading">
        <h1 id="loginTitle">Welcome back</h1>
        <p>Sign in to manage Aili's Craft orders and products.</p>
      </div>
      <form id="loginForm" novalidate>
        <div class="login-field"><label for="username">Username or Email</label><input id="username" name="username" autocomplete="username" required></div>
        <div class="login-field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <label class="remember"><input id="remember" type="checkbox" checked> <span>Keep me signed in</span></label>
        <button class="login-submit" type="submit">Sign In</button>
        <p id="loginError" class="login-error" role="alert" aria-live="polite"></p>
      </form>
      <div class="login-demo"><strong>Authorized owner account required</strong><span>Sign in with an active Owner account registered in the database.</span></div>
    </section>
  </main>
<script src="login.js"></script>
</body>
</html>