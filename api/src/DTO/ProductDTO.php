<?php
declare(strict_types=1);

namespace Marana\DTO;

use JsonSerializable;
use Marana\Enums\ProductStatus;

/**
 * Data Transfer Object inmutable para representar un Amigurumi / Producto
 * Aprovecha PHP 8.2 readonly class, constructor promotion y Enums nativos
 */
readonly class ProductDTO implements JsonSerializable {
    public function __construct(
        public string $id,
        public string $name,
        public string $sku,
        public string $category,
        public string $categoryLabel,
        public int $price,
        public ?int $originalPrice,
        public ?string $discountBadge,
        public float $usdApprox,
        public float $rating,
        public int $reviewCount,
        public int $stock,
        public int $maxStock,
        public int $reserved,
        public ProductStatus $status,
        public string $statusLabel,
        public string $tagline,
        public string $description,
        public array $images,
        public float $craftHours,
        public string $dimensions,
        public string $weight,
        public string $material,
        public string $filling,
        public string $safety,
        public string $includes,
        public ?int $preorderDays = null
    ) {}

    public static function fromArray(array $data): self {
        $statusEnum = isset($data['status']) && $data['status'] instanceof ProductStatus
            ? $data['status']
            : ProductStatus::tryFrom((string)($data['status'] ?? 'in-stock')) ?? ProductStatus::InStock;

        // Decodificar imágenes si vienen como string JSON
        $images = $data['images'] ?? [];
        if (is_string($images) && !empty($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }

        return new self(
            id: (string)$data['id'],
            name: (string)$data['name'],
            sku: (string)($data['sku'] ?? 'MAR-000'),
            category: (string)($data['category'] ?? 'amigurumis'),
            categoryLabel: (string)($data['categoryLabel'] ?? 'Colección Artesanal'),
            price: (int)$data['price'],
            originalPrice: isset($data['originalPrice']) && $data['originalPrice'] !== null ? (int)$data['originalPrice'] : null,
            discountBadge: !empty($data['discountBadge']) ? (string)$data['discountBadge'] : null,
            usdApprox: (float)($data['usdApprox'] ?? round(((int)$data['price']) / 900, 2)),
            rating: (float)($data['rating'] ?? 5.0),
            reviewCount: (int)($data['reviewCount'] ?? 0),
            stock: (int)($data['stock'] ?? 0),
            maxStock: (int)($data['maxStock'] ?? 10),
            reserved: (int)($data['reserved'] ?? 0),
            status: $statusEnum,
            statusLabel: (string)($data['statusLabel'] ?? $statusEnum->label()),
            tagline: (string)($data['tagline'] ?? ''),
            description: (string)($data['description'] ?? ''),
            images: is_array($images) ? $images : [],
            craftHours: (float)($data['craftHours'] ?? 4.0),
            dimensions: (string)($data['dimensions'] ?? ''),
            weight: (string)($data['weight'] ?? ''),
            material: (string)($data['material'] ?? ''),
            filling: (string)($data['filling'] ?? ''),
            safety: (string)($data['safety'] ?? ''),
            includes: (string)($data['includes'] ?? ''),
            preorderDays: isset($data['preorderDays']) && $data['preorderDays'] !== null ? (int)$data['preorderDays'] : null
        );
    }

    public function toArray(): array {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'sku'           => $this->sku,
            'category'      => $this->category,
            'categoryLabel' => $this->categoryLabel,
            'price'         => $this->price,
            'originalPrice' => $this->originalPrice,
            'discountBadge' => $this->discountBadge,
            'usdApprox'     => $this->usdApprox,
            'rating'        => $this->rating,
            'reviewCount'   => $this->reviewCount,
            'stock'         => $this->stock,
            'maxStock'      => $this->maxStock,
            'reserved'      => $this->reserved,
            'status'        => $this->status->value,
            'statusLabel'   => $this->statusLabel,
            'tagline'       => $this->tagline,
            'description'   => $this->description,
            'images'        => $this->images,
            'craftHours'    => $this->craftHours,
            'dimensions'    => $this->dimensions,
            'weight'        => $this->weight,
            'material'      => $this->material,
            'filling'       => $this->filling,
            'safety'        => $this->safety,
            'includes'      => $this->includes,
            'preorderDays'  => $this->preorderDays,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
