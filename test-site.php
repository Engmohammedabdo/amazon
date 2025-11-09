<?php
/**
 * PyraStore - Comprehensive Site Testing Script
 * Tests all functionality and reports issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];
$warnings = [];
$success = [];
$tests_run = 0;
$tests_passed = 0;

function test($name, $callback) {
    global $tests_run, $tests_passed, $errors, $success;
    $tests_run++;

    try {
        $result = $callback();
        if ($result === true || $result === null) {
            $tests_passed++;
            $success[] = $name;
            return true;
        } else {
            $errors[] = "$name: " . ($result ?: 'Failed');
            return false;
        }
    } catch (Exception $e) {
        $errors[] = "$name: " . $e->getMessage();
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PyraStore - Site Test Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        .test-section h2 {
            color: #1a1a1a;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .test-item {
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .test-item.success {
            background: #d1fae5;
            color: #065f46;
        }
        .test-item.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .test-item.warning {
            background: #fef3c7;
            color: #92400e;
        }
        .icon {
            font-size: 18px;
            min-width: 24px;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .summary h2 {
            margin-bottom: 15px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-card {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .code {
            background: #1a1a1a;
            color: #0f0;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PyraStore - Site Test Report</h1>
        <p class="subtitle">Comprehensive testing and diagnostics</p>

        <?php
        // =================================================================
        // TEST 1: File Structure
        // =================================================================
        echo '<div class="test-section">';
        echo '<h2>📁 File Structure Check</h2>';

        $required_files = [
            'index.php' => 'Homepage',
            'product.php' => 'Product details page',
            'config/config.php' => 'Main configuration',
            'config/database.php' => 'Database configuration',
            'admin/index.php' => 'Admin dashboard',
            'admin/login.php' => 'Admin login',
            'admin/products.php' => 'Products management',
            'admin/analytics.php' => 'Analytics page',
            'admin/categories.php' => 'Categories management',
            'admin/settings.php' => 'Settings page',
            'api/products.php' => 'Products API',
            'api/categories.php' => 'Categories API',
            'api/tracking.php' => 'Tracking API',
            'api/analytics.php' => 'Analytics API',
            'assets/css/style.css' => 'Main stylesheet',
            'assets/css/admin.css' => 'Admin stylesheet',
            'assets/js/main.js' => 'Main JavaScript',
            'assets/js/tracking.js' => 'Tracking JavaScript',
        ];

        foreach ($required_files as $file => $description) {
            $exists = file_exists(__DIR__ . '/' . $file);
            test("File: $description ($file)", function() use ($exists) {
                return $exists;
            });

            echo '<div class="test-item ' . ($exists ? 'success' : 'error') . '">';
            echo '<span class="icon">' . ($exists ? '✓' : '✗') . '</span>';
            echo '<span>' . $description . ' (' . $file . ')</span>';
            echo '</div>';
        }

        echo '</div>';

        // =================================================================
        // TEST 2: Database Connection
        // =================================================================
        echo '<div class="test-section">';
        echo '<h2>🗄️ Database Connection</h2>';

        $db_connected = false;
        $db_error = '';

        try {
            require_once __DIR__ . '/config/database.php';
            $db = getDB();
            $db_connected = true;

            echo '<div class="test-item success">';
            echo '<span class="icon">✓</span>';
            echo '<span>Database connection successful</span>';
            echo '</div>';

            test("Database connection", function() { return true; });
        } catch (Exception $e) {
            $db_error = $e->getMessage();
            echo '<div class="test-item error">';
            echo '<span class="icon">✗</span>';
            echo '<span>Database connection failed: ' . htmlspecialchars($db_error) . '</span>';
            echo '</div>';

            test("Database connection", function() use ($db_error) { return $db_error; });
        }

        if ($db_connected) {
            // Check tables
            $required_tables = [
                'products',
                'product_images',
                'categories',
                'click_tracking',
                'reviews',
                'settings',
                'admin_users'
            ];

            foreach ($required_tables as $table) {
                try {
                    $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
                    $count = $stmt->fetchColumn();

                    echo '<div class="test-item success">';
                    echo '<span class="icon">✓</span>';
                    echo '<span>Table: ' . $table . ' (' . $count . ' rows)</span>';
                    echo '</div>';

                    test("Table: $table", function() { return true; });
                } catch (Exception $e) {
                    echo '<div class="test-item error">';
                    echo '<span class="icon">✗</span>';
                    echo '<span>Table: ' . $table . ' - ' . htmlspecialchars($e->getMessage()) . '</span>';
                    echo '</div>';

                    test("Table: $table", function() use ($e) { return $e->getMessage(); });
                }
            }

            // Check UTF-8 encoding
            try {
                $stmt = $db->query("SELECT name_ar FROM categories LIMIT 1");
                $row = $stmt->fetch();

                if ($row && !empty($row['name_ar']) && mb_detect_encoding($row['name_ar'], 'UTF-8', true)) {
                    echo '<div class="test-item success">';
                    echo '<span class="icon">✓</span>';
                    echo '<span>UTF-8 encoding correct: ' . htmlspecialchars($row['name_ar']) . '</span>';
                    echo '</div>';
                    test("UTF-8 encoding", function() { return true; });
                } else {
                    echo '<div class="test-item error">';
                    echo '<span class="icon">✗</span>';
                    echo '<span>UTF-8 encoding issue detected - Run fix-encoding.php</span>';
                    echo '</div>';
                    test("UTF-8 encoding", function() { return "Encoding issue"; });
                }
            } catch (Exception $e) {
                // Silent fail
            }
        }

        echo '</div>';

        // =================================================================
        // TEST 3: Configuration
        // =================================================================
        echo '<div class="test-section">';
        echo '<h2>⚙️ Configuration Check</h2>';

        if ($db_connected) {
            require_once __DIR__ . '/config/config.php';

            // Check constants
            $constants = [
                'SITE_URL' => 'Site URL',
                'AFFILIATE_ID' => 'Amazon Affiliate ID',
                'DB_HOST' => 'Database Host',
                'DB_NAME' => 'Database Name',
            ];

            foreach ($constants as $const => $label) {
                if (defined($const)) {
                    $value = constant($const);
                    echo '<div class="test-item success">';
                    echo '<span class="icon">✓</span>';
                    echo '<span>' . $label . ': ' . htmlspecialchars($value) . '</span>';
                    echo '</div>';
                    test("Constant: $const", function() { return true; });
                } else {
                    echo '<div class="test-item error">';
                    echo '<span class="icon">✗</span>';
                    echo '<span>' . $label . ' not defined</span>';
                    echo '</div>';
                    test("Constant: $const", function() { return "Not defined"; });
                }
            }

            // Check settings from database
            try {
                $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $important_settings = [
                    'affiliate_id' => 'Affiliate ID',
                    'google_analytics_id' => 'Google Analytics',
                    'meta_pixel_id' => 'Meta Pixel',
                    'tiktok_pixel_id' => 'TikTok Pixel',
                ];

                foreach ($important_settings as $key => $label) {
                    $value = $settings[$key] ?? '';
                    $has_value = !empty($value);

                    echo '<div class="test-item ' . ($has_value ? 'success' : 'warning') . '">';
                    echo '<span class="icon">' . ($has_value ? '✓' : '⚠') . '</span>';
                    echo '<span>' . $label . ': ' . ($has_value ? htmlspecialchars($value) : 'Not configured') . '</span>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                // Silent fail
            }
        }

        echo '</div>';

        // =================================================================
        // TEST 4: Permissions
        // =================================================================
        echo '<div class="test-section">';
        echo '<h2>🔒 Directory Permissions</h2>';

        $directories = [
            'assets/uploads' => 'Uploads directory',
            'config' => 'Config directory',
        ];

        foreach ($directories as $dir => $label) {
            $path = __DIR__ . '/' . $dir;
            $writable = is_writable($path);

            echo '<div class="test-item ' . ($writable ? 'success' : 'error') . '">';
            echo '<span class="icon">' . ($writable ? '✓' : '✗') . '</span>';
            echo '<span>' . $label . ': ' . ($writable ? 'Writable' : 'Not writable - chmod 777 needed') . '</span>';
            echo '</div>';

            test("Permission: $dir", function() use ($writable) { return $writable; });
        }

        echo '</div>';

        // =================================================================
        // TEST 5: PHP Extensions
        // =================================================================
        echo '<div class="test-section">';
        echo '<h2>🔧 PHP Extensions</h2>';

        $extensions = [
            'pdo' => 'PDO',
            'pdo_mysql' => 'PDO MySQL',
            'json' => 'JSON',
            'mbstring' => 'MBString',
            'gd' => 'GD (Images)',
        ];

        foreach ($extensions as $ext => $label) {
            $loaded = extension_loaded($ext);

            echo '<div class="test-item ' . ($loaded ? 'success' : 'error') . '">';
            echo '<span class="icon">' . ($loaded ? '✓' : '✗') . '</span>';
            echo '<span>' . $label . ': ' . ($loaded ? 'Loaded' : 'Not loaded') . '</span>';
            echo '</div>';

            test("Extension: $ext", function() use ($loaded) { return $loaded; });
        }

        // PHP Version
        $php_version = PHP_VERSION;
        $version_ok = version_compare($php_version, '7.4.0', '>=');

        echo '<div class="test-item ' . ($version_ok ? 'success' : 'error') . '">';
        echo '<span class="icon">' . ($version_ok ? '✓' : '✗') . '</span>';
        echo '<span>PHP Version: ' . $php_version . ($version_ok ? ' (OK)' : ' (Need 7.4+)') . '</span>';
        echo '</div>';

        echo '</div>';

        // =================================================================
        // TEST 6: API Endpoints
        // =================================================================
        if ($db_connected) {
            echo '<div class="test-section">';
            echo '<h2>🔌 API Endpoints</h2>';

            $api_tests = [
                'categories' => 'Categories API',
                'products' => 'Products API',
            ];

            foreach ($api_tests as $endpoint => $label) {
                $api_file = __DIR__ . '/api/' . $endpoint . '.php';
                if (file_exists($api_file)) {
                    echo '<div class="test-item success">';
                    echo '<span class="icon">✓</span>';
                    echo '<span>' . $label . ' exists</span>';
                    echo '</div>';
                } else {
                    echo '<div class="test-item error">';
                    echo '<span class="icon">✗</span>';
                    echo '<span>' . $label . ' missing</span>';
                    echo '</div>';
                }
            }

            echo '</div>';
        }
        ?>

        <!-- Summary -->
        <div class="summary">
            <h2>📊 Test Summary</h2>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?= $tests_run ?></div>
                    <div class="stat-label">Total Tests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $tests_passed ?></div>
                    <div class="stat-label">Passed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count($errors) ?></div>
                    <div class="stat-label">Failed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= round(($tests_passed / $tests_run) * 100) ?>%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>

        <?php if (count($errors) > 0): ?>
        <div class="test-section">
            <h2>❌ Issues Found</h2>
            <?php foreach ($errors as $error): ?>
            <div class="test-item error">
                <span class="icon">✗</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($tests_passed === $tests_run): ?>
        <div class="test-section" style="background: #d1fae5; border-left-color: #10b981;">
            <h2 style="color: #065f46;">✅ All Tests Passed!</h2>
            <p style="color: #065f46;">Your PyraStore installation is working perfectly!</p>
            <a href="index.php" class="btn">Go to Homepage</a>
            <a href="admin/login.php" class="btn">Go to Admin</a>
        </div>
        <?php else: ?>
        <div class="test-section" style="background: #fee2e2; border-left-color: #ef4444;">
            <h2 style="color: #991b1b;">⚠️ Action Required</h2>
            <p style="color: #991b1b; margin-bottom: 15px;">Some tests failed. Please fix the issues above.</p>

            <?php if (strpos(implode('', $errors), 'UTF-8') !== false): ?>
            <div class="code">
                Step 1: Run UTF-8 fix<br>
                Open: <a href="fix-encoding.php" style="color: #0f0;">fix-encoding.php</a>
            </div>
            <?php endif; ?>

            <?php if (strpos(implode('', $errors), 'writable') !== false): ?>
            <div class="code">
                Step 2: Fix permissions<br>
                chmod 777 -R assets/uploads<br>
                chmod 777 -R config
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0; text-align: center; color: #999;">
            <p>PyraStore Test Report - Generated on <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
