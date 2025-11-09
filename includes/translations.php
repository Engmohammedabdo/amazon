<?php
/**
 * Translation System - PYRASTORE
 * Handles UI translations (NOT product content)
 */

/**
 * Get current language from cookie
 * @return string 'ar' or 'en'
 */
function getCurrentLanguage() {
    return isset($_COOKIE['site_language']) && $_COOKIE['site_language'] === 'en' ? 'en' : 'ar';
}

/**
 * Get language direction
 * @return string 'rtl' or 'ltr'
 */
function getLanguageDirection() {
    return getCurrentLanguage() === 'ar' ? 'rtl' : 'ltr';
}

/**
 * Translation function
 * @param string $key Translation key
 * @return string Translated text
 */
function t($key) {
    $lang = getCurrentLanguage();

    $translations = [
        // Site Header
        'site_tagline' => [
            'ar' => 'UAE PICKS',
            'en' => 'UAE PICKS'
        ],
        'site_description' => [
            'ar' => 'أفضل المنتجات من أمازون الإمارات بأسعار مميزة',
            'en' => 'Best Products from Amazon UAE at Amazing Prices'
        ],

        // Search
        'search_placeholder' => [
            'ar' => 'ابحث عن منتج...',
            'en' => 'Search for a product...'
        ],

        // Categories
        'category_all' => [
            'ar' => 'الكل',
            'en' => 'All'
        ],
        'category_electronics' => [
            'ar' => 'إلكترونيات',
            'en' => 'Electronics'
        ],
        'category_fashion' => [
            'ar' => 'أزياء',
            'en' => 'Fashion'
        ],
        'category_home' => [
            'ar' => 'منزل ومطبخ',
            'en' => 'Home & Kitchen'
        ],
        'category_sports' => [
            'ar' => 'رياضة',
            'en' => 'Sports'
        ],
        'category_beauty' => [
            'ar' => 'جمال وعناية',
            'en' => 'Beauty & Care'
        ],
        'category_books' => [
            'ar' => 'كتب',
            'en' => 'Books'
        ],
        'category_toys' => [
            'ar' => 'ألعاب',
            'en' => 'Toys & Games'
        ],

        // Filters
        'filter_price' => [
            'ar' => 'السعر:',
            'en' => 'Price:'
        ],
        'filter_from' => [
            'ar' => 'من',
            'en' => 'From'
        ],
        'filter_to' => [
            'ar' => 'إلى',
            'en' => 'To'
        ],
        'filter_discount' => [
            'ar' => 'الخصم:',
            'en' => 'Discount:'
        ],
        'filter_sort_by' => [
            'ar' => 'ترتيب حسب:',
            'en' => 'Sort by:'
        ],
        'sort_newest' => [
            'ar' => 'الأحدث',
            'en' => 'Newest'
        ],
        'sort_price_asc' => [
            'ar' => 'السعر: منخفض → مرتفع',
            'en' => 'Price: Low → High'
        ],
        'sort_price_desc' => [
            'ar' => 'السعر: مرتفع → منخفض',
            'en' => 'Price: High → Low'
        ],
        'sort_discount' => [
            'ar' => 'الأعلى خصماً',
            'en' => 'Highest Discount'
        ],
        'filter_reset' => [
            'ar' => 'إعادة تعيين',
            'en' => 'Reset'
        ],

        // Loading messages
        'loading' => [
            'ar' => 'جاري التحميل...',
            'en' => 'Loading...'
        ],
        'loading_products' => [
            'ar' => 'جاري تحميل المنتجات...',
            'en' => 'Loading products...'
        ],

        // Product Page
        'click_to_zoom' => [
            'ar' => 'انقر للتكبير',
            'en' => 'Click to zoom'
        ],
        'image_alt' => [
            'ar' => 'صورة',
            'en' => 'Image'
        ],
        'amazon_rating' => [
            'ar' => 'تقييم أمازون',
            'en' => 'Amazon Rating'
        ],
        'bought_recently' => [
            'ar' => 'تم شراؤه مؤخراً',
            'en' => 'bought recently'
        ],
        'review_count' => [
            'ar' => 'مراجعة',
            'en' => 'review'
        ],
        'currency' => [
            'ar' => 'درهم',
            'en' => 'AED'
        ],
        'save' => [
            'ar' => 'وفر',
            'en' => 'Save'
        ],
        'savings' => [
            'ar' => 'توفير',
            'en' => 'Savings'
        ],
        'buy_now_amazon' => [
            'ar' => 'اشتري الآن من أمازون',
            'en' => 'Buy Now from Amazon'
        ],
        'buy_now' => [
            'ar' => 'اشتري الآن',
            'en' => 'Buy Now'
        ],
        'product_description' => [
            'ar' => 'وصف المنتج',
            'en' => 'Product Description'
        ],
        'product_video' => [
            'ar' => 'فيديو المنتج',
            'en' => 'Product Video'
        ],
        'reviews' => [
            'ar' => 'المراجعات',
            'en' => 'Reviews'
        ],
        'share_product' => [
            'ar' => 'شارك هذا المنتج',
            'en' => 'Share this product'
        ],
        'whatsapp' => [
            'ar' => 'واتساب',
            'en' => 'WhatsApp'
        ],
        'facebook' => [
            'ar' => 'فيسبوك',
            'en' => 'Facebook'
        ],
        'twitter' => [
            'ar' => 'تويتر',
            'en' => 'Twitter'
        ],
        'copy' => [
            'ar' => 'نسخ',
            'en' => 'Copy'
        ],
        'similar_products' => [
            'ar' => 'منتجات مشابهة',
            'en' => 'Similar Products'
        ],

        // Product Cards (JavaScript)
        'showing_products' => [
            'ar' => 'عرض {showing} من {total} منتج',
            'en' => 'Showing {showing} of {total} products'
        ],
        'no_products_found' => [
            'ar' => 'لا توجد منتجات تطابق البحث',
            'en' => 'No products match your search'
        ],
        'reset_filters' => [
            'ar' => 'إعادة تعيين الفلاتر',
            'en' => 'Reset Filters'
        ],
        'error_loading' => [
            'ar' => 'حدث خطأ أثناء تحميل المنتجات',
            'en' => 'Error loading products'
        ],
        'retry' => [
            'ar' => 'إعادة المحاولة',
            'en' => 'Retry'
        ],
        'category_other' => [
            'ar' => 'منتجات أخرى',
            'en' => 'Other Products'
        ],

        // Footer
        'privacy_policy' => [
            'ar' => 'سياسة الخصوصية',
            'en' => 'Privacy Policy'
        ],
        'terms_of_use' => [
            'ar' => 'شروط الاستخدام',
            'en' => 'Terms of Use'
        ],
        'admin_login' => [
            'ar' => 'تسجيل الدخول',
            'en' => 'Admin Login'
        ],
        'all_rights_reserved' => [
            'ar' => 'جميع الحقوق محفوظة',
            'en' => 'All Rights Reserved'
        ]
    ];

    return $translations[$key][$lang] ?? $key;
}

/**
 * Get category name (works with both languages)
 * @param string $category Category key
 * @return string Category name in current language
 */
function getCategoryName($category) {
    $lang = getCurrentLanguage();

    $categories = [
        'electronics' => ['ar' => 'إلكترونيات', 'en' => 'Electronics'],
        'fashion' => ['ar' => 'أزياء', 'en' => 'Fashion'],
        'home' => ['ar' => 'منزل ومطبخ', 'en' => 'Home & Kitchen'],
        'sports' => ['ar' => 'رياضة', 'en' => 'Sports'],
        'beauty' => ['ar' => 'جمال وعناية', 'en' => 'Beauty & Care'],
        'books' => ['ar' => 'كتب', 'en' => 'Books'],
        'toys' => ['ar' => 'ألعاب', 'en' => 'Toys & Games'],
        'other' => ['ar' => 'منتجات أخرى', 'en' => 'Other Products']
    ];

    return $categories[$category][$lang] ?? $categories['other'][$lang];
}
?>
