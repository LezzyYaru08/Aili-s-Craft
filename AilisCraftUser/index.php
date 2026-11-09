<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aili's Craft — Handmade Crochet from the Philippines</title>
<meta name="description" content="Aili's Craft — handmade crochet bouquets, bags, headwear, and keychains from the Philippines. Stitched with love, made just for you.">
<link rel="stylesheet" href="style.css">
</head>
<body class="landing-page">
<div class="app-frame">

  <!-- ===== Landing Header ===== -->
  <header class="landing-header">
    <a class="landing-header__logo" href="index.php" data-ac-logo aria-label="Aili's Craft home"></a>
    <nav class="landing-header__nav">
      <a href="home.php">Shop</a>
      <a href="customize.php">Customize</a>
      <a href="#" data-open-login>Log in</a>
    </nav>
  </header>

  <main class="landing-main">

    <!-- ===== Hero Section ===== -->
    <section class="landing-hero">
      <div class="landing-hero__blob"></div>
      <span class="landing-hero__eyebrow">Handmade in Calamba City</span>
      <h1 class="landing-hero__title">Stitched with love,<br>made just for you.</h1>
      <p class="landing-hero__desc">
        Crochet and a Fuzzy Wire crafter!
        Keychains, Bouquets, Wearable and Bags!
      </p>
      <div class="landing-hero__cta">
        <button class="btn btn--landing" type="button" data-shop-now>SHOP NOW</button>
        <a class="btn btn-secondary btn--landing-ghost" href="customize.php">CUSTOMIZE YOUR OWN</a>
      </div>
    </section>

    <!-- ===== Best Sellers Carousel (reuses home.php markup + initHeroCarousel) ===== -->
    <section class="landing-bestsellers">
      <div class="section__head">
        <h2 class="section__title">Best sellers</h2>
        <span class="section__hint">Tap any item to add it to your bag</span>
      </div>

      <section class="hero-carousel" data-hero-carousel>
        <div class="carousel-track">

          <!-- Category 1: Flowers -->
          <div class="carousel-slide active"
              data-name="Sunflower Trio Bouquet"
              data-category="flowers"
              data-price="480"
              data-options="3x Crochet Tulips, Pink Wrap"
              data-landing-buy>
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
              <button class="btn btn-sm" type="button">ADD TO BAG</button>
            </div>
          </div>

          <!-- Category 2: Bags -->
          <div class="carousel-slide"
              data-name="Green Classic Sling Bag"
              data-category="bags"
              data-price="350"
              data-options="Long Crossbody Strap, Classic Finish"
              data-landing-buy>
            <img class="carousel-slide__img" src="assets/images/bag1.png" alt="Green Classic Sling Bag" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
            <div class="carousel-slide__overlay"></div>
            <div class="carousel-slide__content">
              <div class="carousel-slide__tags">
                <span class="carousel-tag">2026</span>
                <span class="carousel-tag">Handmade</span>
                <span class="carousel-tag">Bags</span>
              </div>
              <h2 class="carousel-slide__title">Green Classic Sling Bag</h2>
              <p class="carousel-slide__price">₱350</p>
              <button class="btn btn-sm" type="button">ADD TO BAG</button>
            </div>
          </div>

          <!-- Category 3: Head Wears -->
          <div class="carousel-slide"
              data-name="Pink Bow Barrette Band"
              data-category="headwears"
              data-price="220"
              data-options="Bow Barrette, Pastel Pink"
              data-landing-buy>
            <img class="carousel-slide__img" src="assets/images/headwear1.png" alt="Pink Bow Barrette Band" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
            <div class="carousel-slide__overlay"></div>
            <div class="carousel-slide__content">
              <div class="carousel-slide__tags">
                <span class="carousel-tag">2026</span>
                <span class="carousel-tag">Accessory</span>
                <span class="carousel-tag">Head Wear</span>
              </div>
              <h2 class="carousel-slide__title">Pink Bow Barrette Band</h2>
              <p class="carousel-slide__price">₱220</p>
              <button class="btn btn-sm" type="button">ADD TO BAG</button>
            </div>
          </div>

          <!-- Category 4: Keychains -->
          <div class="carousel-slide"
              data-name="Pink Star Flower Keychain"
              data-category="keychains"
              data-price="150"
              data-options="Mini Bunny, Metal Ring"
              data-landing-buy>
            <img class="carousel-slide__img" src="assets/images/keychain1.png" alt="Pink Star Flower Keychain" onerror="this.onerror=null;this.parentElement.classList.add('no-img');">
            <div class="carousel-slide__overlay"></div>
            <div class="carousel-slide__content">
              <div class="carousel-slide__tags">
                <span class="carousel-tag">2026</span>
                <span class="carousel-tag">Cute</span>
                <span class="carousel-tag">Keychain</span>
              </div>
              <h2 class="carousel-slide__title">Pink Star Flower Keychain</h2>
              <p class="carousel-slide__price">₱150</p>
              <button class="btn btn-sm" type="button">ADD TO BAG</button>
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
    </section>

    <!-- ===== Footer / About Us ===== -->
    <footer class="landing-footer">
      <div class="landing-footer__about">
        <div data-ac-logo class="landing-footer__logo"></div>
        <h3>About Aili's Craft</h3>
        <p>
          Aili's Craft is a homegrown handmade crochet shop based in the Calamba City, Laguna.
          What started as a passion for yarn and hooks has grown into a small studio
          creating everlasting bouquets, everyday bags, hair accessories, and
          keepsake keychains.
        </p>
        <p>
          Every item is crocheted by hand with soft, durable yarn — made with love,
          patience, and a little bit of magic. We happily accept custom orders so your
          piece can be exactly as you imagine it.
        </p>
      </div>
      <div class="landing-footer__social">
        <h4>Connect with us</h4>
        <ul>
          <li><a href="https://facebook.com/ailiscraft" target="_blank" rel="noopener noreferrer">Facebook</a></li>
          <li><a href="https://instagram.com/ailiscraft" target="_blank" rel="noopener noreferrer">Instagram</a></li>
          <li><a href="https://tiktok.com/@ailiscraft" target="_blank" rel="noopener noreferrer">TikTok</a></li>
          <li><a href="mailto:hello@ailiscraft.com">Email</a></li>
        </ul>
      </div>
      <div class="landing-footer__bottom">
        <span>&copy; <?php echo date('Y'); ?> Aili's Craft. Handmade in the Philippines with love.</span>
      </div>
    </footer>

  </main>
