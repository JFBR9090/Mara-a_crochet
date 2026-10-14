<?php
// products.php - Endpoints REST para productos de Maraña Crochet
require_once __DIR__ . '/db.php';

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

if ($method === 'PUT' || $method === 'PATCH' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El campo ID de producto es obligatorio']);
        exit();
    }

    $id = $input['id'];
    $fields = [];
    $params = [':id' => $id];

    // Actualización de campos individuales o generales
    if (isset($input['stock'])) {
        $fields[] = "`stock` = :stock";
        $params[':stock'] = (int)$input['stock'];

        // Actualizar automáticamente estado y etiqueta según stock
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
        // Ajuste proporcional USD aproximado
        $fields[] = "`usdApprox` = :usdApprox";
        $params[':usdApprox'] = round($input['price'] / 900, 2);
    }

    if (isset($input['status'])) {
        $fields[] = "`status` = :status";
        $params[':status'] = $input['status'];
    }

    if (isset($input['statusLabel'])) {
        $fields[] = "`statusLabel` = :statusLabel";
        $params[':statusLabel'] = $input['statusLabel'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No se proporcionaron campos para actualizar']);
        exit();
    }

    $sql = "UPDATE `products` SET " . implode(', ', $fields) . " WHERE `id` = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Retornar el producto actualizado
    $resStmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
    $resStmt->execute([':id' => $id]);
    $updated = $resStmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Producto actualizado correctamente en MySQL',
        'product' => formatProductRow($updated)
    ]);
    exit();
}
