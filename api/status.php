<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/cors.php';

use Marana\Database\Database;
use Marana\Http\Response;
use Marana\Support\Environment;

$dbConnected = false;
$dbMessage = '';
$productCount = 0;
$orderCount = 0;

try {
    $pdo = Database::getConnection();
    $dbConnected = true;
    $dbMessage = 'Conectado a MySQL con éxito';
    $productCount = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
    $orderCount = (int)$pdo->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
} catch (Throwable $e) {
    $dbConnected = false;
    $dbMessage = $e->getMessage();
}

$diagnostics = Environment::getDiagnostics();
$diagnostics['database'] = [
    'connected'     => $dbConnected,
    'message'       => $dbMessage,
    'product_count' => $productCount,
    'order_count'   => $orderCount,
];

// Si se pide JSON vía query param o header Accept
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
if (isset($_GET['json']) || str_contains($acceptHeader, 'application/json')) {
    Response::success($diagnostics);
}

// Render HTML visual si se accede desde el navegador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maraña Crochet · Estado del Motor PHP 8.2+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #7B2CBF;
            --primary-light: #C77DFF;
            --gold: #FFB703;
            --dark: #1D1128;
            --bg: #FAF8FC;
            --card-bg: #FFFFFF;
            --border: #E8E2EE;
            --success: #10B981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--dark);
            padding: 2.5rem 1rem;
            line-height: 1.6;
        }
        .container {
            max-width: 840px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #4D1282 0%, #7B2CBF 100%);
            color: #fff;
            padding: 2rem 2.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px -5px rgba(123, 44, 191, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header h1 { font-size: 1.6rem; font-weight: 700; }
        .header p { opacity: 0.9; font-size: 0.95rem; margin-top: 0.25rem; }
        .version-badge {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.95rem;
            backdrop-filter: blur(8px);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(29, 17, 40, 0.04);
        }
        .card h2 {
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .metric {
            display: flex;
            justify-content: space-between;
            padding: 0.45rem 0;
            border-bottom: 1px dashed var(--border);
            font-size: 0.9rem;
        }
        .metric:last-child { border-bottom: none; }
        .metric-label { color: #665c72; }
        .metric-value { font-weight: 600; color: var(--dark); }
        .tag-active {
            color: var(--success);
            font-weight: 700;
        }
        .links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .btn:hover { background: #651FA4; }
        .btn-alt {
            background: #fff;
            color: var(--primary);
            border: 1.5px solid var(--primary);
        }
        .btn-alt:hover { background: #F6EDFC; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Maraña Crochet · Motor PHP Avanzado</h1>
                <p>Arquitectura tipada, PSR-4, Enums y DTOs en producción</p>
            </div>
            <div class="version-badge">
                PHP <?= htmlspecialchars($diagnostics['engine']['php_version']) ?>
            </div>
        </div>

        <div class="grid">
            <!-- Tarjeta 1: Motor y Entorno -->
            <div class="card">
                <h2>⚡ Motor PHP 8.2+</h2>
                <div class="metric">
                    <span class="metric-label">Versión de PHP:</span>
                    <span class="metric-value"><?= htmlspecialchars($diagnostics['engine']['php_version']) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Arquitectura:</span>
                    <span class="metric-value"><?= htmlspecialchars($diagnostics['engine']['architecture']) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Interfaz SAPI:</span>
                    <span class="metric-value"><?= htmlspecialchars($diagnostics['engine']['sapi']) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Sistema Operativo:</span>
                    <span class="metric-value"><?= htmlspecialchars($diagnostics['engine']['operating_system']) ?></span>
                </div>
            </div>

            <!-- Tarjeta 2: Características del Lenguaje -->
            <div class="card">
                <h2>✨ Nivel del Lenguaje</h2>
                <div class="metric">
                    <span class="metric-label">declare(strict_types=1):</span>
                    <span class="metric-value tag-active">✓ Activado</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Backed Enums:</span>
                    <span class="metric-value tag-active">✓ Soportado</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Readonly Classes (DTOs):</span>
                    <span class="metric-value tag-active">✓ Soportado</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Autoloading PSR-4:</span>
                    <span class="metric-value tag-active">✓ Marana\ Namespace</span>
                </div>
            </div>

            <!-- Tarjeta 3: Base de Datos MySQL -->
            <div class="card">
                <h2>🗄️ Base de Datos MySQL</h2>
                <div class="metric">
                    <span class="metric-label">Estado Conexión:</span>
                    <span class="metric-value <?= $dbConnected ? 'tag-active' : '' ?>">
                        <?= $dbConnected ? '✓ Conectado (PDO)' : '✗ Desconectado' ?>
                    </span>
                </div>
                <div class="metric">
                    <span class="metric-label">Amigurumis Registrados:</span>
                    <span class="metric-value"><?= $productCount ?> piezas</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Pedidos en Cola:</span>
                    <span class="metric-value"><?= $orderCount ?> órdenes</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Consultas Preparadas:</span>
                    <span class="metric-value tag-active">✓ PDO Emulated = false</span>
                </div>
            </div>

            <!-- Tarjeta 4: Rendimiento y Memoria -->
            <div class="card">
                <h2>📊 Rendimiento</h2>
                <div class="metric">
                    <span class="metric-label">Límite de Memoria:</span>
                    <span class="metric-value"><?= htmlspecialchars($diagnostics['performance']['memory_limit']) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Uso Actual de Memoria:</span>
                    <span class="metric-value"><?= $diagnostics['performance']['memory_usage_mb'] ?> MB</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Pico de Memoria:</span>
                    <span class="metric-value"><?= $diagnostics['performance']['memory_peak_mb'] ?> MB</span>
                </div>
                <div class="metric">
                    <span class="metric-label">OPcache Activo:</span>
                    <span class="metric-value <?= $diagnostics['performance']['opcache_enabled'] ? 'tag-active' : '' ?>">
                        <?= $diagnostics['performance']['opcache_enabled'] ? '✓ Habilitado' : '○ Standby' ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="links">
            <a href="products.php" class="btn">Ver API Productos (JSON)</a>
            <a href="status.php?json=1" class="btn btn-alt">Ver Diagnóstico JSON</a>
            <a href="../" class="btn btn-alt">Ir a la Tienda</a>
        </div>
    </div>
</body>
</html>
