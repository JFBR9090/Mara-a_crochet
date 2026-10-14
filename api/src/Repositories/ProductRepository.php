<?php
declare(strict_types=1);

namespace Marana\Repositories;

use Marana\Database\Database;
use Marana\DTO\ProductDTO;
use Marana\Enums\ProductStatus;
use PDO;

/**
 * Repositorio tipado para acceso y mutación de amigurumis/productos
 */
class ProductRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * @return ProductDTO[]
     */
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM `products` ORDER BY `name` ASC");
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => ProductDTO::fromArray($row), $rows);
    }

    public function findById(string $id): ?ProductDTO {
        $stmt = $this->pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? ProductDTO::fromArray($row) : null;
    }

    public function updateStock(string $id, int $newStock): bool {
        $product = $this->findById($id);
        if (!$product) {
            return false;
        }

        $newStatus = ProductStatus::fromStock($newStock, $product->preorderDays);

        $stmt = $this->pdo->prepare("
            UPDATE `products` 
            SET `stock` = :stock,
                `status` = :status,
                `statusLabel` = :statusLabel
            WHERE `id` = :id
        ");

        return $stmt->execute([
            ':stock'       => $newStock,
            ':status'      => $newStatus->value,
            ':statusLabel' => $newStatus->label(),
            ':id'          => $id,
        ]);
    }

    public function updatePrice(string $id, int $newPrice, ?int $originalPrice = null): bool {
        $usdApprox = round($newPrice / 900, 2);
        $stmt = $this->pdo->prepare("
            UPDATE `products`
            SET `price` = :price,
                `originalPrice` = :orig,
                `usdApprox` = :usd
            WHERE `id` = :id
        ");

        return $stmt->execute([
            ':price' => $newPrice,
            ':orig'  => $originalPrice,
            ':usd'   => $usdApprox,
            ':id'    => $id,
        ]);
    }
}
