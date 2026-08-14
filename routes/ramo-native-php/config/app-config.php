<?php
// Database connection parameters
include '../products/connectfile.php';
header('Content-Type: application/json');

try {
    // Get the latest configuration
    $configSql = "SELECT config_json FROM app_config ORDER BY id DESC LIMIT 1";
    $configStmt = $con->prepare($configSql);
    $configStmt->execute();
    $config = $configStmt->fetch(PDO::FETCH_ASSOC);
    
    // Decode config_json if it exists
    $configData = $config && isset($config['config_json']) ? json_decode($config['config_json'], true) : [];

    echo json_encode($configData);

} catch (PDOException $e) {
    error_log('Native app-config database error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred.',
        'success' => false,
    ]);
}