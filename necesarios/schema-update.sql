-- Migra la base de datos para las nuevas mejoras.
-- Reemplaza el prefijo si tu instalación usa uno diferente.

ALTER TABLE `no_borrar_markeplacev1_products`
  ADD COLUMN `price_retail` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  ADD COLUMN `price_wholesale` DECIMAL(10,2) NOT NULL DEFAULT '1',
  ADD COLUMN `show_retail` TINYINT(1) NOT NULL DEFAULT '1',
  ADD COLUMN `show_wholesale` TINYINT(1) NOT NULL DEFAULT '1',
  ADD COLUMN `allow_negative_stock` TINYINT(1) NOT NULL DEFAULT '0',
  ADD COLUMN `gallery_mode` VARCHAR(20) NOT NULL DEFAULT 'single',
  ADD COLUMN `delivery_enabled` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `no_borrar_markeplacev1_orders`
  ADD COLUMN `seller_id` INT NULL DEFAULT NULL,
  ADD COLUMN `delivery_method` VARCHAR(50) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `no_borrar_markeplacev1_sellers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `link_token` VARCHAR(80) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  UNIQUE KEY `idx_link_token` (`link_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `no_borrar_markeplacev1_testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `photo` VARCHAR(255) NOT NULL,
  `approved` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_approved` (`approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `no_borrar_markeplacev1_exchange_rates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `date` DATE NOT NULL,
  `usd_to_ves` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
