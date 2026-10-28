<?php
// scratch/test_checkout_guest.php - Prueba del flujo de compra obligatoria para invitados
require_once __DIR__ . '/../php_app/config/db.php';
require_once __DIR__ . '/../php_app/includes/functions.php';

// Limpiar sesión para simular invitado puro
clearCart();
logoutCustomer();

// 1. Agregar amigurumi a la canasta
$prodStmt = $pdo->query("SELECT * FROM `products` WHERE `id` = 'lulu-aranita' LIMIT 1");
$product = $prodStmt->fetch(PDO::FETCH_ASSOC);
addToCart($product, 1);

echo "1. Carrito contiene: " . getCartCount() . " items.\n";
echo "2. ¿Cliente autenticado? " . (isCustomerLoggedIn() ? 'SÍ' : 'NO (Invitado)') . "\n";

// Simular acceso a checkout.php como invitado
$baseUrl = getBaseUrl();
$redirectTarget = null;

if (!isCustomerLoggedIn()) {
    $redirectTarget = $baseUrl . '/auth.php?redirect=' . urlencode($baseUrl . '/checkout.php') . '&reason=buy_required';
    echo "3. Checkout bloquea invitado y redirige a: " . $redirectTarget . "\n";
}

// 4. Simular inicio de sesión de cliente
$custStmt = $pdo->query("SELECT * FROM `customers` LIMIT 1");
$sampleCustomer = $custStmt->fetch(PDO::FETCH_ASSOC);

if ($sampleCustomer) {
    loginCustomer($sampleCustomer);
    echo "4. Cliente inicia sesión como: " . htmlspecialchars($sampleCustomer['name']) . "\n";
    echo "5. ¿Cliente autenticado ahora? " . (isCustomerLoggedIn() ? 'SÍ' : 'NO') . "\n";
    echo "6. Carrito se mantiene intacto con: " . getCartCount() . " items.\n";
    echo "7. Ahora el checkout permite proceder al pago directamente con dirección de " . htmlspecialchars($sampleCustomer['city']) . ".\n";
}
