<?php
// api/timeline_config.php

// Enforce GET method only
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    header('Allow: GET');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error'   => 'Method not allowed. Only GET requests are supported.'
    ]);
    exit;
}

// Configuration
define('DEBUG_MODE', false); // Set to true only in development

// Secure headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once 'serveraouth/connectfile.php'; // PDO connection ($con)

// -----------------------------
// Helper Functions
// -----------------------------

/**
 * Send JSON success response (array data)
 */
function sendJsonSuccess(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send JSON error response
 */
function sendJsonError(string $message, int $status = 500, ?string $details = null): void
{
    http_response_code($status);
    $response = [
        'success' => false,
        'error'   => $message
    ];
    if ($details && DEBUG_MODE) {
        $response['details'] = $details;
    }
    echo json_encode($response);
    exit;
}

/**
 * Validate and sanitize language code
 */
function getValidatedLangCode(): string
{
    $langCode = $_GET['lang_code'] ?? 'en';

    // Strict validation: 2-3 lowercase letters (ISO 639-1/2), optional region
    if (!preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/i', $langCode)) {
        sendJsonError('Invalid lang_code format', 400);
    }

    // Normalize: use only the language part in lowercase
    return strtolower(explode('-', $langCode)[0]);
}

/**
 * Fetch timeline config for a given language code
 */
function fetchTimelineConfig(PDO $con, string $langCode): ?string
{
    try {
        $sql = "SELECT config_json FROM time_line_configs WHERE lang_code = :lang_code";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':lang_code', $langCode, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['config_json'] : null;
    } catch (PDOException $e) {
        error_log('DB Error (fetchTimelineConfig): ' . $e->getMessage());
        sendJsonError('Failed to retrieve configuration', 500);
        return null;
    }
}

/**
 * Decode and validate stored JSON config
 */
function decodeAndValidateConfig(?string $jsonString): array
{
    if ($jsonString === null || trim($jsonString) === '') {
        return []; // No config for this language – return empty array (safe fallback)
    }

    $decoded = json_decode($jsonString, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Invalid JSON in time_line_configs for lang_code: ' . json_last_error_msg());
        sendJsonError('Configuration data is corrupted', 500);
    }

    if (!is_array($decoded)) {
        error_log('Config JSON did not decode to an array');
        sendJsonError('Invalid configuration format', 500);
    }

    return $decoded;
}

// -----------------------------
// Main Logic
// -----------------------------

try {
    $langCode = getValidatedLangCode();

    $rawConfig = fetchTimelineConfig($con, $langCode);

    $configData = decodeAndValidateConfig($rawConfig);

    sendJsonSuccess($configData);

} catch (Exception $e) {
    error_log('Unexpected error in timeline config endpoint: ' . $e->getMessage());
    sendJsonError('Internal server error', 500);
}