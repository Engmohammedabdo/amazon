/**
 * PYRASTORE - Main JavaScript
 * وظائف الموقع الأمامي
 */

// ==================== Session Management ====================
function getOrCreateSessionId() {
    let sessionId = localStorage.getItem('pyra_session');
    if (!sessionId) {
        sessionId = generateRandomId();
        localStorage.setItem('pyra_session', sessionId);
    }
    return sessionId;
}

function generateRandomId() {
    return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

// ==================== Analytics Tracking ====================
function trackEvent(eventType, productId = null) {
    const sessionId = getOrCreateSessionId();

    fetch('/api/track.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            event_type: eventType,
            product_id: productId,
            session_id: sessionId
        })
    }).catch(err => console.error('Tracking error:', err));
}

// Track page view on load
document.addEventListener('DOMContentLoaded', function() {
    trackEvent('page_view');
});

// ==================== Product Actions ====================
function viewProduct(productId) {
    trackEvent('product_click', productId);
    window.location.href = `/product.php?id=${productId}`;
}

function buyNow(event, productId, affiliateLink) {
    event.stopPropagation();
    trackEvent('purchase_button_click', productId);

    // فتح رابط الأفلييت في نافذة جديدة
    window.open(affiliateLink, '_blank', 'noopener,noreferrer');
}

// ==================== Filters & Search ====================
let currentFilters = {
    search: '',
    category: '',
    minPrice: '',
    maxPrice: '',
    discount: '',
    sortBy: 'newest'
};

function updateFilters() {
    // جمع قيم الفلاتر
    currentFilters.search = document.getElementById('searchInput')?.value || '';
    currentFilters.category = document.querySelector('.category-btn.active')?.dataset.category || '';
    currentFilters.minPrice = document.getElementById('minPrice')?.value || '';
    currentFilters.maxPrice = document.getElementById('maxPrice')?.value || '';
    currentFilters.discount = document.querySelector('.discount-btn.active')?.dataset.discount || '';
    currentFilters.sortBy = document.getElementById('sortBy')?.value || 'newest';

    // تحميل المنتجات
    loadProducts();
}

function setCategory(category) {
    // إزالة active من جميع الأزرار
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // إضافة active للزر المحدد
    if (category) {
        const btn = document.querySelector(`[data-category="${category}"]`);
        if (btn) btn.classList.add('active');
    }

    currentFilters.category = category;
    updateFilters();
}

function setDiscount(discount) {
    // إزالة active من جميع الأزرار
    document.querySelectorAll('.discount-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // إضافة active للزر المحدد
    if (discount) {
        const btn = document.querySelector(`[data-discount="${discount}"]`);
        if (btn) btn.classList.add('active');
    }

    currentFilters.discount = discount;
    updateFilters();
}

function resetFilters() {
    // إعادة تعيين الفلاتر
    document.getElementById('searchInput').value = '';
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('sortBy').value = 'newest';

    document.querySelectorAll('.category-btn, .discount-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    currentFilters = {
        search: '',
        category: '',
        minPrice: '',
        maxPrice: '',
        discount: '',
        sortBy: 'newest'
    };

    loadProducts();
}

// ==================== Load Products ====================
function loadProducts() {
    const container = document.getElementById('productsContainer');
    const counter = document.getElementById('resultsCounter');

    // عرض حالة التحميل
    container.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 1rem; color: var(--muted-color);">جاري التحميل...</p>
        </div>
    `;

    // بناء query string
    const params = new URLSearchParams();
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.category) params.append('category', currentFilters.category);
    if (currentFilters.minPrice) params.append('min_price', currentFilters.minPrice);
    if (currentFilters.maxPrice) params.append('max_price', currentFilters.maxPrice);
    if (currentFilters.discount) params.append('discount', currentFilters.discount);
    if (currentFilters.sortBy) params.append('sort', currentFilters.sortBy);

    // جلب المنتجات
    fetch(`/api/products.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.products.length > 0) {
                displayProducts(data.products);
                updateCounter(data.products.length, data.total);
            } else {
                showEmptyState();
                updateCounter(0, 0);
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            showErrorState();
        });
}

