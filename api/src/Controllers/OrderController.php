<?php
declare(strict_types=1);

namespace Marana\Controllers;

use Marana\Enums\OrderStatus;
use Marana\Http\Response;
use Marana\Repositories\OrderRepository;
use Marana\Services\OrderService;
use Throwable;

/**
 * Controlador REST para el ciclo de vida de pedidos y compras
 */
class OrderController {
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderRepository $orderRepo
    ) {}

    public function index(): void {
        try {
            $id = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
            if ($id !== '') {
                $order = $this->orderRepo->findById($id);
                if (!$order) {
                    Response::error("Pedido '{$id}' no encontrado", 404);
                    return;
                }
                Response::json(['success' => true, 'order' => $order->toArray()]);
                return;
            }

            $orders = $this->orderRepo->findAll();
            Response::json([
                'success' => true,
                'orders'  => array_map(fn($o) => $o->toArray(), $orders)
            ]);
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function create(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                Response::error("Datos de pedido inválidos", 400);
                return;
            }

            $order = $this->orderService->processCheckout($input);
            Response::json([
                'success' => true,
                'message' => 'Pedido registrado exitosamente',
                'order'   => $order->toArray()
            ], 201);
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function updateStatus(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input) || empty($input['id']) || empty($input['status'])) {
                Response::error("Campos 'id' y 'status' son obligatorios", 400);
                return;
            }

            $id = (string)$input['id'];
            $statusEnum = OrderStatus::tryFrom((string)$input['status']);
            if (!$statusEnum) {
                Response::error("Estado de pedido '{$input['status']}' inválido", 400);
                return;
            }

            $note = (string)($input['note'] ?? '');
            $author = (string)($input['author'] ?? 'Taller Maraña');

            $success = $this->orderRepo->updateStatus($id, $statusEnum, $note, $author);
            if (!$success) {
                Response::error("No se pudo actualizar el estado del pedido", 400);
                return;
            }

            $order = $this->orderRepo->findById($id);
            Response::json([
                'success' => true,
                'message' => 'Estado de pedido actualizado',
                'order'   => $order?->toArray()
            ]);
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
