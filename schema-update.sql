-- Migra la base de datos para las nuevas mejoras.
-- Reemplaza el prefijo si tu instalación usa uno diferente.

ALTER TABLE `demo_markeplacev1_products`
  ADD COLUMN `price_retail` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  ADD COLUMN `price_wholesale` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  ADD COLUMN `show_retail` TINYINT(1) NOT NULL DEFAULT '1',
  ADD COLUMN `show_wholesale` TINYINT(1) NOT NULL DEFAULT '1',
  ADD COLUMN `allow_negative_stock` TINYINT(1) NOT NULL DEFAULT '0',
  ADD COLUMN `gallery_mode` VARCHAR(20) NOT NULL DEFAULT 'single';

ALTER TABLE `demo_markeplacev1_orders`
  ADD COLUMN `seller_id` INT NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `demo_markeplacev1_sellers` (
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
