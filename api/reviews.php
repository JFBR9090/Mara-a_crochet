<?php
declare(strict_types=1);

// api/reviews.php - Endpoints REST de Reseñas en PHP 8.2+ para Maraña Crochet
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

use Marana\Http\Response;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function formatReviewRow(?array $row): ?array {
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
    Response::json([
        'success' => true,
        'reviews' => $reviews,
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input['id'])) {
        Response::error('ID de reseña requerido', 400);
    }

    $stmt = $pdo->prepare("
        INSERT INTO `reviews`
        (`id`, `customerInitials`, `customerName`, `emailMasked`, `orderId`, `sku`, `productName`, `productIcon`, `rating`, `date`, `comment`, `verified`, `status`, `photos`, `workshopReply`, `rejectionReason`, `helpfulCount`)
        VALUES
        (:id, :customerInitials, :customerName, :emailMasked, :orderId, :sku, :productName, :productIcon, :rating, :date, :comment, :verified, :status, :photos, :workshopReply, :rejectionReason, :helpfulCount)
    ");

    $stmt->execute([
        ':id'              => $input['id'],
        ':customerInitials' => $input['customerInitials'] ?? 'MC',
        ':customerName'    => $input['customerName'] ?? 'Cliente Anónimo',
        ':emailMasked'     => $input['emailMasked'] ?? '',
        ':orderId'         => $input['orderId'] ?? '',
        ':sku'             => $input['sku'] ?? '',
        ':productName'     => $input['productName'] ?? '',
        ':productIcon'     => $input['productIcon'] ?? 'star',
        ':rating'          => (int)($input['rating'] ?? 5),
        ':date'            => $input['date'] ?? 'Hoy',
        ':comment'         => $input['comment'] ?? '',
        ':verified'        => !empty($input['verified']) ? 1 : 0,
        ':status'          => $input['status'] ?? 'pending',
        ':photos'          => !empty($input['photos']) ? json_encode($input['photos'], JSON_UNESCAPED_UNICODE) : null,
        ':workshopReply'   => !empty($input['workshopReply']) ? json_encode($input['workshopReply'], JSON_UNESCAPED_UNICODE) : null,
        ':rejectionReason' => $input['rejectionReason'] ?? null,
        ':helpfulCount'    => (int)($input['helpfulCount'] ?? 0),
    ]);

    Response::json([
        'success' => true,
        'message' => 'Reseña guardada con éxito en MySQL (PHP 8.2+)',
    ], 201);
}

if ($method === 'PUT' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input['id'])) {
        Response::error('ID de reseña requerido', 400);
    }

    $id = (string)$input['id'];
    $fields = [];
    $params = [':id' => $id];

    if (isset($input['status'])) {
        $fields[] = "`status` = :status";
        $params[':status'] = (string)$input['status'];
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

    Response::json([
        'success' => true,
        'message' => 'Reseña actualizada',
        'review'  => formatReviewRow($updated),
    ]);
}

Response::error("Método HTTP '{$method}' no permitido", 405);
