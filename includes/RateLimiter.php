<?php

class RateLimiter {
    private $pdo;
    private $limit;
    private $interval;
    private $blockDuration;

    public function __construct($pdo, $limit = 5, $interval = 300, $blockDuration = 900) {
        $this->pdo = $pdo;
        $this->limit = $limit; // макс. попыток за интервал
        $this->interval = $interval; // интервал в секундах (5 мин)
        $this->blockDuration = $blockDuration; // блокировка на 15 мин
    }

    public function isBlocked($identifier, $ip) {
        $stmt = $this->pdo->prepare("
            SELECT is_blocked, blocked_until
            FROM rate_limit_sessions
            WHERE identifier = ? AND ip_address = ?
        ");
        $stmt->execute([$identifier, $ip]);
        $row = $stmt->fetch();

        if ($row && $row['is_blocked'] && strtotime($row['blocked_until']) > time()) {
            return true;
        }
        return false;
    }

    public function logAttempt($identifier, $ip, $success) {
        $currentTime = date('Y-m-d H:i:s');

        // Обновляем или создаём сессию
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limit_sessions (identifier, login, ip_address, attempt_count, first_attempt, last_attempt, is_blocked, blocked_until)
            VALUES (?, ?, ?, 1, ?, ?, 0, NULL)
            ON DUPLICATE KEY UPDATE
                attempt_count = attempt_count + 1,
                last_attempt = ?,
                is_blocked = CASE
                    WHEN attempt_count + 1 >= ? AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(first_attempt)) < ? THEN 1
                    ELSE is_blocked
                END,
                blocked_until = CASE
                    WHEN attempt_count + 1 >= ? AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(first_attempt)) < ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                    ELSE blocked_until
                END
        ");

        $stmt->execute([
            $identifier, $identifier, $ip, $currentTime, $currentTime, $currentTime,
            $this->limit, $this->interval,
            $this->limit, $this->interval, $this->blockDuration
        ]);
    }

    public function allowLogin($login, $ip) {
        if ($this->isBlocked($login, $ip)) {
            return false;
        }

        $this->logAttempt($login, $ip, false); // логируем попытку (неудачную или успешную — не важно)
        
        // Проверяем, не заблокирован ли сейчас
        return !$this->isBlocked($login, $ip);
    }
}
