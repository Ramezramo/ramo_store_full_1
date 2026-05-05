<?php
// api/lang_config.php or similar

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once 'serveraouth/connectfile.php'; // PDO connection ($con)

// -----------------------------
// Validation Functions
// -----------------------------

/**
 * Validate and sanitize the lang_code parameter
 */
function getValidatedLangCode(): ?string
{
    $langCode = $_GET['lang_code'] ?? null;

    if (!$langCode || !is_string($langCode)) {
        sendJsonError('Missing or invalid lang_code parameter', 400);
        return null;
    }

    // Basic language code validation: 2-3 letters (e.g., en, eng, fr)
    // Allow optional region like en-US, but keep strict
    if (!preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/i', $langCode)) {
        sendJsonError('Invalid lang_code format', 400);
        return null;
    }

    // Normalize to lowercase for consistent DB lookup
    return strtolower(explode('-', $langCode)[0]); // Use only the language part
}

/**
 * Send a JSON error response and exit
 */
function sendJsonError(string $message, int $httpStatus = 400): void
{
    http_response_code($httpStatus);
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * Send JSON success response (raw stored JSON)
 */
function sendJsonSuccess(string $jsonData): void
{
    echo $jsonData;
    exit;
}

// -----------------------------
// Database Functions
// -----------------------------

/**
 * Fetch the stored JSON response for a given language code
 */
function fetchConfigByLangCode(PDO $con, string $langCode): ?string
{
    try {
        $sql = "SELECT response FROM `ustore-config` WHERE langcode = :langcode";
        $stmt = $con->prepare($sql);
        $stmt->execute([':langcode' => $langCode]);
        $data = $stmt->fetchColumn();

        return $data !== false ? $data : null;
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        sendJsonError('Server error', 500);
        return null;
    }
}

/**
 * Validate that the stored data is valid JSON
 */
function validateStoredJson(string $data): bool
{
    json_decode($data);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Invalid JSON in database for lang_code: ' . json_last_error_msg());
        sendJsonError('Configuration data corrupted', 500);
        return false;
    }
    return true;
}

// -----------------------------
// Main Logic
// -----------------------------

$validatedLangCode = getValidatedLangCode();

if ($validatedLangCode === null) {
    // Error already sent in function
    exit;
}

$configJson = fetchConfigByLangCode($con, $validatedLangCode);

if ($configJson === null) {
    sendJsonError('No configuration found for the given lang_code', 404);
}

if (!validateStoredJson($configJson)) {
    // Error already sent
    exit;
}

// All good — output the raw valid JSON
sendJsonSuccess($configJson);