<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customize · Aili's Crochet PH</title>
<link rel="stylesheet" href="style.css">
<style>
  /* Logo Positioning Fixes */
  .topbar__logo-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
  }
  .topbar__logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
  .drawer__logo-img {
    width: 36px;
    height: 36px;
    object-fit: contain;
    margin-right: 10px;
  }

  /* Live Preview Component Styles */
  .live-preview-card {
    background: #ffffff;
    border: 2px stroke #f6c9de;
    border-radius: 16px;
    padding: 16px;
    margin: 16px auto 8px auto; max-width: 720px; width: calc(100% - 32px);
    box-shadow: 0 4px 15px rgba(201, 79, 134, 0.08);
    border: 1px solid #f6c9de;
  }
  .live-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    border-bottom: 1px dashed #f0d5e2;
    padding-bottom: 8px;
  }
  .live-preview-badge {
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #c94f86;
    background: #fff0f5;
    padding: 3px 8px;
    border-radius: 12px;
  }
  .live-preview-cat {
    font-size: 13px;
    font-weight: 600;
    color: #2b2230;
  }
  .live-preview-stage {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .page-main {
    padding-bottom: 100px !important; /* Forces enough scroll space above the footer */
  }
  .preview-color-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    background-color: #ffb6d9;
    flex-shrink: 0;
    transition: background-color 0.25s ease;
  }
  .preview-main-icon {
    font-size: 32px;
    line-height: 1;
  }
  .preview-details {
    flex: 1;
    overflow: hidden;
  }
  .preview-title {
    font-size: 14px;
    font-weight: bold;
    color: #2b2230;
    margin-bottom: 4px;
  }
  .preview-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    max-height: 52px;
    overflow-y: auto;
  }
  .preview-tag {
    font-size: 11px;
    background: #f7f3f6;
    color: #554852;
    padding: 2px 7px;
    border-radius: 6px;
    border: 1px solid #eae2e7;
  }
  .preview-letter-box {
    margin-top: 10px;
    padding: 8px 12px;
    background: #fff8fa;
    border-left: 3px solid #eb6e9b;
    border-radius: 4px;
    font-size: 12px;
    color: #554852;
    font-style: italic;
  }

body { padding-bottom: 110px; }
  
  /* Fixed Centered Sticky CTA Footer */
  .sticky-cta { 
    position: fixed; 
    left: 50%; 
    transform: translateX(-50%); top: auto;
    bottom: 0; 
    width: min(100%, var(--content-max, 100%)); 
    z-index: 1000; 
    box-sizing: border-box; 
  }
