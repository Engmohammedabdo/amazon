<?php
/**
 * Social Media Sticky Sidebar Component
 * Displays floating social media icons on desktop (hides on mobile)
 */

if (!function_exists('getSetting')) {
    require_once __DIR__ . '/functions.php';
}

// Get social media URLs from database
$facebookUrl = getSetting('facebook_url');
$tiktokUrl = getSetting('tiktok_url');
$instagramUrl = getSetting('instagram_url');

// Only show if at least one social URL is configured
$hasSocialMedia = !empty($facebookUrl) || !empty($tiktokUrl) || !empty($instagramUrl);

if (!$hasSocialMedia) {
    return; // Don't display if no URLs configured
}
?>

<!-- Social Media Sticky Sidebar (Desktop Only) -->
<div class="social-sticky-sidebar" id="socialStickySidebar">
    <?php if (!empty($facebookUrl)): ?>
    <a href="<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>"
       target="_blank"
       rel="noopener noreferrer"
       class="social-sticky-icon facebook"
       onclick="return openSocialLink(event, '<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>', 'facebook')"
       aria-label="تابعنا على Facebook"
       title="Facebook">
        <i class="fab fa-facebook-f"></i>
    </a>
    <?php endif; ?>

    <?php if (!empty($tiktokUrl)): ?>
    <a href="<?php echo htmlspecialchars($tiktokUrl, ENT_QUOTES, 'UTF-8'); ?>"
       target="_blank"
       rel="noopener noreferrer"
       class="social-sticky-icon tiktok"
       onclick="return openSocialLink(event, '<?php echo htmlspecialchars($tiktokUrl, ENT_QUOTES, 'UTF-8'); ?>', 'tiktok')"
       aria-label="تابعنا على TikTok"
       title="TikTok">
        <i class="fab fa-tiktok"></i>
    </a>
    <?php endif; ?>

    <?php if (!empty($instagramUrl)): ?>
    <a href="<?php echo htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>"
       target="_blank"
       rel="noopener noreferrer"
       class="social-sticky-icon instagram"
       onclick="return openSocialLink(event, '<?php echo htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>', 'instagram')"
       aria-label="تابعنا على Instagram"
       title="Instagram">
        <i class="fab fa-instagram"></i>
    </a>
    <?php endif; ?>
</div>

<style>
/* Social Sticky Sidebar Styling */
.social-sticky-sidebar {
    position: fixed;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 15px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}

.social-sticky-sidebar.visible {
    opacity: 1;
    visibility: visible;
}

.social-sticky-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    font-size: 22px;
    text-decoration: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.social-sticky-icon:hover {
    transform: scale(1.15);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.social-sticky-icon.facebook {
    background: #1877F2;
}

.social-sticky-icon.tiktok {
    background: #000000;
}

.social-sticky-icon.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

/* Hide on mobile (< 768px) */
@media (max-width: 768px) {
    .social-sticky-sidebar {
        display: none !important;
    }
}

/* Tablet adjustments */
@media (max-width: 1024px) and (min-width: 769px) {
    .social-sticky-sidebar {
        right: 10px;
    }

    .social-sticky-icon {
        width: 45px;
        height: 45px;
        font-size: 20px;
    }
}
</style>

<script>
// Show/hide sticky sidebar on scroll
(function() {
    const stickySidebar = document.getElementById('socialStickySidebar');

    if (!stickySidebar) return;

    let lastScrollTop = 0;

    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // Show after scrolling down 200px
        if (scrollTop > 200) {
            stickySidebar.classList.add('visible');
        } else {
            stickySidebar.classList.remove('visible');
        }

        lastScrollTop = scrollTop;
    }

    // Throttle scroll event for better performance
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Initial check
    handleScroll();
})();
</script>
