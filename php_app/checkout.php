<?php
// checkout.php - Proceso de checkout con validación y transferencia bancaria
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$cartItems = getCart();
$subtotal = getCartSubtotal();
$baseUrl = getBaseUrl();

// Si el carrito está vacío, redirigir al catálogo
if (empty($cartItems)) {
    setFlash('info', 'Tu canasta está vacía', 'Por favor selecciona al menos un amigurumi para comprar.');
    header('Location: ' . $baseUrl . '/index.php#catalogo');
    exit();
}

$error = '';

// Procesar Confirmación de Pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName  = trim($_POST['customerName'] ?? '');
    $customerEmail = trim($_POST['customerEmail'] ?? '');
    $customerPhone = trim($_POST['customerPhone'] ?? '');
    $customerRut   = trim($_POST['customerRut'] ?? '');

    $region        = trim($_POST['region'] ?? 'Región Metropolitana de Santiago');
    $comuna        = trim($_POST['comuna'] ?? 'Providencia');
    $street        = trim($_POST['street'] ?? '');
    $dept          = trim($_POST['dept'] ?? '');
    $instructions  = trim($_POST['instructions'] ?? '');

    $shippingMethod = $_POST['shippingMethod'] ?? 'nacional-colombia';
    $shippingCost = 0;
    if ($shippingMethod === 'nacional-colombia') {
        $shippingCost = 14500;
    } elseif ($shippingMethod === 'envio-neiva') {
        $shippingCost = 6000;
    } elseif ($shippingMethod === 'retiro') {
        $shippingCost = 0;
    }

    $giftDedication = trim($_POST['giftDedication'] ?? '');
    $bank           = $_POST['bank'] ?? 'Bancolombia';
    $tefFolio       = trim($_POST['tefFolio'] ?? '');
    $accountType    = $_POST['accountType'] ?? 'Cuenta de Ahorros';

    if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($street)) {
        $error = 'Por favor completa todos los campos requeridos marcados con (*).';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Generar ID único de pedido estilo Maraña
            $orderId = '#MC-' . date('Y') . '-' . rand(100, 999);
            $total = $subtotal + $shippingCost;

            // 2. Preparar datos en JSON para MySQL
            $addressJson = json_encode([
                'region' => $region,
                'comuna' => $comuna,
                'street' => $street,
                'dept' => $dept,
                'instructions' => $instructions
            ], JSON_UNESCAPED_UNICODE);

            $orderItems = [];
            foreach ($cartItems as $c) {
                $orderItems[] = [
                    'productId' => $c['id'],
                    'productName' => $c['name'],
                    'colorDetails' => 'Edición Taller Artesanal',
                    'quantity' => (int)$c['quantity'],
                    'price' => (int)$c['price'],
                    'image' => $c['image']
                ];
            }
            $itemsJson = json_encode($orderItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $transferDetailsJson = json_encode([
                'folio' => $tefFolio ?: 'TEF-' . rand(10000000, 99999999),
                'tefCode' => 'TEF-' . rand(10000000, 99999999),
                'transferredAmount' => $total,
                'transferDate' => date('d/m/Y H:i') . ' hrs',
                'channel' => 'Transferencia Web / Móvil',
                'subject' => 'Pedido Maraña ' . $orderId,
                'voucherFilename' => !empty($_FILES['voucher']['name']) ? basename($_FILES['voucher']['name']) : 'comprobante_transferencia.pdf'
            ], JSON_UNESCAPED_UNICODE);

            $auditLogJson = json_encode([
                [
                    'time' => date('H:i'),
                    'note' => 'Pedido ingresado por checkout web con comprobante TEF adjunto',
                    'type' => 'info'
                ],
                [
                    'time' => date('H:i'),
                    'note' => 'Validando folio TEF en cartola bancaria ' . $bank,
                    'type' => 'warning'
                ]
            ], JSON_UNESCAPED_UNICODE);

            // 3. Insertar pedido en orders
            $orderStmt = $pdo->prepare("
                INSERT INTO `orders`
                (`id`, `date`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `bank`, `accountType`, `transferDetails`, `giftDedication`, `auditLog`)
                VALUES
                (:id, :date, :customerName, :customerEmail, :customerPhone, :customerRut, :address, :shippingMethod, :shippingCost, :items, :subtotal, :total, 'revision', :bank, :accountType, :transferDetails, :giftDedication, :auditLog)
            ");

            $orderStmt->execute([
                ':id' => $orderId,
                ':date' => 'Hoy ' . date('H:i') . ' hrs',
                ':customerName' => $customerName,
                ':customerEmail' => $customerEmail,
                ':customerPhone' => $customerPhone,
                ':customerRut' => $customerRut ?: '11.111.111-1',
                ':address' => $addressJson,
                ':shippingMethod' => $shippingMethod,
                ':shippingCost' => $shippingCost,
                ':items' => $itemsJson,
                ':subtotal' => $subtotal,
                ':total' => $total,
                ':bank' => $bank,
                ':accountType' => $accountType,
                ':transferDetails' => $transferDetailsJson,
                ':giftDedication' => $giftDedication ?: null,
                ':auditLog' => $auditLogJson,
            ]);

            // 4. Descontar stock de cada producto en products
            $stockStmt = $pdo->prepare("
                UPDATE `products` 
                SET `stock` = GREATEST(0, `stock` - :qty),
                    `status` = CASE 
                        WHEN GREATEST(0, `stock` - :qty2) = 0 THEN 'soldout'
                        WHEN GREATEST(0, `stock` - :qty3) <= 4 THEN 'low-stock'
                        ELSE 'in-stock'
                    END,
                    `statusLabel` = CASE 
                        WHEN GREATEST(0, `stock` - :qty4) = 0 THEN 'Agotado Temporalmente'
                        WHEN GREATEST(0, `stock` - :qty5) <= 4 THEN CONCAT('¡Últimas ', GREATEST(0, `stock` - :qty6), ' unidades!')
                        ELSE CONCAT('Stock Disponible (', GREATEST(0, `stock` - :qty7), ')')
                    END
                WHERE `id` = :productId
            ");

            foreach ($orderItems as $item) {
                $qty = (int)$item['quantity'];
                $stockStmt->execute([
                    ':qty' => $qty,
                    ':qty2' => $qty,
                    ':qty3' => $qty,
                    ':qty4' => $qty,
                    ':qty5' => $qty,
                    ':qty6' => $qty,
                    ':qty7' => $qty,
                    ':productId' => $item['productId']
                ]);
            }

            $pdo->commit();

            // 5. Vaciar carrito y redirigir
            clearCart();
            header('Location: ' . $baseUrl . '/order-success.php?id=' . urlencode($orderId));
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Error registrando el pedido: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Finalizar Compra | Maraña Crochet';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">
  
  <!-- Encabezado y Barra de Progreso de Checkout -->
  <div style="max-width: 800px; margin: 0 auto 2rem; text-align: center;">
    <h1 style="font-size: 1.85rem; margin-bottom: 0.5rem;">Adopción Artesanal Segura</h1>
    <p style="font-size: 0.88rem; color: var(--color-neutral-medium); margin-bottom: 1.5rem;">
      Completa tus datos de despacho y confirma tu transferencia electrónica (TEF).
    </p>

    <!-- Barra de Progreso de 3 Pasos -->
    <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--color-primary);">
      <span>1. Datos del Cliente</span>
      <span>2. Despacho & Dedicatoria</span>
      <span>3. Transferencia (TEF)</span>
    </div>
    <div class="progress-container" style="height: 0.45rem;">
      <div class="progress-bar" style="width: 80%;"></div>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div style="max-width: 800px; margin: 0 auto 1.5rem; background: var(--color-danger-bg); border-left: 4px solid var(--color-danger); padding: 1rem; border-radius: var(--radius-sm); color: var(--color-danger); font-size: 0.88rem;">
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <form action="<?= $baseUrl ?>/checkout.php" method="POST" enctype="multipart/form-data" style="max-width: 850px; margin: 0 auto;">
    
    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
      
      <!-- Paso 1: Datos Personales -->
      <div class="card-panel">
        <div class="card-header">
          <h3 style="font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <span style="width: 1.6rem; height: 1.6rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">1</span>
            Datos del Destinatario
          </h3>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="cust-name">Nombre y Apellido Completo *</label>
            <input type="text" id="cust-name" name="customerName" required class="form-control" placeholder="Ej: Valentina Castro Pizarro" value="Valentina Castro Pizarro">
          </div>
          <div class="form-group">
            <label class="form-label" for="cust-email">Correo Electrónico *</label>
            <input type="email" id="cust-email" name="customerEmail" required class="form-control" placeholder="v.castro@correo.com" value="v.castro@correo.com">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="cust-phone">Teléfono / WhatsApp *</label>
            <input type="tel" id="cust-phone" name="customerPhone" required class="form-control" placeholder="+57 312 456 7890" value="+57 312 456 7890">
          </div>
          <div class="form-group">
            <label class="form-label" for="cust-rut">Cédula de Ciudadanía (CC) / NIT *</label>
            <input type="text" id="cust-rut" name="customerRut" required class="form-control" placeholder="Ej: 1.075.289.412" value="1.075.289.412">
          </div>
        </div>
      </div>

      <!-- Paso 2: Dirección y Método de Despacho -->
      <div class="card-panel">
        <div class="card-header">
          <h3 style="font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <span style="width: 1.6rem; height: 1.6rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">2</span>
            Dirección de Envío Nacional (Colombia)
          </h3>
        </div>

        <!-- Banner de Cobertura Nacional desde Neiva -->
        <div style="background: var(--color-primary-bg); border-left: 3px solid var(--color-primary); padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.83rem; color: var(--color-neutral-dark);">
          📍 <strong>Despachos desde Neiva, Huila:</strong> Realizamos envíos únicamente dentro del territorio nacional de Colombia con empaque especial y aroma a lavanda.
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="ship-region">Departamento (Colombia) *</label>
            <select name="region" id="ship-region" class="form-control">
              <option value="Huila" selected>Huila (Sede Principal Taller)</option>
              <option value="Bogotá D.C.">Bogotá D.C.</option>
              <option value="Cundinamarca">Cundinamarca</option>
              <option value="Antioquia">Antioquia</option>
              <option value="Valle del Cauca">Valle del Cauca</option>
              <option value="Tolima">Tolima</option>
              <option value="Santander">Santander</option>
              <option value="Boyacá">Boyacá</option>
              <option value="Atlántico">Atlántico</option>
              <option value="Bolívar">Bolívar</option>
              <option value="Risaralda">Risaralda</option>
              <option value="Caldas">Caldas</option>
              <option value="Meta">Meta</option>
              <option value="Otro Departamento">Otro Departamento Nacional</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="ship-comuna">Ciudad o Municipio *</label>
            <input type="text" id="ship-comuna" name="comuna" required class="form-control" placeholder="Ej: Neiva, Pitalito, Garzón, Bogotá, etc." value="Neiva">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="ship-street">Dirección y Barrio (Calle / Carrera / Número) *</label>
            <input type="text" id="ship-street" name="street" required class="form-control" placeholder="Ej: Carrera 5 # 12-45, Barrio Centro" value="Carrera 5 # 12-45, Barrio Centro">
          </div>
          <div class="form-group">
            <label class="form-label" for="ship-dept">Apto / Torre / Casa (Opcional)</label>
            <input type="text" id="ship-dept" name="dept" class="form-control" placeholder="Apto 302, Torre 1" value="Apto 302, Torre 1">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="ship-method">Modalidad de Envío *</label>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
            <label style="border: 1px solid var(--color-primary); background: var(--color-primary-subtle); border-radius: var(--radius-sm); padding: 0.85rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
              <input type="radio" name="shippingMethod" value="nacional-colombia" checked onchange="updateTotalWithShipping(14500)">
              <div>
                <div style="font-weight: 700; font-size: 0.85rem;">Envío Nacional Colombia ($14.500)</div>
                <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Interrapidísimo / Servientrega (2-4 días)</div>
              </div>
            </label>

            <label style="border: 1px solid var(--color-neutral-border); background: #FFFFFF; border-radius: var(--radius-sm); padding: 0.85rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
              <input type="radio" name="shippingMethod" value="envio-neiva" onchange="updateTotalWithShipping(6000)">
              <div>
                <div style="font-weight: 700; font-size: 0.85rem;">Envío Local Neiva ($6.000)</div>
                <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Mensajería urbana express el mismo día</div>
              </div>
            </label>

            <label style="border: 1px solid var(--color-neutral-border); background: #FFFFFF; border-radius: var(--radius-sm); padding: 0.85rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
              <input type="radio" name="shippingMethod" value="retiro" onchange="updateTotalWithShipping(0)">
              <div>
                <div style="font-weight: 700; font-size: 0.85rem;">Retiro en Taller (Gratis)</div>
                <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Neiva, Huila (Coordinación previa)</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Dedicatoria de Regalo Opcional -->
        <div class="form-group" style="margin-top: 1rem; border-top: 1px solid var(--color-neutral-border-light); padding-top: 1rem;">
          <label class="form-label" for="gift-dedication">
            🎁 Dedicatoria Caligrafiada de Regalo (Opcional - Gratis)
          </label>
          <textarea id="gift-dedication" name="giftDedication" rows="2" class="form-control" placeholder="Escribe tu dedicatoria especial. La caligrafiaremos a mano en el certificado de adopción...">¡Feliz Cumpleaños pequeña Sophie! Que tu arañita cósmica cuide tus más dulces sueños siempre con amor. De tu tía Vale.</textarea>
        </div>
      </div>

      <!-- Paso 3: Pago por Transferencia Electrónica (TEF) -->
      <div class="card-panel">
        <div class="card-header">
          <h3 style="font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <span style="width: 1.6rem; height: 1.6rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">3</span>
            Pago por Transferencia Bancaria / Nequi / Daviplata
          </h3>
        </div>

        <!-- Datos Bancarios Oficiales de Maraña en Colombia -->
        <div style="background-color: var(--color-neutral-bg); border: 1px solid var(--color-primary-border); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
          <div style="font-weight: 800; color: var(--color-primary); font-size: 0.92rem; margin-bottom: 0.6rem;">
            🏦 Cuentas Oficiales para Transferir (Colombia):
          </div>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
            <div><strong>Bancolombia (Ahorros):</strong> 824-000192-41</div>
            <div><strong>Nequi:</strong> 312 456 7890</div>
            <div><strong>Daviplata:</strong> 312 456 7890</div>
            <div><strong>Davivienda (Ahorros):</strong> 488-291039-11</div>
            <div><strong>Titular:</strong> Maraña Crochet Artesanal</div>
            <div><strong>NIT:</strong> 901.542.883-1 (Neiva, Huila)</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="pay-bank">Entidad o Billetera de Origen *</label>
            <select name="bank" id="pay-bank" class="form-control">
              <option value="Bancolombia" selected>Bancolombia</option>
              <option value="Nequi">Nequi</option>
              <option value="Daviplata">Daviplata</option>
              <option value="Davivienda">Davivienda</option>
              <option value="Banco de Bogotá">Banco de Bogotá</option>
              <option value="BBVA Colombia">BBVA Colombia</option>
              <option value="Otro Banco Colombiano">Otro Banco / Cooperativa</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="tef-folio">Folio / Número de Aprobación *</label>
            <input type="text" id="tef-folio" name="tefFolio" required class="form-control" placeholder="Ej: 94827103" value="94827103">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="voucher-file">Adjuntar Comprobante (PDF o Imagen PNG/JPG)</label>
          <input type="file" id="voucher-file" name="voucher" accept="image/*,.pdf" class="form-control">
          <span style="font-size: 0.72rem; color: var(--color-neutral-light);">Opcional si indicaste el N° de folio de transferencia arriba.</span>
        </div>
      </div>

      <!-- Resumen y Confirmación Final -->
      <div class="card-panel" style="background-color: #FFFFFF; border-color: var(--color-primary-border);">
        <h3 style="font-size: 1.15rem; margin-bottom: 1rem;">Resumen Final a Transferir</h3>
        
        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.5rem;">
          <span>Subtotal amigurumis (<?= getCartCount() ?> piezas):</span>
          <span style="font-weight: 700;"><?= formatCLP($subtotal) ?> COP</span>
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.5rem;">
          <span>Costo de envío nacional:</span>
          <span id="summary-shipping" style="font-weight: 700;"><?= formatCLP(14500) ?> COP</span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: baseline; font-size: 1.25rem; border-top: 1px solid var(--color-neutral-border); padding-top: 1rem; margin-top: 1rem; margin-bottom: 1.5rem;">
          <span style="font-weight: 800;">Total a Transferir:</span>
          <span id="summary-total" style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary);"><?= formatCLP($subtotal + 14500) ?> COP</span>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-size: 1.05rem;">
          Confirmar Compra y Generar Comprobante Oficial &rarr;
        </button>

        <p style="text-align: center; font-size: 0.75rem; color: var(--color-neutral-light); margin-top: 0.75rem;">
          Al hacer clic, tu pedido se registrará inmediatamente en el taller en Neiva, Huila y se emitirá tu comprobante de pago oficial descargable en PDF.
        </p>
      </div>

    </div>
  </form>

</div>

<script>
  const subtotal = <?= $subtotal ?>;
  function updateTotalWithShipping(cost) {
    const total = subtotal + cost;
    const shippingEl = document.getElementById('summary-shipping');
    const totalEl = document.getElementById('summary-total');
    if (shippingEl) shippingEl.textContent = cost === 0 ? 'Gratis' : '$' + new Intl.NumberFormat('es-CO').format(cost) + ' COP';
    if (totalEl) totalEl.textContent = '$' + new Intl.NumberFormat('es-CO').format(total) + ' COP';
  }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
