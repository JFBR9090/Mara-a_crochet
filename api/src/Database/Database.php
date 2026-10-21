<?php
declare(strict_types=1);

namespace Marana\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Gestor de Conexión y Persistencia PDO para Maraña Crochet
 * Diseñado bajo estándares modernos de PHP 8.2+
 */
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // 1. Verificación de versión de PHP
        if (PHP_VERSION_ID < 80100) {
            throw new RuntimeException("Maraña Crochet requiere PHP 8.1 o superior. Versión actual: " . PHP_VERSION);
        }

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $name = $_ENV['DB_NAME'] ?? 'marana_crochet';

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Asegurar base de datos y conjunto de caracteres utf8mb4
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");

            self::initSchema($pdo);
            self::seedInitialData($pdo);
            self::$instance = $pdo;

            return self::$instance;
        } catch (PDOException $e) {
            throw new RuntimeException("Error al conectar con la base de datos MySQL en XAMPP: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private static function initSchema(PDO $pdo): void {
        $pdo->exec("
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
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `orders` (
              `id` VARCHAR(100) NOT NULL PRIMARY KEY,
              `date` VARCHAR(100) NOT NULL,
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
              `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
              `bank` VARCHAR(100) NOT NULL DEFAULT 'Bancolombia',
              `accountType` VARCHAR(100) NOT NULL DEFAULT 'Cuenta de Ahorros',
              `transferDetails` LONGTEXT,
              `giftDedication` TEXT DEFAULT NULL,
              `auditLog` LONGTEXT,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
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
        ");
    }

    private static function seedInitialData(PDO $pdo): void {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
        if ($count > 0) {
            return;
        }

        $seedPaths = [
            __DIR__ . '/../../seed_data.json',
            __DIR__ . '/../seed_data.json',
        ];

        $seedFile = null;
        foreach ($seedPaths as $path) {
            if (file_exists($path)) {
                $seedFile = $path;
                break;
            }
        }

        if (!$seedFile) {
            return;
        }

        $data = json_decode(file_get_contents($seedFile), true);
        if (!is_array($data)) {
            return;
        }

        if (!empty($data['products'])) {
            $stmt = $pdo->prepare("
                INSERT INTO `products` 
                (`id`, `name`, `sku`, `category`, `categoryLabel`, `price`, `originalPrice`, `discountBadge`, `usdApprox`, `rating`, `reviewCount`, `stock`, `maxStock`, `reserved`, `status`, `statusLabel`, `tagline`, `description`, `images`, `craftHours`, `dimensions`, `weight`, `material`, `filling`, `safety`, `includes`, `preorderDays`)
                VALUES
                (:id, :name, :sku, :category, :categoryLabel, :price, :originalPrice, :discountBadge, :usdApprox, :rating, :reviewCount, :stock, :maxStock, :reserved, :status, :statusLabel, :tagline, :description, :images, :craftHours, :dimensions, :weight, :material, :filling, :safety, :includes, :preorderDays)
            ");

            foreach ($data['products'] as $prod) {
                $stmt->execute([
                    ':id' => $prod['id'],
                    ':name' => $prod['name'],
                    ':sku' => $prod['sku'],
                    ':category' => $prod['category'],
                    ':categoryLabel' => $prod['categoryLabel'],
                    ':price' => $prod['price'],
                    ':originalPrice' => $prod['originalPrice'] ?? null,
                    ':discountBadge' => $prod['discountBadge'] ?? null,
                    ':usdApprox' => $prod['usdApprox'],
                    ':rating' => $prod['rating'],
                    ':reviewCount' => $prod['reviewCount'],
                    ':stock' => $prod['stock'],
                    ':maxStock' => $prod['maxStock'],
                    ':reserved' => $prod['reserved'],
                    ':status' => $prod['status'],
                    ':statusLabel' => $prod['statusLabel'],
                    ':tagline' => $prod['tagline'],
                    ':description' => $prod['description'],
                    ':images' => json_encode($prod['images'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ':craftHours' => $prod['craftHours'],
                    ':dimensions' => $prod['dimensions'],
                    ':weight' => $prod['weight'],
                    ':material' => $prod['material'],
                    ':filling' => $prod['filling'],
                    ':safety' => $prod['safety'],
                    ':includes' => $prod['includes'],
                    ':preorderDays' => $prod['preorderDays'] ?? null,
                ]);
            }
        }

        if (!empty($data['orders'])) {
            $oStmt = $pdo->prepare("
                INSERT INTO `orders`
                (`id`, `date`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `bank`, `accountType`, `transferDetails`, `giftDedication`, `auditLog`)
                VALUES
                (:id, :date, :customerName, :customerEmail, :customerPhone, :customerRut, :address, :shippingMethod, :shippingCost, :items, :subtotal, :total, :status, :bank, :accountType, :transferDetails, :giftDedication, :auditLog)
            ");

            foreach ($data['orders'] as $ord) {
                $oStmt->execute([
                    ':id' => $ord['id'],
                    ':date' => $ord['date'],
                    ':customerName' => $ord['customerName'],
                    ':customerEmail' => $ord['customerEmail'],
                    ':customerPhone' => $ord['customerPhone'],
                    ':customerRut' => $ord['customerRut'],
                    ':address' => json_encode($ord['address'], JSON_UNESCAPED_UNICODE),
                    ':shippingMethod' => $ord['shippingMethod'],
                    ':shippingCost' => $ord['shippingCost'],
                    ':items' => json_encode($ord['items'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ':subtotal' => $ord['subtotal'],
                    ':total' => $ord['total'],
                    ':status' => $ord['status'],
                    ':bank' => $ord['bank'] ?? 'Bancolombia',
                    ':accountType' => $ord['accountType'] ?? 'Cuenta de Ahorros',
                    ':transferDetails' => json_encode($ord['transferDetails'] ?? [], JSON_UNESCAPED_UNICODE),
                    ':giftDedication' => $ord['giftDedication'] ?? null,
                    ':auditLog' => json_encode($ord['auditLog'] ?? [], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }
}
