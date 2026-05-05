<?php
include 'connectfile.php';
include 'update-usage-times.php';

header('Content-Type: application/json');
http_response_code(200);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError('Invalid request method', ['method' => $_SERVER['REQUEST_METHOD']]);
    http_response_code(405);
    echo json_encode(['status' => 'device-rejected', 'error' => 'Method Not Allowed']);
    exit;
}

// Read raw POST body
$postData = file_get_contents('php://input');

if ($postData === false || trim($postData) === '') {
    logError('Empty request body', []);
    echo json_encode(['status' => 'device-rejected', 'error' => 'Empty request']);
    exit;
}

$data = json_decode($postData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logError('Invalid JSON', ['error' => json_last_error_msg()]);
    echo json_encode(['status' => 'device-rejected', 'error' => 'Invalid JSON']);
    exit;
}

if (!isset($data['pkgver']) || !is_string($data['pkgver'])) {
    logError('Missing or invalid pkgver', $data);
    echo json_encode(['status' => 'device-rejected', 'error' => 'Missing pkgver']);
    exit;
}

$pkgver = trim($data['pkgver']);

if (!validatePkgVer($pkgver)) {
    logError('Version rejected', ['pkgver' => $pkgver]);
    echo json_encode(['status' => 'device-rejected', 'error' => 'Unsupported version']);
    exit;
}

// Version is valid → update usage and respond
$linkName = implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4));
$currentFile = basename(__FILE__, '.php');
updateUsageTimes($linkName . '/' . $currentFile);

echo json_encode(['status' => 'all-good']);
exit;

/**
 * Validate pkgver against supported range in database
 */
function validatePkgVer(string $pkgver): bool
{
    global $con;

    // Must be in format x.y.z
    if (!preg_match('/^\d+\.\d+\.\d+$/', $pkgver)) {
        return false;
    }

    try {
        $stmt = $con->prepare("SELECT supported_ver_from, supported_ver_to FROM version_config WHERE id = 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            logError('No version config found in database', []);
            return false;
        }

        $min = $result['supported_ver_from'];
        $max = $result['supported_ver_to'];

        // Allow >= min and < max
        return version_compare($pkgver, $min, '>=') && version_compare($pkgver, $max, '<');

    } catch (Exception $e) {
        logError('DB error in validatePkgVer', ['msg' => $e->getMessage()]);
        return false;
    }
}

function logError(string $message, array $context = []): void
{
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message'   => $message,
        'context'   => $context,
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'ua'        => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    file_put_contents('error_log.txt', json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}