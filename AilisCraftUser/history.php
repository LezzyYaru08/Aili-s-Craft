<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>History · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>

<header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <span class="topbar__title">History</span>
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
    <section class="section">
      <div class="section__head"><h2 class="section__title">Your orders</h2></div>
      <div data-history-list></div>

      <div class="policy-card">
        <strong>Cancellation &amp; Refund Policy</strong>
        <p>Orders may be cancelled only when cancellation is still permitted by the order status and applicable law. Orders that are already in transit, delivered, or otherwise beyond the permitted cancellation stage may not be cancellable. If a GCash payment has already been made, cancellation does not automatically mean an immediate refund; any refund is subject to the order status, applicable refund rights, delivery or third-party costs, and the applicable terms of the transaction. Refunds for eligible payments should be processed through the merchant’s approved payment/refund process.</p>
      </div>
    </section>
  </main>

  <div class="modal-scrim" id="cancelOrderModal" aria-hidden="true">
    <div class="modal-card modal-card--wide">
      <div class="modal-card__icon modal-card__icon--warning">!</div>
      <h2 class="modal-title-neutral">Cancel this order?</h2>
      <p data-cancel-order-text>Are you sure you want to cancel this order?</p>
      <div class="policy-text">
        <strong>Refund policy</strong>
        <p>If you have already paid through GCash, cancellation does not guarantee an instant refund. Eligible refunds will be handled according to the order status, the agreed transaction terms, applicable delivery/third-party charges, and applicable Philippine consumer and e-commerce laws. Orders already in transit or otherwise outside the permitted cancellation stage may not be cancelled.</p>
        <p>Under the Internet Transactions Act (R.A. 11967), online consumers generally may not cancel confirmed delivered orders once paid for, or goods that are perishable and already with a delivery service/in transit, except in the circumstances provided by law or where the parties agree otherwise.</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" data-cancel-close>KEEP ORDER</button>
        <button type="button" class="btn btn-danger" data-cancel-confirm>YES, CANCEL</button>
      </div>
    </div>
  </div>

