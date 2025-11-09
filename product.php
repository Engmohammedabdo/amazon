<?php
require_once __DIR__ . '/config/config.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productId) {
    header('Location: /');
    exit;
}

// Fetch product data
$db = getDB();
$stmt = $db->prepare("
    SELECT p.*
    FROM products p
    WHERE p.id = :id AND p.is_active = 1
");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /');
    exit;
}

// Get product images
$stmt = $db->prepare("
    SELECT image_url
    FROM product_images
    WHERE product_id = :id
    ORDER BY is_primary DESC, display_order ASC
");
$stmt->execute([':id' => $productId]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get reviews
$stmt = $db->prepare("
    SELECT *
    FROM reviews
    WHERE product_id = :id AND is_approved = 1
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute([':id' => $productId]);
$reviews = $stmt->fetchAll();

// Get similar products
$stmt = $db->prepare("
    SELECT
        p.id,
        p.title_ar,
        p.title_en,
        p.price,
        p.original_price,
        p.discount_percentage,
        p.rating,
        (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    WHERE p.category = :category AND p.id != :id AND p.is_active = 1
    ORDER BY RAND()
    LIMIT 4
");
$stmt->execute([':category' => $product['category'], ':id' => $productId]);
$similarProducts = $stmt->fetchAll();

$lang = getCurrentLang();
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
$title = $lang === 'ar' ? $product['title_ar'] : $product['title_en'];
$description = $lang === 'ar' ? $product['description_ar'] : $product['description_en'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - <?= t('متجر بيرا', 'PyraStore') ?></title>

    <meta name="description" content="<?= htmlspecialchars($description) ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <?php if (!empty($images)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($images[0]) ?>">
    <?php endif; ?>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/product.css">

    <!-- Tracking Pixels -->
    <?php
    // Include same tracking pixels as index.php
    if ($gaId = getSetting('google_analytics_id')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaId) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($gaId) ?>');
    </script>
    <?php endif; ?>
</head>
<body>

<!-- Simple Header for Product Page -->
<header class="header">
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <div class="logo-icon">🛍️</div>
                    <div class="logo-text">
                        <h1><?= t('متجر بيرا', 'PyraStore') ?></h1>
                        <p><?= t('أفضل العروض من أمازون الإمارات', 'Best Deals from Amazon UAE') ?></p>
                    </div>
                </a>

                <div class="lang-switcher">
                    <button class="lang-btn <?= $lang === 'ar' ? 'active' : '' ?>" onclick="setLanguage('ar')">العربية</button>
                    <button class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>" onclick="setLanguage('en')">English</button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Product Details -->
<section class="product-details">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/"><?= t('الرئيسية', 'Home') ?></a>
            <span>/</span>
            <span><?= t(getCategoryName($product['category'], 'ar'), getCategoryName($product['category'], 'en')) ?></span>
            <span>/</span>
            <span><?= htmlspecialchars($title) ?></span>
        </div>

        <div class="product-layout">
            <!-- Product Images Gallery -->
            <div class="product-gallery">
                <div class="main-image">
                    <img id="mainImage" src="<?= !empty($images) ? htmlspecialchars($images[0]) : '/assets/images/placeholder.png' ?>" alt="<?= htmlspecialchars($title) ?>">

                    <?php if ($product['discount_percentage'] > 0): ?>
                    <div class="discount-badge-large">
                        -<?= $product['discount_percentage'] ?>%
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (count($images) > 1): ?>
                <div class="thumbnail-images">
                    <?php foreach ($images as $index => $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>"
                         alt="<?= htmlspecialchars($title) ?>"
                         class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                         onclick="changeMainImage('<?= htmlspecialchars($image, ENT_QUOTES) ?>', this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Information -->
            <div class="product-main-info">
                <div class="product-category-badge">
                    <?= t(getCategoryName($product['category'], 'ar'), getCategoryName($product['category'], 'en')) ?>
                </div>

                <h1 class="product-title-large"><?= htmlspecialchars($title) ?></h1>

                <?php if ($product['rating'] > 0): ?>
                <div class="product-rating-large">
                    <?= getStarRatingHTML($product['rating']) ?>
                    <span class="rating-value"><?= $product['rating'] ?></span>
                    <span class="reviews-link">(<?= count($reviews) ?> <?= t('تقييم', 'reviews') ?>)</span>
                </div>
                <?php endif; ?>

                <div class="product-price-large">
                    <?php if ($product['original_price'] > $product['price']): ?>
                    <div class="price-comparison">
                        <span class="original-price-large"><?= formatPrice($product['original_price']) ?></span>
                        <span class="discount-percentage-large">-<?= $product['discount_percentage'] ?>%</span>
                    </div>
                    <?php endif; ?>
                    <div class="current-price-large"><?= formatPrice($product['price']) ?></div>
                </div>

                <div class="product-description">
                    <h3><?= t('وصف المنتج', 'Product Description') ?></h3>
                    <p><?= nl2br(htmlspecialchars($description)) ?></p>
                </div>

                <div class="product-features">
                    <div class="feature-item">
                        <span class="feature-icon">✅</span>
                        <span><?= t('منتج أصلي 100%', '100% Authentic') ?></span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🚚</span>
                        <span><?= t('شحن سريع من أمازون', 'Fast Amazon Shipping') ?></span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">↩️</span>
                        <span><?= t('إرجاع مجاني', 'Free Returns') ?></span>
                    </div>
                </div>

                <div class="product-cta">
                    <button class="btn-buy-large" onclick="trackAndRedirect(<?= $product['id'] ?>, '<?= htmlspecialchars($product['affiliate_link'], ENT_QUOTES) ?>')">
                        <span class="btn-icon-large">🛒</span>
                        <span><?= t('اشتري الآن من أمازون', 'Buy Now on Amazon') ?></span>
                    </button>
                </div>

                <div class="social-share">
                    <h4><?= t('شارك المنتج', 'Share Product') ?></h4>
                    <div class="share-buttons">
                        <button class="share-btn facebook" onclick="shareOnFacebook()">
                            <span>f</span> Facebook
                        </button>
                        <button class="share-btn twitter" onclick="shareOnTwitter()">
                            <span>𝕏</span> Twitter
                        </button>
                        <button class="share-btn whatsapp" onclick="shareOnWhatsApp()">
                            <span>📱</span> WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <?php if (!empty($reviews)): ?>
        <div class="reviews-section">
            <h2><?= t('آراء العملاء', 'Customer Reviews') ?></h2>

            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">👤</div>
                            <div>
                                <div class="reviewer-name"><?= htmlspecialchars($review['customer_name']) ?></div>
                                <div class="review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></div>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?= getStarRatingHTML($review['rating']) ?>
                        </div>
                    </div>
                    <div class="review-text">
                        <?= htmlspecialchars($review['review_text']) ?>
                    </div>
                    <?php if ($review['is_verified']): ?>
                    <div class="verified-badge">✓ <?= t('مشتري موثق', 'Verified Purchase') ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Similar Products -->
        <?php if (!empty($similarProducts)): ?>
        <div class="similar-products">
            <h2><?= t('منتجات مشابهة', 'Similar Products') ?></h2>

            <div class="products-grid">
                <?php foreach ($similarProducts as $similarProduct): ?>
                    <?php
                    $similarTitle = $lang === 'ar' ? $similarProduct['title_ar'] : $similarProduct['title_en'];
                    ?>
                    <div class="product-card" onclick="window.location.href='/product.php?id=<?= $similarProduct['id'] ?>'">
                        <?php if ($similarProduct['discount_percentage'] > 0): ?>
                        <div class="product-badge discount-badge">-<?= $similarProduct['discount_percentage'] ?>%</div>
                        <?php endif; ?>

                        <div class="product-image">
                            <img src="<?= $similarProduct['primary_image'] ?? '/assets/images/placeholder.png' ?>" alt="<?= htmlspecialchars($similarTitle) ?>">
                        </div>

                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($similarTitle) ?></h3>

                            <div class="product-price-section">
                                <?php if ($similarProduct['original_price'] > $similarProduct['price']): ?>
                                <span class="original-price"><?= formatPrice($similarProduct['original_price']) ?></span>
                                <?php endif; ?>
                                <span class="current-price"><?= formatPrice($similarProduct['price']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= t('متجر بيرا', 'PyraStore') ?>. <?= t('جميع الحقوق محفوظة', 'All rights reserved') ?>.</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="/assets/js/tracking.js"></script>
<script>
// Track product view on page load
tracker.trackProductView(<?= $product['id'] ?>);

// Change main image
function changeMainImage(imageUrl, thumbnail) {
    document.getElementById('mainImage').src = imageUrl;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Get star rating HTML
function getStarRatingHTML(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    let html = '<div class="stars">';

    for (let i = 0; i < fullStars; i++) {
        html += '<span class="star filled">⭐</span>';
    }
    if (hasHalfStar && fullStars < 5) {
        html += '<span class="star half">⭐</span>';
    }
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        html += '<span class="star empty">☆</span>';
    }
    html += '</div>';
    return html;
}

// Social sharing
function shareOnFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
}

function shareOnTwitter() {
    const text = '<?= htmlspecialchars($title) ?>';
    window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(window.location.href), '_blank');
}

function shareOnWhatsApp() {
    const text = '<?= htmlspecialchars($title) ?> - ' + window.location.href;
    window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
}

function setLanguage(lang) {
    localStorage.setItem('pyra_lang', lang);
    location.reload();
}

// Helper functions
<?php
// Output helper functions as JavaScript
?>
function getCategoryName(slug, lang) {
    const categories = {
        'electronics': { ar: 'إلكترونيات', en: 'Electronics' },
        'fashion': { ar: 'أزياء', en: 'Fashion' },
        'home-kitchen': { ar: 'المنزل والمطبخ', en: 'Home & Kitchen' },
        'beauty-care': { ar: 'الجمال والعناية', en: 'Beauty & Care' },
        'sports-fitness': { ar: 'رياضة ولياقة', en: 'Sports & Fitness' },
        'toys-gifts': { ar: 'ألعاب وهدايا', en: 'Toys & Gifts' },
        'books-stationery': { ar: 'كتب وقرطاسية', en: 'Books & Stationery' },
        'automotive': { ar: 'سيارات وإكسسوارات', en: 'Automotive' }
    };
    return categories[slug] ? categories[slug][lang] : slug;
}

function formatPrice(price) {
    const lang = '<?= $lang ?>';
    if (lang === 'ar') {
        return parseFloat(price).toFixed(2) + ' درهم';
    } else {
        return 'AED ' + parseFloat(price).toFixed(2);
    }
}
</script>

<?php
// PHP helper functions
function getStarRatingHTML($rating) {
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $html = '<div class="stars">';

    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<span class="star filled">⭐</span>';
    }

    if ($hasHalfStar && $fullStars < 5) {
        $html .= '<span class="star half">⭐</span>';
    }

    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<span class="star empty">☆</span>';
    }

    $html .= '</div>';
    return $html;
}

function getCategoryName($slug, $lang) {
    $categories = [
        'electronics' => ['ar' => 'إلكترونيات', 'en' => 'Electronics'],
        'fashion' => ['ar' => 'أزياء', 'en' => 'Fashion'],
        'home-kitchen' => ['ar' => 'المنزل والمطبخ', 'en' => 'Home & Kitchen'],
        'beauty-care' => ['ar' => 'الجمال والعناية', 'en' => 'Beauty & Care'],
        'sports-fitness' => ['ar' => 'رياضة ولياقة', 'en' => 'Sports & Fitness'],
        'toys-gifts' => ['ar' => 'ألعاب وهدايا', 'en' => 'Toys & Gifts'],
        'books-stationery' => ['ar' => 'كتب وقرطاسية', 'en' => 'Books & Stationery'],
        'automotive' => ['ar' => 'سيارات وإكسسوارات', 'en' => 'Automotive']
    ];

    return $categories[$slug][$lang] ?? $slug;
}

function formatPrice($price) {
    global $lang;
    if ($lang === 'ar') {
        return number_format($price, 2) . ' درهم';
    } else {
        return 'AED ' . number_format($price, 2);
    }
}
?>

</body>
</html>
