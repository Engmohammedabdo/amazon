<?php
/**
 * Search Suggestions API
 * Returns product title suggestions based on search query
 */

header('Content-Type: application/json');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get query parameter
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validate query
if (empty($query) || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Limit query length
$query = substr($query, 0, 100);

// Load database connection
require_once __DIR__ . '/../includes/db.php';

try {
    $db = getDB();

    // Prepare query with LIKE for partial matching
    // Using prepared statements to prevent SQL injection
    $searchPattern = '%' . $query . '%';

    $sql = "SELECT
                SUBSTRING_INDEX(title, ' ', 4) as short_title,
                COUNT(*) as count
            FROM products
            WHERE is_active = 1
            AND title LIKE :search
            GROUP BY short_title
            ORDER BY count DESC
            LIMIT 5";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':search', $searchPattern, PDO::PARAM_STR);
    $stmt->execute();

    $suggestions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'term' => htmlspecialchars($row['short_title'], ENT_QUOTES, 'UTF-8'),
            'count' => (int)$row['count'],
            'icon' => (int)$row['count'] > 3 ? '🔥' : '🔍'
        ];
    }

    echo json_encode($suggestions);

} catch (PDOException $e) {
    error_log('Search suggestions error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
