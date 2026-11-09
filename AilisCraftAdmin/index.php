<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aili's Craft — System Administration</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <div class="brand-mark">A</div>
        <div><strong>Aili's Craft</strong><small>System Administration</small></div>
      </div>

      <nav class="nav" aria-label="Main navigation">
        <div class="nav-label">FLOWCHART</div>
        <button class="nav-item active" data-page="accounts"><span>◎</span> Manage Admin Accounts</button>
        <button class="nav-item" data-page="users"><span>◉</span> User Accounts</button>
        <button class="nav-item" data-page="maintenance"><span>⌘</span> System Maintenance</button>
        <div class="nav-label other-label">SYSTEM</div>
        <button class="nav-item" data-page="activity"><span>▤</span> Activity Log</button>
      </nav>

      <div class="sidebar-bottom">
        <div class="mini-profile">
          <div class="avatar" id="sidebarAvatar">SA</div>
          <div><strong id="sidebarName">System Admin</strong><small id="sidebarRole">Administrator</small></div>
        </div>
        <button class="logout-btn" id="logoutBtn">↪ Logout</button>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <button class="menu-toggle" id="menuToggle">☰</button>
        <div class="breadcrumb"><span>Admin System</span><b>/</b><strong id="breadcrumbCurrent">Manage Accounts</strong></div>
        <div class="top-actions">
          <button class="icon-btn" id="notificationBtn" title="System notifications">🔔<i id="notificationDot"></i></button>
          <div class="profile">
            <div class="avatar" id="topAvatar">SA</div>
            <div class="profile-text"><strong id="topName">System Admin</strong><small id="topRole">Administrator</small></div>
          </div>
        </div>
      </header>

      <div class="content">
        <section id="page-accounts" class="page active"></section>
        <section id="page-users" class="page"></section>
        <section id="page-maintenance" class="page"></section>
        <section id="page-source" class="page"></section>
        <section id="page-activity" class="page"></section>
      </div>
    </main>
  </div>

  <div class="toast-container" id="toastContainer"></div>
  <div class="modal-backdrop" id="modalBackdrop" aria-hidden="true">
    <div class="modal" id="modal" role="dialog" aria-modal="true"></div>
  </div>
  <script src="script.js"></script>
</body>
</html>
