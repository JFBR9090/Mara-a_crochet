<?php
declare(strict_types=1);

// php_app/api/products.php - Endpoint REST para productos con arquitectura PHP 8.2+
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/db.php';

use Marana\DTO\ProductDTO;
use Marana\Enums\ProductStatus;
use Marana\Http\Response;
use Marana\Repositories\ProductRepository;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$repo = new ProductRepository($pdo);

if ($method === 'GET') {
    $id = isset($_GET['id']) ? trim((string)$_GET['id']) : '';

    if ($id !== '') {
        $product = $repo->findById($id);
        if ($product) {
            Response::json(['success' => true, 'product' => $product->toArray()]);
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

if ($method === 'PUT' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input['id'])) {
        Response::error('ID de producto requerido', 400);
    }

    $id = (string)$input['id'];
    $existing = $repo->findById($id);
    if (!$existing) {
        Response::error('Producto no encontrado', 404);
    }

    $fields = [];
    $params = [':id' => $id];

    if (isset($input['stock'])) {
        $newStock = (int)$input['stock'];
        $fields[] = "`stock` = :stock";
        $params[':stock'] = $newStock;

        $statusEnum = ProductStatus::fromStock($newStock, $existing->preorderDays);
        $fields[] = "`status` = :status";
        $fields[] = "`statusLabel` = :statusLabel";
        $params[':status'] = $statusEnum->value;
        $params[':statusLabel'] = $statusEnum->label();
    }

    if (isset($input['price'])) {
        $price = (int)$input['price'];
        $fields[] = "`price` = :price";
        $params[':price'] = $price;
        $fields[] = "`usdApprox` = :usd";
        $params[':usd'] = round($price / 900, 2);
    }

    if (empty($fields)) {
        Response::error('No se enviaron campos válidos para actualizar', 400);
    }

    $sql = "UPDATE `products` SET " . implode(', ', $fields) . " WHERE `id` = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $updated = $repo->findById($id);

    Response::json([
        'success' => true,
        'message' => 'Producto actualizado correctamente (PHP 8.2+)',
        'product' => $updated?->toArray(),
    ]);
}

Response::error("Método HTTP '{$method}' no permitido", 405);

