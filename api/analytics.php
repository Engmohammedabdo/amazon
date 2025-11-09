<?php
/**
 * Analytics API
 * Provides statistics and analytics data
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

// Require admin authentication
requireAdmin();

if ($method !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $type = $_GET['type'] ?? 'overview';

    switch ($type) {
        case 'overview':
            getOverview();
            break;

        case 'top_products':
            getTopProducts();
            break;

        case 'daily_stats':
            getDailyStats();
            break;

        case 'conversion_rate':
            getConversionRate();
            break;

        case 'traffic_sources':
            getTrafficSources();
            break;

        case 'device_stats':
            getDeviceStats();
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid type'], 400);
    }

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}

/**
 * Get overview statistics
 */
function getOverview() {
    global $db;

    $period = $_GET['period'] ?? 7; // days

    // Total clicks
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM click_tracking
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    ");
    $stmt->execute([':period' => $period]);
    $totalClicks = $stmt->fetch()['total'];

    // Product clicks
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM click_tracking
        WHERE click_type = 'product_click'
        AND created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    ");
    $stmt->execute([':period' => $period]);
    $productClicks = $stmt->fetch()['total'];

    // Purchase clicks
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM click_tracking
        WHERE click_type = 'purchase_click'
        AND created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    ");
    $stmt->execute([':period' => $period]);
    $purchaseClicks = $stmt->fetch()['total'];

    // Unique visitors
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT session_id) as total
        FROM click_tracking
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
    ");
    $stmt->execute([':period' => $period]);
    $uniqueVisitors = $stmt->fetch()['total'];

    // Conversion rate
    $conversionRate = $productClicks > 0 ? round(($purchaseClicks / $productClicks) * 100, 2) : 0;

    // CTR (Click-through rate)
    $ctr = $uniqueVisitors > 0 ? round(($productClicks / $uniqueVisitors) * 100, 2) : 0;

    jsonResponse([
        'success' => true,
        'data' => [
            'total_clicks' => intval($totalClicks),
            'product_clicks' => intval($productClicks),
            'purchase_clicks' => intval($purchaseClicks),
            'unique_visitors' => intval($uniqueVisitors),
            'conversion_rate' => $conversionRate,
            'ctr' => $ctr,
            'period_days' => intval($period)
        ]
    ]);
}

/**
 * Get top performing products
 */
function getTopProducts() {
    global $db;

    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 10;
    $period = $_GET['period'] ?? 30; // days

    $stmt = $db->prepare("
        SELECT
            p.id,
            p.product_id,
            p.title_ar,
            p.title_en,
            p.category,
            p.price,
            COUNT(DISTINCT ct.session_id) as unique_views,
            SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
            SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks,
            ROUND(
                (SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) * 100.0 /
                NULLIF(SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END), 0)),
                2
            ) as conversion_rate,
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN click_tracking ct ON p.id = ct.product_id
            AND ct.created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
        WHERE p.is_active = 1
        GROUP BY p.id
        HAVING product_clicks > 0
        ORDER BY purchase_clicks DESC, product_clicks DESC
        LIMIT :limit
    ");

    $stmt->bindValue(':period', $period, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $products = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $products
    ]);
}

/**
 * Get daily statistics
 */
function getDailyStats() {
    global $db;

    $days = isset($_GET['days']) ? min(90, max(1, intval($_GET['days']))) : 30;

    $stmt = $db->prepare("
        SELECT
            DATE(created_at) as date,
            COUNT(DISTINCT session_id) as unique_visitors,
            COUNT(*) as total_clicks,
            SUM(CASE WHEN click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
            SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks
        FROM click_tracking
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");

    $stmt->execute([':days' => $days]);
    $stats = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $stats
    ]);
}

/**
 * Get conversion rate by product
 */
function getConversionRate() {
    global $db;

    $period = $_GET['period'] ?? 30;

    $stmt = $db->prepare("
        SELECT
            p.id,
            p.title_ar,
            p.title_en,
            p.category,
            SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
            SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks,
            ROUND(
                (SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) * 100.0 /
                NULLIF(SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END), 0)),
                2
            ) as conversion_rate
        FROM products p
        LEFT JOIN click_tracking ct ON p.id = ct.product_id
            AND ct.created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
        WHERE p.is_active = 1
        GROUP BY p.id
        HAVING product_clicks > 0
        ORDER BY conversion_rate DESC
        LIMIT 20
    ");

    $stmt->execute([':period' => $period]);
    $data = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $data
    ]);
}

/**
 * Get traffic sources (UTM)
 */
function getTrafficSources() {
    global $db;

    $period = $_GET['period'] ?? 30;

    $stmt = $db->prepare("
        SELECT
            COALESCE(utm_source, 'Direct') as source,
            COALESCE(utm_medium, 'None') as medium,
            COALESCE(utm_campaign, 'None') as campaign,
            COUNT(DISTINCT session_id) as unique_visitors,
            COUNT(*) as total_clicks,
            SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as conversions
        FROM click_tracking
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
        GROUP BY utm_source, utm_medium, utm_campaign
        ORDER BY unique_visitors DESC
        LIMIT 20
    ");

    $stmt->execute([':period' => $period]);
    $sources = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $sources
    ]);
}

/**
 * Get device statistics
 */
function getDeviceStats() {
    global $db;

    $period = $_GET['period'] ?? 30;

    $stmt = $db->prepare("
        SELECT
            device_type,
            browser,
            COUNT(DISTINCT session_id) as unique_visitors,
            COUNT(*) as total_clicks,
            SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as conversions
        FROM click_tracking
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
        GROUP BY device_type, browser
        ORDER BY unique_visitors DESC
    ");

    $stmt->execute([':period' => $period]);
    $stats = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $stats
    ]);
}
