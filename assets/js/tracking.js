/**
 * Enhanced Tracking System for PyraStore UAE
 * Tracks user interactions and sends to server + analytics platforms
 */

// Tracking configuration
const TRACKING_CONFIG = {
    apiUrl: '/api/track.php',
    gaEnabled: typeof gtag !== 'undefined',
    tiktokEnabled: typeof ttq !== 'undefined',
    metaEnabled: typeof fbq !== 'undefined'
};

/**
 * Get or create session ID
 */
function getSessionId() {
    let sessionId = localStorage.getItem('pyra_session');
    if (!sessionId) {
        sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('pyra_session', sessionId);
    }
    return sessionId;
}

/**
 * Send tracking event to server and analytics platforms
 */
function trackEvent(eventType, data = {}) {
    // Add common data
    data.event_type = eventType;
    data.session_id = getSessionId();
    data.language = document.documentElement.lang || 'ar';
    data.timestamp = new Date().toISOString();

    // Send to our API
    fetch(TRACKING_CONFIG.apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data),
        keepalive: true
    }).catch(err => console.log('Tracking error:', err));

    // Google Analytics
    if (TRACKING_CONFIG.gaEnabled) {
        gtag('event', eventType, {
            ...data,
            event_category: data.category || 'User Interaction'
        });
    }

    // TikTok Pixel
    if (TRACKING_CONFIG.tiktokEnabled) {
        ttq.track(getTikTokEventName(eventType), {
            content_id: data.product_id,
            content_name: data.product_title,
            value: data.price,
            currency: 'AED'
        });
    }

    // Meta Pixel
    if (TRACKING_CONFIG.metaEnabled) {
        fbq('track', getMetaEventName(eventType), {
            content_ids: data.product_id ? [data.product_id] : [],
            content_name: data.product_title,
            value: data.price,
            currency: 'AED'
        });
    }
}

/**
 * Map our events to TikTok event names
 */
function getTikTokEventName(eventType) {
    const mapping = {
        'affiliate_click': 'ClickButton',
        'product_view': 'ViewContent',
        'buy_now_click': 'AddToCart',
        'purchase_button_click': 'InitiateCheckout',
        'search': 'Search',
        'page_view': 'PageView'
    };
    return mapping[eventType] || 'CompleteRegistration';
}

/**
 * Map our events to Meta event names
 */
function getMetaEventName(eventType) {
    const mapping = {
        'affiliate_click': 'InitiateCheckout',
        'product_view': 'ViewContent',
        'buy_now_click': 'AddToCart',
        'purchase_button_click': 'InitiateCheckout',
        'search': 'Search',
        'page_view': 'PageView'
    };
    return mapping[eventType] || 'CustomEvent';
}

/**
 * Track affiliate link clicks (Buy Now button)
 */
function trackAffiliateClick(productId, productTitle, price, category, affiliateLink) {
    trackEvent('affiliate_click', {
        product_id: productId,
        product_title: productTitle,
        price: price,
        category: category || 'Product',
        metadata: {
            affiliate_link: affiliateLink,
            button_type: 'buy_now'
        }
    });
}

/**
 * Track product views
 */
function trackProductView(productId, productTitle, price, category) {
    trackEvent('product_view', {
        product_id: productId,
        product_title: productTitle,
        price: price,
        category: category || 'Product'
    });
}

/**
 * Track product page views (on product.php)
 */
function trackProductPageView(productId, productTitle, price) {
    trackEvent('product_page_view', {
        product_id: productId,
        product_title: productTitle,
        price: price,
        category: 'Product Page'
    });
}

/**
 * Track checkout intent (from tracking.php pixels)
 */
function trackCheckoutIntent(productId, productTitle, price) {
    trackEvent('purchase_button_click', {
        product_id: productId,
        product_title: productTitle,
        price: price,
        category: 'Conversion'
    });
}

/**
 * Track search
 */
function trackSearch(query, resultsCount) {
    trackEvent('search', {
        category: 'Search',
        metadata: {
            query: query,
            results_count: resultsCount
        }
    });
}

/**
 * Track filter changes
 */
function trackFilter(filterType, filterValue) {
    trackEvent('filter_change', {
        category: 'Filter',
        metadata: {
            filter_type: filterType,
            filter_value: filterValue
        }
    });
}

/**
 * Track language switch
 */
function trackLanguageSwitch(fromLang, toLang) {
    trackEvent('language_switch', {
        category: 'Language',
        metadata: {
            from: fromLang,
            to: toLang
        }
    });
}

/**
 * Track category selection
 */
function trackCategoryClick(category, productCount) {
    trackEvent('category_click', {
        category: 'Navigation',
        metadata: {
            selected_category: category,
            product_count: productCount
        }
    });
}

/**
 * Track discount filter
 */
function trackDiscountFilter(discountPercent) {
    trackEvent('discount_filter', {
        category: 'Filter',
        metadata: {
            discount_percent: discountPercent
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Enhanced tracking initialized');

    // Track page view
    trackEvent('page_view', {
        category: 'Navigation',
        metadata: {
            page_url: window.location.href,
            page_title: document.title
        }
    });
});
