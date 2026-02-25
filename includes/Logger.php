<?php

class Logger
{
    private string $logFile;

    public function __construct(string $logFile = '/home/bitrix/ext_www/cabinet.mg-ceramic.ru/logs/user_actions.log')
    {
        $this->logFile = $logFile;
    }

    /**
     * Debug/file log (оставляем до конца разработки).
     */
    private function logToFile(string $action, string $description = '', ?int $userId = null): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');

        $userPart = $userId !== null ? "user_id: {$userId}" : "user_id: NULL";
        $descPart = $description !== '' ? " | {$description}" : '';

        $logLine = "[{$timestamp}] {$action} | {$userPart} | ip: {$ip}{$descPart}\n";

        // best-effort: не ломаем приложение, если файл недоступен
        @file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Основной лог: запись в таблицу user_actions.
     * Дополнительно дублируем в файл (debug).
     */
    public function logAction(PDO $pdo, ?int $userId, string $action, string $description = ''): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO user_actions (user_id, action, description, ip_address, created_at)
                VALUES (:user_id, :action, :description, :ip_address, NOW())
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':description' => $description,
                ':ip_address' => $ip,
            ]);
        } catch (Throwable $e) {
            // Если БД-логирование упало, не блокируем бизнес-операцию.
            $this->logToFile('logger_db_error', $e->getMessage(), $userId);
        }

        // debug-лог всегда стараемся писать
        $this->logToFile($action, $description, $userId);
    }

    /**
     * Backward compatible: старый метод для логов входа.
     * Теперь пишет в файл как debug, а если передан PDO — и в БД.
     */
    public function logLoginAttempt(string $login, bool $success, ?PDO $pdo = null, ?int $userId = null): void
    {
        $action = $success ? 'login_success' : 'login_failed';
        $description = "Login attempt: {$login}";

        if ($pdo instanceof PDO) {
            $this->logAction($pdo, $userId, $action, $description);
            return;
        }

        $this->logToFile($action, $description, $userId);
    }
}