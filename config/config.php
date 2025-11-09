<?php
/**
 * General Configuration
 * PyraStore - Amazon Affiliate UAE
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Site Configuration
define('SITE_NAME_AR', 'متجر بيرا - الإمارات');
define('SITE_NAME_EN', 'PyraStore - UAE');
define('SITE_URL', 'https://yoursite.com'); // Replace with your domain
define('ADMIN_URL', SITE_URL . '/admin');

// Amazon Affiliate Configuration
define('AFFILIATE_ID', 'pyrastore-21');
define('AMAZON_DOMAIN', 'amazon.ae');
define('AMAZON_BASE_URL', 'https://www.' . AMAZON_DOMAIN);

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Default Language
define('DEFAULT_LANG', 'ar');

// Cookie Settings
define('COOKIE_LIFETIME', 60 * 60 * 24 * 30); // 30 days

// Security
define('ADMIN_SESSION_TIMEOUT', 60 * 60 * 2); // 2 hours

// Helper Functions
function getCurrentLang() {
    if (isset($_SESSION['lang'])) {
        return $_SESSION['lang'];
    }
    if (isset($_COOKIE['lang'])) {
        return $_COOKIE['lang'];
    }
    return DEFAULT_LANG;
}

function setLanguage($lang) {
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + COOKIE_LIFETIME, '/');
}

function t($ar, $en) {
    $lang = getCurrentLang();
    return $lang === 'ar' ? $ar : $en;
}

function buildAffiliateLink($amazonUrl) {
    if (strpos($amazonUrl, '?') !== false) {
        return $amazonUrl . '&tag=' . AFFILIATE_ID;
    } else {
        return $amazonUrl . '?tag=' . AFFILIATE_ID;
    }
}

function formatPrice($price, $currency = 'AED') {
    $lang = getCurrentLang();
    if ($lang === 'ar') {
        return number_format($price, 2) . ' ' . $currency;
    } else {
        return $currency . ' ' . number_format($price, 2);
    }
}

function calculateDiscount($originalPrice, $currentPrice) {
    if ($originalPrice <= 0 || $currentPrice >= $originalPrice) {
        return 0;
    }
    return round((($originalPrice - $currentPrice) / $originalPrice) * 100);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > ADMIN_SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: /admin/login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

function generateSessionId() {
    if (!isset($_SESSION['visitor_session_id'])) {
        $_SESSION['visitor_session_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['visitor_session_id'];
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getDeviceType() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/mobile|android|iphone|ipad|phone/i', $userAgent)) {
        return 'mobile';
    } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
        return 'tablet';
    } else {
        return 'desktop';
    }
}

function getBrowser() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    if (strpos($userAgent, 'Opera') !== false) return 'Opera';
    return 'Other';
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function uploadImage($file, $directory = 'products') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }

    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }

    $uploadPath = UPLOAD_DIR . $directory . '/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadPath . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'url' => UPLOAD_URL . $directory . '/' . $filename
        ];
    }

    return ['success' => false, 'message' => 'Failed to move file'];
}

// Get site settings from database
function getSiteSettings() {
    static $settings = null;

    if ($settings === null) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            $settings = [];
        }
    }

    return $settings;
}

function getSetting($key, $default = '') {
    $settings = getSiteSettings();
    return $settings[$key] ?? $default;
}
