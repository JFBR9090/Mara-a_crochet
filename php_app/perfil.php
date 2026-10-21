<?php
declare(strict_types=1);

// perfil.php - Perfil de Cliente e Historial de Pedidos en Maraña Crochet
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$baseUrl = getBaseUrl();

// Proteger ruta para clientes autenticados
if (!isCustomerLoggedIn()) {
    header('Location: ' . $baseUrl . '/auth.php?redirect=' . urlencode($baseUrl . '/perfil.php'));
    exit();
}

$customerId = (int)$_SESSION['customer_id'];
$successMsg = '';
$errorMsg = '';

// Procesar actualización de datos del cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($name) || empty($phone) || empty($address) || empty($city)) {
            $errorMsg = 'Por favor completa todos los campos de información personal.';
        } else {
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $errorMsg = 'La nueva contraseña debe tener al menos 6 caracteres.';
                } else {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("
                        UPDATE `customers` 
                        SET `name` = :name, `phone` = :phone, `address` = :address, `city` = :city, `department` = :department, `password` = :pass 
                        WHERE `id` = :id
                    ");
                    $stmt->execute([
                        ':name' => $name,
                        ':phone' => $phone,
                        ':address' => $address,
                        ':city' => $city,
                        ':department' => $department,
                        ':pass' => $hash,
                        ':id' => $customerId
                    ]);
                    $successMsg = '¡Tus datos y tu contraseña han sido actualizados con éxito!';
                }
            } else {
                $stmt = $pdo->prepare("
                    UPDATE `customers` 
                    SET `name` = :name, `phone` = :phone, `address` = :address, `city` = :city, `department` = :department 
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':address' => $address,
                    ':city' => $city,
                    ':department' => $department,
                    ':id' => $customerId
                ]);
                $successMsg = '¡Tus datos de entrega han sido actualizados con éxito!';
            }

            if (empty($errorMsg)) {
                // Actualizar sesión
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_phone'] = $phone;
                $_SESSION['customer_address'] = $address;
                $_SESSION['customer_city'] = $city;
                $_SESSION['customer_department'] = $department;
            }
        }
    }
}

// Obtener datos frescos del cliente
$custStmt = $pdo->prepare("SELECT * FROM `customers` WHERE `id` = :id LIMIT 1");
$custStmt->execute([':id' => $customerId]);
$customer = $custStmt->fetch();