</div>

<script src="main.js"></script>
<script src="features.js"></script>
<script>
(function () {
  var Store = window.ACStore;

  // --- SHOP NOW: open login modal (or go home if already logged in) ---
  function openLoginOrHome() {
    var p = Store ? Store.get('ac_profile', {}) : {};
    if (p && p.authenticated) {
      window.location.href = 'home.php';
      return;
    }
    if (window.ACLoginModal) window.ACLoginModal.open();
    else window.location.href = 'login.php';
  }
  var shopNow = document.querySelector('[data-shop-now]');
  if (shopNow) shopNow.addEventListener('click', openLoginOrHome);
  var loginLink = document.querySelector('[data-open-login]');
  if (loginLink) loginLink.addEventListener('click', function (e) { e.preventDefault(); openLoginOrHome(); });

  // --- Best-seller slide click: push to cart, go straight to order-summary (no login required) ---
  document.querySelectorAll('[data-landing-buy]').forEach(function (slide) {
    slide.style.cursor = 'pointer';
    slide.addEventListener('click', function (e) {
      // Ignore clicks on carousel controls (they live outside the slide anyway, but be safe).
      if (e.target.closest('.carousel-controls') || e.target.closest('[data-carousel-prev]') || e.target.closest('[data-carousel-next]') || e.target.closest('[data-slide-index]')) return;
      var name = slide.getAttribute('data-name') || 'Handmade item';
      var price = parseFloat(slide.getAttribute('data-price') || '0');
      var category = slide.getAttribute('data-category') || 'flowers';
      var rawOptions = slide.getAttribute('data-options') || '';
      var optionsList = rawOptions ? rawOptions.split(',').map(function (s) { return s.trim(); }) : [];
      var imgEl = slide.querySelector('img');
      var imageUrl = imgEl ? imgEl.getAttribute('src') : '';
      var cart = Store ? Store.get('ac_cart', []) : [];
      cart.push({
        id: 'item_' + Date.now(),
        category: category,
        name: name,
        options: optionsList,
        price: price,
        quantity: 1,
        imageUrl: imageUrl
      });
      if (Store) Store.set('ac_cart', cart);
      if (window.ACToast) window.ACToast('Added to your bag');
      setTimeout(function () { window.location.href = 'order-summary.php'; }, 500);
    });
  });
})();
</script>
</body>
</html>
