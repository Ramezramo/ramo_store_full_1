<?php
// Optional: Prevent direct access (uncomment if file is outside web root)
// defined('SECURE_ACCESS') or die('Direct access not permitted');

include 'serveraouth/connectfile.php';
include 'serveraouth/update-usage-times.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle preflight OPTIONS request (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ------------------------------------------------------------------
// 1. Extract and validate pagination parameters
// ------------------------------------------------------------------
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20))); // Max 100 per page

$offset = ($page - 1) * $limit;

// Prevent deep pagination attacks (brands table is small, but good practice)
if ($offset > 10000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Pagination too deep.'
    ]);
    exit;
}

// ------------------------------------------------------------------
// 2. Optional: Authentication (uncomment when ready)
// ------------------------------------------------------------------
// include 'serveraouth/token-validation.php';
// $consumer_key = getBearerToken();
// if (!$consumer_key || !validateConsumerKey($consumer_key)) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'error' => 'Unauthorized']);
//     exit;
// }

// ------------------------------------------------------------------
// 3. Update usage statistics
// ------------------------------------------------------------------
try {
    $pathParts = array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4);
    $linkName = implode('/', $pathParts) . '/' . basename(__FILE__, '.php');
    updateUsageTimes($linkName);
} catch (Exception $e) {
    error_log("Failed to update usage times for brands API: " . $e->getMessage());
}

// ------------------------------------------------------------------
// 4. Fetch brands (only existing columns)
// ------------------------------------------------------------------
try {
    // Only select columns that actually exist
    $sql = "SELECT id, name 
            FROM brands 
            ORDER BY name ASC 
            LIMIT :limit OFFSET :offset";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optional: Get total count for proper pagination
    $countStmt = $con->query("SELECT COUNT(*) FROM brands");
    $totalBrands = (int)$countStmt->fetchColumn();

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $brands,
        'pagination' => [
            'page'   => $page,
            'limit'  => $limit,
            'total'  => $totalBrands,
            'pages'  => ceil($totalBrands / $limit)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Brands API Database Error: " . $e->getMessage() . " | File: " . __FILE__);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to retrieve brands. Please try again later.'
    ]);
} catch (Throwable $e) {
    error_log("Unexpected Error in Brands API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal server error'
    ]);
}