// Obtener historial de pedidos asociados por customerId o por correo
$ordersStmt = $pdo->prepare("
    SELECT * FROM `orders` 
    WHERE `customerId` = :cid OR `customerEmail` = :email 
    ORDER BY `created_at` DESC
");
$ordersStmt->execute([
    ':cid' => $customerId,
    ':email' => $customer['email'] ?? ''
]);
$customerOrders = $ordersStmt->fetchAll();

$pageTitle = 'Mi Perfil & Historial de Pedidos | Maraña Crochet';
$pageDesc = 'Consulta tus amigurumis adoptados, certificados de nacimiento y actualiza tus datos de envío en Colombia.';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1040px; margin: 3rem auto 6rem; padding: 0 1rem;">

  <!-- Header de Perfil -->
  <div style="background: linear-gradient(135deg, #fbf7ff 0%, #f3ebfc 100%); border: 1px solid var(--color-primary-light); border-radius: 1.25rem; padding: 2rem; margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; box-shadow: 0 4px 20px rgba(123,44,191,0.06);">
    <div style="display: flex; align-items: center; gap: 1.25rem;">
      <div style="width: 4.5rem; height: 4.5rem; border-radius: 50%; background: var(--color-primary); color: #fff; font-size: 1.8rem; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(123,44,191,0.3); border: 3px solid #fff;">
        <?= strtoupper(substr($customer['name'], 0, 1)) ?>
      </div>
      <div>
        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--color-neutral-dark); margin: 0 0 0.25rem 0;">
          <?= h($customer['name']) ?>
        </h1>
        <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--color-neutral-medium); flex-wrap: wrap;">
          <span>✉️ <?= h($customer['email']) ?></span>
          <span>📱 <?= h($customer['phone']) ?></span>
          <span>📍 <?= h($customer['city']) ?>, <?= h($customer['department'] ?? 'Colombia') ?></span>
        </div>
      </div>
    </div>

    <div style="display: flex; gap: 0.75rem;">
      <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-primary btn-sm">
        🧶 Explorar Catálogo
      </a>
      <a href="<?= $baseUrl ?>/logout_customer.php" class="btn btn-outlined btn-sm" style="color: #b71c1c; border-color: #ffcccc;">
        Cerrar Sesión
      </a>
    </div>
  </div>

  <?php if (!empty($successMsg)): ?>
    <div style="background: #f0fff4; border-left: 4px solid #38a169; color: #22543d; padding: 1rem 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.9rem; font-weight: 600;">
      ✓ <?= h($successMsg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div style="background: #fff0f0; border-left: 4px solid #e63946; color: #b71c1c; padding: 1rem 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.9rem; font-weight: 600;">
      ⚠ <?= h($errorMsg) ?>
    </div>
  <?php endif; ?>

  <!-- Grid de Contenido: Historial de Pedidos (Columna Principal) y Datos de Envío (Columna Lateral) -->
  <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 2rem; align-items: flex-start;">

    <!-- COLUMNA 1: HISTORIAL DE PEDIDOS -->
    <div>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--color-neutral-dark); margin: 0;">
          🛍️ Mis Adopciones & Pedidos (<?= count($customerOrders) ?>)
        </h2>
      </div>

      <?php if (empty($customerOrders)): ?>
        <div class="checkout-card" style="padding: 3rem 1.5rem; text-align: center; border-radius: 1rem;">
          <div style="font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.7;">🧺</div>
          <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Aún no tienes pedidos registrados</h3>
          <p style="font-size: 0.88rem; color: var(--color-neutral-medium); max-width: 380px; margin: 0 auto 1.5rem;">
            Explora las criaturas mágicas y kits de nuestro taller en Neiva y adopta tu primer amigurumi hecho a mano.
          </p>
          <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-primary">
            Ver Amigurumis Disponibles
          </a>
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
          <?php foreach ($customerOrders as $order): ?>
            <?php 
              $items = json_decode($order['items'] ?? '[]', true) ?: [];
              $status = strtolower($order['status'] ?? 'revision');
              $statusMap = [
                  'revision' => ['label' => 'En Verificación', 'bg' => '#fff3cd', 'color' => '#856404'],
                  'preparando' => ['label' => 'En Tejido / Empaque', 'bg' => '#e2e3e5', 'color' => '#383d41'],
                  'despachado' => ['label' => 'En Camino (Guía Activa)', 'bg' => '#cce5ff', 'color' => '#004085'],
                  'entregado' => ['label' => 'Entregado & Adoptado', 'bg' => '#d4edda', 'color' => '#155724']
              ];
              $stInfo = $statusMap[$status] ?? ['label' => ucfirst($status), 'bg' => '#f8f9fa', 'color' => '#333'];
            ?>
            <div class="checkout-card" style="padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--color-neutral-border);">
              
              <!-- Encabezado de la Orden -->
              <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 0.85rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                  <span style="font-weight: 800; font-size: 1.05rem; color: var(--color-primary);">
                    <?= h($order['id']) ?>
                  </span>
                  <span style="font-size: 0.78rem; color: var(--color-neutral-medium); margin-left: 0.5rem;">
                    📅 <?= h($order['date']) ?>
                  </span>
                </div>
                <div>
                  <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; background: <?= $stInfo['bg'] ?>; color: <?= $stInfo['color'] ?>;">
                    <?= $stInfo['label'] ?>
                  </span>
                </div>
              </div>

              <!-- Lista de Items -->
              <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.15rem;">
                <?php foreach ($items as $item): ?>
                  <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <?php if (!empty($item['image'])): ?>
                      <img src="<?= h($item['image']) ?>" alt="<?= h($item['productName'] ?? $item['name'] ?? '') ?>" style="width: 2.8rem; height: 2.8rem; border-radius: 0.5rem; object-fit: cover; border: 1px solid var(--color-neutral-border);">
                    <?php else: ?>
                      <div style="width: 2.8rem; height: 2.8rem; border-radius: 0.5rem; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🧶</div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                      <div style="font-weight: 700; font-size: 0.88rem; color: var(--color-neutral-dark);">
                        <?= h($item['productName'] ?? $item['name'] ?? 'Amigurumi') ?>
                      </div>
                      <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">
                        Cant: <?= (int)($item['quantity'] ?? 1) ?> · <?= formatCOP($item['price'] ?? 0) ?> c/u
                      </div>
                    </div>
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--color-neutral-dark);">
                      <?= formatCOP(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Resumen de Pago y Acciones -->
              <div style="background: var(--color-neutral-bg); border-radius: var(--radius-sm); padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                  <div style="font-size: 0.78rem; color: var(--color-neutral-medium);">Método de Pago:</div>
                  <div style="font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark);">
                    <?= h(ucwords(str_replace('_', ' ', $order['paymentMethod'] ?? 'Transferencia Bancolombia'))) ?>
                  </div>
                </div>
                <div style="text-align: right;">
                  <div style="font-size: 0.78rem; color: var(--color-neutral-medium);">Total Pagado:</div>
                  <div style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">
                    <?= formatCOP($order['total']) ?>
                  </div>
                </div>
              </div>

              <!-- Botones de Acción -->
              <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                <a href="<?= $baseUrl ?>/comprobante.php?id=<?= urlencode($order['id']) ?>" target="_blank" class="btn btn-outlined btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                  <svg style="width: 0.95rem; height: 0.95rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                  Ver Comprobante Oficial
                </a>
                <a href="https://wa.me/573124567890?text=<?= urlencode("¡Hola Maraña Crochet! Quisiera consultar sobre mi pedido {$order['id']}") ?>" target="_blank" class="btn btn-sm" style="background: #25D366; color: #fff; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                  WhatsApp Taller
                </a>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- COLUMNA 2: MIS DATOS DE ENVÍO Y PERFIL -->
    <div>
      <div class="checkout-card" style="padding: 1.75rem; border-radius: 1rem; position: sticky; top: 5.5rem;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">
          ⚙️ Mis Datos de Entrega
        </h3>
        <p style="font-size: 0.8rem; color: var(--color-neutral-medium); margin-bottom: 1.25rem;">
          Estos datos se autocompletarán de forma segura en tus futuros checkouts para agilizar tus compras.
        </p>

        <form action="perfil.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
          <input type="hidden" name="action" value="update_profile">

          <div>
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Nombre Completo *</label>
            <input type="text" name="name" required class="form-input" style="width: 100%;" value="<?= h($customer['name']) ?>">
          </div>

          <div>
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Correo Electrónico (No modificable)</label>
            <input type="email" disabled class="form-input" style="width: 100%; background: #f3f4f6; color: #6b7280; cursor: not-allowed;" value="<?= h($customer['email']) ?>">
          </div>

          <div>
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Teléfono / WhatsApp *</label>
            <input type="tel" name="phone" required class="form-input" style="width: 100%;" value="<?= h($customer['phone']) ?>">
          </div>

          <div>
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Dirección de Entrega en Colombia *</label>
            <input type="text" name="address" required class="form-input" style="width: 100%;" value="<?= h($customer['address']) ?>">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
              <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Ciudad / Municipio *</label>
              <input type="text" name="city" required class="form-input" style="width: 100%;" value="<?= h($customer['city']) ?>">
            </div>
            <div>
              <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Departamento *</label>
              <select name="department" class="form-select" style="width: 100%;">
                <?php
                $deptos = [
                    'Huila', 'Bogotá D.C.', 'Antioquia', 'Valle del Cauca', 'Cundinamarca',
                    'Atlántico', 'Santander', 'Bolívar', 'Caldas', 'Risaralda', 'Quindío',
                    'Boyacá', 'Tolima', 'Nariño', 'Cauca', 'Cesar', 'Córdoba', 'Meta'
                ];
                $curDep = $customer['department'] ?? 'Huila';
                foreach ($deptos as $d): ?>
                  <option value="<?= h($d) ?>" <?= $curDep === $d ? 'selected' : '' ?>><?= h($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="border-top: 1px dashed var(--color-neutral-border); padding-top: 0.85rem; margin-top: 0.25rem;">
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Cambiar Contraseña (Opcional)</label>
            <input type="password" name="new_password" placeholder="Dejar en blanco para conservar actual" class="form-input" style="width: 100%;">
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; justify-content: center;">
            Guardar Cambios
          </button>
        </form>

      </div>
    </div>

  </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
