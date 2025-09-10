<?php
// db.php — central PDO connection using .env config
function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $host    = getenv('DB_HOST')    ?: 'localhost';
    $name    = getenv('DB_NAME')    ?: 'evasion';
    $user    = getenv('DB_USER')    ?: 'evasion';
    $pass    = getenv('DB_PASS')    ?: 'S7BrLseYgj0EhQl';
    $port    = (int)(getenv('DB_PORT') ?: 3306);
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$name};port={$port};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared stmts
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    // Optional: align DB timezone to app/server (adjust if needed)
    // $pdo->exec("SET time_zone = '+01:00'");

    return $pdo;
}
