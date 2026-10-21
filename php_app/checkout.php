<?php
declare(strict_types=1);

// checkout.php - Proceso de compra integral de Maraña Crochet con Pasarela Colombiana
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/gateway.php';

$cartItems = getCart();
$subtotal = getCartSubtotal();
$baseUrl = getBaseUrl();
$customer = getCustomer();

// Si el carrito está vacío, redirigir al catálogo
if (empty($cartItems)) {
    setFlash('info', 'Tu canasta está vacía', 'Por favor selecciona al menos un amigurumi o kit para comprar.');
    header('Location: ' . $baseUrl . '/index.php#catalogo');
    exit();
}

$error = '';
$paymentMethods = getPaymentMethodsConfig();

// Procesar Confirmación de Pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName   = trim($_POST['customerName'] ?? '');
    $customerEmail  = trim($_POST['customerEmail'] ?? '');
    $customerPhone  = trim($_POST['customerPhone'] ?? '');
    $customerRut    = trim($_POST['customerRut'] ?? '');

    $region         = trim($_POST['region'] ?? 'Huila');
    $comuna         = trim($_POST['comuna'] ?? 'Neiva');
    $street         = trim($_POST['street'] ?? '');
    $dept           = trim($_POST['dept'] ?? '');
    $instructions   = trim($_POST['instructions'] ?? '');

    $shippingMethod = $_POST['shippingMethod'] ?? 'ciudades-principales';
    $shippingCost   = 11500;
    if ($shippingMethod === 'nacional-colombia') {
        $shippingCost = 14500;
    } elseif ($shippingMethod === 'envio-neiva') {
        $shippingCost = 6000;
    } elseif ($shippingMethod === 'retiro') {
        $shippingCost = 0;
    }

    $giftDedication = trim($_POST['giftDedication'] ?? '');
    $paymentMethod  = $_POST['paymentMethod'] ?? 'wompi_card';
    $total          = $subtotal + $shippingCost;

    // Validación básica de campos
    if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($street) || empty($comuna)) {
        $error = 'Por favor completa todos los campos requeridos marcados con (*).';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Manejo de Cuenta de Cliente (Asociación o Creación Automática)
            $customerId = $customer['id'] ?? null;

            // Si el cliente no estaba autenticado pero pidió crear cuenta:
            $createAccount = !empty($_POST['createAccount']);
            $accountPassword = $_POST['accountPassword'] ?? '';

            if (!$customerId && $createAccount && !empty($accountPassword)) {
                // Verificar si ya existía
                $checkCust = $pdo->prepare("SELECT id FROM `customers` WHERE `email` = :email LIMIT 1");
                $checkCust->execute([':email' => $customerEmail]);
                $existingId = $checkCust->fetchColumn();

                if ($existingId) {
                    $customerId = (int)$existingId;
                } else {
                    $hash = password_hash($accountPassword, PASSWORD_BCRYPT);
                    $newCustStmt = $pdo->prepare("
                        INSERT INTO `customers` (`name`, `email`, `phone`, `address`, `city`, `department`, `password`)
                        VALUES (:name, :email, :phone, :address, :city, :department, :password)
                    ");
                    $newCustStmt->execute([
                        ':name' => $customerName,
                        ':email' => $customerEmail,
                        ':phone' => $customerPhone,
                        ':address' => $street . ($dept ? ", {$dept}" : ''),
                        ':city' => $comuna,
                        ':department' => $region,
                        ':password' => $hash,
                    ]);
                    $customerId = (int)$pdo->lastInsertId();

                    loginCustomer([
                        'id' => $customerId,
                        'name' => $customerName,
                        'email' => $customerEmail,
                        'phone' => $customerPhone,
                        'address' => $street,
                        'city' => $comuna,
                        'department' => $region
                    ]);
                }
            }

            // 2. Generar ID único de pedido estilo Maraña
            $orderId = '#MC-' . date('Y') . '-' . rand(100, 999);

            // 3. Preparar detalles específicos según el método de pago seleccionado
            $transferDetails = [];
            $orderStatus = 'revision';
            $bankName = 'Bancolombia';
            $accountTypeName = 'Cuenta de Ahorros';

            if ($paymentMethod === 'wompi_card') {
                $orderStatus = 'aprobado';
                $bankName = 'Pasarela Wompi (Bancolombia)';
                $accountTypeName = 'Tarjeta Crédito / Débito';
                $cardMasked = '**** **** **** ' . substr($_POST['cardNumber'] ?? '4242', -4);
                $wompiSignature = generateWompiIntegritySignature($orderId, $total);
                
                $transferDetails = [
                    'gateway' => 'Wompi Colombia (Bancolombia)',
                    'environment' => getEnvVar('WOMPI_ENVIRONMENT', 'sandbox'),
                    'transactionId' => 'WMP-' . strtoupper(bin2hex(random_bytes(5))),
                    'cardHolder' => $_POST['cardHolder'] ?? $customerName,
                    'cardMasked' => $cardMasked,
                    'installments' => (int)($_POST['cardInstallments'] ?? 1),
                    'integritySignature' => $wompiSignature,
                    'transferredAmount' => $total,
                    'transferDate' => date('d/m/Y H:i') . ' hrs'
                ];
            } elseif ($paymentMethod === 'pse') {
                $orderStatus = 'aprobado';
                $pseBank = $_POST['pseBank'] ?? 'Bancolombia';
                $bankName = 'PSE - ' . $pseBank;
                $accountTypeName = 'Débito Bancario en Línea (PSE)';
                
                $transferDetails = [
                    'gateway' => 'PSE (Proveedor de Servicios Electrónicos)',
                    'bank' => $pseBank,
                    'userType' => $_POST['pseUserType'] ?? 'Natural',
                    'docType' => $_POST['pseDocType'] ?? 'CC',
                    'docNumber' => $_POST['pseDocNumber'] ?? $customerRut,
                    'transferredAmount' => $total,
                    'transferDate' => date('d/m/Y H:i') . ' hrs'
                ];
            } elseif ($paymentMethod === 'nequi_daviplata') {
                $orderStatus = 'revision';
                $mobileWallet = $_POST['mobileWallet'] ?? 'Nequi';
                $bankName = $mobileWallet;
                $accountTypeName = 'Billetera Digital ' . $mobileWallet;
                
                $transferDetails = [
                    'wallet' => $mobileWallet,
                    'targetPhone' => '312 456 7890',
                    'senderPhone' => $_POST['mobilePhone'] ?? $customerPhone,
                    'reference' => trim($_POST['mobileRef'] ?? 'NEQ-' . rand(100000, 999999)),
                    'transferredAmount' => $total,
                    'transferDate' => date('d/m/Y H:i') . ' hrs'
                ];
            } elseif ($paymentMethod === 'contra_entrega') {
                $orderStatus = 'preparando';
                $bankName = 'Pago Contra Entrega';
                $accountTypeName = 'Efectivo contra entrega a transportadora';
                
                $transferDetails = [
                    'type' => 'Contra Entrega en Efectivo',
                    'courier' => 'Inter Rapidísimo / Servientrega',
                    'paymentDue' => $total,
                    'note' => 'El cliente abonará el total en efectivo al recibir el paquete en su domicilio.'
                ];
            } else {
                // Transferencia Bancaria Directa Tradicional
                $orderStatus = 'revision';
                $bankName = $_POST['bank'] ?? 'Bancolombia';
                $accountTypeName = 'Cuenta de Ahorros ' . $bankName;
                
                $transferDetails = [
                    'folio' => trim($_POST['tefFolio'] ?? 'TEF-' . rand(10000000, 99999999)),
                    'tefCode' => 'TEF-' . rand(10000000, 99999999),
                    'transferredAmount' => $total,
                    'transferDate' => date('d/m/Y H:i') . ' hrs',
                    'channel' => 'Transferencia App / Sucursal',
                    'voucherFilename' => !empty($_FILES['voucher']['name']) ? basename($_FILES['voucher']['name']) : 'comprobante_bancolombia.pdf'
                ];
            }

            // 4. Preparar datos en JSON para MySQL
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
                    'colorDetails' => 'Edición Taller Artesanal Neiva',
                    'quantity' => (int)$c['quantity'],
                    'price' => (int)$c['price'],
                    'image' => $c['image']
                ];
            }
            $itemsJson = json_encode($orderItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $transferDetailsJson = json_encode($transferDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $auditLogJson = json_encode([
                [
                    'time' => date('H:i'),
                    'note' => "Pedido ingresado por checkout web vía {$paymentMethod}",
                    'type' => 'info'
                ],
                [
                    'time' => date('H:i'),
                    'note' => ($orderStatus === 'aprobado') ? 'Pago confirmado y validado inmediatamente' : 'En espera de verificación en cartola del taller',
                    'type' => ($orderStatus === 'aprobado') ? 'success' : 'warning'
                ]
            ], JSON_UNESCAPED_UNICODE);

            // 5. Insertar pedido en orders
            $orderStmt = $pdo->prepare("
                INSERT INTO `orders`
                (`id`, `date`, `customerId`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `paymentMethod`, `bank`, `accountType`, `transferDetails`, `giftDedication`, `auditLog`)
                VALUES
                (:id, :date, :customerId, :customerName, :customerEmail, :customerPhone, :customerRut, :address, :shippingMethod, :shippingCost, :items, :subtotal, :total, :status, :paymentMethod, :bank, :accountType, :transferDetails, :giftDedication, :auditLog)
            ");

            $orderStmt->execute([
                ':id' => $orderId,
                ':date' => 'Hoy ' . date('H:i') . ' hrs',
                ':customerId' => $customerId,
                ':customerName' => $customerName,
                ':customerEmail' => $customerEmail,
                ':customerPhone' => $customerPhone,
                ':customerRut' => $customerRut ?: '1.075.289.412',
                ':address' => $addressJson,
                ':shippingMethod' => $shippingMethod,
                ':shippingCost' => $shippingCost,
                ':items' => $itemsJson,
                ':subtotal' => $subtotal,
                ':total' => $total,
                ':status' => $orderStatus,
                ':paymentMethod' => $paymentMethod,
                ':bank' => $bankName,
                ':accountType' => $accountTypeName,
                ':transferDetails' => $transferDetailsJson,
                ':giftDedication' => $giftDedication ?: null,
                ':auditLog' => $auditLogJson,
            ]);

            // 6. Descontar stock de cada producto en products
            $stockStmt = $pdo->prepare("
                UPDATE `products` 
                SET `stock` = GREATEST(0, `stock` - :qty),
                    `status` = CASE 
                        WHEN GREATEST(0, `stock` - :qty2) = 0 THEN 'soldout'
                        WHEN GREATEST(0, `stock` - :qty3) <= 4 THEN 'low-stock'
                        ELSE 'in-stock'
                    END
                WHERE `id` = :productId
            ");

            foreach ($orderItems as $item) {
                $qty = (int)$item['quantity'];
                $stockStmt->execute([
                    ':qty' => $qty,
                    ':qty2' => $qty,
                    ':qty3' => $qty,
                    ':productId' => $item['productId']
                ]);
            }

            $pdo->commit();

            // 7. Vaciar carrito y redirigir
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

$pageTitle = 'Finalizar Compra & Checkout Seguro | Maraña Crochet';
$pageDesc = 'Finaliza tu adopción artesanal con Wompi, PSE, Nequi, Daviplata o Contra Entrega. Taller en Neiva, Huila (Colombia).';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 5rem; max-width: 1100px;">
  
  <!-- Banner Informativo de Sesión de Cliente -->
  <?php if (!isCustomerLoggedIn()): ?>
    <div style="background: #fdf4ff; border: 1px solid #f0abfc; border-radius: var(--radius-md); padding: 0.85rem 1.25rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 1.4rem;">👤</span>
        <span style="font-size: 0.88rem; color: #701a75;">
          ¿Ya tienes cuenta en Maraña? <strong>Inicia sesión</strong> para autocompletar tus datos de envío e historial.
        </span>
      </div>
      <a href="<?= $baseUrl ?>/auth.php?redirect=<?= urlencode($baseUrl . '/checkout.php') ?>" class="btn btn-outlined btn-sm" style="border-color: #c026d3; color: #a21caf;">
        Iniciar Sesión
      </a>
    </div>
  <?php else: ?>
    <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: var(--radius-md); padding: 0.75rem 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.88rem; color: #166534;">
      <span style="font-size: 1.25rem;">✓</span>
      <span>Comprando como <strong><?= h($customer['name']) ?></strong> (<?= h($customer['email']) ?>). Tus datos han sido autocompletados.</span>
    </div>
  <?php endif; ?>

  <!-- Encabezado de Checkout -->
  <div style="text-align: center; margin-bottom: 2.5rem;">
    <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.4rem;">
      Finalizar Adopción Artesanal
    </h1>
    <p style="font-size: 0.95rem; color: var(--color-neutral-medium);">
      Taller Maraña · Despachos nacionales desde Neiva, Huila (Colombia) con empaque aromático de lavanda.
    </p>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background: #fff0f0; border-left: 4px solid #e63946; color: #b71c1c; padding: 1rem 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.9rem; font-weight: 600;">
      ⚠ <?= h($error) ?>
    </div>
  <?php endif; ?>

  <form id="checkout-form" action="<?= $baseUrl ?>/checkout.php" method="POST" enctype="multipart/form-data">
    
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2.5rem; align-items: flex-start;">
      
      <!-- COLUMNA IZQUIERDA: FORMULARIOS DE PASO A PASO -->
      <div style="display: flex; flex-direction: column; gap: 2rem;">

        <!-- PASO 1: DATOS PERSONALES -->
        <div class="checkout-card" style="padding: 1.75rem; border-radius: 1rem;">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-neutral-dark); display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.25rem;">
            <span style="width: 1.8rem; height: 1.8rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">1</span>
            Datos del Destinatario
          </h3>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Nombre Completo *</label>
              <input type="text" name="customerName" required class="form-input" style="width: 100%;" value="<?= h($customer['name'] ?? 'Valentina Castro Pizarro') ?>" placeholder="Ej: Valentina Castro">
            </div>
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Correo Electrónico *</label>
              <input type="email" name="customerEmail" required class="form-input" style="width: 100%;" value="<?= h($customer['email'] ?? 'valentina.castro@gmail.com') ?>" placeholder="tu@correo.com">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Teléfono / WhatsApp *</label>
              <input type="tel" name="customerPhone" required class="form-input" style="width: 100%;" value="<?= h($customer['phone'] ?? '+57 312 456 7890') ?>" placeholder="312 456 7890">
            </div>
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Cédula de Ciudadanía (CC) / NIT *</label>
              <input type="text" name="customerRut" required class="form-input" style="width: 100%;" value="1.075.289.412" placeholder="1.075.289.412">
            </div>
          </div>

          <!-- Opción de Crear Cuenta si no está logueado -->
          <?php if (!isCustomerLoggedIn()): ?>
            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--color-neutral-border);">
              <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 700; color: var(--color-primary); cursor: pointer;">
                <input type="checkbox" name="createAccount" id="createAccountCheck" value="1" onchange="document.getElementById('pass-field-wrap').style.display = this.checked ? 'block' : 'none';">
                Crear una cuenta con estos datos para guardar mi historial de pedidos
              </label>
              <div id="pass-field-wrap" style="display: none; margin-top: 0.75rem;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Crea una Contraseña (mínimo 6 caracteres)</label>
                <input type="password" name="accountPassword" class="form-input" style="width: 100%; max-width: 300px;" placeholder="••••••••">
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- PASO 2: DIRECCIÓN Y ENVÍO -->
        <div class="checkout-card" style="padding: 1.75rem; border-radius: 1rem;">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-neutral-dark); display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.25rem;">
            <span style="width: 1.8rem; height: 1.8rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">2</span>
            Dirección de Envío (Colombia)
          </h3>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Departamento *</label>
              <select name="region" id="ship-region" class="form-select" style="width: 100%;">
                <?php
                $deptos = [
                    'Huila' => 'Huila (Sede Taller Neiva)',
                    'Bogotá D.C.' => 'Bogotá D.C.',
                    'Antioquia' => 'Antioquia (Medellín)',
                    'Valle del Cauca' => 'Valle del Cauca (Cali)',
                    'Cundinamarca' => 'Cundinamarca',
                    'Atlántico' => 'Atlántico (Barranquilla)',
                    'Santander' => 'Santander (Bucaramanga)',
                    'Bolívar' => 'Bolívar (Cartagena)',
                    'Tolima' => 'Tolima (Ibagué)',
                    'Risaralda' => 'Risaralda (Pereira)',
                    'Caldas' => 'Caldas (Manizales)',
                    'Quindío' => 'Quindío (Armenia)',
                    'Boyacá' => 'Boyacá',
                    'Meta' => 'Meta (Villavicencio)'
                ];
                $curReg = $customer['department'] ?? 'Bogotá D.C.';
                foreach ($deptos as $code => $label): ?>
                  <option value="<?= h($code) ?>" <?= $curReg === $code ? 'selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Ciudad / Municipio *</label>
              <input type="text" name="comuna" required class="form-input" style="width: 100%;" value="<?= h($customer['city'] ?? 'Bogotá (Chapinero)') ?>" placeholder="Ej: Neiva, Bogotá, Medellín">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Dirección y Barrio *</label>
              <input type="text" name="street" required class="form-input" style="width: 100%;" value="<?= h($customer['address'] ?? 'Carrera 15 # 85-30') ?>" placeholder="Calle / Carrera #, Barrio">
            </div>
            <div>
              <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Apto / Torre</label>
              <input type="text" name="dept" class="form-input" style="width: 100%;" value="Apto 402, Torre 1" placeholder="Ej: Torre 2 Apto 301">
            </div>
          </div>

          <!-- Opciones de Envío -->
          <div style="margin-top: 1.25rem;">
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.5rem;">Modalidad de Envío *</label>
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
              <label class="shipping-option-card" style="border: 1.5px solid var(--color-primary); background: #faf5ff; padding: 0.85rem 1rem; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                  <input type="radio" name="shippingMethod" value="ciudades-principales" checked onchange="updateShipping(11500)">
                  <div>
                    <div style="font-weight: 700; font-size: 0.88rem; color: var(--color-neutral-dark);">Ciudades Principales (Bogotá, Medellín, Cali, Neiva)</div>
                    <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">Inter Rapidísimo / Servientrega (2-3 días hábiles)</div>
                  </div>
                </div>
                <span style="font-weight: 800; color: var(--color-primary); font-size: 0.95rem;">$11.500 COP</span>
              </label>

              <label class="shipping-option-card" style="border: 1px solid var(--color-neutral-border); background: #fff; padding: 0.85rem 1rem; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                  <input type="radio" name="shippingMethod" value="nacional-colombia" onchange="updateShipping(14500)">
                  <div>
                    <div style="font-weight: 700; font-size: 0.88rem; color: var(--color-neutral-dark);">Resto del País / Cobertura Nacional</div>
                    <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">Coordinadora / Envia a municipios de Colombia</div>
                  </div>
                </div>
                <span style="font-weight: 800; color: var(--color-neutral-dark); font-size: 0.95rem;">$14.500 COP</span>
              </label>

              <label class="shipping-option-card" style="border: 1px solid var(--color-neutral-border); background: #fff; padding: 0.85rem 1rem; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                  <input type="radio" name="shippingMethod" value="envio-neiva" onchange="updateShipping(6000)">
                  <div>
                    <div style="font-weight: 700; font-size: 0.88rem; color: var(--color-neutral-dark);">Entrega Urbana Express en Neiva (Huila)</div>
                    <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">Mensajería directa el mismo día</div>
                  </div>
                </div>
                <span style="font-weight: 800; color: var(--color-neutral-dark); font-size: 0.95rem;">$6.000 COP</span>
              </label>
            </div>
          </div>

          <!-- Dedicatoria -->
          <div style="margin-top: 1.25rem;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">
              🎁 Dedicatoria Caligrafiada de Regalo (Opcional - Gratis)
            </label>
            <textarea name="giftDedication" rows="2" class="form-input" style="width: 100%;" placeholder="Mensaje para escribir a mano en la tarjeta kraft aromática...">¡Feliz Cumpleaños! Que tu amigurumi tejido a mano con amor en Neiva cuide tus más dulces sueños.</textarea>
          </div>
        </div>

        <!-- PASO 3: MÉTODO DE PAGO COLOMBIANO -->
        <div class="checkout-card" style="padding: 1.75rem; border-radius: 1rem;">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-neutral-dark); display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.5rem;">
            <span style="width: 1.8rem; height: 1.8rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">3</span>
            Elige tu Método de Pago en Colombia
          </h3>
          <p style="font-size: 0.82rem; color: var(--color-neutral-medium); margin-bottom: 1.25rem;">
            Selecciona la pasarela o modalidad con la que deseas abonar tu pedido. Transacciones 100% protegidas.
          </p>

          <!-- Selectores Tipo Tarjeta de Pago -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">
            
            <!-- Opción 1: Wompi Tarjetas -->
            <label class="payment-method-tile active" id="tile-wompi_card" onclick="selectPaymentMethod('wompi_card')" style="border: 2px solid var(--color-primary); background: #faf5ff; padding: 1rem; border-radius: var(--radius-sm); cursor: pointer; display: flex; flex-direction: column; gap: 0.35rem;">
              <input type="radio" name="paymentMethod" value="wompi_card" checked style="display: none;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.3rem;">💳</span>
                <span style="font-size: 0.68rem; font-weight: 800; background: #c084fc; color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px;">Wompi</span>
              </div>
              <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-neutral-dark);">Tarjetas Débito / Crédito</div>
              <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Visa, Mastercard, Amex</div>
            </label>

            <!-- Opción 2: PSE -->
            <label class="payment-method-tile" id="tile-pse" onclick="selectPaymentMethod('pse')" style="border: 1px solid var(--color-neutral-border); background: #fff; padding: 1rem; border-radius: var(--radius-sm); cursor: pointer; display: flex; flex-direction: column; gap: 0.35rem;">
              <input type="radio" name="paymentMethod" value="pse" style="display: none;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.3rem;">⚡</span>
                <span style="font-size: 0.68rem; font-weight: 800; background: #38bdf8; color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px;">PSE</span>
              </div>
              <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-neutral-dark);">Débito PSE</div>
              <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Todos los bancos colombianos</div>
            </label>

            <!-- Opción 3: Nequi / Daviplata -->
            <label class="payment-method-tile" id="tile-nequi_daviplata" onclick="selectPaymentMethod('nequi_daviplata')" style="border: 1px solid var(--color-neutral-border); background: #fff; padding: 1rem; border-radius: var(--radius-sm); cursor: pointer; display: flex; flex-direction: column; gap: 0.35rem;">
              <input type="radio" name="paymentMethod" value="nequi_daviplata" style="display: none;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.3rem;">📱</span>
                <span style="font-size: 0.68rem; font-weight: 800; background: #f472b6; color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px;">Móvil</span>
              </div>
              <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-neutral-dark);">Nequi / Daviplata</div>
              <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">QR o transferencia directa</div>
            </label>

            <!-- Opción 4: Transferencia Bancolombia -->
            <label class="payment-method-tile" id="tile-transferencia" onclick="selectPaymentMethod('transferencia')" style="border: 1px solid var(--color-neutral-border); background: #fff; padding: 1rem; border-radius: var(--radius-sm); cursor: pointer; display: flex; flex-direction: column; gap: 0.35rem;">
              <input type="radio" name="paymentMethod" value="transferencia" style="display: none;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.3rem;">🏦</span>
                <span style="font-size: 0.68rem; font-weight: 800; background: #facc15; color: #713f12; padding: 0.15rem 0.4rem; border-radius: 4px;">Bancolombia</span>
              </div>
              <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-neutral-dark);">Cta. Ahorros Taller</div>
              <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Adjuntar comprobante TEF</div>
            </label>

            <!-- Opción 5: Contra Entrega -->
            <label class="payment-method-tile" id="tile-contra_entrega" onclick="selectPaymentMethod('contra_entrega')" style="border: 1px solid var(--color-neutral-border); background: #fff; padding: 1rem; border-radius: var(--radius-sm); cursor: pointer; display: flex; flex-direction: column; gap: 0.35rem;">
              <input type="radio" name="paymentMethod" value="contra_entrega" style="display: none;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1.3rem;">💵</span>
                <span style="font-size: 0.68rem; font-weight: 800; background: #4ade80; color: #064e3b; padding: 0.15rem 0.4rem; border-radius: 4px;">Efectivo</span>
              </div>
              <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-neutral-dark);">Pago Contra Entrega</div>
              <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">Pagas al recibir tu paquete</div>
            </label>

          </div>

          <!-- PANELES DINÁMICOS SEGÚN EL MÉTODO -->

          <!-- PANEL 1: WOMPI TARJETAS -->
          <div id="panel-wompi_card" class="payment-panel" style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: var(--radius-sm); padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
              <span style="font-weight: 800; font-size: 0.92rem; color: #6b21a8;">Integración Wompi (Bancolombia Sandbox Activo)</span>
              <span style="font-size: 0.72rem; font-family: monospace; background: #e9d5ff; padding: 0.2rem 0.5rem; border-radius: 4px;">Modo Seguro 256-bit</span>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Número de Tarjeta</label>
                <input type="text" name="cardNumber" class="form-input" style="width: 100%;" placeholder="4242 •••• •••• 4242" value="4242 4242 4242 4242">
              </div>
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Vencimiento (MM/AA)</label>
                <input type="text" name="cardExpiry" class="form-input" style="width: 100%;" placeholder="12/28" value="12/28">
              </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">CVC / CVV</label>
                <input type="text" name="cardCvc" class="form-input" style="width: 100%;" placeholder="123" value="123">
              </div>
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Cuotas</label>
                <select name="cardInstallments" class="form-select" style="width: 100%;">
                  <option value="1">1 Cuota (Sin Interés)</option>
                  <option value="3">3 Cuotas</option>
                  <option value="6">6 Cuotas</option>
                  <option value="12">12 Cuotas</option>
                </select>
              </div>
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Titular</label>
                <input type="text" name="cardHolder" class="form-input" style="width: 100%;" placeholder="Nombre en tarjeta" value="Valentina Castro">
              </div>
            </div>
          </div>

          <!-- PANEL 2: PSE -->
          <div id="panel-pse" class="payment-panel" style="display: none; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: var(--radius-sm); padding: 1.25rem;">
            <div style="font-weight: 800; font-size: 0.92rem; color: #0369a1; margin-bottom: 0.75rem;">
              Débito Bancario PSE (Pagos Seguros en Línea)
            </div>
            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Selecciona tu Entidad Bancaria *</label>
                <select name="pseBank" class="form-select" style="width: 100%;">
                  <?php foreach ($paymentMethods['pse']['banks'] as $b): ?>
                    <option value="<?= h($b) ?>"><?= h($b) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Tipo de Persona *</label>
                <select name="pseUserType" class="form-select" style="width: 100%;">
                  <option value="Natural">Persona Natural</option>
                  <option value="Juridica">Persona Jurídica (Empresa)</option>
                </select>
              </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Tipo Documento</label>
                <select name="pseDocType" class="form-select" style="width: 100%;">
                  <option value="CC">Cédula de Ciudadanía (CC)</option>
                  <option value="NIT">NIT</option>
                  <option value="CE">Cédula de Extranjería (CE)</option>
                  <option value="PP">Pasaporte</option>
                </select>
              </div>
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Número de Documento *</label>
                <input type="text" name="pseDocNumber" class="form-input" style="width: 100%;" value="1.075.289.412" placeholder="Número de cédula">
              </div>
            </div>
          </div>

          <!-- PANEL 3: NEQUI / DAVIPLATA -->
          <div id="panel-nequi_daviplata" class="payment-panel" style="display: none; background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: var(--radius-sm); padding: 1.25rem;">
            <div style="font-weight: 800; font-size: 0.92rem; color: #9d174d; margin-bottom: 0.75rem;">
              Pago Inmediato Móvil por Nequi o Daviplata
            </div>
            <div style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
              <div style="background: #fff; padding: 0.75rem; border-radius: 8px; border: 1px solid #f472b6; text-align: center;">
                <div style="font-size: 0.75rem; font-weight: 800; color: #db2777; margin-bottom: 0.25rem;">QR MARAÑA</div>
                <div style="width: 90px; height: 90px; background: #262626; color: #fff; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; text-align: center; border-radius: 4px; padding: 0.2rem;">
                  [QR NEQUI / DAVIPLATA]
                </div>
              </div>
              <div style="flex: 1; min-width: 220px;">
                <div style="font-size: 0.85rem; margin-bottom: 0.4rem;">
                  Número de Envío: <strong style="font-size: 1.1rem; color: #9d174d;">312 456 7890</strong>
                </div>
                <div style="font-size: 0.78rem; color: #701a75; margin-bottom: 0.85rem;">
                  Titular: <strong>Maraña Crochet Artesanal</strong> (Cero comisión)
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                  <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 700;">Billetera</label>
                    <select name="mobileWallet" class="form-select" style="width: 100%;">
                      <option value="Nequi">Nequi</option>
                      <option value="Daviplata">Daviplata</option>
                    </select>
                  </div>
                  <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 700;">Referencia / Folio</label>
                    <input type="text" name="mobileRef" class="form-input" style="width: 100%;" placeholder="M9482910">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PANEL 4: TRANSFERENCIA BANCOLOMBIA TRADICIONAL -->
          <div id="panel-transferencia" class="payment-panel" style="display: none; background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-sm); padding: 1.25rem;">
            <div style="font-weight: 800; font-size: 0.92rem; color: #92400e; margin-bottom: 0.75rem;">
              Transferencia a Cuenta de Ahorros Bancolombia
            </div>
            <div style="background: #fff; border: 1px solid #fde68a; border-radius: 6px; padding: 0.85rem; margin-bottom: 1rem; font-size: 0.85rem;">
              <div><strong>Banco:</strong> Bancolombia</div>
              <div><strong>Cuenta de Ahorros:</strong> 824-000192-41</div>
              <div><strong>Titular:</strong> Maraña Crochet Taller Artesanal (Neiva)</div>
              <div><strong>NIT:</strong> 901.482.910-3</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Número de Folio / Aprobación</label>
                <input type="text" name="tefFolio" class="form-input" style="width: 100%;" placeholder="Ej: 94827103">
              </div>
              <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem;">Adjuntar Soporte (PDF/JPG)</label>
                <input type="file" name="voucher" class="form-input" style="width: 100%; padding: 0.35rem;">
              </div>
            </div>
          </div>

          <!-- PANEL 5: CONTRA ENTREGA -->
          <div id="panel-contra_entrega" class="payment-panel" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-sm); padding: 1.25rem;">
            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
              <span style="font-size: 1.5rem;">🤝</span>
              <div>
                <div style="font-weight: 800; font-size: 0.92rem; color: #166534; margin-bottom: 0.25rem;">
                  Pago Contra Entrega en Efectivo
                </div>
                <p style="font-size: 0.82rem; color: #14532d; line-height: 1.45; margin: 0;">
                  No necesitas pagar nada por adelantado. Tu pedido será despachado desde nuestro taller de Neiva por <strong>Inter Rapidísimo</strong> o <strong>Servientrega</strong>, y le abonarás el valor exacto en efectivo al transportador al momento de recibir la caja en tus manos.
                </p>
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- COLUMNA DERECHA: RESUMEN DE COMPRA & FINALIZAR (STICKY) -->
      <div style="position: sticky; top: 5.5rem;">
        
        <div class="checkout-card" style="padding: 1.75rem; border-radius: 1rem; box-shadow: 0 4px 20px rgba(123,44,191,0.08);">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-neutral-dark); border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 0.85rem; margin-bottom: 1rem;">
            Resumen de Adopción (<?= getCartCount() ?> piezas)
          </h3>

          <!-- Lista de Items en Cart -->
          <div style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 240px; overflow-y: auto; margin-bottom: 1.25rem; padding-right: 0.35rem;">
            <?php foreach ($cartItems as $c): ?>
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="<?= h($c['image']) ?>" alt="<?= h($c['name']) ?>" style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid var(--color-neutral-border);" onerror="this.onerror=null; this.src='<?= $baseUrl ?>/assets/img/kit-crochet.jpg';">
                <div style="flex: 1; min-width: 0;">
                  <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-neutral-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= h($c['name']) ?>
                  </div>
                  <div style="font-size: 0.72rem; color: var(--color-neutral-medium);">
                    Cant: <?= (int)$c['quantity'] ?> · <?= formatCOP($c['price']) ?>
                  </div>
                </div>
                <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-neutral-dark);">
                  <?= formatCOP($c['price'] * $c['quantity']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Desglose de Totales -->
          <div style="border-top: 1px solid var(--color-neutral-border); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--color-neutral-medium);">Subtotal Criaturas:</span>
              <span id="summary-subtotal" style="font-weight: 700; color: var(--color-neutral-dark);"><?= formatCOP($subtotal) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--color-neutral-medium);">Envío Nacional:</span>
              <span id="summary-shipping" style="font-weight: 700; color: var(--color-primary);">$11.500 COP</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #15803d;">
              <span>Empaque Kraft & Lavanda:</span>
              <span style="font-weight: 700;">Gratis</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; color: var(--color-neutral-dark); border-top: 1px solid var(--color-neutral-border); padding-top: 0.75rem; margin-top: 0.25rem;">
              <span>Total a Pagar:</span>
              <span id="summary-total" style="color: var(--color-primary);"><?= formatCOP($subtotal + 11500) ?></span>
            </div>
          </div>

          <!-- Botón de Confirmación Principal -->
          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; margin-top: 1.5rem; font-size: 1.05rem; box-shadow: 0 4px 14px rgba(123,44,191,0.35);">
            <svg style="width: 1.2rem; height: 1.2rem; margin-right: 0.4rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            Confirmar Pedido & Proceder
          </button>

          <div style="text-align: center; margin-top: 1rem; font-size: 0.72rem; color: var(--color-neutral-light); line-height: 1.4;">
            🔒 Pago seguro cifrado SSL · Garantía artesanal Maraña · Despachos desde Neiva (Huila)
          </div>

        </div>

      </div>

    </div>

  </form>

