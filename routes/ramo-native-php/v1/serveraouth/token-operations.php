<?php
// connectfile.php should define $con as PDO with ERRMODE_EXCEPTION
include 'connectfile.php';
include 'update-usage-times.php';
header('Content-Type: application/json; charset=utf-8');

// Helper: Send JSON response and exit
function jsonResponse(array $data, int $httpStatus = 200): void
{
    http_response_code($httpStatus);
    echo json_encode($data);
    exit;
}

// Helper: Log error securely (no sensitive data in response)
function logError(string $message): void
{
    error_log("[TokenAPI] " . $message);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

// Update usage statistics
$linkName = implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4));
$currentFile = basename(__FILE__, '.php');
updateUsageTimes($linkName . "/" . $currentFile);

// Fetch master encryption settings
function getEncryptionSettings(): array|false
{
    global $con;
    try {
        $stmt = $con->prepare("SELECT key_in, identfier FROM koto WHERE id = 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    } catch (PDOException $e) {
        logError("Failed to fetch encryption settings: " . $e->getMessage());
        return false;
    }
}

$settings = getEncryptionSettings();
if (!$settings || empty($settings['key_in']) || empty($settings['identfier'])) {
    jsonResponse(['success' => false, 'error' => 'Server configuration error'], 500);
}

$masterKey = $settings['key_in']; // 32-byte key for AES-256
$identifier = $settings['identfier']; // Fixed prefix

// Read and validate JSON input
$rawInput = file_get_contents('php://input');
if ($rawInput === false) {
    jsonResponse(['success' => false, 'error' => 'Failed to read request body'], 400);
}
$data = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonResponse(['success' => false, 'error' => 'Invalid JSON payload'], 400);
}

// ---------------------------
// Case 1: Generate new token
// ---------------------------
if (isset($data['d_id']) && is_string($data['d_id']) && trim($data['d_id']) !== '') {
    $deviceId = trim($data['d_id']);
    $aboutDevice = $data['aboutd'] ?? null; // Optional

    if (strlen($deviceId) > 255) {
        jsonResponse(['success' => false, 'error' => 'Device ID too long'], 400);
    }

    // Check if device is blocked
    try {
        $stmt = $con->prepare("SELECT blocked FROM device_access_tokens WHERE device_id = :device_id LIMIT 1");
        $stmt->execute([':device_id' => $deviceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && (int)$row['blocked'] === 1) {
            logError("Blocked device attempted token generation: $deviceId");
            jsonResponse(['success' => false, 'error' => 'Device access denied'], 403);
        }
    } catch (PDOException $e) {
        logError("Failed to check blocked status for device $deviceId: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Server error'], 500);
    }

    $plainText = $identifier . $deviceId;
    $token = encryptData($plainText, $masterKey);
    if ($token === false) {
        logError("Encryption failed for device: $deviceId");
        jsonResponse(['success' => false, 'error' => 'Encryption failed'], 500);
    }

    // Store or update token
    if (!storeTokenInDatabase($token, $deviceId, $masterKey, $identifier, $aboutDevice)) {
        jsonResponse(['success' => false, 'error' => 'Failed to save token'], 500);
    }

    jsonResponse(['success' => true, 'token' => $token], 200);
}

// ---------------------------
// Case 2: Verify/decrypt token
// ---------------------------
elseif (isset($data['token']) && is_string($data['token']) && trim($data['token']) !== '') {
    $token = trim($data['token']);

    // Retrieve token settings including blocked status
    $tokenSettings = getTokenEncryptionSettings($token);
    if (!$tokenSettings || !isset($tokenSettings['key_pass'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid or unknown token'], 401);
    }

    // Check if device is blocked
    if (!empty($tokenSettings['blocked']) && (int)$tokenSettings['blocked'] === 1) {
        logError("Blocked device attempted token verification with token: " . substr($token, 0, 20) . "...");
        jsonResponse(['success' => false, 'error' => 'Device access revoked'], 403);
    }

    $usedKey = $tokenSettings['key_pass'];
    $decrypted = decryptData($token, $usedKey);
    if ($decrypted === false) {
        jsonResponse(['success' => false, 'error' => 'Token decryption failed (tampered or invalid)'], 401);
    }

    // Validate identifier prefix
    if (!str_starts_with($decrypted, $identifier)) {
        jsonResponse(['success' => false, 'error' => 'Token integrity check failed'], 401);
    }

    jsonResponse(['success' => true, 'decryptedData' => $decrypted], 200);
}

// ---------------------------
// Invalid request
// ---------------------------
else {
    jsonResponse(['success' => false, 'error' => 'Missing or invalid parameters. Expected "d_id" or "token".'], 400);
}

// ======================== Functions ========================
function encryptData(string $data, string $key): string|false
{
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    if ($iv === false) return false;
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if ($encrypted === false) return false;
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData(string $token, string $key): string|false
{
    $raw = base64_decode($token, true);
    if ($raw === false) return false;
    $parts = explode('::', $raw, 2);
    if (count($parts) !== 2) return false;
    [$encryptedData, $iv] = $parts;
    return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

function getTokenEncryptionSettings(string $token): array|false
{
    global $con;
    try {
        $stmt = $con->prepare("SELECT key_pass, blocked FROM device_access_tokens WHERE token = :token LIMIT 1");
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    } catch (PDOException $e) {
        logError("Token lookup failed: " . $e->getMessage());
        return false;
    }
}

function storeTokenInDatabase(string $token, string $deviceId, string $keyPass, string $identifier, $aboutDevice): bool
{
    global $con;
    try {
        // Check if exists
        $stmt = $con->prepare("SELECT 1 FROM device_access_tokens WHERE device_id = :device_id LIMIT 1");
        $stmt->execute([':device_id' => $deviceId]);
        $exists = $stmt->fetch() !== false;

        $encodedAbout = $aboutDevice !== null ? json_encode($aboutDevice) : null;

        if ($exists) {
            $sql = "UPDATE device_access_tokens
                    SET token = :token, key_pass = :key_pass, identifier = :identifier,
                        about_device = :about_device, updated_at = NOW()
                    WHERE device_id = :device_id";
        } else {
            $sql = "INSERT INTO device_access_tokens
                    (token, device_id, key_pass, identifier, about_device, created_at, updated_at)
                    VALUES (:token, :device_id, :key_pass, :identifier, :about_device, NOW(), NOW())";
        }

        $stmt = $con->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':device_id', $deviceId);
        $stmt->bindParam(':key_pass', $keyPass);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->bindValue(':about_device', $encodedAbout, $encodedAbout === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        return $stmt->execute();
    } catch (PDOException $e) {
        logError("Store token failed for device $deviceId: " . $e->getMessage());
        return false;
    }
}