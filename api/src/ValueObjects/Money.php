<?php
declare(strict_types=1);

namespace Marana\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Objeto de Valor inmutable para manejo de montos monetarios (PHP 8.2 readonly class)
 */
readonly class Money implements JsonSerializable {
    public function __construct(
        public int $amount,
        public string $currency = 'CLP'
    ) {
        if ($this->amount < 0) {
            throw new InvalidArgumentException("El monto no puede ser negativo: {$this->amount}");
        }
    }

    public static function fromCLP(int $amount): self {
        return new self($amount, 'CLP');
    }

    public function format(): string {
        if ($this->currency === 'CLP') {
            return '$' . number_format($this->amount, 0, ',', '.');
        }
        return '$' . number_format($this->amount / 100, 2, '.', ',');
    }

    public function toUSD(float $exchangeRate = 900.0): float {
        if ($exchangeRate <= 0) {
            throw new InvalidArgumentException("Tasa de cambio inválida");
        }
        return round($this->amount / $exchangeRate, 2);
    }

    public function add(self $other): self {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function multiply(int $multiplier): self {
        if ($multiplier < 0) {
            throw new InvalidArgumentException("El multiplicador debe ser positivo");
        }
        return new self($this->amount * $multiplier, $this->currency);
    }

    private function ensureSameCurrency(self $other): void {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("No se pueden sumar divisas distintas: {$this->currency} y {$other->currency}");
        }
    }

    public function jsonSerialize(): array {
        return [
            'amount'    => $this->amount,
            'currency'  => $this->currency,
            'formatted' => $this->format(),
            'usdApprox' => $this->toUSD(),
        ];
    }
}
