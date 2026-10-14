<?php
declare(strict_types=1);

// api/orders.php - Endpoints REST de Pedidos en PHP 8.2+ para Maraña Crochet
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

use Marana\DTO\OrderDTO;
use Marana\Enums\OrderStatus;
use Marana\Http\Response;
use Marana\Repositories\OrderRepository;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$repo = new OrderRepository($pdo);

// 1. Obtención de pedidos (GET)
if ($method === 'GET') {
    $id = isset($_GET['id']) ? trim((string)$_GET['id']) : '';

    if ($id !== '') {
        $order = $repo->findById($id);
        if ($order) {
            Response::json([
                'success' => true,
                'order'   => $order->toArray()
            ]);
        } else {
            Response::error('Pedido no encontrado', 404);
        }
    } else {
        $orders = $repo->findAll();
        Response::json([
            'success' => true,
            'orders'  => array_map(fn(OrderDTO $o) => $o->toArray(), $orders)
        ]);
    }
}

// 2. Registro de nuevo pedido (POST)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input['id'])) {
        Response::error('Datos de pedido incompletos o inválidos', 400);
    }

    try {
        $order = $repo->create($input);
        Response::json([
            'success' => true,
            'message' => 'Pedido registrado exitosamente en MySQL (PHP 8.2+)',
            'order'   => $order->toArray(),
        ], 201);
    } catch (Throwable $e) {
        Response::error('Error al procesar el pedido: ' . $e->getMessage(), 500);
    }
}

// 3. Actualización de estado o nota de auditoría (PUT / PATCH)
if ($method === 'PUT' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input['id'])) {
        Response::error('El campo ID de pedido es requerido', 400);
    }

    $id = (string)$input['id'];
    $current = $repo->findById($id);
    if (!$current) {
        Response::error("El pedido con ID '{$id}' no fue encontrado", 404);
    }

    $fields = [];
    $params = [':id' => $id];

    if (isset($input['status'])) {
        $newStatus = OrderStatus::tryFrom((string)$input['status']);
        $fields[] = "`status` = :status";
        $params[':status'] = $newStatus ? $newStatus->value : (string)$input['status'];
    }

    if (isset($input['note'])) {
        $auditLog = $current->auditLog;
        $auditLog[] = [
            'time' => date('H:i'),
            'note' => (string)$input['note'],
            'type' => 'info'
        ];
        $fields[] = "`auditLog` = :auditLog";
        $params[':auditLog'] = json_encode($auditLog, JSON_UNESCAPED_UNICODE);
    }

    if (!empty($fields)) {
        $sql = "UPDATE `orders` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $updated = $repo->findById($id);

    Response::json([
        'success' => true,
        'message' => 'Estado de pedido actualizado en MySQL',
        'order'   => $updated?->toArray(),
    ]);
}

Response::error("Método HTTP '{$method}' no permitido", 405);
