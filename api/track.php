<?php
/**
 * API - تتبع الأحداث التحليلية
 * POST /api/track.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $eventType = $input['event_type'] ?? '';
    $productId = $input['product_id'] ?? null;
    $sessionId = $input['session_id'] ?? '';

    // التحقق من البيانات
    if (empty($eventType) || empty($sessionId)) {
        sendJsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }

    // التحقق من نوع الحدث
    $validEvents = ['page_view', 'product_click', 'purchase_button_click'];
    if (!in_array($eventType, $validEvents)) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid event type'], 400);
    }

    $db = getDB();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';

    $stmt = $db->prepare("INSERT INTO analytics_events (event_type, product_id, session_id, user_agent, referrer)
                         VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$eventType, $productId, $sessionId, $userAgent, $referrer]);

    sendJsonResponse(['success' => true, 'message' => 'Event tracked successfully']);

} catch (Exception $e) {
    error_log("Track API Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Failed to track event'], 500);
}
?>
