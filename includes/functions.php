<?php
/**
 * الدوال المساعدة - PYRASTORE
 */

/**
 * تنظيف وتأمين المدخلات من XSS
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * إعادة التوجيه
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * الحصول على إعداد من قاعدة البيانات
 */
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("Error getting setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * تحديث إعداد في قاعدة البيانات
 */
function updateSetting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                             ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        error_log("Error updating setting: " . $e->getMessage());
        return false;
    }
}

/**
 * توليد CSRF Token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * التحقق من CSRF Token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * التحقق من تسجيل دخول الأدمن
 */
function isAdminLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * طلب تسجيل الدخول - إعادة توجيه إذا لم يكن مسجل دخول
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect('/admin/login.php');
    }
}

/**
 * تنسيق السعر
 */
function formatPrice($price, $currency = 'AED') {
    return number_format($price, 2) . ' ' . $currency;
}

/**
 * حساب نسبة الخصم
 */
function calculateDiscount($originalPrice, $currentPrice) {
    if ($originalPrice <= 0 || $currentPrice >= $originalPrice) {
        return 0;
    }
    return round((($originalPrice - $currentPrice) / $originalPrice) * 100);
}

/**
 * حساب قيمة التوفير
 */
function calculateSavings($originalPrice, $currentPrice) {
    return max(0, $originalPrice - $currentPrice);
}

/**
 * اختصار النص
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * الحصول على اسم الفئة بالعربية
 */
function getCategoryNameAr($category) {
    $categories = [
        'electronics' => 'إلكترونيات',
        'fashion' => 'أزياء',
        'home' => 'منزل ومطبخ',
        'sports' => 'رياضة',
        'beauty' => 'جمال وعناية',
        'books' => 'كتب',
        'toys' => 'ألعاب',
        'other' => 'منتجات أخرى'
    ];
    return $categories[$category] ?? 'منتجات أخرى';
}

/**
 * الحصول على أيقونة الفئة
 */
function getCategoryIcon($category) {
    $icons = [
        'electronics' => '📱',
        'fashion' => '👔',
        'home' => '🏠',
        'sports' => '⚽',
        'beauty' => '💄',
        'books' => '📚',
        'toys' => '🧸',
        'other' => '🛍️'
    ];
    return $icons[$category] ?? '🛍️';
}

/**
 * تحويل رابط Google Drive للعرض المباشر
 */
function convertDriveLink($url) {
    // تحويل رابط Google Drive من صيغة المشاركة إلى صيغة العرض المباشر
    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
    }
    return $url;
}

/**
 * توليد Session ID عشوائي
 */
function generateSessionId() {
    return bin2hex(random_bytes(16));
}

/**
 * تسجيل حدث في التحليلات
 */
function logAnalyticsEvent($eventType, $productId = null) {
    try {
        $db = getDB();

        // الحصول على Session ID من Cookie أو إنشاء واحد جديد
        $sessionId = $_COOKIE['pyra_session'] ?? generateSessionId();
        if (!isset($_COOKIE['pyra_session'])) {
            setcookie('pyra_session', $sessionId, time() + (86400 * 30), '/'); // 30 يوم
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        $stmt = $db->prepare("INSERT INTO analytics_events (event_type, product_id, session_id, user_agent, referrer)
                             VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$eventType, $productId, $sessionId, $userAgent, $referrer]);

        return true;
    } catch (PDOException $e) {
        error_log("Error logging analytics event: " . $e->getMessage());
        return false;
    }
}

/**
 * تحويل رابط فيديو YouTube إلى embed
 */
function convertYouTubeLink($url) {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return $url;
}

/**
 * التحقق من صحة البريد الإلكتروني
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * التحقق من صحة الرابط
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * الحصول على عدد المنتجات حسب الفئة
 */
function getProductCountByCategory() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT category, COUNT(*) as count FROM products WHERE is_active = 1 GROUP BY category");
        $counts = [];
        while ($row = $stmt->fetch()) {
            $counts[$row['category']] = $row['count'];
        }
        return $counts;
    } catch (PDOException $e) {
        error_log("Error getting product counts: " . $e->getMessage());
        return [];
    }
}

/**
 * توليد API Key عشوائي
 */
function generateApiKey() {
    return bin2hex(random_bytes(32));
}

/**
 * التحقق من API Key
 */
function verifyApiKey($providedKey) {
    $storedKey = getSetting('api_key');
    return !empty($providedKey) && hash_equals($storedKey, $providedKey);
}

/**
 * إرسال استجابة JSON
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * الحصول على عنوان IP الحقيقي للزائر
 */
function getRealIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * تحويل التاريخ إلى صيغة عربية مقروءة
 */
function formatDateArabic($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'الآن';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' ' . ($mins == 1 ? 'دقيقة' : 'دقائق');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . ($hours == 1 ? 'ساعة' : 'ساعات');
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' ' . ($days == 1 ? 'يوم' : 'أيام');
    } else {
        return date('Y-m-d', $timestamp);
    }
}
?>
