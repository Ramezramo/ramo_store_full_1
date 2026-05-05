<?php
// api/app_config.php or similar

// Configuration: Define DEBUG_MODE (set to true in development, false in production)
define('DEBUG_MODE', false); // Change to true for development to show detailed errors
// Enforce GET method only
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error'   => 'Method not allowed. Only GET requests are supported.'
    ]);
    exit;
}
// Secure response headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once 'serveraouth/connectfile.php'; // PDO connection ($con)

// Ensure PDO is configured securely (recommended in connectfile.php)
// $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// -----------------------------
// Response Functions
// -----------------------------

/**
 * Send a JSON success response and exit
 */
function sendJsonSuccess(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a JSON error response and exit
 */
function sendJsonError(string $message, int $httpStatus = 500, ?string $details = null): void
{
    http_response_code($httpStatus);
    $response = [
        'success' => false,
        'error'   => $message,
    ];
    if ($details && DEBUG_MODE) {
        $response['details'] = $details; // Only show details if DEBUG_MODE is true
    }
    echo json_encode($response);
    exit;
}

// -----------------------------
// Database Functions
// -----------------------------

/**
 * Fetch the latest app configuration from the database
 */
function fetchLatestConfig(PDO $con): ?string
{
    try {
        $sql = "SELECT config_json FROM app_config ORDER BY id DESC LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['config_json'] : null;
    } catch (PDOException $e) {
        error_log('Database error in fetchLatestConfig: ' . $e->getMessage());
        sendJsonError('Failed to retrieve configuration', 500);
        return null;
    }
}

/**
 * Validate and decode stored JSON config
 */
function decodeConfigJson(?string $jsonString): array
{
    if ($jsonString === null || trim($jsonString) === '') {
        return []; // Return empty config if none exists
    }

    $decoded = json_decode($jsonString, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Invalid JSON in app_config table: ' . json_last_error_msg());
        sendJsonError('Configuration data is corrupted', 500);
        return [];
    }

    // Optional: Ensure it's an array (not object or scalar)
    if (!is_array($decoded)) {
        error_log('Config JSON decoded to non-array type');
        sendJsonError('Invalid configuration format', 500);
        return [];
    }

    return $decoded;
}

// -----------------------------
// Main Logic
// -----------------------------

try {
    $rawConfigJson = fetchLatestConfig($con);

    $configData = decodeConfigJson($rawConfigJson);

    sendJsonSuccess($configData);

} catch (Exception $e) {
    // Fallback for any unexpected errors
    error_log('Unexpected error in app config endpoint: ' . $e->getMessage());
    sendJsonError('Internal server error', 500);
}