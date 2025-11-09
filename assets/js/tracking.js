/**
 * PyraStore Tracking System
 * Handles click tracking and analytics
 */

class PyraTracker {
    constructor() {
        this.sessionId = this.getOrCreateSessionId();
        this.apiUrl = '/api/tracking.php';
    }

    // Get or create session ID
    getOrCreateSessionId() {
        let sessionId = localStorage.getItem('pyra_session_id');
        if (!sessionId) {
            sessionId = this.generateSessionId();
            localStorage.setItem('pyra_session_id', sessionId);
        }
        return sessionId;
    }

    // Generate unique session ID
    generateSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Track event
    async track(productId, clickType) {
        const data = {
            session_id: this.sessionId,
            product_id: productId,
            click_type: clickType, // product_view, product_click, purchase_click
            utm_source: this.getUrlParam('utm_source'),
            utm_medium: this.getUrlParam('utm_medium'),
            utm_campaign: this.getUrlParam('utm_campaign')
        };

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Tracking error:', error);
            return null;
        }
    }

    // Get URL parameter
    getUrlParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // Track product view
    trackProductView(productId) {
        return this.track(productId, 'product_view');
    }

    // Track product click
    trackProductClick(productId) {
        return this.track(productId, 'product_click');
    }

    // Track purchase click (when user clicks to Amazon)
    trackPurchaseClick(productId) {
        return this.track(productId, 'purchase_click');
    }
}

// Initialize tracker
const tracker = new PyraTracker();

// Track purchase clicks on Amazon links
function trackAndRedirect(productId, amazonUrl) {
    tracker.trackPurchaseClick(productId);

    // Fire Google Analytics event
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase_click', {
            'product_id': productId,
            'url': amazonUrl
        });
    }

    // Fire Meta Pixel event
    if (typeof fbq !== 'undefined') {
        fbq('track', 'InitiateCheckout', {
            content_ids: [productId],
            content_type: 'product'
        });
    }

    // Fire TikTok Pixel event
    if (typeof ttq !== 'undefined') {
        ttq.track('InitiateCheckout', {
            content_id: productId,
            content_type: 'product'
        });
    }

    // Redirect to Amazon
    window.open(amazonUrl, '_blank');
}
