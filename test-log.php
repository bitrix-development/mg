<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$logFile = '/home/bitrix/ext_www/cabinet.mg-ceramic.ru/logs/test.log';

// Создаём папку, если её нет
mkdir(dirname($logFile), 0755, true);

// Пишем тестовую строку
$result = file_put_contents($logFile, "[TEST] " . date('Y-m-d H:i:s') . " - Лог записан
", FILE_APPEND | LOCK_EX);

if ($result !== false) {
    echo "Успех: запись добавлена в $logFile";
} else {
    echo "Ошибка: не удалось записать в файл. Проверь права на папку logs.";
}