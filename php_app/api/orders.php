<?php
// api/orders.php - Endpoint REST para pedidos
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function formatOrderRow($row) {
    if (!$row) return null;
    $row['shippingCost'] = (int)$row['shippingCost'];
    $row['subtotal'] = (int)$row['subtotal'];
    $row['total'] = (int)$row['total'];
    $row['address'] = !empty($row['address']) ? json_decode($row['address'], true) : [];
    $row['items'] = !empty($row['items']) ? json_decode($row['items'], true) : [];
    $row['transferDetails'] = !empty($row['transferDetails']) ? json_decode($row['transferDetails'], true) : [];
    $row['auditLog'] = !empty($row['auditLog']) ? json_decode($row['auditLog'], true) : [];
    return $row;
}

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $_GET['id']]);
        $ord = $stmt->fetch();
        if ($ord) {
            echo json_encode(['success' => true, 'order' => formatOrderRow($ord)]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pedido no encontrado']);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM `orders` ORDER BY `created_at` DESC, `id` DESC");
        $orders = array_map('formatOrderRow', $stmt->fetchAll());
        echo json_encode(['success' => true, 'orders' => $orders]);
    }
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos de pedido inválidos']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO `orders`
            (`id`, `date`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `bank`, `accountType`, `transferDetails`, `giftDedication`, `auditLog`)
            VALUES
            (:id, :date, :customerName, :customerEmail, :customerPhone, :customerRut, :address, :shippingMethod, :shippingCost, :items, :subtotal, :total, :status, :bank, :accountType, :transferDetails, :giftDedication, :auditLog)
        ");

        $stmt->execute([
            ':id' => $input['id'],
            ':date' => $input['date'] ?? date('d/m/Y H:i'),
            ':customerName' => $input['customerName'],
            ':customerEmail' => $input['customerEmail'],
            ':customerPhone' => $input['customerPhone'],
            ':customerRut' => $input['customerRut'],
            ':address' => json_encode($input['address'] ?? [], JSON_UNESCAPED_UNICODE),
            ':shippingMethod' => $input['shippingMethod'] ?? 'nacional-colombia',
            ':shippingCost' => (int)($input['shippingCost'] ?? 0),
            ':items' => json_encode($input['items'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':subtotal' => (int)($input['subtotal'] ?? 0),
            ':total' => (int)($input['total'] ?? 0),
            ':status' => $input['status'] ?? 'revision',
            ':bank' => $input['bank'] ?? 'Bancolombia',
            ':accountType' => $input['accountType'] ?? 'Cuenta de Ahorros',
            ':transferDetails' => json_encode($input['transferDetails'] ?? [], JSON_UNESCAPED_UNICODE),
            ':giftDedication' => $input['giftDedication'] ?? null,
            ':auditLog' => json_encode($input['auditLog'] ?? [
                [
                    'time' => date('H:i'),
                    'note' => 'Pedido registrado y almacenado en MySQL',
                    'type' => 'success'
                ]
            ], JSON_UNESCAPED_UNICODE),
        ]);

        // Descontar inventario de cada producto comprado
        if (!empty($input['items']) && is_array($input['items'])) {
            $stockUpdateStmt = $pdo->prepare("
                UPDATE `products` 
                SET `stock` = GREATEST(0, `stock` - :qty),
                    `status` = CASE 
                        WHEN GREATEST(0, `stock` - :qty2) = 0 THEN 'soldout'
                        WHEN GREATEST(0, `stock` - :qty3) <= 4 THEN 'low-stock'
                        ELSE 'in-stock'
                    END,
                    `statusLabel` = CASE 
                        WHEN GREATEST(0, `stock` - :qty4) = 0 THEN 'Agotado Temporalmente'
                        WHEN GREATEST(0, `stock` - :qty5) <= 4 THEN CONCAT('¡Últimas ', GREATEST(0, `stock` - :qty6), ' unidades!')
                        ELSE CONCAT('Stock Disponible (', GREATEST(0, `stock` - :qty7), ')')
                    END
                WHERE `id` = :productId
            ");

            foreach ($input['items'] as $item) {
                if (!empty($item['productId'])) {
                    $qty = (int)($item['quantity'] ?? 1);
                    $stockUpdateStmt->execute([
                        ':qty' => $qty,
                        ':qty2' => $qty,
                        ':qty3' => $qty,
                        ':qty4' => $qty,
                        ':qty5' => $qty,
                        ':qty6' => $qty,
                        ':qty7' => $qty,
                        ':productId' => $item['productId']
                    ]);
                }
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pedido registrado exitosamente en MySQL',
            'order' => $input
        ]);
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}

if ($method === 'PUT' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de pedido requerido']);
        exit();
    }

    $id = $input['id'];
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $current = $stmt->fetch();

    if (!$current) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Pedido no encontrado']);
        exit();
    }

    $fields = [];
    $params = [':id' => $id];

    if (isset($input['status'])) {
        $fields[] = "`status` = :status";
        $params[':status'] = $input['status'];
    }

    if (isset($input['note'])) {
        $auditLog = !empty($current['auditLog']) ? json_decode($current['auditLog'], true) : [];
        $auditLog[] = [
            'time' => date('H:i'),
            'note' => $input['note'],
            'type' => 'info'
        ];
        $fields[] = "`auditLog` = :auditLog";
        $params[':auditLog'] = json_encode($auditLog, JSON_UNESCAPED_UNICODE);
    }

    if (!empty($fields)) {
        $sql = "UPDATE `orders` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute($params);
    }

    $stmt->execute([':id' => $id]);
    $updated = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Estado de pedido actualizado en MySQL',
        'order' => formatOrderRow($updated)
    ]);
    exit();
}

