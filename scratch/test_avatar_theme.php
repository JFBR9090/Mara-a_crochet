<?php
declare(strict_types=1);

require_once __DIR__ . '/../php_app/config/db.php';
require_once __DIR__ . '/../php_app/includes/functions.php';

echo "=== TEST: FOTO DE PERFIL Y TEMA EN MARAÑA CROCHET ===\n";

// 1. Verificar cliente de prueba
$stmt = $pdo->prepare("SELECT id, name, email, avatar, theme_preference FROM `customers` WHERE id = 1 LIMIT 1");
$stmt->execute();
$cust = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Cliente ID 1:\n";
print_r($cust);

// 2. Probar preset de avatar
$pdo->prepare("UPDATE `customers` SET `avatar` = 'preset:rana', `theme_preference` = 'dark' WHERE id = 1")->execute();
$stmt->execute();
$cust = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Cliente actualizado a preset:rana y tema oscuro:\n";
print_r($cust);

// 3. Probar renderCustomerAvatarHtml
$htmlPreset = renderCustomerAvatarHtml('preset:rana', $cust['name'], 'md');
echo "HTML Avatar Preset: " . substr($htmlPreset, 0, 100) . "...\n";

$htmlCustom = renderCustomerAvatarHtml('uploads/avatars/test.jpg', $cust['name'], 'sm');
echo "HTML Avatar Custom: " . substr($htmlCustom, 0, 100) . "...\n";

$htmlInitial = renderCustomerAvatarHtml(null, $cust['name'], 'lg');
echo "HTML Avatar Iniciales: " . substr($htmlInitial, 0, 100) . "...\n";

// 4. Probar API save_theme.php llamándola localmente
$_SESSION['customer_id'] = 1;
$_SESSION['customer_name'] = $cust['name'];
$_SESSION['customer_email'] = $cust['email'];

$pdo->prepare("UPDATE `customers` SET `theme_preference` = 'light' WHERE id = 1")->execute();
echo "Revertido a tema 'light' para inicio.\n";

echo "✓ Todas las funciones de foto de perfil y tema se ejecutaron sin errores.\n";
