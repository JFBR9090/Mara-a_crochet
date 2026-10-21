<?php
declare(strict_types=1);

namespace Marana\Enums;

/**
 * Estado del flujo de vida de un pedido en Maraña Crochet
 */
enum OrderStatus: string {
    case Pending       = 'pending';         // Esperando comprobante de transferencia
    case Verified      = 'verified';        // Transferencia validada por el taller
    case InProduction  = 'in_production';   // Amigurumi en tejido o preparación
    case Shipped       = 'shipped';         // Enviado por Inter Rapidísimo / Servientrega / Coordinadora
    case Delivered     = 'delivered';       // Entregado al cliente
    case Cancelled     = 'cancelled';       // Anulado

    public function label(): string {
        return match($this) {
            self::Pending      => 'Pendiente de Validación',
            self::Verified     => 'Transferencia Verificada',
            self::InProduction => 'En Tejido Artesanal',
            self::Shipped      => 'Enviado con Seguimiento',
            self::Delivered    => 'Entregado con Éxito',
            self::Cancelled    => 'Cancelado',
        };
    }

    public function badgeColor(): string {
        return match($this) {
            self::Pending      => '#FFB703', // Dorado artesanal
            self::Verified     => '#7B2CBF', // Morado primario
            self::InProduction => '#9D4EDD', // Morado claro
            self::Shipped      => '#0284C7', // Azul cielo
            self::Delivered    => '#10B981', // Verde esmeralda
            self::Cancelled    => '#EF4444', // Rojo advertencia
        };
    }

    public function canTransitionTo(self $target): bool {
        return match($this) {
            self::Pending      => in_array($target, [self::Verified, self::Cancelled], true),
            self::Verified     => in_array($target, [self::InProduction, self::Shipped, self::Cancelled], true),
            self::InProduction => in_array($target, [self::Shipped, self::Cancelled], true),
            self::Shipped      => in_array($target, [self::Delivered], true),
            self::Delivered    => false,
            self::Cancelled    => false,
        };
    }
}
