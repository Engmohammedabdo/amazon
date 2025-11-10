<?php
/**
 * UPDATE GOOGLE ANALYTICS 4 MEASUREMENT ID
 *
 * New ID: G-3TRP9PJ0GT
 * Replaces: G-GZTBRKFFGT, G-HRSW9RC061
 *
 * USAGE: Run this script from browser or CLI
 * - Browser: https://events.pyramedia.info/update_ga4_id.php
 * - CLI: php update_ga4_id.php
 */

// Security: Only allow execution from localhost or CLI
$allowed = (
    php_sapi_name() === 'cli' ||
    $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ||
    $_SERVER['REMOTE_ADDR'] === '::1'
);

if (!$allowed && !isset($_GET['force'])) {
    die('⛔ Access denied. Run from CLI or add ?force=1 to URL.');
}

// Check if config.php exists
if (!file_exists(__DIR__ . '/includes/config.php')) {
    die("❌ ERROR: config.php not found.\n\n" .
        "Please use one of these methods instead:\n" .
        "1. Admin Panel: /admin/settings.php (paste G-3TRP9PJ0GT in GA field)\n" .
        "2. SQL Script: mysql -u user -p database < update_ga4_id.sql\n" .
        "3. phpMyAdmin: Import update_ga4_id.sql\n");
}

// Load database connection
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $db = getDB();

    // Get current value
    $oldValue = getSetting('google_analytics_id');

    echo "🔍 Current GA4 ID: " . ($oldValue ?: '(empty)') . "\n";
    echo "🆕 New GA4 ID: G-3TRP9PJ0GT\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    // Update to new ID
    $stmt = $db->prepare(
        "INSERT INTO site_settings (setting_key, setting_value)
         VALUES ('google_analytics_id', 'G-3TRP9PJ0GT')
         ON DUPLICATE KEY UPDATE setting_value = 'G-3TRP9PJ0GT'"
    );

    $stmt->execute();

    // Verify update
    $newValue = getSetting('google_analytics_id');

    if ($newValue === 'G-3TRP9PJ0GT') {
        echo "✅ SUCCESS! GA4 ID updated successfully\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📊 Verification:\n";
        echo "   Old: " . ($oldValue ?: '(empty)') . "\n";
        echo "   New: $newValue\n\n";
        echo "🎯 Next Steps:\n";
        echo "1. Test tracking: https://events.pyramedia.info/?utm_source=test&utm_medium=cpc\n";
        echo "2. Check console for: ✅ GA4 configured with UTM (CORRECT FORMAT)\n";
        echo "3. Open GA4 Real-Time report: https://analytics.google.com/\n";
        echo "4. Verify traffic shows 'test / cpc' instead of '(direct)'\n\n";
        echo "⚠️  Important: Delete this file after successful update!\n";
        echo "   rm update_ga4_id.php\n";
    } else {
        echo "❌ ERROR: Update failed. Current value: $newValue\n";
    }

} catch (PDOException $e) {
    die("❌ Database Error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>
