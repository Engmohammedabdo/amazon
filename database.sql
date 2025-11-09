-- Amazon Affiliate Store Database
-- PyraStore UAE
-- Created: 2025-11-09

-- Create Database
CREATE DATABASE IF NOT EXISTS pyrastore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pyrastore_db;

-- ====================================
-- Products Table
-- ====================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(50) UNIQUE NOT NULL,
    title_ar VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    discount_percentage INT DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'AED',
    amazon_url TEXT NOT NULL,
    affiliate_link TEXT NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0,
    reviews_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    stock_status VARCHAR(50) DEFAULT 'in_stock',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_price (price),
    INDEX idx_discount (discount_percentage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Product Images Table
-- ====================================
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url TEXT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Categories Table
-- ====================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(100),
    color VARCHAR(50) DEFAULT '#FF9900',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Click Tracking Table
-- ====================================
CREATE TABLE IF NOT EXISTS click_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    click_type ENUM('product_view', 'product_click', 'purchase_click') NOT NULL,
    user_ip VARCHAR(45),
    user_agent TEXT,
    referrer TEXT,
    utm_source VARCHAR(100),
    utm_medium VARCHAR(100),
    utm_campaign VARCHAR(100),
    device_type VARCHAR(50),
    browser VARCHAR(100),
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_product (product_id),
    INDEX idx_click_type (click_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Reviews Table
-- ====================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_approved (is_approved),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Settings Table
-- ====================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Admin Users Table
-- ====================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Insert Default Categories
-- ====================================
INSERT INTO categories (name_ar, name_en, slug, icon, color, display_order) VALUES
('إلكترونيات', 'Electronics', 'electronics', '📱', '#3B82F6', 1),
('أزياء', 'Fashion', 'fashion', '👕', '#EC4899', 2),
('المنزل والمطبخ', 'Home & Kitchen', 'home-kitchen', '🏠', '#10B981', 3),
('الجمال والعناية', 'Beauty & Care', 'beauty-care', '💄', '#F59E0B', 4),
('رياضة ولياقة', 'Sports & Fitness', 'sports-fitness', '⚽', '#8B5CF6', 5),
('ألعاب وهدايا', 'Toys & Gifts', 'toys-gifts', '🎁', '#EF4444', 6),
('كتب وقرطاسية', 'Books & Stationery', 'books-stationery', '📚', '#6366F1', 7),
('سيارات وإكسسوارات', 'Automotive', 'automotive', '🚗', '#14B8A6', 8);

-- ====================================
-- Insert Default Settings
-- ====================================
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name_ar', 'متجر بيرا - الإمارات', 'text'),
('site_name_en', 'PyraStore - UAE', 'text'),
('affiliate_id', 'pyrastore-21', 'text'),
('amazon_domain', 'amazon.ae', 'text'),
('google_analytics_id', '', 'text'),
('meta_pixel_id', '', 'text'),
('tiktok_pixel_id', '', 'text'),
('currency', 'AED', 'text'),
('language_default', 'ar', 'text'),
('products_per_page', '12', 'number'),
('featured_products_count', '8', 'number'),
('enable_reviews', '1', 'boolean'),
('auto_approve_reviews', '0', 'boolean'),
('contact_email', 'info@pyrastore.com', 'email'),
('whatsapp_number', '', 'text');

-- ====================================
-- Insert Default Admin User
-- Password: admin123 (hashed with PASSWORD_DEFAULT)
-- ====================================
INSERT INTO admin_users (username, password, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@pyrastore.com', 'Administrator');

-- ====================================
-- Insert Sample Products
-- ====================================
INSERT INTO products (product_id, title_ar, title_en, description_ar, description_en, category, price, original_price, discount_percentage, amazon_url, affiliate_link, rating, reviews_count, is_featured) VALUES
('B0CQCVFHW2', 'سماعات بلوتوث لاسلكية', 'Wireless Bluetooth Headphones', 'سماعات بلوتوث عالية الجودة مع خاصية إلغاء الضوضاء', 'High-quality Bluetooth headphones with noise cancellation', 'electronics', 299.00, 499.00, 40, 'https://www.amazon.ae/dp/B0CQCVFHW2', 'https://www.amazon.ae/dp/B0CQCVFHW2?tag=pyrastore-21', 4.5, 150, TRUE);

-- ====================================
-- Views for Analytics
-- ====================================

-- Daily Stats View
CREATE OR REPLACE VIEW daily_stats AS
SELECT
    DATE(created_at) as date,
    COUNT(DISTINCT session_id) as unique_visitors,
    COUNT(*) as total_clicks,
    SUM(CASE WHEN click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
    SUM(CASE WHEN click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks
FROM click_tracking
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Product Performance View
CREATE OR REPLACE VIEW product_performance AS
SELECT
    p.id,
    p.product_id,
    p.title_en,
    p.title_ar,
    p.category,
    p.price,
    COUNT(DISTINCT ct.session_id) as unique_views,
    SUM(CASE WHEN ct.click_type = 'product_click' THEN 1 ELSE 0 END) as product_clicks,
    SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) as purchase_clicks,
    ROUND((SUM(CASE WHEN ct.click_type = 'purchase_click' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0)), 2) as conversion_rate
FROM products p
LEFT JOIN click_tracking ct ON p.id = ct.product_id
WHERE p.is_active = TRUE
GROUP BY p.id
ORDER BY purchase_clicks DESC, product_clicks DESC;
