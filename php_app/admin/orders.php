<?php
// admin/orders.php - Gestión de pedidos y comprobantes en el Taller Maraña
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$baseUrl = getBaseUrl();

// 1. Procesar Actualizaciones de Estado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = $_POST['orderId'] ?? '';
    $newStatus = $_POST['newStatus'] ?? '';
    $note = trim($_POST['auditNote'] ?? '');

    if ($action === 'update_status' && $orderId && $newStatus) {
        $getStmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1");
        $getStmt->execute([':id' => $orderId]);
        $currentOrder = $getStmt->fetch();

        if ($currentOrder) {
            $auditLog = !empty($currentOrder['auditLog']) ? json_decode($currentOrder['auditLog'], true) : [];
            $auditLog[] = [
                'time' => date('H:i'),
                'note' => $note ?: "Estado cambiado a " . strtoupper($newStatus) . " por tallerista",
                'type' => ($newStatus === 'rechazado') ? 'warning' : 'info'
            ];

            $updateStmt = $pdo->prepare("
                UPDATE `orders` 
                SET `status` = :st, `auditLog` = :al 
                WHERE `id` = :id
            ");
            $updateStmt->execute([
                ':st' => $newStatus,
                ':al' => json_encode($auditLog, JSON_UNESCAPED_UNICODE),
                ':id' => $orderId,
            ]);

            setFlash('success', "Pedido {$orderId} actualizado", "Nuevo estado: " . strtoupper($newStatus));
            header('Location: ' . $baseUrl . '/admin/orders.php?selected=' . urlencode($orderId));
            exit();
        }
    }
}

// 2. Filtros y Búsqueda
$statusFilter = $_GET['status'] ?? 'todos';
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM `orders` WHERE 1=1";
$params = [];

if ($statusFilter !== 'todos') {
    $sql .= " AND `status` = :st";
    $params[':st'] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (`id` LIKE :q OR `customerName` LIKE :q OR `customerRut` LIKE :q OR `customerEmail` LIKE :q)";
    $params[':q'] = "%{$searchQuery}%";
}

$sql .= " ORDER BY `created_at` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// 3. Métricas KPI
$allOrdersStmt = $pdo->query("SELECT * FROM `orders`");
$allOrders = $allOrdersStmt->fetchAll();

$totalSales = array_reduce($allOrders, fn($acc, $o) => $acc + (int)$o['total'], 0);
$countRevision = count(array_filter($allOrders, fn($o) => $o['status'] === 'revision' || $o['status'] === 'pendiente'));
$countTaller = count(array_filter($allOrders, fn($o) => $o['status'] === 'taller'));
$countDespachados = count(array_filter($allOrders, fn($o) => $o['status'] === 'despachado' || $o['status'] === 'entregado'));

// Pedido seleccionado para el panel lateral
$selectedId = $_GET['selected'] ?? ($orders[0]['id'] ?? '');
$selectedOrder = null;
if ($selectedId) {
    foreach ($allOrders as $o) {
        if ($o['id'] === $selectedId) {
            $selectedOrder = $o;
            break;
        }
    }
}
if (!$selectedOrder && !empty($orders)) {
    $selectedOrder = $orders[0];
}

