<?php
/**
 * Social Media Footer Component
 * Displays social media icons in the footer
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

<!-- Social Media Footer Section -->
<div class="social-footer" style="text-align: center; padding: 2rem 0; border-top: 1px solid #eee;">
    <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem; color: #333;">
        تابعنا على مواقع التواصل
    </h3>

    <div class="social-icons-footer" style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
        <?php if (!empty($facebookUrl)): ?>
        <a href="<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="social-icon-link facebook"
           onclick="return openSocialLink(event, '<?php echo htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8'); ?>', 'facebook')"
           aria-label="تابعنا على Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <?php endif; ?>

        <?php if (!empty($tiktokUrl)): ?>
        <a href="<?php echo htmlspecialchars($tiktokUrl, ENT_QUOTES, 'UTF-8'); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="social-icon-link tiktok"
           onclick="return openSocialLink(event, '<?php echo htmlspecialchars($tiktokUrl, ENT_QUOTES, 'UTF-8'); ?>', 'tiktok')"
           aria-label="تابعنا على TikTok">
            <i class="fab fa-tiktok"></i>
        </a>
        <?php endif; ?>

        <?php if (!empty($instagramUrl)): ?>
        <a href="<?php echo htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="social-icon-link instagram"
           onclick="return openSocialLink(event, '<?php echo htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8'); ?>', 'instagram')"
           aria-label="تابعنا على Instagram">
            <i class="fab fa-instagram"></i>
        </a>
        <?php endif; ?>
    </div>
</div>

<style>
/* Social Footer Icons Styling */
.social-icon-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: white;
    font-size: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
}

.social-icon-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.social-icon-link.facebook {
    background: #1877F2;
}

.social-icon-link.facebook:hover {
    background: #155db2;
}

.social-icon-link.tiktok {
    background: #000000;
}

.social-icon-link.tiktok:hover {
    background: #333333;
}

.social-icon-link.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-icon-link.instagram:hover {
    background: linear-gradient(45deg, #e08422 0%, #d5572b 25%, #cb1632 50%, #bb1255 75%, #ab0777 100%);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .social-footer h3 {
        font-size: 1.1rem;
    }

    .social-icon-link {
        width: 50px;
        height: 50px;
        font-size: 18px;
    }

    .social-icons-footer {
        gap: 15px;
    }
}
</style>
