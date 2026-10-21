<?php
// admin/inventory.php - Gestión de inventario de amigurumis y materias primas del taller
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Exigir inicio de sesión
requireAdminAuth();

$baseUrl = getBaseUrl();
$adminUser = getAdminUser();

// 1. Procesar Actualizaciones de Stock o Precio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_product') {
    $pId = $_POST['productId'] ?? '';
    $newStock = max(0, (int)($_POST['stock'] ?? 0));
    $newPrice = max(0, (int)($_POST['price'] ?? 0));

    if ($pId) {
        $status = 'in-stock';
        $statusLabel = "Stock Disponible ({$newStock})";
        if ($newStock === 0) {
            $status = 'soldout';
            $statusLabel = 'Agotado Temporalmente';
        } elseif ($newStock <= 4) {
            $status = 'low-stock';
            $statusLabel = "¡Últimas {$newStock} unidades!";
        }

        $usd = round($newPrice / 900, 2);

        $upStmt = $pdo->prepare("
            UPDATE `products` 
            SET `stock` = :stock, `price` = :price, `usdApprox` = :usd, `status` = :st, `statusLabel` = :stl 
            WHERE `id` = :id
        ");
        $upStmt->execute([
            ':stock' => $newStock,
            ':price' => $newPrice,
            ':usd' => $usd,
            ':st' => $status,
            ':stl' => $statusLabel,
            ':id' => $pId
        ]);

        setFlash('success', 'Inventario actualizado', "Stock ({$newStock}) y precio guardados para el producto.");
        header('Location: ' . $baseUrl . '/admin/inventory.php');
        exit();
    }
}

// 2. Obtener Productos
$stmt = $pdo->query("SELECT * FROM `products` ORDER BY `name` ASC");
$products = $stmt->fetchAll();

// Materias primas del taller (Chenille, hilados, ojos de seguridad, vellón, etc.)
$materials = [
    [
        'name' => 'Lana Chenille Suave Lavanda Cósmica',
        'type' => 'Lana',
        'stock' => 18,
        'unit' => 'ovillos (100g)',
        'min' => 5,
        'status' => 'ok',
        'supplier' => 'Lanas del Sur SpA'
    ],
    [
        'name' => 'Hilo Algodón Peinado Tangüis Púrpura Profundo',
        'type' => 'Lana',
        'stock' => 12,
        'unit' => 'madejas (100g)',
        'min' => 4,
        'status' => 'ok',
        'supplier' => 'Hilandería Textil Santiago'
    ],
    [
        'name' => 'Ojos de Seguridad Negros 12mm con Traba',
        'type' => 'Insumo',
        'stock' => 14,
        'unit' => 'pares',
        'min' => 25,
        'status' => 'critical',
        'supplier' => 'Mercería Central'
    ],
    [
        'name' => 'Vellón Siliconado Hipoalergénico Extra Soft',
        'type' => 'Relleno',
        'stock' => 8.5,
        'unit' => 'kg',
        'min' => 3,
        'status' => 'ok',
        'supplier' => 'Distribuidora Rellenos Chile'
    ],
    [
        'name' => 'Cajas de Embalaje Kraft & Flores de Lavanda',
        'type' => 'Empaque',
        'stock' => 42,
        'unit' => 'unidades',
        'min' => 15,
        'status' => 'ok',
        'supplier' => 'EcoPack Sustentable'
    ]
];

