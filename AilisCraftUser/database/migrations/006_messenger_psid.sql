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
