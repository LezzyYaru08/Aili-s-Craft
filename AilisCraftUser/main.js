/* =========================================================================
   Aili's Crochet PH — Shared Script with Interactive Animations
   ========================================================================= */
(function () {
  'use strict';

  /* -----------------------------------------------------------------------
     0. Storage & Page Animation Helpers
  --------------------------------------------------------------------- */
  var memoryStore = {};
  var storageAvailable = (function () {
    try {
      var k = '__ac_test__';
      window.localStorage.setItem(k, '1');
      window.localStorage.removeItem(k);
      return true;
    } catch (e) {
      return false;
    }
  })();

  var Store = {
    get: function (key, fallback) {
      try {
        var raw = storageAvailable ? window.localStorage.getItem(key) : memoryStore[key];
        if (raw === undefined || raw === null) return fallback;
        return JSON.parse(raw);
      } catch (e) {
        return fallback;
      }
    },
    set: function (key, value) {
      var raw = JSON.stringify(value);
      try {
        if (storageAvailable) window.localStorage.setItem(key, raw);
        else memoryStore[key] = raw;
      } catch (e) {
        memoryStore[key] = raw;
      }
    },
    remove: function (key) {
      try {
        if (storageAvailable) window.localStorage.removeItem(key);
        else delete memoryStore[key];
      } catch (e) { /* no-op */ }
    }
  };
  window.ACStore = Store;

  /* Helper to store animation class and redirect */
  function navigateWithAnim(url, animClass) {
    try {
      window.sessionStorage.setItem('ac_page_anim', animClass || 'anim-fade-in');
    } catch (e) {}
    window.location.href = url;
  }

  /* Trigger page arrival animation */
  function applyPageAnimation() {
    var frame = document.querySelector('.app-frame') || document.body;
    var anim = null;
    try {
      anim = window.sessionStorage.getItem('ac_page_anim');
      window.sessionStorage.removeItem('ac_page_anim');
    } catch (e) {}

    if (anim) {
      frame.classList.add(anim);
    } else {
      frame.classList.add('anim-fade-in'); // Default subtle entrance
    }
  }

  /* Automatic link transition mapper */
  function initLinkTransitions() {
    document.addEventListener('click', function (e) {
      var link = e.target.closest('a[href]');
      if (!link) return;
      var href = link.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

      var anim = link.getAttribute('data-anim');

      if (!anim) {
        if (href.includes('home')) {
          anim = 'anim-push-left';
        } else if (href.includes('order') || href.includes('customize') || href.includes('summary')) {
          anim = 'anim-push-up';
        } else if (href.includes('buy') || href.includes('checkout') || href.includes('register')) {
          anim = 'anim-push-right';
        } else {
          anim = 'anim-fade-in';
        }
      }

      try {
        window.sessionStorage.setItem('ac_page_anim', anim);
      } catch (err) {}
    });
  }

  /* -----------------------------------------------------------------------
     1. Logo badge
  --------------------------------------------------------------------- */
  var LOGO_HTML = '<img src="assets/images/logo.png" alt="Aili\'s Crochet PH Logo" style="width:100%;height:100%;object-fit:contain;display:block;">';

  function injectLogos() {
    document.querySelectorAll('[data-ac-logo]').forEach(function (el) {
      el.innerHTML = LOGO_HTML;
    });
  }

  /* -----------------------------------------------------------------------
     2. Sliding Preview Carousel
  --------------------------------------------------------------------- */
  function initHeroCarousel() {
    var carousel = document.querySelector('[data-hero-carousel]');
    if (!carousel) return;

    var slides = carousel.querySelectorAll('.carousel-slide');
    var dots = carousel.querySelectorAll('[data-slide-index]');
    var prevBtn = carousel.querySelector('[data-carousel-prev]');
    var nextBtn = carousel.querySelector('[data-carousel-next]');
    var currentIndex = 0;
    var timer = null;

    function showSlide(index) {
      if (index < 0) index = slides.length - 1;
      if (index >= slides.length) index = 0;
      currentIndex = index;

      slides.forEach(function (slide, i) {
        slide.classList.toggle('active', i === currentIndex);
      });

      dots.forEach(function (dot, i) {
        dot.classList.toggle('active', i === currentIndex);
      });
    }

    function nextSlide() {
      showSlide(currentIndex + 1);
    }

    function startAutoPlay() {
      stopAutoPlay();
      timer = setInterval(nextSlide, 3000);
    }

    function stopAutoPlay() {
      if (timer) clearInterval(timer);
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        showSlide(currentIndex - 1);
        startAutoPlay();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        nextSlide();
        startAutoPlay();
      });
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var idx = parseInt(dot.getAttribute('data-slide-index'), 10);
        showSlide(idx);
        startAutoPlay();
      });
    });

    carousel.querySelectorAll('[data-carousel-buy]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var slide = btn.closest('.carousel-slide');
        if (!slide) return;

        var name = slide.getAttribute('data-name');
        var price = parseFloat(slide.getAttribute('data-price') || '0');
        var category = slide.getAttribute('data-category');
        var rawOptions = slide.getAttribute('data-options') || '';
        var optionsList = rawOptions ? rawOptions.split(',').map(function (s) { return s.trim(); }) : [];

        var cart = Store.get('ac_cart', []);
        cart.push({
          id: 'item_' + Date.now(),
          category: category,
          name: name,
          options: optionsList,
          price: price
        });
        Store.set('ac_cart', cart);
        toast('Added to your bag ✨');
        setTimeout(function () { navigateWithAnim('order-summary.php', 'anim-push-right'); }, 600);
      });
    });

    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);

    showSlide(0);
    startAutoPlay();
  }

  /* -----------------------------------------------------------------------
     3. Nav drawer
  --------------------------------------------------------------------- */
  function initNavDrawer() {
    var openBtn = document.querySelector('[data-drawer-open], .topbar__menu-btn');
    var drawer = document.querySelector('[data-drawer], .nav-drawer');
    if (!drawer) return;

    var closeBtn = drawer.querySelector('[data-drawer-close], .nav-drawer__close');
    var scrim = drawer.querySelector('.nav-drawer__scrim, .nav-drawer-scrim, [class*="scrim"]');

    // Derive the active drawer item from the current document route.
    // Each drawer item highlights its matching page, including Customize.
    var currentFile = (window.location.pathname.split('/').pop() || 'home.php').toLowerCase();
    var routeAliases = {
      'order-success.php': 'history.php',
      'register-success.php': 'profile.php'
    };
    var activeFile = routeAliases[currentFile] || currentFile;
    drawer.querySelectorAll('.nav-drawer__link[href]').forEach(function (link) {
      var href = (link.getAttribute('href') || '').split(/[?#]/)[0].split('/').pop().toLowerCase();
      var active = href === activeFile;
      link.classList.toggle('is-active', active);
      if (active) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });

    function open(e) { 
      if (e && e.preventDefault) e.preventDefault();
      drawer.classList.add('open'); 
      document.body.classList.add('drawer-open');
      document.body.style.overflow = 'hidden'; 
    }
    
    function close(e) { 
      if (e && e.preventDefault) e.preventDefault();
      drawer.classList.remove('open'); 
      document.body.classList.remove('drawer-open');
      document.body.style.overflow = ''; 
    }

    if (openBtn) openBtn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    if (scrim) scrim.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });
  }

  /* -----------------------------------------------------------------------
     4. Toast helper
  --------------------------------------------------------------------- */
  function toast(message) {
    var el = document.querySelector('.toast');
    if (!el) {
      el = document.createElement('div');
      el.className = 'toast';
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.classList.remove('show');
    void el.offsetWidth; // Force CSS reflow for re-triggering animation
    el.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(function () { el.classList.remove('show'); }, 2200);
  }
  window.ACToast = toast;

  /* -----------------------------------------------------------------------
     4b. Password strength helper (shared with register.php meter)
     Rules: min 8 chars + uppercase + lowercase + digit.
     Weak    = fails any minimum rule
     Medium  = meets minimums but no symbol AND <12 chars
     Strong  = meets minimums AND (has symbol OR >=12 chars)
  --------------------------------------------------------------------- */
  function acPasswordStrength(pw) {
    pw = String(pw || '');
    var hasUpper = /[A-Z]/.test(pw);
    var hasLower = /[a-z]/.test(pw);
    var hasDigit = /[0-9]/.test(pw);
    var hasSymbol = /[^A-Za-z0-9]/.test(pw);
    var longEnough = pw.length >= 8;
    var meetsMin = longEnough && hasUpper && hasLower && hasDigit;
    var level, label, message;
    if (!pw) {
      level = 'empty'; label = ''; message = '';
    } else if (!meetsMin) {
      level = 'weak'; label = 'Weak';
      var missing = [];
      if (!longEnough) missing.push('at least 8 characters');
      if (!hasUpper) missing.push('an uppercase letter');
      if (!hasLower) missing.push('a lowercase letter');
      if (!hasDigit) missing.push('a number');
      message = 'Password needs ' + missing.join(', ') + '.';
    } else if (hasSymbol || pw.length >= 12) {
      level = 'strong'; label = 'Strong'; message = 'Strong password.';
    } else {
      level = 'medium'; label = 'Medium'; message = 'Add a symbol or make it 12+ chars for Strong.';
    }
    return { level: level, label: label, message: message, meetsMin: meetsMin };
  }
  window.ACPasswordStrength = acPasswordStrength;

  /* -----------------------------------------------------------------------
     4c. Auth gate helper — returns true if logged in, otherwise toasts
         and opens the login modal (or falls back to login.php).
  --------------------------------------------------------------------- */
  function requireAuth() {
    var p = Store.get('ac_profile', {});
    if (p && p.authenticated) return true;
    if (window.ACToast) window.ACToast('Please log in first.');
    if (window.ACLoginModal && typeof window.ACLoginModal.open === 'function') {
      window.ACLoginModal.open();
    } else {
      window.location.href = 'login.php';
    }
    return false;
  }
  window.ACRequireAuth = requireAuth;

  /* -----------------------------------------------------------------------
     5. Modal helper
  --------------------------------------------------------------------- */
  function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = document.querySelector(btn.getAttribute('data-modal-open'));
        if (target) target.classList.add('open');
      });
    });
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var scrim = btn.closest('.modal-scrim');
        if (scrim) scrim.classList.remove('open');
      });
    });
  }
  window.ACShowModal = function (selector) {
    var el = document.querySelector(selector);
    if (el) el.classList.add('open');
  };

  /* -----------------------------------------------------------------------
     6. Auth forms
  --------------------------------------------------------------------- */
  function initLoginForm() {
    var form = document.querySelector('[data-login-form]');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = form.querySelector('[name="email"]');
      var password = form.querySelector('[name="password"]');

      toggleFieldError(email, !email.value || !/^\S+@\S+\.\S+$/.test(email.value));
      toggleFieldError(password, !password.value || password.value.length < 6);
      var ok = !email.closest('.field').classList.contains('invalid') &&
               !password.closest('.field').classList.contains('invalid');

      if (!ok) return;

      var submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;
      fetch('api/auth.php?action=login', {method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify({email:email.value.trim(), password:password.value})})
        .then(function(r){return r.json().then(function(d){if(!r.ok || !d.ok) throw new Error(d.error || 'Unable to log in.'); return d;});})
        .then(function(data){Store.set('ac_profile',{id:data.user.id,fullName:data.user.fullName,email:data.user.email,authenticated:true}); navigateWithAnim('home.php','anim-push-left');})
        .catch(function(err){if(window.ACToast) window.ACToast(err.message); else alert(err.message);})
        .finally(function(){if(submit) submit.disabled=false;});
    });
  }

  function initRegisterForm() {
    var form = document.querySelector('[data-register-form]');
    if (!form) return;

    var terms = form.querySelector('[data-register-terms-checkbox]');
    var submitBtn = form.querySelector('[data-register-submit]');
    var termsError = form.querySelector('[data-register-terms-error]');

    function syncRegisterTerms() {
      var accepted = !!(terms && terms.checked);
      if (submitBtn) submitBtn.disabled = !accepted;
      if (termsError) termsError.classList.toggle('is-visible', !accepted && form.getAttribute('data-terms-attempted') === 'true');
    }

    if (terms) terms.addEventListener('change', function () {
      form.setAttribute('data-terms-attempted', 'false');
      syncRegisterTerms();
    });
    syncRegisterTerms();

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fullName = form.querySelector('[name="fullName"]');
      var email = form.querySelector('[name="email"]');
      var password = form.querySelector('[name="password"]');
      var confirm = form.querySelector('[name="confirmPassword"]');

      toggleFieldError(fullName, !fullName.value.trim());
      toggleFieldError(email, !email.value || !/^\S+@\S+\.\S+$/.test(email.value));
      var pwStrength = acPasswordStrength(password.value);
      toggleFieldError(password, !password.value || !pwStrength.meetsMin);
      if (!password.value || !pwStrength.meetsMin) {
        var errSpan = password.closest('.field') ? password.closest('.field').querySelector('.field-error') : null;
        if (errSpan && pwStrength.message) errSpan.textContent = pwStrength.message;
      }
      toggleFieldError(confirm, confirm.value !== password.value || !confirm.value);

      var termsAccepted = !!(terms && terms.checked);
      if (!termsAccepted) {
        form.setAttribute('data-terms-attempted', 'true');
        syncRegisterTerms();
        if (window.ACToast) window.ACToast('Please agree to the Terms & Conditions before creating your account.');
      }

      var anyInvalid = form.querySelectorAll('.field.invalid').length > 0 || !termsAccepted;
      if (anyInvalid) return;

      var acceptedAt = new Date().toISOString();
      if (submitBtn) submitBtn.disabled = true;
      fetch('api/auth.php?action=register', {method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify({fullName:fullName.value.trim(),email:email.value.trim(),password:password.value,termsAccepted:true})})
        .then(function(r){return r.json().then(function(d){if(!r.ok || !d.ok) throw new Error(d.error || 'Unable to create account.'); return d;});})
        .then(function(data){
          Store.set('ac_profile',{id:data.user.id,fullName:data.user.fullName,email:data.user.email,authenticated:true,termsAccepted:true,termsAcceptedAt:acceptedAt,termsVersion:'1.0'});
          Store.set('ac_terms_acceptance',{accepted:true,acceptedAt:acceptedAt,version:'1.0',type:'registration'});
          navigateWithAnim('register-success.php','anim-push-down');
        })
        .catch(function(err){if(window.ACToast) window.ACToast(err.message); else alert(err.message);})
        .finally(function(){if(submitBtn) submitBtn.disabled=!(terms && terms.checked);});
    });
  }

  function initRegisterTermsModal() {
    var modal = document.querySelector('#registerTermsModal');
    var openBtn = document.querySelector('[data-register-terms-open]');
    if (!modal || !openBtn) return;

    function open() { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
    function close() { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }

    openBtn.addEventListener('click', function (e) { e.preventDefault(); open(); });
    modal.querySelectorAll('[data-register-terms-close]').forEach(function (b) { b.addEventListener('click', close); });
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
  }

  function toggleFieldError(input, isInvalid) {
    var field = input.closest('.field');
    if (!field) return;
    field.classList.remove('invalid');
    if (isInvalid) {
      void field.offsetWidth; // Re-trigger shake animation
      field.classList.add('invalid');
    }
  }

  /* -----------------------------------------------------------------------
     7. Customize builder & Quick Buy
  --------------------------------------------------------------------- */
  var BASE_PRICE = {
    flowers: 480,
    bags: 350,
    headwears: 60,
    keychains: 70
  };

  function initShopFilters() {
    var tabs = document.querySelectorAll('[data-shop-filter]');
    if (!tabs.length) return;

    function show(category, activeTab) {
      tabs.forEach(function (tab) {
        tab.classList.toggle('active', tab === activeTab);
        tab.setAttribute('aria-selected', tab === activeTab ? 'true' : 'false');
      });

      document.querySelectorAll('[data-shop-panel]').forEach(function (panel) {
        var match = category === 'all' || panel.getAttribute('data-shop-panel') === category;
        panel.hidden = !match;
        panel.setAttribute('aria-hidden', match ? 'false' : 'true');
      });
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        show(tab.getAttribute('data-shop-filter') || 'flowers', tab);
      });
    });

    var initial = document.querySelector('[data-shop-filter].active') || tabs[0];
    show(initial.getAttribute('data-shop-filter') || 'flowers', initial);
  }

  function initCategoryTabs() {
    var tabs = document.querySelectorAll('[data-category-tab]');
    if (!tabs.length) return;
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var target = tab.getAttribute('data-category-tab');
        tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
        
        document.querySelectorAll('.customize-panel').forEach(function (panel) {
          var isTarget = panel.getAttribute('data-panel') === target;
          panel.classList.toggle('active', isTarget);
        });

        recalcCustomizeTotal();
        
        // Ensure BOTH text/tags and SVG graphics refresh together
        if (window.updateLivePreview) {
          window.updateLivePreview();
        } else if (window.renderVisualSVGPreview) {
          window.renderVisualSVGPreview();
        }
      });
    });
  }

  function initRangeInputs() {
    document.querySelectorAll('.range-row input[type="range"]').forEach(function (input) {
      var out = input.parentElement.querySelector('.range-row__value');
      var suffix = input.getAttribute('data-suffix') || '';
      function update() { if (out) out.textContent = input.value + suffix; }
      input.addEventListener('input', function () { 
        update(); 
        recalcCustomizeTotal(); 
        if (window.updateLivePreview) window.updateLivePreview();
      });
      update();
    });
  }

  // Individual flower quantity controls (delegated so the buttons always respond).
  function initFlowerQuantityControls() {
    var panel = document.querySelector('.customize-panel[data-panel="flowers"]');
    if (!panel || panel.dataset.flowerQtyReady === '1') return;
    panel.dataset.flowerQtyReady = '1';

    function totalFlowers() {
      return Array.from(panel.querySelectorAll('[data-flower-quantity]')).reduce(function (sum, input) {
        return sum + Math.max(0, parseInt(input.value || '0', 10) || 0);
      }, 0);
    }
    function refresh(input) {
      var amount = Math.max(0, Math.min(24, parseInt(input.value || '0', 10) || 0));
      input.value = String(amount);
      var flowerId = input.getAttribute('data-flower-quantity');
      var toggle = flowerId ? panel.querySelector('#' + flowerId) : null;
      if (toggle) toggle.checked = amount > 0;
      if (typeof recalcCustomizeTotal === 'function') recalcCustomizeTotal();
      if (typeof renderVisualSVGPreview === 'function') renderVisualSVGPreview();
      if (window.updateLivePreview) window.updateLivePreview();
    }

    panel.addEventListener('click', function (event) {
      var button = event.target.closest('.flower-qty-btn[data-qty-for]');
      if (!button || !panel.contains(button)) return;
      event.preventDefault();
      var flowerId = button.getAttribute('data-qty-for');
      var input = panel.querySelector('[data-flower-quantity="' + flowerId + '"]');
      if (!input) return;
      var current = Math.max(0, parseInt(input.value || '0', 10) || 0);
      if (button.getAttribute('data-dir') === 'up') {
        if (totalFlowers() >= 24) {
          if (typeof toast === 'function') toast('Maximum 24 flowers per bouquet');
          return;
        }
        current += 1;
      } else {
        current = Math.max(0, current - 1);
      }
      input.value = String(current);
      refresh(input);
    });

    panel.addEventListener('input', function (event) {
      var input = event.target.closest('[data-flower-quantity]');
      if (!input || !panel.contains(input)) return;
      input.value = String(input.value || '').replace(/[^0-9]/g, '').slice(0, 2);
      var sum = totalFlowers();
      if (sum > 24) {
        var excess = sum - 24;
        input.value = String(Math.max(0, (parseInt(input.value || '0', 10) || 0) - excess));
      }
      refresh(input);
    });
    panel.addEventListener('change', function (event) {
      var input = event.target.closest('[data-flower-quantity]');
      if (input && panel.contains(input)) refresh(input);
    });
  }

  function initQtySelectors() {
    document.querySelectorAll('.qty-select').forEach(function (group) {
      var valueEl = group.querySelector('.qty-select__value');
      var min = parseInt(group.getAttribute('data-min') || '1', 10);
      var max = parseInt(group.getAttribute('data-max') || '10', 10);
      group.querySelectorAll('button').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var val = parseInt(valueEl.textContent, 10) || min;
          val += btn.getAttribute('data-dir') === 'up' ? 1 : -1;
          val = Math.max(min, Math.min(max, val));
          
          valueEl.textContent = val;
          valueEl.classList.remove('qty-bump');
          void valueEl.offsetWidth; // Re-trigger quantity bump animation
          valueEl.classList.add('qty-bump');

          recalcCustomizeTotal();
          if (window.updateLivePreview) window.updateLivePreview();
        });
      });
    });
  }

  function initCheckedClassFallback() {
    document.querySelectorAll('.chip input, .color-swatch input').forEach(function (input) {
      var sync = function () {
        var label = input.closest('label');
        if (!label) return;
        if (input.type === 'radio' && input.name) {
          document.querySelectorAll('input[name="' + input.name + '"]').forEach(function (sibling) {
            var siblingLabel = sibling.closest('label');
            if (siblingLabel) siblingLabel.classList.toggle('is-checked', sibling.checked);
          });
        } else {
          label.classList.toggle('is-checked', input.checked);
        }
      };
      input.addEventListener('change', sync);
      sync();
    });
  }

  function initAddonToggles() {
    document.querySelectorAll('.customize-panel input[type="checkbox"], .customize-panel input[type="radio"]').forEach(function (input) {
      input.addEventListener('change', function() {
        if (input.checked && input.id.indexOf('fo-') === 0) {
          var foliagePanel = input.closest('.customize-panel');
          var checkedFoliage = foliagePanel ? foliagePanel.querySelectorAll('input[id^="fo-"]:checked') : [];
          if (checkedFoliage.length > 3) {
            input.checked = false;
            var foliageLabel = input.closest('label');
            if (foliageLabel) foliageLabel.classList.remove('is-checked');
            toast('Choose up to 3 kinds of foliage');
          }
        }
        recalcCustomizeTotal();
        if (window.updateLivePreview) window.updateLivePreview();
      });
    });
  }

  function activeCategoryKey() {
    var activeTab = document.querySelector('[data-category-tab].active');
    return activeTab ? activeTab.getAttribute('data-category-tab') : 'flowers';
  }

  function recalcCustomizeTotal() {
    var totalEl = document.querySelector('[data-customize-total]');
    if (!totalEl) return;
    var key = activeCategoryKey();
    var panel = document.querySelector('.customize-panel[data-panel="' + key + '"]');
    var total = BASE_PRICE[key] || 0;
    if (panel) {
      panel.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked').forEach(function (input) {
        var addPrice = parseFloat(input.getAttribute('data-price') || '0');
        total += addPrice;
      });
      if (key === 'flowers') {
        var qtyInputs = panel.querySelectorAll('[data-flower-quantity]');
        var count = qtyInputs.length ? Array.from(qtyInputs).reduce(function (sum, input) { return sum + Math.max(0, parseInt(input.value || '0', 10)); }, 0) : Math.max(1, Math.min(24, parseInt((panel.querySelector('#flower-count') || {}).value || '1', 10)));
        count = Math.min(24, count);
        var flowerTypes = panel.querySelectorAll('input[id^="fl-"]:checked').length;
        total += Math.max(0, count - 1) * 20 + Math.max(0, flowerTypes - 1) * 20;
      }
    }
    totalEl.textContent = '\u20B1' + total.toLocaleString();
    totalEl.setAttribute('data-computed-total', total);
  }

  /* Render thumbnail previews for special-request reference photo pickers. */
  function initSpecialRequestImagePreviews() {
    document.querySelectorAll('.js-special-request-images').forEach(function (input) {
      input.addEventListener('change', function () {
        var preview = input.parentNode ? input.parentNode.querySelector('.js-special-request-preview') : null;
        if (!preview) return;
        preview.innerHTML = '';
        Array.prototype.forEach.call(input.files || [], function (file) {
          if (!file.type || file.type.indexOf('image/') !== 0) return;
          var reader = new FileReader();
          reader.onload = function (e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.alt = file.name;
            img.title = file.name;
            preview.appendChild(img);
          };
          reader.readAsDataURL(file);
        });
      });
    });
  }

  /* Capture the live preview as a PNG data URL.
   * Flowers: try to rasterize the SVG stage; all categories fall back to
   * html2canvas on the preview card, then to the product image URL.
   * Calls cb(dataUrl) with '' when nothing is available. */
  function captureLivePreviewPng(key, panel, cb) {
    var imageUrl = (panel && panel.querySelector('[data-product-image]')) ? panel.querySelector('[data-product-image]').getAttribute('src') : '';
    var finish = function (url) { try { cb(url || ''); } catch (e) { cb(''); } };

    // Inline every relative <image href> inside the SVG as a base64 data URL.
    // Without this, serializing the SVG to a data: URL breaks relative-href
    // resolution and all external artwork (flower heads, bag bodies, headwear,
    // keychain charms — plus mask images in <defs>) renders blank.
    function inlineSvgImages(svgEl, done) {
      try {
        var images = svgEl.querySelectorAll('image');
        var pending = images.length;
        if (!pending) { done(); return; }
        images.forEach(function (img) {
          var href = img.getAttribute('href') || img.getAttribute('xlink:href') || '';
          var isExternal = !!href && !/^(data:|https?:|\/\/|#)/i.test(href);
          if (!isExternal) { pending--; if (!pending) done(); return; }
          fetch(href, { cache: 'force-cache' })
            .then(function (r) { return r.blob(); })
            .then(function (blob) {
              var reader = new FileReader();
              reader.onload = function () {
                var dataUrl = String(reader.result || '');
                img.setAttribute('href', dataUrl);
                if (img.hasAttribute('xlink:href')) img.setAttribute('xlink:href', dataUrl);
                pending--; if (!pending) done();
              };
              reader.onerror = function () { pending--; if (!pending) done(); };
              reader.readAsDataURL(blob);
            })
            .catch(function () { pending--; if (!pending) done(); });
        });
      } catch (e) { done(); }
    }

    var svgToPng = function (svgEl) {
      try {
        var rect = svgEl.getBoundingClientRect();
        var scale = 2; // crisp capture (was effectively 1)
        var w = Math.max(240, Math.round((rect.width || 240) * scale));
        var h = Math.max(240, Math.round((rect.height || 240) * scale));
        var clone = svgEl.cloneNode(true);
        clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        clone.setAttribute('width', w);
        clone.setAttribute('height', h);
        inlineSvgImages(clone, function () {
          try {
            var src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(new XMLSerializer().serializeToString(clone));
            var img = new Image();
            img.onload = function () {
              try {
                // Pad to a square on white so square thumbnails
                // (object-fit: cover) never crop the product artwork.
                var size = Math.max(w, h);
                var canvas = document.createElement('canvas');
                canvas.width = size; canvas.height = size;
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, size, size);
                ctx.drawImage(img, (size - w) / 2, (size - h) / 2, w, h);
                finish(canvas.toDataURL('image/png'));
              } catch (e) { finish(imageUrl || ''); }
            };
            img.onerror = function () { finish(imageUrl || ''); };
            img.src = src;
          } catch (e) { finish(imageUrl || ''); }
        });
      } catch (e) { finish(imageUrl || ''); }
    };

    try {
      // Every category renders an inline <svg> inside #visual-canvas —
      // capture it directly for all categories (previously only flowers).
      var svgEl = document.querySelector('#visual-canvas svg') || document.querySelector('#visual-flowers-layer svg');
      if (svgEl) { svgToPng(svgEl); return; }
      // Fallback only: html2canvas on the live preview card.
      if (window.html2canvas) {
        var target = document.querySelector('#live-preview-box');
        if (target) {
          window.html2canvas(target, { backgroundColor: '#ffffff', scale: 2, useCORS: true, allowTaint: false, logging: false })
            .then(function (canvas) {
              try { finish(canvas.toDataURL('image/png')); }
              catch (e) { finish(imageUrl || ''); }
            })
            .catch(function () { finish(imageUrl || ''); });
          return;
        }
      }
      finish(imageUrl || '');
    } catch (e) {
      finish(imageUrl || '');
    }
  }

  function initAddToBag() {
    var btn = document.querySelector('[data-add-to-bag]');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var key = activeCategoryKey();
      var panel = document.querySelector('.customize-panel[data-panel="' + key + '"]');
      var totalEl = document.querySelector('[data-customize-total]');
      var total = totalEl ? parseFloat(totalEl.getAttribute('data-computed-total') || '0') : (BASE_PRICE[key] || 0);

      var chosenLabels = [];
      if (panel) {
        panel.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked').forEach(function (input) {
          var label = panel.querySelector('label[for="' + input.id + '"]');
          var text = label ? label.textContent.trim() : input.value;
          if (text) chosenLabels.push(text);
        });

        if (key === 'flowers') {
          var flowerQuantities = panel.querySelectorAll('[data-flower-quantity]');
          if (flowerQuantities.length) {
            chosenLabels = chosenLabels.filter(function (label) {
              return !flowerQuantities.length || true;
            });
            flowerQuantities.forEach(function (qty) {
              var amount = Math.max(0, parseInt(qty.value || '0', 10));
              if (amount > 0) {
                var flowerId = qty.getAttribute('data-flower-quantity');
                var flowerLabel = panel.querySelector('label[for="' + flowerId + '"]');
                var flowerName = flowerLabel ? flowerLabel.textContent.replace(/\\s*\\d+\\s*$/, '').trim() : flowerId;
                chosenLabels.push(flowerName + ': ' + amount);
              }
            });
          }
          var flowerCount = panel.querySelector('#flower-count');
          if (flowerCount) chosenLabels.push('Flower count: ' + (flowerQuantities.length ? Array.from(flowerQuantities).reduce(function (sum, qty) { return sum + (Number(qty.value) || 0); }, 0) : flowerCount.value));
          var specialRequest = panel.querySelector('.js-special-request');
          if (specialRequest && specialRequest.value.trim()) chosenLabels.push('Special request: ' + specialRequest.value.trim());
          var wrapperName = getSelectedColorName(panel, 'flower-wrap');
          var ribbonName = getSelectedColorName(panel, 'flower-ribbon');

          if (wrapperName) chosenLabels.push('Wrapper Color: ' + wrapperName);
          if (ribbonName) chosenLabels.push('Ribbon Color: ' + ribbonName);
        }
      }

      var letterChoice = panel ? panel.querySelector('input[name="letterAddon"]:checked') : null;
      var letterInput = panel ? panel.querySelector('#letter-text-input') : null;
      var requestInput = panel ? panel.querySelector('.js-special-request') : null;
      var snapshot = key === 'flowers' && document.querySelector('#visual-canvas') ? document.querySelector('#visual-canvas').innerHTML : '';
      var structured = {
        category: key,
        selectedOptions: chosenLabels.slice(),
        flowerCount: panel && panel.querySelector('#flower-count') ? Number(panel.querySelector('#flower-count').value || 0) : null,
        flowers: [],
        foliage: [],
        wrapping: '',
        wrapColor: getSelectedColorName(panel, 'flower-wrap') || '',
        ribbonColor: getSelectedColorName(panel, 'flower-ribbon') || '',
        letter: {
          type: letterChoice && letterChoice.value !== 'none' ? (letterChoice.value === 'love' ? 'Love letter' : 'Birthday letter') : '',
          message: letterChoice && letterChoice.value !== 'none' && letterInput ? letterInput.value.trim() : ''
        },
        specialRequest: requestInput ? requestInput.value.trim() : ''
      };
      if (panel) {
        panel.querySelectorAll('[data-flower-quantity]').forEach(function(qty) {
          var amount = Math.max(0, parseInt(qty.value || '0', 10));
          if (amount > 0) {
            var label = panel.querySelector('label[for="' + qty.getAttribute('data-flower-quantity') + '"]');
            structured.flowers.push({name: label ? label.textContent.replace(/\\s*\\d+\\s*$/, '').trim() : qty.getAttribute('data-flower-quantity'), quantity: amount});
          }
        });
        panel.querySelectorAll('input[name="foliage"]:checked').forEach(function(input) {
          var label = panel.querySelector('label[for="' + input.id + '"]');
          structured.foliage.push(label ? label.textContent.trim() : input.value);
        });
        var wrap = panel.querySelector('input[name="flower-wrap"]:checked');
        if (wrap) structured.wrapping = wrap.value;
      }
      var imageUrl = (panel && panel.querySelector('[data-product-image]')) ? panel.querySelector('[data-product-image]').getAttribute('src') : '';
      var fileInput = panel ? panel.querySelector('.js-special-request-images') : null;
      var files = fileInput ? Array.prototype.slice.call(fileInput.files || []) : [];

      function finish(snapshotPng, specialRequestImages) {
        var cart = Store.get('ac_cart', []);
        cart.push({
          id: 'item_' + Date.now(),
          category: key,
          name: categoryDisplayName(key) + ' — Custom',
          options: chosenLabels,
          customization: structured,
          image: snapshot,
          imageUrl: imageUrl,
          snapshotPng: snapshotPng || imageUrl || '',
          specialRequestImages: specialRequestImages || [],
          price: total || (BASE_PRICE[key] || 0),
          quantity: 1
        });
        Store.set('ac_cart', cart);
        toast('Added to your bag ✨');
        setTimeout(function () { navigateWithAnim('order-summary.php', 'anim-push-down'); }, 700);
      }

      // Upload reference photos; on failure we still add to cart with an empty list.
      var uploadPromise;
      if (files.length) {
        var fd = new FormData();
        files.forEach(function (f, i) { fd.append('images[' + i + ']', f); });
        uploadPromise = fetch('api/upload_special_request.php', { method: 'POST', body: fd, cache: 'no-store' })
          .then(function (r) { return r.json(); })
          .then(function (d) { return (d && d.ok && Array.isArray(d.files)) ? d.files : []; })
          .catch(function () { toast('Reference photos could not be uploaded — added without them.'); return []; });
      } else {
        uploadPromise = Promise.resolve([]);
      }

      // Capture the live preview as a PNG (falls back to imageUrl, then '').
      var snapshotPromise = new Promise(function (resolve) { captureLivePreviewPng(key, panel, resolve); });

      Promise.all([snapshotPromise, uploadPromise]).then(function (results) {
        finish(results[0] || '', results[1] || []);
      });
    });
  }

  /* Quick Buy ("Buy Now") handler */
  function initBuyNow() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-buy-now]');
      if (!btn) return;
      e.preventDefault();

      var card = btn.closest('.product-card');
      if (!card) return;

      var nameEl = card.querySelector('.product-card__name');
      var name = card.getAttribute('data-name') || (nameEl ? nameEl.textContent.trim() : 'Quick Item');
      var price = parseFloat(card.getAttribute('data-price') || '0');
      var category = card.getAttribute('data-category') || 'flowers';
      var rawOptions = card.getAttribute('data-options') || 'Standard preset';
      var optionsList = rawOptions ? rawOptions.split(',').map(function (s) { return s.trim(); }) : [];

      var cart = Store.get('ac_cart', []);
      cart.push({
        id: 'item_' + Date.now(),
        category: category,
        name: name,
        options: optionsList,
        price: price
      });
      Store.set('ac_cart', cart);
      toast('Added to your bag ✨');
      setTimeout(function () { navigateWithAnim('order-summary.php', 'anim-push-right'); }, 600);
    });
  }

  function categoryDisplayName(key) {
    return { flowers: 'Flower Bouquet', bags: 'Crochet Bag', headwears: 'Head Wear', keychains: 'Keychain' }[key] || key;
  }

  /* -----------------------------------------------------------------------
     8. Order summary page with slide-out item deletion
  --------------------------------------------------------------------- */
  function initOrderSummary() {
    function isValidSnapshotSrc(s){ s=String(s||''); if(!s) return false; if(/^(data:|https?:|\/\/)/i.test(s)) return true; return /^[a-zA-Z0-9_\/.\-]+\.(png|jpe?g|gif|webp|svg)$/i.test(s); }
    var list = document.querySelector('[data-cart-list]');
    if (!list) return;

    function render() {
      var cart = Store.get('ac_cart', []);
      list.innerHTML = '';
      if (!cart.length) {
        list.innerHTML = '<p style="font-size:13px;color:var(--ink-soft);text-align:center;padding:24px 0;">Your bag is empty. Tap "Add another item" to start customizing.</p>';
      }
      var subtotal = 0;
      cart.forEach(function (item) {
        subtotal += item.price;
        var validSnap = isValidSnapshotSrc(item.snapshotPng);
        var validUrl = isValidSnapshotSrc(item.imageUrl);
        var row = document.createElement('div');
        row.className = 'cart-item';
        row.innerHTML =
          '<div class="cart-item__thumb">' + (validSnap ? '<img src="' + escapeHtml(item.snapshotPng) + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:8px;display:block;" onerror="this.style.display=\'none\'">' : (validUrl ? '<img src="' + escapeHtml(item.imageUrl) + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:8px;display:block;" onerror="this.style.display=\'none\'">' : (item.image ? '<div class="custom-item-snapshot" aria-label="Custom bouquet preview">' + item.image + '</div>' : '\uD83C\uDF38'))) + '</div>' +
          '<div class="cart-item__body">' +
          '<div class="cart-item__name">' + escapeHtml(item.name) + ' <span class="cart-item__count">× ' + (Number(item.quantity) || 1) + '</span></div>' +
          '<div class="cart-item__opts">' + escapeHtml(item.options.join(', ') || 'Standard options') + '</div>' +
          '<div class="cart-item__row">' +
          '<span class="cart-item__price">\u20B1' + item.price.toLocaleString() + '</span>' +
          '<button class="cart-item__remove" data-remove="' + item.id + '">Remove</button>' +
          '</div></div>';
        list.appendChild(row);
      });

      var deliveryFee = cart.length ? 80 : 0;
      var total = subtotal + deliveryFee;
      setText('[data-sum-subtotal]', '\u20B1' + subtotal.toLocaleString());
      setText('[data-sum-delivery]', '\u20B1' + deliveryFee.toLocaleString());
      setText('[data-sum-total]', '\u20B1' + total.toLocaleString());
      window.dispatchEvent(new CustomEvent('ac:checkout-total-updated'));

      list.querySelectorAll('[data-remove]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-remove');
          var cartItemRow = btn.closest('.cart-item');
          
          if (cartItemRow) {
            cartItemRow.classList.add('removing');
            setTimeout(function () {
              var next = Store.get('ac_cart', []).filter(function (i) { return i.id !== id; });
              Store.set('ac_cart', next);
              render();
            }, 320);
          }
        });
      });
    }

    render();
  }

  function setText(selector, value) {
    document.querySelectorAll(selector).forEach(function (el) { el.textContent = value; });
  }
  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // Public helper for pages that render stored order status safely.
  window.ACStore.escapeHtml = escapeHtml;

  function initNumericInputs() {
    document.querySelectorAll('input[type=\"tel\"], input[type=\"number\"], input[data-numeric=\"true\"]').forEach(function (input) {
      input.setAttribute('inputmode', 'numeric');
      input.setAttribute('pattern', '[0-9]*');

      var field = input.closest('.field') || input.parentElement;
      var notice = field ? field.querySelector('[data-numeric-notice]') : null;
      if (!notice && field) {
        notice = document.createElement('span');
        notice.className = 'numeric-input-notice';
        notice.setAttribute('data-numeric-notice', '');
        notice.setAttribute('role', 'alert');
        notice.textContent = 'Type numbers only';
        notice.hidden = true;
        field.appendChild(notice);
      }

      function showNotice(show) {
        if (!notice) return;
        notice.hidden = !show;
        if (show) {
          if (field) field.classList.add('numeric-invalid');
          input.setAttribute('aria-invalid', 'true');
        } else {
          if (field) field.classList.remove('numeric-invalid');
          input.removeAttribute('aria-invalid');
        }
      }

      input.addEventListener('input', function () {
        var original = input.value;
        var cleaned = original.replace(/\D/g, '');
        var hadInvalidCharacters = original !== cleaned;
        if (hadInvalidCharacters) input.value = cleaned;
        showNotice(hadInvalidCharacters);
      });

      input.addEventListener('blur', function () {
        // Keep the notice hidden once the value is valid.
        if (/^\d*$/.test(input.value)) showNotice(false);
      });
    });
  }

  function initTermsAcceptance() {
    var checkbox = document.querySelector('[data-terms-checkbox]');
    var termsBtn = document.querySelector('[data-terms-open]');
    var modal = document.querySelector('#termsModal');
    if (!checkbox || !termsBtn || !modal) return;

    function open() { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
    function close() { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
    termsBtn.addEventListener('click', function (e) { e.preventDefault(); open(); });
    modal.querySelectorAll('[data-terms-close]').forEach(function (b) { b.addEventListener('click', close); });
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
  }

  function initOrderCancellation() {
    var modal = document.getElementById('cancelOrderModal');
    if (!modal) return;
    var pendingOrder = null;
    var confirmBtn = modal.querySelector('[data-cancel-confirm]');

    function close() { pendingOrder = null; modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
    function open(order) {
      pendingOrder = order;
      var text = modal.querySelector('[data-cancel-order-text]');
      if (text) text.textContent = 'Are you sure you want to cancel order ' + (order.id || '') + '?';
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
    }

   if (confirmBtn) confirmBtn.addEventListener('click', function () {
      if (!pendingOrder) return;
      var profile = Store.get('ac_profile', {});
      var email = String(profile.email || profile.userEmail || '').trim().toLowerCase();
      if (!email) { toast('Please log in again to cancel this order.'); return; }
      confirmBtn.disabled = true;
      fetch('api/orders.php?action=cancel', {
        method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({orderCode: pendingOrder.id, email: email}), cache: 'no-store'
      }).then(function(response) {
        return response.json().then(function(data) {
          if (!response.ok || !data.ok) throw new Error(data.error || 'Could not cancel this order.');
          var orders = Store.get('ac_order_history', []);
          var index = orders.findIndex(function(o) { return o.id === data.order.id; });
          if (index >= 0) orders[index] = data.order; else orders.unshift(data.order);
          Store.set('ac_order_history', orders);
          close();
          window.dispatchEvent(new CustomEvent('ac:orders-updated'));
          toast('Order cancellation saved.');
        });
      }).catch(function(error) {
        toast(error.message || 'Could not cancel this order.');
      }).finally(function() { confirmBtn.disabled = false; });
    });

    modal.querySelectorAll('[data-cancel-close]').forEach(function (b) { b.addEventListener('click', close); });
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    window.ACConfirmOrderCancellation = open;
  }

  function initPaymentAutofill() {
    var input = document.querySelector('[name="payerFullName"]');
    if (!input) return;
    var profile = Store.get('ac_profile', null);
    if (profile && profile.fullName) {
      input.value = profile.fullName;
      input.setAttribute('data-autofilled', 'true');
    }
    input.addEventListener('input', function () {
      var profile = Store.get('ac_profile', { fullName: '', email: '' });
      profile.fullName = input.value;
      Store.set('ac_profile', profile);
    });
  }

  function initPaymentMethodUI() {
    var box = document.getElementById('gcash-payment-box');
    if (!box) return;

    function getCheckoutTotal() {
      var totalEl = document.querySelector('[data-sum-total]');
      if (!totalEl) return 0;
      return Number(totalEl.textContent.replace(/[^\d.]/g, '')) || 0;
    }

    function sync() {
      var selected = document.querySelector('input[name="payMethod"]:checked');
      var total = getCheckoutTotal();
      var isFull = selected && selected.id === 'pay-gcash-full';
      var upfrontPercent = isFull ? 100 : 30;
      var upfront = Math.round(total * upfrontPercent) / 100;
      var remaining = Math.max(0, total - upfront);

      box.hidden = false;
      box.setAttribute('aria-hidden', 'false');

      var amountEl = box.querySelector('[data-upfront-gcash-amount]');
      var percentEl = box.querySelector('[data-upfront-gcash-percent]');
      var remainingEl = box.querySelector('[data-remaining-balance]');
      var instructionEl = box.querySelector('[data-payment-instruction]');
      var noteEl = box.querySelector('[data-payment-note]');

      if (amountEl) amountEl.textContent = '₱' + upfront.toLocaleString();
      if (percentEl) percentEl.textContent = upfrontPercent + '%';
      if (remainingEl) remainingEl.textContent = '₱' + remaining.toLocaleString();

      if (isFull) {
        if (instructionEl) instructionEl.textContent =
          'Your full order total must be paid upfront through GCash before the order is confirmed.';
        if (noteEl) noteEl.textContent =
          'The GCash payment will be initialized by the payment service in the next checkout step.';
      } else {
        if (instructionEl) instructionEl.textContent =
          'A 30% GCash down payment is required to confirm this COD order. The remaining 70% is payable in cash upon delivery.';
        if (noteEl) noteEl.textContent =
          'Only the 30% upfront amount is collected through GCash. The remaining balance is due on delivery.';
      }
    }

    document.querySelectorAll('input[name="payMethod"]').forEach(function (input) {
      input.addEventListener('change', sync);
    });

    // initOrderSummary updates the total after cart rendering.
    window.addEventListener('ac:checkout-total-updated', sync);
    sync();
    setTimeout(sync, 0);
  }

function initPlaceOrder() {
    var btn = document.querySelector('[data-place-order]');
    if (!btn) return;

    function getPaymentMethod() {
      var checked = document.querySelector('input[name="payMethod"]:checked');
      return checked ? (checked.id === 'pay-gcash-full' ? 'gcash_full' : 'cod_30_gcash_70_cod') : 'cod_30_gcash_70_cod';
    }

    btn.addEventListener('click', function () {
      var cart = Store.get('ac_cart', []);
      if (!cart.length) {
        toast('Your bag is empty');
        return;
      }

      var address = document.querySelector('input[placeholder="Delivery address"]');
      var contact = document.querySelector('input[placeholder="Contact number"]');
      var date = document.querySelector('input[type="date"]');
      var payer = document.querySelector('[name="payerFullName"]');
      var terms = document.querySelector('[data-terms-checkbox]');

      if (!address || !address.value.trim() || !contact || !contact.value.trim() || !date || !date.value || !payer || !payer.value.trim()) {
        toast('Please complete your delivery and payment details');
        return;
      }
      if (terms && !terms.checked) {
        toast('Please agree to the Terms & Conditions before placing your order.');
        return;
      }

      var method = getPaymentMethod();
      var subtotal = cart.reduce(function (sum, item) {
        return sum + (parseFloat(item.price) || 0);
      }, 0);
      var deliveryFee = cart.length ? 80 : 0;
      var total = subtotal + deliveryFee;
      var orderId = 'AC-' + Math.floor(100000 + Math.random() * 900000);

      var downPaymentPercent = method === 'cod_30_gcash_70_cod' ? 30 : 100;
      var upfrontGcashAmount = Math.round(total * downPaymentPercent) / 100;
      var remainingBalance = Math.max(0, total - upfrontGcashAmount);

      var order = {
        id: orderId,
        date: new Date().toISOString(),
        status: 'Preparing',
        paymentMethod: method,
        paymentStatus: 'Payment Pending',
        downPaymentStatus: method === 'cod_30_gcash_70_cod' ? 'Pending GCash Payment' : 'Not Applicable',
        downPaymentPercent: method === 'cod_30_gcash_70_cod' ? 30 : 100,
        downPaymentAmount: upfrontGcashAmount,
        upfrontGcashAmount: upfrontGcashAmount,
        remainingBalance: remainingBalance,
        gcashReference: '',
        gcashProofName: '',
        payerName: payer.value.trim(),
        address: address.value.trim(),
        contact: contact.value.trim(),
        deliveryDate: date.value,
        items: cart.slice(),
        subtotal: subtotal,
        deliveryFee: deliveryFee,
        total: total,
        termsAccepted: true,
        termsAcceptedAt: new Date().toISOString()
      };

      var profile = Store.get('ac_profile', {});
      var email = String(profile.email || profile.userEmail || '').trim().toLowerCase();
      if (!email) {
        toast('Please log in again so your order can be linked to your account.');
        return;
      }
      btn.disabled = true;
      btn.setAttribute('aria-busy', 'true');
      var payload = {
        customer: { email: email, fullName: profile.fullName || order.payerName },
        delivery: { payerFullName: order.payerName, address: order.address, contact: order.contact, date: order.deliveryDate },
        paymentMethod: method,
        termsAccepted: true,
        items: cart.map(function(item) {
          return {
            id: item.id, productCode: item.productCode || item.productId || '',
            category: item.category, name: item.name, quantity: Number(item.quantity || 1),
            price: Number(item.price || 0), options: item.options || [],
            customization: item.customization || {},
            image: item.image || '', imageUrl: item.imageUrl || '',
            snapshotPng: item.snapshotPng || '', specialRequestImages: item.specialRequestImages || []
          };
        })
      };
      fetch('api/orders.php?action=create', {
        method: 'POST', headers: {'Content-Type':'application/json', 'Accept':'application/json'},
        body: JSON.stringify(payload), cache: 'no-store'
      }).then(function(response) {
        return response.json().then(function(data) {
          if (!response.ok || !data.ok || !data.order) throw new Error(data.error || 'Could not save the order.');
          return data.order;
        });
      }).then(function(savedOrder) {
        var history = Store.get('ac_order_history', []);
        history = history.filter(function(existing) { return existing.id !== savedOrder.id; });
        history.unshift(savedOrder);
        Store.set('ac_order_history', history.slice(0, 50));
        Store.set('ac_last_order_id', savedOrder.id);
        window.ACShowModal('#successModal');
        var continueBtn = document.querySelector('[data-order-continue]');
        if (continueBtn) continueBtn.onclick = function() {
          Store.set('ac_cart', []);
          navigateWithAnim('order-success.php', 'anim-push-down');
        };
      }).catch(function(error) {
        toast(error.message || 'Order could not be saved. Please try again.');
      }).finally(function() {
        btn.disabled = false;
        btn.removeAttribute('aria-busy');
      });
    });
  }

/* -----------------------------------------------------------------------
     9. Order success page
  --------------------------------------------------------------------- */
  function initOrderSuccessId() {
    var el = document.querySelector('[data-order-id]');
    if (!el) return;
    var id = Store.get('ac_last_order_id', null);
    el.textContent = id || ('AC-' + Math.floor(100000 + Math.random() * 900000));

    var successMessage = document.querySelector('[data-payment-success]');
    var copy = document.querySelector('[data-confirmation-copy]');
    var paymentConfirmed = Store.get('ac_payment_success', false);
    if (successMessage && paymentConfirmed) {
      successMessage.hidden = false;
      if (copy) copy.textContent = 'Thank you for shopping with Aili\'s Crochet PH. Your payment has been confirmed and your order is now in the queue.';
    }
  }

  /* -----------------------------------------------------------------------
     10. Logout confirmation
  --------------------------------------------------------------------- */
  function initLogout() {
    var btn = document.querySelector('[data-confirm-logout]');
    if (!btn) return;
    btn.addEventListener('click', function () {
      Store.remove('ac_profile');
      Store.remove('ac_cart');
      navigateWithAnim('login.php', 'anim-push-left');
    });
  }

  /* -----------------------------------------------------------------------
     11. Splash / loading screen
  --------------------------------------------------------------------- */
  function initSplashRedirect() {
    var splash = document.querySelector('[data-splash]');
    if (!splash) return;
    setTimeout(function () { navigateWithAnim('login.php', 'anim-fade-in'); }, 1800);
  }

/* -----------------------------------------------------------------------
   Photorealistic Dynamic Flower Bouquet Live Visual Preview
   Updated:
   - Full Wrap: stems disappear into the wrap.
   - Ribbon: stems gather naturally into a single tied bundle.
   --------------------------------------------------------------------- */

var SVG_ASSET_PATHS = {
  'fl-rose': 'assets/images/customize/flowers/rose.svg',
  'fl-tulip': 'assets/images/customize/flowers/tulip.svg',
  'fl-sunflower': 'assets/images/customize/flowers/sunflower.svg',
  'fl-daisy': 'assets/images/customize/flowers/daisy.svg',
  'fl-peony': 'assets/images/customize/flowers/peony.svg',
  'fl-lavender': 'assets/images/customize/flowers/lavender.svg',
  'fl-hydrangea': 'assets/images/customize/flowers/hydrangea.svg',
  'fl-orchid': 'assets/images/customize/flowers/orchid.svg',
  'fl-carnation': 'assets/images/customize/flowers/carnation.svg',
  'fl-lily': 'assets/images/customize/flowers/lily.svg',

  'fo-eucalyptus': 'assets/images/customize/flowers/foliage/olive.svg',
  'fo-fern': 'assets/images/customize/flowers/foliage/fern.svg',
  'fo-babysbreath': 'assets/images/customize/flowers/foliage/bbplant.svg'
};

/* Real wrapping assets supplied by the project. */
var WRAP_ASSET_PATHS = {
  /* Full Wrap is intentionally split into two layers.
     Back sits behind flowers/stems; Front sits above the stems. */
  fullBack: 'assets/images/customize/flowers/wrap/fullwrapback.svg',
  fullFront: 'assets/images/customize/flowers/wrap/fullwrapfront.svg',
  ribbon: 'assets/images/customize/flowers/wrap/ribbon.svg'
};

/* Runtime colorized copies of the real wrapping assets. */
var WRAP_SVG_CACHE = {};
var WRAP_COLOR_CACHE = {};
var WRAP_RENDER_ASSETS = {
  fullBack: WRAP_ASSET_PATHS.fullBack,
  fullFront: WRAP_ASSET_PATHS.fullFront,
  ribbon: WRAP_ASSET_PATHS.ribbon
};

/* -----------------------------------------------------------------------
   Flower positions
   --------------------------------------------------------------------- */

var FLOWER_SLOTS = [
  { x: 100, y: 68, s: .56, r: -1,  z: 5 },
  { x: 76,  y: 57, s: .50, r: -16, z: 5 },
  { x: 126, y: 68, s: .50, r: 15,  z: 5 },

  { x: 65,  y: 55, s: .43, r: -55, z: 6 },
  { x: 135, y: 55, s: .43, r: 55,  z: 6 },

  { x: 84,  y: 68, s: .46, r: -8, z: 7 },
  { x: 116, y: 69, s: .46, r: 8,  z: 7 },

  { x: 85,  y: 72, s: .38, r: -50, z: 8 },
  { x: 100, y: 72, s: .38, r: -1,  z: 9 },
  { x: 114, y: 73, s: .39, r: 50,  z: 10 },

  // Additional compact positions let the bouquet render up to 24 flowers.
  { x: 72,  y: 67, s: .34, r: -38, z: 6 },
  { x: 91,  y: 57, s: .34, r: -8,  z: 6 },
  { x: 109, y: 57, s: .34, r: 8,   z: 6 },
  { x: 128, y: 67, s: .34, r: 38,  z: 6 },
  { x: 60,  y: 64, s: .29, r: -58, z: 7 },
  { x: 77,  y: 48, s: .30, r: -25, z: 7 },
  { x: 94,  y: 48, s: .30, r: -8,  z: 7 },
  { x: 106, y: 48, s: .30, r: 8,   z: 7 },
  { x: 123, y: 48, s: .30, r: 25,  z: 7 },
  { x: 140, y: 64, s: .29, r: 58,  z: 7 },
  { x: 68,  y: 57, s: .28, r: -42, z: 8 },
  { x: 86,  y: 43, s: .27, r: -18, z: 8 },
  { x: 114, y: 43, s: .27, r: 18,  z: 8 },
  { x: 132, y: 57, s: .28, r: 42,  z: 8 }
];

/* -----------------------------------------------------------------------
   Foliage stays between / behind flower heads.
   --------------------------------------------------------------------- */

var FOLIAGE_SLOTS = [
  { x: 67,  y: 76, w: 38, h: 72, r: -34, flip: false, z: 2 },
  { x: 133, y: 76, w: 38, h: 72, r: 34,  flip: true,  z: 2 },

  { x: 78,  y: 69, w: 34, h: 67, r: -19, flip: false, z: 3 },
  { x: 122, y: 69, w: 34, h: 67, r: 19,  flip: true,  z: 3 },

  { x: 94,  y: 65, w: 30, h: 60, r: -7, flip: false, z: 4 },
  { x: 106, y: 65, w: 30, h: 60, r: 7,  flip: true,  z: 4 }
];

function safeAttr(value) {
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

function selectedIds(panel, prefix) {
  var ids = [];

  if (!panel) {
    return prefix === 'fl-'
      ? ['fl-rose']
      : ['fo-eucalyptus'];
  }

  panel
    .querySelectorAll(
      'input[type="checkbox"]:checked,input[type="radio"]:checked'
    )
    .forEach(function (input) {

      var id = input.id || '';

      if (
        id.indexOf(prefix) === 0 &&
        SVG_ASSET_PATHS[id] &&
        ids.indexOf(id) === -1
      ) {
        ids.push(id);
      }
    });

  return ids.length
    ? ids
    : (prefix === 'fl-' ? ['fl-rose'] : ['fo-eucalyptus']);
}

/* -----------------------------------------------------------------------
   Detect selected wrapping.

   IMPORTANT:
   The old version only recognized "wrap-full".
   This version explicitly supports:
   - wrap-full
   - wrap-ribbon

   It also checks the input value/name text as a fallback.
   --------------------------------------------------------------------- */

function getSelectedColor(panel, groupName, fallback) {
  var input =
    panel &&
    panel.querySelector(
      '[data-color-group="' + groupName + '"] input:checked'
    );

  if (!input) {
    input = document.querySelector(
      '[data-color-group="' + groupName + '"] input:checked'
    );
  }

  if (input) {
    var swatch = input.closest('.color-swatch');
    if (swatch) {
      // Swatches may be populated with either backgroundColor or background.
      // Read both the inline value and the computed value so the selected
      // color is always passed to the preview renderer.
      var inlineColor = swatch.style.backgroundColor || '';
      if (inlineColor) return normalizeHexColor(inlineColor);

      var computed = window.getComputedStyle ? window.getComputedStyle(swatch).backgroundColor : '';
      if (computed) return normalizeHexColor(computed);
    }
  }

  return normalizeHexColor(fallback || '#ffb6d9');
}

function getSelectedColorName(panel, groupName) {
  var input =
    panel &&
    panel.querySelector(
      '[data-color-group="' + groupName + '"] input:checked'
    );

  if (!input) return '';

  var swatch = input.closest('.color-swatch');
  return swatch ? (swatch.title || '') : '';
}

function normalizeHexColor(color) {
  var value = String(color || '').trim();

  if (/^#[0-9a-f]{6}$/i.test(value)) {
    return value.toLowerCase();
  }

  var match = value.match(
    /^rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i
  );

  if (match) {
    return '#' + [1, 2, 3]
      .map(function (i) {
        return Math.max(0, Math.min(255, parseInt(match[i], 10)))
          .toString(16)
          .padStart(2, '0');
      })
      .join('');
  }

  return '#ffb6d9';
}

function colorLuminance(hex) {
  var value = normalizeHexColor(hex).slice(1);
  var r = parseInt(value.slice(0, 2), 16) / 255;
  var g = parseInt(value.slice(2, 4), 16) / 255;
  var b = parseInt(value.slice(4, 6), 16) / 255;

  return (0.2126 * r) + (0.7152 * g) + (0.0722 * b);
}

function shadeTargetColor(targetHex, sourceHex) {
  var target = normalizeHexColor(targetHex).slice(1);
  var tr = parseInt(target.slice(0, 2), 16);
  var tg = parseInt(target.slice(2, 4), 16);
  var tb = parseInt(target.slice(4, 6), 16);

  var sourceLum = colorLuminance(sourceHex);
  var factor = 0.35 + (sourceLum * 0.95);
  factor = Math.max(0.35, Math.min(1.10, factor));

  function scale(value) {
    return Math.max(0, Math.min(255, Math.round(value * factor)))
      .toString(16)
      .padStart(2, '0');
  }

  return '#' + scale(tr) + scale(tg) + scale(tb);
}

function colorizeWrapSVG(svgText, targetColor) {
  var whiteThreshold = 0.92;
  var target = normalizeHexColor(targetColor);

  return String(svgText).replace(
    /#[0-9a-fA-F]{6}\b/g,
    function (sourceHex) {
      if (colorLuminance(sourceHex) >= whiteThreshold) {
        return sourceHex;
      }
      return shadeTargetColor(target, sourceHex);
    }
  );
}

function svgToDataUri(svgText) {
  return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgText);
}

function loadWrapSVGAssets() {
  return Promise.all(
    Object.keys(WRAP_ASSET_PATHS).map(function (key) {
      var path = WRAP_ASSET_PATHS[key];

      return fetch(path)
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Unable to load ' + path);
          }
          return response.text();
        })
        .then(function (text) {
          WRAP_SVG_CACHE[path] = text;
        })
        .catch(function () {
          return null;
        });
    })
  );
}

