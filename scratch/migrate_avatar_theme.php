<?php
require_once __DIR__ . '/../php_app/config/db.php';

try {
    // 1. Verificar columnas actuales en customers
    $stmt = $pdo->query("SHOW COLUMNS FROM `customers`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas actuales en customers: " . implode(', ', $columns) . "\n";

    if (!in_array('avatar', $columns)) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `password`");
        echo "Columna 'avatar' agregada exitosamente.\n";
    } else {
        echo "Columna 'avatar' ya existe.\n";
    }

    if (!in_array('theme_preference', $columns)) {
        $pdo->exec("ALTER TABLE `customers` ADD COLUMN `theme_preference` VARCHAR(20) NOT NULL DEFAULT 'light' AFTER `avatar`");
        echo "Columna 'theme_preference' agregada exitosamente.\n";
    } else {
        echo "Columna 'theme_preference' ya existe.\n";
    }

    // Comprobar clientes existentes
    $cust = $pdo->query("SELECT id, name, email, avatar, theme_preference FROM `customers`")->fetchAll(PDO::FETCH_ASSOC);
    echo "Clientes:\n";
    print_r($cust);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
