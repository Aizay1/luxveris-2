<?php
// DB credentials must match docker-compose
define('DB_HOST', 'luxveris_db');
define('DB_NAME', 'luxveris_db');
define('DB_USER', 'luxveris');
define('DB_PASS', '12?34?56?Aa');

function db() {
    static $pdo;
    if (!$pdo) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

session_start();
