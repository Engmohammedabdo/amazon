/**
 * UTM Parameter Tracker for PyraStore UAE
 * Preserves UTM parameters across pages and sends to Google Analytics
 */
(function() {
    'use strict';

    // Get UTM parameters from URL
    const params = new URLSearchParams(window.location.search);
    const utmParams = {};
    const utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    // Extract UTM parameters
    utmKeys.forEach(param => {
        if (params.has(param)) {
            const value = params.get(param);
            utmParams[param] = value;

            // Store in sessionStorage for persistence across pages
            sessionStorage.setItem(param, value);
        } else {
            // Try to retrieve from sessionStorage if not in URL
            const stored = sessionStorage.getItem(param);
            if (stored) {
                utmParams[param] = stored;
            }
        }
    });

    // Send UTM data to Google Analytics if available
    if (Object.keys(utmParams).length > 0 && typeof gtag !== 'undefined') {
        gtag('event', 'utm_tracking', {
            event_category: 'Campaign Tracking',
            utm_source: utmParams.utm_source || '(not set)',
            utm_medium: utmParams.utm_medium || '(not set)',
            utm_campaign: utmParams.utm_campaign || '(not set)',
            utm_content: utmParams.utm_content || '(not set)',
            utm_term: utmParams.utm_term || '(not set)'
        });

        // Log to console for debugging (remove in production)
        console.log('UTM Parameters tracked:', utmParams);
    }

    // Add UTM parameters to all internal links
    function addUtmToLinks() {
        const links = document.querySelectorAll('a[href^="/"], a[href^="' + window.location.origin + '"]');

        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href.includes('?')) return; // Skip if already has params

            const url = new URL(href, window.location.origin);

            // Add stored UTM params to internal links
            Object.keys(utmParams).forEach(key => {
                if (utmParams[key]) {
                    url.searchParams.set(key, utmParams[key]);
                }
            });

            link.setAttribute('href', url.toString());
        });
    }

    // Run on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addUtmToLinks);
    } else {
        addUtmToLinks();
    }

    // Expose function to get current UTM params
    window.getUtmParams = function() {
        return utmParams;
    };

})();
