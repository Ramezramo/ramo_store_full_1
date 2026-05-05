<?php
include 'serveraouth/connectfile.php';
include 'serveraouth/update-usage-times.php';
include 'constants/product_arrays.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

// ------------------------------------------------------------------
// INPUT PARSING
// ------------------------------------------------------------------
$rawInput = file_get_contents('php://input');
$input    = ($rawInput && $rawInput !== 'null' && $rawInput !== 'false')
    ? json_decode($rawInput, true)
    : [];

if (!is_array($input)) $input = [];

$page     = max(1, (int)($_GET['page']     ?? $input['page']     ?? 1));
$limit    = min(100, max(1, (int)($_GET['limit']    ?? $input['limit']    ?? 10)));
$search   = trim((string)($_GET['search']   ?? $input['search']   ?? ''));
$category = $_GET['category'] ?? $input['category'] ?? null;

// NEW: Single product ID request
$productId = null;
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $productId = (int)$_GET['id'];
} elseif (isset($input['id']) && $input['id'] !== '') {
    $productId = (int)$input['id'];
}
if ($productId <= 0) {
    $productId = null;
}

$langInput = $_GET['lang'] ?? $input['lang'] ?? 'en';
$lang = (is_string($langInput) && preg_match('/^[a-z]{2}$/', $langInput)) ? $langInput : 'en';

$offset = ($page - 1) * $limit;
if ($offset > 50000 && $productId === null) {  // Only enforce deep pagination limit for list mode
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Pagination too deep']);
    exit;
}

$jsonFields = $product_array_fields;
$hideFields = $product_hide_fields;
$imageBase  = IMAGE_BASE_URL;

// ------------------------------------------------------------------
// HELPER: Safe JSON decode
// ------------------------------------------------------------------
function decodeJsonFields(&$product, array $fields): void
{
    foreach ($fields as $field) {
        if (isset($product[$field]) && is_string($product[$field])) {
            $decoded = json_decode($product[$field], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $product[$field] = $decoded;
            } elseif ($product[$field] === 'null') {
                $product[$field] = [];
            }
        }
    }
}

