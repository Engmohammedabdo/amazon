<?php
/**
 * Language Switcher Component
 * Shows language toggle buttons (Arabic/English)
 */

$currentLang = getCurrentLanguage();
?>

<div class="language-switcher" style="display: flex; gap: 0.5rem; align-items: center; position: relative; z-index: 1000;">
    <button
        type="button"
        class="lang-btn <?php echo $currentLang === 'ar' ? 'active' : ''; ?>"
        title="العربية"
        style="padding: 0.75rem 1.5rem; border: 2px solid #FF6B35; background: <?php echo $currentLang === 'ar' ? '#FF6B35' : '#f8f9fa'; ?>; color: <?php echo $currentLang === 'ar' ? 'white' : '#FF6B35'; ?>; border-radius: 6px; cursor: pointer; font-weight: 600; position: relative; z-index: 1001;">
        AR
    </button>
    <button
        type="button"
        class="lang-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>"
        title="English"
        style="padding: 0.75rem 1.5rem; border: 2px solid #FF6B35; background: <?php echo $currentLang === 'en' ? '#FF6B35' : '#f8f9fa'; ?>; color: <?php echo $currentLang === 'en' ? 'white' : '#FF6B35'; ?>; border-radius: 6px; cursor: pointer; font-weight: 600; position: relative; z-index: 1001;">
        EN
    </button>
</div>
