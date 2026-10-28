<?php
declare(strict_types=1);

namespace Marana\Services;

use InvalidArgumentException;
use Marana\DTO\OrderDTO;
use Marana\Enums\OrderStatus;
use Marana\Repositories\OrderRepository;
use Marana\Repositories\ProductRepository;
use Marana\ValueObjects\Money;

/**
 * Servicio de Dominio para Pedidos y Checkout en Maraña Crochet
 * Gestiona validaciones de negocio, costos de envío por región y cálculo de totales en COP.
 */
class OrderService {
    public function __construct(
        private readonly OrderRepository $orderRepo,
        private readonly ProductRepository $productRepo
    ) {}

    /**
     * Calcula el costo de envío según la zona geográfica colombiana
     */
    public function calculateShippingCost(string $shippingMethod): Money {
        $costInCOP = match ($shippingMethod) {
            'envio-neiva'            => 6000,
            'ciudades-principales'   => 11500,
            'nacional-colombia'      => 14500,
            'retiro'                 => 0,
            default                  => 11500
        };

        return Money::fromCOP($costInCOP);
    }

    /**
     * Procesa y registra la orden de compra con transacción atómica
     *
     * @param array $payload Datos recibidos del formulario o API REST
     * @return OrderDTO
     */
    public function processCheckout(array $payload): OrderDTO {
        // Validaciones estrictas
        if (empty($payload['customerName'])) {
            throw new InvalidArgumentException("El nombre del cliente es obligatorio.");
        }
        if (empty($payload['customerEmail']) || !filter_var($payload['customerEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Debe proporcionar un correo electrónico válido.");
        }
        if (empty($payload['customerPhone'])) {
            throw new InvalidArgumentException("El número de teléfono/WhatsApp es obligatorio.");
        }
        if (empty($payload['items']) || !is_array($payload['items'])) {
            throw new InvalidArgumentException("La canasta de compras no puede estar vacía.");
        }

        // Calcular subtotal real validando precio y existencia en repositorio
        $subtotalAmount = 0;
        foreach ($payload['items'] as $item) {
            $product = $this->productRepo->findById((string)$item['id']);
            if (!$product) {
                throw new InvalidArgumentException("El producto '{$item['id']}' no existe en el catálogo.");
            }
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $subtotalAmount += $product->price->getAmountInCents() * $qty;
        }

        $subtotal = Money::fromCents($subtotalAmount);
        $shippingMethod = $payload['shippingMethod'] ?? 'ciudades-principales';
        $shippingCost = $this->calculateShippingCost($shippingMethod);
        $total = $subtotal->add($shippingCost);

        // Armar datos enriquecidos para el repositorio
        $orderData = $payload;
        $orderData['subtotal'] = $subtotal->getAmount();
        $orderData['shippingCost'] = $shippingCost->getAmount();
        $orderData['total'] = $total->getAmount();
        $orderData['status'] = OrderStatus::Revision;

        return $this->orderRepo->create($orderData);
    }

    /**
     * Genera firma de integridad criptográfica para pasarela Wompi
     */
    public function generateWompiSignature(string $reference, int $amountInCOP, string $integrityKey): string {
        $amountInCents = $amountInCOP * 100;
        $currency = 'COP';
        $concatenated = "{$reference}{$amountInCents}{$currency}{$integrityKey}";
        return hash('sha256', $concatenated);
    }
}
