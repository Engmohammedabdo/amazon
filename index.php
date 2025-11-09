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

// جلب عدد المنتجات حسب الفئة
$productCounts = getProductCountByCategory();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PYRASTORE - UAE PICKS - أفضل المنتجات من أمازون الإمارات</title>
    <meta name="description" content="اكتشف أفضل المنتجات من أمازون الإمارات بأسعار مميزة وخصومات رائعة">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <?php
    // تحميل أكواد التتبع
    $gaId = getSetting('google_analytics_id');
    $metaPixelId = getSetting('meta_pixel_id');
    $tiktokPixelId = getSetting('tiktok_pixel_id');

    // Google Analytics
    if (!empty($gaId)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo clean($gaId); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo clean($gaId); ?>');
    </script>
    <?php endif; ?>

    <?php // Meta Pixel
    if (!empty($metaPixelId)): ?>
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo clean($metaPixelId); ?>');
        fbq('track', 'PageView');
    </script>
    <?php endif; ?>

    <?php // TikTok Pixel
    if (!empty($tiktokPixelId)): ?>
    <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
          ttq.load('<?php echo clean($tiktokPixelId); ?>');
          ttq.page();
        }(window, document, 'ttq');
    </script>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="site-logo">
                <h1>PYRASTORE</h1>
            </div>
            <p class="site-tagline">UAE PICKS</p>
            <p class="site-description">أفضل المنتجات من أمازون الإمارات بأسعار مميزة</p>
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
                        placeholder="ابحث عن منتج..."
                        autocomplete="off">
                    <span class="search-icon">🔍</span>
                </div>
            </div>

            <!-- Category Filters -->
            <div class="category-filters">
                <button class="category-btn" onclick="setCategory('')">
                    <span>الكل</span>
                    <span class="category-count"><?php echo array_sum($productCounts); ?></span>
                </button>
                <button class="category-btn" data-category="electronics" onclick="setCategory('electronics')">
                    <span>📱 إلكترونيات</span>
                    <span class="category-count"><?php echo $productCounts['electronics'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="fashion" onclick="setCategory('fashion')">
                    <span>👔 أزياء</span>
                    <span class="category-count"><?php echo $productCounts['fashion'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="home" onclick="setCategory('home')">
                    <span>🏠 منزل ومطبخ</span>
                    <span class="category-count"><?php echo $productCounts['home'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="sports" onclick="setCategory('sports')">
                    <span>⚽ رياضة</span>
                    <span class="category-count"><?php echo $productCounts['sports'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="beauty" onclick="setCategory('beauty')">
                    <span>💄 جمال وعناية</span>
                    <span class="category-count"><?php echo $productCounts['beauty'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="books" onclick="setCategory('books')">
                    <span>📚 كتب</span>
                    <span class="category-count"><?php echo $productCounts['books'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="toys" onclick="setCategory('toys')">
                    <span>🧸 ألعاب</span>
                    <span class="category-count"><?php echo $productCounts['toys'] ?? 0; ?></span>
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <!-- Price Range -->
                <div class="filter-group">
                    <span class="filter-label">السعر:</span>
                    <input type="number" id="minPrice" class="filter-input" placeholder="من" min="0">
                    <span>-</span>
                    <input type="number" id="maxPrice" class="filter-input" placeholder="إلى" min="0">
                </div>

                <!-- Discount Filter -->
                <div class="filter-group">
                    <span class="filter-label">الخصم:</span>
                    <div class="discount-filter">
                        <button class="discount-btn" data-discount="10" onclick="setDiscount('10')">10%+</button>
                        <button class="discount-btn" data-discount="20" onclick="setDiscount('20')">20%+</button>
                        <button class="discount-btn" data-discount="30" onclick="setDiscount('30')">30%+</button>
                        <button class="discount-btn" data-discount="50" onclick="setDiscount('50')">50%+</button>
                    </div>
                </div>

                <!-- Sort By -->
                <div class="filter-group">
                    <span class="filter-label">ترتيب حسب:</span>
                    <select id="sortBy" class="sort-select">
                        <option value="newest">الأحدث</option>
                        <option value="price_asc">السعر: منخفض → مرتفع</option>
                        <option value="price_desc">السعر: مرتفع → منخفض</option>
                        <option value="discount">الأعلى خصماً</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button class="reset-btn" onclick="resetFilters()">إعادة تعيين</button>
            </div>
        </div>
    </section>

    <!-- Results Counter -->
    <div class="container">
        <div id="resultsCounter" class="results-counter">جاري التحميل...</div>
    </div>

    <!-- Products Grid -->
    <main class="container">
        <div id="productsContainer" class="products-grid">
            <!-- سيتم ملؤها عبر JavaScript -->
            <div class="loading">
                <div class="spinner"></div>
                <p style="margin-top: 1rem; color: var(--muted-color);">جاري تحميل المنتجات...</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-links">
                <a href="#">سياسة الخصوصية</a>
                <a href="#">شروط الاستخدام</a>
                <a href="/admin/login.php">تسجيل الدخول</a>
            </div>
            <p class="copyright">
                &copy; <?php echo date('Y'); ?> PYRASTORE - جميع الحقوق محفوظة
            </p>
        </div>
    </footer>

    <!-- Main JavaScript -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
