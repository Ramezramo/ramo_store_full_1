<?php
// Optional: Prevent direct access (recommended)
// defined('SECURE_ACCESS') or die('Direct access not permitted');

include 'serveraouth/connectfile.php';
include 'serveraouth/update-usage-times.php';
include 'serveraouth/token-validation.php';

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
// 2. Validate pagination parameters
// ------------------------------------------------------------------
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20))); // Max 100 items per page

$offset = ($page - 1) * $limit;

// Prevent deep pagination abuse
if ($offset > 10000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Pagination too deep. Maximum allowed offset exceeded.'
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
    error_log("Failed to update usage times for tags API: " . $e->getMessage());
}

// ------------------------------------------------------------------
// 4. Fetch tags securely
// ------------------------------------------------------------------
try {
    // Get accurate total count
    $countStmt = $con->query("SELECT COUNT(*) FROM tags");
    $totalTags = (int)$countStmt->fetchColumn();

    // Main query - adjust columns if needed (common tag fields)
    $sql = "SELECT id, name, slug 
            FROM tags 
            ORDER BY name ASC 
            LIMIT :limit OFFSET :offset";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $tags,
        'pagination' => [
            'page'     => $page,
            'per_page' => $limit,
            'total'    => $totalTags,
            'pages'    => (int)ceil($totalTags / $limit),
            'offset'   => $offset
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    // Log error internally - NEVER expose raw message
    error_log("Tags API Database Error: " . $e->getMessage() . " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to retrieve tags. Please try again later.'
    ]);
} catch (Throwable $e) {
    error_log("Unexpected error in Tags API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal server error'
    ]);
}