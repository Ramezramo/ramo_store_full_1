<?php
// Database connection parameters
include '../products/connectfile.php';
header('Content-Type: application/json');

$lang_Code = isset($_GET["lang_code"]) ? $_GET["lang_code"] : "en";

try {
    // Get the latest configuration
    $configSql = "SELECT config_json FROM time_line_configs WHERE lang_code = :lang_Code";
    $configStmt = $con->prepare($configSql);

    // Bind parameter before executing
    $configStmt->bindParam(":lang_Code", $lang_Code, PDO::PARAM_STR);
    $configStmt->execute();

    $config = $configStmt->fetch(PDO::FETCH_ASSOC);
    
    // Decode config_json if it exists
    $configData = $config && isset($config['config_json']) ? json_decode($config['config_json'], true) : [];

    echo json_encode($configData);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred.',
        'message' => $e->getMessage(),
        'success' => false
    ]);
}
