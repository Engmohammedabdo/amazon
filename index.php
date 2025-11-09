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
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                </div>
            </div>

            <!-- Category Filters -->
            <div class="category-filters">
                <button class="category-btn" onclick="setCategory('')">
                    <span><i class="fas fa-th"></i> الكل</span>
                    <span class="category-count"><?php echo array_sum($productCounts); ?></span>
                </button>
                <button class="category-btn" data-category="electronics" onclick="setCategory('electronics')">
                    <span><i class="fas fa-mobile-alt"></i> إلكترونيات</span>
                    <span class="category-count"><?php echo $productCounts['electronics'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="fashion" onclick="setCategory('fashion')">
                    <span><i class="fas fa-tshirt"></i> أزياء</span>
                    <span class="category-count"><?php echo $productCounts['fashion'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="home" onclick="setCategory('home')">
                    <span><i class="fas fa-home"></i> منزل ومطبخ</span>
                    <span class="category-count"><?php echo $productCounts['home'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="sports" onclick="setCategory('sports')">
                    <span><i class="fas fa-futbol"></i> رياضة</span>
                    <span class="category-count"><?php echo $productCounts['sports'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="beauty" onclick="setCategory('beauty')">
                    <span><i class="fas fa-spa"></i> جمال وعناية</span>
                    <span class="category-count"><?php echo $productCounts['beauty'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="books" onclick="setCategory('books')">
                    <span><i class="fas fa-book"></i> كتب</span>
                    <span class="category-count"><?php echo $productCounts['books'] ?? 0; ?></span>
                </button>
                <button class="category-btn" data-category="toys" onclick="setCategory('toys')">
                    <span><i class="fas fa-gamepad"></i> ألعاب</span>
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
                <button class="reset-btn" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> إعادة تعيين
                </button>
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
