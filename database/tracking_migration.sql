-- Enhanced Tracking Table for PyraStore UAE
-- Tracks all user interactions for analytics and conversion optimization

CREATE TABLE IF NOT EXISTS `click_tracking` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(50) NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `product_title` VARCHAR(255) DEFAULT NULL,
  `user_ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `referrer` TEXT DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `language` VARCHAR(5) DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `metadata` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
