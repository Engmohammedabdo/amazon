<?php
/**
 * Click Tracking API
 * Tracks user interactions and clicks
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['product_id']) || !isset($data['click_type'])) {
        jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }

    $sessionId = $data['session_id'] ?? generateSessionId();
    $productId = intval($data['product_id']);
    $clickType = $data['click_type']; // product_view, product_click, purchase_click

    // Validate click type
    $validTypes = ['product_view', 'product_click', 'purchase_click'];
    if (!in_array($clickType, $validTypes)) {
        jsonResponse(['success' => false, 'message' => 'Invalid click type'], 400);
    }

    // Get UTM parameters
    $utmSource = $data['utm_source'] ?? $_GET['utm_source'] ?? null;
    $utmMedium = $data['utm_medium'] ?? $_GET['utm_medium'] ?? null;
    $utmCampaign = $data['utm_campaign'] ?? $_GET['utm_campaign'] ?? null;

    // Get user info
    $userIp = getUserIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    $deviceType = getDeviceType();
    $browser = getBrowser();

    // Insert tracking record
    $stmt = $db->prepare("
        INSERT INTO click_tracking (
            session_id, product_id, click_type, user_ip, user_agent,
            referrer, utm_source, utm_medium, utm_campaign,
            device_type, browser
        ) VALUES (
            :session_id, :product_id, :click_type, :user_ip, :user_agent,
            :referrer, :utm_source, :utm_medium, :utm_campaign,
            :device_type, :browser
        )
    ");

    $stmt->execute([
        ':session_id' => $sessionId,
        ':product_id' => $productId,
        ':click_type' => $clickType,
        ':user_ip' => $userIp,
        ':user_agent' => $userAgent,
        ':referrer' => $referrer,
        ':utm_source' => $utmSource,
        ':utm_medium' => $utmMedium,
        ':utm_campaign' => $utmCampaign,
        ':device_type' => $deviceType,
        ':browser' => $browser
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Click tracked successfully',
        'session_id' => $sessionId
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
