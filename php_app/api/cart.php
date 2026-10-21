<?php
// api/cart.php - Manejo AJAX de carrito y favoritos en sesión
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $productId = $input['productId'] ?? '';
    $quantity = (int)($input['quantity'] ?? 1);

    if ($productId) {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch();

        if ($product) {
            addToCart($product, $quantity);
            echo json_encode([
                'success' => true,
                'cartCount' => getCartCount(),
                'subtotal' => getCartSubtotal(),
                'items' => getCart(),
            ]);
            exit();
        }
    }

    echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    exit();
}

if ($action === 'update') {
    $productId = $input['productId'] ?? '';
    $delta = (int)($input['delta'] ?? 0);

    if ($productId && $delta !== 0) {
        updateCartQuantity($productId, $delta);
        echo json_encode([
            'success' => true,
            'cartCount' => getCartCount(),
            'subtotal' => getCartSubtotal(),
            'items' => getCart(),
        ]);
        exit();
    }

    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit();
}

if ($action === 'remove') {
    $productId = $input['productId'] ?? '';
    if ($productId) {
        removeFromCart($productId);
        echo json_encode([
            'success' => true,
            'cartCount' => getCartCount(),
            'subtotal' => getCartSubtotal(),
            'items' => getCart(),
        ]);
        exit();
    }

    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit();
}

if ($action === 'toggle_fav') {
    $productId = $input['productId'] ?? '';
    if ($productId) {
        $isFav = toggleFavorite($productId);
        echo json_encode([
            'success' => true,
            'isFav' => $isFav,
            'favCount' => count(getFavorites()),
        ]);
        exit();
    }

    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit();
}

// Retornar estado actual
echo json_encode([
    'success' => true,
    'cartCount' => getCartCount(),
    'subtotal' => getCartSubtotal(),
    'items' => getCart(),
    'favorites' => getFavorites(),
]);

