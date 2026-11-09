<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aili's Craft — Administrator Login</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="login-card">
      <div class="login-brand">
        <div class="brand-mark">A</div>
        <div><strong>Aili's Craft</strong><small>System Administration</small></div>
      </div>
      <div class="login-heading">
        <span class="eyebrow">ADMINISTRATOR ACCESS</span>
        <h1>Welcome back</h1>
        <p>Sign in to manage administrator accounts and maintain the system.</p>
      </div>
      <form id="loginForm" novalidate>
        <div class="login-field"><label for="username">Username or Email</label><input id="username" autocomplete="username" required autofocus></div>
        <div class="login-field"><label for="password">Password</label><input id="password" type="password" autocomplete="current-password" required></div>
        <label class="remember"><input id="remember" type="checkbox" checked> <span>Keep me signed in</span></label>
        <button class="login-submit" type="submit">Sign In</button>
        <p id="loginError" class="login-error" role="alert"></p>  
      </form>
      <div class="login-demo"><strong>Database administrator account required</strong><span>Use an active Administrator or Manager account stored in admin_accounts.</span></div>
    </section>
  </main>
  <script src="login.js"></script>
</body>
</html>
