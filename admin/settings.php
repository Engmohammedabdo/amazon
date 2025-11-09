<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            $stmt = $db->prepare("
                UPDATE settings SET setting_value = :value WHERE setting_key = :key
            ");
            $stmt->execute([
                ':value' => $value,
                ':key' => $key
            ]);
        }
    }

    header('Location: settings.php?success=Settings saved successfully');
    exit;
}

// Get current settings
$stmt = $db->query("SELECT * FROM settings ORDER BY setting_key ASC");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$pageTitle = 'Settings';
include 'header.php';
?>

<style>
.settings-container {
    max-width: 800px;
}

.settings-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
}

.settings-section h2 {
    font-size: 20px;
    margin-bottom: 20px;
    color: var(--dark);
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border);
}

.help-text {
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

.save-button {
    position: sticky;
    bottom: 20px;
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    transition: all 0.3s;
}

.save-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.4);
}
</style>

<?php if (isset($_GET['success'])): ?>
<div class="success-message">
    ✅ <?= htmlspecialchars($_GET['success']) ?>
</div>
<?php endif; ?>

<h1 style="margin-bottom: 30px;">⚙️ Settings</h1>

<div class="settings-container">
    <form method="POST">
        <!-- Site Settings -->
        <div class="settings-section">
            <h2>🌐 Site Information</h2>

            <div class="form-group">
                <label>Site Name (Arabic)</label>
                <input type="text" name="site_name_ar" value="<?= htmlspecialchars($settings['site_name_ar'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Site Name (English)</label>
                <input type="text" name="site_name_en" value="<?= htmlspecialchars($settings['site_name_en'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Contact Email</label>
                <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>WhatsApp Number</label>
                <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>" placeholder="+971 XX XXX XXXX">
            </div>
        </div>

        <!-- Amazon Affiliate Settings -->
        <div class="settings-section">
            <h2>🛒 Amazon Affiliate Settings</h2>

            <div class="form-group">
                <label>Amazon Affiliate ID</label>
                <input type="text" name="affiliate_id" value="<?= htmlspecialchars($settings['affiliate_id'] ?? '') ?>" placeholder="yourstore-21">
                <p class="help-text">Your Amazon Associates tracking ID (e.g., pyrastore-21)</p>
            </div>

            <div class="form-group">
                <label>Amazon Domain</label>
                <input type="text" name="amazon_domain" value="<?= htmlspecialchars($settings['amazon_domain'] ?? '') ?>" placeholder="amazon.ae">
                <p class="help-text">The Amazon domain you're promoting (e.g., amazon.ae, amazon.com)</p>
            </div>
        </div>

        <!-- Tracking & Analytics -->
        <div class="settings-section">
            <h2>📊 Tracking & Analytics</h2>

            <div class="form-group">
                <label>Google Analytics ID</label>
                <input type="text" name="google_analytics_id" value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                <p class="help-text">Your Google Analytics 4 measurement ID</p>
            </div>

            <div class="form-group">
                <label>Meta Pixel ID</label>
                <input type="text" name="meta_pixel_id" value="<?= htmlspecialchars($settings['meta_pixel_id'] ?? '') ?>" placeholder="123456789012345">
                <p class="help-text">Your Facebook/Meta Pixel ID for tracking conversions</p>
            </div>

            <div class="form-group">
                <label>TikTok Pixel ID</label>
                <input type="text" name="tiktok_pixel_id" value="<?= htmlspecialchars($settings['tiktok_pixel_id'] ?? '') ?>" placeholder="ABCDEFGHIJK1234567890">
                <p class="help-text">Your TikTok Pixel ID for ad tracking</p>
            </div>
        </div>

        <!-- Display Settings -->
        <div class="settings-section">
            <h2>🎨 Display Settings</h2>

            <div class="form-group">
                <label>Products Per Page</label>
                <input type="number" name="products_per_page" value="<?= htmlspecialchars($settings['products_per_page'] ?? '12') ?>" min="1" max="100">
                <p class="help-text">Number of products to display per page</p>
            </div>

            <div class="form-group">
                <label>Featured Products Count</label>
                <input type="number" name="featured_products_count" value="<?= htmlspecialchars($settings['featured_products_count'] ?? '8') ?>" min="1" max="50">
                <p class="help-text">Number of featured products to highlight</p>
            </div>

            <div class="form-group">
                <label>Default Language</label>
                <select name="language_default">
                    <option value="ar" <?= ($settings['language_default'] ?? 'ar') === 'ar' ? 'selected' : '' ?>>العربية (Arabic)</option>
                    <option value="en" <?= ($settings['language_default'] ?? 'ar') === 'en' ? 'selected' : '' ?>>English</option>
                </select>
            </div>

            <div class="form-group">
                <label>Currency</label>
                <input type="text" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'AED') ?>" placeholder="AED">
            </div>
        </div>

        <!-- Reviews Settings -->
        <div class="settings-section">
            <h2>⭐ Reviews Settings</h2>

            <div class="form-group">
                <label>Enable Reviews</label>
                <select name="enable_reviews">
                    <option value="1" <?= ($settings['enable_reviews'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= ($settings['enable_reviews'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                </select>
            </div>

            <div class="form-group">
                <label>Auto-Approve Reviews</label>
                <select name="auto_approve_reviews">
                    <option value="1" <?= ($settings['auto_approve_reviews'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= ($settings['auto_approve_reviews'] ?? '0') === '0' ? 'selected' : '' ?>>No (Manual Approval)</option>
                </select>
            </div>
        </div>

        <button type="submit" name="submit" class="save-button">
            💾 Save All Settings
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>
