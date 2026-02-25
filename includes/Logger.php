<?php
class Logger {
    private $logFile;

    public function __construct($logFile = '/home/bitrix/ext_www/cabinet.mg-ceramic.ru/logs/user_actions.log') {
        $this->logFile = $logFile;
    }

    public function logLoginAttempt($login, $success) {
        $action = $success ? 'login_success' : 'login_failed';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] $action | user: $login | ip: $ip
";

        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}