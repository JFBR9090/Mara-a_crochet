<?php
declare(strict_types=1);

namespace Marana\DTO;

use JsonSerializable;
use Marana\Enums\OrderStatus;

/**
 * Data Transfer Object inmutable para Pedidos de Maraña Crochet
 */
readonly class OrderDTO implements JsonSerializable {
    public function __construct(
        public string $id,
        public string $date,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public string $customerRut,
        public array $address,
        public string $shippingMethod,
        public int $shippingCost,
        public array $items,
        public int $subtotal,
        public int $total,
        public OrderStatus $status,
        public string $bank,
        public string $accountType,
        public array $transferDetails,
        public string $giftDedication,
        public array $auditLog
    ) {}

    public static function fromArray(array $data): self {
        $statusEnum = isset($data['status']) && $data['status'] instanceof OrderStatus
            ? $data['status']
            : OrderStatus::tryFrom((string)($data['status'] ?? 'pending')) ?? OrderStatus::Pending;

        $decodeIfNeeded = function (mixed $value): array {
            if (is_array($value)) return $value;
            if (is_string($value) && !empty($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        };

        return new self(
            id: (string)$data['id'],
            date: (string)($data['date'] ?? date('d/m/Y H:i')),
            customerName: (string)($data['customerName'] ?? ''),
            customerEmail: (string)($data['customerEmail'] ?? ''),
            customerPhone: (string)($data['customerPhone'] ?? ''),
            customerRut: (string)($data['customerRut'] ?? ''),
            address: $decodeIfNeeded($data['address'] ?? []),
            shippingMethod: (string)($data['shippingMethod'] ?? 'delivery'),
            shippingCost: (int)($data['shippingCost'] ?? 0),
            items: $decodeIfNeeded($data['items'] ?? []),
            subtotal: (int)($data['subtotal'] ?? 0),
            total: (int)($data['total'] ?? 0),
            status: $statusEnum,
            bank: (string)($data['bank'] ?? 'BancoEstado'),
            accountType: (string)($data['accountType'] ?? 'CuentaRUT'),
            transferDetails: $decodeIfNeeded($data['transferDetails'] ?? []),
            giftDedication: (string)($data['giftDedication'] ?? ''),
            auditLog: $decodeIfNeeded($data['auditLog'] ?? [])
        );
    }

    public function toArray(): array {
        return [
            'id'              => $this->id,
            'date'            => $this->date,
            'customerName'    => $this->customerName,
            'customerEmail'   => $this->customerEmail,
            'customerPhone'   => $this->customerPhone,
            'customerRut'     => $this->customerRut,
            'address'         => $this->address,
            'shippingMethod'  => $this->shippingMethod,
            'shippingCost'    => $this->shippingCost,
            'items'           => $this->items,
            'subtotal'        => $this->subtotal,
            'total'           => $this->total,
            'status'          => $this->status->value,
            'statusLabel'     => $this->status->label(),
            'bank'            => $this->bank,
            'accountType'     => $this->accountType,
            'transferDetails' => $this->transferDetails,
            'giftDedication'  => $this->giftDedication,
            'auditLog'        => $this->auditLog,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
