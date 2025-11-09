<?php
/**
 * Language Switcher Component
 * Shows language toggle buttons (Arabic/English)
 */

$currentLang = getCurrentLanguage();
?>

<div class="language-switcher" style="display: flex; gap: 0.5rem; align-items: center;">
    <button
        class="lang-btn <?php echo $currentLang === 'ar' ? 'active' : ''; ?>"
        onclick="switchLanguage('ar')"
        title="العربية"
        style="padding: 0.5rem 1rem; border: 2px solid var(--primary-color); background: <?php echo $currentLang === 'ar' ? 'var(--primary-color)' : 'white'; ?>; color: <?php echo $currentLang === 'ar' ? 'white' : 'var(--primary-color)'; ?>; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
        AR
    </button>
    <button
        class="lang-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>"
        onclick="switchLanguage('en')"
        title="English"
        style="padding: 0.5rem 1rem; border: 2px solid var(--primary-color); background: <?php echo $currentLang === 'en' ? 'var(--primary-color)' : 'white'; ?>; color: <?php echo $currentLang === 'en' ? 'white' : 'var(--primary-color)'; ?>; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
        EN
    </button>
</div>

<script>
function switchLanguage(lang) {
    // Set cookie for 1 year
    const expiryDate = new Date();
    expiryDate.setFullYear(expiryDate.getFullYear() + 1);
    document.cookie = `site_language=${lang}; path=/; expires=${expiryDate.toUTCString()}; SameSite=Lax`;

    // Reload page to apply language change
    window.location.reload();
}
</script>