</style>
</head>

  <header class="topbar">
    <button class="topbar__menu-btn" data-drawer-open aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <span class="topbar__title">Customize</span>
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

  <main class="page-main" style="padding-bottom:12px;">

    <!-- ===================== LIVE PREVIEW ===================== -->
    <div class="live-preview-card" id="live-preview-box">
      <div class="live-preview-header">
        <span class="live-preview-badge">✨ Live Visual Concept Preview</span>
      </div>
      <div class="live-preview-body">
        <div class="live-preview-stage">
          
          <!-- Interactive Visual Layered SVG Stage -->
          <div class="visual-canvas" id="visual-canvas">
            <div class="visual-layer visual-foliage" id="visual-foliage-layer"></div>
            <div class="visual-layer visual-flowers" id="visual-flowers-layer"></div>
            <div class="visual-layer visual-wrap" id="visual-wrap-layer"></div>
          </div>

          <div class="preview-color-circle" id="preview-color-circle" title="Selected Main Color"></div>
          <div class="preview-main-icon" id="preview-main-icon" style="display:none;">💐</div>
          
          <div class="preview-details">
            <div class="preview-title" id="preview-title-text">Custom Item</div>
            <div class="preview-tags" id="preview-tags-container"></div>
          </div>
        </div>
        <div class="preview-letter-box" id="preview-letter-box" style="display:none;">
          <strong>Letter:</strong> "<span id="preview-letter-text"></span>"
        </div>
      </div>
    </div>

    <div class="category-tabs" role="tablist" aria-label="Customize categories">
      <button class="category-tab active" data-category-tab="flowers">💐 Flowers</button>
      <button class="category-tab" data-category-tab="bags">👜 Bags</button>
      <button class="category-tab" data-category-tab="headwears">🎀 Head Wears</button>
      <button class="category-tab" data-category-tab="keychains">🔑 Keychains</button>
    </div>

    <!-- ===================== FLOWERS ===================== -->
    <section class="customize-panel active" data-panel="flowers">
      <img data-product-image="1" src="assets/images/flower1.png" style="display:none" alt="">

      <div class="option-group">
        <div class="option-group__title">Choose your flowers</div>
        <div class="option-group__hint">Set each flower quantity individually. Maximum 24 flowers per bouquet.</div>
        <div class="flower-quantity-grid">
          <div class="flower-qty-card"><label for="fl-rose">Rose</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-rose" data-dir="down" aria-label="Remove one rose">−</button><input id="qty-rose" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="1" data-flower-quantity="fl-rose" aria-label="Rose quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-rose" data-dir="up" aria-label="Add one rose">+</button></div><input type="checkbox" id="fl-rose" class="flower-type-toggle" checked aria-label="Include rose"></div>
          <div class="flower-qty-card"><label for="fl-tulip">Tulip</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-tulip" data-dir="down" aria-label="Remove one tulip">−</button><input id="qty-tulip" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-tulip" aria-label="Tulip quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-tulip" data-dir="up" aria-label="Add one tulip">+</button></div><input type="checkbox" id="fl-tulip" class="flower-type-toggle"  aria-label="Include tulip"></div>
          <div class="flower-qty-card"><label for="fl-sunflower">Sunflower</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-sunflower" data-dir="down" aria-label="Remove one sunflower">−</button><input id="qty-sunflower" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-sunflower" aria-label="Sunflower quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-sunflower" data-dir="up" aria-label="Add one sunflower">+</button></div><input type="checkbox" id="fl-sunflower" class="flower-type-toggle"  aria-label="Include sunflower"></div>
          <div class="flower-qty-card"><label for="fl-daisy">Daisy</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-daisy" data-dir="down" aria-label="Remove one daisy">−</button><input id="qty-daisy" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-daisy" aria-label="Daisy quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-daisy" data-dir="up" aria-label="Add one daisy">+</button></div><input type="checkbox" id="fl-daisy" class="flower-type-toggle"  aria-label="Include daisy"></div>
          <div class="flower-qty-card"><label for="fl-peony">Peony</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-peony" data-dir="down" aria-label="Remove one peony">−</button><input id="qty-peony" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-peony" aria-label="Peony quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-peony" data-dir="up" aria-label="Add one peony">+</button></div><input type="checkbox" id="fl-peony" class="flower-type-toggle"  aria-label="Include peony"></div>
          <div class="flower-qty-card"><label for="fl-lavender">Lavender</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-lavender" data-dir="down" aria-label="Remove one lavender">−</button><input id="qty-lavender" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-lavender" aria-label="Lavender quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-lavender" data-dir="up" aria-label="Add one lavender">+</button></div><input type="checkbox" id="fl-lavender" class="flower-type-toggle"  aria-label="Include lavender"></div>
          <div class="flower-qty-card"><label for="fl-hydrangea">Hydrangea</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-hydrangea" data-dir="down" aria-label="Remove one hydrangea">−</button><input id="qty-hydrangea" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-hydrangea" aria-label="Hydrangea quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-hydrangea" data-dir="up" aria-label="Add one hydrangea">+</button></div><input type="checkbox" id="fl-hydrangea" class="flower-type-toggle"  aria-label="Include hydrangea"></div>
          <div class="flower-qty-card"><label for="fl-orchid">Orchid</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-orchid" data-dir="down" aria-label="Remove one orchid">−</button><input id="qty-orchid" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-orchid" aria-label="Orchid quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-orchid" data-dir="up" aria-label="Add one orchid">+</button></div><input type="checkbox" id="fl-orchid" class="flower-type-toggle"  aria-label="Include orchid"></div>
          <div class="flower-qty-card"><label for="fl-carnation">Carnation</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-carnation" data-dir="down" aria-label="Remove one carnation">−</button><input id="qty-carnation" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-carnation" aria-label="Carnation quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-carnation" data-dir="up" aria-label="Add one carnation">+</button></div><input type="checkbox" id="fl-carnation" class="flower-type-toggle"  aria-label="Include carnation"></div>
          <div class="flower-qty-card"><label for="fl-lily">Lily</label><div class="flower-qty-control"><button type="button" class="flower-qty-btn" data-qty-for="fl-lily" data-dir="down" aria-label="Remove one lily">−</button><input id="qty-lily" class="flower-quantity" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="2" value="0" data-flower-quantity="fl-lily" aria-label="Lily quantity"><button type="button" class="flower-qty-btn" data-qty-for="fl-lily" data-dir="up" aria-label="Add one lily">+</button></div><input type="checkbox" id="fl-lily" class="flower-type-toggle"  aria-label="Include lily"></div>
        </div>
        <div class="flower-total-line">Total flowers: <strong id="flower-total-count">1 / 24</strong></div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Foliage</div>
        <div class="option-group__hint">Choose up to 3 kinds of greenery</div>
        <div class="chip-grid">
          <label class="chip" for="fo-eucalyptus"><input type="checkbox" id="fo-eucalyptus" checked>Olive</label>
          <label class="chip" for="fo-fern"><input type="checkbox" id="fo-fern">Fern</label>
          <label class="chip" for="fo-babysbreath"><input type="checkbox" id="fo-babysbreath">Baby's Breath</label>
        </div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Wrapping</div>
        <div class="option-group__hint">Choose your wrap style, wrapper color, and ribbon color</div>
        <div class="chip-grid" style="margin-bottom:14px;">
          <label class="chip" for="wrap-ribbon"><input type="radio" name="wrapping" id="wrap-ribbon" value="ribbon" checked>Ribbon</label>
          <label class="chip" for="wrap-full"><input type="radio" name="wrapping" id="wrap-full" value="full">Full Wrap</label>
        </div>

        <div class="wrap-color-field" id="flower-wrap-color-field">
          <div class="option-group__hint wrap-color-field__label">Wrapper Color</div>
          <div class="color-grid" aria-label="Wrapper color" data-color-group="flower-wrap"></div>
        </div>

        <div class="wrap-color-field" id="flower-ribbon-color-field">
          <div class="option-group__hint wrap-color-field__label">Ribbon Color</div>
          <div class="color-grid" aria-label="Ribbon color" data-color-group="flower-ribbon"></div>
        </div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Add-ons</div>
        <div class="letter-addon-options" role="radiogroup" aria-label="Choose one letter add-on">
          <label class="letter-addon-card" for="addon-no-letter">
            <input type="radio" name="letterAddon" id="addon-no-letter" value="none" checked>
            <span class="letter-addon-card__radio" aria-hidden="true"></span>
            <span class="letter-addon-card__content"><span class="addon-row__label">No letter</span><span class="addon-row__price">Free · No add-on</span></span>
          </label>
          <label class="letter-addon-card" for="addon-love-letter">
            <input type="radio" name="letterAddon" id="addon-love-letter" value="love" data-price="30">
            <span class="letter-addon-card__radio" aria-hidden="true"></span>
            <span class="letter-addon-card__content"><span class="addon-row__label">Love letter</span><span class="addon-row__price">+ ₱30 · Up to 200 characters</span></span>
          </label>
          <label class="letter-addon-card" for="addon-birthday-letter">
            <input type="radio" name="letterAddon" id="addon-birthday-letter" value="birthday" data-price="30">
            <span class="letter-addon-card__radio" aria-hidden="true"></span>
            <span class="letter-addon-card__content"><span class="addon-row__label">Birthday letter</span><span class="addon-row__price">+ ₱30 · Up to 200 characters</span></span>
          </label>
        </div>
        <div id="letter-fields" class="letter-fields" style="display:none;">
          <label for="letter-text-input" class="option-group__hint" style="display:block;margin-top:12px;">Your message <span id="letter-character-count">0/200</span></label>
          <textarea class="letter-input" id="letter-text-input" placeholder="Write your letter message here..." maxlength="200" aria-label="Letter message"></textarea>
        </div>
        <label for="special-request-flowers" class="option-group__hint" style="display:block;margin-top:12px;">Special request</label>
        <textarea class="letter-input js-special-request" id="special-request-flowers" placeholder="Any special requests? (e.g. color shades, theme, occasion)" maxlength="500" aria-label="Special request"></textarea>
        <label class="option-group__hint" style="display:block;margin-top:12px;">Reference photos (optional, up to 2MB each)</label>
        <input type="file" accept="image/*" multiple class="js-special-request-images" style="font-size:12px;color:var(--ink-soft);width:100%;">
        <div class="js-special-request-preview ac-upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
      </div>
    </section>

    <!-- ===================== BAGS ===================== -->
    <section class="customize-panel" data-panel="bags">
      <img data-product-image="1" src="assets/images/bag1.png" style="display:none" alt="">
      <div class="option-group">
        <div class="option-group__title">Bag type</div>
        <div class="chip-grid">
          <label class="chip" for="bag-baguette"><input type="radio" name="bagType" id="bag-baguette" checked>Baguette Bag</label>
          <label class="chip" for="bag-purse"><input type="radio" name="bagType" id="bag-purse">Purse</label>
          <label class="chip" for="bag-sling"><input type="radio" name="bagType" id="bag-sling">Sling</label>
        </div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Color</div>
        <div class="option-group__hint">Choose your main crochet color</div>
        <div class="color-grid" data-color-group="bag-color"></div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Decorations</div>
        <div class="chip-grid">
          <label class="chip" for="bag-deco-flower"><input type="checkbox" id="bag-deco-flower" data-price="30" checked>Sunflower</label>
          <label class="chip" for="bag-deco-zipper"><input type="checkbox" id="bag-deco-zipper" data-price="20">Zipper Holder</label>
        </div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Special request</div>
        <label for="special-request-bags" class="option-group__hint">Anything specific you'd like? (optional)</label>
        <textarea class="letter-input js-special-request" id="special-request-bags" placeholder="Any special requests? (e.g. size, strap length, color shades)" maxlength="500" aria-label="Special request"></textarea>
        <label class="option-group__hint" style="display:block;margin-top:12px;">Reference photos (optional, up to 2MB each)</label>
        <input type="file" accept="image/*" multiple class="js-special-request-images" style="font-size:12px;color:var(--ink-soft);width:100%;">
        <div class="js-special-request-preview ac-upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
      </div>
    </section>

    <!-- ===================== HEAD WEARS ===================== -->
    <section class="customize-panel" data-panel="headwears">
      <img data-product-image="1" src="assets/images/headwear1.png" style="display:none" alt="">
      <div class="option-group">
        <div class="option-group__title">Head wear type</div>
        <div class="chip-grid">
          <label class="chip" for="hw-hairclip"><input type="radio" name="headwearType" id="hw-hairclip" checked>Hair Clip</label>
          <label class="chip" for="hw-headband"><input type="radio" name="headwearType" id="hw-headband">Headband</label>
          <label class="chip" for="hw-bandana"><input type="radio" name="headwearType" id="hw-bandana">Bandana</label>
        </div>
      </div>

      <div class="option-group" id="headwear-color-group">
        <div class="option-group__title">Color</div>
        <div class="color-grid" data-color-group="headwear-color"></div>
      </div>

      <div class="option-group" id="headwear-deco-group">
        <div class="option-group__title">Decorations</div>
        <div class="chip-grid">
          <label class="chip" for="hw-deco-bow"><input type="radio" name="headwearDecoration" id="hw-deco-bow" data-price="10" checked>Bow Barrette</label>
          <label class="chip" for="hw-deco-flower"><input type="radio" name="headwearDecoration" id="hw-deco-flower" data-price="10">Sunflower</label>
          <label class="chip" for="hw-deco-bunny"><input type="radio" name="headwearDecoration" id="hw-deco-bunny" data-price="15">Bunny Ear</label>
        </div>
      </div>

      <!-- Independent Decoration Color Pickers -->
      <div class="option-group headwear-decoration-color" id="hw-bow-color-group">
        <div class="option-group__title">Bow Barrette Color</div>
        <div class="color-grid" aria-label="Bow Barrette color" data-color-group="headwear-bow-color"></div>
      </div>

      <div class="option-group headwear-decoration-color" id="hw-bunny-color-group" style="display:none;">
        <div class="option-group__title">Bunny Ear Color</div>
        <div class="color-grid" aria-label="Bunny Ear color" data-color-group="headwear-bunny-color"></div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Special request</div>
        <label for="special-request-headwears" class="option-group__hint">Anything specific you'd like? (optional)</label>
        <textarea class="letter-input js-special-request" id="special-request-headwears" placeholder="Any special requests? (e.g. size, color shades, matching set)" maxlength="500" aria-label="Special request"></textarea>
        <label class="option-group__hint" style="display:block;margin-top:12px;">Reference photos (optional, up to 2MB each)</label>
        <input type="file" accept="image/*" multiple class="js-special-request-images" style="font-size:12px;color:var(--ink-soft);width:100%;">
        <div class="js-special-request-preview ac-upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
      </div>
    </section>

    <!-- ===================== KEYCHAINS ===================== -->
    <section class="customize-panel" data-panel="keychains">
      <img data-product-image="1" src="assets/images/keychain1.png" style="display:none" alt="">
      <div class="option-group">
        <div class="option-group__title">Charm type</div>
        <div class="chip-grid" style="margin-bottom:14px;">
          <label class="chip" for="kc-type-animal"><input type="radio" name="keychainType" id="kc-type-animal" checked data-charm-toggle="animal">Animal</label>
          <label class="chip" for="kc-type-flower"><input type="radio" name="keychainType" id="kc-type-flower" data-charm-toggle="flower">Flower</label>
        </div>

        <div id="charm-animal">
          <div class="option-group__hint">Choose an animal</div>
          <div class="chip-grid">
            <label class="chip" for="kc-capybara"><input type="radio" name="animalCharm" id="kc-capybara" checked>Capybara</label>
            <label class="chip" for="kc-bunny"><input type="radio" name="animalCharm" id="kc-bunny">Bunny</label>
            <label class="chip" for="kc-cat"><input type="radio" name="animalCharm" id="kc-cat">Cat</label>
          </div>
        </div>

        <div id="charm-flower" style="display:none;">
          <div class="option-group__hint">Choose a flower charm</div>
          <div class="chip-grid">
            <label class="chip" for="kc-fl-rose"><input type="radio" name="flowerCharm" id="kc-fl-rose" checked>Rose</label>
            <label class="chip" for="kc-fl-tulip"><input type="radio" name="flowerCharm" id="kc-fl-tulip">Tulip</label>
            <label class="chip" for="kc-fl-sunflower"><input type="radio" name="flowerCharm" id="kc-fl-sunflower">Sunflower</label>
            <label class="chip" for="kc-fl-daisy"><input type="radio" name="flowerCharm" id="kc-fl-daisy">Daisy</label>
            <label class="chip" for="kc-fl-peony"><input type="radio" name="flowerCharm" id="kc-fl-peony">Peony</label>
            <label class="chip" for="kc-fl-lavender"><input type="radio" name="flowerCharm" id="kc-fl-lavender">Lavender</label>
            <label class="chip" for="kc-fl-hydrangea"><input type="radio" name="flowerCharm" id="kc-fl-hydrangea">Hydrangea</label>
            <label class="chip" for="kc-fl-orchid"><input type="radio" name="flowerCharm" id="kc-fl-orchid">Orchid</label>
            <label class="chip" for="kc-fl-carnation"><input type="radio" name="flowerCharm" id="kc-fl-carnation">Carnation</label>
            <label class="chip" for="kc-fl-lily"><input type="radio" name="flowerCharm" id="kc-fl-lily">Lily</label>
          </div>
        </div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Chain length</div>
        <div class="range-row">
          <label for="chain-length">Chain length</label>
          <input type="range" min="1" max="5" step="1" value="3" data-suffix="&quot;" id="chain-length">
          <span class="range-row__value">3&quot;</span>
        </div>
      </div>

      <div class="option-group">
        <div class="option-group__title">Special request</div>
        <label for="special-request-keychains" class="option-group__hint">Anything specific you'd like? (optional)</label>
        <textarea class="letter-input js-special-request" id="special-request-keychains" placeholder="Any special requests? (e.g. initials, color shades, charm combo)" maxlength="500" aria-label="Special request"></textarea>
        <label class="option-group__hint" style="display:block;margin-top:12px;">Reference photos (optional, up to 2MB each)</label>
        <input type="file" accept="image/*" multiple class="js-special-request-images" style="font-size:12px;color:var(--ink-soft);width:100%;">
        <div class="js-special-request-preview ac-upload-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
      </div>
    </section>

      <div class="sticky-cta">
    <div class="sticky-cta__total">
      <span class="sticky-cta__total-label">Estimated price</span>
      <span class="sticky-cta__total-value" data-customize-total>₱480</span>
    </div>
    <button class="btn" data-add-to-bag>ADD TO BAG</button>
  </div>

  </main>


