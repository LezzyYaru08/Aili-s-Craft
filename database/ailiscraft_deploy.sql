-- =====================================================================
-- AilisCraft — CONSOLIDATED DEPLOY SQL (additive, non-destructive)
-- Import this file in phpMyAdmin (or: mysql -h HOST -u USER -p DB < thisfile)
--
-- SAFE TO RE-RUN: schema tables use CREATE TABLE IF NOT EXISTS; migrations
-- are idempotent. There are NO DROP / TRUNCATE / DELETE statements — an
-- existing database with real order/customer data is NEVER wiped.
--
-- First deploy  : import the whole file.
-- Existing DB   : re-importing is safe (no-op for existing objects).
--
-- After import, seed the admin/owner accounts via
--   /AilisCraftAdmin/setup_accounts.php  (localhost, or with AC_ADMIN_SETUP_TOKEN)
-- then DELETE that file. See DEPLOY_SHARED_HOSTING.md step 6.
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- CREATE DATABASE IF NOT EXISTS ailiscraft_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;  -- uncomment only if you have CREATE privilege; on shared hosting the DB already exists

-- USE ailiscraft_db;  -- select your DB in phpMyAdmin before importing


-- ---------------------------------------------------------------------
-- 1. product_categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_categories (
  id    INT PRIMARY KEY AUTO_INCREMENT,
  name  VARCHAR(50) NOT NULL,   
  slug  VARCHAR(50) NOT NULL,   
  UNIQUE KEY uq_category_name (name),
  UNIQUE KEY uq_category_slug (slug)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id                BIGINT PRIMARY KEY AUTO_INCREMENT,
  full_name         VARCHAR(150) NOT NULL,
  email             VARCHAR(190) NOT NULL,
  phone             VARCHAR(20)  NULL,       
  password_hash     VARCHAR(255) NOT NULL,   
  status            ENUM('Active','Disabled') NOT NULL DEFAULT 'Active',
  terms_accepted_at DATETIME NULL,             
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. admin_accounts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_accounts (
  id             BIGINT PRIMARY KEY AUTO_INCREMENT,
  admin_code     VARCHAR(20)  NULL,             
  full_name      VARCHAR(150) NOT NULL,
  email          VARCHAR(190) NOT NULL,
  username       VARCHAR(60)  NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('Owner','Administrator','Manager') NOT NULL DEFAULT 'Administrator',
  status         ENUM('Active','Disabled') NOT NULL DEFAULT 'Active',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admin_email (email),
  UNIQUE KEY uq_admin_username (username),
  UNIQUE KEY uq_admin_code (admin_code)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. products
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id               BIGINT PRIMARY KEY AUTO_INCREMENT,
  product_code     VARCHAR(60)  NOT NULL,   
  category_id      INT NOT NULL,
  name             VARCHAR(150) NOT NULL,
  description      TEXT NULL,               
  price            DECIMAL(10,2) NOT NULL,
  stock_quantity   INT NOT NULL DEFAULT 0,
  image_path       VARCHAR(255) NULL,
  emoji_icon       VARCHAR(10)  NULL,       
  default_options  VARCHAR(500) NULL,       
  is_best_seller   TINYINT(1) NOT NULL DEFAULT 0,
  is_active        TINYINT(1) NOT NULL DEFAULT 1,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_product_code (product_code),
  KEY idx_products_category (category_id),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id)
    REFERENCES product_categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_products_price CHECK (price >= 0),
  CONSTRAINT chk_products_stock CHECK (stock_quantity >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. orders
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id                    BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_code            VARCHAR(20) NOT NULL,    
  user_id               BIGINT NOT NULL,
  status                VARCHAR(32) NOT NULL DEFAULT 'Preparing',
  payer_full_name       VARCHAR(150) NOT NULL,   
  delivery_address      VARCHAR(255) NOT NULL,
  contact_number        VARCHAR(20)  NOT NULL,
  delivery_date         DATE NOT NULL,
  payment_method        ENUM('cod_30_gcash_70_cod','gcash_full') NOT NULL
                          DEFAULT 'cod_30_gcash_70_cod',
  payment_status        VARCHAR(32) NOT NULL DEFAULT 'Payment Pending',
  down_payment_status   VARCHAR(32) NULL,
  down_payment_percent  DECIMAL(5,2)  NULL,
  down_payment_amount   DECIMAL(12,2) NULL,
  remaining_balance     DECIMAL(12,2) NULL,
  subtotal              DECIMAL(12,2) NOT NULL,
  delivery_fee          DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_amount          DECIMAL(12,2) NOT NULL,
  cancellation_reason   VARCHAR(500) NULL,
  terms_accepted        TINYINT(1) NOT NULL DEFAULT 0,
  terms_accepted_at     DATETIME NULL,
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_orders_code (order_code),
  KEY idx_orders_user (user_id),
  KEY idx_orders_status (status),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_orders_amounts CHECK (subtotal >= 0 AND total_amount >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. order_items
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id                BIGINT NOT NULL,
  product_id              BIGINT NULL,   
  category_id             INT NOT NULL,
  item_name               VARCHAR(150) NOT NULL,
  quantity                INT NOT NULL DEFAULT 1,
  unit_price              DECIMAL(10,2) NOT NULL,
  line_total              DECIMAL(12,2) NOT NULL,
  customization_summary   TEXT NULL,   
  customization_json      JSON NULL,
  snapshot_data           LONGTEXT NULL,
  image_url               VARCHAR(500) NULL,
  special_request_images  VARCHAR(1000) NULL,
  created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
    REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_order_items_category FOREIGN KEY (category_id)
    REFERENCES product_categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_order_items_qty CHECK (quantity > 0),
  CONSTRAINT chk_order_items_price CHECK (unit_price >= 0 AND line_total >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. order_payments
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_payments (
  id                     BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id               BIGINT NOT NULL,
  method                 VARCHAR(32) NOT NULL,
  status                 VARCHAR(32) NOT NULL DEFAULT 'Pending',
  down_payment_percent   DECIMAL(5,2)  NULL,
  down_payment_amount    DECIMAL(12,2) NULL,
  remaining_balance      DECIMAL(12,2) NULL,
  transaction_reference  VARCHAR(120) NULL,
  proof_path             VARCHAR(500) NULL,  
  verified_at            DATETIME NULL,
  created_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_order_payments_order (order_id),
  KEY idx_order_payments_reference (transaction_reference),
  CONSTRAINT fk_order_payments_order FOREIGN KEY (order_id)
    REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. product_reviews
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_reviews (
  id             BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_item_id  BIGINT NOT NULL,   
  product_id     BIGINT NOT NULL,
  user_id        BIGINT NOT NULL,
  rating         TINYINT NOT NULL,
  review_text    TEXT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review_per_item (order_item_id),   
  KEY idx_reviews_product (product_id),
  KEY idx_reviews_user (user_id),
  CONSTRAINT fk_reviews_order_item FOREIGN KEY (order_item_id)
    REFERENCES order_items(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. notifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id     BIGINT NOT NULL,
  order_id    BIGINT NULL,
  type        VARCHAR(40) NOT NULL DEFAULT 'order',
  title       VARCHAR(150) NULL,
  message     VARCHAR(500) NOT NULL,
  is_read     TINYINT(1) NOT NULL DEFAULT 0,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at     DATETIME NULL,
  KEY idx_notifications_user_read (user_id, is_read),
  KEY idx_notifications_created (created_at),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_notifications_order FOREIGN KEY (order_id)
    REFERENCES orders(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. user_messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_messages (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id     BIGINT NOT NULL,
  admin_id    BIGINT NULL,
  subject     VARCHAR(150) NOT NULL,
  message     TEXT NOT NULL,
  is_read     TINYINT(1) NOT NULL DEFAULT 0,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_messages_user (user_id),
  CONSTRAINT fk_user_messages_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_user_messages_admin FOREIGN KEY (admin_id)
    REFERENCES admin_accounts(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. admin_activity_log
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_activity_log (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  admin_id    BIGINT NULL,
  action      VARCHAR(100) NOT NULL,
  detail      VARCHAR(500) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_activity_admin (admin_id),
  KEY idx_activity_created (created_at),
  CONSTRAINT fk_activity_admin FOREIGN KEY (admin_id)
    REFERENCES admin_accounts(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. shop_settings
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS shop_settings (
  id                     TINYINT PRIMARY KEY DEFAULT 1,
  notifications_enabled  TINYINT(1) NOT NULL DEFAULT 1,
  auto_backup_enabled    TINYINT(1) NOT NULL DEFAULT 1,
  guest_checkout_enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_settings_single_row CHECK (id = 1)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 13. order_status_history: audit trail for customer/owner-visible status changes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_status_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  old_status VARCHAR(32) NULL,
  new_status VARCHAR(32) NOT NULL,
  changed_by VARCHAR(32) NOT NULL DEFAULT 'system',
  note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_order_status_history_order (order_id, created_at),
  CONSTRAINT fk_order_status_history_order FOREIGN KEY (order_id)
    REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- Seed data
-- =====================================================================
INSERT INTO product_categories (name, slug) VALUES
  ('Flowers',    'flowers'),
  ('Bags',       'bags'),
  ('Head Wears', 'headwears'),
  ('Keychains',  'keychains');

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



INSERT INTO shop_settings (id) VALUES (1);

INSERT INTO admin_accounts (admin_code, full_name, email, username, password_hash, role, status)
VALUES ('ADM-001', 'System Administrator', 'admin@ailiscraft.com', 'admin',
        '$2y$10$REPLACE_WITH_REAL_PASSWORD_HASH', 'Administrator', 'Active');

-- =====================================================================
-- Post-schema ALTER notes (run these on an EXISTING database only)
-- =====================================================================
-- order_items.snapshot_data (LONGTEXT) and the user_messages table already
-- exist in the CREATE TABLE IF NOT EXISTS statements above; no ALTER needed for them.
-- Special-request reference photos (comma-joined relative paths, <=1000 chars):
ALTER TABLE order_items ADD COLUMN special_request_images VARCHAR(1000) NULL AFTER image_url;
-- ===== Migrations (applied in order; each idempotent) =====

-- ----- migration 001_feature_integration.sql -----
-- Aili's Craft feature migration
-- Additive only. Apply to the existing database; do not replace existing tables.
-- Adapt foreign-key column types/names to the current Order/Product schema.

CREATE TABLE IF NOT EXISTS order_payments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  method VARCHAR(32) NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'Pending',
  down_payment_percent DECIMAL(5,2) NULL,
  down_payment_amount DECIMAL(12,2) NULL,
  remaining_balance DECIMAL(12,2) NULL,
  transaction_reference VARCHAR(120) NULL,
  proof_path VARCHAR(500) NULL,
  verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_order_payments_order (order_id),
  INDEX idx_order_payments_reference (transaction_reference)
);

CREATE TABLE IF NOT EXISTS product_reviews (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  rating TINYINT NOT NULL,
  review_text TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_order_product_review (order_id, product_id),
  INDEX idx_product_reviews_product (product_id),
  CONSTRAINT chk_product_review_rating CHECK (rating BETWEEN 1 AND 5)
);

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  order_id BIGINT NULL,
  type VARCHAR(40) NOT NULL DEFAULT 'order',
  message VARCHAR(500) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  INDEX idx_notifications_user_read (user_id, is_read),
  INDEX idx_notifications_created (created_at)
);

-- Optional columns for existing orders table.
-- Run only if the existing schema does not already contain these columns.
-- ALTER TABLE orders ADD COLUMN payment_status VARCHAR(32) NOT NULL DEFAULT 'Unpaid';
-- ALTER TABLE orders ADD COLUMN down_payment_status VARCHAR(32) NULL;
-- ALTER TABLE orders ADD COLUMN down_payment_percent DECIMAL(5,2) NULL;
-- ALTER TABLE orders ADD COLUMN down_payment_amount DECIMAL(12,2) NULL;
-- ALTER TABLE orders ADD COLUMN remaining_balance DECIMAL(12,2) NULL;
-- ALTER TABLE orders ADD COLUMN cancellation_reason VARCHAR(500) NULL;


-- ----- migration 002_order_sync_details.sql -----
-- AilisCraft additive order synchronization migration.
-- Back up the database first. This migration preserves existing rows.
ALTER TABLE order_items
  ADD COLUMN IF NOT EXISTS snapshot_data LONGTEXT NULL,
  ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) NULL;

CREATE TABLE IF NOT EXISTS order_status_history (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT NOT NULL,
  old_status VARCHAR(32) NULL,
  new_status VARCHAR(32) NOT NULL,
  changed_by VARCHAR(32) NOT NULL DEFAULT 'system',
  note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_order_status_history_order (order_id, created_at),
  CONSTRAINT fk_order_status_history_order FOREIGN KEY (order_id)
    REFERENCES orders(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;


-- ----- migration 003_password_resets.sql -----
-- =====================================================================
-- AilisCraft migration 003 — self-service password reset (forgot password)
-- Additive only. Run after 001 / 002 against `ailiscraft_db`.
-- =====================================================================

CREATE TABLE IF NOT EXISTS password_resets (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  email       VARCHAR(190) NOT NULL,
  token_hash  CHAR(64)     NOT NULL,          -- sha256(raw token); raw token is only in the emailed link
  expires_at  DATETIME     NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pr_email      (email),
  KEY idx_pr_token_hash (token_hash),
  KEY idx_pr_expires    (expires_at)
) ENGINE=InnoDB;


-- ----- migration 004_sms_log.sql -----
-- =====================================================================
-- AilisCraft migration 004 — SMS notification audit log
-- Additive only. Run after 003 against `ailiscraft_db`.
-- =====================================================================

CREATE TABLE IF NOT EXISTS sms_log (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id   BIGINT       NULL,
  phone      VARCHAR(20)  NOT NULL,
  status     VARCHAR(32)  NOT NULL,
  message    VARCHAR(500) NOT NULL,
  provider   VARCHAR(32)  NOT NULL DEFAULT 'logonly',
  sent       TINYINT(1)   NOT NULL DEFAULT 0,
  error      VARCHAR(500) NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_sms_order (order_id),
  KEY idx_sms_phone (phone),
  KEY idx_sms_created (created_at)
) ENGINE=InnoDB;


-- ----- migration 005_product_reviews_catalog.sql -----
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


-- ----- migration 006_messenger_psid.sql -----
-- =====================================================================
-- AilisCraft migration 006 — Facebook Messenger opt-in (PSID storage)
-- Additive & idempotent. Run after 005 against `ailiscraft_db`.
-- Stores the customer's Page-Scoped ID when they opt in via the
-- Send-to-Messenger button on the order success page.
-- Compatible with MySQL 5.7+, 8.x and MariaDB (uses information_schema
-- + PREPARE/EXECUTE instead of ADD COLUMN IF NOT EXISTS).
-- =====================================================================
SET @col_exists = (
    SELECT COUNT(*)
      FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'orders'
       AND COLUMN_NAME  = 'messenger_psid'
);
SET @ddl = IF(@col_exists = 0,
    'ALTER TABLE orders ADD COLUMN messenger_psid VARCHAR(50) NULL AFTER contact_number',
    'SELECT ''messenger_psid already exists — skipping'' AS note'
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ----- migration 007_sessions.sql -----
-- =====================================================================
-- AilisCraft migration 007 — Database-backed session storage (ac_sessions)
-- Additive & idempotent. Used ONLY when AC_SESSION_DRIVER=db (default is
-- 'files', so localhost/XAMPP and shared hosting are unchanged and this
-- table is optional). The handler lives at api/session_db.php.
-- expires_at is a Unix timestamp (seconds) so gc is a simple range delete.
-- =====================================================================
CREATE TABLE IF NOT EXISTS ac_sessions (
    id          VARCHAR(128)  NOT NULL,
    data        MEDIUMTEXT    NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at  INT UNSIGNED  NOT NULL,
    PRIMARY KEY (id),
    KEY idx_ac_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


SET FOREIGN_KEY_CHECKS=1;
-- END OF DEPLOY SQL --
