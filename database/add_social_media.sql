-- ================================
-- ADD SOCIAL MEDIA INTEGRATION
-- ================================
-- Adds social media URLs and pop-up settings
-- Date: 2025-11-11
-- ================================

-- Add social media URL settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('facebook_url', ''),
('tiktok_url', ''),
('instagram_url', ''),
('social_popup_enabled', '1'),
('social_popup_delay', '60'),
('social_popup_title', 'Stay Connected!'),
('social_popup_message', 'Follow us for exclusive deals')
ON DUPLICATE KEY UPDATE
    `setting_value` = VALUES(`setting_value`);

-- Verify the addition
SELECT setting_key, setting_value
FROM site_settings
WHERE setting_key IN ('facebook_url', 'tiktok_url', 'instagram_url', 'social_popup_enabled', 'social_popup_delay', 'social_popup_title', 'social_popup_message')
ORDER BY setting_key;

-- ================================
-- EXECUTION INSTRUCTIONS
-- ================================
--
-- METHOD 1: MySQL Command Line
-- mysql -u your_username -p pyrastore_db < database/add_social_media.sql
--
-- METHOD 2: phpMyAdmin
-- 1. Login to phpMyAdmin
-- 2. Select 'pyrastore_db' database
-- 3. Click 'SQL' tab
-- 4. Copy and paste the INSERT statement above
-- 5. Click 'Go'
--
-- METHOD 3: Admin Panel (After adding the tab)
-- 1. Go to: /admin/settings.php
-- 2. Click on "Social Media" tab
-- 3. Enter your social media URLs
-- 4. Configure pop-up settings
-- 5. Click "💾 حفظ الإعدادات"
--
-- ================================
-- SETTINGS EXPLAINED
-- ================================
--
-- facebook_url: Full Facebook profile/page URL
--   Example: https://facebook.com/pyrastore
--
-- tiktok_url: Full TikTok profile URL
--   Example: https://tiktok.com/@pyrastore
--
-- instagram_url: Full Instagram profile URL
--   Example: https://instagram.com/pyrastore
--
-- social_popup_enabled: Show/hide the follow pop-up
--   Values: 1 (enabled) or 0 (disabled)
--
-- social_popup_delay: Seconds before showing pop-up
--   Default: 60 (shows after 1 minute)
--   Range: 0-999 seconds
--
-- social_popup_title: Pop-up modal title
--   Default: "Stay Connected!"
--   Can be in Arabic or English
--
-- social_popup_message: Pop-up modal message
--   Default: "Follow us for exclusive deals"
--   Can be in Arabic or English
--
-- ================================
