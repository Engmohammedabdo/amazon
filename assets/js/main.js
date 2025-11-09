/**
 * PyraStore Main JavaScript
 */

// Current language
let currentLang = localStorage.getItem('pyra_lang') || 'ar';

// Set language
function setLanguage(lang) {
    currentLang = lang;
    localStorage.setItem('pyra_lang', lang);
    location.reload();
}

// Load products
async function loadProducts(filters = {}) {
    const params = new URLSearchParams(filters);

    try {
        const response = await fetch(`/api/products.php?${params}`);
        const result = await response.json();

        if (result.success) {
            displayProducts(result.data);
            displayPagination(result.pagination);
            updateResultsCount(result.pagination.total);
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Display products
function displayProducts(products) {
    const container = document.getElementById('products-container');
    if (!container) return;

    if (products.length === 0) {
        container.innerHTML = `
            <div class="no-products">
                <div class="no-products-icon">📦</div>
                <h3>${currentLang === 'ar' ? 'لا توجد منتجات' : 'No Products Found'}</h3>
                <p>${currentLang === 'ar' ? 'جرب تغيير الفلاتر' : 'Try changing your filters'}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = products.map(product => {
        const title = currentLang === 'ar' ? product.title_ar : product.title_en;
        const description = currentLang === 'ar' ? product.description_ar : product.description_en;

        return `
            <div class="product-card" data-product-id="${product.id}">
                ${product.discount_percentage > 0 ? `
                    <div class="product-badge discount-badge">
                        -${product.discount_percentage}%
                    </div>
                ` : ''}

                ${product.is_featured ? `
                    <div class="product-badge featured-badge">
                        ⭐ ${currentLang === 'ar' ? 'مميز' : 'Featured'}
                    </div>
                ` : ''}

                <div class="product-image">
                    <img src="${product.primary_image || '/assets/images/placeholder.png'}"
                         alt="${title}"
                         loading="lazy"
                         onerror="this.src='/assets/images/placeholder.png'">
                </div>

                <div class="product-info">
                    <div class="product-category">${getCategoryName(product.category)}</div>
                    <h3 class="product-title">${title}</h3>

                    ${product.rating > 0 ? `
                        <div class="product-rating">
                            ${getStarRating(product.rating)}
                            <span class="rating-count">(${product.reviews_count})</span>
                        </div>
                    ` : ''}

                    <div class="product-price-section">
                        ${product.original_price > product.price ? `
                            <span class="original-price">${formatPrice(product.original_price)}</span>
                        ` : ''}
                        <span class="current-price">${formatPrice(product.price)}</span>
                    </div>

                    <div class="product-actions">
                        <button class="btn-view-details" onclick="viewProduct(${product.id})">
                            <span class="btn-icon">👁️</span>
                            <span>${currentLang === 'ar' ? 'التفاصيل' : 'View Details'}</span>
                        </button>

                        <button class="btn-buy-now" onclick="trackAndRedirect(${product.id}, '${product.affiliate_link}')">
                            <span class="btn-icon">🛒</span>
                            <span>${currentLang === 'ar' ? 'اشتري الآن' : 'Buy Now'}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    // Track product views
    products.forEach(product => {
        tracker.trackProductView(product.id);
    });
}

// Get star rating HTML
function getStarRating(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    let stars = '';

    for (let i = 0; i < fullStars; i++) {
        stars += '<span class="star filled">⭐</span>';
    }

    if (hasHalfStar && fullStars < 5) {
        stars += '<span class="star half">⭐</span>';
    }

    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        stars += '<span class="star empty">☆</span>';
    }

    return `<div class="stars">${stars}</div><span class="rating-value">${rating}</span>`;
}

// Format price
function formatPrice(price) {
    if (currentLang === 'ar') {
        return `${parseFloat(price).toFixed(2)} درهم`;
    } else {
        return `AED ${parseFloat(price).toFixed(2)}`;
    }
}

// Get category name
function getCategoryName(slug) {
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

    return categories[slug] ? categories[slug][currentLang] : slug;
}

// View product details
function viewProduct(productId) {
    tracker.trackProductClick(productId);
    window.location.href = `/product.php?id=${productId}`;
}

// Filter products
function filterProducts() {
    const category = document.getElementById('category-filter')?.value || '';
    const search = document.getElementById('search-input')?.value || '';
    const minPrice = document.getElementById('min-price')?.value || '';
    const maxPrice = document.getElementById('max-price')?.value || '';
    const minDiscount = document.getElementById('discount-filter')?.value || '';
    const sort = document.getElementById('sort-select')?.value || 'newest';

    const filters = {};
    if (category) filters.category = category;
    if (search) filters.search = search;
    if (minPrice) filters.min_price = minPrice;
    if (maxPrice) filters.max_price = maxPrice;
    if (minDiscount) filters.min_discount = minDiscount;
    if (sort) filters.sort = sort;

    loadProducts(filters);
}

// Reset filters
function resetFilters() {
    document.getElementById('category-filter').value = '';
    document.getElementById('search-input').value = '';
    document.getElementById('min-price').value = '';
    document.getElementById('max-price').value = '';
    document.getElementById('discount-filter').value = '';
    document.getElementById('sort-select').value = 'newest';

    loadProducts();
}

// Update results count
function updateResultsCount(total) {
    const countElement = document.getElementById('results-count');
    if (countElement) {
        const text = currentLang === 'ar'
            ? `تم العثور على ${total} منتج`
            : `${total} Products Found`;
        countElement.textContent = text;
    }
}

// Display pagination
function displayPagination(pagination) {
    const container = document.getElementById('pagination');
    if (!container || pagination.pages <= 1) {
        if (container) container.innerHTML = '';
        return;
    }

    let html = '<div class="pagination-buttons">';

    // Previous button
    if (pagination.page > 1) {
        html += `<button class="page-btn" onclick="loadPage(${pagination.page - 1})">
            ${currentLang === 'ar' ? '←' : '→'}
        </button>`;
    }

    // Page numbers
    const startPage = Math.max(1, pagination.page - 2);
    const endPage = Math.min(pagination.pages, pagination.page + 2);

    if (startPage > 1) {
        html += `<button class="page-btn" onclick="loadPage(1)">1</button>`;
        if (startPage > 2) html += `<span class="page-dots">...</span>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="page-btn ${i === pagination.page ? 'active' : ''}"
                         onclick="loadPage(${i})">${i}</button>`;
    }

    if (endPage < pagination.pages) {
        if (endPage < pagination.pages - 1) html += `<span class="page-dots">...</span>`;
        html += `<button class="page-btn" onclick="loadPage(${pagination.pages})">${pagination.pages}</button>`;
    }

    // Next button
    if (pagination.page < pagination.pages) {
        html += `<button class="page-btn" onclick="loadPage(${pagination.page + 1})">
            ${currentLang === 'ar' ? '→' : '←'}
        </button>`;
    }

    html += '</div>';
    container.innerHTML = html;
}

// Load specific page
function loadPage(page) {
    const filters = getCurrentFilters();
    filters.page = page;
    loadProducts(filters);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Get current filters
function getCurrentFilters() {
    return {
        category: document.getElementById('category-filter')?.value || '',
        search: document.getElementById('search-input')?.value || '',
        min_price: document.getElementById('min-price')?.value || '',
        max_price: document.getElementById('max-price')?.value || '',
        min_discount: document.getElementById('discount-filter')?.value || '',
        sort: document.getElementById('sort-select')?.value || 'newest'
    };
}

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('/api/categories.php');
        const result = await response.json();

        if (result.success) {
            displayCategories(result.data);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Display categories
function displayCategories(categories) {
    const container = document.getElementById('categories-container');
    if (!container) return;

    container.innerHTML = categories.map(cat => {
        const name = currentLang === 'ar' ? cat.name_ar : cat.name_en;
        return `
            <button class="category-btn"
                    style="border-color: ${cat.color}; color: ${cat.color};"
                    onclick="filterByCategory('${cat.slug}')">
                <span class="category-icon">${cat.icon}</span>
                <span class="category-name">${name}</span>
                <span class="category-count">${cat.product_count}</span>
            </button>
        `;
    }).join('');
}

// Filter by category
function filterByCategory(slug) {
    document.getElementById('category-filter').value = slug;
    filterProducts();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();
});
