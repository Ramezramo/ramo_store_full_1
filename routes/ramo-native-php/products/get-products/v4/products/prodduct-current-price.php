<?php
// Optional: Prevent direct access (uncomment if desired)
// defined('SECURE_ACCESS') or die('Direct access not permitted');

include 'serveraouth/connectfile.php';

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
// 1. Get and validate product_id
// ------------------------------------------------------------------
$input = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
} else {
    // POST with JSON body
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }
    
    $id = $input['id'] ?? null;
}

// Validate as positive integer
$id = filter_var($id, FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid or missing product_id. Must be a positive integer.'
    ]);
    exit;
}

// ------------------------------------------------------------------
// 2. Optional: Re-enable authentication if needed
// ------------------------------------------------------------------
// require_once 'auth.php';
// $token = getBearerToken();
// if (!$token || !validateConsumerKey($token)) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'error' => 'Unauthorized']);
//     exit;
// }

// ------------------------------------------------------------------
// 3. Fetch the MAIN variation's price using main_variation = 1
// ------------------------------------------------------------------
try {
    $sql = "
        SELECT 
            COALESCE(NULLIF(sale_price, 0), price) AS effective_price
        FROM product_variations 
        WHERE product_id = :product_id 
          AND main_variation = true
        LIMIT 1
    ";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':product_id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $variation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($variation && $variation['effective_price'] !== null) {
        $price = (float)$variation['effective_price'];

        http_response_code(200);
        echo json_encode([
            'success'     => true,
            'product_id'  => $id,
            'sale_price'  => $price,
            'message'     => 'Main variation price retrieved successfully'
        ], JSON_NUMERIC_CHECK);
    } else {
        // No main variation found
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error'   => 'Main variation not found for this product'
        ]);
    }
} catch (PDOException $e) {
    // Log error but don't expose details
    error_log("Price API Error (Product ID: $id): " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal server error'
    ]);
} catch (Exception $e) {
    error_log("Unexpected Error in Price API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal server error'
    ]);
}