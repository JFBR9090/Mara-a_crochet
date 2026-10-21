<?php
// comprobante.php - Comprobante Oficial de Pago y Pedido en Maraña Crochet
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$baseUrl = getBaseUrl();
$orderId = trim($_GET['id'] ?? '');

if (empty($orderId)) {
    header('Location: ' . $baseUrl . '/index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("<div style='font-family: sans-serif; text-align: center; padding: 4rem 1rem;'>
        <h2>Comprobante no encontrado</h2>
        <p>No se pudo localizar el comprobante del pedido " . htmlspecialchars($orderId) . ".</p>
        <a href='{$baseUrl}/index.php' style='color: #7B2CBF; font-weight: bold;'>Volver a la tienda</a>
    </div>");
}

$address = !empty($order['address']) ? json_decode($order['address'], true) : [];
$items = !empty($order['items']) ? json_decode($order['items'], true) : [];
$transfer = !empty($order['transferDetails']) ? json_decode($order['transferDetails'], true) : [];

// Generar serie formal del comprobante
$voucherSeries = 'CMP-' . date('Y', strtotime($order['created_at'] ?? 'now')) . '-' . preg_replace('/[^0-9]/', '', $order['id']);
$dateFormatted = date('d/m/Y h:i A', strtotime($order['created_at'] ?? 'now'));
$folio = $transfer['folio'] ?? 'TEF-94827103';
$bankName = $order['bank'] ?? 'Bancolombia';

// Texto para compartir por WhatsApp
$waMessage = urlencode("Hola Taller Maraña Crochet, adjunto mi comprobante de pago para el pedido {$order['id']} por valor de " . formatCOP($order['total']) . ". Mi nombre es {$order['customerName']}.");
$waUrl = "https://api.whatsapp.com/send?phone=573124567890&text={$waMessage}";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comprobante de Pago <?= h($order['id']) ?> | Maraña Crochet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --color-primary: #7B2CBF;
      --color-primary-light: #C77DFF;
      --color-gold: #FFB703;
      --color-dark: #1D1128;
      --color-muted: #6b6377;
      --color-border: #e6dfed;
      --color-bg-subtle: #faf8fc;
      --color-success: #15803d;
      --color-success-bg: #f0fdf4;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #f4eff8;
      color: var(--color-dark);
      padding: 2rem 1rem;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .action-bar {
      max-width: 780px;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .action-group {
      display: flex;
      gap: 0.65rem;
      flex-wrap: wrap;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      padding: 0.65rem 1.15rem;
      font-size: 0.88rem;
      font-weight: 700;
      border-radius: 8px;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s ease;
      border: 1px solid transparent;
      font-family: inherit;
    }
    .btn-primary {
      background: var(--color-primary);
      color: #fff;
    }
    .btn-primary:hover {
      background: #651FA4;
    }
    .btn-outlined {
      background: #fff;
      color: var(--color-primary);
      border-color: var(--color-primary);
    }
    .btn-outlined:hover {
      background: #f8f1fe;
    }
    .btn-wa {
      background: #25D366;
      color: #fff;
    }
    .btn-wa:hover {
      background: #1eb956;
    }
    .btn-secondary {
      background: #fff;
      color: var(--color-muted);
      border-color: var(--color-border);
    }
    .btn-secondary:hover {
      background: #eee8f3;
      color: var(--color-dark);
    }

    /* Tarjeta Principal del Comprobante */
    .receipt-card {
      max-width: 780px;
      width: 100%;
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid var(--color-border);
      box-shadow: 0 12px 32px rgba(123, 44, 191, 0.08);
      overflow: hidden;
      position: relative;
    }

    /* Encabezado del Comprobante */
    .receipt-header {
      background: linear-gradient(135deg, #2a0845 0%, #4D1282 100%);
      color: #ffffff;
      padding: 2.25rem 2.5rem;
      position: relative;
    }
    .receipt-header::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      width: 250px;
      background: radial-gradient(circle, rgba(199, 125, 255, 0.2) 0%, transparent 70%);
      pointer-events: none;
    }
    .brand-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1.5rem;
      flex-wrap: wrap;
    }
    .brand-info h1 {
      font-size: 1.6rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin-bottom: 0.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .brand-badge {
      display: inline-block;
      font-size: 0.72rem;
      background: rgba(255, 183, 3, 0.25);
      color: #ffd666;
      padding: 0.2rem 0.55rem;
      border-radius: 4px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .brand-meta {
      font-size: 0.82rem;
      color: #e5d7f5;
      line-height: 1.5;
      margin-top: 0.4rem;
    }
    .voucher-stamp {
      text-align: right;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(8px);
      padding: 0.85rem 1.25rem;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .voucher-title {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #e5d7f5;
    }
    .voucher-number {
      font-family: 'Space Mono', monospace;
      font-size: 1.25rem;
      font-weight: 700;
      color: #ffffff;
      margin: 0.15rem 0;
    }
    .voucher-date {
      font-size: 0.75rem;
      color: #d1b8eb;
    }

    /* Cuerpo del Comprobante */
    .receipt-body {
      padding: 2.25rem 2.5rem;
    }

    /* Sección de Estado y Notificación */
    .status-alert {
      background: var(--color-success-bg);
      border: 1px solid #bbf7d0;
      border-radius: 8px;
      padding: 1rem 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }
    .status-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .status-icon {
      width: 2rem;
      height: 2rem;
      background: var(--color-success);
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1rem;
    }
    .status-text h4 {
      font-size: 0.95rem;
      color: #14532d;
      font-weight: 800;
      margin-bottom: 0.15rem;
    }
    .status-text p {
      font-size: 0.78rem;
      color: #166534;
    }
    .status-tag {
      background: #dcfce7;
      color: #15803d;
      font-size: 0.75rem;
      font-weight: 800;
      text-transform: uppercase;
      padding: 0.35rem 0.75rem;
      border-radius: 999px;
      border: 1px solid #86efac;
    }

    /* Grid de Datos Emisor y Cliente */
    .parties-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-bottom: 2rem;
      padding-bottom: 1.75rem;
      border-bottom: 1px solid var(--color-border);
    }
    .party-box h3 {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--color-primary);
      font-weight: 800;
      margin-bottom: 0.65rem;
      display: flex;
      align-items: center;
      gap: 0.35rem;
    }
    .party-details {
      font-size: 0.85rem;
      line-height: 1.55;
      color: var(--color-dark);
    }
    .party-name {
      font-weight: 700;
      font-size: 0.95rem;
      color: var(--color-dark);
      margin-bottom: 0.2rem;
    }
    .party-meta {
      color: var(--color-muted);
      font-size: 0.8rem;
    }

    /* Tabla de Conceptos / Amigurumis */
    .table-container {
      width: 100%;
      margin-bottom: 1.75rem;
    }
    .items-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.88rem;
    }
    .items-table th {
      text-align: left;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-muted);
      padding: 0.65rem 0.5rem;
      border-bottom: 2px solid var(--color-border);
    }
    .items-table td {
      padding: 0.95rem 0.5rem;
      border-bottom: 1px solid var(--color-border);
      vertical-align: middle;
    }
    .item-desc {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .item-thumb {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      object-fit: cover;
      border: 1px solid var(--color-border);
    }
    .item-title {
      font-weight: 700;
      color: var(--color-dark);
    }
    .item-sku {
      font-size: 0.75rem;
      color: var(--color-muted);
    }

    /* Resumen Financiero */
    .totals-wrapper {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 2rem;
    }
    .totals-box {
      width: 320px;
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
      font-size: 0.85rem;
    }
    .total-row {
      display: flex;
      justify-content: space-between;
      color: var(--color-muted);
    }
    .total-row.final {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--color-primary);
      border-top: 2px solid var(--color-border);
      padding-top: 0.65rem;
      margin-top: 0.35rem;
    }

    /* Bloque Bancario y Certificación */
    .proof-box {
      background: var(--color-bg-subtle);
      border: 1px dashed var(--color-border);
      border-radius: 8px;
      padding: 1.25rem 1.5rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-bottom: 2rem;
      font-size: 0.82rem;
    }
    .proof-col h5 {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--color-primary);
      margin-bottom: 0.35rem;
      font-weight: 800;
    }
    .proof-data {
      line-height: 1.5;
      color: var(--color-dark);
    }

    /* Pie de página del comprobante */
    .receipt-footer {
      border-top: 1px solid var(--color-border);
      padding: 1.5rem 2.5rem;
      background: #faf8fc;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      font-size: 0.78rem;
      color: var(--color-muted);
    }
    .seal-badge {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--color-primary);
      font-weight: 700;
    }

    /* Reglas para Impresión Fina y Limpia (PDF / Papel) */
    @media print {
      body {
        background: #ffffff !important;
        padding: 0 !important;
      }
      .action-bar {
        display: none !important;
      }
      .receipt-card {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      .receipt-header {
        background: #2a0845 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .status-tag, .brand-badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }

    @media (max-width: 640px) {
      .receipt-header, .receipt-body, .receipt-footer {
        padding: 1.5rem 1.25rem;
      }
      .parties-grid, .proof-box {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      .voucher-stamp {
        text-align: left;
        width: 100%;
      }
      .totals-box {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <!-- Barra Superior de Acciones (Oculta al Imprimir) -->
  <div class="action-bar">
    <div class="action-group">
      <a href="<?= $baseUrl ?>/index.php" class="btn btn-secondary">
        &larr; Volver a la Tienda
      </a>
      <a href="<?= $baseUrl ?>/order-success.php?id=<?= urlencode($order['id']) ?>" class="btn btn-secondary">
        Ver Estado de Envío
      </a>
    </div>

    <div class="action-group">
      <button onclick="window.print()" class="btn btn-primary">
        <svg style="width: 1.1rem; height: 1.1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 6 2 18 2 18 9"></polyline>
          <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
          <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        Imprimir / Guardar en PDF
      </button>

      <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-wa">
        <svg style="width: 1.1rem; height: 1.1rem;" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.16 12.04 20.16C10.66 20.16 9.3 19.8 8.1 19.11L7.76 18.91L4.62 19.73L5.46 16.66L5.24 16.31C4.47 15.08 4.07 13.52 4.07 11.91C4.07 7.37 7.77 3.67 12.05 3.67M9.08 7.39C8.91 7.39 8.64 7.45 8.41 7.7C8.18 7.95 7.53 8.56 7.53 9.81C7.53 11.06 8.44 12.27 8.57 12.44C8.7 12.61 10.35 15.16 12.87 16.25C13.47 16.51 13.94 16.66 14.3 16.78C14.9 16.97 15.44 16.94 15.88 16.88C16.36 16.8 17.38 16.26 17.59 15.67C17.8 15.08 17.8 14.58 17.74 14.47C17.68 14.37 17.51 14.31 17.26 14.18C17 14.06 15.75 13.44 15.52 13.36C15.29 13.27 15.12 13.23 14.95 13.48C14.78 13.73 14.3 14.31 14.16 14.47C14.02 14.64 13.88 14.66 13.63 14.53C13.38 14.41 12.58 14.15 11.63 13.3C10.89 12.64 10.39 11.83 10.25 11.58C10.11 11.33 10.23 11.2 10.36 11.07C10.47 10.96 10.61 10.78 10.74 10.63C10.87 10.48 10.91 10.37 11 10.19C11.08 10 11.04 9.84 10.98 9.72C10.92 9.6 10.44 8.42 10.25 7.94C10.05 7.47 9.86 7.53 9.71 7.52C9.58 7.52 9.42 7.39 9.08 7.39Z"/>
        </svg>
        Compartir por WhatsApp
      </a>
    </div>
  </div>

  <!-- Contenedor del Comprobante -->
  <main class="receipt-card">
    
    <!-- Encabezado con Identidad y Serie -->
    <header class="receipt-header">
      <div class="brand-row">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Logo Maraña" style="width: 4rem; height: 4rem; border-radius: 50%; object-fit: cover; border: 2.5px solid var(--color-primary); box-shadow: 0 4px 12px rgba(123, 44, 191, 0.25);">
          <div class="brand-info">
            <h1>
              <span>Maraña Crochet</span>
              <span class="brand-badge">Taller Artesanal</span>
            </h1>
          <div class="brand-meta">
            <strong>MARAÑA CROCHET ARTESANAL</strong> • NIT: 901.542.883-1<br>
            📍 Carrera 5 # 12-45, Centro · <strong>Neiva, Huila, Colombia</strong><br>
            🇨🇴 Envíos Exclusivamente a Nivel Nacional · WhatsApp: (+57) 312 456 7890
          </div>
        </div>
      </div>

        <div class="voucher-stamp">
          <div class="voucher-title">Comprobante de Pago</div>
          <div class="voucher-number"><?= h($voucherSeries) ?></div>
          <div class="voucher-date">Orden: <strong><?= h($order['id']) ?></strong></div>
          <div class="voucher-date">Fecha: <?= h($dateFormatted) ?></div>
        </div>
      </div>
    </header>

    <div class="receipt-body">
      
      <!-- Estado de la Transacción -->
      <div class="status-alert">
        <div class="status-left">
          <div class="status-icon">✓</div>
          <div class="status-text">
            <h4>Transacción Registrada con Éxito</h4>
            <p>Comprobante de transferencia bancaria recibido. En proceso de validación y empaque en el taller.</p>
          </div>
        </div>
        <div class="status-tag">
          <?= strtoupper(h($order['status'])) ?>
        </div>
      </div>

      <!-- Datos del Taller Emisor y del Cliente Receptor -->
      <div class="parties-grid">
        <div class="party-box">
          <h3>
            <svg style="width: 0.95rem; height: 0.95rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Taller Emisor (Despachos)
          </h3>
          <div class="party-details">
            <div class="party-name">Taller Maraña Crochet Artesanal</div>
            <div><strong>Ubicación:</strong> Neiva, Huila, Colombia</div>
            <div><strong>Cobertura:</strong> Envíos nacionales en todo el territorio colombiano</div>
            <div class="party-meta">Email: pedidos@maranacrochet.co · Tel: +57 312 456 7890</div>
          </div>
        </div>

        <div class="party-box">
          <h3>
            <svg style="width: 0.95rem; height: 0.95rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Cliente / Destinatario Nacional
          </h3>
          <div class="party-details">
            <div class="party-name"><?= h($order['customerName']) ?></div>
            <div><strong>Doc / Cédula CC:</strong> <?= h($order['customerRut'] ?: 'No especificada') ?></div>
            <div><strong>Destino:</strong> <?= h($address['street'] ?? '') ?> <?= !empty($address['dept']) ? 'Apto ' . h($address['dept']) : '' ?></div>
            <div><strong>Ciudad / Depto:</strong> <?= h($address['comuna'] ?? 'Neiva') ?>, <?= h($address['region'] ?? 'Huila') ?> (Colombia)</div>
            <div class="party-meta">Tel: <?= h($order['customerPhone']) ?> · Email: <?= h($order['customerEmail']) ?></div>
          </div>
        </div>
      </div>

      <!-- Detalle de Ítems Adquiridos -->
      <div class="table-container">
        <table class="items-table">
          <thead>
            <tr>
              <th>Amigurumi / Criatura Artesanal</th>
              <th style="text-align: center; width: 80px;">Cant.</th>
              <th style="text-align: right; width: 120px;">Precio Unit.</th>
              <th style="text-align: right; width: 130px;">Subtotal COP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td>
                  <div class="item-desc">
                    <img src="<?= h($item['image']) ?>" alt="<?= h($item['productName']) ?>" class="item-thumb">
                    <div>
                      <div class="item-title"><?= h($item['productName']) ?></div>
                      <div class="item-sku">SKU: <?= h($item['productId']) ?> · 100% Algodón Hipoalergénico</div>
                    </div>
                  </div>
                </td>
                <td style="text-align: center; font-weight: 700;">
                  <?= (int)$item['quantity'] ?>
                </td>
                <td style="text-align: right;">
                  <?= formatCOP($item['price']) ?>
                </td>
                <td style="text-align: right; font-weight: 700; color: var(--color-dark);">
                  <?= formatCOP($item['price'] * $item['quantity']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Resumen de Liquidación -->
      <div class="totals-wrapper">
        <div class="totals-box">
          <div class="total-row">
            <span>Subtotal Amigurumis:</span>
            <span><?= formatCOP($order['subtotal']) ?></span>
          </div>
          <div class="total-row">
            <span>Envío Nacional (<?= h($order['shippingMethod'] ?? 'Despacho Nacional') ?>):</span>
            <span><?= formatCOP($order['shippingCost']) ?></span>
          </div>
          <div class="total-row final">
            <span>Total Pagado:</span>
            <span><?= formatCOP($order['total']) ?></span>
          </div>
        </div>
      </div>

      <!-- Datos de la Transferencia Bancaria y Dedicatoria -->
      <div class="proof-box">
        <div class="proof-col">
          <h5>Detalles de Pago Bancario (TEF)</h5>
          <div class="proof-data">
            <strong>Entidad / Medio:</strong> <?= h($bankName) ?><br>
            <strong>Folio de Transacción:</strong> <code><?= h($folio) ?></code><br>
            <strong>Fecha de Transacción:</strong> <?= h($transfer['transferDate'] ?? $dateFormatted) ?><br>
            <strong>Archivo Comprobante:</strong> <?= h($transfer['voucherFilename'] ?? 'comprobante_bancario.pdf') ?>
          </div>
        </div>

        <div class="proof-col">
          <h5>Dedicatoria de Regalo & Empaque</h5>
          <div class="proof-data">
            <?php if (!empty($order['giftDedication'])): ?>
              <span style="font-style: italic; color: var(--color-primary);">"<?= h($order['giftDedication']) ?>"</span><br>
              <span style="color: var(--color-muted); font-size: 0.75rem;">(Incluida en tarjeta kraft aromática con lavanda)</span>
            <?php else: ?>
              <span style="color: var(--color-muted);">Sin dedicatoria especial. Incluye Certificado de Adopción numerado.</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- Sello y Garantía de Autenticidad -->
    <footer class="receipt-footer">
      <div class="seal-badge">
        <svg style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
        </svg>
        <span>Garantía Artesanal Maraña Crochet · Neiva, Huila (Colombia)</span>
      </div>
      <div>
        Documento Electrónico Generado para el Cliente · Válido como Soporte de Pago
      </div>
    </footer>

  </main>

</body>
</html>

