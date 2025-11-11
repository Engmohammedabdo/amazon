<?php
/**
 * صفحة تفاصيل المنتج - PYRASTORE
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';

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
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo getLanguageDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($product['description']), 0, 155)); ?>">

    <!-- SEO Meta Tags -->
    <meta name="keywords" content="<?php echo htmlspecialchars($product['title'] . ', Amazon UAE, ' . getCategoryName($product['category']) . ', PyraStore'); ?>">
    <meta name="author" content="PyraStore UAE">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($product['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($product['description']), 0, 200)); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($product['image_url']); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="PyraStore UAE">
    <meta property="og:locale" content="<?php echo getCurrentLanguage() === 'ar' ? 'ar_AE' : 'en_US'; ?>">

    <!-- Product Price for Facebook/Pinterest -->
    <meta property="product:price:amount" content="<?php echo $product['price']; ?>">
    <meta property="product:price:currency" content="AED">
    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
    <meta property="product:sale_price:amount" content="<?php echo $product['price']; ?>">
    <meta property="product:original_price:amount" content="<?php echo $product['original_price']; ?>">
    <?php endif; ?>
    <meta property="product:availability" content="in stock">
    <meta property="product:condition" content="new">
    <meta property="product:retailer_item_id" content="<?php echo $product['id']; ?>">
    <meta property="product:brand" content="Amazon UAE">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($product['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($product['description']), 0, 200)); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($product['image_url']); ?>">
    <meta name="twitter:label1" content="Price">
    <meta name="twitter:data1" content="<?php echo formatPrice($product['price']); ?> AED">
    <?php if ($product['discount_percentage']): ?>
    <meta name="twitter:label2" content="Save">
    <meta name="twitter:data2" content="<?php echo $product['discount_percentage']; ?>%">
    <?php endif; ?>

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon/apple-touch-icon.png">
    <link rel="shortcut icon" href="/assets/images/favicon/favicon-32x32.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/product.css">

    <?php
    // Load tracking pixels (TikTok, Meta, Google Analytics)
    include_once __DIR__ . '/includes/tracking.php';
    ?>

    <!-- UTM Parameter Tracker -->
    <script src="/assets/js/utm-tracker.js"></script>

    <script>
        // Track purchase button click and open affiliate link
        function buyProduct() {
            // Track checkout intent across all platforms
            trackCheckoutIntent(
                '<?php echo $productId; ?>',
                '<?php echo addslashes($product['title']); ?>',
                <?php echo $product['price']; ?>
            );

            // Internal tracking
            fetch('/api/track.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    event_type: 'purchase_button_click',
                    product_id: <?php echo $productId; ?>,
                    session_id: localStorage.getItem('pyra_session') || 'guest'
                })
            });

            // Open affiliate link
            window.open('<?php echo clean($product['affiliate_link']); ?>', '_blank');
        }

        // Fire ViewContent event on page load
        document.addEventListener('DOMContentLoaded', function() {
            trackProductView(
                '<?php echo $productId; ?>',
                '<?php echo addslashes($product['title']); ?>',
                <?php echo $product['price']; ?>
            );
        });

        // Share functions
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
        }
    </script>

    <!-- Schema.org Structured Data for SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Product",
      "name": <?php echo json_encode($product['title']); ?>,
      "image": [
        <?php echo json_encode($product['image_url']); ?>
        <?php foreach ($additionalImages as $img): ?>
        ,<?php echo json_encode($img['image_url']); ?>
        <?php endforeach; ?>
      ],
      "description": <?php echo json_encode(strip_tags($product['description'])); ?>,
      "sku": <?php echo json_encode('PYRA-' . $product['id']); ?>,
      "brand": {
        "@type": "Brand",
        "name": "Amazon UAE"
      },
      "offers": {
        "@type": "Offer",
        "url": <?php echo json_encode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>,
        "priceCurrency": "AED",
        "price": <?php echo json_encode($product['price']); ?>,
        "priceValidUntil": <?php echo json_encode(date('Y-m-d', strtotime('+30 days'))); ?>,
        "availability": "https://schema.org/InStock",
        "itemCondition": "https://schema.org/NewCondition",
        "seller": {
          "@type": "Organization",
          "name": "PyraStore UAE"
        }
      }
      <?php if (!empty($product['star_rating']) && !empty($product['sales_volume'])): ?>
      ,"aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": <?php echo json_encode($product['star_rating']); ?>,
        "reviewCount": <?php echo json_encode($product['sales_volume']); ?>,
        "bestRating": "5",
        "worstRating": "1"
      }
      <?php endif; ?>
    }
    </script>

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": <?php echo json_encode('https://' . $_SERVER['HTTP_HOST'] . '/'); ?>
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": <?php echo json_encode(getCategoryName($product['category'])); ?>,
          "item": <?php echo json_encode('https://' . $_SERVER['HTTP_HOST'] . '/?category=' . $product['category']); ?>
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": <?php echo json_encode($product['title']); ?>
        }
      ]
    }
    </script>

    <!-- Organization Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "PyraStore UAE",
      "url": <?php echo json_encode('https://' . $_SERVER['HTTP_HOST']); ?>,
      "logo": <?php echo json_encode('https://' . $_SERVER['HTTP_HOST'] . '/assets/images/logo.png'); ?>,
      "sameAs": [
        "https://www.tiktok.com/@pyrastore"
      ]
    }
    </script>
</head>
<body>
    <?php outputGTMBodyNoscript(); ?>

    <header class="site-header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div class="site-logo">
                        <h1><a href="/" style="color: var(--primary-color);">PYRASTORE</a></h1>
                    </div>
                    <p class="site-tagline"><?php echo t('site_tagline'); ?></p>
                </div>
                <?php include __DIR__ . '/includes/language_switcher.php'; ?>
            </div>
        </div>
    </header>

    <div class="product-detail">
        <div class="product-main">
            <!-- Gallery -->
            <div class="product-gallery">
                <div class="main-image-container">
                    <img id="mainImage" src="<?php echo clean($product['image_url']); ?>" alt="<?php echo clean($product['title']); ?>" class="main-image">
                    <div class="zoom-badge">
                        <i class="fas fa-search-plus"></i>
                        <?php echo t('click_to_zoom'); ?>
                    </div>
                    <?php if ($product['discount_percentage']): ?>
                        <div class="discount-badge">-<?php echo $product['discount_percentage']; ?>%</div>
                    <?php endif; ?>
                </div>

                <?php if (count($additionalImages) > 0): ?>
                <div class="thumbnail-list">
                    <div class="thumbnail-item active">
                        <img src="<?php echo clean($product['image_url']); ?>" alt="<?php echo t('image_alt'); ?> 1">
                    </div>
                    <?php foreach ($additionalImages as $img): ?>
                    <div class="thumbnail-item">
                        <img src="<?php echo clean($img['image_url']); ?>" alt="<?php echo t('image_alt'); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-info">
                <div class="product-header">
                    <div class="product-category-badge">
                        <i class="fas fa-tag"></i>
                        <?php echo getCategoryName($product['category']); ?>
                    </div>

                    <h1 class="product-detail-title"><?php echo clean($product['title']); ?></h1>

                    <!-- Trust Signals: Star Rating & Sales Volume -->
                    <?php if ($product['star_rating'] || $product['sales_volume']): ?>
                        <div class="trust-signals" style="display: flex; align-items: center; gap: 1rem; margin: 0.75rem 0; flex-wrap: wrap;">
                            <?php if ($product['star_rating']): ?>
                                <div class="amazon-rating" style="display: flex; align-items: center; gap: 0.5rem; background: #FFF3CD; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid #FFE69C;">
                                    <div class="rating-stars" style="color: #FFA500; font-size: 1rem;">
                                        <?php
                                        $rating = floatval($product['star_rating']);
                                        $fullStars = floor($rating);
                                        $hasHalfStar = ($rating - $fullStars) >= 0.5;

                                        for ($i = 0; $i < 5; $i++) {
                                            if ($i < $fullStars) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i == $fullStars && $hasHalfStar) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span style="font-weight: 600; color: #856404;">
                                        <?php echo number_format($product['star_rating'], 1); ?>/5
                                    </span>
                                    <span style="color: #856404; font-size: 0.9rem;">
                                        <?php echo t('amazon_rating'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($product['sales_volume'] && $product['sales_volume'] > 0): ?>
                                <div class="sales-badge" style="display: flex; align-items: center; gap: 0.5rem; background: #FFE5E5; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid #FFCCCC;">
                                    <span style="font-size: 1.2rem;">🔥</span>
                                    <span style="font-weight: 600; color: #C92A2A;">
                                        <?php echo number_format($product['sales_volume']); ?>+
                                    </span>
                                    <span style="color: #C92A2A; font-size: 0.9rem;">
                                        <?php echo t('bought_recently'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($reviews) > 0): ?>
                        <div class="review-rating">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i < round($avgRating) ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                            <span style="color: #666; font-size: 0.9rem; margin-right: 0.5rem;">(<?php echo count($reviews); ?> <?php echo t('review_count'); ?>)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="price-section">
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <div class="original-price">
                            <?php echo formatPrice($product['original_price']); ?> <?php echo t('currency'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="current-price">
                        <?php echo formatPrice($product['price']); ?> <?php echo t('currency'); ?>
                    </div>

                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <div class="discount-amount">
                            <i class="fas fa-badge-percent"></i>
                            <?php echo t('save'); ?> <?php echo $product['discount_percentage'] ?? calculateDiscount($product['original_price'], $product['price']); ?>%
                        </div>
                        <div class="savings-highlight">
                            <i class="fas fa-piggy-bank"></i>
                            <?php echo t('savings'); ?> <?php echo formatPrice($product['original_price'] - $product['price']); ?> <?php echo t('currency'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="cta-section">
                    <button class="buy-now-btn" onclick="buyProduct()">
                        <i class="fas fa-shopping-cart"></i>
                        <span><?php echo t('buy_now_amazon'); ?></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="description-section">
            <h2 class="section-title"><?php echo t('product_description'); ?></h2>
            <div class="product-description">
                <?php
                // Allow HTML but sanitize for security
                $description = $product['description'];
                // Remove dangerous tags but keep formatting
                $allowed_tags = '<h1><h2><h3><h4><h5><h6><p><strong><b><em><i><ul><ol><li><br><a><blockquote><code>';
                echo strip_tags($description, $allowed_tags);
                ?>
            </div>
        </div>

        <!-- Video Section -->
        <?php if ($product['video_url']): ?>
        <div class="description-section">
            <h2 class="section-title">
                <i class="fas fa-video"></i> <?php echo t('product_video'); ?>
            </h2>
            <div class="video-container <?php echo $product['video_orientation'] === 'portrait' ? 'portrait' : ''; ?>">
                <iframe src="<?php echo convertDriveLink($product['video_url']); ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <?php if (count($reviews) > 0): ?>
        <div class="reviews-section">
            <h2 class="section-title">
                <i class="fas fa-star"></i> <?php echo t('reviews'); ?> (<?php echo count($reviews); ?>)
            </h2>
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-name">
                        <i class="fas fa-user-circle"></i>
                        <?php echo clean($review['reviewer_name']); ?>
                    </div>
                    <div class="review-rating">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i < $review['rating'] ? '' : ' far'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <p class="review-text"><?php echo clean($review['comment']); ?></p>
                <div class="review-date">
                    <i class="far fa-calendar"></i>
                    <?php echo formatDateArabic($review['created_at']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Share Section (Moved to bottom for better conversion) -->
        <div class="share-section">
            <h3 class="share-title">
                <i class="fas fa-share-alt"></i>
                <?php echo t('share_product'); ?>
            </h3>
            <div class="share-buttons">
                <button class="share-btn whatsapp" onclick="shareWhatsApp()">
                    <i class="fab fa-whatsapp"></i> <?php echo t('whatsapp'); ?>
                </button>
                <button class="share-btn facebook" onclick="shareFacebook()">
                    <i class="fab fa-facebook-f"></i> <?php echo t('facebook'); ?>
                </button>
                <button class="share-btn twitter" onclick="shareTwitter()">
                    <i class="fab fa-twitter"></i> <?php echo t('twitter'); ?>
                </button>
                <button class="share-btn copy" onclick="copyLink()">
                    <i class="fas fa-link"></i> <?php echo t('copy'); ?>
                </button>
            </div>
        </div>

        <!-- Similar Products -->
        <?php if (count($similarProducts) > 0): ?>
        <div class="similar-products-section">
            <h2 class="section-title">
                <i class="fas fa-th-large"></i> <?php echo t('similar_products'); ?>
            </h2>
            <div class="similar-products-grid">
                <?php foreach ($similarProducts as $p): ?>
                <div class="product-card" onclick="window.location.href='/product.php?id=<?php echo $p['id']; ?>'">
                    <div class="product-image-wrapper">
                        <img src="<?php echo clean($p['image_url']); ?>" alt="<?php echo clean($p['title']); ?>" class="product-image" loading="lazy">
                        <div class="category-badge">
                            <i class="fas fa-tag"></i>
                            <?php echo getCategoryName($p['category']); ?>
                        </div>
                        <?php if ($p['discount_percentage']): ?>
                            <div class="discount-badge">-<?php echo $p['discount_percentage']; ?>%</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?php echo clean(truncateText($p['title'], 50)); ?></h3>
                        <div class="product-pricing">
                            <div class="product-price">
                                <?php echo formatPrice($p['price']); ?> <?php echo t('currency'); ?>
                            </div>
                        </div>
                        <button class="buy-btn" onclick="event.stopPropagation(); window.open('<?php echo clean($p['affiliate_link']); ?>', '_blank');">
                            <i class="fas fa-shopping-cart"></i>
                            <span><?php echo t('buy_now'); ?></span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <div class="container">
            <p class="copyright">&copy; <?php echo date('Y'); ?> PYRASTORE - <?php echo t('all_rights_reserved'); ?></p>
        </div>
    </footer>

    <!-- Enhanced Tracking System -->
    <script src="/assets/js/tracking.js"></script>

    <!-- Main JavaScript -->
    <script src="/assets/js/main.js"></script>
    <!-- Product Page JavaScript -->
    <script src="/assets/js/product.js"></script>

    <!-- Sticky CTA Bar (Mobile Only) - Product Page -->
    <div class="sticky-cta-bar" id="stickyCTAProduct">
        <div class="sticky-cta-price">
            <div class="price"><?php echo formatPrice($product['price']); ?> <?php echo t('currency'); ?></div>
            <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                <div class="original"><?php echo formatPrice($product['original_price']); ?> <?php echo t('currency'); ?></div>
            <?php endif; ?>
        </div>
        <button class="sticky-cta-btn" onclick="buyProduct()">
            <i class="fas fa-shopping-cart"></i>
            <span><?php echo t('buy_now'); ?></span>
        </button>
    </div>
</body>
</html>
