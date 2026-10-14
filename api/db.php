<?php
// db.php - Conexión PDO a MySQL en XAMPP y auto-inicialización de Maraña Crochet
require_once __DIR__ . '/cors.php';

$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$pass = '';
$dbName = 'marana_crochet';

try {
    // 1. Conexión al servidor MySQL
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // 2. Crear BD si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");

    // 3. Crear tablas si no existen
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
          `status` VARCHAR(50) NOT NULL DEFAULT 'revision',
          `bank` VARCHAR(100) NOT NULL DEFAULT 'Santander',
          `accountType` VARCHAR(100) NOT NULL DEFAULT 'Cuenta Corriente',
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

    // 4. Si la tabla products está vacía, sembrar productos iniciales
    $countStmt = $pdo->query("SELECT COUNT(*) FROM `products`");
    if ($countStmt->fetchColumn() == 0) {
        $seedFile = __DIR__ . '/seed_data.json';
        if (file_exists($seedFile)) {
            $data = json_decode(file_get_contents($seedFile), true);
            if (!empty($data['products'])) {
                $pStmt = $pdo->prepare("
                    INSERT INTO `products` 
                    (`id`, `name`, `sku`, `category`, `categoryLabel`, `price`, `originalPrice`, `discountBadge`, `usdApprox`, `rating`, `reviewCount`, `stock`, `maxStock`, `reserved`, `status`, `statusLabel`, `tagline`, `description`, `images`, `craftHours`, `dimensions`, `weight`, `material`, `filling`, `safety`, `includes`, `preorderDays`)
                    VALUES
                    (:id, :name, :sku, :category, :categoryLabel, :price, :originalPrice, :discountBadge, :usdApprox, :rating, :reviewCount, :stock, :maxStock, :reserved, :status, :statusLabel, :tagline, :description, :images, :craftHours, :dimensions, :weight, :material, :filling, :safety, :includes, :preorderDays)
                ");

                foreach ($data['products'] as $prod) {
                    $pStmt->execute([
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
                        ':bank' => $ord['bank'],
                        ':accountType' => $ord['accountType'],
                        ':transferDetails' => json_encode($ord['transferDetails'], JSON_UNESCAPED_UNICODE),
                        ':giftDedication' => $ord['giftDedication'] ?? null,
                        ':auditLog' => json_encode($ord['auditLog'] ?? [], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }

            if (!empty($data['reviews'])) {
                $rStmt = $pdo->prepare("
                    INSERT INTO `reviews`
                    (`id`, `customerInitials`, `customerName`, `emailMasked`, `orderId`, `sku`, `productName`, `productIcon`, `rating`, `date`, `comment`, `verified`, `status`, `photos`, `workshopReply`, `rejectionReason`, `helpfulCount`)
                    VALUES
                    (:id, :customerInitials, :customerName, :emailMasked, :orderId, :sku, :productName, :productIcon, :rating, :date, :comment, :verified, :status, :photos, :workshopReply, :rejectionReason, :helpfulCount)
                ");

                foreach ($data['reviews'] as $rev) {
                    $rStmt->execute([
                        ':id' => $rev['id'],
                        ':customerInitials' => $rev['customerInitials'],
                        ':customerName' => $rev['customerName'],
                        ':emailMasked' => $rev['emailMasked'],
                        ':orderId' => $rev['orderId'] ?? '',
                        ':sku' => $rev['sku'] ?? '',
                        ':productName' => $rev['productName'],
                        ':productIcon' => $rev['productIcon'] ?? 'star',
                        ':rating' => $rev['rating'],
                        ':date' => $rev['date'],
                        ':comment' => $rev['comment'],
                        ':verified' => !empty($rev['verified']) ? 1 : 0,
                        ':status' => $rev['status'],
                        ':photos' => !empty($rev['photos']) ? json_encode($rev['photos'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                        ':workshopReply' => !empty($rev['workshopReply']) ? json_encode($rev['workshopReply'], JSON_UNESCAPED_UNICODE) : null,
                        ':rejectionReason' => $rev['rejectionReason'] ?? null,
                        ':helpfulCount' => $rev['helpfulCount'] ?? 0,
                    ]);
                }
            }
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error de conexión a la base de datos MySQL en XAMPP',
        'details' => $e->getMessage()
    ]);
    exit();
}
