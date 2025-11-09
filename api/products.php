<?php
/**
 * API - جلب المنتجات مع الفلترة والبحث
 * GET /api/products.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $db = getDB();

    // بناء الاستعلام الأساسي
    $sql = "SELECT * FROM products WHERE is_active = 1";
    $params = [];

    // البحث
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $sql .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }

    // الفئة
    if (!empty($_GET['category'])) {
        $sql .= " AND category = ?";
        $params[] = $_GET['category'];
    }

    // نطاق السعر
    if (!empty($_GET['min_price'])) {
        $sql .= " AND price >= ?";
        $params[] = floatval($_GET['min_price']);
    }

    if (!empty($_GET['max_price'])) {
        $sql .= " AND price <= ?";
        $params[] = floatval($_GET['max_price']);
    }

    // نسبة الخصم
    if (!empty($_GET['discount'])) {
        $discount = intval($_GET['discount']);
        $sql .= " AND discount_percentage >= ?";
        $params[] = $discount;
    }

    // حساب الإجمالي
    $countStmt = $db->prepare(str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql));
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // الترتيب
    $sort = $_GET['sort'] ?? 'newest';
    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY price DESC";
            break;
        case 'discount':
            $sql .= " ORDER BY discount_percentage DESC";
            break;
        default:
            $sql .= " ORDER BY created_at DESC";
    }

    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = min(100, intval($_GET['per_page'] ?? 50)); // max 100 items per page
    $offset = ($page - 1) * $perPage;

    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    // تنفيذ الاستعلام
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // حساب معلومات Pagination
    $totalPages = ceil($total / $perPage);

    sendJsonResponse([
        'success' => true,
        'products' => $products,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'has_more' => $page < $totalPages
    ]);

} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'حدث خطأ أثناء جلب المنتجات'
    ], 500);
}
?>
