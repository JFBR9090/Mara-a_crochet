<?php
declare(strict_types=1);

// api/products.php - Endpoints REST de Productos en PHP 8.2+ para Maraña Crochet
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

use Marana\Database\Database;
use Marana\DTO\ProductDTO;
use Marana\Enums\ProductStatus;
use Marana\Http\Response;
use Marana\Repositories\ProductRepository;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$repo = new ProductRepository($pdo);

// 1. Obtención de productos (GET)
if ($method === 'GET') {
    $id = isset($_GET['id']) ? trim((string)$_GET['id']) : '';

    if ($id !== '') {
        $product = $repo->findById($id);
        if ($product) {
            Response::json([
                'success' => true,
                'product' => $product->toArray()
            ]);
        } else {
            Response::error('Producto no encontrado', 404);
        }
    } else {
        $products = $repo->findAll();
        Response::json([
            'success'  => true,
            'products' => array_map(fn(ProductDTO $p) => $p->toArray(), $products)
        ]);
    }
}

// 2. Modificación de producto (PUT / PATCH / POST)
if (in_array($method, ['PUT', 'PATCH', 'POST'], true)) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input['id'])) {
        Response::error('El campo ID de producto es obligatorio', 400);
    }

    $id = (string)$input['id'];
    $existing = $repo->findById($id);
    if (!$existing) {
        Response::error("El producto con ID '{$id}' no existe", 404);
    }

    $fields = [];
    $params = [':id' => $id];

    if (isset($input['stock'])) {
        $newStock = (int)$input['stock'];
        $fields[] = "`stock` = :stock";
        $params[':stock'] = $newStock;

        $newStatus = ProductStatus::fromStock($newStock, $existing->preorderDays);
        $fields[] = "`status` = :status";
        $fields[] = "`statusLabel` = :statusLabel";
        $params[':status'] = $newStatus->value;
        $params[':statusLabel'] = $newStatus->label();
    }

    if (isset($input['price'])) {
        $price = (int)$input['price'];
        $fields[] = "`price` = :price";
        $params[':price'] = $price;
        $fields[] = "`usdApprox` = :usdApprox";
        $params[':usdApprox'] = round($price / 900, 2);
    }

    if (isset($input['status'])) {
        $fields[] = "`status` = :customStatus";
        $params[':customStatus'] = (string)$input['status'];
    }

    if (isset($input['statusLabel'])) {
        $fields[] = "`statusLabel` = :customLabel";
        $params[':customLabel'] = (string)$input['statusLabel'];
    }

    if (empty($fields)) {
        Response::error('No se proporcionaron campos para actualizar', 400);
    }

    $sql = "UPDATE `products` SET " . implode(', ', $fields) . " WHERE `id` = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $updated = $repo->findById($id);

    Response::json([
        'success' => true,
        'message' => 'Producto actualizado correctamente en MySQL (PHP 8.2+)',
        'product' => $updated?->toArray(),
    ]);
}

Response::error("Método HTTP '{$method}' no permitido", 405);
