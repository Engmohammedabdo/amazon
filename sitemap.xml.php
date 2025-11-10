<?php
/**
 * Dynamic Sitemap Generator for PyraStore UAE
 * Automatically generates XML sitemap with all active products
 */

// Set XML content type header
header('Content-Type: application/xml; charset=utf-8');

// Database connection
require_once __DIR__ . '/includes/db.php';

// Get current date in W3C format
$currentDate = date('c');

// Start XML output
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    <!-- Homepage -->
    <url>
        <loc>https://events.pyramedia.info/</loc>
        <lastmod><?php echo $currentDate; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Products -->
    <?php
    try {
        $db = getDB();

        // Fetch all active products
        $stmt = $db->query("
            SELECT id, title, image_url, updated_at
            FROM products
            WHERE is_active = 1
            ORDER BY id DESC
        ");

        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Format last modified date
            $lastmod = !empty($product['updated_at'])
                ? date('c', strtotime($product['updated_at']))
                : $currentDate;

            // Product URL
            $productUrl = 'https://events.pyramedia.info/product.php?id=' . $product['id'];

            echo "\n    <url>\n";
            echo "        <loc>" . htmlspecialchars($productUrl, ENT_XML1) . "</loc>\n";
            echo "        <lastmod>" . $lastmod . "</lastmod>\n";
            echo "        <changefreq>weekly</changefreq>\n";
            echo "        <priority>0.8</priority>\n";

            // Add image info (helps with Google Images)
            if (!empty($product['image_url'])) {
                echo "        <image:image>\n";
                echo "            <image:loc>" . htmlspecialchars($product['image_url'], ENT_XML1) . "</image:loc>\n";
                echo "            <image:title>" . htmlspecialchars($product['title'], ENT_XML1) . "</image:title>\n";
                echo "        </image:image>\n";
            }

            echo "    </url>";
        }

    } catch (Exception $e) {
        // Silently fail - don't break the sitemap
        error_log("Sitemap generation error: " . $e->getMessage());
    }
    ?>

</urlset>
