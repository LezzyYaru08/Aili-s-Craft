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
