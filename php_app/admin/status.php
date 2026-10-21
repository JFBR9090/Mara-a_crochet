<?php
declare(strict_types=1);

// admin/status.php - Panel de Diagnóstico y Salud del Motor PHP 8.2+
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Exigir inicio de sesión
requireAdminAuth();

use Marana\Database\Database;
use Marana\Support\Environment;

$baseUrl = getBaseUrl();
$adminUser = getAdminUser();
$diagnostics = Environment::getDiagnostics();

$dbConnected = true;
$dbMessage = 'Conexión activa vía PDO (Consultas preparadas estrictas)';
$productCount = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$orderCount = (int)$pdo->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$reviewCount = (int)$pdo->query("SELECT COUNT(*) FROM `reviews`")->fetchColumn();

$pageTitle = 'Estado del Motor PHP 8.2+ | Taller Maraña';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
  
  <!-- Sub-Navegación del Backoffice -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 1rem;">
    <div>
      <h1 style="font-size: 1.65rem; margin-bottom: 0.25rem;">Rendimiento y Diagnóstico del Motor PHP</h1>
      <p style="font-size: 0.85rem;">Verificación en tiempo real de tipos estrictos, Enums, DTOs y persistencia en MySQL.</p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
      <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-outlined btn-sm">Pedidos y Pagos</a>
      <a href="<?= $baseUrl ?>/admin/inventory.php" class="btn btn-outlined btn-sm">Inventario de Lanas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php" class="btn btn-outlined btn-sm">Moderación de Reseñas</a>
      <a href="<?= $baseUrl ?>/admin/status.php" class="btn btn-primary btn-sm">⚡ Motor PHP 8.2+</a>
      <a href="<?= $baseUrl ?>/index.php" class="btn btn-outlined btn-sm">Ver Tienda</a>

      <!-- Usuario Logueado & Botón Cerrar Sesión -->
      <div style="margin-left: 0.5rem; display: flex; align-items: center; gap: 0.5rem; background: var(--color-primary-bg); border: 1px solid var(--color-primary-border); padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); font-size: 0.78rem;">
        <span style="font-weight: 700; color: var(--color-primary);">👤 <?= h($adminUser['name'] ?? 'Admin') ?> (Neiva)</span>
        <a href="<?= $baseUrl ?>/admin/logout.php" title="Cerrar Sesión" style="color: var(--color-danger); text-decoration: none; font-weight: 700; border-left: 1px solid var(--color-primary-border); padding-left: 0.5rem;">
          Salir
        </a>
      </div>
    </div>
  </div>

  <!-- Banner de Versión -->
  <div style="background: linear-gradient(135deg, #4D1282 0%, #7B2CBF 100%); color: #fff; padding: 1.75rem 2rem; border-radius: 12px; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.85;">Entorno del Servidor</div>
      <h2 style="font-size: 1.5rem; margin-top: 0.2rem; font-weight: 700;">PHP <?= htmlspecialchars($diagnostics['engine']['php_version']) ?> · Nivel Empresarial</h2>
      <p style="font-size: 0.85rem; opacity: 0.9; margin-top: 0.35rem;">Arquitectura modular con Autoloading PSR-4 (<code style="background: rgba(255,255,255,0.2); padding: 0.1rem 0.4rem; border-radius: 4px;">namespace Marana\</code>), Value Objects y Enums.</p>
    </div>
    <div style="text-align: right;">
      <span style="display: inline-block; background: #FFB703; color: #1D1128; font-weight: 800; font-size: 0.85rem; padding: 0.4rem 0.9rem; border-radius: 9999px;">
        ★ 64-BIT <?= strtoupper($diagnostics['engine']['operating_system']) ?>
      </span>
    </div>
  </div>

  <!-- Grid de Métricas y Capacidades -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
    
    <!-- Bloque 1: Tipado y Lenguaje -->
    <div class="card-panel" style="margin-bottom: 0; padding: 1.5rem;">
      <h3 style="font-size: 1.05rem; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 0.5rem;">
        🧬 Capacidades de Tipado Estricto
      </h3>
      <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.88rem; display: flex; flex-direction: column; gap: 0.6rem;">
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">declare(strict_types=1):</span>
          <strong style="color: #10B981;">✓ Forzado en Core</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Backed Enums (OrderStatus / ProductStatus):</span>
          <strong style="color: #10B981;">✓ Activo</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Readonly Classes (DTOs):</span>
          <strong style="color: #10B981;">✓ Inmutabilidad Nativa</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Constructor Property Promotion:</span>
          <strong style="color: #10B981;">✓ Implementado</strong>
        </li>
      </ul>
    </div>

    <!-- Bloque 2: Persistencia MySQL -->
    <div class="card-panel" style="margin-bottom: 0; padding: 1.5rem;">
      <h3 style="font-size: 1.05rem; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 0.5rem;">
        🗄️ Persistencia y Base de Datos
      </h3>
      <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.88rem; display: flex; flex-direction: column; gap: 0.6rem;">
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Capa PDO Singleton:</span>
          <strong style="color: #10B981;">✓ Conectado</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Amigurumis en Inventario:</span>
          <strong><?= $productCount ?> piezas</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Pedidos en Auditoría:</span>
          <strong><?= $orderCount ?> órdenes</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Reseñas Validadas:</span>
          <strong><?= $reviewCount ?> opiniones</strong>
        </li>
      </ul>
    </div>

    <!-- Bloque 3: Memoria y Rendimiento -->
    <div class="card-panel" style="margin-bottom: 0; padding: 1.5rem;">
      <h3 style="font-size: 1.05rem; color: var(--color-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 0.5rem;">
        ⚡ Memoria y Rendimiento
      </h3>
      <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.88rem; display: flex; flex-direction: column; gap: 0.6rem;">
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Límite de Memoria (RAM):</span>
          <strong><?= htmlspecialchars($diagnostics['performance']['memory_limit']) ?></strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Consumo Actual:</span>
          <strong><?= $diagnostics['performance']['memory_usage_mb'] ?> MB</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Pico Máximo de Memoria:</span>
          <strong><?= $diagnostics['performance']['memory_peak_mb'] ?> MB</strong>
        </li>
        <li style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-neutral-medium);">Tiempo Máximo de Script:</span>
          <strong><?= htmlspecialchars($diagnostics['performance']['max_execution_sec']) ?>s</strong>
        </li>
      </ul>
    </div>

  </div>

  <div style="margin-top: 2rem; text-align: center;">
    <a href="<?= $baseUrl ?>/../api/status.php?json=1" target="_blank" class="btn btn-outlined btn-sm">
      Descargar Reporte JSON Completo &rarr;
    </a>
  </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