</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
(function () {
  var list = document.querySelector('[data-history-list]');
  if (!list) return;

  function profileEmail() {
    var profile = window.ACStore.get('ac_profile', {});
    return String(profile.email || profile.userEmail || '').trim().toLowerCase();
  }

  function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function fallbackAssetSrc(name) {
    const n = String(name || '').toLowerCase();
    if (/keychain|charm/.test(n)) return "assets/products/keychain1.png";
    if (/bag|sling|purse|baguette|tote/.test(n)) return "assets/products/bag1.png";
    if (/head|bandana|clip|headband|hat|ear|barrette|bow/.test(n)) return "assets/products/headwear1.png";
    return "assets/products/flower1.png";
  }

  function refPhotoSrc(p) {
    if (!p) return "";
    p = String(p).trim();
    if (!p) return "";
    if (/^(data:|https?:|\/\/)/i.test(p)) return p;
    return "../AilisCraftUser/" + p.replace(/^\/+/,"");
  }

  function render(orders) {
    list.innerHTML = '';
    if (!orders.length) {
      list.innerHTML = '<p style="font-size:13px;color:var(--ink-soft);text-align:center;padding:32px 0;">No orders yet — once you place one, it will show up here.</p>';
      return;
    }

    orders.forEach(function (o) {
      var row = document.createElement('div');
      row.className = 'history-item';
      var date = o.date ? new Date(o.date) : null;
      var dateText = date && !isNaN(date.getTime()) ? date.toLocaleDateString(undefined, {year:'numeric',month:'short',day:'numeric'}) : 'Recent order';
      var status = o.status || 'Preparing';
      var cancellable = ['Preparing','Payment Pending','Downpayment Paid','Paid','Order Confirmed','Pending','Processing'].indexOf(status) !== -1;

      row.innerHTML = 
        '<div class="history-item__main">' +
          '<div class="history-item__id">Order ' + escapeHtml(o.id || '—') + '</div>' +
          '<div class="history-item__date">' + dateText + '</div>' +
        '</div>' +
        '<div class="history-item__actions">' +
          '<span class="status-pill">' + escapeHtml(status) + '</span>' +
          (cancellable ? '<button type="button" class="btn btn-small btn-danger-outline" data-cancel-order>Cancel Order</button>' : '') +
        '</div>';

      var cancelBtn = row.querySelector('[data-cancel-order]');
      if (cancelBtn) {
        cancelBtn.addEventListener('click', function () { 
          if (window.ACConfirmOrderCancellation) window.ACConfirmOrderCancellation(o); 
        });
      }

      var detail = document.createElement('details');
      detail.className = 'ac-order-details';
      var summary = document.createElement('summary');
      summary.innerHTML = '<span>View order details &amp; snapshots</span>';
      detail.appendChild(summary);

      var body = document.createElement('div');
      body.className = 'ac-order-details-body';
      
      var items = Array.isArray(o.items) ? o.items : [];
      var itemsHtml = items.map(function(item) {
        var custom = item.customization || {};
        var details = [];
        if (item.options && item.options.length) details.push(item.options.join(', '));
        if (custom.specialRequest) details.push('Special request: ' + custom.specialRequest);
        if (custom.letter && custom.letter.message) details.push((custom.letter.type || 'Letter') + ': ' + custom.letter.message);

        // Snapshot or Product Image
        var imgSrc = item.snapshotPng || item.snapshot || item.snapshot_data || item.imageUrl || item.image || fallbackAssetSrc(item.name);
        
        // Special Request Reference Photos
        var refPhotos = [];
        if (item.specialRequestImages && Array.isArray(item.specialRequestImages)) {
          refPhotos = item.specialRequestImages;
        } else if (typeof item.specialRequestImages === 'string' && item.specialRequestImages.trim()) {
          refPhotos = item.specialRequestImages.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
        }

        var refPhotosHtml = '';
        if (refPhotos.length) {
          refPhotosHtml = '<div class="snapshot-row" style="margin-top:8px;">' + 
            refPhotos.map(function(p) {
              var src = refPhotoSrc(p);
              return '<figure class="snapshot-item"><img class="snapshot-modal-img" src="' + src + '" alt="Reference photo" title="Click to enlarge" onclick="openSnapshot(\'' + src + '\')"><figcaption>Reference Photo</figcaption></figure>';
            }).join('') + 
          '</div>';
        }

        return '<div class="ac-order-item-card" style="display:flex;gap:12px;align-items:flex-start;padding:10px 0;border-bottom:1px solid var(--line);">' +
          '<div style="width:56px;height:56px;flex:0 0 56px;border-radius:10px;overflow:hidden;border:1px solid var(--line);background:#fff;">' +
            '<img src="' + escapeHtml(imgSrc) + '" alt="" style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onclick="openSnapshot(this.src)" onerror="this.onerror=null;this.src=\'' + fallbackAssetSrc(item.name) + '\'">' +
          '</div>' +
          '<div style="flex:1;min-width:0;">' +
            '<div style="font-weight:700;color:var(--ink);">' + escapeHtml(item.name || 'Item') + ' × ' + (item.quantity || 1) + '</div>' +
            (details.length ? '<div style="font-size:11px;color:var(--ink-soft);margin-top:2px;white-space:pre-wrap;">' + escapeHtml(details.join('\n')) + '</div>' : '') +
            refPhotosHtml +
          '</div>' +
        '</div>';
      }).join('');

      body.innerHTML = 
        '<div style="display:flex;flex-direction:column;gap:8px;padding:12px 0;font-size:13px;">' +
          itemsHtml +
          '<div style="margin-top:8px;padding-top:8px;border-top:2px solid var(--line);display:flex;justify-content:space-between;font-weight:700;">' +
            '<span>Total Amount:</span>' +
            '<span style="color:var(--pink-button-dk);">₱' + Number(o.total || 0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}) + '</span>' +
          '</div>' +
          '<div style="font-size:12px;color:var(--ink-soft);">Delivery Date: ' + escapeHtml(o.deliveryDate || '—') + '</div>' +
          (o.address ? '<div style="font-size:12px;color:var(--ink-soft);">Delivery Address: ' + escapeHtml(o.address) + '</div>' : '') +
        '</div>';

      detail.appendChild(body);
      row.appendChild(detail);
      list.appendChild(row);
    });
  }

  function load() {
    var email = profileEmail();
    if (!email) {
      render(window.ACStore.get('ac_order_history', []));
      return;
    }
    fetch('api/orders.php?action=list&email=' + encodeURIComponent(email), {headers:{'Accept':'application/json'},cache:'no-store'})
      .then(function(response) { return response.json().then(function(data) { if (!response.ok || !data.ok) throw new Error(data.error || 'Could not load orders'); return data.orders || []; }); })
      .then(function(orders) {
        try { window.ACStore.set("ac_order_history", orders); } catch(e) { /* localStorage quota: snapshots are large, render from memory */ }
        render(orders);
      })
      .catch(function() { render(window.ACStore.get('ac_order_history', [])); });
  }

  load();
  window.addEventListener('ac:orders-updated', load);
})();
</script>
</>
</body>
</html>
