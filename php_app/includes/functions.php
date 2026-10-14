<?php
// includes/functions.php - Funciones utilitarias y manejo de estado de sesión

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar sesión de carrito y favoritos si no existen
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = ['lulu-aranita'];
}

/**
 * Retorna la URL base del proyecto para enlaces absolutos limpios
 */
function getBaseUrl(): string {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Normalizar separadores y eliminar trailing slashes innecesarios
    $path = str_replace('\\', '/', $scriptDir);
    // Si estamos en un subdirectorio como /admin o /api, obtener la raíz de la app
    if (basename($path) === 'admin' || basename($path) === 'api') {
        $path = dirname($path);
    }
    return rtrim($path, '/');
}

/**
 * Escapar contenido seguro para HTML
 */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear número a moneda chilena (CLP)
 */
function formatCLP(float|int $amount): string {
    return '$' . number_format($amount, 0, ',', '.');
}

/**
 * Manejo de mensajes Flash / Toasts
 */
function setFlash(string $type, string $message, string $subMessage = ''): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'info', 'error'
        'message' => $message,
        'subMessage' => $subMessage,
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Carrito de Compras en Sesión
 */
function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function getCartCount(): int {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += (int)$item['quantity'];
    }
    return $count;
}

function getCartSubtotal(): int {
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += ((int)$item['price']) * ((int)$item['quantity']);
    }
    return $subtotal;
}

function addToCart(array $product, int $quantity = 1): void {
    $id = $product['id'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $firstImage = '';
        if (!empty($product['images'])) {
            $imgs = is_array($product['images']) ? $product['images'] : json_decode($product['images'], true);
            $firstImage = $imgs[0]['url'] ?? '';
        }
        $_SESSION['cart'][$id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => (int)$product['price'],
            'sku' => $product['sku'],
            'image' => $firstImage,
            'quantity' => $quantity,
        ];
    }
}

function updateCartQuantity(string $productId, int $delta): void {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $delta;
        if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
            unset($_SESSION['cart'][$productId]);
        }
    }
}

function removeFromCart(string $productId): void {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart(): void {
    $_SESSION['cart'] = [];
}

/**
 * Favoritos en Sesión
 */
function getFavorites(): array {
    return $_SESSION['favorites'] ?? [];
}

function toggleFavorite(string $productId): bool {
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }
    $index = array_search($productId, $_SESSION['favorites']);
    if ($index !== false) {
        array_splice($_SESSION['favorites'], $index, 1);
        return false; // Removido
    } else {
        $_SESSION['favorites'][] = $productId;
        return true; // Agregado
    }
}
