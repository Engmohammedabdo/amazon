<?php
/**
 * UTF-8 Encoding Fix Script
 * Run this ONCE to fix encoding issues in existing database
 */

require_once __DIR__ . '/config/config.php';

echo "<h1>UTF-8 Encoding Fix</h1>";
echo "<pre>";

try {
    $db = getDB();

    echo "Step 1: Setting database charset to utf8mb4...\n";
    $dbName = DB_NAME;
    $db->exec("ALTER DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database charset updated\n\n";

    echo "Step 2: Converting tables to utf8mb4...\n";

    $tables = [
        'products',
        'product_images',
        'categories',
        'click_tracking',
        'reviews',
        'settings',
        'admin_users'
    ];

    foreach ($tables as $table) {
        echo "Converting table: $table\n";
        $db->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "  ✓ $table converted\n";
    }

    echo "\nStep 3: Fixing category names...\n";

    // Re-insert correct category names
    $categories = [
        ['إلكترونيات', 'Electronics', 'electronics', '📱', '#3B82F6', 1],
        ['أزياء', 'Fashion', 'fashion', '👕', '#EC4899', 2],
        ['المنزل والمطبخ', 'Home & Kitchen', 'home-kitchen', '🏠', '#10B981', 3],
        ['الجمال والعناية', 'Beauty & Care', 'beauty-care', '💄', '#F59E0B', 4],
        ['رياضة ولياقة', 'Sports & Fitness', 'sports-fitness', '⚽', '#8B5CF6', 5],
        ['ألعاب وهدايا', 'Toys & Gifts', 'toys-gifts', '🎁', '#EF4444', 6],
        ['كتب وقرطاسية', 'Books & Stationery', 'books-stationery', '📚', '#6366F1', 7],
        ['سيارات وإكسسوارات', 'Automotive', 'automotive', '🚗', '#14B8A6', 8]
    ];

    $stmt = $db->prepare("
        UPDATE categories
        SET name_ar = :name_ar, name_en = :name_en, icon = :icon, color = :color, display_order = :display_order
        WHERE slug = :slug
    ");

    foreach ($categories as $cat) {
        $stmt->execute([
            ':name_ar' => $cat[0],
            ':name_en' => $cat[1],
            ':slug' => $cat[2],
            ':icon' => $cat[3],
            ':color' => $cat[4],
            ':display_order' => $cat[5]
        ]);
        echo "  ✓ Updated: {$cat[1]} ({$cat[0]})\n";
    }

    echo "\nStep 4: Verifying encoding...\n";
    $stmt = $db->query("SELECT name_ar, name_en FROM categories ORDER BY display_order ASC");
    $results = $stmt->fetchAll();

    echo "\nCategories after fix:\n";
    echo "----------------------------------------\n";
    foreach ($results as $row) {
        echo "AR: {$row['name_ar']}\n";
        echo "EN: {$row['name_en']}\n";
        echo "----------------------------------------\n";
    }

    echo "\n✅ ALL DONE!\n\n";
    echo "What to do next:\n";
    echo "1. Refresh your website homepage\n";
    echo "2. Check if category names appear correctly\n";
    echo "3. Delete this file (fix-encoding.php) for security\n\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        pre { background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #667eea; }
    </style>
</head>
</html>
