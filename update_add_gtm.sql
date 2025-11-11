-- ================================
-- ADD GOOGLE TAG MANAGER SUPPORT
-- ================================
-- Adds GTM Container ID to settings
-- Date: 2025-11-10
-- ================================

-- Add GTM container ID setting (empty by default)
INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES ('gtm_container_id', '')
ON DUPLICATE KEY UPDATE
    `setting_value` = COALESCE(setting_value, '');

-- Verify the addition
SELECT setting_key, setting_value
FROM site_settings
WHERE setting_key IN ('google_analytics_id', 'gtm_container_id', 'meta_pixel_id', 'tiktok_pixel_id')
ORDER BY setting_key;

-- ================================
-- EXECUTION INSTRUCTIONS
-- ================================
--
-- METHOD 1: MySQL Command Line
-- mysql -u your_username -p pyrastore_db < update_add_gtm.sql
--
-- METHOD 2: phpMyAdmin
-- 1. Login to phpMyAdmin
-- 2. Select 'pyrastore_db' database
-- 3. Click 'SQL' tab
-- 4. Copy and paste the INSERT statement above
-- 5. Click 'Go'
--
-- After update, configure GTM:
-- 1. Create container at: https://tagmanager.google.com
-- 2. Get Container ID: GTM-XXXXXXX
-- 3. Go to: /admin/settings.php
-- 4. Paste Container ID in "Google Tag Manager ID" field
-- 5. Follow GTM_SETUP_GUIDE.md for complete configuration
--
-- ================================
