/**
 * Floating Social Media Button (Mobile Only)
 * Expandable FAB with social media icons
 */

(function() {
    'use strict';

    // Check if on mobile
    function isMobile() {
        return window.innerWidth < 768;
    }

    // Only initialize if on mobile and social URLs exist
    if (!isMobile()) {
        return;
    }

    // State
    let isExpanded = false;

    // Create floating button HTML
    function createFloatingButton() {
        const fab = document.createElement('div');
        fab.id = 'socialFloatingBtn';
        fab.className = 'social-fab';
        fab.innerHTML = `
            <button class="social-fab-main" id="socialFabMain" aria-label="Share">
                <i class="fas fa-share-nodes"></i>
            </button>
            <div class="social-fab-menu" id="socialFabMenu">
                ${window.SOCIAL_URLS && window.SOCIAL_URLS.facebook ? `
                <a href="${window.SOCIAL_URLS.facebook}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="social-fab-item facebook"
                   onclick="return openSocialLink(event, '${window.SOCIAL_URLS.facebook}', 'facebook')"
                   aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>` : ''}
                ${window.SOCIAL_URLS && window.SOCIAL_URLS.tiktok ? `
                <a href="${window.SOCIAL_URLS.tiktok}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="social-fab-item tiktok"
                   onclick="return openSocialLink(event, '${window.SOCIAL_URLS.tiktok}', 'tiktok')"
                   aria-label="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>` : ''}
                ${window.SOCIAL_URLS && window.SOCIAL_URLS.instagram ? `
                <a href="${window.SOCIAL_URLS.instagram}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="social-fab-item instagram"
                   onclick="return openSocialLink(event, '${window.SOCIAL_URLS.instagram}', 'instagram')"
                   aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>` : ''}
            </div>
        `;

        document.body.appendChild(fab);

        // Add event listeners
        setupEventListeners();
    }

    // Toggle expanded state
    function toggleFab() {
        isExpanded = !isExpanded;
        const fab = document.getElementById('socialFloatingBtn');
        const mainBtn = document.getElementById('socialFabMain');
        const menu = document.getElementById('socialFabMenu');

        if (isExpanded) {
            fab.classList.add('expanded');
            mainBtn.innerHTML = '<i class="fas fa-times"></i>';
            menu.style.display = 'flex';
            // Trigger animation after display
            setTimeout(() => {
                menu.classList.add('visible');
            }, 10);
        } else {
            menu.classList.remove('visible');
            mainBtn.innerHTML = '<i class="fas fa-share-nodes"></i>';
            // Wait for animation to finish
            setTimeout(() => {
                menu.style.display = 'none';
                fab.classList.remove('expanded');
            }, 300);
        }
    }

    // Close FAB
    function closeFab() {
        if (isExpanded) {
            toggleFab();
        }
    }

    // Setup event listeners
    function setupEventListeners() {
        const mainBtn = document.getElementById('socialFabMain');
        const fab = document.getElementById('socialFloatingBtn');

        // Main button click
        mainBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleFab();
        });

        // Click outside to close
        document.addEventListener('click', function(e) {
            if (!fab.contains(e.target) && isExpanded) {
                closeFab();
            }
        });

        // Close on scroll
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (isExpanded) {
                    closeFab();
                }
            }, 100);
        });
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        const fab = document.getElementById('socialFloatingBtn');
        if (!isMobile() && fab) {
            fab.remove();
        } else if (isMobile() && !fab && window.SOCIAL_URLS) {
            createFloatingButton();
        }
    });

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createFloatingButton);
    } else {
        createFloatingButton();
    }

})();
