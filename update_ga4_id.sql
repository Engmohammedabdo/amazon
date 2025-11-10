-- ================================
-- UPDATE GOOGLE ANALYTICS 4 ID
-- ================================
-- New Measurement ID: G-3TRP9PJ0GT
-- Old IDs being replaced: G-GZTBRKFFGT, G-HRSW9RC061
-- Date: 2025-11-10
-- ================================

-- Update or insert the new GA4 measurement ID
INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES ('google_analytics_id', 'G-3TRP9PJ0GT')
ON DUPLICATE KEY UPDATE
    `setting_value` = 'G-3TRP9PJ0GT';

-- Verify the update
SELECT setting_key, setting_value
FROM site_settings
WHERE setting_key = 'google_analytics_id';

-- ================================
-- EXECUTION INSTRUCTIONS
-- ================================
--
-- METHOD 1: MySQL Command Line
-- mysql -u your_username -p pyrastore_db < update_ga4_id.sql
--
-- METHOD 2: phpMyAdmin
-- 1. Login to phpMyAdmin
-- 2. Select 'pyrastore_db' database
-- 3. Click 'SQL' tab
-- 4. Copy and paste the INSERT statement above
-- 5. Click 'Go'
--
-- METHOD 3: Admin Panel (Easiest)
-- 1. Go to: https://events.pyramedia.info/admin/settings.php
-- 2. Under "أدوات التتبع والتحليلات"
-- 3. Paste: G-3TRP9PJ0GT in the "Google Analytics ID" field
-- 4. Click "💾 حفظ الإعدادات"
--
-- ================================