function getColorizedWrapAsset(key, color) {
  var path = WRAP_ASSET_PATHS[key];
  var normalizedColor = normalizeHexColor(color);
  var cacheKey = key + '|' + normalizedColor;

  if (WRAP_COLOR_CACHE[cacheKey]) {
    return WRAP_COLOR_CACHE[cacheKey];
  }

  var source = WRAP_SVG_CACHE[path];
  if (!source) return path;

  var dataUri = svgToDataUri(
    colorizeWrapSVG(source, normalizedColor)
  );

  WRAP_COLOR_CACHE[cacheKey] = dataUri;
  return dataUri;
}

/* -----------------------------------------------------------------------
   Detect selected wrapping and its independent colors.
   --------------------------------------------------------------------- */

function getPreviewWrap(panel) {
  var wrapping =
    panel &&
    panel.querySelector('input[name="wrapping"]:checked');

  if (!wrapping) {
    wrapping = document.querySelector(
      'input[name="wrapping"]:checked'
    );
  }

  var wrappingId = wrapping
    ? String(wrapping.id || '').toLowerCase()
    : '';

  var wrappingValue = wrapping
    ? String(wrapping.value || '').toLowerCase()
    : '';

  var isFullWrap =
    wrappingId === 'wrap-full' ||
    wrappingValue === 'full' ||
    wrappingValue === 'full-wrap' ||
    wrappingValue === 'fullwrap';

  return {
    type: isFullWrap ? 'full' : 'ribbon',
    full: isFullWrap,
    ribbon: !isFullWrap,
    wrapperColor: getSelectedColor(panel, 'flower-wrap', '#ffb6d9'),
    ribbonColor: getSelectedColor(panel, 'flower-ribbon', '#ffb6d9')
  };
}

