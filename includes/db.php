<?php
/**
 * ملف الاتصال بقاعدة البيانات - PYRASTORE
 * يستخدم PDO للحماية من SQL Injection
 */

// التحقق من وجود ملف الإعدادات
if (!file_exists(__DIR__ . '/config.php')) {
    die('خطأ: ملف config.php غير موجود. الرجاء تشغيل install.php أولاً.');
}

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;

    /**
     * Constructor - إنشاء اتصال PDO
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("خطأ في الاتصال بقاعدة البيانات. الرجاء المحاولة لاحقاً.");
        }
    }

    /**
     * الحصول على نسخة واحدة من الاتصال (Singleton Pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * الحصول على الاتصال
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * منع الاستنساخ
     */
    private function __clone() {}

    /**
     * منع فك التسلسل
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * دالة مساعدة للحصول على الاتصال
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
?>
