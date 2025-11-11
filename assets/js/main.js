/**
 * PYRASTORE - Main JavaScript
 * وظائف الموقع الأمامي مع تحسينات الأنيميشن
 */

// ==================== Language Switcher ====================
function switchLanguage(lang) {
    // Delete old 'lang' cookie if it exists (cleanup)
    document.cookie = 'lang=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';

    // Set cookie for 1 year
    const expiryDate = new Date();
    expiryDate.setFullYear(expiryDate.getFullYear() + 1);
    document.cookie = `site_language=${lang}; path=/; expires=${expiryDate.toUTCString()}; SameSite=Lax`;

    // Reload page to apply language change
    window.location.reload();
}

// Get current language from cookie
function getCurrentLanguage() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'site_language') {
            return value;
        }
    }
    return 'ar'; // Default to Arabic
}

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
        <div class="product-card fade-in-up"
             style="animation-delay: ${index * 0.1}s"
             onclick="viewProduct(${product.id})"
             data-product-id="${product.id}"
             data-product-title="${escapeHtml(product.title)}"
             data-product-price="${product.price}"
             data-product-category="${escapeHtml(product.category)}">
            <div class="product-image-wrapper">
                <img src="${escapeHtml(product.image_url)}"
                     alt="${escapeHtml(product.title)}"
                     class="product-image"
                     loading="lazy"
                     onerror="this.src='/assets/images/placeholder.jpg'">
                <div class="category-badge">
                    <i class="fas fa-${getCategoryIconFA(product.category)}"></i>
                    ${getCategoryName(product.category)}
                </div>
                ${product.discount_percentage ? `<div class="discount-badge">-${product.discount_percentage}%</div>` : ''}
            </div>
            <div class="product-content">
                <h3 class="product-title">${escapeHtml(product.title)}</h3>
                <p class="product-description">${escapeHtml(truncateText(stripHtml(product.description), 100))}</p>
                <div class="product-pricing">
                    <div class="product-price">
                        ${formatPrice(product.price)} ${window.TRANSLATIONS.currency}
                        ${product.original_price ? `<span class="product-original-price">${formatPrice(product.original_price)} ${window.TRANSLATIONS.currency}</span>` : ''}
                    </div>
                    ${product.original_price ? `<div class="product-savings">${window.TRANSLATIONS.save} ${formatPrice(product.original_price - product.price)} ${window.TRANSLATIONS.currency}</div>` : ''}
                </div>
                <div class="amazon-benefits">
                    <span class="benefit-badge benefit-original">
                        <i class="fas fa-check-circle"></i>
                        ${window.TRANSLATIONS.amazon_original}
                    </span>
                    <span class="benefit-badge benefit-protection">
                        <i class="fas fa-shield-alt"></i>
                        ${window.TRANSLATIONS.amazon_protection}
                    </span>
                    <span class="benefit-badge benefit-support">
                        <i class="fas fa-headset"></i>
                        ${window.TRANSLATIONS.amazon_support}
                    </span>
                    <span class="benefit-badge benefit-returns">
                        <i class="fas fa-undo"></i>
                        ${window.TRANSLATIONS.amazon_returns}
                    </span>
                </div>
                <button class="buy-btn" onclick="buyNow(event, ${product.id}, '${escapeHtml(product.affiliate_link)}')">
                    <i class="fas fa-shopping-cart"></i>
                    <span>${window.TRANSLATIONS.buy_now}</span>
                </button>
            </div>
        </div>
    `).join('');

    container.innerHTML = html;

    // Update sticky CTA bar with first product
    if (products.length > 0 && window.innerWidth < 768) {
        updateStickyCTA(products[0]);
    }

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
            <p class="empty-state-text">${window.TRANSLATIONS.no_products_found}</p>
            <button class="btn" onclick="resetFilters()" style="margin-top: 1rem;">
                <i class="fas fa-redo"></i> ${window.TRANSLATIONS.reset_filters}
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
            <p class="empty-state-text">${window.TRANSLATIONS.error_loading}</p>
            <button class="btn" onclick="loadProducts()" style="margin-top: 1rem;">
                <i class="fas fa-sync-alt"></i> ${window.TRANSLATIONS.retry}
            </button>
        </div>
    `;
}

function updateCounter(showing, total) {
    const counter = document.getElementById('resultsCounter');
    if (counter) {
        counter.textContent = window.TRANSLATIONS.showing_products
            .replace('{showing}', showing)
            .replace('{total}', total);
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
// Strip HTML tags from text
function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

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

function getCategoryName(category) {
    return window.TRANSLATIONS.categories[category] || window.TRANSLATIONS.categories.other;
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

// ==================== Attach Language Switcher Listeners ====================
// Attach immediately (script is at end of body)
document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const isArabic = this.textContent.trim() === 'AR';
        switchLanguage(isArabic ? 'ar' : 'en');
    });
});

// ==================== Update Sticky CTA Bar ====================
function updateStickyCTA(product) {
  const priceEl = document.getElementById('stickyPrice');
  const originalPriceEl = document.getElementById('stickyOriginalPrice');
  const buyBtn = document.getElementById('stickyBuyBtn');

  if (priceEl && product) {
    priceEl.textContent = `${formatPrice(product.price)} ${window.TRANSLATIONS.currency}`;

    if (product.original_price && originalPriceEl) {
      originalPriceEl.textContent = `${formatPrice(product.original_price)} ${window.TRANSLATIONS.currency}`;
      originalPriceEl.style.display = 'block';
    } else if (originalPriceEl) {
      originalPriceEl.style.display = 'none';
    }

    if (buyBtn) {
      buyBtn.onclick = function(e) {
        e.preventDefault();
        buyNow(e, product.id, product.affiliate_link);
      };
    }
  }
}

