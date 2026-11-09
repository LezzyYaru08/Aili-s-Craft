-- Aili's Craft product catalog + legacy order-item linking for product reviews.
-- Run once against an existing database after the base schema has been imported.
-- This intentionally reuses product_reviews; it does not create a second review table.

INSERT INTO products (product_code, category_id, name, description, price, stock_quantity, image_path, is_active)
SELECT v.product_code, c.id, v.name, v.description, v.price, 0, v.image_path, 1
FROM (
  SELECT 'flower_tulip_trio' product_code, 'flowers' category_slug, 'Sunflower Trio Bouquet' name, '3x crochet tulips with pink paper wrap.' description, 480.00 price, 'assets/images/orders/flower1.png' image_path UNION ALL
  SELECT 'flower_rose_bunny','flowers','Rose & Bunny Bouquet','Rose flower with mini crochet bunny and kraft wrap.',550.00,'assets/images/orders/flower3.png' UNION ALL
  SELECT 'flower_sunflower','flowers','Sunflower Wrap','Crochet sunflower with eucalyptus stems.',200.00,'assets/images/orders/flower2.png' UNION ALL
  SELECT 'flower_cherry_blossom','flowers','Cherry Blossom Mix','Crochet cherry blossom mix.',550.00,'assets/images/orders/flower4.jpg' UNION ALL
  SELECT 'bag_mini','bags','Green Classic Sling Bag','Mini handmade sling bag.',350.00,'assets/images/orders/bag1.png' UNION ALL
  SELECT 'bag_purse','bags','Coin Purse','Handheld crochet purse with zipper closure.',250.00,'assets/images/orders/bag2.jpg' UNION ALL
  SELECT 'bag_sling','bags','Beach Bag','Crochet beach bag with long crossbody strap.',420.00,'assets/images/orders/bag3.png' UNION ALL
  SELECT 'head_clips','headwears','Pink Bow Barrette Band','Set of two crochet clips.',75.00,'assets/images/orders/headwear1.png' UNION ALL
  SELECT 'head_headband','headwears','Headband','Flexible crochet headband.',85.00,'assets/images/orders/headwear2.png' UNION ALL
  SELECT 'head_bandana','headwears','Bandana','Tie-style crochet bandana.',95.00,'assets/images/orders/headwear3.png' UNION ALL
  SELECT 'key_capybara','keychains','Pink Star Flower Keychain','Decorative handmade keychain.',150.00,'assets/images/orders/keychain1.png' UNION ALL
  SELECT 'key_bunny','keychains','Bunny Charm','Mini bunny crochet charm.',150.00,'assets/images/orders/keychain2.png' UNION ALL
  SELECT 'key_cat','keychains','Cat Charm','Crochet kitty charm.',150.00,'assets/images/orders/keychain3.png'
) AS v
JOIN product_categories c ON c.slug = v.category_slug
ON DUPLICATE KEY UPDATE category_id=VALUES(category_id), name=VALUES(name), description=VALUES(description), price=VALUES(price), image_path=VALUES(image_path), is_active=1;

-- Link existing available-product order lines to the catalog product so the
-- existing product_reviews.order_item_id/product_id foreign keys can be used.
UPDATE order_items oi
JOIN products p ON p.product_code = JSON_UNQUOTE(JSON_EXTRACT(oi.customization_json, '$.productCode'))
SET oi.product_id = p.id
WHERE oi.product_id IS NULL AND oi.customization_json IS NOT NULL;

UPDATE order_items oi
JOIN products p ON p.name = oi.item_name
SET oi.product_id = p.id
WHERE oi.product_id IS NULL;
