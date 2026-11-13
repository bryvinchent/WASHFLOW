<?php
declare(strict_types=1);
session_start();

/*
 * Ubah konfigurasi ini sesuai database hosting kamu.
 * Jangan unggah kredensial database ke repository publik.
 */
const DB_HOST = 'localhost';
const DB_NAME = 'washflow';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(string|int|float|null $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
