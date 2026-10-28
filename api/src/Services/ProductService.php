<?php
declare(strict_types=1);

namespace Marana\Services;

use InvalidArgumentException;
use Marana\DTO\ProductDTO;
use Marana\Repositories\ProductRepository;

/**
 * Servicio de Dominio para Productos e Inventario en Maraña Crochet
 */
class ProductService {
    public function __construct(
        private readonly ProductRepository $productRepo
    ) {}

    /**
     * @return ProductDTO[]
     */
    public function getCatalog(): array {
        return $this->productRepo->findAll();
    }

    public function getProductById(string $id): ?ProductDTO {
        return $this->productRepo->findById($id);
    }

    public function adjustStock(string $id, int $newStock): ProductDTO {
        if ($newStock < 0) {
            throw new InvalidArgumentException("El stock no puede ser un valor negativo.");
        }

        $success = $this->productRepo->updateStock($id, $newStock);
        if (!$success) {
            throw new InvalidArgumentException("No se pudo actualizar el stock del producto '{$id}'.");
        }

        return $this->productRepo->findById($id);
    }

    public function adjustPrice(string $id, int $newPrice, ?int $originalPrice = null): ProductDTO {
        if ($newPrice <= 0) {
            throw new InvalidArgumentException("El precio debe ser mayor a cero.");
        }

        $success = $this->productRepo->updatePrice($id, $newPrice, $originalPrice);
        if (!$success) {
            throw new InvalidArgumentException("No se pudo actualizar el precio del producto '{$id}'.");
        }

        return $this->productRepo->findById($id);
    }
}
