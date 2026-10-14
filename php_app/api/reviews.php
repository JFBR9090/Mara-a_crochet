<?php
// api/reviews.php - Endpoint REST para reseñas
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function formatReviewRow($row) {
    if (!$row) return null;
    $row['rating'] = (int)$row['rating'];
    $row['helpfulCount'] = (int)$row['helpfulCount'];
    $row['verified'] = (bool)$row['verified'];
    $row['photos'] = !empty($row['photos']) ? json_decode($row['photos'], true) : [];
    $row['workshopReply'] = !empty($row['workshopReply']) ? json_decode($row['workshopReply'], true) : null;
    return $row;
}

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM `reviews` ORDER BY `created_at` DESC, `id` DESC");
    $reviews = array_map('formatReviewRow', $stmt->fetchAll());
    echo json_encode(['success' => true, 'reviews' => $reviews]);
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de reseña requerido']);
        exit();
    }

    $stmt = $pdo->prepare("
        INSERT INTO `reviews`
        (`id`, `customerInitials`, `customerName`, `emailMasked`, `orderId`, `sku`, `productName`, `productIcon`, `rating`, `date`, `comment`, `verified`, `status`, `photos`, `workshopReply`, `rejectionReason`, `helpfulCount`)
        VALUES
        (:id, :customerInitials, :customerName, :emailMasked, :orderId, :sku, :productName, :productIcon, :rating, :date, :comment, :verified, :status, :photos, :workshopReply, :rejectionReason, :helpfulCount)
    ");

    $stmt->execute([
        ':id' => $input['id'],
        ':customerInitials' => $input['customerInitials'] ?? 'MC',
        ':customerName' => $input['customerName'],
        ':emailMasked' => $input['emailMasked'] ?? '',
        ':orderId' => $input['orderId'] ?? '',
        ':sku' => $input['sku'] ?? '',
        ':productName' => $input['productName'] ?? '',
        ':productIcon' => $input['productIcon'] ?? 'star',
        ':rating' => (int)($input['rating'] ?? 5),
        ':date' => $input['date'] ?? 'Hoy',
        ':comment' => $input['comment'],
        ':verified' => !empty($input['verified']) ? 1 : 0,
        ':status' => $input['status'] ?? 'pending',
        ':photos' => !empty($input['photos']) ? json_encode($input['photos'], JSON_UNESCAPED_UNICODE) : null,
        ':workshopReply' => !empty($input['workshopReply']) ? json_encode($input['workshopReply'], JSON_UNESCAPED_UNICODE) : null,
        ':rejectionReason' => $input['rejectionReason'] ?? null,
        ':helpfulCount' => (int)($input['helpfulCount'] ?? 0),
    ]);

    echo json_encode(['success' => true, 'message' => 'Reseña guardada con éxito']);
    exit();
}

if ($method === 'PUT' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de reseña requerido']);
        exit();
    }

    $id = $input['id'];
    $fields = [];
    $params = [':id' => $id];

    if (isset($input['status'])) {
        $fields[] = "`status` = :status";
        $params[':status'] = $input['status'];
    }

    if (isset($input['workshopReply'])) {
        $fields[] = "`workshopReply` = :workshopReply";
        $params[':workshopReply'] = json_encode($input['workshopReply'], JSON_UNESCAPED_UNICODE);
    }

    if (!empty($fields)) {
        $sql = "UPDATE `reviews` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $updateStmt = $pdo->prepare($sql);
        $updateStmt->execute($params);
    }

    $stmt = $pdo->prepare("SELECT * FROM `reviews` WHERE `id` = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $updated = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Reseña actualizada en MySQL',
        'review' => formatReviewRow($updated)
    ]);
    exit();
}