/* -----------------------------------------------------------------------
   SVG image helpers
   --------------------------------------------------------------------- */

function imageNode(path, x, y, width, height, cls, extra) {
  return (
    '<image class="' + (cls || '') +
    '" href="' + safeAttr(path) +
    '" x="' + x +
    '" y="' + y +
    '" width="' + width +
    '" height="' + height +
    '" preserveAspectRatio="xMidYMid meet"' +
    ' focusable="false"' +
    (extra || '') +
    '/>'
  );
}

function fullWrapBackImageNode(x, y, width, height, cls) {
  return imageNode(
    WRAP_RENDER_ASSETS.fullBack,
    x,
    y,
    width,
    height,
    cls || 'wrap-image wrap-image-back'
  );
}

function fullWrapFrontImageNode(x, y, width, height, cls) {
  return imageNode(
    WRAP_RENDER_ASSETS.fullFront,
    x,
    y,
    width,
    height,
    cls || 'wrap-image wrap-image-front'
  );
}

function ribbonImageNode(x, y, width, height) {
  return imageNode(
    WRAP_RENDER_ASSETS.ribbon,
    x,
    y,
    width,
    height,
    'ribbon-image'
  );
}


/* -----------------------------------------------------------------------
   Generic live preview renderers
   --------------------------------------------------------------------- */

