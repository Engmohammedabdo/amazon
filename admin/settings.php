<?php
/**
 * صفحة الإعدادات
 */

$pageTitle = 'الإعدادات';
include '_header.php';

$db = getDB();
$message = '';

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tracking') {
        updateSetting('google_analytics_id', trim($_POST['ga_id'] ?? ''));
        updateSetting('meta_pixel_id', trim($_POST['meta_pixel_id'] ?? ''));
        updateSetting('tiktok_pixel_id', trim($_POST['tiktok_pixel_id'] ?? ''));
        $message = 'تم حفظ إعدادات التتبع بنجاح';
    } elseif ($action === 'api_key') {
        $apiKey = trim($_POST['api_key'] ?? '');
        if (!empty($apiKey)) {
            updateSetting('api_key', $apiKey);
            $message = 'تم تحديث API Key بنجاح';
        }
    } elseif ($action === 'generate_key') {
        $newKey = 'pyrastore-' . bin2hex(random_bytes(16));
        updateSetting('api_key', $newKey);
        $message = 'تم توليد API Key جديد';
    }
}

$gaId = getSetting('google_analytics_id');
$metaPixelId = getSetting('meta_pixel_id');
$tiktokPixelId = getSetting('tiktok_pixel_id');
$apiKey = getSetting('api_key');
?>

<div class="page-header">
    <h1>⚙️ الإعدادات</h1>
    <p>إعدادات الموقع والتتبع والـ API</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo clean($message); ?></div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
    <button class="tab active" data-tab="tracking" onclick="switchTab('tracking')">أدوات التتبع</button>
    <button class="tab" data-tab="webhook" onclick="switchTab('webhook')">Webhook API</button>
</div>

<!-- Tracking Tab -->
<div id="tracking" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h2>🎯 أدوات التتبع والتحليلات</h2>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="action" value="tracking">

            <div class="form-group">
                <label>Google Analytics ID</label>
                <input type="text" name="ga_id" class="form-control" placeholder="G-XXXXXXXXXX" value="<?php echo clean($gaId); ?>">
                <small style="color: #666;">مثال: G-XXXXXXXXXX</small>
            </div>

            <div class="form-group">
                <label>Meta Pixel ID (Facebook)</label>
                <input type="text" name="meta_pixel_id" class="form-control" placeholder="123456789012345" value="<?php echo clean($metaPixelId); ?>">
                <small style="color: #666;">مثال: 123456789012345</small>
            </div>

            <div class="form-group">
                <label>TikTok Pixel ID</label>
                <input type="text" name="tiktok_pixel_id" class="form-control" placeholder="XXXXXXXXXXXX" value="<?php echo clean($tiktokPixelId); ?>">
                <small style="color: #666;">مثال: C1234567890ABCDEF</small>
            </div>

            <button type="submit" class="btn btn-primary">💾 حفظ الإعدادات</button>
        </form>
    </div>
</div>

<!-- Webhook Tab -->
<div id="webhook" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h2>🔗 Webhook API - استقبال المنتجات من n8n</h2>
        </div>

        <div style="background: #F9FAFB; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">📍 معلومات الاتصال</h3>

            <div style="margin-bottom: 1rem;">
                <strong>Endpoint URL:</strong>
                <code style="background: white; padding: 0.5rem; display: block; margin-top: 0.5rem; border-radius: 4px;">
                    POST <?php echo (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']; ?>/api/webhook.php
                </code>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong>Health Check:</strong>
                <code style="background: white; padding: 0.5rem; display: block; margin-top: 0.5rem; border-radius: 4px;">
                    GET <?php echo (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']; ?>/api/webhook.php?action=health
                </code>
            </div>

            <div>
                <strong>التوثيق الكامل:</strong>
                <a href="/api/webhook.php?action=docs" target="_blank" class="btn btn-sm btn-primary" style="margin-top: 0.5rem;">📖 عرض التوثيق</a>
            </div>
        </div>

        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">🔑 API Key الحالي</h3>

            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem;">
                <input type="password" id="apiKeyValue" class="form-control" value="<?php echo clean($apiKey); ?>" readonly style="flex: 1; font-family: monospace;">
                <button type="button" class="btn btn-primary" id="toggleBtn" onclick="toggleApiKey()">👁️ إظهار</button>
                <button type="button" class="btn btn-success" onclick="copyToClipboard('<?php echo clean($apiKey); ?>')">📋 نسخ</button>
            </div>
        </div>

        <div style="display: flex; gap: 1rem;">
            <form method="POST" action="" style="flex: 1;">
                <input type="hidden" name="action" value="generate_key">
                <button type="submit" class="btn btn-warning" style="width: 100%;" onclick="return confirm('توليد مفتاح جديد؟ المفتاح القديم سيتوقف عن العمل.')">
                    🔄 توليد مفتاح جديد
                </button>
            </form>

            <form method="POST" action="" style="flex: 2;">
                <div style="display: flex; gap: 0.5rem;">
                    <input type="hidden" name="action" value="api_key">
                    <input type="text" name="api_key" class="form-control" placeholder="أدخل مفتاح مخصص" style="flex: 1;">
                    <button type="submit" class="btn btn-primary">💾 حفظ المفتاح</button>
                </div>
            </form>
        </div>

        <div style="background: #FEF3C7; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; border-right: 4px solid #F59E0B;">
            <strong>⚠️ تنبيه أمني:</strong> لا تشارك API Key مع أحد. احفظه في مكان آمن. يمكنك استخدامه في n8n أو أي أداة automation.
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>