</div>

<script>
const subtotalAmount = <?= (int)$subtotal ?>;

function updateShipping(cost) {
  const shippingEl = document.getElementById('summary-shipping');
  const totalEl = document.getElementById('summary-total');
  
  if (cost === 0) {
    shippingEl.textContent = 'Gratis';
  } else {
    shippingEl.textContent = '$' + cost.toLocaleString('es-CO') + ' COP';
  }
  
  const total = subtotalAmount + cost;
  totalEl.textContent = '$' + total.toLocaleString('es-CO') + ' COP';
}

function selectPaymentMethod(method) {
  // Desactivar todos los tiles
  document.querySelectorAll('.payment-method-tile').forEach(t => {
    t.style.borderColor = 'var(--color-neutral-border)';
    t.style.background = '#FFFFFF';
  });
  
  // Ocultar todos los paneles
  document.querySelectorAll('.payment-panel').forEach(p => {
    p.style.display = 'none';
  });

  // Activar tile seleccionado
  const tile = document.getElementById('tile-' + method);
  if (tile) {
    tile.style.borderColor = 'var(--color-primary)';
    tile.style.background = '#faf5ff';
    const radio = tile.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
  }

  // Mostrar panel correspondiente
  const panel = document.getElementById('panel-' + method);
  if (panel) {
    panel.style.display = 'block';
  }
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