try {
    $products = [];
    $totalProducts = 0;

    // ------------------------------------------------------------------
    // CASE 1: Single product by ID
    // ------------------------------------------------------------------
    if ($productId !== null) {
        $sql = "SELECT p.* FROM products_data p WHERE p.id = ? LIMIT 1";
        $stmt = $con->prepare($sql);
        $stmt->bindValue(1, $productId, PDO::PARAM_INT);
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit;
        }

        $totalProducts = 1;
        $page = 1;
        $limit = 1;
    }
    // ------------------------------------------------------------------
    // CASE 2: Paginated list (original behavior)
    // ------------------------------------------------------------------
    else {
        // Count total products
        $countSql = "SELECT COUNT(*) AS total FROM products_data p";
        $countWhere = [];
        $countParams = [];

        if ($search !== '') {
            $terms = array_filter(
                array_map('trim', preg_split('/\s+/u', $search)),
                fn($t) => mb_strlen($t, 'UTF-8') >= 2
            );
            if ($terms) {
                $likes = [];
                foreach ($terms as $term) {
                    $likes[] = "p.search_text LIKE ?";
                    $countParams[] = "%$term%";
                }
                $countWhere[] = '(' . implode(' OR ', $likes) . ')';
            }
        }

        if ($category !== null && (is_numeric($category) || is_string($category))) {
            $countWhere[] = "EXISTS (
                SELECT 1 FROM product_category pc 
                WHERE pc.product_id = p.id AND pc.category_id = ?
            )";
            $countParams[] = (int)$category;
        }

        if ($countWhere) {
            $countSql .= " WHERE " . implode(" AND ", $countWhere);
        }

        $countStmt = $con->prepare($countSql);
        foreach ($countParams as $i => $val) {
            $countStmt->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $totalProducts = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Main query
        $sql = "SELECT p.*, ";
        $conditions = [];
        $params = [];

        $pathParts = array_slice(explode('/', $_SERVER['PHP_SELF']), -5, 4);
        $linkName = implode('/', $pathParts) . '/' . basename(__FILE__, '.php');
        updateUsageTimes($linkName);

        if ($search !== '') {
            $terms = array_filter(
                array_map('trim', preg_split('/\s+/u', $search)),
                fn($t) => mb_strlen($t, 'UTF-8') >= 2
            );

            if ($terms) {
                $relevanceParts = [];
                $conditionParts = [];

                foreach ($terms as $term) {
                    $conditionParts[] = "p.search_text LIKE ?";
                    $relevanceParts[]  = "p.search_text LIKE ?";
                    $params[] = "%$term%";
                    $params[] = "%$term%";
                }

                $sql .= "(" . implode(' + ', $relevanceParts) . ") AS relevance, ";
                $conditions[] = "(" . implode(' OR ', $conditionParts) . ")";
            } else {
                $sql .= "0 AS relevance, ";
            }
        } else {
            $sql .= "0 AS relevance, ";
        }

        $sql = rtrim($sql, ", ") . " FROM products_data p";

        if ($category !== null && (is_numeric($category) || is_string($category))) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM product_category pc 
                WHERE pc.product_id = p.id AND pc.category_id = ?
            )";
            $params[] = (int)$category;
        }

        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= $search ? " ORDER BY relevance DESC, p.id DESC" : " ORDER BY p.id DESC";
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $con->prepare($sql);
        foreach ($params as $i => $value) {
            $stmt->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // Common processing for both single and list mode
    // ------------------------------------------------------------------
    $productIds = array_column($products, 'id') ?: [];

    // Fetch variations
    $variationsByProduct = [];
    if (!empty($productIds)) {
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $varStmt = $con->prepare("
            SELECT product_id, attributes, price, regular_price, sale_price, stock_quantity, images
            FROM product_variations 
            WHERE product_id IN ($placeholders)
            ORDER BY id
        ");
        $varStmt->execute($productIds);
        foreach ($varStmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
            $pid = $v['product_id'];
            unset($v['product_id']);
            $v['attributes'] = is_string($v['attributes']) ? json_decode($v['attributes'], true) ?: [] : [];
            if (is_string($v['images'])) {
                $imgs = json_decode($v['images'], true) ?: [];
                $v['images'] = array_map(fn($i) => $imageBase . ltrim(str_replace('\\', '/', $i), '/'), $imgs);
            }
            $variationsByProduct[$pid][] = $v;
        }
    }

    // Fetch categories
    $productCategories = [];
    $categoryCache = [];

    if (!empty($productIds)) {
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        $relStmt = $con->prepare("
            SELECT product_id, category_id 
            FROM product_category 
            WHERE product_id IN ($placeholders)
        ");
        $relStmt->execute($productIds);
        $relations = $relStmt->fetchAll(PDO::FETCH_ASSOC);

        $allCatIds = [];
        foreach ($relations as $r) {
            $pid = (int)$r['product_id'];
            $cid = (int)$r['category_id'];
            $productCategories[$pid][] = $cid;
            $allCatIds[$cid] = true;
        }
        $allCatIds = array_keys($allCatIds);

        if (!empty($allCatIds)) {
            $catPlaceholders = str_repeat('?,', count($allCatIds) - 1) . '?';
            $catStmt = $con->prepare("
                SELECT id, name, slug, parent, image
                FROM categories2 
                WHERE id IN ($catPlaceholders)
            ");
            $catStmt->execute($allCatIds);
            foreach ($catStmt->fetchAll(PDO::FETCH_ASSOC) as $cat) {
                $imageJson = is_string($cat['image']) ? json_decode($cat['image'], true) : null;
                $imageUrl = $imageJson['src'] ?? '';

                $categoryCache[(int)$cat['id']] = [
                    'id'        => (int)$cat['id'],
                    'name'      => $cat['name'],
                    'slug'      => $cat['slug'],
                    'parent_id' => $cat['parent'] && $cat['parent'] !== '0' ? (int)$cat['parent'] : null,
                    'image'     => $imageUrl
                ];
            }
        }
    }

    // Process each product
    foreach ($products as &$product) {
        decodeJsonFields($product, $jsonFields);

        $product['variations'] = $variationsByProduct[$product['id']] ?? [];

        $catIds = $productCategories[$product['id']] ?? [];
        $cats = [];
        foreach ($catIds as $cid) {
            if (isset($categoryCache[$cid])) {
                $cats[] = $categoryCache[$cid];
            }
        }
        $product['categories'] = $cats;

        // Localization
        if (!empty($product['translations']) && is_array($product['translations'])) {
            foreach ($product['translations'] as $t) {
                if (is_array($t) && ($t['locale'] ?? '') === $lang) {
                    foreach (['name', 'description', 'short_description'] as $f) {
                        if (!empty($t[$f])) $product[$f] = $t[$f];
                    }
                    break;
                }
            }
        }

        // Fix product images
        if (!empty($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $type => &$imgs) {
                if (is_array($imgs)) {
                    foreach ($imgs as &$img) {
                        if (is_string($img)) {
                            $img = $imageBase . ltrim(str_replace('\\', '/', $img), '/');
                        }
                    }
                } elseif (is_string($imgs)) {
                    $product['images'][$type] = $imageBase . ltrim(str_replace('\\', '/', $imgs), '/');
                }
            }
        }

        foreach ($hideFields as $f) unset($product[$f]);
    }
    unset($product);

    // ------------------------------------------------------------------
    // Final response
    // ------------------------------------------------------------------
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $products,
        'pagination' => [
            'total' => $totalProducts,
            'page'  => $page,
            'limit' => $limit,
            'pages' => max(1, ceil($totalProducts / $limit))
        ],
        'lang' => $lang
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log("Products API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}