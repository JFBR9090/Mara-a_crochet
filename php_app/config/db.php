<?php
declare(strict_types=1);

// config/db.php - Conexión centralizada con soporte para arquitectura moderna PHP 8.2+
$autoloadPaths = [
    __DIR__ . '/../../api/autoload.php',
    __DIR__ . '/../api/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
];

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

use Marana\Database\Database;

try {
    $pdo = Database::getConnection();
} catch (Throwable $e) {
    die("<div style='font-family: sans-serif; padding: 2rem; background: #fff0f0; border: 1px solid #ffcccc; border-radius: 8px; max-width: 600px; margin: 3rem auto; color: #900;'>
        <h3 style='margin-top:0;'>Error de Conexión a la Base de Datos MySQL</h3>
        <p>No se pudo conectar al servidor MySQL en XAMPP.</p>
        <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p style='font-size: 0.9em; color: #666;'>Por favor verifica que el servicio <strong>MySQL</strong> esté activo en el Panel de Control de XAMPP.</p>
    </div>");
}
