<?php
/**
 * ملف الإعدادات - PYRASTORE
 * هذا ملف مثال - سيتم إنشاء config.php تلقائياً من خلال install.php
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'pyrastore_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// إعدادات الموقع
define('SITE_URL', 'http://localhost/amazon'); // تغيير هذا إلى رابط موقعك
define('SITE_NAME', 'PYRASTORE');
define('SITE_TAGLINE', 'UAE PICKS');

// إعدادات الأمان
define('SESSION_LIFETIME', 7200); // مدة الجلسة بالثواني (2 ساعة)

// المنطقة الزمنية
date_default_timezone_set('Asia/Dubai');

// عرض الأخطاء (تعطيل في الإنتاج)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
?>
