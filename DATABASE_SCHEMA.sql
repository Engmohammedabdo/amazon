-- ==========================================
-- PYRASTORE - Amazon UAE Affiliate Website
-- Database Schema
-- ==========================================

-- جدول المنتجات الرئيسي
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `original_price` DECIMAL(10,2) DEFAULT NULL,
  `discount_percentage` INT(3) DEFAULT NULL,
  `currency` VARCHAR(10) DEFAULT 'AED',
  `category` ENUM('electronics', 'fashion', 'home', 'sports', 'beauty', 'books', 'toys', 'other') DEFAULT 'other',
  `affiliate_link` VARCHAR(1000) NOT NULL,
  `video_url` VARCHAR(500) DEFAULT NULL,
  `video_orientation` ENUM('portrait', 'landscape') DEFAULT 'landscape',
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الصور الإضافية للمنتجات
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `display_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المراجعات
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `reviewer_name` VARCHAR(100) NOT NULL,
  `rating` INT(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول أحداث التحليلات
CREATE TABLE IF NOT EXISTS `analytics_events` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_type` ENUM('page_view', 'product_click', 'purchase_button_click') NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `session_id` VARCHAR(100) NOT NULL,
  `user_agent` TEXT,
  `referrer` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_session_id` (`session_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول إعدادات الموقع
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مستخدمي لوحة التحكم
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- البيانات الافتراضية
-- ==========================================

-- إدراج الإعدادات الافتراضية
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('api_key', 'pyrastore-webhook-2025'),
('google_analytics_id', 'G-3TRP9PJ0GT'),
('gtm_container_id', ''),
('meta_pixel_id', ''),
('tiktok_pixel_id', ''),
('site_name', 'PYRASTORE'),
('site_tagline', 'UAE PICKS'),
('site_description', 'أفضل المنتجات من أمازون الإمارات بأسعار مميزة');

-- إدراج مستخدم Admin افتراضي
-- Username: admin
-- Password: admin123
INSERT INTO `admin_users` (`username`, `password_hash`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@pyrastore.com');

-- إدراج منتجات تجريبية
INSERT INTO `products` (`title`, `description`, `image_url`, `price`, `original_price`, `discount_percentage`, `category`, `affiliate_link`, `is_active`, `display_order`) VALUES
('سماعات لاسلكية بلوتوث - صوت نقي وجودة عالية', 'سماعات بلوتوث 5.0 مع خاصية إلغاء الضوضاء، بطارية تدوم حتى 30 ساعة، صوت نقي وواضح، مريحة للاستخدام طوال اليوم. مثالية للموسيقى والمكالمات.', 'https://m.media-amazon.com/images/I/61vFO3XcneL._AC_SL1500_.jpg', 149.00, 299.00, 50, 'electronics', 'https://www.amazon.ae/dp/example1', 1, 1),
('ساعة ذكية رياضية - تتبع اللياقة والصحة', 'ساعة ذكية مع مراقبة معدل ضربات القلب، تتبع النوم، عداد الخطوات، مقاومة للماء IP68، شاشة AMOLED ملونة، بطارية تدوم 7 أيام.', 'https://m.media-amazon.com/images/I/71VekEJaUvL._AC_SL1500_.jpg', 199.00, 399.00, 50, 'electronics', 'https://www.amazon.ae/dp/example2', 1, 2),
('حقيبة ظهر عصرية للسفر والعمل', 'حقيبة ظهر متعددة الاستخدامات مع منفذ USB للشحن، مقاومة للماء، جيوب متعددة منظمة، مريحة للظهر، سعة كبيرة تصل إلى 25 لتر.', 'https://m.media-amazon.com/images/I/81F6S8LCHFL._AC_SL1500_.jpg', 89.00, 179.00, 50, 'fashion', 'https://www.amazon.ae/dp/example3', 1, 3),
('خلاط كهربائي متعدد الاستخدامات', 'خلاط قوي 1000 واط، 3 سرعات، وعاء زجاجي 1.5 لتر، سهل التنظيف، مثالي للعصائر والشوربات والمشروبات الصحية.', 'https://m.media-amazon.com/images/I/61oXn8DT6xL._AC_SL1500_.jpg', 129.00, 249.00, 48, 'home', 'https://www.amazon.ae/dp/example4', 1, 4),
('مجموعة أوزان رياضية منزلية', 'مجموعة أوزان حرة قابلة للتعديل من 5 إلى 25 كجم، مع حامل معدني، مثالية للتمارين المنزلية وبناء العضلات.', 'https://m.media-amazon.com/images/I/71ZJqY0dJgL._AC_SL1500_.jpg', 299.00, 499.00, 40, 'sports', 'https://www.amazon.ae/dp/example5', 1, 5);

-- إدراج صور إضافية للمنتجات
INSERT INTO `product_images` (`product_id`, `image_url`, `display_order`) VALUES
(1, 'https://m.media-amazon.com/images/I/61vFO3XcneL._AC_SL1500_.jpg', 1),
(1, 'https://m.media-amazon.com/images/I/71TZ0pFxRrL._AC_SL1500_.jpg', 2),
(2, 'https://m.media-amazon.com/images/I/71VekEJaUvL._AC_SL1500_.jpg', 1),
(2, 'https://m.media-amazon.com/images/I/71k7CNASJBL._AC_SL1500_.jpg', 2);

-- إدراج مراجعات تجريبية
INSERT INTO `reviews` (`product_id`, `reviewer_name`, `rating`, `comment`) VALUES
(1, 'أحمد محمد', 5, 'سماعات رائعة جداً! الصوت نقي والبطارية تدوم طويلاً. أنصح بها بشدة.'),
(1, 'فاطمة علي', 5, 'جودة ممتازة وسعر مناسب. استخدمها يومياً ولم تخذلني أبداً.'),
(2, 'خالد السعيد', 4, 'ساعة ذكية جيدة جداً. التطبيق سهل الاستخدام والتتبع دقيق.'),
(2, 'نورة الزهراني', 5, 'أفضل ساعة ذكية اشتريتها! تساعدني على تتبع صحتي ونشاطي اليومي.'),
(3, 'سعيد المري', 5, 'حقيبة عملية جداً وجودة ممتازة. الجيوب المنظمة تسهل الوصول لكل شيء.'),
(4, 'ليلى حسن', 5, 'خلاط قوي ويستحق السعر. صنع العصائر أصبح سهل وسريع.'),
(5, 'عمر الكعبي', 4, 'أوزان ممتازة للتمارين المنزلية. جودة جيدة وسعر معقول.');