function displayProducts(products) {
    const container = document.getElementById('productsContainer');

    const html = products.map(product => `
        <div class="product-card" onclick="viewProduct(${product.id})">
            <div class="product-image-wrapper">
                <img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.title)}" class="product-image">
                <div class="category-badge">${getCategoryIcon(product.category)} ${getCategoryNameAr(product.category)}</div>
                ${product.discount_percentage ? `<div class="discount-badge">-${product.discount_percentage}%</div>` : ''}
            </div>
            <div class="product-content">
                <h3 class="product-title">${escapeHtml(product.title)}</h3>
                <p class="product-description">${escapeHtml(truncateText(product.description, 100))}</p>
                <div class="product-pricing">
                    <div class="product-price">
                        ${formatPrice(product.price)} درهم
                        ${product.original_price ? `<span class="product-original-price">${formatPrice(product.original_price)} درهم</span>` : ''}
                    </div>
                    ${product.original_price ? `<div class="product-savings">وفر ${formatPrice(product.original_price - product.price)} درهم</div>` : ''}
                </div>
                <button class="buy-btn" onclick="buyNow(event, ${product.id}, '${escapeHtml(product.affiliate_link)}')">
                    <span>🛒</span>
                    <span>اشتري الآن</span>
                </button>
            </div>
        </div>
    `).join('');

    container.innerHTML = html;
}

function showEmptyState() {
    const container = document.getElementById('productsContainer');
    container.innerHTML = `
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <p class="empty-state-text">لا توجد منتجات تطابق البحث</p>
        </div>
    `;
}

function showErrorState() {
    const container = document.getElementById('productsContainer');
    container.innerHTML = `
        <div class="empty-state">
            <div class="empty-state-icon">⚠️</div>
            <p class="empty-state-text">حدث خطأ أثناء تحميل المنتجات</p>
            <button class="btn" onclick="loadProducts()" style="margin-top: 1rem;">إعادة المحاولة</button>
        </div>
    `;
}

function updateCounter(showing, total) {
    const counter = document.getElementById('resultsCounter');
    if (counter) {
        counter.textContent = `عرض ${showing} من ${total} منتج`;
    }
}

// ==================== Helper Functions ====================
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function truncateText(text, length) {
    if (text.length <= length) return text;
    return text.substr(0, length) + '...';
}

function formatPrice(price) {
    return parseFloat(price).toFixed(2);
}

function getCategoryNameAr(category) {
    const names = {
        'electronics': 'إلكترونيات',
        'fashion': 'أزياء',
        'home': 'منزل ومطبخ',
        'sports': 'رياضة',
        'beauty': 'جمال وعناية',
        'books': 'كتب',
        'toys': 'ألعاب',
        'other': 'منتجات أخرى'
    };
    return names[category] || 'منتجات أخرى';
}

function getCategoryIcon(category) {
    const icons = {
        'electronics': '📱',
        'fashion': '👔',
        'home': '🏠',
        'sports': '⚽',
        'beauty': '💄',
        'books': '📚',
        'toys': '🧸',
        'other': '🛍️'
    };
    return icons[category] || '🛍️';
}

// ==================== Search Debounce ====================
let searchTimeout;
function handleSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 500);
}

// ==================== Initialize ====================
document.addEventListener('DOMContentLoaded', function() {
    // تحميل المنتجات عند تحميل الصفحة
    if (document.getElementById('productsContainer')) {
        loadProducts();
    }

    // إضافة مستمعي الأحداث
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
    }

    const sortBy = document.getElementById('sortBy');
    if (sortBy) {
        sortBy.addEventListener('change', updateFilters);
    }

    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');
    if (minPrice) minPrice.addEventListener('change', updateFilters);
    if (maxPrice) maxPrice.addEventListener('change', updateFilters);
});