/* Optional shop configuration:
   Set this to the shop's real GCash number before deployment to prefill it.
   It is intentionally blank here so the system never invents a payment number. */
var AC_GCASH_NUMBER = '';
window.AC_GCASH_NUMBER = AC_GCASH_NUMBER;

var CATEGORY_ASSETS = {
  bags: {
    'bag-baguette': 'assets/images/customize/bag/baguettebag.svg',
    'bag-purse': 'assets/images/customize/bag/purse.svg',
    'bag-sling': 'assets/images/customize/bag/slingbag.svg',
    'bag-deco-flower': 'assets/images/customize/bag/decoration/sunflower.svg',
    'bag-deco-zipper': 'assets/images/customize/bag/decoration/zipper hold.svg'
  },
  headwears: {
    'hw-hairclip': 'assets/images/customize/headwears/hairclip.svg',
    'hw-headband': 'assets/images/customize/headwears/headband.svg',
    'hw-bandana': 'assets/images/customize/headwears/bandana.svg',
    'hw-deco-bow': 'assets/images/customize/headwears/decoration/bowbarrete.svg',
    'hw-deco-flower': 'assets/images/customize/headwears/decoration/sunflower.svg',
    'hw-deco-bunny': 'assets/images/customize/headwears/decoration/bunnyears.svg'
  },
  keychains: {
    'kc-capybara': 'assets/images/customize/kechain/animal/capybara.svg',
    'kc-bunny': 'assets/images/customize/kechain/animal/bunny.svg',
    'kc-cat': 'assets/images/customize/kechain/animal/cat.svg',
    'kc-fl-rose': 'assets/images/customize/kechain/flower/rose.svg',
    'kc-fl-tulip': 'assets/images/customize/kechain/flower/tulip.svg',
    'kc-fl-sunflower': 'assets/images/customize/kechain/flower/sunflower.svg',
    'kc-fl-daisy': 'assets/images/customize/kechain/flower/daisy.svg',
    'kc-fl-peony': 'assets/images/customize/kechain/flower/peony.svg',
    'kc-fl-lavender': 'assets/images/customize/kechain/flower/lavender.svg',
    'kc-fl-hydrangea': 'assets/images/customize/kechain/flower/hydrangea.svg',
    'kc-fl-orchid': 'assets/images/customize/kechain/flower/orchid.svg',
    'kc-fl-carnation': 'assets/images/customize/kechain/flower/carnation.svg',
    'kc-fl-lily': 'assets/images/customize/kechain/flower/lily.svg'
  }
};

