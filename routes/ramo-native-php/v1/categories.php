<?php
// get-all-categories.php - FINAL VERSION (WooCommerce-style categories2 table)

include 'serveraouth/connectfile.php';
include 'serveraouth/update-usage-times.php';
header('Content-Type: application/json');

// === Input Parameters ===
$input = array_merge($_GET, $_POST, json_decode(file_get_contents('php://input'), true) ?: []);

$params = filter_var_array($input, [
    'page'        => FILTER_VALIDATE_INT,
    'per_page'    => FILTER_VALIDATE_INT,
    'exclude'     => FILTER_VALIDATE_INT,
    'hide_empty'  => FILTER_VALIDATE_INT,
    'add_base_url'=> FILTER_VALIDATE_INT,
]);

$page         = max(1, $params['page'] ?? 1);
$limit        = min(200, max(1, $params['per_page'] ?? 100));
$exclude      = $params['exclude'] > 0 ? (int)$params['exclude'] : null;
$hide_empty   = ($params['hide_empty'] ?? 0) == 1;
$add_base_url = ($params['add_base_url'] ?? 0) == 1;

// Optional: Change this to your actual domain if you want to force it
define('FORCE_BASE_URL', 'https://yourdomain.com'); // Set to false if not needed

try {
    // Track usage
    $linkName = implode('/', array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4));
    $currentFile = basename(__FILE__, '.php');
    updateUsageTimes($linkName . "/" . $currentFile);

    // === CORRECT SQL: Use existing columns only ===
    $sql = "SELECT 
                id, name, slug, parent, description, display, 
                image, menu_order, count, has_children, _links 
            FROM categories2 
            ORDER BY menu_order ASC, id ASC";

    $stmt = $con->prepare($sql);
    $stmt->execute();
    $rawCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Helper: prepend or force base URL
    $fixUrl = function ($url) use ($add_base_url) {
        if (!$add_base_url || !$url) return $url;
        if (preg_match('#^https?://#i', $url)) {
            // Already absolute — optionally replace domain
            if (defined('FORCE_BASE_URL') && FORCE_BASE_URL !== false) {
                return preg_replace('#^https?://[^/]+#i', rtrim(FORCE_BASE_URL, '/'), $url);
            }
            return $url;
        }
        return rtrim(FORCE_BASE_URL ?: '', '/') . '/' . ltrim($url, '/');
    };

    $categories = array_map(function ($cat) use ($fixUrl) {
        // Parse JSON fields
        $cat['image'] = json_decode($cat['image'] ?? '', true) ?: null;
        $cat['_links'] = json_decode($cat['_links'] ?? '', true) ?: [];

        // Fix image URL
        if (is_array($cat['image']) && isset($cat['image']['src'])) {
            $cat['image']['src'] = $fixUrl($cat['image']['src']);
        }

        // Fix all _links hrefs
        foreach ($cat['_links'] as $key => $links) {
            if (is_array($links)) {
                foreach ($links as $i => $link) {
                    if (isset($link['href'])) {
                        $cat['_links'][$key][$i]['href'] = $fixUrl($link['href']);
                    }
                }
            }
        }

        // Cast types
        $cat['id']           = (int)$cat['id'];
        $cat['parent']       = (int)$cat['parent'];
        $cat['menu_order']   = (int)$cat['menu_order'];
        $cat['count']        = (int)$cat['count'];           // This is your product count!
        $cat['has_children'] = $cat['has_children'] === '1' || $cat['has_children'] === 1;

        // Rename to standard name (optional)
        $cat['products_count'] = $cat['count'];
        // Keep 'count' for backward compatibility if needed

        return $cat;
    }, $rawCategories);

    // Filter: exclude + hide_empty
    $filtered = array_filter($categories, function($cat) use ($exclude, $hide_empty) {
        if ($exclude !== null && $cat['id'] === $exclude) return false;
        if ($hide_empty && $cat['products_count'] === 0) return false;
        return true;
    });

    $finalCategories = array_values($filtered);

    // Response
    echo json_encode([
        'success'   => true,
        'page'      => $page,
        'per_page'  => $limit,
        'total'     => count($finalCategories),
        'data'      => $finalCategories
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("get-all-categories.php error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Server error'
    ]);
}
?>