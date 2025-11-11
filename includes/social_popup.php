<?php
/**
 * Social Media Follow Pop-up Modal
 * Displays a pop-up encouraging users to follow on social media
 */

if (!function_exists('getSetting')) {
    require_once __DIR__ . '/functions.php';
}

// Get pop-up settings from database
$popupEnabled = getSetting('social_popup_enabled', '1');
$popupDelay = getSetting('social_popup_delay', '60');
$popupTitle = getSetting('social_popup_title', 'Stay Connected!');
$popupMessage = getSetting('social_popup_message', 'Follow us for exclusive deals');

// Get social media URLs
$facebookUrl = getSetting('facebook_url');
$tiktokUrl = getSetting('tiktok_url');
$instagramUrl = getSetting('instagram_url');

// Only show if enabled and at least one social URL is configured
$hasSocialMedia = !empty($facebookUrl) || !empty($tiktokUrl) || !empty($instagramUrl);

if ($popupEnabled !== '1' || !$hasSocialMedia) {
    return; // Don't display if disabled or no URLs configured
}
?>

<!-- Social Media Follow Pop-up Modal -->
<div class="social-popup-overlay" id="socialPopupOverlay">
    <div class="social-popup-modal">
        <button class="social-popup-close" onclick="closeSocialPopup()" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>

        <h2 class="social-popup-title"><?php echo htmlspecialchars($popupTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="social-popup-message"><?php echo htmlspecialchars($popupMessage, ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="social-popup-icons">
            <?php if (!empty($facebookUrl)): ?>
            <a href="<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="social-popup-icon facebook"
               onclick="return openSocialLink(event, '<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>', 'facebook')"
               aria-label="تابعنا على Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <?php endif; ?>

            <?php if (!empty($tiktokUrl)): ?>
            <a href="<?php echo htmlspecialchars($tiktokUrl, ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="social-popup-icon tiktok"
               onclick="return openSocialLink(event, '<?php echo htmlspecialchars($tiktokUrl, ENT_QUOTES, 'UTF-8'); ?>', 'tiktok')"
               aria-label="تابعنا على TikTok">
                <i class="fab fa-tiktok"></i>
            </a>
            <?php endif; ?>

            <?php if (!empty($instagramUrl)): ?>
            <a href="<?php echo htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="social-popup-icon instagram"
               onclick="return openSocialLink(event, '<?php echo htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>', 'instagram')"
               aria-label="تابعنا على Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <?php endif; ?>
        </div>

        <label class="social-popup-checkbox">
            <input type="checkbox" id="socialPopupDontShow" onchange="handleDontShowAgain(this)">
            <span>Don't show this again</span>
        </label>
    </div>
</div>

<style>
/* Social Pop-up Modal Styling */
.social-popup-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.social-popup-overlay.show {
    display: flex;
    opacity: 1;
}

.social-popup-modal {
    background: white;
    border-radius: 12px;
    padding: 30px;
    width: 90%;
    max-width: 400px;
    position: relative;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideInScale 0.4s ease;
    text-align: center;
}

@keyframes slideInScale {
    from {
        transform: scale(0.8) translateY(-20px);
        opacity: 0;
    }
    to {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}

.social-popup-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: transparent;
    border: none;
    font-size: 24px;
    color: #999;
    cursor: pointer;
    padding: 5px;
    line-height: 1;
    transition: color 0.2s ease, transform 0.2s ease;
}

.social-popup-close:hover {
    color: #333;
    transform: rotate(90deg);
}

.social-popup-title {
    margin: 0 0 15px 0;
    font-size: 1.5rem;
    color: #333;
    font-weight: 700;
}

.social-popup-message {
    margin: 0 0 25px 0;
    font-size: 1rem;
    color: #666;
    line-height: 1.5;
}

.social-popup-icons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.social-popup-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    color: white;
    font-size: 26px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.social-popup-icon:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.social-popup-icon.facebook {
    background: #1877F2;
}

.social-popup-icon.tiktok {
    background: #000000;
}

.social-popup-icon.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-popup-checkbox {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #666;
    cursor: pointer;
    user-select: none;
}

.social-popup-checkbox input[type="checkbox"] {
    cursor: pointer;
    width: 18px;
    height: 18px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .social-popup-modal {
        width: 90%;
        padding: 25px 20px;
    }

    .social-popup-title {
        font-size: 1.3rem;
    }

    .social-popup-message {
        font-size: 0.95rem;
    }

    .social-popup-icon {
        width: 55px;
        height: 55px;
        font-size: 24px;
    }

    .social-popup-icons {
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .social-popup-icon {
        width: 50px;
        height: 50px;
        font-size: 22px;
    }
}
</style>

<script>
(function() {
    const STORAGE_KEY = 'socialPopupDismissed';
    const popupDelay = <?php echo intval($popupDelay); ?> * 1000; // Convert to milliseconds

    let popupShown = false;

    // Check if user has dismissed the popup before
    function shouldShowPopup() {
        // Check localStorage
        const dismissed = localStorage.getItem(STORAGE_KEY);
        if (dismissed === 'true') {
            return false;
        }

        // Check session (only show once per session)
        if (sessionStorage.getItem('socialPopupShownThisSession') === 'true') {
            return false;
        }

        return true;
    }

    // Show the popup
    function showPopup() {
        if (popupShown || !shouldShowPopup()) {
            return;
        }

        const overlay = document.getElementById('socialPopupOverlay');
        if (overlay) {
            overlay.classList.add('show');
            popupShown = true;
            sessionStorage.setItem('socialPopupShownThisSession', 'true');
        }
    }

    // Close the popup
    window.closeSocialPopup = function() {
        const overlay = document.getElementById('socialPopupOverlay');
        if (overlay) {
            overlay.classList.remove('show');
        }
    };

    // Handle "Don't show again" checkbox
    window.handleDontShowAgain = function(checkbox) {
        if (checkbox.checked) {
            localStorage.setItem(STORAGE_KEY, 'true');
        } else {
            localStorage.removeItem(STORAGE_KEY);
        }
    };

    // Close popup when clicking outside modal
    document.getElementById('socialPopupOverlay')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeSocialPopup();
        }
    });

    // Show popup after delay
    if (shouldShowPopup()) {
        setTimeout(showPopup, popupDelay);
    }
})();
</script>
