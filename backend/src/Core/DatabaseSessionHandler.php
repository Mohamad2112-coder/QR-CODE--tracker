<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?string $lockName = null;
    private string $driver;

    public function __construct(private PDO $pdo)
    {
        $this->driver = (string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $activityIndex = $this->driver === 'mysql'
            ? ', INDEX idx_qr_pager_sessions_activity (last_activity)'
            : '';
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS qr_pager_sessions (
                session_id VARCHAR(128) NOT NULL PRIMARY KEY,
                payload TEXT NOT NULL,
                last_activity BIGINT NOT NULL' . $activityIndex . '
            )'
        );
        if ($this->driver !== 'mysql') {
            $this->pdo->exec(
                'CREATE INDEX IF NOT EXISTS idx_qr_pager_sessions_activity
                 ON qr_pager_sessions (last_activity)'
            );
        }
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        if ($this->lockName !== null && $this->driver === 'mysql') {
            $statement = $this->pdo->prepare('SELECT RELEASE_LOCK(?)');
            $statement->execute([$this->lockName]);
            $this->lockName = null;
        }

        return true;
    }

    public function read(string $id): string|false
    {
        if ($this->driver === 'mysql') {
            $lockName = 'qrpager_sess_' . substr(hash('sha256', $id), 0, 48);
            if ($this->lockName !== $lockName) {
                $this->close();
                $statement = $this->pdo->prepare('SELECT GET_LOCK(?, 10)');
                $statement->execute([$lockName]);
                if ((int)$statement->fetchColumn() !== 1) {
                    throw new \RuntimeException('Unable to acquire the database session lock.');
                }
                $this->lockName = $lockName;
            }
        }

        $statement = $this->pdo->prepare(
            'SELECT payload FROM qr_pager_sessions WHERE session_id = ?'
        );
        $statement->execute([$id]);
        $payload = $statement->fetchColumn();

        return $payload === false ? '' : (string)$payload;
    }

    public function write(string $id, string $data): bool
    {
        if ($this->driver === 'mysql') {
            $sql = 'INSERT INTO qr_pager_sessions (session_id, payload, last_activity)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        payload = VALUES(payload),
                        last_activity = VALUES(last_activity)';
        } else {
            $sql = 'INSERT INTO qr_pager_sessions (session_id, payload, last_activity)
                    VALUES (?, ?, ?)
                    ON CONFLICT(session_id) DO UPDATE SET
                        payload = excluded.payload,
                        last_activity = excluded.last_activity';
        }

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([$id, $data, time()]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM qr_pager_sessions WHERE session_id = ?'
        );
        return $statement->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM qr_pager_sessions WHERE last_activity < ?'
        );
        if (!$statement->execute([time() - $max_lifetime])) {
            return false;
        }

        return $statement->rowCount();
    }
}
