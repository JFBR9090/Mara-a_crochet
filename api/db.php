<?php
declare(strict_types=1);

// api/db.php - Conexión centralizada y compatible para Maraña Crochet (PHP 8.2+)
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/autoload.php';

use Marana\Database\Database;

try {
    $pdo = Database::getConnection();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error'   => 'Error de conexión a la base de datos MySQL en XAMPP',
        'details' => $e->getMessage(),
    ]);
    exit();
}
