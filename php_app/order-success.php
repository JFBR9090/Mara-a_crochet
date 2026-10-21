<?php
// order-success.php - Confirmación de orden con recibo y seguimiento de pedido
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$orderId = $_GET['id'] ?? '';
$baseUrl = getBaseUrl();

if (!$orderId) {
    header('Location: ' . $baseUrl . '/index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    $pageTitle = 'Pedido no encontrado | Maraña Crochet';
    require __DIR__ . '/includes/header.php';
    echo "<div class='container' style='padding: 5rem 1rem; text-align: center;'>
            <h2>Pedido no encontrado</h2>
            <p>No se pudo localizar el comprobante del pedido {$orderId}.</p>
            <a href='{$baseUrl}/index.php' class='btn btn-primary' style='margin-top: 1.5rem;'>Volver a la Tienda</a>
          </div>";
    require __DIR__ . '/includes/footer.php';
    exit();
}

$address = !empty($order['address']) ? json_decode($order['address'], true) : [];
$items = !empty($order['items']) ? json_decode($order['items'], true) : [];
$transfer = !empty($order['transferDetails']) ? json_decode($order['transferDetails'], true) : [];

$pageTitle = "¡Pedido Confirmado! {$orderId} | Maraña Crochet";
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 3rem; padding-bottom: 5rem; max-width: 780px;">
  
  <!-- Tarjeta de Éxito Principal -->
  <div class="card-panel" style="text-align: center; padding: 2.5rem 1.5rem; border-top: 5px solid var(--color-primary);">
    <div style="width: 3.5rem; height: 3.5rem; border-radius: 50%; background: var(--color-success-bg); color: var(--color-success); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
      <svg style="width: 2rem; height: 2rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>

    <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">¡Tu Pedido ha Sido Registrado con Éxito!</h1>
    <p style="color: var(--color-neutral-medium); font-size: 0.95rem; margin-bottom: 1.5rem;">
      Código de Pedido: <strong style="color: var(--color-primary); font-size: 1.1rem;"><?= h($order['id']) ?></strong>
    </p>

    <!-- Barra de Estado / Timeline Minimalista -->
    <div style="background: var(--color-neutral-bg); border-radius: var(--radius-sm); padding: 1rem 1.5rem; margin-bottom: 2rem; text-align: left;">
      <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">
        <span>1. Transferencia Recibida</span>
        <span>2. Preparación en Taller</span>
        <span>3. Empaque Aromático</span>
        <span>4. Despachado</span>
      </div>
      <div class="progress-container">
        <div class="progress-bar" style="width: 35%;"></div>
      </div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-top: 0.5rem; text-align: center;">
        Estado Actual: <span class="status-pill status-revision"><?= strtoupper(h($order['status'])) ?></span> — Validando TEF Folio #<?= h($transfer['folio'] ?? '94827103') ?>
      </div>
    </div>

    <!-- Recibo Detallado -->
    <div style="border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); padding: 1.5rem; text-align: left; margin-bottom: 2rem;">
      <h3 style="font-size: 1.05rem; margin-bottom: 1rem; border-bottom: 1px solid var(--color-neutral-border-light); padding-bottom: 0.5rem;">
        Detalle de Amigurumis Adoptados
      </h3>

      <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.5rem;">
        <?php foreach ($items as $item): ?>
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 0.75rem; align-items: center;">
              <img src="<?= h($item['image']) ?>" alt="<?= h($item['productName']) ?>" style="width: 44px; height: 44px; border-radius: var(--radius-sm); object-fit: cover;">
              <div>
                <div style="font-weight: 700; font-size: 0.88rem;"><?= h($item['productName']) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-neutral-light);">Cantidad: <?= (int)$item['quantity'] ?> unidad(es)</div>
              </div>
            </div>
            <div style="font-weight: 700; font-size: 0.92rem;">
              <?= formatCLP($item['price'] * $item['quantity']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="border-top: 1px solid var(--color-neutral-border-light); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.85rem;">
        <div style="display: flex; justify-content: space-between;">
          <span class="text-muted">Subtotal:</span>
          <span style="font-weight: 600;"><?= formatCLP($order['subtotal']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span class="text-muted">Costo de Envío (<?= h($order['shippingMethod']) ?>):</span>
          <span style="font-weight: 600;"><?= formatCLP($order['shippingCost']) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; color: var(--color-primary); border-top: 1px solid var(--color-neutral-border); padding-top: 0.5rem; margin-top: 0.3rem;">
          <span>Total Transferido:</span>
          <span><?= formatCLP($order['total']) ?></span>
        </div>
      </div>
    </div>

    <!-- Información de Despacho y Dedicatoria -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: left; margin-bottom: 2rem; font-size: 0.85rem;">
      <div style="background: var(--color-neutral-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-neutral-border-light);">
        <strong style="color: var(--color-neutral-dark); display: block; margin-bottom: 0.35rem;">📍 Destino Nacional (Colombia):</strong>
        <?= h($order['customerName']) ?><br>
        <?= h($address['street'] ?? '') ?><?= !empty($address['dept']) ? ' - ' . h($address['dept']) : '' ?><br>
        <?= h($address['comuna'] ?? 'Neiva') ?>, <?= h($address['region'] ?? 'Huila') ?> (Colombia)<br>
        Tel: <?= h($order['customerPhone']) ?>
        <div style="font-size: 0.75rem; color: var(--color-primary); margin-top: 0.35rem; font-weight: 600;">
          📦 Despacho directo desde taller en Neiva, Huila
        </div>
      </div>

      <div style="background: var(--color-neutral-bg); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-neutral-border-light);">
        <strong style="color: var(--color-neutral-dark); display: block; margin-bottom: 0.35rem;">🎁 Dedicatoria de Regalo:</strong>
        <?php if (!empty($order['giftDedication'])): ?>
          <span style="font-style: italic;">"<?= h($order['giftDedication']) ?>"</span>
        <?php else: ?>
          <span class="text-muted">Sin dedicatoria especial solicitada.</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comprobante Oficial de Pago Generado -->
    <div style="background: var(--color-primary-bg); border: 1.5px dashed var(--color-primary); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; text-align: left;">
      <div>
        <div style="font-weight: 800; font-size: 0.95rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.45rem;">
          <svg style="width: 1.2rem; height: 1.2rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          Comprobante Oficial de Pago Generado
        </div>
        <div style="font-size: 0.8rem; color: var(--color-neutral-medium); margin-top: 0.2rem;">
          Serie única electrónica con validez de compra, garantía artesanal y desglose detallado.
        </div>
      </div>
      <a href="<?= $baseUrl ?>/comprobante.php?id=<?= urlencode($order['id']) ?>" target="_blank" class="btn btn-primary" style="font-size: 0.88rem; padding: 0.75rem 1.25rem;">
        📄 Ver / Descargar Comprobante (PDF)
      </a>
    </div>

    <!-- Botones de Acción -->
    <div style="display: flex; justify-content: center; gap: 0.85rem; flex-wrap: wrap;">
      <a href="<?= $baseUrl ?>/comprobante.php?id=<?= urlencode($order['id']) ?>" target="_blank" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir Comprobante Oficial
      </a>
      <a href="<?= $baseUrl ?>/index.php" class="btn btn-outlined">Volver al Catálogo</a>
      <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-inverted">Panel Taller</a>
    </div>

  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
