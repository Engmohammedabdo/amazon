/**
 * Social Media Integration
 * Handles deep linking, sticky sidebar, and follow pop-up
 */

/**
 * Check if user is on mobile device
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Open social media link with deep linking support
 * @param {Event} event - Click event
 * @param {string} webUrl - Web URL (fallback)
 * @param {string} platform - Platform name (facebook, tiktok, instagram)
 */
function openSocialLink(event, webUrl, platform) {
    if (!isMobileDevice()) {
        // Desktop: just open web URL
        return true; // Let browser handle the link normally
    }

    // Mobile: try app deep link first
    event.preventDefault();

    const appUrl = getAppDeepLink(webUrl, platform);

    if (appUrl) {
        // Try to open app
        window.location.href = appUrl;

        // Fallback to web URL after delay if app doesn't open
        setTimeout(function() {
            window.open(webUrl, '_blank');
        }, 1500);

        return false;
    }

    // No app URL, just open web URL
    window.open(webUrl, '_blank');
    return false;
}

/**
 * Get app deep link URL based on platform
 * @param {string} webUrl - Web URL
 * @param {string} platform - Platform name
 * @returns {string|null} - App deep link or null
 */
function getAppDeepLink(webUrl, platform) {
    try {
        const url = new URL(webUrl);
        const pathname = url.pathname;

        switch (platform) {
            case 'facebook':
                // Extract page/profile from URL
                // Example: https://facebook.com/pyrastore -> fb://page/pyrastore
                const fbUsername = pathname.replace(/^\//, '').split('/')[0];
                if (fbUsername) {
                    // Try page first, then profile
                    return 'fb://page/' + fbUsername;
                }
                break;

            case 'instagram':
                // Example: https://instagram.com/pyrastore -> instagram://user?username=pyrastore
                const igUsername = pathname.replace(/^\//, '').split('/')[0];
                if (igUsername) {
                    return 'instagram://user?username=' + igUsername;
                }
                break;

            case 'tiktok':
                // Example: https://tiktok.com/@pyrastore -> tiktok://user?username=pyrastore
                const ttUsername = pathname.replace(/^\/@?/, '').split('/')[0];
                if (ttUsername) {
                    return 'tiktok://user?username=' + ttUsername;
                }
                break;
        }
    } catch (e) {
        console.error('Error parsing social media URL:', e);
    }

    return null;
}

// Export functions for use in onclick handlers
window.openSocialLink = openSocialLink;
window.isMobileDevice = isMobileDevice;
