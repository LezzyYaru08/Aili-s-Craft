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
