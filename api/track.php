<?php
/**
 * Enhanced Tracking API for PyraStore UAE
 * Logs user interactions for analytics and conversion tracking
 */

header('Content-Type: application/json; charset=utf-8');

// CORS Policy - السماح فقط للـ domains المحددة
$allowedOrigins = ['https://events.pyramedia.info', 'http://localhost', 'http://127.0.0.1'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $eventType = $input['event_type'] ?? '';
    $productId = $input['product_id'] ?? null;
    $productTitle = $input['product_title'] ?? null;
    $sessionId = $input['session_id'] ?? '';
    $language = $input['language'] ?? 'ar';
    $category = $input['category'] ?? null;
    $price = $input['price'] ?? null;
    $metadata = isset($input['metadata']) ? json_encode($input['metadata']) : null;

    // التحقق من البيانات الأساسية
    if (empty($eventType)) {
        sendJsonResponse(['success' => false, 'message' => 'Missing event_type'], 400);
    }

    $db = getDB();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $userIp = $_SERVER['REMOTE_ADDR'] ?? null;

    // Insert into enhanced tracking table
    $stmt = $db->prepare("
        INSERT INTO click_tracking
        (event_type, product_id, product_title, user_ip, user_agent, referrer,
         session_id, language, category, price, metadata)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $eventType, $productId, $productTitle, $userIp, $userAgent,
        $referrer, $sessionId, $language, $category, $price, $metadata
    ]);

    // Also insert into old analytics_events table for backwards compatibility (if exists)
    try {
        $validEvents = ['page_view', 'product_click', 'purchase_button_click'];
        if (in_array($eventType, $validEvents)) {
            $stmt2 = $db->prepare("INSERT INTO analytics_events (event_type, product_id, session_id, user_agent, referrer)
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt2->execute([$eventType, $productId, $sessionId, $userAgent, $referrer]);
        }
    } catch (Exception $e) {
        // Silently fail if old table doesn't exist
        error_log("Legacy tracking failed (this is OK): " . $e->getMessage());
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Event tracked successfully',
        'tracking_id' => $db->lastInsertId()
    ]);

} catch (Exception $e) {
    error_log("Track API Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to track event'], 500);
}
?>
