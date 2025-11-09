<?php
/**
 * Categories API
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

try {
    if ($method === 'GET') {
        getCategories();
    } else {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}

function getCategories() {
    global $db;

    // Get all active categories with product counts
    $stmt = $db->query("
        SELECT
            c.*,
            COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.slug = p.category AND p.is_active = 1
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.display_order ASC, c.name_en ASC
    ");

    $categories = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $categories
    ]);
}