$pageTitle = 'Control de Pedidos | Backoffice Taller Maraña';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
  
  <!-- Sub-Navegación del Backoffice -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 1rem;">
    <div>
      <h1 style="font-size: 1.65rem; margin-bottom: 0.25rem;">Panel del Taller de Amigurumis</h1>
      <p style="font-size: 0.85rem;">Validación de transferencias bancarias TEF, empaque y despacho de pedidos.</p>
    </div>

    <!-- Pestañas de Navegación del Administrador -->
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-primary btn-sm">Pedidos y Pagos</a>
      <a href="<?= $baseUrl ?>/admin/inventory.php" class="btn btn-outlined btn-sm">Inventario de Lanas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php" class="btn btn-outlined btn-sm">Moderación de Reseñas</a>
      <a href="<?= $baseUrl ?>/index.php" class="btn btn-inverted btn-sm">Ver Tienda &rarr;</a>
    </div>
  </div>

  <!-- Tarjetas KPI Minimalistas -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem;">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-neutral-light); margin-bottom: 0.35rem;">Total de Pedidos</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark);"><?= count($allOrders) ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-top: 0.25rem;">Registrados en MySQL</div>
    </div>

    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid var(--color-warning);">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-warning); margin-bottom: 0.35rem;">Por Validar (TEF)</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-warning);"><?= $countRevision ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-top: 0.25rem;">Comprobantes en revisión</div>
    </div>

    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid var(--color-primary);">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-primary); margin-bottom: 0.35rem;">En Preparación / Taller</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-primary);"><?= $countTaller ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-top: 0.25rem;">Empaque con lavanda y certificado</div>
    </div>

    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid var(--color-success);">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-success); margin-bottom: 0.35rem;">Ventas Totales</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-success);"><?= formatCLP($totalSales) ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-top: 0.25rem;">Transferencias confirmadas</div>
    </div>
  </div>

  <!-- Barra de Filtros y Buscador de Pedidos -->
  <div class="filters-bar" style="margin-top: 0;">
    <div class="category-pills">
      <a href="<?= $baseUrl ?>/admin/orders.php" class="cat-pill <?= ($statusFilter === 'todos') ? 'active' : '' ?>">Todos los Pedidos</a>
      <a href="<?= $baseUrl ?>/admin/orders.php?status=revision" class="cat-pill <?= ($statusFilter === 'revision') ? 'active' : '' ?>">En Revisión</a>
      <a href="<?= $baseUrl ?>/admin/orders.php?status=taller" class="cat-pill <?= ($statusFilter === 'taller') ? 'active' : '' ?>">En Taller</a>
      <a href="<?= $baseUrl ?>/admin/orders.php?status=despachado" class="cat-pill <?= ($statusFilter === 'despachado') ? 'active' : '' ?>">Despachados</a>
      <a href="<?= $baseUrl ?>/admin/orders.php?status=entregado" class="cat-pill <?= ($statusFilter === 'entregado') ? 'active' : '' ?>">Entregados</a>
      <a href="<?= $baseUrl ?>/admin/orders.php?status=rechazado" class="cat-pill <?= ($statusFilter === 'rechazado') ? 'active' : '' ?>">Rechazados</a>
    </div>

    <!-- Buscador con icono de lupa -->
    <form action="<?= $baseUrl ?>/admin/orders.php" method="GET" style="display: flex; gap: 0.5rem; max-width: 300px; width: 100%;">
      <?php if ($statusFilter !== 'todos'): ?>
        <input type="hidden" name="status" value="<?= h($statusFilter) ?>">
      <?php endif; ?>
      <div class="search-wrapper">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="q" value="<?= h($searchQuery) ?>" placeholder="Buscar cliente, RUT, orden..." class="search-input" style="padding-top: 0.45rem; padding-bottom: 0.45rem;">
      </div>
    </form>
  </div>

  <!-- Layout Principal de Pedidos: Grilla de Tabla + Panel Detalle -->
  <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;" class="admin-orders-grid">
    <style>
      @media (min-width: 960px) {
        .admin-orders-grid { grid-template-columns: 1.35fr 1fr !important; }
      }
    </style>

    <!-- Lista de Pedidos -->
    <div class="card-panel" style="margin-bottom: 0; padding: 0; overflow: hidden;">
      <table class="table-custom">
        <thead>
          <tr>
            <th>Código</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th style="text-align: right;">Total</th>
            <th style="text-align: center;">Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--color-neutral-light);">
                No se encontraron pedidos con el filtro actual.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $ord): 
              $isSel = ($selectedOrder && $selectedOrder['id'] === $ord['id']);
              $statusClass = 'status-' . $ord['status'];
            ?>
              <tr style="background-color: <?= $isSel ? 'var(--color-primary-subtle)' : 'transparent' ?>; cursor: pointer;" onclick="window.location.href='<?= $baseUrl ?>/admin/orders.php?selected=<?= urlencode($ord['id']) ?>&status=<?= urlencode($statusFilter) ?>';">
                <td>
                  <strong style="color: var(--color-primary);"><?= h($ord['id']) ?></strong>
                </td>
                <td>
                  <div style="font-weight: 600; font-size: 0.88rem;"><?= h($ord['customerName']) ?></div>
                  <div style="font-size: 0.72rem; color: var(--color-neutral-light);"><?= h($ord['customerRut']) ?></div>
                </td>
                <td style="font-size: 0.78rem; color: var(--color-neutral-light);"><?= h($ord['date']) ?></td>
                <td>
                  <span class="status-pill <?= $statusClass ?>"><?= strtoupper(h($ord['status'])) ?></span>
                </td>
                <td style="text-align: right; font-weight: 700;"><?= formatCLP($ord['total']) ?></td>
                <td style="text-align: center;">
                  <!-- Botón de Etiqueta con icono de edición según requerimiento -->
                  <a href="<?= $baseUrl ?>/admin/orders.php?selected=<?= urlencode($ord['id']) ?>" class="tag-btn-edit" title="Gestionar Pedido">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    Revisar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Panel de Detalle y Gestión del Pedido Seleccionado -->
    <?php if ($selectedOrder): 
      $selItems = !empty($selectedOrder['items']) ? json_decode($selectedOrder['items'], true) : [];
      $selAddress = !empty($selectedOrder['address']) ? json_decode($selectedOrder['address'], true) : [];
      $selTransfer = !empty($selectedOrder['transferDetails']) ? json_decode($selectedOrder['transferDetails'], true) : [];
      $selAudit = !empty($selectedOrder['auditLog']) ? json_decode($selectedOrder['auditLog'], true) : [];
    ?>
      <div class="card-panel" style="margin-bottom: 0;">
        <div class="card-header">
          <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-primary);">Ficha de Gestión de Pedido</div>
            <h3 style="font-size: 1.25rem;"><?= h($selectedOrder['id']) ?></h3>
          </div>
          <span class="status-pill status-<?= h($selectedOrder['status']) ?>"><?= strtoupper(h($selectedOrder['status'])) ?></span>
        </div>

        <!-- Acciones Rápidas de Taller -->
        <div style="background-color: var(--color-neutral-bg); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.25rem; border: 1px solid var(--color-neutral-border-light);">
          <div style="font-size: 0.8rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-neutral-dark);">Flujo de Validación y Estado:</div>
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            
            <!-- Botón Aprobar Pago -->
            <form action="<?= $baseUrl ?>/admin/orders.php" method="POST" style="display: inline;">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="orderId" value="<?= h($selectedOrder['id']) ?>">
              <input type="hidden" name="newStatus" value="taller">
              <input type="hidden" name="auditNote" value="Transferencia aprobada por el tallerista. En preparación para empaque.">
              <button type="submit" class="btn btn-primary btn-sm">
                ✓ Aprobar TEF (Taller)
              </button>
            </form>

            <!-- Botón Marcar Despachado -->
            <form action="<?= $baseUrl ?>/admin/orders.php" method="POST" style="display: inline;">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="orderId" value="<?= h($selectedOrder['id']) ?>">
              <input type="hidden" name="newStatus" value="despachado">
              <input type="hidden" name="auditNote" value="Paquete despachado vía transporte oficial con aroma a lavanda.">
              <button type="submit" class="btn btn-outlined btn-sm">
                📦 Marcar Despachado
              </button>
            </form>

            <!-- Botón Marcar Entregado -->
            <form action="<?= $baseUrl ?>/admin/orders.php" method="POST" style="display: inline;">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="orderId" value="<?= h($selectedOrder['id']) ?>">
              <input type="hidden" name="newStatus" value="entregado">
              <input type="hidden" name="auditNote" value="Entrega confirmada con el destinatario final.">
              <button type="submit" class="btn btn-secondary btn-sm">
                ✓ Entregado
              </button>
            </form>

            <!-- Botón Rechazar Comprobante -->
            <form action="<?= $baseUrl ?>/admin/orders.php" method="POST" style="display: inline;" onsubmit="const r = prompt('Ingresa el motivo del rechazo del comprobante:'); if (!r) return false; this.auditNote.value = 'Comprobante no válido: ' + r;">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="orderId" value="<?= h($selectedOrder['id']) ?>">
              <input type="hidden" name="newStatus" value="rechazado">
              <input type="hidden" name="auditNote" value="Comprobante bancario no válido o monto no coincide">
              <button type="submit" class="btn btn-sm" style="background-color: var(--color-danger); color: #fff;">
                ✕ Rechazar
              </button>
            </form>
          </div>
        </div>

        <!-- Comprobante Bancario Transferencia -->
        <div style="background-color: #FFFFFF; border: 1px solid var(--color-primary-border); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.25rem;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <strong style="color: var(--color-primary); font-size: 0.85rem;">Validación Bancaria TEF:</strong>
            <span style="font-size: 0.75rem; background: var(--color-primary-subtle); color: var(--color-primary); padding: 0.2rem 0.5rem; border-radius: var(--radius-sm); font-weight: 600;">
              Folio: <?= h($selTransfer['folio'] ?? '94827103') ?>
            </span>
          </div>
          <div style="font-size: 0.82rem; line-height: 1.45; color: var(--color-neutral-dark);">
            Banco: <strong><?= h($selectedOrder['bank']) ?></strong> (<?= h($selectedOrder['accountType']) ?>)<br>
            Monto Transferido: <strong><?= formatCLP($selectedOrder['total']) ?></strong><br>
            Comprobante adjunto: <em><?= h($selTransfer['voucherFilename'] ?? 'comprobante_transferencia.pdf') ?></em>
          </div>
        </div>

        <!-- Destinatario y Despacho -->
        <div style="font-size: 0.85rem; line-height: 1.4; margin-bottom: 1.25rem;">
          <div style="font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Destinatario:</div>
          <?= h($selectedOrder['customerName']) ?> (<?= h($selectedOrder['customerRut']) ?>)<br>
          <?= h($selAddress['street'] ?? '') ?>, <?= h($selAddress['dept'] ?? '') ?><br>
          <?= h($selAddress['comuna'] ?? '') ?>, <?= h($selAddress['region'] ?? '') ?><br>
          Tel: <a href="tel:<?= h($selectedOrder['customerPhone']) ?>" style="color: var(--color-primary); font-weight: 600;"><?= h($selectedOrder['customerPhone']) ?></a>
        </div>

        <!-- Ítems del Pedido -->
        <div style="border-top: 1px solid var(--color-neutral-border-light); padding-top: 1rem; margin-bottom: 1rem;">
          <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 0.5rem;">Amigurumis a Empacar:</div>
          <?php foreach ($selItems as $it): ?>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 0.35rem;">
              <span><?= (int)$it['quantity'] ?>x <?= h($it['productName']) ?></span>
              <span style="font-weight: 600;"><?= formatCLP($it['price'] * $it['quantity']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Dedicatoria -->
        <?php if (!empty($selectedOrder['giftDedication'])): ?>
          <div style="background-color: var(--color-neutral-bg); border-left: 3px solid var(--color-accent); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.8rem;">
            <strong>Dedicatoria para Certificado:</strong><br>
            <em>"<?= h($selectedOrder['giftDedication']) ?>"</em>
          </div>
        <?php endif; ?>

        <!-- Historial de Auditoría -->
        <?php if (!empty($selAudit)): ?>
          <div style="border-top: 1px solid var(--color-neutral-border-light); padding-top: 0.85rem;">
            <div style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.4rem; color: var(--color-neutral-medium);">Registro de Auditoría:</div>
            <?php foreach ($selAudit as $log): ?>
              <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-bottom: 0.25rem;">
                <strong>[<?= h($log['time']) ?>]</strong> <?= h($log['note']) ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    <?php endif; ?>

  </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
