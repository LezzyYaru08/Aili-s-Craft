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
