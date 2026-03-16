CREATE DATABASE IF NOT EXISTS `doorlockshop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `doorlockshop`;

-- =============================================
-- Пользователи и авторизация
-- =============================================

CREATE TABLE `users` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`           VARCHAR(255) NOT NULL UNIQUE,
    `phone`           VARCHAR(20)  DEFAULT NULL,
    `password_hash`   VARCHAR(255) NOT NULL,
    `first_name`      VARCHAR(100) NOT NULL,
    `last_name`       VARCHAR(100) DEFAULT NULL,
    `avatar`          VARCHAR(255) DEFAULT NULL,
    `role`            ENUM('customer','manager','admin') NOT NULL DEFAULT 'customer',
    `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
    `email_verified`  TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phone`)
) ENGINE=InnoDB;

CREATE TABLE `user_sessions` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT UNSIGNED NOT NULL,
    `token`        VARCHAR(255) NOT NULL UNIQUE,
    `ip_address`   VARCHAR(45)  DEFAULT NULL,
    `user_agent`   TEXT         DEFAULT NULL,
    `expires_at`   TIMESTAMP    NOT NULL,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB;

-- =============================================
-- Каталог: категории
-- =============================================

CREATE TABLE `categories` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `parent_id`    INT UNSIGNED DEFAULT NULL,
    `name`         VARCHAR(255) NOT NULL,
    `slug`         VARCHAR(255) NOT NULL UNIQUE,
    `description`  TEXT         DEFAULT NULL,
    `image`        VARCHAR(255) DEFAULT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_parent` (`parent_id`)
) ENGINE=InnoDB;

-- =============================================
-- Каталог: бренды
-- =============================================

CREATE TABLE `brands` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(255) NOT NULL,
    `slug`         VARCHAR(255) NOT NULL UNIQUE,
    `logo`         VARCHAR(255) DEFAULT NULL,
    `description`  TEXT         DEFAULT NULL,
    `website`      VARCHAR(255) DEFAULT NULL,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB;

-- =============================================
-- Каталог: товары
-- =============================================

CREATE TABLE `products` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id`     INT UNSIGNED NOT NULL,
    `brand_id`        INT UNSIGNED DEFAULT NULL,
    `name`            VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(255) NOT NULL UNIQUE,
    `sku`             VARCHAR(50)  DEFAULT NULL UNIQUE,
    `short_desc`      VARCHAR(500) DEFAULT NULL,
    `description`     TEXT         DEFAULT NULL,
    `price`           DECIMAL(10,2) NOT NULL,
    `old_price`       DECIMAL(10,2) DEFAULT NULL,
    `cost_price`      DECIMAL(10,2) DEFAULT NULL,
    `stock`           INT          NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
    `is_featured`     TINYINT(1)   NOT NULL DEFAULT 0,
    `is_new`          TINYINT(1)   NOT NULL DEFAULT 0,
    `weight`          DECIMAL(8,2) DEFAULT NULL COMMENT 'Вес в граммах',
    `views_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `sales_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `rating_avg`      DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    `rating_count`    INT UNSIGNED NOT NULL DEFAULT 0,
    `meta_title`      VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_category` (`category_id`),
    INDEX `idx_brand` (`brand_id`),
    INDEX `idx_price` (`price`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_active_price` (`is_active`, `price`),
    FULLTEXT `ft_search` (`name`, `short_desc`, `description`)
) ENGINE=InnoDB;

CREATE TABLE `product_images` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`   INT UNSIGNED NOT NULL,
    `path`         VARCHAR(255) NOT NULL,
    `alt`          VARCHAR(255) DEFAULT NULL,
    `sort_order`   INT          NOT NULL DEFAULT 0,
    `is_main`      TINYINT(1)   NOT NULL DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB;

-- =============================================
-- Заказы
-- =============================================

CREATE TABLE `delivery_methods` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(255) NOT NULL,
    `slug`         VARCHAR(100) NOT NULL UNIQUE,
    `description`  TEXT         DEFAULT NULL,
    `price`        DECIMAL(10,2) NOT NULL DEFAULT 0,
    `free_from`    DECIMAL(10,2) DEFAULT NULL COMMENT 'Бесплатно от суммы',
    `min_days`     INT          DEFAULT NULL,
    `max_days`     INT          DEFAULT NULL,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`   INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE `payment_methods` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(255) NOT NULL,
    `slug`         VARCHAR(100) NOT NULL UNIQUE,
    `description`  TEXT         DEFAULT NULL,
    `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`   INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE `orders` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`           INT UNSIGNED DEFAULT NULL,
    `order_number`      VARCHAR(20)  NOT NULL UNIQUE,
    `status`            ENUM('new','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'new',
    `first_name`        VARCHAR(100) NOT NULL,
    `last_name`         VARCHAR(100) DEFAULT NULL,
    `email`             VARCHAR(255) NOT NULL,
    `phone`             VARCHAR(20)  NOT NULL,
    `delivery_method_id` INT UNSIGNED DEFAULT NULL,
    `delivery_address`  TEXT         DEFAULT NULL,
    `delivery_price`    DECIMAL(10,2) NOT NULL DEFAULT 0,
    `payment_method_id` INT UNSIGNED DEFAULT NULL,
    `payment_status`    ENUM('pending','paid','refunded','failed') NOT NULL DEFAULT 'pending',
    `subtotal`          DECIMAL(10,2) NOT NULL,
    `discount`          DECIMAL(10,2) NOT NULL DEFAULT 0,
    `total`             DECIMAL(10,2) NOT NULL,
    `comment`           TEXT         DEFAULT NULL,
    `admin_note`        TEXT         DEFAULT NULL,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`delivery_method_id`) REFERENCES `delivery_methods`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_number` (`order_number`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`     INT UNSIGNED NOT NULL,
    `product_id`   INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `product_sku`  VARCHAR(50)  DEFAULT NULL,
    `price`        DECIMAL(10,2) NOT NULL,
    `quantity`     INT UNSIGNED NOT NULL DEFAULT 1,
    `total`        DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
    INDEX `idx_order` (`order_id`)
) ENGINE=InnoDB;

-- =============================================
-- Обратная связь / заявки на звонок
-- =============================================

CREATE TABLE `callbacks` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(100) NOT NULL,
    `phone`        VARCHAR(20)  DEFAULT NULL,
    `email`        VARCHAR(255) DEFAULT NULL,
    `message`      TEXT         DEFAULT NULL,
    `status`       ENUM('new','processing','done') NOT NULL DEFAULT 'new',
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
