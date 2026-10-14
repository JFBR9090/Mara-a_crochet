<?php
declare(strict_types=1);

namespace Marana\Enums;

/**
 * Estado de disponibilidad de un producto artesanal
 * PHP 8.1+ Backed Enum con métodos de apoyo
 */
enum ProductStatus: string {
    case InStock    = 'in-stock';
    case LowStock   = 'low-stock';
    case OutOfStock = 'out-of-stock';
    case PreOrder   = 'pre-order';

    public function label(): string {
        return match($this) {
            self::InStock    => 'Disponible en taller',
            self::LowStock   => '¡Últimas unidades!',
            self::OutOfStock => 'Agotado (Bajo encargo)',
            self::PreOrder   => 'Preventa Artesanal',
        };
    }

    public function badgeClass(): string {
        return match($this) {
            self::InStock    => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::LowStock   => 'bg-amber-100 text-amber-800 border-amber-200',
            self::OutOfStock => 'bg-rose-100 text-rose-800 border-rose-200',
            self::PreOrder   => 'bg-purple-100 text-purple-800 border-purple-200',
        };
    }

    public function isAvailable(): bool {
        return match($this) {
            self::InStock, self::LowStock, self::PreOrder => true,
            self::OutOfStock => false,
        };
    }

    public static function fromStock(int $stock, ?int $preorderDays = null): self {
        if ($preorderDays !== null && $preorderDays > 0 && $stock === 0) {
            return self::PreOrder;
        }
        if ($stock <= 0) {
            return self::OutOfStock;
        }
        if ($stock <= 5) {
            return self::LowStock;
        }
        return self::InStock;
    }
}
