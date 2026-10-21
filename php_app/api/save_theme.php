<?php
declare(strict_types=1);

// api/save_theme.php - Guardar preferencia de tema (claro / oscuro) en sesión y base de datos
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents('php://input'), true);
$theme = $input['theme'] ?? 'light';

if (!in_array($theme, ['light', 'dark'], true)) {
    $theme = 'light';
}

$_SESSION['customer_theme'] = $theme;

// Si el cliente está autenticado, persistir en MySQL
if (isCustomerLoggedIn()) {
    $customerId = (int)$_SESSION['customer_id'];
    try {
        $stmt = $pdo->prepare("UPDATE `customers` SET `theme_preference` = :theme WHERE `id` = :id");
        $stmt->execute([
            ':theme' => $theme,
            ':id' => $customerId
        ]);
    } catch (Throwable $e) {
        // Fallback silencioso si la conexión falla
    }
}

echo json_encode([
    'success' => true,
    'theme' => $theme
]);
