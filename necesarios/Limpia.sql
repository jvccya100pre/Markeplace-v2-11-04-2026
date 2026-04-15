-- Backup generado el 2026-04-15 15:11:19

-- Estructura de la tabla `no_borrar_markeplacev1_categories`
CREATE TABLE `no_borrar_markeplacev1_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de la tabla `no_borrar_markeplacev1_categories`
INSERT INTO `no_borrar_markeplacev1_categories` VALUES ('1', 'Libros', '1');
INSERT INTO `no_borrar_markeplacev1_categories` VALUES ('2', 'Papeleria', '1');
INSERT INTO `no_borrar_markeplacev1_categories` VALUES ('3', 'General', '1');


-- Estructura de la tabla `no_borrar_markeplacev1_contact_messages`
CREATE TABLE `no_borrar_markeplacev1_contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `no_borrar_markeplacev1_exchange_rates`
CREATE TABLE `no_borrar_markeplacev1_exchange_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `usd_to_ves` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de la tabla `no_borrar_markeplacev1_exchange_rates`
INSERT INTO `no_borrar_markeplacev1_exchange_rates` VALUES ('1', '2026-04-14', '477.14', '2026-04-14 15:40:22', '2026-04-14 15:40:22');
INSERT INTO `no_borrar_markeplacev1_exchange_rates` VALUES ('2', '2026-04-15', '478.58', '2026-04-15 08:21:04', '2026-04-15 08:21:04');

-- Estructura de la tabla `no_borrar_markeplacev1_order_items`
CREATE TABLE `no_borrar_markeplacev1_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `no_borrar_markeplacev1_orders`
CREATE TABLE `no_borrar_markeplacev1_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'pendiente',
  `payment_method` varchar(40) NOT NULL,
  `delivery_method` varchar(60) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de la tabla `no_borrar_markeplacev1_orders`
INSERT INTO `no_borrar_markeplacev1_orders` VALUES ('1', '2', '0.00', 'pendiente', 'deposito bancario', 'personal', '', '2026-04-14 23:58:46', NULL);

-- Estructura de la tabla `no_borrar_markeplacev1_products`
CREATE TABLE `no_borrar_markeplacev1_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `internal_code` varchar(60) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `color` varchar(60) NOT NULL DEFAULT 'Surtido',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `price_retail` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_wholesale` decimal(10,2) NOT NULL DEFAULT 0.00,
  `show_retail` tinyint(1) NOT NULL DEFAULT 1,
  `show_wholesale` tinyint(1) NOT NULL DEFAULT 1,
  `allow_negative_stock` tinyint(1) NOT NULL DEFAULT 0,
  `gallery_mode` varchar(20) NOT NULL DEFAULT 'single',
  `delivery_enabled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_internal_code` (`internal_code`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `no_borrar_markeplacev1_sellers`
CREATE TABLE `no_borrar_markeplacev1_sellers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `username` varchar(100) NOT NULL,
  `link_token` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  UNIQUE KEY `idx_link_token` (`link_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de la tabla `no_borrar_markeplacev1_settings`
CREATE TABLE `no_borrar_markeplacev1_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de la tabla `no_borrar_markeplacev1_settings`
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('1', 'company_name', 'Tu Tienda Online LT', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('2', 'company_address', 'Calle. Miquilen, Los Teques, Venezuela', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('3', 'company_phone', '+5804120161515', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('4', 'company_email', 'tutiendaonline012@gmail.com', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('5', 'pm_bank', '0102 BANCO DE VENEZUELA S.A.I.C.A.', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('6', 'pm_identity_type', 'V', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('7', 'pm_identity_number', '', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('8', 'pm_phone_prefix', '+58', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('9', 'pm_phone_number', '', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('10', 'binance_uid', '', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_settings` VALUES ('11', 'paypal_email', '', '2026-04-08 12:06:16');

-- Estructura de la tabla `no_borrar_markeplacev1_shipping_profiles`
CREATE TABLE `no_borrar_markeplacev1_shipping_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `delivery_registration` varchar(60) NOT NULL,
  `phone_contact` varchar(60) NOT NULL,
  `email_contact` varchar(150) NOT NULL,
  `payment_method` varchar(60) NOT NULL,
  `delivery_method` varchar(60) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `no_borrar_markeplacev1_testimonials`
CREATE TABLE `no_borrar_markeplacev1_testimonials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `photo` varchar(255) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_approved` (`approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de la tabla `no_borrar_markeplacev1_users`
CREATE TABLE `no_borrar_markeplacev1_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','cliente') NOT NULL DEFAULT 'cliente',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de la tabla `no_borrar_markeplacev1_users`
INSERT INTO `no_borrar_markeplacev1_users` VALUES ('1', 'Administrador', 'jvczxc2021@gmail.com', '031b80c23a8c4d2a095f84acf64824535a12eb48', 'admin', '2026-04-08 12:06:16');
INSERT INTO `no_borrar_markeplacev1_users` VALUES ('2', 'Administrador', 'j.vczxc2021@gmail.com', '031b80c23a8c4d2a095f84acf64824535a12eb48', 'cliente', '2026-04-08 12:06:16');

-- Estructura de la tabla `no_borrar_markeplacev1_visits`
CREATE TABLE `no_borrar_markeplacev1_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `page_visited` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1569 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos de la tabla `no_borrar_markeplacev1_visits`