/* Flexible product SVG assets. Preload ALL products and decorations into the cache. */
var PRODUCT_SVG_CACHE = {};
var PRODUCT_COLOR_CACHE = {};
var FLEXIBLE_PRODUCT_PATHS = [];

Object.keys(CATEGORY_ASSETS).forEach(function (category) {
  Object.keys(CATEGORY_ASSETS[category]).forEach(function (id) {
    var path = CATEGORY_ASSETS[category][id];
    if (FLEXIBLE_PRODUCT_PATHS.indexOf(path) === -1) FLEXIBLE_PRODUCT_PATHS.push(path);
  });
});

function mixHex(a, b, amount) {
  var ah = normalizeHexColor(a).slice(1);
  var bh = normalizeHexColor(b).slice(1);
  var ar = parseInt(ah.slice(0,2),16), ag = parseInt(ah.slice(2,4),16), ab = parseInt(ah.slice(4,6),16);
  var br = parseInt(bh.slice(0,2),16), bg = parseInt(bh.slice(2,4),16), bb = parseInt(bh.slice(4,6),16);
  function c(x,y){ return Math.round(x + (y-x)*amount).toString(16).padStart(2,'0'); }
  return '#' + c(ar,br) + c(ag,bg) + c(ab,bb);
}

function colorizeFlexibleProductSVG(svgText, targetColor) {
  var target = normalizeHexColor(targetColor);
  var light = mixHex(target, '#ffffff', .42);
  var dark = mixHex(target, '#3d2632', .50);
  var source = String(svgText || '');

  /* Dynamic product SVGs use #FF00FF as the editable-material marker.
     Keep the older three-color palette replacements for backwards
     compatibility with previously prepared product artwork. */
  return source
    .replace(/#FF00FF/gi, target)
    .replace(/#C982A1/gi, target)
    .replace(/#EFD3DE/gi, light)
    .replace(/#7B405C/gi, dark)
    .replace(/#ffb6d9/gi, target);
}

function loadFlexibleProductSVGAssets() {
  return Promise.all(FLEXIBLE_PRODUCT_PATHS.map(function (path) {
    return fetch(path).then(function (response) {
      if (!response.ok) throw new Error('Unable to load ' + path);
      return response.text();
    }).then(function (text) {
      PRODUCT_SVG_CACHE[path] = text;
    }).catch(function () { return null; });
  }));
}

function getFlexibleProductAsset(path, color) {
  var source = PRODUCT_SVG_CACHE[path];
  if (!source) return path;
  var normalized = normalizeHexColor(color);
  var key = path + '|' + normalized;
  if (!PRODUCT_COLOR_CACHE[key]) {
    PRODUCT_COLOR_CACHE[key] = svgToDataUri(colorizeFlexibleProductSVG(source, normalized));
  }
  return PRODUCT_COLOR_CACHE[key];
}

/* Public helper for product SVGs that use #FF00FF as their dynamic-material
   marker. */
window.ACColorizeProductSVG = function (svgText, selectedColor) {
  return colorizeFlexibleProductSVG(svgText, selectedColor);
};

function getCheckedId(panel, selector) {
  var el = panel && panel.querySelector(selector);
  return el ? el.id : '';
}

function getPreviewColor(panel, group, fallback) {
  return getSelectedColor(panel, group, fallback || '#ffb6d9');
}

function tintRectForImage(color, opacity) {
  return '<rect x="0" y="0" width="200" height="220" fill="' + safeAttr(color) + '" opacity="' + (opacity || .72) + '" style="mix-blend-mode:multiply" pointer-events="none" mask="url(#itemColorMask)"/>';
}

function imageNodeWithFilter(src, x, y, width, height, cls, filter) {
  return '<image href="' + safeAttr(src) + '" x="' + x + '" y="' + y + '" width="' + width + '" height="' + height + '" preserveAspectRatio="xMidYMid meet" class="' + (cls || '') + '"' + (filter ? ' filter="' + safeAttr(filter) + '"' : '') + ' />';
}

function imageNodeTransformed(path, x, y, width, height, cls, transform) {
  return '<g class="' + (cls || '') + '" transform="' + safeAttr(transform || '') + '">' +
    imageNode(path, x, y, width, height, '') +
  '</g>';
}

function buildProductSvgStart(ariaLabel, maskAsset, mx, my, mw, mh, maskId) {
  var defs =
    '<filter id="productShadow" x="-35%" y="-35%" width="170%" height="180%"><feDropShadow dx="0" dy="4" stdDeviation="3.4" flood-color="#2b2230" flood-opacity=".18"/></filter>' +
    '<filter id="smallShadow" x="-35%" y="-35%" width="170%" height="180%"><feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-color="#2b2230" flood-opacity=".16"/></filter>';
  if (maskAsset) {
    defs += '<mask id="' + safeAttr(maskId) + '" maskUnits="userSpaceOnUse" mask-type="alpha" x="0" y="0" width="200" height="220"><image href="' + safeAttr(maskAsset) + '" x="' + mx + '" y="' + my + '" width="' + mw + '" height="' + mh + '" preserveAspectRatio="xMidYMid meet"/></mask>';
  }
  return '<svg class="product-preview-svg" viewBox="0 0 200 220" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' + safeAttr(ariaLabel) + '">' + '<defs>' + defs + '</defs>';
}

function itemTintLayer(color, maskId, opacity) {
  return '<rect x="0" y="0" width="200" height="220" fill="' + safeAttr(color) + '" opacity="' + (opacity || .55) + '" style="mix-blend-mode:multiply" mask="url(#' + safeAttr(maskId) + ')" pointer-events="none"/>';
}

function finishProductSvg(canvas, svg, category, color) {
  svg += '</svg>';
  canvas.innerHTML = svg;
  canvas.setAttribute('data-rendered-category', category);
  canvas.classList.remove('preview-refresh');
  void canvas.offsetWidth;
  canvas.classList.add('preview-refresh');
  canvas.style.setProperty('--preview-accent', normalizeHexColor(color || '#ffb6d9'));
}

/* ----------------------------- BAGS ----------------------------------
   Each bag has its own coordinate system. Decorations are anchored to
   physical features of that exact bag rather than one generic overlay.
   --------------------------------------------------------------------- */
function renderBagPreview(panel, canvas) {
  var assetMap = CATEGORY_ASSETS.bags;
  var selected = panel && panel.querySelector('input[name="bagType"]:checked');
  var id = selected ? selected.id : 'bag-baguette';
  var color = getPreviewColor(panel, 'bag-color', '#d98fae');

  var layouts = {
    'bag-baguette': {
      main: {x:33,y:20,w:134,h:168},
      surface: {x:73,y:120,w:31,h:31,r:-4},
      zipper: {x:100,y:105,w:50,h:50,r:-12}
    },
    'bag-purse': {
      main: {x:31,y:28,w:138,h:155},
      surface: {x:64,y:90,w:40,h:40,r:2},
      zipper: {x:90,y:50,w:70,h:70,r:70}
    },
    'bag-sling': {
      main: {x:-20,y:-55,w:236,h:270},
      surface: {x:58,y:135,w:31,h:31,r:-6},
      zipper: {x:85,y:131,w:50,h:50,r:-10}
    }
  };
  var g = layouts[id] || layouts['bag-baguette'];
  var productAsset = getFlexibleProductAsset(assetMap[id], color);

  var svg = buildProductSvgStart('Dynamic ' + id + ' crochet bag preview');
  svg += '<ellipse cx="100" cy="201" rx="58" ry="8" fill="#2b2230" opacity=".055"/>';
  svg += '<g filter="url(#productShadow)">' +
    imageNode(productAsset, g.main.x,g.main.y,g.main.w,g.main.h,'bag-main') +
  '</g>';

  var flower = panel && panel.querySelector('#bag-deco-flower:checked');
  var zipper = panel && panel.querySelector('#bag-deco-zipper:checked');

  /* Surface decoration: always clipped visually to the body area by using
     the exact body anchor for each bag, never the zipper/edge coordinates. */
  if (flower) {
    svg += imageNodeTransformed(
      getFlexibleProductAsset(assetMap['bag-deco-flower'], color),
      g.surface.x, g.surface.y, g.surface.w, g.surface.h,
      'bag-surface-decoration',
      'rotate(' + g.surface.r + ' 99 106)'
    );
  }

  /* Zipper holder: positioned at the zipper seam and kept small enough to
     read as a pull/holder instead of a second bag decoration. */
  if (zipper) {
    svg += imageNodeTransformed(
      getFlexibleProductAsset(assetMap['bag-deco-zipper'], color),
      g.zipper.x, g.zipper.y, g.zipper.w, g.zipper.h,
      'bag-zipper-holder',
      'rotate(' + g.zipper.r + ' 138 66)'
    );
  }

  finishProductSvg(canvas, svg, 'bags', color);
}

/* --------------------------- HEADWEARS ------------------------------- */
function renderHeadwearPreview(panel, canvas) {
  var assetMap = CATEGORY_ASSETS.headwears;
  var selected = panel && panel.querySelector('input[name="headwearType"]:checked');
  var id = selected ? selected.id : 'hw-hairclip';
  var deco = panel && panel.querySelector('input[name="headwearDecoration"]:checked');
  var decoId = deco ? deco.id : 'hw-deco-bow';
  /* Keep the main headwear color separate from decoration colors.
     Bow Barrette and Bunny Ear have their own color groups in customize.php. */
  var color = getPreviewColor(panel, 'headwear-color', '#ff00ff');
  var bowColor = getPreviewColor(panel, 'headwear-bow-color', '#ffb6d9');
  var bunnyColor = getPreviewColor(panel, 'headwear-bunny-color', '#ffb6d9');
  var productAsset = getFlexibleProductAsset(assetMap[id], color);

  var svg = buildProductSvgStart('Dynamic ' + id + ' preview');
  svg += '<ellipse cx="100" cy="199" rx="45" ry="7" fill="#2b2230" opacity=".055"/>';

  if (id === 'hw-hairclip') {
    /* Hair clip is deliberately smaller. Decoration occupies the clip's
       center so the bow/flower visually covers the metal/plastic base. */
    svg += '<g filter="url(#productShadow)">' + imageNode(productAsset,69,101,62,29,'hw-hairclip-main') + '</g>';
    if (decoId === 'hw-deco-bow') {
      svg += imageNodeTransformed(getFlexibleProductAsset(assetMap[decoId], bowColor),50,86,100,100,'hw-hairclip-bow','rotate(-8 100 112)');
    } else if (decoId === 'hw-deco-flower') {
      svg += imageNodeTransformed(getFlexibleProductAsset(assetMap[decoId], color),79,83,45,45,'hw-hairclip-flower','rotate(-10 101 105)');
    }
    /* Bunny ears intentionally do not render on hair clips. */
  }

  if (id === 'hw-headband') {
    /* Headband is rendered as a curved band first, then the decoration sits
       on its crown so bunny ears emerge naturally from the band. */
    svg += '<g filter="url(#productShadow)">' + imageNode(productAsset,45,42,110,137,'hw-headband-main') + '</g>';
    if (decoId === 'hw-deco-bow') {
      svg += imageNodeTransformed(getFlexibleProductAsset(assetMap[decoId], bowColor),31,62,81,65,'hw-headband-bow','rotate(-7 98 75)');
    } else if (decoId === 'hw-deco-flower') {
      svg += imageNodeTransformed(getFlexibleProductAsset(assetMap[decoId], color),61,62,30,30,'hw-headband-flower','rotate(-10 101 73)');
    } else if (decoId === 'hw-deco-bunny') {
      svg += imageNodeTransformed(getFlexibleProductAsset(assetMap[decoId], bunnyColor),-1,-45,200,200,'hw-headband-bunny','rotate(-3 100 78)');
    }
  }

  if (id === 'hw-bandana') {
    /* Bandana is its own silhouette. The decoration sits on the front fold,
       not as a free-floating layer away from the scarf shape. */
    svg += '<g filter="url(#productShadow)">' + imageNode(productAsset,37,35,126,145,'hw-bandana-main') + '</g>';
    if (decoId === 'hw-deco-flower') {
      svg += imageNodeTransformed(getFlexibleProductAsset(assetMap[decoId], color),114,62,35,35,'hw-bandana-flower','rotate(-14 105 92)');
    } 
  }

  finishProductSvg(canvas, svg, 'headwears', color);
}

/* ---------------------------- KEYCHAINS ------------------------------ */
function renderKeychainPreview(panel, canvas) {
  var type = panel && panel.querySelector('input[name="keychainType"]:checked');
  var isFlower = type && type.getAttribute('data-charm-toggle') === 'flower';
  var selected = panel && panel.querySelector(isFlower ? 'input[name="flowerCharm"]:checked' : 'input[name="animalCharm"]:checked');
  var id = selected ? selected.id : (isFlower ? 'kc-fl-rose' : 'kc-capybara');
  var assetMap = CATEGORY_ASSETS.keychains;
  var asset = assetMap[id];
  // Animals retain their original illustrated colors. Only flower keychains
  // participate in the configurable color system.
  var flowerColor = getPreviewColor(panel, 'keychain-color', '#d98fae');
  var productAsset = isFlower ? getFlexibleProductAsset(asset, flowerColor) : asset;

  var lengthInput = panel && panel.querySelector('#chain-length');
  var inches = lengthInput ? Math.max(1, Math.min(8, parseInt(lengthInput.value,10) || 3)) : 3;
  var chainCount = 5 + inches * 3;
  var topY = 30;
  var bottomY = 40 + inches * 17;
  var charmTop = Math.min(bottomY - 25, 175);

  var charmW = isFlower ? 54 : 82;
  var charmH = isFlower ? 74: 82;
  var charmX = 100 - charmW / 2;

  var svg = buildProductSvgStart(
    'Dynamic ' + (isFlower ? 'flower' : 'animal') + ' keychain preview',
    null, 0, 0, 0, 0, 'keychainColorMask'
  );
  svg += '<ellipse cx="100" cy="202" rx="34" ry="6" fill="#2b2230" opacity=".05"/>';

  /* Mini metal chain: alternating oval links give a readable physical chain
     and its visible length changes directly with the slider. */
  svg += '<g class="mini-metal-chain" fill="none" stroke="#8e9298" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">';
  svg += '<circle cx="100" cy="24" r="5" stroke="#70757b" stroke-width="2"/>';
  for (var i = 0; i < chainCount; i++) {
    var t = chainCount === 1 ? 0 : i / (chainCount - 1);
    var y = topY + (bottomY - topY) * t;
    var rot = i % 2 ? 100 : 0;
    svg += '<ellipse cx="100" cy="' + y.toFixed(2) + '" rx="3" ry="5.1" transform="rotate(' + rot + ' 100 ' + y.toFixed(2) + ')"/>';
  }
  svg += '<circle cx="100" cy="' + bottomY.toFixed(2) + '" r="3.6" stroke="#70757b"/>';
  svg += '</g>';

  /* The charm is deliberately anchored below the final link. Animals use
     animal SVGs; flowers use only flower SVGs, so the two branches cannot
     accidentally display one another's assets. */
  svg += '<g filter="url(#smallShadow)">' +
    imageNode(productAsset, charmX, charmTop, charmW, charmH, 'keychain-charm') +
  '</g>';

  finishProductSvg(canvas, svg, 'keychains', isFlower ? flowerColor : '#8e9298');
}

function renderGenericVisualPreview(category, panel, canvas) {
  if (category === 'bags') return renderBagPreview(panel, canvas);
  if (category === 'headwears') return renderHeadwearPreview(panel, canvas);
  if (category === 'keychains') return renderKeychainPreview(panel, canvas);
  canvas.innerHTML = '';
}

/* -----------------------------------------------------------------------
   Main bouquet renderer
   --------------------------------------------------------------------- */

function renderVisualSVGPreview() {
  var canvas = document.getElementById('visual-canvas');

  if (!canvas) return;

  var tab = document.querySelector(
    '[data-category-tab].active'
  );

  var category = tab
    ? tab.getAttribute('data-category-tab')
    : 'flowers';

  var panel = document.querySelector(
    '.customize-panel[data-panel="' + category + '"]'
  );

  if (category !== 'flowers') {
    renderGenericVisualPreview(category, panel, canvas);
    return;
  }

  var flowerIds = [];
  if (panel) {
    panel.querySelectorAll('[data-flower-quantity]').forEach(function(qty) {
      var amount = Math.max(0, Math.min(24, parseInt(qty.value || '0', 10) || 0));
      var id = qty.getAttribute('data-flower-quantity');
      for (var n = 0; n < amount; n++) flowerIds.push(id);
    });
  }
  if (!flowerIds.length) flowerIds = selectedIds(panel, 'fl-');
  var flowerCount = Math.max(1, Math.min(FLOWER_SLOTS.length, flowerIds.length));
  var activeFlowerSlots = FLOWER_SLOTS.slice(0, flowerCount);
  var foliageIds = selectedIds(panel, 'fo-');
  var wrap = getPreviewWrap(panel);

  WRAP_RENDER_ASSETS.fullBack = getColorizedWrapAsset(
    'fullBack',
    wrap.wrapperColor
  );
  WRAP_RENDER_ASSETS.fullFront = getColorizedWrapAsset(
    'fullFront',
    wrap.wrapperColor
  );
  WRAP_RENDER_ASSETS.ribbon = getColorizedWrapAsset(
    'ribbon',
    wrap.ribbonColor
  );

  var svg =
    '<svg class="bouquet-svg" ' +
    'viewBox="0 0 200 200" ' +
    'xmlns="http://www.w3.org/2000/svg" ' +
    'aria-label="Natural dynamic flower bouquet preview" ' +
    'role="img">' +

    '<defs>' +

      '<radialGradient id="petalLight" cx="40%" cy="27%" r="72%">' +
        '<stop offset="0" stop-color="#fff" stop-opacity=".20"/>' +
        '<stop offset=".55" stop-color="#fff" stop-opacity="0"/>' +
        '<stop offset="1" stop-color="#2b2230" stop-opacity=".09"/>' +
      '</radialGradient>' +

      '<filter id="bouquetShadow" ' +
        'x="-35%" y="-35%" width="170%" height="175%">' +
        '<feDropShadow ' +
          'dx="0" dy="2.2" stdDeviation="2.5" ' +
          'flood-color="#2b2230" flood-opacity=".18"/>' +
      '</filter>' +

      '<filter id="softFlowerShadow" ' +
        'x="-25%" y="-25%" width="150%" height="150%">' +
        '<feDropShadow ' +
          'dx="0" dy="1.1" stdDeviation="1.35" ' +
          'flood-color="#2b2230" flood-opacity=".14"/>' +
      '</filter>' +

    '</defs>';

  /* -------------------------------------------------------------------
     1. BACK OF FULL WRAP

     fullwrapback.svg is rendered behind the bouquet.
     ------------------------------------------------------------------- */

  if (wrap.full) {
    svg +=
      '<g class="wrap-layer wrap-back" ' +
      'filter="url(#bouquetShadow)" ' +
      'pointer-events="none">' +

        fullWrapBackImageNode(
          23,
          92,
          154,
          96
        ) +

      '</g>';
  }

  /* -------------------------------------------------------------------
     2. INTERNAL FOLIAGE
     ------------------------------------------------------------------- */

  svg += '<g class="foliage-layer">';

  FOLIAGE_SLOTS
    .slice()
    .sort(function (a, b) {
      return a.z - b.z;
    })
    .forEach(function (slot, i) {

      var id = foliageIds[i % foliageIds.length];

      /*
       * Ribbon bouquet adjustment:
       * keep every foliage item's ORIGINAL z value, but pull the foliage
       * inward and downward so its lower ends sit directly above the
       * ribbon tie instead of spreading too far left/right.
       *
       * Only x / y / rotation are changed here. z is untouched.
       */
      var renderSlot = slot;

      if (wrap.ribbon) {
        var ribbonFoliage = [
          { x: 84,  y: 84, w: 38, h: 72, r: -18 },
          { x: 92,  y: 81, w: 34, h: 67, r: -10 },
          { x: 97,  y: 79, w: 30, h: 60, r: -5  },
          { x: 103, y: 79, w: 30, h: 60, r: 5   },
          { x: 108, y: 81, w: 34, h: 67, r: 10  },
          { x: 116, y: 84, w: 38, h: 72, r: 18  }
        ][i % 6];

        renderSlot = {
          x: ribbonFoliage.x,
          y: ribbonFoliage.y,
          w: ribbonFoliage.w,
          h: ribbonFoliage.h,
          r: ribbonFoliage.r,
          flip: slot.flip,
          z: slot.z
        };
      }

      var transform =
        'translate(' + renderSlot.x + ' ' + renderSlot.y + ') ' +
        'rotate(' + renderSlot.r + ') ' +
        (renderSlot.flip
          ? 'scale(-1 1)'
          : '');

      svg +=
        '<g class="foliage-element" ' +
        'transform="' + transform + '" ' +
        'opacity=".94">' +

          imageNode(
            SVG_ASSET_PATHS[id],
            -renderSlot.w / 2,
            -renderSlot.h / 2,
            renderSlot.w,
            renderSlot.h,
            'foliage-image'
          ) +

        '</g>';
    });

  svg += '</g>';

  /* -------------------------------------------------------------------
     3. STEMS

     FULL WRAP:
       Stems converge below the flowers and stop INSIDE the wrapping.

     RIBBON:
       Stems are longer and visibly gather toward a single tie point,
       just like a hand-tied bouquet.
     ------------------------------------------------------------------- */

  /*
   * Ribbon tie contact is raised slightly so the exposed stems above the
   * bow are shorter and more compact. Full Wrap keeps its existing point.
   */
  var stemTieY = wrap.ribbon ? 151 : 176;

  svg +=
    '<g class="stem-layer ' +
    (wrap.ribbon ? 'stem-layer-ribbon' : 'stem-layer-full') +
    '" ' +
    'fill="none" ' +
    'stroke="#4d7654" ' +
    'stroke-linecap="round" ' +
    'opacity=".82">';

  /*
   * Match stem endpoints to the same ordered flower slots and count-based
   * vertical reflow used by the visible flower heads below. This prevents
   * stems from ending at stale slot coordinates above/beside a moved head.
   */
  var stemFlowerSlots = activeFlowerSlots.slice().sort(function (a, b) {
    return a.z - b.z;
  });
  var stemRibbonY = [
    46, 50, 49, 61, 61, 66, 67, 72, 71, 72, 72, 43, 39, 38, 39, 43,
    58, 69, 76, 76, 69, 52, 52, 61
  ];
  var stemCenterPull = flowerCount <= 1 ? 0 :
    Math.min(1, 0.28 + (flowerCount - 2) * (0.72 / 22));

  stemFlowerSlots.forEach(function (slot, index) {
    var centerX = 100 + (slot.x - 100) * 0.64;
    var baseFlowerY = wrap.ribbon
      ? (stemRibbonY[index] !== undefined ? stemRibbonY[index] : slot.y)
      : slot.y;
    var stemFlowerY = (flowerCount === 1 ? 62 : 66) +
      (baseFlowerY - 66) * stemCenterPull;
    var stemEndY = stemFlowerY + 7;

    if (wrap.ribbon) {
      var ribbonStemX = 100 + (slot.x - 100) * 0.30;
      var controlX = 100 + (slot.x - 100) * 0.34 +
        (index % 2 === 0 ? -1.2 : 1.0);
      var controlY = 110 + (index % 3) * 2;

      svg +=
        '<path d="M100 ' + stemTieY +
        ' Q' + controlX + ' ' + controlY +
        ' ' + ribbonStemX + ' ' + stemEndY +
        '" stroke-width="' +
        (index % 3 === 0 ? '4.0' : '1.5') +
        '"/>';
    } else {
      svg +=
        '<path d="M100 ' + stemTieY +
        ' Q' + centerX + ' 121 ' +
        slot.x + ' ' + stemEndY +
        '" stroke-width="1.8"/>';
    }
  });

  svg += '</g>';

  /* -------------------------------------------------------------------
     4. EXTENDED STEMS BELOW RIBBON

     All lower stems start at the same bottom-center contact point of the
     ribbon, then separate gradually. This makes the bundle look physically
     tied together rather than like several independent lines.
     ------------------------------------------------------------------- */

  if (wrap.ribbon) {
    svg +=
      '<g class="extended-stems" ' +
      'fill="none" ' +
      'stroke="#4d7654" ' +
      'stroke-linecap="round" ' +
      'opacity=".88">' +

        '<path d="M100 135 Q96 172 90 187" stroke-width="1.75"/>' +
        '<path d="M100 135 Q98 174 95 199" stroke-width="1.9"/>' +
        '<path d="M100 135 Q99 176 98 199" stroke-width="1.65"/>' +
        '<path d="M100 135 Q100 177 100 199" stroke-width="2.05"/>' +
        '<path d="M100 135 Q101 176 102 199" stroke-width="1.65"/>' +
        '<path d="M100 135 Q102 174 105 198" stroke-width="1.9"/>' +
        '<path d="M100 135 Q104 172 110 196" stroke-width="1.7"/>' +

      '</g>';
  }

  /* -------------------------------------------------------------------
     5. FLOWER HEADS
     ------------------------------------------------------------------- */

  svg +=
    '<g class="flowers-layer" ' +
    'filter="url(#softFlowerShadow)">';

  activeFlowerSlots
    .slice()
    .sort(function (a, b) {
      return a.z - b.z;
    })
    .forEach(function (slot, i) {

      var id = flowerIds[i % flowerIds.length];

      /*
       * Ribbon bouquet: tighten flower heads without changing their z
       * ordering. Only position/rotation/scale are adjusted for this mode.
       */
      var renderFlower = slot;

      if (wrap.ribbon) {
        var ribbonFlowerLayout = [
          { x: 86,  y: 46, s: .48, r: -10 },
          { x: 78,  y: 50, s: .44, r: -12 },
          { x: 114, y: 49, s: .44, r: 12  },
          { x: 82,  y: 61, s: .40, r: -28 },
          { x: 118, y: 61, s: .40, r: 28  },
          { x: 91,  y: 66, s: .43, r: -6  },
          { x: 109, y: 67, s: .43, r: 6   },
          { x: 94,  y: 72, s: .35, r: -22 },
          { x: 100, y: 71, s: .36, r: 0   },
          { x: 106, y: 72, s: .35, r: 22  },
          { x: 69,  y: 58, s: .31, r: -42 },
          { x: 75,  y: 43, s: .30, r: -26 },
          { x: 88,  y: 39, s: .29, r: -12 },
          { x: 100, y: 38, s: .30, r: 0   },
          { x: 112, y: 39, s: .29, r: 12  },
          { x: 125, y: 43, s: .30, r: 26  },
          { x: 131, y: 58, s: .31, r: 42  },
          { x: 73,  y: 69, s: .28, r: -36 },
          { x: 83,  y: 76, s: .27, r: -20 },
          { x: 117, y: 76, s: .27, r: 20  },
          { x: 127, y: 69, s: .28, r: 36  },
          { x: 92,  y: 52, s: .28, r: -8  },
          { x: 108, y: 52, s: .28, r: 8   },
          { x: 100, y: 61, s: .27, r: 0   }
        ];
        var ribbonFlowers = ribbonFlowerLayout[i];

        renderFlower = {
          x: ribbonFlowers.x,
          y: ribbonFlowers.y,
          s: ribbonFlowers.s,
          r: ribbonFlowers.r,
          z: slot.z
        };
      }

      /*
       * Reflow the visible heads for the selected quantity. Small bouquets
       * are pulled toward the visual center and enlarged; larger bouquets
       * retain the wider arrangement. Apply after the wrap-specific layout
       * so both ribbon and full-wrap previews behave consistently.
       */
      var centerPull = flowerCount <= 1 ? 0 : Math.min(1, 0.28 + (flowerCount - 2) * (0.72 / 22));
      var sizeBoost = flowerCount === 1 ? 1.38 :
        flowerCount === 2 ? 1.22 :
        flowerCount === 3 ? 1.14 :
        flowerCount === 4 ? 1.09 :
        flowerCount === 5 ? 1.05 : 1;
      renderFlower = {
        x: 100 + (renderFlower.x - 100) * centerPull,
        y: (flowerCount === 1 ? 62 : 66) + (renderFlower.y - 66) * centerPull,
        s: renderFlower.s * sizeBoost,
        r: flowerCount <= 2 ? renderFlower.r * 0.35 : renderFlower.r,
        z: renderFlower.z
      };

      svg +=
        '<g class="flower-element" ' +
        'transform="translate(' +
        renderFlower.x +
        ' ' +
        renderFlower.y +
        ') rotate(' +
        renderFlower.r +
        ') scale(' +
        renderFlower.s +
        ')">' +

          imageNode(
            SVG_ASSET_PATHS[id],
            -50,
            -50,
            100,
            100,
            'flower-image'
          ) +

          '<circle ' +
            'cx="0" cy="0" r="47" ' +
            'fill="url(#petalLight)" ' +
            'opacity=".40" ' +
            'pointer-events="none"/>' +

        '</g>';
    });

  svg += '</g>';

  /* -------------------------------------------------------------------
     5. FRONT OF FULL WRAP

     fullwrapfront.svg is rendered over the stems/flowers.
     Both full-wrap layers use the same geometry so the artwork aligns.
     ------------------------------------------------------------------- */

  if (wrap.full) {
    svg +=
      '<g class="wrap-layer wrap-front" ' +
      'pointer-events="none">' +

        fullWrapFrontImageNode(
          23,
          92,
          154,
          96
        ) +

      '</g>';
  }

  /* -------------------------------------------------------------------
     6. RIBBON

     Ribbon is placed directly at the stem gathering point.
     ------------------------------------------------------------------- */

  if (wrap.ribbon) {

    svg +=
      '<g class="ribbon-bow" ' +
      'pointer-events="none">' +

        ribbonImageNode(
          56,
          112,
          90,
          90
        ) +

      '</g>';
  }

  svg += '</svg>';

  canvas.innerHTML = svg;

  canvas.setAttribute(
    'data-rendered-category',
    category
  );

  canvas.classList.remove('preview-refresh');

  void canvas.offsetWidth;

  canvas.classList.add('preview-refresh');
}

window.renderVisualSVGPreview = renderVisualSVGPreview;

/* Load flexible base artwork once; rerender when it becomes available. */
loadFlexibleProductSVGAssets().then(function () {
  if (window.renderVisualSVGPreview) window.renderVisualSVGPreview();
});

/* -----------------------------------------------------------------------
   Dynamic option listeners
   --------------------------------------------------------------------- */

function initDynamicOptionListeners() {

  document.addEventListener('change', function (e) {

    if (
      e.target.matches('.customize-panel input, .customize-panel textarea') ||
      e.target.matches('.category-tab') ||
      e.target.matches('[data-color-group] input')
    ) {
      renderVisualSVGPreview();
    }
  });

  document.addEventListener('click', function (e) {

    if (e.target.closest('.qty-select button')) {

      setTimeout(
        renderVisualSVGPreview,
        0
      );
    }
  });
}


  function initPersistentBottomNav() {
    var frame = document.querySelector('.app-frame');
    if (!frame || frame.querySelector('.bottom-nav')) return;
    var current = (location.pathname.split('/').pop() || 'home.php');
    var links = [{href:'home.php',icon:'⌂',label:'Home'},{href:'order.php',icon:'✿',label:'Shop'},{href:'order-summary.php',icon:'▣',label:'Bag'},{href:'profile.php',icon:'♙',label:'Profile'}];
    var nav = document.createElement('nav'); nav.className='bottom-nav'; nav.setAttribute('aria-label','Main navigation');
    nav.innerHTML=links.map(function(x){return '<a href="'+x.href+'" class="bottom-nav__link'+(current===x.href?' is-active':'')+'"><span class="bottom-nav__icon" aria-hidden="true">'+x.icon+'</span><span>'+x.label+'</span></a>';}).join('');
    frame.appendChild(nav);
  }

  /* -----------------------------------------------------------------------
     12. Reusable login modal (injected on every page)
  --------------------------------------------------------------------- */
  var acLoginModalEl = null;
  function injectLoginModal() {
    if (acLoginModalEl) return;
    if (!document.body) return;
    var wrap = document.createElement('div');
    wrap.className = 'ac-login-modal';
    wrap.setAttribute('aria-hidden', 'true');
    wrap.innerHTML =
      '<div class="ac-login-modal__scrim" data-ac-login-close></div>' +
      '<div class="ac-login-modal__card" role="dialog" aria-modal="true" aria-labelledby="ac-login-title">' +
        '<button class="ac-login-modal__close" type="button" data-ac-login-close aria-label="Close">&times;</button>' +
        '<div class="ac-login-modal__logo" data-ac-logo></div>' +
        '<h2 id="ac-login-title" class="ac-login-modal__title">WELCOME BACK</h2>' +
        '<form class="ac-login-modal__form" data-ac-login-form novalidate>' +
          '<div class="ac-login-modal__field">' +
            '<input type="email" name="email" placeholder="Email" autocomplete="email" required>' +
          '</div>' +
          '<div class="ac-login-modal__field">' +
            '<input type="password" name="password" placeholder="Password" autocomplete="current-password" required>' +
          '</div>' +
          '<div class="ac-login-modal__row">' +
            '<a class="ac-login-modal__forgot" href="forgot-password.php">FORGOT PASSWORD?</a>' +
          '</div>' +
          '<p class="ac-login-modal__error" data-ac-login-error hidden></p>' +
          '<button type="submit" class="ac-login-modal__submit">LOGIN</button>' +
          '<a class="ac-login-modal__register" href="register.php">CREATE NEW ACCOUNT</a>' +
        '</form>' +
      '</div>';
    document.body.appendChild(wrap);
    acLoginModalEl = wrap;

    var errorEl = wrap.querySelector('[data-ac-login-error]');
    var form = wrap.querySelector('[data-ac-login-form]');

    function showError(msg) {
      if (!errorEl) return;
      errorEl.textContent = msg;
      errorEl.hidden = false;
    }
    function clearError() { if (errorEl) { errorEl.hidden = true; errorEl.textContent = ''; } }

    wrap.querySelectorAll('[data-ac-login-close]').forEach(function (el) {
      el.addEventListener('click', function (e) { e.preventDefault(); window.ACLoginModal.close(); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && wrap.classList.contains('is-open')) window.ACLoginModal.close();
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearError();
      var email = form.querySelector('[name="email"]');
      var password = form.querySelector('[name="password"]');
      if (!email.value.trim() || !/^\S+@\S+\.\S+$/.test(email.value)) { showError('Enter a valid email address.'); return; }
      if (!password.value) { showError('Enter your password.'); return; }
      var submit = form.querySelector('[type="submit"]');
      if (submit) { submit.disabled = true; submit.textContent = 'LOGGING IN...'; }
      fetch('api/auth.php?action=login', { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin', body: JSON.stringify({ email: email.value.trim(), password: password.value }) })
        .then(function (r) { return r.json().then(function (d) { if (!r.ok || !d.ok) throw new Error(d.error || 'Incorrect email or password.'); return d; }); })
        .then(function (data) {
          Store.set('ac_profile', { id: data.user.id, fullName: data.user.fullName, email: data.user.email, authenticated: true });
          window.ACLoginModal.close();
          if (window.ACToast) window.ACToast('Welcome back, ' + (data.user.fullName || '') + '!');
          setTimeout(function () { navigateWithAnim('home.php', 'anim-push-left'); }, 300);
        })
        .catch(function (err) { showError(err.message || 'Unable to log in.'); if (window.ACToast) window.ACToast(err.message || 'Unable to log in.'); })
        .finally(function () { if (submit) { submit.disabled = false; submit.textContent = 'LOGIN'; } });
    });
  }

  window.ACLoginModal = {
    open: function () {
      if (!acLoginModalEl) injectLoginModal();
      if (!acLoginModalEl) return;
      acLoginModalEl.classList.add('is-open');
      acLoginModalEl.setAttribute('aria-hidden', 'false');
      document.body.classList.add('ac-login-modal-open');
      var emailInput = acLoginModalEl.querySelector('[name="email"]');
      if (emailInput) setTimeout(function () { emailInput.focus(); }, 50);
    },
    close: function () {
      if (!acLoginModalEl) return;
      acLoginModalEl.classList.remove('is-open');
      acLoginModalEl.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('ac-login-modal-open');
    }
  };
  // Inject immediately (script loads at end of body, so document.body exists).
  injectLoginModal();

  /* -----------------------------------------------------------------------
     13. Auth gate — intercepts protected actions when logged out.
     Capture-phase so it runs before any other handler on the same element.
  --------------------------------------------------------------------- */
  function initAuthGate() {
    var protectedSelectors = [
      '[data-place-order]',
      '[data-buy-now]',
      '[data-add-to-bag]',
      '[data-carousel-buy]',
      '[data-require-auth]',
      '.nav-drawer__link[href$="profile.php"]',
      '.nav-drawer__link[href$="history.php"]'
    ];
    document.addEventListener('click', function (e) {
      var target = e.target;
      if (!target || !target.closest) return;
      for (var i = 0; i < protectedSelectors.length; i++) {
        if (target.closest(protectedSelectors[i])) {
          var p = Store.get('ac_profile', {});
          if (p && p.authenticated) return; // logged in, let action proceed
          e.preventDefault();
          e.stopImmediatePropagation();
          requireAuth(); // toasts + opens modal
          return;
        }
      }
    }, true);
  }

  /* -----------------------------------------------------------------------
     Boot
  --------------------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    applyPageAnimation();
    initLinkTransitions();
    initAuthGate();
    // Bottom navigation removed; primary routes live in the side drawer.
    injectLogos();
    initHeroCarousel();
    initCheckedClassFallback();
    initNavDrawer();
    initModals();
    initLoginForm();
    initRegisterForm();
    initCategoryTabs();
    initShopFilters();
    initRangeInputs();
    initQtySelectors();
    initFlowerQuantityControls();
    initAddonToggles();
    initAddToBag();
    initBuyNow();
    initOrderSummary();
    initSpecialRequestImagePreviews();
    initPaymentAutofill();
    initPaymentMethodUI();
    initPlaceOrder();
    initOrderSuccessId();
    initNumericInputs();
    initTermsAcceptance();
    initRegisterTermsModal();
    initOrderCancellation();
    initLogout();
    initSplashRedirect();
    initDynamicOptionListeners();
    recalcCustomizeTotal();
    renderVisualSVGPreview();

    loadWrapSVGAssets().then(function () {
      renderVisualSVGPreview();
      if (window.updateLivePreview) window.updateLivePreview();
    });

    if (window.updateLivePreview) window.updateLivePreview();
  });
})();

/* PH MOBILE NUMBER FORMATTER */
(function initPHMobileNumberFormatter() {
  function formatPHMobile(value) {
    var digits = String(value || "").replace(/\D/g, "").slice(0, 11);
    if (digits.length <= 4) return digits;
    if (digits.length <= 7) return digits.slice(0, 4) + "-" + digits.slice(4);
    return digits.slice(0, 4) + "-" + digits.slice(4, 7) + "-" + digits.slice(7, 11);
  }

  function isMobileField(el) {
    if (!el || el.tagName !== "INPUT") return false;
    var name = (el.name || "").toLowerCase();
    var id = (el.id || "").toLowerCase();
    var placeholder = (el.placeholder || "").toLowerCase();
    return el.type === "tel" ||
      name.includes("mobile") ||
      name.includes("phone") ||
      id.includes("mobile") ||
      id.includes("phone") ||
      placeholder.includes("mobile") ||
      placeholder.includes("phone");
  }

  document.addEventListener("input", function(e) {
    var el = e.target;
    if (!isMobileField(el)) return;

    var digits = String(el.value || "").replace(/\D/g, "").slice(0, 11);
    el.value = formatPHMobile(digits);
    el.maxLength = 13; // 11 digits + 2 hyphens
    el.inputMode = "numeric";
    el.setAttribute("autocomplete", "tel");
  });

  document.addEventListener("keydown", function(e) {
    var el = e.target;
    if (!isMobileField(el)) return;

    // Allow navigation/editing/clipboard shortcuts.
    if (e.ctrlKey || e.metaKey || e.altKey ||
        ["Backspace","Delete","ArrowLeft","ArrowRight","Home","End","Tab"].includes(e.key)) {
      return;
    }

    // Reject anything except digits. Formatting hyphens are inserted automatically.
    if (!/^\d$/.test(e.key)) {
      e.preventDefault();
    }
  });

  document.addEventListener("paste", function(e) {
    var el = e.target;
    if (!isMobileField(el)) return;

    e.preventDefault();
    var pasted = (e.clipboardData || window.clipboardData).getData("text");
    el.value = formatPHMobile(pasted);
    el.dispatchEvent(new Event("input", { bubbles: true }));
  });
})();

