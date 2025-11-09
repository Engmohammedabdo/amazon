<?php
require_once __DIR__ . '/config/config.php';

// Set default language if not set
if (!isset($_SESSION['lang'])) {
    setLanguage('ar');
}

$lang = getCurrentLang();
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('متجر بيرا - أفضل العروض من أمازون الإمارات', 'PyraStore - Best Deals from Amazon UAE') ?></title>

    <meta name="description" content="<?= t('اكتشف أفضل المنتجات والعروض من أمازون الإمارات مع عمولات حصرية', 'Discover the best products and deals from Amazon UAE') ?>">
    <meta name="keywords" content="amazon uae, shopping, deals, online shopping, <?= t('تسوق أونلاين', 'uae shopping') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= t('متجر بيرا', 'PyraStore') ?>">
    <meta property="og:description" content="<?= t('أفضل العروض من أمازون الإمارات', 'Best Deals from Amazon UAE') ?>">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Google Analytics -->
    <?php if ($gaId = getSetting('google_analytics_id')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaId) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($gaId) ?>');
    </script>
    <?php endif; ?>

    <!-- Meta Pixel -->
    <?php if ($metaPixelId = getSetting('meta_pixel_id')): ?>
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?= htmlspecialchars($metaPixelId) ?>');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?= htmlspecialchars($metaPixelId) ?>&ev=PageView&noscript=1"
    /></noscript>
    <?php endif; ?>

    <!-- TikTok Pixel -->
    <?php if ($tiktokPixelId = getSetting('tiktok_pixel_id')): ?>
    <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
          ttq.load('<?= htmlspecialchars($tiktokPixelId) ?>');
          ttq.page();
        }(window, document, 'ttq');
    </script>
    <?php endif; ?>
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-top">
        <div class="container">
            <div class="header-info">
                <span>📞 <?= getSetting('whatsapp_number', '+971 XX XXX XXXX') ?></span>
                <span>📧 <?= getSetting('contact_email', 'info@pyrastore.com') ?></span>
            </div>

            <div class="lang-switcher">
                <button class="lang-btn <?= $lang === 'ar' ? 'active' : '' ?>" onclick="setLanguage('ar')">العربية</button>
                <button class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>" onclick="setLanguage('en')">English</button>
            </div>
        </div>
    </div>

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

                <div class="header-search">
                    <div class="search-box">
                        <input type="text"
                               id="search-input"
                               placeholder="<?= t('ابحث عن المنتجات...', 'Search for products...') ?>"
                               onkeyup="if(event.key === 'Enter') filterProducts()">
                        <button onclick="filterProducts()">
                            <?= t('بحث', 'Search') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?= t('اكتشف أفضل العروض 🔥', 'Discover the Best Deals 🔥') ?></h1>
            <p><?= t('منتجات مختارة بعناية من أمازون الإمارات', 'Carefully Curated Products from Amazon UAE') ?></p>

            <div class="hero-badges">
                <div class="hero-badge">
                    <span class="hero-badge-icon">✅</span>
                    <span><?= t('منتجات أصلية 100%', '100% Authentic Products') ?></span>
                </div>
                <div class="hero-badge">
                    <span class="hero-badge-icon">🚚</span>
                    <span><?= t('توصيل سريع', 'Fast Delivery') ?></span>
                </div>
                <div class="hero-badge">
                    <span class="hero-badge-icon">💳</span>
                    <span><?= t('دفع آمن', 'Secure Payment') ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories">
    <div class="container">
        <h2 class="section-title"><?= t('تصفح حسب الفئة', 'Browse by Category') ?></h2>
        <div id="categories-container" class="categories-container">
            <!-- Categories will be loaded via JavaScript -->
        </div>
    </div>
</section>

<!-- Filters -->
<section class="products-section">
    <div class="container">
        <div class="filters">
            <div class="filters-grid">
                <div class="filter-group">
                    <label><?= t('الفئة', 'Category') ?></label>
                    <select id="category-filter" onchange="filterProducts()">
                        <option value=""><?= t('جميع الفئات', 'All Categories') ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><?= t('الحد الأدنى للسعر', 'Min Price') ?></label>
                    <input type="number" id="min-price" placeholder="0" min="0">
                </div>

                <div class="filter-group">
                    <label><?= t('الحد الأقصى للسعر', 'Max Price') ?></label>
                    <input type="number" id="max-price" placeholder="10000" min="0">
                </div>

                <div class="filter-group">
                    <label><?= t('الخصم الأدنى', 'Min Discount') ?></label>
                    <select id="discount-filter" onchange="filterProducts()">
                        <option value=""><?= t('كل الخصومات', 'All Discounts') ?></option>
                        <option value="10">10%+</option>
                        <option value="20">20%+</option>
                        <option value="30">30%+</option>
                        <option value="50">50%+</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><?= t('الترتيب', 'Sort By') ?></label>
                    <select id="sort-select" onchange="filterProducts()">
                        <option value="newest"><?= t('الأحدث', 'Newest') ?></option>
                        <option value="price_asc"><?= t('السعر: من الأقل', 'Price: Low to High') ?></option>
                        <option value="price_desc"><?= t('السعر: من الأعلى', 'Price: High to Low') ?></option>
                        <option value="discount_desc"><?= t('الخصم الأعلى', 'Highest Discount') ?></option>
                        <option value="rating_desc"><?= t('الأعلى تقييماً', 'Top Rated') ?></option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button class="btn btn-primary" onclick="filterProducts()">
                    <?= t('تطبيق الفلاتر', 'Apply Filters') ?>
                </button>
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <?= t('إعادة تعيين', 'Reset') ?>
                </button>
            </div>
        </div>

        <!-- Products Header -->
        <div class="products-header">
            <h2 id="results-count" class="results-count"></h2>
        </div>

        <!-- Products Grid -->
        <div id="products-container" class="products-grid">
            <!-- Products will be loaded via JavaScript -->
        </div>

        <!-- Pagination -->
        <div id="pagination" class="pagination"></div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?= t('عن المتجر', 'About Store') ?></h3>
                <p><?= t('نحن نقدم أفضل المنتجات المختارة من أمازون الإمارات بأسعار تنافسية وعروض حصرية.', 'We offer the best curated products from Amazon UAE at competitive prices and exclusive deals.') ?></p>
            </div>

            <div class="footer-section">
                <h3><?= t('روابط سريعة', 'Quick Links') ?></h3>
                <ul>
                    <li><a href="/"><?= t('الرئيسية', 'Home') ?></a></li>
                    <li><a href="#categories"><?= t('الفئات', 'Categories') ?></a></li>
                    <li><a href="/admin"><?= t('لوحة التحكم', 'Admin') ?></a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3><?= t('اتصل بنا', 'Contact Us') ?></h3>
                <ul>
                    <li>📧 <?= getSetting('contact_email', 'info@pyrastore.com') ?></li>
                    <li>📞 <?= getSetting('whatsapp_number', '+971 XX XXX XXXX') ?></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3><?= t('إخلاء المسؤولية', 'Disclaimer') ?></h3>
                <p style="font-size: 12px; opacity: 0.8;">
                    <?= t('نحن مشاركون في برنامج Amazon Affiliate ونكسب عمولة من المشتريات المؤهلة.', 'We are participants in the Amazon Affiliate Program and earn commission from qualifying purchases.') ?>
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= t('متجر بيرا', 'PyraStore') ?>. <?= t('جميع الحقوق محفوظة', 'All rights reserved') ?>.</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="/assets/js/tracking.js"></script>
<script src="/assets/js/main.js"></script>

</body>
</html>
