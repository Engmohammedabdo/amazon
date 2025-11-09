<?php
require_once __DIR__ . '/config/config.php';

$lang = getCurrentLang();
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('الصفحة غير موجودة', '404 - Page Not Found') ?> - <?= t('متجر بيرا', 'PyraStore') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .error-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-home {
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-back {
            padding: 14px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: 2px solid #667eea;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
        }

        .suggestions {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
            text-align: left;
        }

        .suggestions h3 {
            color: #1a1a1a;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .suggestions ul {
            list-style: none;
            padding: 0;
        }

        .suggestions li {
            padding: 8px 0;
            color: #666;
        }

        .suggestions a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .suggestions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="error-icon">🔍</div>
            <h1 class="error-title">
                <?= t('عذراً! الصفحة غير موجودة', 'Oops! Page Not Found') ?>
            </h1>
            <p class="error-message">
                <?= t(
                    'الصفحة التي تبحث عنها غير موجودة أو تم نقلها إلى موقع آخر.',
                    'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'
                ) ?>
            </p>

            <div class="error-actions">
                <a href="/" class="btn-home">
                    🏠 <?= t('العودة للرئيسية', 'Go to Homepage') ?>
                </a>
                <a href="javascript:history.back()" class="btn-back">
                    ← <?= t('الصفحة السابقة', 'Go Back') ?>
                </a>
            </div>

            <div class="suggestions">
                <h3><?= t('ربما تبحث عن:', 'You might be looking for:') ?></h3>
                <ul>
                    <li>🛍️ <a href="/"><?= t('المتجر الرئيسي', 'Main Store') ?></a></li>
                    <li>📱 <a href="/?category=electronics"><?= t('إلكترونيات', 'Electronics') ?></a></li>
                    <li>👕 <a href="/?category=fashion"><?= t('أزياء', 'Fashion') ?></a></li>
                    <li>🔐 <a href="/admin"><?= t('لوحة التحكم', 'Admin Panel') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
