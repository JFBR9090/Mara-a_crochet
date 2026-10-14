<?php
// api/products.php - Endpoint REST para productos
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function formatProductRow($row) {
    if (!$row) return null;
    $row['price'] = (int)$row['price'];
    $row['originalPrice'] = $row['originalPrice'] !== null ? (int)$row['originalPrice'] : null;
    $row['usdApprox'] = (float)$row['usdApprox'];
    $row['rating'] = (float)$row['rating'];
    $row['reviewCount'] = (int)$row['reviewCount'];
    $row['stock'] = (int)$row['stock'];
    $row['maxStock'] = (int)$row['maxStock'];
    $row['reserved'] = (int)$row['reserved'];
    $row['craftHours'] = (float)$row['craftHours'];
    $row['preorderDays'] = $row['preorderDays'] !== null ? (int)$row['preorderDays'] : null;
    $row['images'] = !empty($row['images']) ? json_decode($row['images'], true) : [];
    return $row;
}

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $_GET['id']]);
        $prod = $stmt->fetch();
        if ($prod) {
            echo json_encode(['success' => true, 'product' => formatProductRow($prod)]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM `products` ORDER BY `name` ASC");
        $products = array_map('formatProductRow', $stmt->fetchAll());
        echo json_encode(['success' => true, 'products' => $products]);
    }
    exit();
}

if ($method === 'PUT' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de producto requerido']);
        exit();
    }

    $id = $input['id'];
    $fields = [];
    $params = [':id' => $id];

    if (isset($input['stock'])) {
        $fields[] = "`stock` = :stock";
        $params[':stock'] = (int)$input['stock'];
        $newStock = (int)$input['stock'];
        if ($newStock <= 0) {
            $fields[] = "`status` = 'soldout'";
            $fields[] = "`statusLabel` = 'Agotado Temporalmente'";
        } else if ($newStock <= 4) {
            $fields[] = "`status` = 'low-stock'";
            $fields[] = "`statusLabel` = '¡Últimas " . $newStock . " unidades!'";
        } else {
            $fields[] = "`status` = 'in-stock'";
            $fields[] = "`statusLabel` = 'Stock Disponible (" . $newStock . ")'";
        }
    }

    if (isset($input['price'])) {
        $fields[] = "`price` = :price";
        $params[':price'] = (int)$input['price'];
        $fields[] = "`usdApprox` = :usdApprox";
        $params[':usdApprox'] = round($input['price'] / 900, 2);
    }

    if (!empty($fields)) {
        $sql = "UPDATE `products` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $resStmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
    $resStmt->execute([':id' => $id]);
    $updated = $resStmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Producto actualizado en MySQL',
        'product' => formatProductRow($updated)
    ]);
    exit();
}
