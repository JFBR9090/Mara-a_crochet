-- Base de datos y tablas para Maraña Crochet en PHP / XAMPP
CREATE DATABASE IF NOT EXISTS `marana_crochet` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `marana_crochet`;

CREATE TABLE IF NOT EXISTS `products` (
  `id` VARCHAR(100) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `categoryLabel` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL,
  `originalPrice` INT DEFAULT NULL,
  `discountBadge` VARCHAR(50) DEFAULT NULL,
  `usdApprox` DECIMAL(10,2) NOT NULL,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 5.0,
  `reviewCount` INT NOT NULL DEFAULT 0,
  `stock` INT NOT NULL DEFAULT 0,
  `maxStock` INT NOT NULL DEFAULT 10,
  `reserved` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(50) NOT NULL DEFAULT 'in-stock',
  `statusLabel` VARCHAR(100) NOT NULL DEFAULT 'Disponible',
  `tagline` VARCHAR(255) DEFAULT '',
  `description` TEXT,
  `images` LONGTEXT,
  `craftHours` DECIMAL(4,1) DEFAULT 4.0,
  `dimensions` VARCHAR(100) DEFAULT '',
  `weight` VARCHAR(100) DEFAULT '',
  `material` VARCHAR(100) DEFAULT '',
  `filling` VARCHAR(100) DEFAULT '',
  `safety` VARCHAR(100) DEFAULT '',
  `includes` VARCHAR(255) DEFAULT '',
  `preorderDays` INT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(50) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL DEFAULT 'Cundinamarca',
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` VARCHAR(100) NOT NULL PRIMARY KEY,
  `date` VARCHAR(100) NOT NULL,
  `customerId` INT NULL,
  `customerName` VARCHAR(255) NOT NULL,
  `customerEmail` VARCHAR(255) NOT NULL,
  `customerPhone` VARCHAR(100) NOT NULL,
  `customerRut` VARCHAR(50) NOT NULL,
  `address` LONGTEXT,
  `shippingMethod` VARCHAR(50) NOT NULL,
  `shippingCost` INT NOT NULL DEFAULT 0,
  `items` LONGTEXT NOT NULL,
  `subtotal` INT NOT NULL,
  `total` INT NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'revision',
  `paymentMethod` VARCHAR(100) NOT NULL DEFAULT 'transferencia',
  `bank` VARCHAR(100) NOT NULL DEFAULT 'Bancolombia',
  `accountType` VARCHAR(100) NOT NULL DEFAULT 'Cuenta de Ahorros',
  `transferDetails` LONGTEXT,
  `giftDedication` TEXT DEFAULT NULL,
  `auditLog` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`customerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` VARCHAR(100) NOT NULL PRIMARY KEY,
  `customerInitials` VARCHAR(10) NOT NULL,
  `customerName` VARCHAR(255) NOT NULL,
  `emailMasked` VARCHAR(255) NOT NULL,
  `orderId` VARCHAR(100) DEFAULT '',
  `sku` VARCHAR(100) DEFAULT '',
  `productName` VARCHAR(255) NOT NULL,
  `productIcon` VARCHAR(50) DEFAULT 'star',
  `rating` INT NOT NULL DEFAULT 5,
  `date` VARCHAR(100) NOT NULL,
  `comment` TEXT NOT NULL,
  `verified` TINYINT(1) NOT NULL DEFAULT 1,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `photos` LONGTEXT DEFAULT NULL,
  `workshopReply` LONGTEXT DEFAULT NULL,
  `rejectionReason` TEXT DEFAULT NULL,
  `helpfulCount` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
