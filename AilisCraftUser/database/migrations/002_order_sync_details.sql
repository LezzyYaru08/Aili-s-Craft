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
