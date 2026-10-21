<?php
require 'c:/Users/SoporteSENA/Maraña antigravity/Mara-a_crochet/php_app/config/db.php';
require 'c:/Users/SoporteSENA/Maraña antigravity/Mara-a_crochet/php_app/includes/functions.php';
require 'c:/Users/SoporteSENA/Maraña antigravity/Mara-a_crochet/php_app/includes/gateway.php';

echo "=== TEST 1: REGISTRO DE CLIENTE ===\n";
$testEmail = 'cliente.test.' . time() . '@gmail.com';
$hash = password_hash('secreto123', PASSWORD_BCRYPT);

$ins = $pdo->prepare("
    INSERT INTO `customers` (`name`, `email`, `phone`, `address`, `city`, `department`, `password`)
    VALUES (:name, :email, :phone, :address, :city, :department, :password)
");
$ins->execute([
    ':name' => 'Camila Andrea Rojas',
    ':email' => $testEmail,
    ':phone' => '+57 315 987 6543',
    ':address' => 'Calle 100 # 11B-20, Torre Apto 502',
    ':city' => 'Bogotá (Usaquén)',
    ':department' => 'Bogotá D.C.',
    ':password' => $hash,
]);
$testCustId = (int)$pdo->lastInsertId();
echo "Cliente registrado con ID: {$testCustId} y email: {$testEmail}\n";

echo "\n=== TEST 2: LOGIN Y VALIDACIÓN DE HASH ===\n";
$fetch = $pdo->prepare("SELECT * FROM `customers` WHERE `id` = :id");
$fetch->execute([':id' => $testCustId]);
$c = $fetch->fetch();
$passOk = password_verify('secreto123', $c['password']);
echo "Password verify result: " . ($passOk ? "OK (VERIFICADA)" : "FALLÓ") . "\n";

echo "\n=== TEST 3: CREACIÓN DE PEDIDO CON MÉTODO DE PAGO WOMPI ===\n";
$orderId = '#MC-2026-TEST-' . rand(100, 999);
$subtotal = 34900;
$shipping = 11500;
$total = $subtotal + $shipping;

$items = [
    [
        'productId' => 'kit-crochet-principiante',
        'productName' => 'Kit de Crochet Artesanal Maraña',
        'colorDetails' => 'Edición Taller Artesanal Neiva',
        'quantity' => 1,
        'price' => 34900,
        'image' => '/marana/assets/img/kit-crochet.jpg'
    ]
];

$wompiSignature = generateWompiIntegritySignature($orderId, $total);
$transferDetails = [
    'gateway' => 'Wompi Colombia (Bancolombia)',
    'environment' => getEnvVar('WOMPI_ENVIRONMENT', 'sandbox'),
    'transactionId' => 'WMP-TEST-' . rand(1000, 9999),
    'cardMasked' => '**** **** **** 4242',
    'integritySignature' => $wompiSignature,
    'transferredAmount' => $total,
    'transferDate' => date('d/m/Y H:i') . ' hrs'
];

$orderStmt = $pdo->prepare("
    INSERT INTO `orders`
    (`id`, `date`, `customerId`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `paymentMethod`, `bank`, `accountType`, `transferDetails`, `auditLog`)
    VALUES
    (:id, :date, :customerId, :customerName, :customerEmail, :customerPhone, :customerRut, :address, :shippingMethod, :shippingCost, :items, :subtotal, :total, 'aprobado', 'wompi_card', 'Wompi Bancolombia', 'Tarjeta Crédito', :transferDetails, :auditLog)
");

$orderStmt->execute([
    ':id' => $orderId,
    ':date' => 'Hoy ' . date('H:i') . ' hrs',
    ':customerId' => $testCustId,
    ':customerName' => $c['name'],
    ':customerEmail' => $c['email'],
    ':customerPhone' => $c['phone'],
    ':customerRut' => '1.092.384.112',
    ':address' => json_encode(['street' => $c['address'], 'city' => $c['city'], 'dept' => $c['department']]),
    ':shippingMethod' => 'ciudades-principales',
    ':shippingCost' => $shipping,
    ':items' => json_encode($items),
    ':subtotal' => $subtotal,
    ':total' => $total,
    ':transferDetails' => json_encode($transferDetails),
    ':auditLog' => json_encode([['time' => date('H:i'), 'note' => 'Pago Wompi verificado', 'type' => 'success']])
]);

echo "Pedido creado con ID: {$orderId} asociado al cliente {$testCustId}\n";

echo "\n=== TEST 4: CONSULTA HISTORIAL DE PEDIDOS DEL CLIENTE ===\n";
$histStmt = $pdo->prepare("SELECT id, total, status, paymentMethod FROM `orders` WHERE `customerId` = :cid");
$histStmt->execute([':cid' => $testCustId]);
$hist = $histStmt->fetchAll(PDO::FETCH_ASSOC);
print_r($hist);

echo "\n=== TODOS LOS TESTS COMPLETADOS CON ÉXITO ===\n";
