<?php
declare(strict_types=1);

namespace Marana\Repositories;

use Exception;
use Marana\Database\Database;
use Marana\DTO\OrderDTO;
use Marana\Enums\OrderStatus;
use PDO;
use RuntimeException;

/**
 * Repositorio transaccional para pedidos en Maraña Crochet
 */
class OrderRepository {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * @return OrderDTO[]
     */
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM `orders` ORDER BY `created_at` DESC, `id` DESC");
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row) => OrderDTO::fromArray($row), $rows);
    }

    public function findById(string $id): ?OrderDTO {
        $stmt = $this->pdo->prepare("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? OrderDTO::fromArray($row) : null;
    }

    /**
     * Crea un pedido ejecutando una transacción atómica con descuento de stock
     */
    public function create(array $data): OrderDTO {
        $id = $data['id'] ?? ('ORD-' . strtoupper(bin2hex(random_bytes(4))));
        $date = $data['date'] ?? date('d/m/Y H:i');

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO `orders`
                (`id`, `date`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `bank`, `accountType`, `transferDetails`, `giftDedication`, `auditLog`)
                VALUES
                (:id, :date, :customerName, :customerEmail, :customerPhone, :customerRut, :address, :shippingMethod, :shippingCost, :items, :subtotal, :total, :status, :bank, :accountType, :transferDetails, :giftDedication, :auditLog)
            ");

            $items = is_array($data['items'] ?? null) ? $data['items'] : [];
            $address = is_array($data['address'] ?? null) ? $data['address'] : [];
            $transferDetails = is_array($data['transferDetails'] ?? null) ? $data['transferDetails'] : [];
            $auditLog = is_array($data['auditLog'] ?? null) ? $data['auditLog'] : [
                [
                    'date'   => $date,
                    'user'   => 'Cliente Web (Checkout)',
                    'action' => 'Pedido registrado vía Transferencia Bancaria',
                ]
            ];

            $status = $data['status'] instanceof OrderStatus
                ? $data['status']->value
                : ($data['status'] ?? OrderStatus::Pending->value);

            $stmt->execute([
                ':id'              => $id,
                ':date'            => $date,
                ':customerName'    => $data['customerName'] ?? '',
                ':customerEmail'   => $data['customerEmail'] ?? '',
                ':customerPhone'   => $data['customerPhone'] ?? '',
                ':customerRut'     => $data['customerRut'] ?? '',
                ':address'         => json_encode($address, JSON_UNESCAPED_UNICODE),
                ':shippingMethod'  => $data['shippingMethod'] ?? 'delivery',
                ':shippingCost'    => (int)($data['shippingCost'] ?? 0),
                ':items'           => json_encode($items, JSON_UNESCAPED_UNICODE),
                ':subtotal'        => (int)($data['subtotal'] ?? 0),
                ':total'           => (int)($data['total'] ?? 0),
                ':status'          => $status,
                ':bank'            => $data['bank'] ?? 'BancoEstado',
                ':accountType'     => $data['accountType'] ?? 'CuentaRUT',
                ':transferDetails' => json_encode($transferDetails, JSON_UNESCAPED_UNICODE),
                ':giftDedication'  => $data['giftDedication'] ?? '',
                ':auditLog'        => json_encode($auditLog, JSON_UNESCAPED_UNICODE),
            ]);

            // Descontar stock atómicamente por cada item comprado
            $productRepo = new ProductRepository($this->pdo);
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $prod = $productRepo->findById((string)$item['id']);
                    if ($prod) {
                        $qty = (int)($item['quantity'] ?? 1);
                        $newStock = max(0, $prod->stock - $qty);
                        $productRepo->updateStock($prod->id, $newStock);
                    }
                }
            }

            $this->pdo->commit();

            return $this->findById($id) ?? throw new RuntimeException("No se pudo recuperar el pedido creado");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException("Fallo en la creación del pedido: " . $e->getMessage(), 0, $e);
        }
    }

    public function updateStatus(string $id, OrderStatus $status, string $note = '', string $author = 'Taller Maraña'): bool {
        $order = $this->findById($id);
        if (!$order) {
            return false;
        }

        $audit = $order->auditLog;
        $audit[] = [
            'date'   => date('d/m/Y H:i'),
            'user'   => $author,
            'action' => "Estado actualizado a '{$status->label()}'. " . ($note !== '' ? "Nota: {$note}" : ""),
        ];

        $stmt = $this->pdo->prepare("
            UPDATE `orders`
            SET `status` = :status,
                `auditLog` = :auditLog
            WHERE `id` = :id
        ");

        return $stmt->execute([
            ':status'   => $status->value,
            ':auditLog' => json_encode($audit, JSON_UNESCAPED_UNICODE),
            ':id'       => $id,
        ]);
    }
}
