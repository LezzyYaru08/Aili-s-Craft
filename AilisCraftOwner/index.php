<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aili's Craft — Owner Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div class="brand">
        <div class="brand-mark">A</div>
        <div>
          <strong>Aili's Craft</strong>
          <small>Owner Panel</small>
        </div>
      </div>

      <nav class="nav">
        <div class="nav-label">MENU</div>
        <button class="nav-item active" data-page="dashboard"><span>▦</span> Dashboard</button>
        <button class="nav-item" data-page="orders"><span>🛍</span> Orders <b class="nav-badge" id="pendingBadge">0</b></button>
        <button class="nav-item" data-page="products"><span>◈</span> Products</button>
        <button class="nav-item" data-page="customers"><span>♙</span> Customers</button>
        <button class="nav-item" data-page="payments"><span>₱</span> Payments</button>
        <button class="nav-item" data-page="reviews"><span>★</span> Reviews</button>

        <div class="nav-label other-label">OTHERS</div>
        <button class="nav-item" data-page="reports"><span>▤</span> Reports</button>
        <button class="nav-item" data-page="settings"><span>⚙</span> Settings</button>
      </nav>

      <div class="sidebar-bottom">
        <div class="mini-profile">
          <div class="avatar">AD</div>
          <div>
            <strong>Owneristrator</strong>
            <small>Aili's Craft</small>
          </div>
        </div>
        <button class="logout-btn" id="logoutBtn">↪ Logout</button>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <button class="menu-toggle" id="menuToggle">☰</button>
        <div class="search-wrap">
          <span>⌕</span>
          <input id="globalSearch" type="search" placeholder="Search orders, products, customers...">
        </div>
        <div class="top-actions">
          <button class="icon-btn" id="notificationBtn" title="Notifications">🔔<i></i></button>
          <div class="profile">
            <div class="avatar">AD</div>
            <div class="profile-text">
              <strong>Owner</strong>
              <small>Owneristrator</small>
            </div>
            <span>⌄</span>
          </div>
        </div>
      </header>

      <div class="content">
        <div id="page-dashboard" class="page active"></div>
        <div id="page-orders" class="page"></div>
        <div id="page-products" class="page"></div>
        <div id="page-customers" class="page"></div>
        <div id="page-payments" class="page"></div>
        <div id="page-reviews" class="page"></div>
        <div id="page-reports" class="page"></div>
        <div id="page-settings" class="page"></div>
      </div>
    </main>
  </div>

  <div class="toast-container" id="toastContainer"></div>
  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal" id="modal"></div>
  </div>

  <script src="script.js"></script>
</body>
</html>
