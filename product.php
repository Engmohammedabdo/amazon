<?php
/**
 * صفحة تفاصيل المنتج - PYRASTORE
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    header('Location: /index.php');
    exit();
}

try {
    $db = getDB();

    // جلب بيانات المنتج
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: /index.php');
        exit();
    }

    // جلب الصور الإضافية
    $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order");
    $stmt->execute([$productId]);
    $additionalImages = $stmt->fetchAll();

    // جلب المراجعات
    $stmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();

    // حساب متوسط التقييم
    $avgRating = 0;
    if (count($reviews) > 0) {
        $avgRating = array_sum(array_column($reviews, 'rating')) / count($reviews);
    }

    // جلب منتجات مشابهة
    $stmt = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
    $stmt->execute([$product['category'], $productId]);
    $similarProducts = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Product Page Error: " . $e->getMessage());
    header('Location: /index.php');
    exit();
}

$pageTitle = clean($product['title']) . ' - PYRASTORE';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo clean(truncateText($product['description'], 150)); ?>">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/product.css">

    <style>
        /* Product Page Specific Styles */
        .product-container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .product-main { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
        .product-gallery { position: sticky; top: 20px; }
        .main-image-wrapper { background: #f5f5f5; border-radius: 15px; overflow: hidden; margin-bottom: 1rem; position: relative; padding-top: 100%; }
        .main-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
        .thumbnails { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.5rem; }
        .thumbnail { cursor: pointer; border-radius: 8px; overflow: hidden; border: 2px solid transparent; transition: all 0.3s; }
        .thumbnail.active, .thumbnail:hover { border-color: var(--primary-color); }
        .thumbnail img { width: 100%; height: 80px; object-fit: cover; }
        .product-info h1 { font-size: 2rem; margin-bottom: 1rem; }
        .product-meta { display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; }
        .rating-stars { color: #FFA500; font-size: 1.2rem; }
        .price-section { background: #f9fafb; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; }
        .current-price { font-size: 2.5rem; font-weight: 700; color: var(--primary-color); }
        .buy-now-large { width: 100%; padding: 1.25rem; font-size: 1.2rem; margin-top: 1rem; }
        .video-section { margin: 2rem 0; }
        .video-wrapper { position: relative; padding-top: 56.25%; border-radius: 15px; overflow: hidden; }
        .video-wrapper.portrait { padding-top: 177.78%; }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .reviews-section, .similar-section { margin: 3rem 0; }
        .review-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: var(--shadow-md); margin-bottom: 1rem; }
        .share-buttons { display: flex; gap: 0.5rem; margin: 1.5rem 0; }
        .share-btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .share-whatsapp { background: #25D366; }
        .share-facebook { background: #1877F2; }
        .share-twitter { background: #1DA1F2; }
        .share-copy { background: #6B7280; }
        @media (max-width: 768px) {
            .product-main { grid-template-columns: 1fr; gap: 2rem; }
            .product-gallery { position: static; }
        }
    </style>

    <script>
        function buyProduct() {
            fetch('/api/track.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    event_type: 'purchase_button_click',
                    product_id: <?php echo $productId; ?>,
                    session_id: localStorage.getItem('pyra_session') || 'guest'
                })
            });
            window.open('<?php echo clean($product['affiliate_link']); ?>', '_blank');
        }

        function changeImage(src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            event.target.closest('.thumbnail').classList.add('active');
        }

        function shareWhatsApp() {
            window.open('https://wa.me/?text=' + encodeURIComponent(document.title + ' ' + window.location.href));
        }

        function shareFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href));
        }

        function shareTwitter() {
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent(document.title));
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href);
            alert('تم نسخ الرابط!');
        }
    </script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="site-logo">
                <h1><a href="/" style="color: var(--primary-color);">PYRASTORE</a></h1>
            </div>
            <p class="site-tagline">UAE PICKS</p>
        </div>
    </header>

    <div class="product-container">
        <div class="product-main">
            <!-- Gallery -->
            <div class="product-gallery">
                <div class="main-image-wrapper">
                    <img id="mainImage" src="<?php echo clean($product['image_url']); ?>" alt="<?php echo clean($product['title']); ?>" class="main-image">
                    <?php if ($product['discount_percentage']): ?>
                        <div class="discount-badge">-<?php echo $product['discount_percentage']; ?>%</div>
                    <?php endif; ?>
                </div>

                <?php if (count($additionalImages) > 0): ?>
                <div class="thumbnails">
                    <div class="thumbnail active">
                        <img src="<?php echo clean($product['image_url']); ?>" alt="صورة 1" onclick="changeImage('<?php echo clean($product['image_url']); ?>')">
                    </div>
                    <?php foreach ($additionalImages as $img): ?>
                    <div class="thumbnail">
                        <img src="<?php echo clean($img['image_url']); ?>" alt="صورة" onclick="changeImage('<?php echo clean($img['image_url']); ?>')">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-info">
                <div class="product-meta">
                    <span class="category-badge"><?php echo getCategoryIcon($product['category']); ?> <?php echo getCategoryNameAr($product['category']); ?></span>
                    <?php if (count($reviews) > 0): ?>
                        <div class="rating-stars">
                            <?php echo str_repeat('⭐', round($avgRating)); ?>
                            <span style="color: #666; font-size: 0.9rem;">(<?php echo count($reviews); ?> مراجعة)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <h1><?php echo clean($product['title']); ?></h1>

                <div class="price-section">
                    <div class="current-price">
                        <?php echo formatPrice($product['price']); ?> درهم
                        <?php if ($product['original_price']): ?>
                            <span class="product-original-price" style="font-size: 1.2rem; display: block; margin-top: 0.5rem;">
                                <?php echo formatPrice($product['original_price']); ?> درهم
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($product['original_price']): ?>
                        <div class="product-savings" style="font-size: 1.1rem; margin-top: 0.5rem;">
                            💰 وفر <?php echo formatPrice($product['original_price'] - $product['price']); ?> درهم (<?php echo $product['discount_percentage']; ?>%)
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <h3>وصف المنتج</h3>
                    <p style="line-height: 1.8; color: #555; white-space: pre-wrap;"><?php echo clean($product['description']); ?></p>
                </div>

                <button class="buy-btn buy-now-large" onclick="buyProduct()">
                    <span>🛒</span>
                    <span>اشتري الآن من أمازون</span>
                </button>

                <div class="share-buttons">
                    <button class="share-btn share-whatsapp" onclick="shareWhatsApp()">📱 واتساب</button>
                    <button class="share-btn share-facebook" onclick="shareFacebook()">📘 فيسبوك</button>
                    <button class="share-btn share-twitter" onclick="shareTwitter()">🐦 تويتر</button>
                    <button class="share-btn share-copy" onclick="copyLink()">🔗 نسخ الرابط</button>
                </div>
            </div>
        </div>

        <!-- Video Section -->
        <?php if ($product['video_url']): ?>
        <div class="video-section">
            <h2>📹 فيديو المنتج</h2>
            <div class="video-wrapper <?php echo $product['video_orientation'] === 'portrait' ? 'portrait' : ''; ?>">
                <iframe src="<?php echo convertDriveLink($product['video_url']); ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <?php if (count($reviews) > 0): ?>
        <div class="reviews-section">
            <h2>⭐ المراجعات (<?php echo count($reviews); ?>)</h2>
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <strong><?php echo clean($review['reviewer_name']); ?></strong>
                    <span class="rating-stars"><?php echo str_repeat('⭐', $review['rating']); ?></span>
                </div>
                <p style="color: #555;"><?php echo clean($review['comment']); ?></p>
                <small style="color: #999;"><?php echo formatDateArabic($review['created_at']); ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Similar Products -->
        <?php if (count($similarProducts) > 0): ?>
        <div class="similar-section">
            <h2>منتجات مشابهة</h2>
            <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
                <?php foreach ($similarProducts as $p): ?>
                <div class="product-card" onclick="window.location.href='/product.php?id=<?php echo $p['id']; ?>'">
                    <div class="product-image-wrapper">
                        <img src="<?php echo clean($p['image_url']); ?>" alt="<?php echo clean($p['title']); ?>" class="product-image">
                        <?php if ($p['discount_percentage']): ?>
                            <div class="discount-badge">-<?php echo $p['discount_percentage']; ?>%</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?php echo clean(truncateText($p['title'], 50)); ?></h3>
                        <div class="product-price"><?php echo formatPrice($p['price']); ?> درهم</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p class="copyright">&copy; <?php echo date('Y'); ?> PYRASTORE - جميع الحقوق محفوظة</p>
        </div>
    </footer>
</body>
</html>
