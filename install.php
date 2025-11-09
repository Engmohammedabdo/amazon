<?php
/**
 * سكريبت التثبيت التلقائي - PYRASTORE
 * يقوم بإنشاء قاعدة البيانات والملفات الأساسية
 */

// حماية من التثبيت المتكرر
if (file_exists(__DIR__ . '/includes/config.php')) {
    die('<h1>تحذير!</h1><p>التثبيت تم من قبل. احذف ملف includes/config.php لإعادة التثبيت.</p>');
}

$error = '';
$success = '';

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = trim($_POST['db_pass'] ?? '');
    $siteUrl = trim($_POST['site_url'] ?? '');

    // التحقق من المدخلات
    if (empty($dbName) || empty($dbUser)) {
        $error = 'الرجاء ملء جميع الحقول المطلوبة';
    } else {
        try {
            // محاولة الاتصال بقاعدة البيانات
            $dsn = "mysql:host={$dbHost};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // إنشاء قاعدة البيانات إذا لم تكن موجودة
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");

            // قراءة وتنفيذ ملف SQL
            $sqlFile = __DIR__ . '/DATABASE_SCHEMA.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);

                // تقسيم الملف إلى statements منفصلة
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($statement) {
                        return !empty($statement) &&
                               !preg_match('/^--/', $statement) &&
                               strlen($statement) > 5;
                    }
                );

                // تنفيذ كل statement
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
            } else {
                throw new Exception('ملف DATABASE_SCHEMA.sql غير موجود');
            }

            // إنشاء ملف config.php
            $configContent = "<?php\n";
            $configContent .= "/**\n";
            $configContent .= " * ملف الإعدادات - PYRASTORE\n";
            $configContent .= " * تم إنشاؤه تلقائياً بواسطة install.php\n";
            $configContent .= " */\n\n";
            $configContent .= "// إعدادات قاعدة البيانات\n";
            $configContent .= "define('DB_HOST', '{$dbHost}');\n";
            $configContent .= "define('DB_NAME', '{$dbName}');\n";
            $configContent .= "define('DB_USER', '{$dbUser}');\n";
            $configContent .= "define('DB_PASS', '" . addslashes($dbPass) . "');\n";
            $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
            $configContent .= "// إعدادات الموقع\n";
            $configContent .= "define('SITE_URL', '{$siteUrl}');\n";
            $configContent .= "define('SITE_NAME', 'PYRASTORE');\n";
            $configContent .= "define('SITE_TAGLINE', 'UAE PICKS');\n\n";
            $configContent .= "// إعدادات الأمان\n";
            $configContent .= "define('SESSION_LIFETIME', 7200); // 2 ساعة\n\n";
            $configContent .= "// المنطقة الزمنية\n";
            $configContent .= "date_default_timezone_set('Asia/Dubai');\n\n";
            $configContent .= "// عرض الأخطاء (تعطيل في الإنتاج)\n";
            $configContent .= "ini_set('display_errors', 0);\n";
            $configContent .= "ini_set('log_errors', 1);\n";
            $configContent .= "error_reporting(E_ALL);\n";
            $configContent .= "?>";

            // كتابة ملف config.php
            $configFile = __DIR__ . '/includes/config.php';
            if (!file_put_contents($configFile, $configContent)) {
                throw new Exception('فشل إنشاء ملف config.php');
            }

            // إنشاء ملف .htaccess للحماية
            $htaccessContent = "# PYRASTORE - .htaccess\n\n";
            $htaccessContent .= "# تمكين إعادة الكتابة\n";
            $htaccessContent .= "RewriteEngine On\n\n";
            $htaccessContent .= "# منع الوصول إلى الملفات الحساسة\n";
            $htaccessContent .= "<FilesMatch \"\\.(sql|log|env)$\">\n";
            $htaccessContent .= "    Order allow,deny\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</FilesMatch>\n\n";
            $htaccessContent .= "# حماية مجلد includes\n";
            $htaccessContent .= "<IfModule mod_rewrite.c>\n";
            $htaccessContent .= "    RewriteCond %{REQUEST_URI} ^/includes/.*\n";
            $htaccessContent .= "    RewriteRule ^(.*)$ - [F,L]\n";
            $htaccessContent .= "</IfModule>\n";

            file_put_contents(__DIR__ . '/.htaccess', $htaccessContent);

            $success = true;

        } catch (PDOException $e) {
            $error = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'خطأ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت PYRASTORE - موقع الأفلييت الاحترافي</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: #1A1A1A;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 5px;
            color: #FF6B35;
        }

        .header p {
            color: #ccc;
            font-size: 0.9rem;
        }

        .content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1A1A1A;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Cairo', sans-serif;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #FF6B35;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.85rem;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Cairo', sans-serif;
        }

        .btn:hover {
            background: #E55A2B;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            border: 2px solid #fcc;
            color: #c33;
        }

        .success-message {
            text-align: center;
        }

        .success-message h2 {
            color: #27ae60;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .success-message .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #FF6B35;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-box h3 {
            color: #1A1A1A;
            margin-bottom: 10px;
        }

        .info-box ul {
            list-style: none;
            padding-right: 20px;
        }

        .info-box li {
            margin: 8px 0;
            color: #555;
        }

        .info-box li:before {
            content: "✓ ";
            color: #27ae60;
            font-weight: bold;
            margin-left: 5px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-secondary {
            background: #1A1A1A;
        }

        .btn-secondary:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PYRASTORE</h1>
            <p>UAE PICKS - تثبيت الموقع</p>
        </div>

        <div class="content">
            <?php if ($success): ?>
                <div class="success-message">
                    <div class="icon">🎉</div>
                    <h2>تم التثبيت بنجاح!</h2>

                    <div class="info-box">
                        <h3>معلومات تسجيل الدخول الافتراضية:</h3>
                        <ul>
                            <li><strong>اسم المستخدم:</strong> admin</li>
                            <li><strong>كلمة المرور:</strong> admin123</li>
                            <li><strong>API Key:</strong> pyrastore-webhook-2025</li>
                        </ul>
                    </div>

                    <div class="info-box">
                        <h3>الخطوات التالية:</h3>
                        <ul>
                            <li>احذف ملف install.php من السيرفر (مهم للأمان)</li>
                            <li>قم بتغيير كلمة المرور الافتراضية</li>
                            <li>قم بتغيير API Key من لوحة التحكم</li>
                            <li>ابدأ بإضافة المنتجات</li>
                        </ul>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn" style="text-decoration: none; display: block; text-align: center;">
                            عرض الموقع
                        </a>
                        <a href="admin/login.php" class="btn btn-secondary" style="text-decoration: none; display: block; text-align: center;">
                            لوحة التحكم
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <strong>خطأ!</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>خادم قاعدة البيانات *</label>
                        <input type="text" name="db_host" value="localhost" required>
                        <small>عادة ما يكون localhost</small>
                    </div>

                    <div class="form-group">
                        <label>اسم قاعدة البيانات *</label>
                        <input type="text" name="db_name" placeholder="pyrastore_db" required>
                        <small>اسم قاعدة البيانات في cPanel</small>
                    </div>

                    <div class="form-group">
                        <label>اسم مستخدم قاعدة البيانات *</label>
                        <input type="text" name="db_user" placeholder="username" required>
                        <small>اسم المستخدم الخاص بقاعدة البيانات</small>
                    </div>

                    <div class="form-group">
                        <label>كلمة مرور قاعدة البيانات</label>
                        <input type="password" name="db_pass" placeholder="password">
                        <small>اتركه فارغاً إذا لم يكن هناك كلمة مرور</small>
                    </div>

                    <div class="form-group">
                        <label>رابط الموقع *</label>
                        <input type="url" name="site_url" placeholder="https://example.com" required>
                        <small>الرابط الكامل للموقع (بدون / في النهاية)</small>
                    </div>

                    <button type="submit" class="btn">
                        تثبيت الموقع
                    </button>
                </form>

                <div class="info-box" style="margin-top: 30px;">
                    <h3>ملاحظات مهمة:</h3>
                    <ul>
                        <li>تأكد من إنشاء قاعدة البيانات في cPanel أولاً</li>
                        <li>استخدم نفس اسم المستخدم وكلمة المرور من cPanel</li>
                        <li>سيتم إنشاء جميع الجداول تلقائياً</li>
                        <li>سيتم إضافة 5 منتجات تجريبية</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
