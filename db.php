<?php
// Загружаем переменные из .env
$dotenv = parse_ini_file(__DIR__ . '/.env');
if (!$dotenv) {
    die('Ошибка: файл .env не найден или повреждён');
}

try {
    $pdo = new PDO(
        'mysql:host=' . $dotenv['DB_HOST'] . ';dbname=' . $dotenv['DB_NAME'] . ';charset=utf8mb4',
        $dotenv['DB_USER'],
        $dotenv['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
