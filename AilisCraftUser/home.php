<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
</head>

<header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <span class="topbar__title">Aili's Crochet PH</span>
    <a class="topbar__logo-link" href="home.php" aria-label="Go to home" data-ac-logo></a>
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
      <a class="nav-drawer__link" href="profile.php"><span aria-hidden="true">♙</span> Profile</a>
      <div class="nav-drawer__section-label">Account</div>
      <a class="nav-drawer__link" href="history.php">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        History
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

    <section class="home-hero">
      <div class="home-hero__blob"></div>
      <span class="eyebrow" style="color:#fff;">Handmade with love</span>
      <h1>Crochet bouquets that never wilt</h1>
      <p>Build your own flowers, bags, head wears, and keychains — piece by piece.</p>
      <a class="btn" href="customize.php">START CUSTOMIZING</a>
    </section>

    <!-- Featured Sliding Preview Hero Carousel -->
    <section class="hero-carousel" data-hero-carousel>
      <div class="carousel-track">
        
        <!-- Category 1: Flowers -->
        <div class="carousel-slide active" 
            data-name="Sunflower Trio Bouquet" 
            data-category="flowers" 
            data-price="480" 
            data-options="3x Crochet Tulips, Pink Wrap">
          <img class="carousel-slide__img" src="assets/images/flower1.png" alt="Sunflower Trio Bouquet" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
          <div class="carousel-slide__overlay"></div>
          <div class="carousel-slide__content">
            <div class="carousel-slide__tags">
              <span class="carousel-tag">2026</span>
              <span class="carousel-tag">Bouquet</span>
              <span class="carousel-tag">Flowers</span>
            </div>
            <h2 class="carousel-slide__title">Sunflower Trio Bouquet</h2>
            <p class="carousel-slide__price">₱480</p>
            <button class="btn btn-sm" data-carousel-buy>BUY NOW & CHECKOUT</button>
          </div>
        </div>

        <!-- Category 2: Bags -->
        <div class="carousel-slide" 
            data-name="Sling Bag Classic" 
            data-category="bags" 
            data-price="350" 
            data-options="Long Crossbody Strap, Classic Finish">
          <img class="carousel-slide__img" src="assets/images/bag1.png" alt="Sling Bag Classic" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
          <div class="carousel-slide__overlay"></div>
          <div class="carousel-slide__content">
            <div class="carousel-slide__tags">
              <span class="carousel-tag">2026</span>
              <span class="carousel-tag">Handmade</span>
              <span class="carousel-tag">Bags</span>
            </div>
            <h2 class="carousel-slide__title">Green Classic Sling Bag</h2>
            <p class="carousel-slide__price">₱350</p>
            <button class="btn btn-sm" data-carousel-buy>BUY NOW & CHECKOUT</button>
          </div>
        </div>

        <!-- Category 3: Head Wears -->
        <div class="carousel-slide" 
            data-name="Bow Barrette Band" 
            data-category="headwears" 
            data-price="220" 
            data-options="Bow Barrette, Pastel Pink">
          <img class="carousel-slide__img" src="assets/images/headwear1.png" alt="Bow Barrette Band" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
          <div class="carousel-slide__overlay"></div>
          <div class="carousel-slide__content">
            <div class="carousel-slide__tags">
              <span class="carousel-tag">2026</span>
              <span class="carousel-tag">Accessory</span>
              <span class="carousel-tag">Head Wear</span>
            </div>
            <h2 class="carousel-slide__title">Pink Bow Barrette Band</h2>
            <p class="carousel-slide__price">₱220</p>
            <button class="btn btn-sm" data-carousel-buy>BUY NOW & CHECKOUT</button>
          </div>
        </div>

        <!-- Category 4: Keychains -->
        <div class="carousel-slide" 
            data-name="Bunny Charm Keychain" 
            data-category="keychains" 
            data-price="150" 
            data-options="Mini Bunny, Metal Ring">
          <img class="carousel-slide__img" src="assets/images/keychain1.png" alt="Bunny Charm Keychain" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
          <div class="carousel-slide__overlay"></div>
          <div class="carousel-slide__content">
            <div class="carousel-slide__tags">
              <span class="carousel-tag">2026</span>
              <span class="carousel-tag">Cute</span>
              <span class="carousel-tag">Keychain</span>
            </div>
            <h2 class="carousel-slide__title">Pink Star Flower Keychain</h2>
            <p class="carousel-slide__price">₱150</p>
            <button class="btn btn-sm" data-carousel-buy>BUY NOW & CHECKOUT</button>
          </div>
        </div>

      </div>

      <!-- Carousel Navigation -->
      <div class="carousel-controls">
        <div class="carousel-dots">
          <button class="carousel-dot active" data-slide-index="0" aria-label="Slide 1"></button>
          <button class="carousel-dot" data-slide-index="1" aria-label="Slide 2"></button>
          <button class="carousel-dot" data-slide-index="2" aria-label="Slide 3"></button>
          <button class="carousel-dot" data-slide-index="3" aria-label="Slide 4"></button>
        </div>
        <div class="carousel-arrows">
          <button class="carousel-arrow" data-carousel-prev aria-label="Previous slide">&#10094;</button>
          <button class="carousel-arrow" data-carousel-next aria-label="Next slide">&#10095;</button>
        </div>
      </div>
    </section>

    <section class="section home-all-products">
      <div class="section__head">
        <h2 class="section__title">All products</h2>
        <a class="section__link" href="order.php">Browse shop</a>
      </div>
      <div class="product-grid home-all-products__grid">
        <div class="product-card" 
             
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

        <div class="product-card" 
             
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

        <div class="product-card" 
            
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

        <div class="product-card" 
            
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
</body>
</html>