<?php
// connectfile.php
// Located at: public/ramo-native-php/products/connectfile.php

// -----------------------------------------------------
// 1. Load .env from the project root (5 levels up)
// -----------------------------------------------------
$root = realpath(__DIR__ . '/../../../../../../../');  // Goes up 7 folders to api-ramo-store-lara
$envPath = $root . '/.env';

if (!file_exists($envPath)) {
    die('Error: .env file not found at ' . $envPath);
}

// Load .env using vlucas/phpdotenv
require_once $root . '/vendor/autoload.php'; // Composer autoloader (if you have Laravel, it's already there)

$dotenv = Dotenv\Dotenv::createImmutable($root);
$dotenv->load();

// -----------------------------------------------------
// 2. Read database credentials from .env
// -----------------------------------------------------
$host     = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost';
$dbname   = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? 'heliumdb';
$username = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'postgres';
$password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';
$port     = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '5432';

// -----------------------------------------------------
// 3. Create PDO connection (PostgreSQL)
// -----------------------------------------------------
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
// die($dsn);
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $con = new PDO($dsn, $username, $password, $options);
    // Connection successful → $con is ready to use
} catch (PDOException $e) {
    // Never show real error in production
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Optional: Make $con globally available
// Or just use it directly in your files after require_once this file

?>