</div>
<script src="assets/js/html2canvas.min.js"></script>
<script src="main.js?v=flower-qty-fix-2"></script>
<script src="features.js"></script>
<script>
  // Populate color swatches
  var PALETTE = [
    ['#ffb6d9','Pink'], ['#e2495c','Red'], ['#ffffff','White'], ['#2b2230','Black'],
    ['#c9a8e0','Lavender'], ['#a8d4f0','Sky Blue'], ['#a8e6c1','Mint Green'],
    ['#ffe17a','Yellow'], ['#ffc9a8','Peach'], ['#e8dcc8','Beige']
  ];  
  document.querySelectorAll('[data-color-group]').forEach(function (grid) {
    var name = grid.getAttribute('data-color-group');
    PALETTE.forEach(function (c, i) {
      var id = name + '-' + i;
      var label = document.createElement('label');
      label.className = 'color-swatch';
      label.style.backgroundColor = c[0];
      label.title = c[1];
      label.innerHTML = '<input type="radio" name="' + name + '" id="' + id + '"' + (i === 0 ? ' checked' : '') + '>';
      grid.appendChild(label);
    });
  });

  // Letter add-on: one radio group guarantees at most one choice, with an optional None state.
  var letterToggle = document.getElementById('addon-love-letter');
  var birthdayToggle = document.getElementById('addon-birthday-letter');
  var noLetterToggle = document.getElementById('addon-no-letter');
  var letterFields = document.getElementById('letter-fields');
  var letterInput = document.getElementById('letter-text-input');
  var letterCharacterCount = document.getElementById('letter-character-count');
  function syncLetterFields() {
    var active = !!((letterToggle && letterToggle.checked) || (birthdayToggle && birthdayToggle.checked));
    if (letterFields) letterFields.style.display = active ? 'block' : 'none';
    document.querySelectorAll('.letter-addon-card').forEach(function(card) {
      var radio = card.querySelector('input[type="radio"]');
      card.classList.toggle('is-selected', !!(radio && radio.checked));
    });
    if (letterInput) letterInput.required = active;
    if (window.updateLivePreview) window.updateLivePreview();
  }
  [letterToggle, birthdayToggle, noLetterToggle].forEach(function(radio) {
    if (radio) radio.addEventListener('change', syncLetterFields);
  });
  if (letterInput) {
    letterInput.addEventListener('input', function () {
      if (letterCharacterCount) letterCharacterCount.textContent = letterInput.value.length + '/200';
      if (window.updateLivePreview) window.updateLivePreview();
    });
  }
  syncLetterFields();

  // Animal vs Flower charm toggle
  document.querySelectorAll('[data-charm-toggle]').forEach(function (radio) {
    radio.addEventListener('change', function () {
      var which = radio.getAttribute('data-charm-toggle');
      document.getElementById('charm-animal').style.display = which === 'animal' ? 'block' : 'none';
      document.getElementById('charm-flower').style.display = which === 'flower' ? 'block' : 'none';
      if (window.updateLivePreview) window.updateLivePreview();
    });
  });

  // Headwear type & decoration availability logic
  function syncHeadwearDecorationAvailability() {
    var selectedType = document.querySelector('input[name="headwearType"]:checked');
    var isHairclip = selectedType && selectedType.id === 'hw-hairclip';
    var isBandana = selectedType && selectedType.id === 'hw-bandana';

    var bowLabel = document.querySelector('label[for="hw-deco-bow"]');
    var bow = document.getElementById('hw-deco-bow');
    var bunnyLabel = document.querySelector('label[for="hw-deco-bunny"]');
    var bunny = document.getElementById('hw-deco-bunny');
    var flower = document.getElementById('hw-deco-flower');

    if (isBandana) {
      // Bandana: Hide Bow Barrette & Bunny Ear options
      if (bowLabel) bowLabel.style.display = 'none';
      if (bunnyLabel) bunnyLabel.style.display = 'none';
      if (bow) bow.disabled = true;
      if (bunny) bunny.disabled = true;
      // Force flower option if available
      if (flower) flower.checked = true;
    } else if (isHairclip) {
      // Hairclip: Hide Bunny Ear option only
      if (bowLabel) bowLabel.style.display = '';
      if (bunnyLabel) bunnyLabel.style.display = 'none';
      if (bow) bow.disabled = false;
      if (bunny) bunny.disabled = true;
      if (bunny && bunny.checked && bow) bow.checked = true;
    } else {
      // Headband: Display all decoration options
      if (bowLabel) bowLabel.style.display = '';
      if (bunnyLabel) bunnyLabel.style.display = '';
      if (bow) bow.disabled = false;
      if (bunny) bunny.disabled = false;
    }
  }

  function syncHeadwearColorUI() {
    var selected = document.querySelector('input[name="headwearType"]:checked');
    var group = document.getElementById('headwear-color-group');
    var isHairclip = selected && selected.id === 'hw-hairclip';
    // Hide base headwear color picker when Hairclip is selected
    if (group) group.style.display = isHairclip ? 'none' : '';
  }

  document.querySelectorAll('input[name="headwearType"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      syncHeadwearDecorationAvailability();
      syncHeadwearColorUI();
      syncHeadwearDecorationColorUI();
      if (window.updateLivePreview) window.updateLivePreview();
    });
  });

  // Decoration color pickers UI state handling
  function syncHeadwearDecorationColorUI() {
    var selectedType = document.querySelector('input[name="headwearType"]:checked');
    var isBandana = selectedType && selectedType.id === 'hw-bandana';
    var selectedDeco = document.querySelector('input[name="headwearDecoration"]:checked');

    var bowGroup = document.getElementById('hw-bow-color-group');
    var bunnyGroup = document.getElementById('hw-bunny-color-group');

    if (isBandana) {
      if (bowGroup) bowGroup.style.display = 'none';
      if (bunnyGroup) bunnyGroup.style.display = 'none';
      return;
    }

    var isBow = selectedDeco && selectedDeco.id === 'hw-deco-bow' && !selectedDeco.disabled;
    var isBunny = selectedDeco && selectedDeco.id === 'hw-deco-bunny' && !selectedDeco.disabled;

    if (bowGroup) bowGroup.style.display = isBow ? '' : 'none';
    if (bunnyGroup) bunnyGroup.style.display = isBunny ? '' : 'none';
  }

  document.querySelectorAll('input[name="headwearDecoration"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      syncHeadwearDecorationColorUI();
      if (window.updateLivePreview) window.updateLivePreview();
    });
  });

  function syncFlowerWrapUI() {
    var full = !!document.getElementById('wrap-full').checked;
    document.getElementById('flower-wrap-color-field').style.display = full ? '' : 'none';
    document.getElementById('flower-ribbon-color-field').style.display = full ? 'none' : '';
  }
  document.querySelectorAll('input[name="wrapping"]').forEach(function(r){r.addEventListener('change',syncFlowerWrapUI);});
  syncFlowerWrapUI();
  var specialRequestInputs = document.querySelectorAll('.js-special-request');
  specialRequestInputs.forEach(function (ta) {
    ta.addEventListener('input', function(){ if(window.updateLivePreview) window.updateLivePreview(); });
  });


  // Trigger initial UI setup
  syncHeadwearDecorationAvailability();
  syncHeadwearColorUI();
  syncHeadwearDecorationColorUI();

  // Listen for changes in decoration colors to refresh preview
  document.querySelectorAll('[data-color-group^="headwear-"]').forEach(function(grid) {
    grid.addEventListener('change', function() {
      if (window.updateLivePreview) window.updateLivePreview();
    });
  });

  // Individual flower quantity controls (total limit: 24).
  (function initIndividualFlowerQuantities() {
    var panel = document.querySelector('.customize-panel[data-panel="flowers"]');
    if (!panel) return;
    var inputs = Array.from(panel.querySelectorAll('[data-flower-quantity]'));
    var totalEl = document.getElementById('flower-total-count');
    function totalQty() { return inputs.reduce(function(sum, input) { return sum + (parseInt(input.value || '0', 10) || 0); }, 0); }
    function sync(changed) {
      var total = totalQty();
      if (total > 24 && changed) {
        changed.value = String(Math.max(0, (parseInt(changed.value || '0', 10) || 0) - (total - 24)));
        total = totalQty();
      }
      inputs.forEach(function(input) {
        var id = input.getAttribute('data-flower-quantity');
        var toggle = document.getElementById(id);
        if (toggle) { toggle.checked = (parseInt(input.value || '0', 10) || 0) > 0; toggle.closest('.flower-qty-card').classList.toggle('is-selected', toggle.checked); }
      });
      if (totalEl) totalEl.textContent = total + ' / 24';
      recalcCustomizeTotal();
      if (window.updateLivePreview) window.updateLivePreview();
    }
    inputs.forEach(function(input) { input.addEventListener('input', function() {
      var value = Math.max(0, Math.min(24, parseInt(input.value || '0', 10) || 0));
      input.value = value;
      sync(input);
    }); });
    sync(null);
  })();

  // Live Preview Logic
  window.updateLivePreview = function() {
    var activeTab = document.querySelector('[data-category-tab].active');
    var cat = activeTab ? activeTab.getAttribute('data-category-tab') : 'flowers';
    var panel = document.querySelector('.customize-panel[data-panel="' + cat + '"]');

    var catInfo = {
      flowers: { icon: '💐', name: 'Flower Bouquet' },
      bags: { icon: '👜', name: 'Crochet Bag' },
      headwears: { icon: '🎀', name: 'Head Wear' },
      keychains: { icon: '🔑', name: 'Keychain' }
    }[cat] || { icon: '✨', name: 'Custom Item' };

    document.getElementById('preview-category-name').textContent = catInfo.name;
    document.getElementById('preview-main-icon').textContent = catInfo.icon;
    document.getElementById('preview-title-text').textContent = 'Custom ' + catInfo.name;

    // Determine target active color for status display
    var activeColor = '#ffb6d9';
    if (panel) {
      var activeGroup = null;
      if (cat === 'flowers') {
        var wrappingChoice = panel.querySelector('input[name="wrapping"]:checked');
        activeGroup = (wrappingChoice && wrappingChoice.id === 'wrap-full')
          ? 'flower-wrap'
          : 'flower-ribbon';
      } else if (cat === 'bags') {
        activeGroup = 'bag-color';
      } else if (cat === 'headwears') {
        var hwType = panel.querySelector('input[name="headwearType"]:checked');
        if (hwType && hwType.id === 'hw-hairclip') {
          activeGroup = 'headwear-bow-color';
        } else {
          activeGroup = 'headwear-color';
        }
      } else if (cat === 'keychains') {
        activeGroup = 'keychain-color';
      }

      if (activeGroup) {
        var selectedColorSwatch = panel.querySelector('[data-color-group="' + activeGroup + '"] .color-swatch input:checked');
        if (selectedColorSwatch) {
          var label = selectedColorSwatch.closest('.color-swatch');
          if (label && label.style.backgroundColor) activeColor = label.style.backgroundColor;
        }
      }
    }
    document.getElementById('preview-color-circle').style.backgroundColor = activeColor;

    // Update Tags Container
    var tagsContainer = document.getElementById('preview-tags-container');
    tagsContainer.innerHTML = '';
    if (panel) {
      var selectedOptions = panel.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked');
      selectedOptions.forEach(function(opt) {
        if (opt.closest('.color-swatch')) return; // skip color swatches in tags
        if (opt.disabled || opt.closest('label').style.display === 'none') return;
        var parentLabel = opt.closest('label');
        var text = parentLabel ? parentLabel.textContent.trim() : opt.value;
        if (text) {
          var tag = document.createElement('span');
          tag.className = 'preview-tag';
          tag.textContent = text;
          tagsContainer.appendChild(tag);
        }
      });
      // Append chain length tag if keychains tab active
      if (cat === 'keychains') {
        var len = document.getElementById('chain-length');
        if (len) {
          var tag = document.createElement('span');
          tag.className = 'preview-tag';
          tag.textContent = 'Chain: ' + len.value + '"';
          tagsContainer.appendChild(tag);
        }
      }
    }

    // Letter Note
    var letterBox = document.getElementById('preview-letter-box');
    var letterText = document.getElementById('preview-letter-text');
    var isLetterActive = (letterToggle && letterToggle.checked) || (birthdayToggle && birthdayToggle.checked);
    if (isLetterActive && letterInput && letterInput.value.trim() !== '') {
      letterText.textContent = letterInput.value.trim();
      letterBox.style.display = 'block';
    } else {
      letterBox.style.display = 'none';
    }

    // Call dynamic visual SVG rendering
    if (window.renderVisualSVGPreview) {
      window.renderVisualSVGPreview();
    }
  };

  // Deep-link support
  (function () {
    var params = new URLSearchParams(window.location.search);
    var cat = params.get('category');
    if (cat) {
      var tab = document.querySelector('[data-category-tab="' + cat + '"]');
      if (tab) tab.click();
    }
    window.updateLivePreview();

    
  })();
</script>
</body>
</html>