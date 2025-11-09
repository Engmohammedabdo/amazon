<?php
/**
 * Products API
 * Handles all product-related requests
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                getProduct($_GET['id']);
            } else {
                getProducts();
            }
            break;

        case 'POST':
            createProduct();
            break;

        case 'PUT':
            updateProduct();
            break;

        case 'DELETE':
            deleteProduct();
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}

/**
 * Get all products with filters
 */
function getProducts() {
    global $db;

    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : PRODUCTS_PER_PAGE;
    $offset = ($page - 1) * $limit;

    // Build query
    $where = ['p.is_active = 1'];
    $params = [];

    // Category filter
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = 'p.category = :category';
        $params[':category'] = $_GET['category'];
    }

    // Search filter
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $where[] = '(p.title_ar LIKE :search1 OR p.title_en LIKE :search2 OR p.description_ar LIKE :search3 OR p.description_en LIKE :search4)';
        $params[':search1'] = $search;
        $params[':search2'] = $search;
        $params[':search3'] = $search;
        $params[':search4'] = $search;
    }

    // Price range filter
    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $where[] = 'p.price >= :min_price';
        $params[':min_price'] = floatval($_GET['min_price']);
    }

    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $where[] = 'p.price <= :max_price';
        $params[':max_price'] = floatval($_GET['max_price']);
    }

    // Discount filter
    if (isset($_GET['min_discount']) && is_numeric($_GET['min_discount'])) {
        $where[] = 'p.discount_percentage >= :min_discount';
        $params[':min_discount'] = intval($_GET['min_discount']);
    }

    // Featured filter
    if (isset($_GET['featured']) && $_GET['featured'] == '1') {
        $where[] = 'p.is_featured = 1';
    }

    $whereClause = implode(' AND ', $where);

    // Sorting
    $sortColumn = 'p.created_at';
    $sortOrder = 'DESC';

    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'price_asc':
                $sortColumn = 'p.price';
                $sortOrder = 'ASC';
                break;
            case 'price_desc':
                $sortColumn = 'p.price';
                $sortOrder = 'DESC';
                break;
            case 'discount_desc':
                $sortColumn = 'p.discount_percentage';
                $sortOrder = 'DESC';
                break;
            case 'rating_desc':
                $sortColumn = 'p.rating';
                $sortOrder = 'DESC';
                break;
            case 'newest':
            default:
                $sortColumn = 'p.created_at';
                $sortOrder = 'DESC';
                break;
        }
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM products p WHERE $whereClause";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Get products with primary image
    $query = "
        SELECT
            p.*,
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
            (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as images_count,
            (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND is_approved = 1) as reviews_count
        FROM products p
        WHERE $whereClause
        ORDER BY $sortColumn $sortOrder
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll();

    // Build affiliate links
    foreach ($products as &$product) {
        $product['affiliate_link'] = buildAffiliateLink($product['amazon_url']);
    }

    jsonResponse([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => intval($total),
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get single product with all images and reviews
 */
function getProduct($id) {
    global $db;

    // Get product
    $stmt = $db->prepare("
        SELECT p.*
        FROM products p
        WHERE p.id = :id AND p.is_active = 1
    ");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }

    // Get all images
    $stmt = $db->prepare("
        SELECT image_url, is_primary, display_order
        FROM product_images
        WHERE product_id = :id
        ORDER BY is_primary DESC, display_order ASC
    ");
    $stmt->execute([':id' => $id]);
    $product['images'] = $stmt->fetchAll();

    // Get reviews
    $stmt = $db->prepare("
        SELECT customer_name, rating, review_text, created_at, is_verified
        FROM reviews
        WHERE product_id = :id AND is_approved = 1
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([':id' => $id]);
    $product['reviews'] = $stmt->fetchAll();

    // Get similar products (same category)
    $stmt = $db->prepare("
        SELECT
            p.id,
            p.title_ar,
            p.title_en,
            p.price,
            p.original_price,
            p.discount_percentage,
            p.rating,
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        WHERE p.category = :category AND p.id != :id AND p.is_active = 1
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([':category' => $product['category'], ':id' => $id]);
    $product['similar_products'] = $stmt->fetchAll();

    // Build affiliate link
    $product['affiliate_link'] = buildAffiliateLink($product['amazon_url']);

    jsonResponse([
        'success' => true,
        'data' => $product
    ]);
}

/**
 * Create new product (admin only)
 */
function createProduct() {
    requireAdmin();
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['title_ar', 'title_en', 'category', 'price', 'amazon_url'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
        }
    }

    try {
        $db->beginTransaction();

        // Calculate discount if original price provided
        $discount = 0;
        if (isset($data['original_price']) && $data['original_price'] > $data['price']) {
            $discount = calculateDiscount($data['original_price'], $data['price']);
        }

        // Generate product ID from Amazon URL
        preg_match('/\/dp\/([A-Z0-9]+)/', $data['amazon_url'], $matches);
        $productId = $matches[1] ?? 'PROD_' . time();

        $stmt = $db->prepare("
            INSERT INTO products (
                product_id, title_ar, title_en, description_ar, description_en,
                category, price, original_price, discount_percentage,
                amazon_url, affiliate_link, rating, is_featured
            ) VALUES (
                :product_id, :title_ar, :title_en, :description_ar, :description_en,
                :category, :price, :original_price, :discount_percentage,
                :amazon_url, :affiliate_link, :rating, :is_featured
            )
        ");

        $affiliateLink = buildAffiliateLink($data['amazon_url']);

        $stmt->execute([
            ':product_id' => $productId,
            ':title_ar' => $data['title_ar'],
            ':title_en' => $data['title_en'],
            ':description_ar' => $data['description_ar'] ?? '',
            ':description_en' => $data['description_en'] ?? '',
            ':category' => $data['category'],
            ':price' => $data['price'],
            ':original_price' => $data['original_price'] ?? $data['price'],
            ':discount_percentage' => $discount,
            ':amazon_url' => $data['amazon_url'],
            ':affiliate_link' => $affiliateLink,
            ':rating' => $data['rating'] ?? 0,
            ':is_featured' => $data['is_featured'] ?? 0
        ]);

        $newProductId = $db->lastInsertId();

        // Add images if provided (supports both 'images' and 'additionalImages' fields)
        $imagesToAdd = [];

        // Check for primary image
        if (isset($data['image']) && !empty($data['image'])) {
            $imagesToAdd[] = $data['image'];
        } elseif (isset($data['primaryImage']) && !empty($data['primaryImage'])) {
            $imagesToAdd[] = $data['primaryImage'];
        }

        // Add images array
        if (isset($data['images']) && is_array($data['images'])) {
            $imagesToAdd = array_merge($imagesToAdd, $data['images']);
        }

        // Add additionalImages array
        if (isset($data['additionalImages']) && is_array($data['additionalImages'])) {
            $imagesToAdd = array_merge($imagesToAdd, $data['additionalImages']);
        }

        // Remove duplicates and save to database
        $imagesToAdd = array_unique($imagesToAdd);

        if (!empty($imagesToAdd)) {
            foreach ($imagesToAdd as $index => $imageUrl) {
                if (!empty($imageUrl)) {
                    $stmt = $db->prepare("
                        INSERT INTO product_images (product_id, image_url, is_primary, display_order)
                        VALUES (:product_id, :image_url, :is_primary, :display_order)
                    ");
                    $stmt->execute([
                        ':product_id' => $newProductId,
                        ':image_url' => $imageUrl,
                        ':is_primary' => $index === 0 ? 1 : 0,
                        ':display_order' => $index
                    ]);
                }
            }
        }

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Product created successfully',
            'id' => $newProductId
        ], 201);

    } catch (PDOException $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Update product (admin only)
 */
function updateProduct() {
    requireAdmin();
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
    }

    try {
        $db->beginTransaction();

        $updates = [];
        $params = [':id' => $data['id']];

        $allowedFields = [
            'title_ar', 'title_en', 'description_ar', 'description_en',
            'category', 'price', 'original_price', 'amazon_url',
            'rating', 'is_featured', 'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        // Recalculate discount if prices changed
        if (isset($data['price']) || isset($data['original_price'])) {
            // Get current values
            $current = $db->prepare("SELECT price, original_price FROM products WHERE id = :id");
            $current->execute([':id' => $data['id']]);
            $currentData = $current->fetch();

            $price = $data['price'] ?? $currentData['price'];
            $originalPrice = $data['original_price'] ?? $currentData['original_price'];

            $discount = calculateDiscount($originalPrice, $price);
            $updates[] = "discount_percentage = :discount_percentage";
            $params[':discount_percentage'] = $discount;
        }

        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
        }

        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Update images if provided
        if (isset($data['images']) || isset($data['additionalImages']) || isset($data['image']) || isset($data['primaryImage'])) {
            // Delete existing images
            $stmt = $db->prepare("DELETE FROM product_images WHERE product_id = :id");
            $stmt->execute([':id' => $data['id']]);

            // Add new images (supports multiple formats)
            $imagesToAdd = [];

            if (isset($data['image']) && !empty($data['image'])) {
                $imagesToAdd[] = $data['image'];
            } elseif (isset($data['primaryImage']) && !empty($data['primaryImage'])) {
                $imagesToAdd[] = $data['primaryImage'];
            }

            if (isset($data['images']) && is_array($data['images'])) {
                $imagesToAdd = array_merge($imagesToAdd, $data['images']);
            }

            if (isset($data['additionalImages']) && is_array($data['additionalImages'])) {
                $imagesToAdd = array_merge($imagesToAdd, $data['additionalImages']);
            }

            $imagesToAdd = array_unique($imagesToAdd);

            if (!empty($imagesToAdd)) {
                foreach ($imagesToAdd as $index => $imageUrl) {
                    if (!empty($imageUrl)) {
                        $stmt = $db->prepare("
                            INSERT INTO product_images (product_id, image_url, is_primary, display_order)
                            VALUES (:product_id, :image_url, :is_primary, :display_order)
                        ");
                        $stmt->execute([
                            ':product_id' => $data['id'],
                            ':image_url' => $imageUrl,
                            ':is_primary' => $index === 0 ? 1 : 0,
                            ':display_order' => $index
                        ]);
                    }
                }
            }
        }

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

/**
 * Delete product (admin only)
 */
function deleteProduct() {
    requireAdmin();
    global $db;

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
    }

    try {
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $data['id']]);

        jsonResponse([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);

    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}