// ==================== Sticky CTA Bar - Show on scroll (mobile only) ====================
if (window.innerWidth < 768) {
  window.addEventListener('scroll', function() {
    const stickyBar = document.querySelector('.sticky-cta-bar');
    if (stickyBar) {
      if (window.scrollY > 200) {
        stickyBar.classList.add('show');
      } else {
        stickyBar.classList.remove('show');
      }
    }
  }, { passive: true });

  // Add class to body for padding
  document.body.classList.add('has-sticky-cta');
}

// ==================== Smart Sticky CTA for Product Page ====================
if (document.getElementById('stickyCTAProduct') && window.innerWidth < 768) {
  const stickyBar = document.getElementById('stickyCTAProduct');
  const mainButton = document.querySelector('.buy-now-btn'); // Main buy button
  
  window.addEventListener('scroll', function() {
    if (!mainButton) {
      // Always show if button not found
      stickyBar.classList.add('show');
      return;
    }

    const buttonRect = mainButton.getBoundingClientRect();
    const buttonInView = buttonRect.top < window.innerHeight && buttonRect.bottom > 0;

    // Hide ONLY when main button is visible
    if (buttonInView) {
      stickyBar.classList.remove('show');
    } else {
      // Show everywhere else (top, bottom, anywhere except near button)
      stickyBar.classList.add('show');
    }
  }, { passive: true });
  
  // Show initially
  stickyBar.classList.add('show');
  document.body.classList.add('has-sticky-cta');
}

// ==================== Enhanced Tracking Integration ====================

// Update product card display to include tracking data attributes
function addTrackingAttributesToCards() {
    document.querySelectorAll('.product-card').forEach(card => {
        const productId = card.dataset.productId;
        const productTitle = card.dataset.productTitle;
        const price = card.dataset.productPrice;
        const category = card.dataset.productCategory;

        // Track product impression when card is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && typeof trackProductView === 'function') {
                    trackProductView(productId, productTitle, price, category);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        observer.observe(card);
    });
}

// Enhanced buyNow function with tracking
const originalBuyNow = window.buyNow;
window.buyNow = function(event, productId, affiliateLink) {
    // Get product data from card
    const card = event.target.closest('.product-card');
    const productTitle = card?.dataset.productTitle || card?.querySelector('.product-title')?.textContent || 'Unknown';
    const priceText = card?.dataset.productPrice || card?.querySelector('.product-price')?.textContent || '0';
    const price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
    const category = card?.dataset.productCategory || 'Unknown';

    // Track the affiliate click
    if (typeof trackAffiliateClick === 'function') {
        trackAffiliateClick(productId, productTitle, price, category, affiliateLink);
    }

    // Call original function if it exists
    if (originalBuyNow) {
        originalBuyNow(event, productId, affiliateLink);
    } else {
        // Fallback: open affiliate link
        event.stopPropagation();
        window.open(affiliateLink, '_blank', 'noopener,noreferrer');
    }
};

// Track search with debouncing
let searchTrackingTimeout;
const originalHandleSearchInput = window.handleSearchInput;
window.handleSearchInput = function() {
    if (originalHandleSearchInput) {
        originalHandleSearchInput();
    }

    clearTimeout(searchTrackingTimeout);
    searchTrackingTimeout = setTimeout(() => {
        const searchQuery = document.getElementById('searchInput')?.value;
        if (searchQuery && typeof trackSearch === 'function') {
            const resultsCount = document.querySelectorAll('.product-card:not(.skeleton)').length;
            trackSearch(searchQuery, resultsCount);
        }
    }, 1000);
};

// Track category changes
const originalSetCategory = window.setCategory;
window.setCategory = function(category) {
    if (originalSetCategory) {
        originalSetCategory(category);
    }

    if (typeof trackCategoryClick === 'function') {
        const productCount = document.querySelectorAll('.product-card:not(.skeleton)').length;
        trackCategoryClick(category, productCount);
    }
};

// Track discount filter
const originalSetDiscount = window.setDiscount;
window.setDiscount = function(discount) {
    if (originalSetDiscount) {
        originalSetDiscount(discount);
    }

    if (typeof trackDiscountFilter === 'function') {
        trackDiscountFilter(discount);
    }
};

// Initialize tracking on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Add tracking to existing product cards
    setTimeout(addTrackingAttributesToCards, 1000);
});

// ==================== Sticky Header on Scroll ====================
(function() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    const stickyThreshold = 150;
    let lastScrollTop = 0;
    let ticking = false;

    function handleHeaderScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > stickyThreshold) {
            if (!header.classList.contains('header-sticky')) {
                header.classList.add('header-sticky');
                document.body.classList.add('header-is-sticky');
            }
        } else {
            if (header.classList.contains('header-sticky')) {
                header.classList.remove('header-sticky');
                document.body.classList.remove('header-is-sticky');
            }
        }

        lastScrollTop = scrollTop;
        ticking = false;
    }

    // Throttle scroll event with requestAnimationFrame
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                handleHeaderScroll();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Initial check
    handleHeaderScroll();
})();
