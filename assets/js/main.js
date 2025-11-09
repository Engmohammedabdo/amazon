/**
 * PYRASTORE - Main JavaScript
 * وظائف الموقع الأمامي مع تحسينات الأنيميشن
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

    // إضافة تأثير الضغط
    const btn = event.currentTarget;
    btn.style.transform = 'scale(0.95)';
    setTimeout(() => {
        btn.style.transform = '';
    }, 150);

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
        if (btn) {
            btn.classList.add('active');
            // تأثير الضغط
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => btn.style.transform = '', 150);
        }
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
        if (btn) {
            btn.classList.add('active');
            // تأثير الضغط
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => btn.style.transform = '', 150);
        }
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

    // عرض Skeleton Loader بدلاً من Spinner
    container.innerHTML = generateSkeletonCards(6);

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

function generateSkeletonCards(count) {
    let html = '';
    for (let i = 0; i < count; i++) {
        html += `
            <div class="product-card skeleton">
                <div class="skeleton-image"></div>
                <div class="skeleton-content">
                    <div class="skeleton-title"></div>
                    <div class="skeleton-text"></div>
                    <div class="skeleton-text short"></div>
                    <div class="skeleton-button"></div>
                </div>
            </div>
        `;
    }
    return html;
}

function displayProducts(products) {
    const container = document.getElementById('productsContainer');

    const html = products.map((product, index) => `
        <div class="product-card fade-in-up" style="animation-delay: ${index * 0.1}s" onclick="viewProduct(${product.id})">
            <div class="product-image-wrapper">
                <img src="${escapeHtml(product.image_url)}"
                     alt="${escapeHtml(product.title)}"
                     class="product-image"
                     loading="lazy"
                     onerror="this.src='/assets/images/placeholder.jpg'">
                <div class="category-badge">
                    <i class="fas fa-${getCategoryIconFA(product.category)}"></i>
                    ${getCategoryNameAr(product.category)}
                </div>
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
                    <i class="fas fa-shopping-cart"></i>
                    <span>اشتري الآن</span>
                </button>
            </div>
        </div>
    `).join('');

    container.innerHTML = html;

    // تفعيل Intersection Observer للكروت
    observeProductCards();
}

function showEmptyState() {
    const container = document.getElementById('productsContainer');
    container.innerHTML = `
        <div class="empty-state fade-in">
            <div class="empty-state-icon">
                <i class="fas fa-search fa-3x"></i>
            </div>
            <p class="empty-state-text">لا توجد منتجات تطابق البحث</p>
            <button class="btn" onclick="resetFilters()" style="margin-top: 1rem;">
                <i class="fas fa-redo"></i> إعادة تعيين الفلاتر
            </button>
        </div>
    `;
}

function showErrorState() {
    const container = document.getElementById('productsContainer');
    container.innerHTML = `
        <div class="empty-state fade-in">
            <div class="empty-state-icon">
                <i class="fas fa-exclamation-triangle fa-3x"></i>
            </div>
            <p class="empty-state-text">حدث خطأ أثناء تحميل المنتجات</p>
            <button class="btn" onclick="loadProducts()" style="margin-top: 1rem;">
                <i class="fas fa-sync-alt"></i> إعادة المحاولة
            </button>
        </div>
    `;
}

function updateCounter(showing, total) {
    const counter = document.getElementById('resultsCounter');
    if (counter) {
        counter.textContent = `عرض ${showing} من ${total} منتج`;
        counter.style.animation = 'fadeIn 0.5s ease';
    }
}

// ==================== Intersection Observer للأنيميشن ====================
function observeProductCards() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
}

// ==================== Scroll Effects ====================
let lastScrollTop = 0;
const header = document.querySelector('.site-header');
const filtersSection = document.querySelector('.filters-section');

window.addEventListener('scroll', function() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    // إخفاء/إظهار الهيدر عند السكرول
    if (scrollTop > lastScrollTop && scrollTop > 100) {
        // السكرول للأسفل
        if (header) header.style.transform = 'translateY(-100%)';
    } else {
        // السكرول للأعلى
        if (header) header.style.transform = 'translateY(0)';
    }

    // تثبيت الفلاتر عند السكرول
    if (filtersSection) {
        if (scrollTop > 200) {
            filtersSection.classList.add('sticky');
        } else {
            filtersSection.classList.remove('sticky');
        }
    }

    lastScrollTop = scrollTop;
}, { passive: true });

// ==================== Smooth Scroll ====================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ==================== Parallax Effect للهيدر ====================
const heroSection = document.querySelector('.hero-section');
if (heroSection) {
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
        heroSection.style.opacity = 1 - (scrolled / 500);
    }, { passive: true });
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

function getCategoryIconFA(category) {
    const icons = {
        'electronics': 'mobile-alt',
        'fashion': 'tshirt',
        'home': 'home',
        'sports': 'futbol',
        'beauty': 'spa',
        'books': 'book',
        'toys': 'gamepad',
        'other': 'shopping-bag'
    };
    return icons[category] || 'shopping-bag';
}

// ==================== Search Debounce ====================
let searchTimeout;
function handleSearchInput() {
    clearTimeout(searchTimeout);

    // تأثير بصري للبحث
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.style.borderColor = 'var(--primary)';
        setTimeout(() => {
            searchInput.style.borderColor = '';
        }, 300);
    }

    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 500);
}

// ==================== Image Lazy Loading ====================
function lazyLoadImages() {
    const images = document.querySelectorAll('img[loading="lazy"]');

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }
}

// ==================== Scroll to Top Button ====================
function createScrollTopButton() {
    const scrollBtn = document.createElement('button');
    scrollBtn.className = 'scroll-top-btn';
    scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollBtn.setAttribute('aria-label', 'العودة للأعلى');
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    }, { passive: true });

    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ==================== Price Range Formatter ====================
function formatPriceRange() {
    const minPrice = document.getElementById('minPrice');
    const maxPrice = document.getElementById('maxPrice');

    if (minPrice) {
        minPrice.addEventListener('input', function() {
            this.style.background = `linear-gradient(to right, var(--primary) 0%, var(--primary) ${(this.value / this.max) * 100}%, var(--border-color) ${(this.value / this.max) * 100}%, var(--border-color) 100%)`;
        });
    }

    if (maxPrice) {
        maxPrice.addEventListener('input', function() {
            this.style.background = `linear-gradient(to right, var(--primary) 0%, var(--primary) ${(this.value / this.max) * 100}%, var(--border-color) ${(this.value / this.max) * 100}%, var(--border-color) 100%)`;
        });
    }
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

    // تهيئة المميزات الإضافية
    lazyLoadImages();
    createScrollTopButton();
    formatPriceRange();

    // تحسين الأداء: Passive Event Listeners
    document.addEventListener('touchstart', function() {}, { passive: true });
});

// ==================== Service Worker للتخزين المؤقت ====================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        // يمكن تفعيل Service Worker لاحقاً لتحسين الأداء
        // navigator.serviceWorker.register('/sw.js');
    });
}
