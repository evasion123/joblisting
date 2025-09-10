<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__); // looks for ".env" in project root
    $dotenv->safeLoad();
}
require_once __DIR__ . '/db.php';
?>
