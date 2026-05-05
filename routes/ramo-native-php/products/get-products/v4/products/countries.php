<?php
// Optional: Prevent direct access
// defined('SECURE_ACCESS') or die('Direct access not permitted');

include 'serveraouth/connectfile.php';
include 'serveraouth/update-usage-times.php';
// include 'serveraouth/token-validation.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// // ------------------------------------------------------------------
// // 1. Authentication
// // ------------------------------------------------------------------
// $consumer_key = getBearerToken();

// if ($consumer_key === null) {
//     http_response_code(400);
//     echo json_encode(['success' => false, 'error' => 'Missing authentication token']);
//     exit;
// }

// if (!validateConsumerKey($consumer_key)) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'error' => 'Invalid or unauthorized token']);
//     exit;
// }

// ------------------------------------------------------------------
// 2. Validate and sanitize pagination parameters
// ------------------------------------------------------------------
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20))); // Max 100 per page

$offset = ($page - 1) * $limit;

// Prevent excessively deep pagination
if ($offset > 10000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Pagination too deep. Maximum offset exceeded.'
    ]);
    exit;
}

// ------------------------------------------------------------------
// 3. Update usage statistics
// ------------------------------------------------------------------
try {
    $pathParts = array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4);
    $linkName = implode('/', $pathParts) . '/' . basename(__FILE__, '.php');
    updateUsageTimes($linkName);
} catch (Exception $e) {
    error_log("Failed to update usage times for countries API: " . $e->getMessage());
}

// ------------------------------------------------------------------
// 4. Fetch countries securely
// ------------------------------------------------------------------
try {
    // Get total count for proper pagination
    $countStmt = $con->query("SELECT COUNT(*) FROM countries");
    $totalCountries = (int)$countStmt->fetchColumn();

    // Main query
    $sql = "SELECT code, name 
            FROM countries 
            ORDER BY name ASC 
            LIMIT :limit OFFSET :offset";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $countries,
        'pagination' => [
            'page'     => $page,
            'per_page' => $limit,
            'total'    => $totalCountries,
            'pages'    => (int)ceil($totalCountries / $limit),
            'offset'   => $offset
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Log error internally — NEVER expose raw message
    error_log("Countries API Error: " . $e->getMessage() . " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to retrieve countries. Please try again later.'
    ]);
} catch (Throwable $e) {
    error_log("Unexpected error in Countries API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal server error'
    ]);
}