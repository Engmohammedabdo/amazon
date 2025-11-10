<?php
/**
 * الصفحة الرئيسية - PYRASTORE
 */

// التحقق من وجود ملف الإعدادات
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: /install.php');
    exit();
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';

// جلب عدد المنتجات حسب الفئة
$productCounts = getProductCountByCategory();
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>" dir="<?php echo getLanguageDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PYRASTORE - UAE PICKS - أفضل المنتجات من أمازون الإمارات</title>
    <meta name="description" content="اكتشف أفضل المنتجات من أمازون الإمارات بأسعار مميزة وخصومات رائعة. إلكترونيات، أزياء، منزل، رياضة، جمال وأكثر">

    <!-- SEO Meta Tags -->
    <meta name="keywords" content="أمازون الإمارات, تسوق أونلاين, منتجات أمازون, خصومات, إلكترونيات, أزياء, PyraStore, Amazon UAE, online shopping">
    <meta name="author" content="PyraStore UAE">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/'); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/'); ?>">
    <meta property="og:title" content="PYRASTORE - أفضل منتجات أمازون الإمارات بأسعار مميزة">
    <meta property="og:description" content="تسوق أفضل المنتجات من أمازون الإمارات مع خصومات حصرية. إلكترونيات، أزياء، منزل، رياضة، جمال والمزيد. اكتشف عروضنا اليوم!">
    <meta property="og:image" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/assets/images/og-image.jpg'); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="PyraStore UAE">
    <meta property="og:locale" content="<?php echo getCurrentLanguage() === 'ar' ? 'ar_AE' : 'en_US'; ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/'); ?>">
    <meta name="twitter:title" content="PYRASTORE - أفضل منتجات أمازون الإمارات">
    <meta name="twitter:description" content="تسوق أفضل المنتجات من أمازون الإمارات مع خصومات حصرية. إلكترونيات، أزياء، منزل والمزيد!">
    <meta name="twitter:image" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/assets/images/og-image.jpg'); ?>">

    <!-- Additional SEO -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="PyraStore">
    <meta name="theme-color" content="#FF6B35">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <?php
    // Load tracking pixels (TikTok, Meta, Google Analytics)
    include_once __DIR__ . '/includes/tracking.php';
    ?>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div class="site-logo">
                        <h1>PYRASTORE</h1>
                    </div>
                    <p class="site-tagline"><?php echo t('site_tagline'); ?></p>
                    <p class="site-description"><?php echo t('site_description'); ?></p>
                </div>
                <?php include __DIR__ . '/includes/language_switcher.php'; ?>
            </div>
        </div>
    </header>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <!-- Search Bar -->
            <div class="search-bar">
                <div class="search-input-wrapper">
                    <input
                        type="text"
                        id="searchInput"
                        class="search-input"
                        placeholder="<?php echo t('search_placeholder'); ?>"
                        autocomplete="off">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                </div>
            </div>

            <!-- Category Filters -->
            <div class="category-filters">
                <button class="category-btn" onclick="setCategory('')">
                    <span><i class="fas fa-th"></i> <?php echo t('category_all'); ?></span>
                    <span class="category-count"><?php echo array_sum($productCounts); ?></span>
                </button>
                <button class="category-btn" data-category="electronics" onclick="setCategory('electronics')">
                    <span><i class="fas fa-mobile-alt"></i> <?php echo t('category_electronics'); ?></span>
                    <span class="category-count"><?php echo $productCounts['electronics'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="fashion" onclick="setCategory('fashion')">
                    <span><i class="fas fa-tshirt"></i> <?php echo t('category_fashion'); ?></span>
                    <span class="category-count"><?php echo $productCounts['fashion'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="home" onclick="setCategory('home')">
                    <span><i class="fas fa-home"></i> <?php echo t('category_home'); ?></span>
                    <span class="category-count"><?php echo $productCounts['home'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="sports" onclick="setCategory('sports')">
                    <span><i class="fas fa-futbol"></i> <?php echo t('category_sports'); ?></span>
                    <span class="category-count"><?php echo $productCounts['sports'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="beauty" onclick="setCategory('beauty')">
                    <span><i class="fas fa-spa"></i> <?php echo t('category_beauty'); ?></span>
                    <span class="category-count"><?php echo $productCounts['beauty'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="books" onclick="setCategory('books')">
                    <span><i class="fas fa-book"></i> <?php echo t('category_books'); ?></span>
                    <span class="category-count"><?php echo $productCounts['books'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="toys" onclick="setCategory('toys')">
                    <span><i class="fas fa-gamepad"></i> <?php echo t('category_toys'); ?></span>
                    <span class="category-count"><?php echo $productCounts['toys'] ?? 0; ?></span>
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <!-- Price Range -->
                <div class="filter-group">
                    <span class="filter-label"><?php echo t('filter_price'); ?></span>
                    <input type="number" id="minPrice" class="filter-input" placeholder="<?php echo t('filter_from'); ?>" min="0">
                    <span>-</span>
                    <input type="number" id="maxPrice" class="filter-input" placeholder="<?php echo t('filter_to'); ?>" min="0">
                </div>

                <!-- Discount Filter -->
                <div class="filter-group">
                    <span class="filter-label"><?php echo t('filter_discount'); ?></span>
                    <div class="discount-filter">
                        <button class="discount-btn" data-discount="10" onclick="setDiscount('10')">10%+</button>
                        <button class="discount-btn" data-discount="20" onclick="setDiscount('20')">20%+</button>
                        <button class="discount-btn" data-discount="30" onclick="setDiscount('30')">30%+</button>
                        <button class="discount-btn" data-discount="50" onclick="setDiscount('50')">50%+</button>
                    </div>
                </div>

                <!-- Sort By -->
                <div class="filter-group">
                    <span class="filter-label"><?php echo t('filter_sort_by'); ?></span>
                    <select id="sortBy" class="sort-select">
                        <option value="newest"><?php echo t('sort_newest'); ?></option>
                        <option value="price_asc"><?php echo t('sort_price_asc'); ?></option>
                        <option value="price_desc"><?php echo t('sort_price_desc'); ?></option>
                        <option value="discount"><?php echo t('sort_discount'); ?></option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button class="reset-btn" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> <?php echo t('filter_reset'); ?>
                </button>
            </div>
        </div>
    </section>

    <!-- Results Counter -->
    <div class="container">
        <div id="resultsCounter" class="results-counter"><?php echo t('loading'); ?></div>
    </div>

    <!-- Products Grid -->
    <main class="container">
        <div id="productsContainer" class="products-grid">
            <!-- سيتم ملؤها عبر JavaScript -->
            <div class="loading">
                <div class="spinner"></div>
                <p style="margin-top: 1rem; color: var(--muted-color);"><?php echo t('loading_products'); ?></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-links">
                <a href="#"><?php echo t('privacy_policy'); ?></a>
                <a href="#"><?php echo t('terms_of_use'); ?></a>
                <a href="/admin/login.php"><?php echo t('admin_login'); ?></a>
            </div>
            <p class="copyright">
                &copy; <?php echo date('Y'); ?> PYRASTORE - <?php echo t('all_rights_reserved'); ?>
            </p>
        </div>
    </footer>

    <!-- Pass translations to JavaScript -->
    <script>
        window.TRANSLATIONS = {
            currency: <?php echo json_encode(t('currency')); ?>,
            save: <?php echo json_encode(t('save')); ?>,
            buy_now: <?php echo json_encode(t('buy_now')); ?>,
            showing_products: <?php echo json_encode(t('showing_products')); ?>,
            no_products_found: <?php echo json_encode(t('no_products_found')); ?>,
            reset_filters: <?php echo json_encode(t('reset_filters')); ?>,
            error_loading: <?php echo json_encode(t('error_loading')); ?>,
            retry: <?php echo json_encode(t('retry')); ?>,
            amazon_original: <?php echo json_encode(t('amazon_original')); ?>,
            amazon_protection: <?php echo json_encode(t('amazon_protection')); ?>,
            amazon_support: <?php echo json_encode(t('amazon_support')); ?>,
            amazon_returns: <?php echo json_encode(t('amazon_returns')); ?>,
            categories: {
                electronics: <?php echo json_encode(getCategoryName('electronics')); ?>,
                fashion: <?php echo json_encode(getCategoryName('fashion')); ?>,
                home: <?php echo json_encode(getCategoryName('home')); ?>,
                sports: <?php echo json_encode(getCategoryName('sports')); ?>,
                beauty: <?php echo json_encode(getCategoryName('beauty')); ?>,
                books: <?php echo json_encode(getCategoryName('books')); ?>,
                toys: <?php echo json_encode(getCategoryName('toys')); ?>,
                other: <?php echo json_encode(getCategoryName('other')); ?>
            }
        };
    </script>

    <!-- Main JavaScript -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
