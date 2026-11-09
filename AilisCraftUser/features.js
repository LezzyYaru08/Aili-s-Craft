/* ==========================================================================
   Aili's Crochet PH — Feature Integration Layer
   Additive module: preserves the existing main.js/localStorage architecture.
   ========================================================================== */
(function () {
  'use strict';

  var Store = window.ACStore;
  if (!Store) return;

  var CFG = {
    downPaymentRate: 0.30,
    deliveryFee: 50,
    social: {
      facebook: 'https://www.facebook.com/Ailicraftsph',
      instagram: 'https://www.instagram.com/ailicrochet_ph',
      tiktok: 'https://www.tiktok.com/@ailiscrafts?is_from_webapp=1&sender_device=pc'
    }
  };

  function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }
  function money(n) { return '₱' + (Number(n) || 0).toLocaleString(); }
  function now() { return new Date().toISOString(); }
  function uid(prefix) {
    return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,7);
  }

  function ensureState() {
    if (!Array.isArray(Store.get('ac_reviews', null))) Store.set('ac_reviews', []);
    if (!Array.isArray(Store.get('ac_notifications', null))) Store.set('ac_notifications', []);
    if (!Store.get('ac_feature_schema_version', null)) Store.set('ac_feature_schema_version', 1);
  }

  function notify(message, type) {
    var list = Store.get('ac_notifications', []);
    list.unshift({
      id: uid('NTF'),
      message: message,
      type: type || 'info',
      read: false,
      createdAt: now()
    });
    Store.set('ac_notifications', list.slice(0, 50));
    renderNotifications();
    window.dispatchEvent(new CustomEvent('ac:notification', { detail: message }));
  }

  function injectNotificationBell() {
    var topbar = document.querySelector('.topbar');
    if (!topbar || document.querySelector('[data-notification-bell]')) return;

    var wrap = document.createElement('div');
    wrap.className = 'ac-notification-wrap';
    wrap.innerHTML =
      '<button class="ac-notification-bell" data-notification-bell aria-label="Notifications">' +
        '<span aria-hidden="true">🔔</span><b data-notification-count hidden>0</b>' +
      '</button>' +
      '<div class="ac-notification-drawer" data-notification-drawer hidden>' +
        '<div class="ac-notification-head"><strong>Notifications</strong><button type="button" data-notification-readall>Mark all read</button></div>' +
        '<div data-notification-list></div>' +
      '</div>';
    topbar.appendChild(wrap);

    wrap.querySelector('[data-notification-bell]').addEventListener('click', function () {
      var drawer = wrap.querySelector('[data-notification-drawer]');
      drawer.hidden = !drawer.hidden;
      if (!drawer.hidden) renderNotifications();
    });
    wrap.querySelector('[data-notification-readall]').addEventListener('click', function () {
      var list = Store.get('ac_notifications', []);
      list.forEach(function (n) { n.read = true; });
      Store.set('ac_notifications', list);
      renderNotifications();
    });
    renderNotifications();
  }

  function renderNotifications() {
    var listEl = document.querySelector('[data-notification-list]');
    var countEl = document.querySelector('[data-notification-count]');
    if (!listEl) return;
    var list = Store.get('ac_notifications', []);
    var unread = list.filter(function (n) { return !n.read; }).length;
    if (countEl) {
      countEl.textContent = unread > 99 ? '99+' : unread;
      countEl.hidden = unread === 0;
    }
    listEl.innerHTML = list.length ? list.slice(0, 20).map(function (n) {
      return '<button class="ac-notification-item ' + (n.read ? '' : 'is-unread') + '" data-notification-id="' + esc(n.id) + '">' +
        '<span>' + esc(n.message) + '</span><small>' + new Date(n.createdAt).toLocaleString() + '</small></button>';
    }).join('') : '<p class="ac-empty-state">No notifications yet.</p>';

    listEl.querySelectorAll('[data-notification-id]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-notification-id');
        var ns = Store.get('ac_notifications', []);
        ns.forEach(function (n) { if (n.id === id) n.read = true; });
        Store.set('ac_notifications', ns);
        renderNotifications();
      });
    });
  }

  function addAboutNavLink() {
    document.querySelectorAll('.nav-drawer__panel').forEach(function (panel) {
      if (!panel.querySelector('a[href="about.php"]')) {
        var a = document.createElement('a');
        a.className = 'nav-drawer__link';
        a.href = 'about.php';
        a.textContent = 'About Us';
        var logout = panel.querySelector('a[href="logout.php"]');
        panel.insertBefore(a, logout || null);
      }
    });
  }

  function initAbout() {
    if (!document.querySelector('[data-about-page]')) return;
    var links = {
      facebook: document.querySelector('[data-social="facebook"]'),
      instagram: document.querySelector('[data-social="instagram"]'),
      tiktok: document.querySelector('[data-social="tiktok"]')
    };
    Object.keys(links).forEach(function (key) {
      if (links[key]) {
        links[key].href = CFG.social[key];
        links[key].target = '_blank';
        links[key].rel = 'noopener noreferrer';
      }
    });
  }

  function productRating(id) {
    var reviews = Store.get('ac_reviews', []);
    var rs = reviews.filter(function (r) { return r.productId === id; });
    if (!rs.length) return 0;
    return rs.reduce(function (a, r) { return a + Number(r.rating || 0); }, 0) / rs.length;
  }

  function initAdvancedSearch() {
    var shop = document.querySelector('[data-shop-page]');
    if (!shop) return;

    var controls = document.createElement('section');
    controls.className = 'ac-search-panel';
    controls.innerHTML =
      '<div class="ac-search-row ac-search-row--search">' +
        '<input type="search" data-product-search placeholder="Search products..." aria-label="Search products">' +
      '</div>' +
      '<div class="ac-price-filter" role="group" aria-label="Price range">' +
        '<span class="ac-filter-label">Price</span>' +
        '<div class="ac-price-options">' +
          '<button type="button" class="ac-price-option is-active" data-price-range="all" data-min="0" data-max="Infinity">All Prices</button>' +
          '<button type="button" class="ac-price-option" data-price-range="under-200" data-min="0" data-max="199">Under ₱200</button>' +
          '<button type="button" class="ac-price-option" data-price-range="200-399" data-min="200" data-max="399">₱200–₱399</button>' +
          '<button type="button" class="ac-price-option" data-price-range="400-599" data-min="400" data-max="599">₱400–₱599</button>' +
          '<button type="button" class="ac-price-option" data-price-range="600-plus" data-min="600" data-max="Infinity">₱600+</button>' +
        '</div>' +
      '</div>' +
      '<div class="ac-search-row ac-search-row--filters">' +
        '<select data-product-rating aria-label="Minimum rating"><option value="0">Any rating</option><option value="4">4★+</option><option value="3">3★+</option><option value="2">2★+</option></select>' +
        '<select data-product-stock aria-label="Availability"><option value="">Any availability</option><option value="in">In stock</option><option value="out">Out of stock</option></select>' +
      '</div>' +
      '<div class="ac-search-state" data-search-state></div>';

    var tabs = document.querySelector('.category-tabs');
    if (tabs) tabs.parentNode.insertBefore(controls, tabs);

    var activeMin = 0;
    var activeMax = Infinity;

    function syncUrl() {
      var p = new URLSearchParams();
      var q = controls.querySelector('[data-product-search]').value.trim();
      var rating = controls.querySelector('[data-product-rating]').value;
      var stock = controls.querySelector('[data-product-stock]').value;
      if (q) p.set('q', q);
      if (activeMin > 0) p.set('min', activeMin);
      if (activeMax !== Infinity) p.set('max', activeMax);
      if (rating !== '0') p.set('rating', rating);
      if (stock) p.set('stock', stock);
      var url = location.pathname + (p.toString() ? '?' + p.toString() : '');
      history.replaceState({}, '', url);
    }

    function filter() {
      var q = controls.querySelector('[data-product-search]').value.toLowerCase().trim();
      var minRating = Number(controls.querySelector('[data-product-rating]').value || 0);
      var stockMode = controls.querySelector('[data-product-stock]').value;
      var cards = document.querySelectorAll('.product-card');
      var shown = 0;

      cards.forEach(function (card) {
        var name = (card.getAttribute('data-name') || card.textContent).toLowerCase();
        var category = card.getAttribute('data-category') || '';
        var price = Number(card.getAttribute('data-price') || 0);
        var stock = Number(card.getAttribute('data-stock') == null ? 99 : card.getAttribute('data-stock'));
        var rating = Number(card.getAttribute('data-rating') || productRating(card.getAttribute('data-id')));
        var ok = (!q || name.indexOf(q) !== -1) &&
          price >= activeMin && price <= activeMax &&
          rating >= minRating &&
          (stockMode === '' || (stockMode === 'in' ? stock > 0 : stock <= 0));
        card.hidden = !ok;
        if (ok) shown++;
      });

      var state = controls.querySelector('[data-search-state]');
      if (state) state.textContent = shown + ' product' + (shown === 1 ? '' : 's') + ' shown';
      syncUrl();
    }

    controls.querySelectorAll('[data-price-range]').forEach(function (button) {
      button.addEventListener('click', function () {
        activeMin = Number(button.getAttribute('data-min') || 0);
        var maxValue = button.getAttribute('data-max');
        activeMax = maxValue === 'Infinity' ? Infinity : Number(maxValue);
        controls.querySelectorAll('[data-price-range]').forEach(function (b) {
          b.classList.toggle('is-active', b === button);
          b.setAttribute('aria-pressed', b === button ? 'true' : 'false');
        });
        filter();
      });
    });

    var p = new URLSearchParams(location.search);
    var search = controls.querySelector('[data-product-search]');
    var rating = controls.querySelector('[data-product-rating]');
    var stock = controls.querySelector('[data-product-stock]');
    if (search && p.has('q')) search.value = p.get('q');
    if (rating && p.has('rating')) rating.value = p.get('rating');
    if (stock && p.has('stock')) stock.value = p.get('stock');

    if (p.has('min') || p.has('max')) {
      activeMin = p.has('min') ? Number(p.get('min')) : 0;
      activeMax = p.has('max') ? Number(p.get('max')) : Infinity;
      var matchingPrice = Array.prototype.find.call(controls.querySelectorAll('[data-price-range]'), function (b) {
        var bMin = Number(b.getAttribute('data-min') || 0);
        var bMax = b.getAttribute('data-max') === 'Infinity' ? Infinity : Number(b.getAttribute('data-max'));
        return bMin === activeMin && bMax === activeMax;
      });
      controls.querySelectorAll('[data-price-range]').forEach(function (b) {
        var isActive = b === matchingPrice;
        b.classList.toggle('is-active', isActive);
        b.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    controls.querySelectorAll('input,select').forEach(function (el) {
      el.addEventListener('input', filter);
      el.addEventListener('change', filter);
    });
    filter();
  }

  function updateProductCardStats() {
    document.querySelectorAll('.product-card').forEach(function (card) {
      var id = card.getAttribute('data-id');
      if (!id) return;
      if (!card.getAttribute('data-stock')) card.setAttribute('data-stock', '99');
      var body = card.querySelector('.product-card__body');
      if (body) {
        var actions = body.querySelector('.product-card__actions');
        if (actions && !actions.querySelector('[data-product-reviews]')) {
          var link = document.createElement('a');
          link.className = 'btn-card ac-review-link';
          link.setAttribute('data-product-reviews', '');
          link.href = 'product-review.php?product_code=' + encodeURIComponent(id);
          link.textContent = 'Reviews';
          actions.insertBefore(link, actions.firstChild);
        }
        var line = body.querySelector('.ac-product-rating');
        if (!line) {
          line = document.createElement('div');
          line.className = 'ac-product-rating';
          line.textContent = 'Loading customer ratings…';
          body.insertBefore(line, actions);
        }
        if (!card.getAttribute('data-review-rating-loaded')) {
          card.setAttribute('data-review-rating-loaded', '1');
          fetch('api/product_reviews.php?product_code=' + encodeURIComponent(id), {credentials:'same-origin', cache:'no-store'})
            .then(function(r){return r.json();})
            .then(function(data){
              if (!data || !data.ok) { line.textContent = 'See customer reviews'; return; }
              line.textContent = data.count ? ('★ ' + Number(data.average).toFixed(1) + ' · ' + data.count + ' review' + (data.count===1?'':'s')) : 'No ratings yet';
              if (data.count) card.setAttribute('data-rating', Number(data.average).toFixed(1));
            }).catch(function(){line.textContent='See customer reviews';});
        }
      }
    });
  }

  function orderAllowedToReview(order) {
    var s = String(order.status || '').toLowerCase();
    return s === 'completed' || s === 'delivered';
  }
  function orderAllowedToCancel(order) {
    var s = String(order.status || '').toLowerCase();
    return s === 'pending' || s === 'processing';
  }

  function renderReviewModal(order) {
    var old = document.getElementById('ac-review-modal');
    if (old) old.remove();
    var modal = document.createElement('div');
    modal.id = 'ac-review-modal';
    modal.className = 'ac-feature-modal';
    var items = Array.isArray(order.items) ? order.items : [];
    modal.innerHTML = '<div class="ac-feature-modal__card"><button class="ac-feature-modal__close" data-review-close>&times;</button>' +
      '<h3>Review Order ' + esc(order.id) + '</h3><p>Choose a product to view its customer reviews or leave your verified-purchase feedback.</p>' +
      '<div data-review-products>' + items.map(function(item){
        var code = item.productCode || item.product_code || '';
        return '<div class="ac-review-product"><strong>' + esc(item.name || 'Product') + '</strong>' +
          (code ? '<a class="btn-card ac-review-link" href="product-review.php?product_code=' + encodeURIComponent(code) + '">View Reviews / Write Review</a>' : '<small>Product review is unavailable because this order item is not linked to a catalog product.</small>') +
          '</div>';
      }).join('') + '</div></div>';
    document.body.appendChild(modal);
    modal.querySelector('[data-review-close]').onclick = function(){modal.remove();};
    modal.addEventListener('click',function(e){if(e.target===modal)modal.remove();});
  }

  function enhanceHistory() {
    var list = document.querySelector('[data-history-list]');
    if (!list) return;
    var orders = Store.get('ac_order_history', []);
    list.querySelectorAll('.history-item').forEach(function (row, index) {
      var order = orders[index];
      if (!order) return;
      if (row.querySelector('.ac-history-actions')) return;
      var actions = document.createElement('div');
      actions.className = 'ac-history-actions';
      if (orderAllowedToCancel(order)) actions.innerHTML += '<button type="button" class="btn-card" data-cancel-order="' + esc(order.id) + '">Cancel Order</button>';
      if (orderAllowedToReview(order)) actions.innerHTML += '<button type="button" class="btn-card" data-review-order="' + esc(order.id) + '">Review</button>';
      actions.innerHTML += '<button type="button" class="btn-card" data-reorder="' + esc(order.id) + '">Reorder</button>';
      row.appendChild(actions);
    });

    list.querySelectorAll('[data-cancel-order]').forEach(function (btn) {
      btn.onclick = function () {
        var id = btn.getAttribute('data-cancel-order');
        var orders2 = Store.get('ac_order_history', []);
        var order = orders2.find(function (o) { return o.id === id; });
        if (!order || !orderAllowedToCancel(order)) return;
        order.status = 'Cancelled';
        if (order.items) {
          order.items.forEach(function (item) {
            item.reservedStock = 0;
          });
        }
        Store.set('ac_order_history', orders2);
        notify('Order ' + id + ' was cancelled.', 'order');
      };
    });
    list.querySelectorAll('[data-review-order]').forEach(function (btn) {
      btn.onclick = function () {
        var id = btn.getAttribute('data-review-order');
        var order = Store.get('ac_order_history', []).find(function (o) { return o.id === id; });
        if (order) renderReviewModal(order);
      };
    });
    list.querySelectorAll('[data-reorder]').forEach(function (btn) {
      btn.onclick = function () {
        var id = btn.getAttribute('data-reorder');
        var order = Store.get('ac_order_history', []).find(function (o) { return o.id === id; });
        if (!order || !order.items) return;
        var cart = Store.get('ac_cart', []);
        order.items.forEach(function (item) {
          var copy = JSON.parse(JSON.stringify(item));
          copy.id = copy.id + '-reorder-' + Date.now() + '-' + Math.random().toString(36).slice(2,5);
          cart.push(copy);
        });
        Store.set('ac_cart', cart);
        notify('Order ' + id + ' was added to your cart for reorder.', 'order');
        window.location.href = 'order-summary.php';
      };
    });
  }

  function initOrderHistoryEnhancer() {
    if (!document.querySelector('[data-history-list]')) return;
    setTimeout(enhanceHistory, 0);
    window.addEventListener('ac:reviews-updated', enhanceHistory);
  }

  function initCheckoutDownPayment() {
    var method = document.getElementById('pay-gcash-down');
    if (!method) return;
    var box = document.querySelector('[data-gcash-down-payment]');
    var totalEl = document.querySelector('[data-sum-total]');
    var amountEl = document.querySelector('[data-down-payment-amount]');
    var remainingEl = document.querySelector('[data-remaining-balance]');
    var pctEl = document.querySelector('[data-down-payment-percent]');
    function calc() {
      var totalText = totalEl ? totalEl.textContent.replace(/[^\d.]/g,'') : '0';
      var total = Number(totalText) || 0;
      var pct = Math.min(100, Math.max(1, Number(pctEl ? pctEl.value : CFG.downPaymentRate * 100)));
      var amount = total * pct / 100;
      if (amountEl) amountEl.textContent = money(amount);
      if (remainingEl) remainingEl.textContent = money(Math.max(0, total - amount));
      if (box) box.querySelector('[data-down-payment-rate-label]').textContent = pct + '%';
    }
    function sync() {
      var selected = document.querySelector('input[name="payMethod"]:checked');
      var show = selected && selected.id === 'pay-gcash-down';
      if (box) box.hidden = !show;
      calc();
    }
    if (pctEl) pctEl.addEventListener('input', calc);
    document.querySelectorAll('input[name="payMethod"]').forEach(function (i) { i.addEventListener('change', sync); });
    sync();
  }

  function initFeaturePaymentValidation() {
    var btn = document.querySelector('[data-place-order]');
    if (!btn) return;
    btn.addEventListener('click', function () {
      // Auth gate (belt-and-suspenders; main.js capture gate also blocks).
      var profile = Store.get('ac_profile', {});
      if (!profile || !profile.authenticated) {
        if (window.ACToast) window.ACToast('Please log in first.');
        if (window.ACLoginModal && typeof window.ACLoginModal.open === 'function') window.ACLoginModal.open();
        else window.location.href = 'login.php';
        return;
      }
      var selected = document.querySelector('input[name="payMethod"]:checked');
      if (!selected || selected.id !== 'pay-gcash-down') return;
      var ref = document.querySelector('[name="gcashReference"]');
      if (!ref || !ref.value.trim()) {
        if (window.ACToast) window.ACToast('Enter the GCash transaction reference before placing the order.');
        else alert('Enter the GCash transaction reference before placing the order.');
        return;
      }
    }, true);
  }

  function initCrossTabSync() {
    window.addEventListener('storage', function (e) {
      if (e.key === 'ac_notifications') renderNotifications();
      if (e.key === 'ac_order_history') enhanceHistory();
      if (e.key === 'ac_reviews') updateProductCardStats();
    });
  }


  function watchOrderLifecycle() {
    var orders = Store.get('ac_order_history', []);
    var snapshot = Store.get('ac_feature_order_snapshot', {});
    var next = {};
    orders.forEach(function (o) {
      next[o.id] = {
        status: o.status || '',
        paymentStatus: o.paymentStatus || '',
        downPaymentStatus: o.downPaymentStatus || ''
      };
      var old = snapshot[o.id];
      if (old) {
        if (old.status !== next[o.id].status) notify('Order ' + o.id + ' is now ' + next[o.id].status + '.', 'order');
        if (old.downPaymentStatus !== next[o.id].downPaymentStatus && next[o.id].downPaymentStatus) {
          notify('Order ' + o.id + ': down payment is ' + next[o.id].downPaymentStatus + '.', 'payment');
        }
        if (old.paymentStatus !== next[o.id].paymentStatus && next[o.id].paymentStatus) {
          notify('Order ' + o.id + ': payment status is ' + next[o.id].paymentStatus + '.', 'payment');
        }
      }
    });
    Store.set('ac_feature_order_snapshot', next);
  }

  function boot() {
    ensureState();
    injectNotificationBell();
    addAboutNavLink();
    initAbout();
    updateProductCardStats();
    initAdvancedSearch();
    initCheckoutDownPayment();
    initFeaturePaymentValidation();
    initOrderHistoryEnhancer();
    initCrossTabSync();
    watchOrderLifecycle();

    var orders = Store.get('ac_order_history', []);
    var lastSeen = Store.get('ac_feature_last_order', '');
    if (orders.length && lastSeen !== orders[0].id) {
      Store.set('ac_feature_last_order', orders[0].id);
      if (orders[0].paymentMethod) notify('Order ' + orders[0].id + ' has been placed.', 'order');
    }
  }

  window.ACFeatures = {
    notify: notify,
    renderNotifications: renderNotifications,
    refreshHistory: enhanceHistory,
    calculateRating: productRating
  };

  document.addEventListener('DOMContentLoaded', boot);

  function initFlowerQuantities() {
    var panel = document.querySelector('.customize-panel[data-panel="flowers"]');
    if (!panel) return;
    panel.querySelectorAll('[data-flower-quantity]').forEach(function (qty) {
      qty.addEventListener('input', function () {
        var value = Math.max(0, Math.min(24, parseInt(qty.value || '0', 10)));
        qty.value = value;
        var checkbox = document.getElementById(qty.getAttribute('data-flower-quantity'));
        if (checkbox) checkbox.checked = value > 0;
        var total = Array.from(panel.querySelectorAll('[data-flower-quantity]')).reduce(function (sum, el) {
          return sum + Math.max(0, parseInt(el.value || '0', 10));
        }, 0);
        if (total > 24) {
          qty.value = Math.max(0, value - (total - 24));
          value = Number(qty.value) || 0;
          total = 24;
        }
        var count = panel.querySelector('#flower-count');
        if (count) count.value = String(Math.max(1, total));
        panel.dispatchEvent(new Event('change', { bubbles: true }));
        if (window.ACToast && total >= 24) window.ACToast('Maximum bouquet size is 24 flowers.');
      });
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initFlowerQuantities); else initFlowerQuantities();
})();
