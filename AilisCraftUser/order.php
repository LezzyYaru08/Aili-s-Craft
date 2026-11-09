<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
<style>
  /* Fix: Force display:none on hidden elements overriding grid styles */
  [hidden], .product-grid[hidden] {
    display: none !important;
  }
</style>
</head>

  <header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <span class="topbar__title">Shop</span>
    <a class="topbar__logo-link" href="home.php" aria-label="Go to home">
      <img src="assets/images/logo.png" alt="Aili's Crochet PH Logo" class="topbar__logo-img">
    </a>
  </header>

  <nav class="nav-drawer" data-drawer aria-hidden="true">
    <div class="nav-drawer__scrim"></div>
    <div class="nav-drawer__panel">
      <button class="nav-drawer__close" data-drawer-close aria-label="Close menu">&times;</button>
      <div class="nav-drawer__brand">
        <img src="assets/images/logo.png" alt="Aili's Crochet PH" class="drawer__logo-img">
        <span class="nav-drawer__brand-name">Aili's Crochet PH</span>
      </div>
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



  <main class="page-main" data-shop-page>
    <section class="section">
      <h1 style="font-size:22px;margin-bottom:4px;">Choose what you love!</h1>
      <p style="font-size:13px;color:var(--ink-soft);margin-bottom:20px;">Every piece is made to order.</p>

      <!-- Category Filter Tabs -->
      <div class="category-tabs" role="tablist" aria-label="Shop categories">
        <button class="category-tab active" data-shop-filter="all">All</button>
        <button class="category-tab" data-shop-filter="flowers">Flowers</button>
        <button class="category-tab" data-shop-filter="bags">Bags</button>
        <button class="category-tab" data-shop-filter="headwears">Head Wears</button>
        <button class="category-tab" data-shop-filter="keychains">Keychains</button>
      </div>

      <!-- Flowers Grid -->
      <div class="product-grid" data-shop-panel="flowers">
        
        <div class="product-card" 
             data-best-seller 
             data-id="flower_tulip_trio" 
             data-name="Tulip Trio Bouquet" 
             data-category="flowers" 
             data-price="480" 
             data-options="3x Crochet Tulips, Pink Paper Wrap">
          <div class="product-card__img"><img src="assets/images/orders/flower1.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Sunflower Trio Bouquet</span>
            <span class="product-card__price">₱480</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
             data-best-seller 
             data-id="flower_rose_bunny" 
             data-name="Rose & Bunny Bouquet" 
             data-category="flowers" 
             data-price="550" 
             data-options="Rose Flower, Mini Crochet Bunny, Kraft Wrap">
          <div class="product-card__img"><img src="assets/images/orders/flower3.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Rose & Bunny Bouquet</span>
            <span class="product-card__price">₱550</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
             data-best-seller 
             data-id="flower_sunflower" 
             data-name="Sunflower Wrap" 
             data-category="flowers" 
             data-price="200" 
             data-options="Crochet Sunflower, Eucalyptus Stems">
          <div class="product-card__img"><img src="assets/images/orders/flower2.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Sunflower Wrap</span>
            <span class="product-card__price">₱200</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
             data-best-seller 
             data-id="flower_cherry_blossom" 
             data-name="Cherry Blossom Mix" 
             data-category="flowers" 
             data-price="550" 
             data-options="Sakura Stems, White Lace Ribbon">
          <div class="product-card__img"><img src="assets/images/orders/flower4.jpg" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Cherry Blossom Mix</span>
            <span class="product-card__price">₱550</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

      </div>

      <!-- Bags Grid -->
      <div class="product-grid" data-shop-panel="bags" hidden>  
        
        <div class="product-card" 
             data-best-seller 
             data-id="bag_mini" 
             data-name="Baguette Bag" 
             data-category="bags" 
             data-price="350" 
             data-options="Mini Size, Cream Yarn, Button Strap">
          <div class="product-card__img"><img src="assets/images/orders/bag1.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Green Classic Sling Bag</span>
            <span class="product-card__price">₱350</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
             data-best-seller 
             data-id="bag_purse" 
             data-name="Coin Purse" 
             data-category="bags" 
             data-price="250" 
             data-options="Handheld Purse, Zipper Closure">
          <div class="product-card__img"><img src="assets/images/orders/bag2.jpg" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Coin Purse</span>
            <span class="product-card__price">₱250</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
            data-best-seller 
            data-id="bag_sling" 
            data-name="Beach Bag" 
            data-category="bags" 
            data-price="420" 
            data-options="Long Crossbody Strap, Flower Patch">
          <div class="product-card__img"><img src="assets/images/orders/bag3.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Beach Bag</span>
            <span class="product-card__price">₱420</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

      </div>

      <!-- Head Wears Grid -->
      <div class="product-grid" data-shop-panel="headwears" hidden>
        
        <div class="product-card" 
            data-best-seller 
            data-id="head_clips" 
            data-name="Hair Clip Set" 
            data-category="headwears" 
            data-price="75" 
            data-options="Set of 2 Clips, Pastel Colors">
          <div class="product-card__img"><img src="assets/images/orders/headwear1.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Pink Bow Barrette Band</span>
            <span class="product-card__price">₱75</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
            ata-best-seller 
            data-id="head_headband" 
            data-name="Crochet Headband" 
            data-category="headwears" 
            data-price="85" 
            data-options="Flexible Band, Floral Pattern">
          <div class="product-card__img"><img src="assets/images/orders/headwear2.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Headband</span>
            <span class="product-card__price">₱85</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
            data-best-seller 
            data-id="head_bandana" 
            data-name="Granny Square Bandana" 
            data-category="headwears" 
            data-price="95" 
            data-options="Tie Bandana, Cotton Yarn">
          <div class="product-card__img"><img src="assets/images/orders/headwear3.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Bandana</span>
            <span class="product-card__price">₱95</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

      </div>

      <!-- Keychains Grid -->
      <div class="product-grid" data-shop-panel="keychains" hidden>
        
        <div class="product-card" 
            data-best-seller 
            data-id="key_capybara" 
            data-name="Capybara Charm" 
            data-category="keychains" 
            data-price="150" 
            data-options="3-inch Capybara, Metal Ring">
          <div class="product-card__img"><img src="assets/images/orders/keychain1.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Pink Star Flower Keychain</span>
            <span class="product-card__price">₱150</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
            data-best-seller 
            data-id="key_bunny" 
            data-name="Bunny Charm" 
            data-category="keychains" 
            data-price="150" 
            data-options="Mini Bunny, Pastel Pink Ribbon">
          <div class="product-card__img"><img src="assets/images/orders/keychain2.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Bunny Charm</span>
            <span class="product-card__price">₱150</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

        <div class="product-card" 
            data-best-seller 
            data-id="key_cat" 
            data-name="Cat Charm" 
            data-category="keychains" 
            data-price="150" 
            data-options="White Kitty Charm, Bell Accessory">
          <div class="product-card__img"><img src="assets/images/orders/keychain3.png" alt="" loading="lazy"></div>
          <div class="product-card__body">
            <span class="product-card__name">Cat Charm</span>
            <span class="product-card__price">₱150</span>
            <div class="product-card__actions">
              <button class="btn-card btn-card--primary" data-buy-now>Buy Now</button>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

</div>
<script src="main.js"></script>
<script src="features.js"></script>
<script>
  // Filter panels switching logic for category tabs
  document.querySelectorAll('[data-shop-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.getAttribute('data-shop-filter');
      
      // Update active tab button style
      document.querySelectorAll('[data-shop-filter]').forEach(function (b) { 
        b.classList.toggle('active', b === btn); 
      });

      // Show matching panel and hide others explicitly
      document.querySelectorAll('[data-shop-panel]').forEach(function (panel) {
        var match = key === 'all' || panel.getAttribute('data-shop-panel') === key;
        if (match) {
          panel.removeAttribute('hidden');
          panel.style.display = 'grid';
        } else {
          panel.setAttribute('hidden', '');
          panel.style.display = 'none';
        }
      });
    });
  });
</script>
</body>
</html>