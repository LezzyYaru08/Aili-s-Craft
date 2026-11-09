<?php
/**
 * Aili's Craft — database-backed session handler (ac_sessions table).
 *
 * Registered by api/config.php ONLY when AC_SESSION_DRIVER=db. The default
 * driver is 'files', so localhost/XAMPP and shared hosting are unchanged.
 *
 * Contract: NEVER throws — a DB failure degrades gracefully (read returns
 * '', write/destroy/gc silently fail). Requires migration 007_sessions.sql.
 */
declare(strict_types=1);
class AC_DBSessionHandler implements SessionHandlerInterface
{
    /** @var PDO|null lazily connected; null = unavailable */
    private $pdo = null;
    private function db()
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }
        try {
            $this->pdo = db();
        } catch (Throwable $e) {
            error_log('[AilisCraft session_db] DB unavailable: ' . $e->getMessage());
            $this->pdo = null;
        }
        return $this->pdo;
    }
    #[\ReturnTypeWillChange]
    public function open($savePath, $sessionName)
    {
        return true;
    }
    #[\ReturnTypeWillChange]
    public function close()
    {
        return true;
    }
    #[\ReturnTypeWillChange]
    public function read($id)
    {
        try {
            $pdo = $this->db();
            if (!$pdo) {
                return '';
            }
            $stmt = $pdo->prepare('SELECT data, expires_at FROM ac_sessions WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                return '';
            }
            if ((int) $row['expires_at'] < time()) {
                return ''; // expired — treat as missing
            }
            return (string) $row['data'];
        } catch (Throwable $e) {
            error_log('[AilisCraft session_db] read failed: ' . $e->getMessage());
            return '';
        }
    }
    #[\ReturnTypeWillChange]
    public function write($id, $data)
    {
        try {
            $pdo = $this->db();
            if (!$pdo) {
                return false;
            }
            $maxLifetime = (int) ini_get('session.gc_maxlifetime');
            if ($maxLifetime <= 0) {
                $maxLifetime = 1440;
            }
            $expires = time() + $maxLifetime;
            $stmt = $pdo->prepare(
                'INSERT INTO ac_sessions (id, data, created_at, updated_at, expires_at)
                 VALUES (?, ?, NOW(), NOW(), ?)
                 ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = NOW(), expires_at = VALUES(expires_at)'
            );
            $stmt->execute([$id, (string) $data, $expires]);
            return true;
        } catch (Throwable $e) {
            error_log('[AilisCraft session_db] write failed: ' . $e->getMessage());
            return false;
        }
    }
    #[\ReturnTypeWillChange]
    public function destroy($id)
    {
        try {
            $pdo = $this->db();
            if (!$pdo) {
                return false;
            }
            $stmt = $pdo->prepare('DELETE FROM ac_sessions WHERE id = ?');
            $stmt->execute([$id]);
            return true;
        } catch (Throwable $e) {
            error_log('[AilisCraft session_db] destroy failed: ' . $e->getMessage());
            return false;
        }
    }
    #[\ReturnTypeWillChange]
    public function gc($maxlifetime)
    {
        try {
            $pdo = $this->db();
            if (!$pdo) {
                return 0;
            }
            $stmt = $pdo->prepare('DELETE FROM ac_sessions WHERE expires_at < ?');
            $stmt->execute([time()]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[AilisCraft session_db] gc failed: ' . $e->getMessage());
            return 0;
        }
    }
}