$pageTitle = 'Inventario & Lanas | Backoffice Taller Maraña';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
  
  <!-- Sub-Navegación del Backoffice -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 1rem;">
    <div>
      <h1 style="font-size: 1.65rem; margin-bottom: 0.25rem;">Control de Inventario de Amigurumis</h1>
      <p style="font-size: 0.85rem;">Monitoreo de existencias en aguja, precios y materias primas del taller.</p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
      <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-outlined btn-sm">Pedidos y Pagos</a>
      <a href="<?= $baseUrl ?>/admin/inventory.php" class="btn btn-primary btn-sm">Inventario de Lanas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php" class="btn btn-outlined btn-sm">Moderación de Reseñas</a>
      <a href="<?= $baseUrl ?>/admin/status.php" class="btn btn-outlined btn-sm" style="border-color: #9D4EDD; color: #7B2CBF;">⚡ Motor PHP 8.2+</a>
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

  <!-- Tabla Principal de Productos -->
  <div class="card-panel" style="padding: 0; overflow: hidden; margin-bottom: 3rem;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-neutral-border); display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.15rem; margin: 0;">Catálogo de Criaturas y Stock Disponible</h3>
      <span style="font-size: 0.8rem; color: var(--color-neutral-light);">Total: <?= count($products) ?> amigurumis</span>
    </div>

    <table class="table-custom">
      <thead>
        <tr>
          <th>Amigurumi</th>
          <th>SKU / Colección</th>
          <th>Precio</th>
          <th>Nivel de Stock</th>
          <th>Estado</th>
          <th style="text-align: center;">Edición Rápida</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): 
          $imgs = !empty($p['images']) ? json_decode($p['images'], true) : [];
          $thumb = $imgs[0]['url'] ?? '';
          $stock = (int)$p['stock'];
          $max = max(1, (int)$p['maxStock']);
          $pct = min(100, round(($stock / $max) * 100));
        ?>
          <tr>
            <td>
              <div style="display: flex; gap: 0.75rem; align-items: center;">
                <img src="<?= h($thumb) ?>" alt="<?= h($p['name']) ?>" style="width: 46px; height: 46px; border-radius: var(--radius-sm); object-fit: cover;">
                <div>
                  <strong style="font-size: 0.9rem; color: var(--color-neutral-dark);"><?= h($p['name']) ?></strong>
                  <div style="font-size: 0.72rem; color: var(--color-neutral-light);"><?= number_format($p['craftHours'], 1) ?> hrs de tejido</div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight: 600; font-size: 0.82rem;"><?= h($p['sku']) ?></div>
              <div style="font-size: 0.72rem; color: var(--color-primary);"><?= h($p['categoryLabel']) ?></div>
            </td>
            <td style="font-weight: 700; font-size: 0.95rem;">
              <?= formatCLP($p['price']) ?>
            </td>
            <td style="min-width: 160px;">
              <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem;">
                <span><?= $stock ?> / <?= $max ?> unidades</span>
                <span><?= $pct ?>%</span>
              </div>
              <div class="progress-container">
                <div class="progress-bar <?= ($stock <= 2) ? 'danger' : (($stock <= 4) ? 'warning' : '') ?>" style="width: <?= $pct ?>%;"></div>
              </div>
            </td>
            <td>
              <span class="status-pill status-<?= ($stock > 0) ? 'entregado' : 'rechazado' ?>">
                <?= h($p['statusLabel']) ?>
              </span>
            </td>
            <td style="text-align: center;">
              <!-- Botón de etiqueta con icono de edición según requerimiento -->
              <button 
                type="button" 
                class="tag-btn-edit" 
                onclick="openEditProductModal('<?= h($p['id']) ?>', '<?= addslashes(h($p['name'])) ?>', <?= (int)$p['stock'] ?>, <?= (int)$p['price'] ?>)"
                title="Editar Stock y Precio"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                Editar
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Materias Primas del Taller -->
  <div class="card-panel" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-neutral-border);">
      <h3 style="font-size: 1.15rem; margin-bottom: 0.2rem;">Materias Primas & Insumos Críticos</h3>
      <p style="font-size: 0.8rem; margin: 0;">Stock de hilos de algodón Tangüis, vellón siliconado y cajas aromáticas.</p>
    </div>

    <table class="table-custom">
      <thead>
        <tr>
          <th>Insumo / Material</th>
          <th>Tipo</th>
          <th>Stock Actual</th>
          <th>Mínimo Requerido</th>
          <th>Estado</th>
          <th>Proveedor</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($materials as $m): ?>
          <tr>
            <td style="font-weight: 600;"><?= h($m['name']) ?></td>
            <td style="font-size: 0.82rem; color: var(--color-primary);"><?= h($m['type']) ?></td>
            <td style="font-weight: 700;"><?= $m['stock'] ?> <?= h($m['unit']) ?></td>
            <td style="font-size: 0.82rem; color: var(--color-neutral-light);"><?= $m['min'] ?> <?= h($m['unit']) ?></td>
            <td>
              <?php if ($m['status'] === 'critical'): ?>
                <span class="status-pill status-rechazado">⚠️ Nivel Crítico</span>
              <?php elseif ($m['status'] === 'warning'): ?>
                <span class="status-pill status-revision">Bajo Mínimo</span>
              <?php else: ?>
                <span class="status-pill status-entregado">✓ Óptimo</span>
              <?php endif; ?>
            </td>
            <td style="font-size: 0.8rem; color: var(--color-neutral-light);"><?= h($m['supplier']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Minimalista para Edición Rápida de Producto -->
<div id="edit-product-modal" style="display: none; position: fixed; inset: 0; background: rgba(29, 17, 40, 0.45); backdrop-filter: blur(2px); z-index: 150; align-items: center; justify-content: center; padding: 1.5rem;">
  <div class="card-panel" style="max-width: 440px; width: 100%; margin: 0; box-shadow: var(--shadow-lg);">
    <div class="card-header">
      <h3 id="modal-product-name" style="font-size: 1.1rem;">Editar Amigurumi</h3>
      <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    </div>

    <form action="<?= $baseUrl ?>/admin/inventory.php" method="POST">
      <input type="hidden" name="action" value="update_product">
      <input type="hidden" name="productId" id="modal-product-id">

      <div class="form-group">
        <label class="form-label" for="modal-stock">Stock Físico en Taller (Unidades):</label>
        <input type="number" id="modal-stock" name="stock" min="0" required class="form-control">
      </div>

      <div class="form-group">
        <label class="form-label" for="modal-price">Precio de Venta (CLP):</label>
        <input type="number" id="modal-price" name="price" min="0" step="100" required class="form-control">
      </div>

      <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
        <button type="button" onclick="closeEditModal()" class="btn btn-outlined btn-sm">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-sm">Guardar en MySQL</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEditProductModal(id, name, stock, price) {
    document.getElementById('modal-product-id').value = id;
    document.getElementById('modal-product-name').textContent = name;
    document.getElementById('modal-stock').value = stock;
    document.getElementById('modal-price').value = price;
    const modal = document.getElementById('edit-product-modal');
    modal.style.display = 'flex';
  }

  function closeEditModal() {
    document.getElementById('edit-product-modal').style.display = 'none';
  }
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
