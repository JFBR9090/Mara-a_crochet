<?php
// config/db.php - Configuración de conexión PDO y base de datos MySQL para Maraña Crochet

$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbUser = 'root';
$dbPass = '';
$dbName = 'marana_crochet';

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Asegurar selección de base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");

} catch (PDOException $e) {
    die("<div style='font-family: sans-serif; padding: 2rem; background: #fff0f0; border: 1px solid #ffcccc; border-radius: 8px; max-width: 600px; margin: 3rem auto; color: #900;'>
        <h3 style='margin-top:0;'>Error de Conexión a la Base de Datos MySQL</h3>
        <p>No se pudo conectar al servidor MySQL en XAMPP.</p>
        <p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p style='font-size: 0.9em; color: #666;'>Por favor verifica que el servicio <strong>MySQL</strong> esté activo en el Panel de Control de XAMPP.</p>
    </div>");
}
