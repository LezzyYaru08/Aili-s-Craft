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
