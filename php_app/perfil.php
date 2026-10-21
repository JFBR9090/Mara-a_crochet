<?php
declare(strict_types=1);

// perfil.php - Perfil de Cliente, Foto de Perfil, Tema y Pedidos en Maraña Crochet
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

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. SUBIR FOTO DE PERFIL PERSONALIZADA
    if ($action === 'upload_avatar' && isset($_FILES['avatar_file'])) {
        $file = $_FILES['avatar_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = 'No se pudo subir la foto de perfil. Código de error: ' . $file['error'];
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errorMsg = 'La foto es demasiado pesada. El tamaño máximo permitido es de 5 MB.';
        } else {
            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            if (!isset($allowedMimes[$mime])) {
                $errorMsg = 'Formato de imagen no compatible. Por favor sube una imagen JPG, PNG o WEBP.';
            } else {
                $ext = $allowedMimes[$mime];
                $newFilename = 'avatar_' . $customerId . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                
                $uploadDir = __DIR__ . '/uploads/avatars';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $destPath = $uploadDir . '/' . $newFilename;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    // Replicar en htdocs para visualización inmediata en servidor local
                    $xamppDir = 'C:/xampp/htdocs/marana/uploads/avatars';
                    if (is_dir($xamppDir)) {
                        @copy($destPath, $xamppDir . '/' . $newFilename);
                    }

                    $avatarDbPath = 'uploads/avatars/' . $newFilename;

                    // Actualizar en base de datos
                    $stmt = $pdo->prepare("UPDATE `customers` SET `avatar` = :avatar WHERE `id` = :id");
                    $stmt->execute([':avatar' => $avatarDbPath, ':id' => $customerId]);

                    $_SESSION['customer_avatar'] = $avatarDbPath;
                    $successMsg = '¡Tu foto de perfil ha sido subida y guardada exitosamente! 📷✨';
                } else {
                    $errorMsg = 'Ocurrió un error al guardar el archivo en el servidor.';
                }
            }
        }
    }

    // 2. ELEGIR AVATAR ARTESANAL PREESTABLECIDO
    elseif ($action === 'preset_avatar') {
        $preset = trim($_POST['preset'] ?? '');
        $allowedPresets = ['preset:rana', 'preset:aranita', 'preset:murcielago', 'preset:pulpo', 'preset:ovillo', 'preset:abeja'];

        if (in_array($preset, $allowedPresets, true)) {
            $stmt = $pdo->prepare("UPDATE `customers` SET `avatar` = :avatar WHERE `id` = :id");
            $stmt->execute([':avatar' => $preset, ':id' => $customerId]);

            $_SESSION['customer_avatar'] = $preset;
            $successMsg = '¡Avatar amigurumi seleccionado correctamente! 🧶✨';
        } else {
            $errorMsg = 'Avatar artesanal no reconocido.';
        }
    }

    // 3. RESTABLECER O ELIMINAR AVATAR
    elseif ($action === 'remove_avatar') {
        $stmt = $pdo->prepare("UPDATE `customers` SET `avatar` = NULL WHERE `id` = :id");
        $stmt->execute([':id' => $customerId]);

        $_SESSION['customer_avatar'] = null;
        $successMsg = '¡Tu foto de perfil se ha restablecido a tus iniciales tradicionales!';
    }

    // 4. GUARDAR PREFERENCIA DE MODO CLARO / OSCURO
    elseif ($action === 'update_theme_preference') {
        $theme = trim($_POST['theme_preference'] ?? 'light');
        if (in_array($theme, ['light', 'dark'], true)) {
            $stmt = $pdo->prepare("UPDATE `customers` SET `theme_preference` = :theme WHERE `id` = :id");
            $stmt->execute([':theme' => $theme, ':id' => $customerId]);

            $_SESSION['customer_theme'] = $theme;
            $successMsg = '¡Preferencia de visualización guardada (' . ($theme === 'dark' ? 'Modo Oscuro 🌙' : 'Modo Claro ☀️') . ')!';
        }
    }

    // 5. ACTUALIZAR INFORMACIÓN PERSONAL Y DE ENVÍO
    elseif ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $department = trim($_POST['department'] ?? 'Huila');
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($name) || empty($phone) || empty($address) || empty($city)) {
            $errorMsg = 'Por favor completa todos los campos de información personal y de entrega.';
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
                    $successMsg = '¡Tus datos de entrega y tu contraseña han sido actualizados con éxito!';
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
                $successMsg = '¡Tus datos de entrega en Colombia han sido actualizados con éxito!';
            }

            if (empty($errorMsg)) {
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_phone'] = $phone;
                $_SESSION['customer_address'] = $address;
                $_SESSION['customer_city'] = $city;
                $_SESSION['customer_department'] = $department;
            }
        }
    }
}

// Obtener datos frescos del cliente desde la base de datos
$custStmt = $pdo->prepare("SELECT * FROM `customers` WHERE `id` = :id LIMIT 1");
$custStmt->execute([':id' => $customerId]);
$customer = $custStmt->fetch();

// Sincronizar sesión con datos de DB
$_SESSION['customer_avatar'] = $customer['avatar'] ?? null;
$_SESSION['customer_theme'] = $customer['theme_preference'] ?? 'light';

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
$pageDesc = 'Personaliza tu foto de perfil, elige el modo claro u oscuro y consulta el estado de tus amigurumis adoptados en Colombia.';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1060px; margin: 3rem auto 6rem; padding: 0 1rem;">

  <!-- Header de Perfil con Foto de Perfil & Acciones -->
  <div class="profile-header-card">
    <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
      <!-- Avatar con indicador -->
      <div style="position: relative; display: inline-block;">
        <?= renderCustomerAvatarHtml($customer['avatar'] ?? null, $customer['name'], 'lg', 'profile-main-avatar') ?>
        <button 
          type="button" 
          onclick="toggleAvatarPanel()" 
          title="Cambiar Foto o Avatar"
          style="position: absolute; bottom: 0; right: 0; background: var(--color-primary); color: #fff; border: 2px solid var(--color-neutral-card); border-radius: 50%; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"
        >
          📷
        </button>
      </div>

      <div>
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
          <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark); margin: 0;">
            <?= h($customer['name']) ?>
          </h1>
          <span style="font-size: 0.72rem; font-weight: 700; background: var(--color-primary-subtle); color: var(--color-primary); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); border: 1px solid var(--color-primary-border);">
            🧶 Miembro Maraña
          </span>
        </div>
        <div style="display: flex; gap: 1.25rem; font-size: 0.85rem; color: var(--color-neutral-medium); margin-top: 0.35rem; flex-wrap: wrap;">
          <span>✉️ <?= h($customer['email']) ?></span>
          <span>📱 <?= h($customer['phone']) ?></span>
          <span>📍 <?= h($customer['city']) ?>, <?= h($customer['department'] ?? 'Colombia') ?></span>
        </div>
      </div>
    </div>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <button type="button" onclick="toggleAvatarPanel()" class="btn btn-outlined btn-sm">
        📸 Cambiar Foto
      </button>
      <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-primary btn-sm">
        🧶 Explorar Catálogo
      </a>
      <a href="<?= $baseUrl ?>/logout_customer.php" class="btn btn-outlined btn-sm" style="color: #e53e3e; border-color: rgba(229, 62, 62, 0.3);">
        Cerrar Sesión
      </a>
    </div>
  </div>

  <!-- Panel Desplegable: Personalización de Foto de Perfil & Avatares Amigurumi -->
  <div id="avatar-customizer-panel" class="checkout-card" style="display: none; padding: 1.75rem; border-radius: 1.25rem; margin-bottom: 2.5rem; animation: slideInDown 0.3s ease;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
      <div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--color-neutral-dark); margin: 0;">
          🖼️ Personalizar Foto de Perfil
        </h3>
        <p style="font-size: 0.85rem; color: var(--color-neutral-medium); margin-top: 0.25rem;">
          Puedes subir una foto propia o elegir un simpático avatar temático de nuestras criaturas de crochet.
        </p>
      </div>
      <button type="button" onclick="toggleAvatarPanel()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--color-neutral-medium);">&times;</button>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 2rem; align-items: flex-start;">
      
      <!-- Opción A: Subir Foto Propia -->
      <div style="background: var(--color-neutral-bg); padding: 1.25rem; border-radius: var(--radius-md); border: 1px dashed var(--color-primary-border);">
        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.5rem;">
          Subir Foto desde tu Equipo
        </h4>
        <form action="perfil.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0.85rem;">
          <input type="hidden" name="action" value="upload_avatar">
          
          <input 
            type="file" 
            name="avatar_file" 
            id="avatar_file_input" 
            accept="image/png, image/jpeg, image/webp, image/gif"
            required 
            class="form-input" 
            style="width: 100%; font-size: 0.8rem; padding: 0.5rem;"
            onchange="previewAvatarImage(this)"
          >
          
          <div id="avatar-preview-container" style="display: none; align-items: center; gap: 0.75rem; margin-top: 0.25rem;">
            <img id="avatar-preview-img" src="" alt="Vista previa" style="width: 3.5rem; height: 3.5rem; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-primary);">
            <span style="font-size: 0.78rem; color: var(--color-neutral-medium);">Vista previa lista para guardar</span>
          </div>

          <button type="submit" class="btn btn-primary btn-sm" style="justify-content: center;">
            ✓ Guardar Foto de Perfil
          </button>
        </form>
      </div>

      <!-- Opción B: Avatares Artesanales Temáticos -->
      <div>
        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">
          O elige un Amigurumi Coleccionable:
        </h4>
        <p style="font-size: 0.78rem; color: var(--color-neutral-medium); margin-bottom: 0.85rem;">
          Haz clic en cualquiera para ponértelo de avatar de inmediato.
        </p>

        <div class="avatar-preset-picker">
          <form action="perfil.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="preset_avatar">
            <input type="hidden" name="preset" value="preset:rana">
            <button type="submit" class="avatar-preset-btn <?= ($customer['avatar'] === 'preset:rana') ? 'active' : '' ?>">
              🐸 Ranita Cósmica
            </button>
          </form>

          <form action="perfil.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="preset_avatar">
            <input type="hidden" name="preset" value="preset:aranita">
            <button type="submit" class="avatar-preset-btn <?= ($customer['avatar'] === 'preset:aranita') ? 'active' : '' ?>">
              🕷️ Lulu Arañita
            </button>
          </form>

          <form action="perfil.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="preset_avatar">
            <input type="hidden" name="preset" value="preset:murcielago">
            <button type="submit" class="avatar-preset-btn <?= ($customer['avatar'] === 'preset:murcielago') ? 'active' : '' ?>">
              🦇 Murciélago Lunar
            </button>
          </form>

          <form action="perfil.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="preset_avatar">
            <input type="hidden" name="preset" value="preset:pulpo">
            <button type="submit" class="avatar-preset-btn <?= ($customer['avatar'] === 'preset:pulpo') ? 'active' : '' ?>">
              🐙 Pulpo de Apego
            </button>
          </form>

          <form action="perfil.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="preset_avatar">
            <input type="hidden" name="preset" value="preset:ovillo">
            <button type="submit" class="avatar-preset-btn <?= ($customer['avatar'] === 'preset:ovillo') ? 'active' : '' ?>">
              🧶 Ovillo Mágico
            </button>
          </form>

          <form action="perfil.php" method="POST" style="display: inline;">
            <input type="hidden" name="action" value="preset_avatar">
            <input type="hidden" name="preset" value="preset:abeja">
            <button type="submit" class="avatar-preset-btn <?= ($customer['avatar'] === 'preset:abeja') ? 'active' : '' ?>">
              🐝 Abejita Silvestre
            </button>
          </form>
        </div>

        <?php if (!empty($customer['avatar'])): ?>
          <div style="margin-top: 1.25rem; border-top: 1px dashed var(--color-neutral-border); padding-top: 0.85rem;">
            <form action="perfil.php" method="POST">
              <input type="hidden" name="action" value="remove_avatar">
              <button type="submit" class="btn btn-outlined btn-sm" style="font-size: 0.75rem; color: var(--color-neutral-medium);">
                ↺ Quitar imagen y usar mis iniciales
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <?php if (!empty($successMsg)): ?>
    <div style="background: var(--color-success-bg); border-left: 4px solid var(--color-success); color: var(--color-neutral-dark); padding: 1rem 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.9rem; font-weight: 600;">
      ✓ <?= h($successMsg) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div style="background: var(--color-danger-bg); border-left: 4px solid var(--color-danger); color: var(--color-neutral-dark); padding: 1rem 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; font-size: 0.9rem; font-weight: 600;">
      ⚠ <?= h($errorMsg) ?>
    </div>
  <?php endif; ?>

  <!-- Grid de Contenido Principal: Historial de Pedidos (Columna Izq) y Configuración / Envío (Columna Der) -->
  <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 2rem; align-items: flex-start;">

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
                  'revision' => ['label' => 'En Verificación', 'bg' => 'var(--color-warning-bg)', 'color' => 'var(--color-warning)'],
                  'preparando' => ['label' => 'En Tejido / Empaque', 'bg' => 'var(--color-primary-subtle)', 'color' => 'var(--color-primary)'],
                  'despachado' => ['label' => 'En Camino (Guía Activa)', 'bg' => 'var(--color-info-bg)', 'color' => 'var(--color-info)'],
                  'entregado' => ['label' => 'Entregado & Adoptado', 'bg' => 'var(--color-success-bg)', 'color' => 'var(--color-success)']
              ];
              $stInfo = $statusMap[$status] ?? ['label' => ucfirst($status), 'bg' => 'var(--color-neutral-bg)', 'color' => 'var(--color-neutral-medium)'];
            ?>
            <div class="checkout-card" style="padding: 1.5rem; border-radius: 1rem;">
              
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
                      <div style="width: 2.8rem; height: 2.8rem; border-radius: 0.5rem; background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🧶</div>
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

    <!-- COLUMNA 2: PREFERENCIAS DE TEMA Y DATOS DE ENTREGA -->
    <div style="display: flex; flex-direction: column; gap: 1.75rem;">
      
      <!-- WIDGET: PREFERENCIAS DE TEMA (CLARO / OSCURO) -->
      <div class="checkout-card" style="padding: 1.5rem; border-radius: 1rem;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">
          🎨 Modo de Visualización
        </h3>
        <p style="font-size: 0.8rem; color: var(--color-neutral-medium); margin-bottom: 1rem;">
          Elige entre el ambiente artesanal lila & crema o el modo noche cósmico.
        </p>

        <?php $currentTheme = $customer['theme_preference'] ?? 'light'; ?>
        <div class="theme-radio-group">
          <div class="theme-radio-card <?= ($currentTheme === 'light') ? 'active' : '' ?>" data-theme-val="light">
            <span style="font-size: 1.3rem;">☀️</span>
            <div>
              <div style="line-height: 1.2;">Modo Claro</div>
              <span style="font-size: 0.72rem; color: var(--color-neutral-light); font-weight: 400;">Lila & Crema</span>
            </div>
          </div>

          <div class="theme-radio-card <?= ($currentTheme === 'dark') ? 'active' : '' ?>" data-theme-val="dark">
            <span style="font-size: 1.3rem;">🌙</span>
            <div>
              <div style="line-height: 1.2;">Modo Oscuro</div>
              <span style="font-size: 0.72rem; color: var(--color-neutral-light); font-weight: 400;">Noche Cósmica</span>
            </div>
          </div>
        </div>
      </div>

      <!-- FORMULARIO: MIS DATOS DE ENTREGA -->
      <div class="checkout-card" style="padding: 1.75rem; border-radius: 1rem;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">
          ⚙️ Mis Datos de Entrega
        </h3>
        <p style="font-size: 0.8rem; color: var(--color-neutral-medium); margin-bottom: 1.25rem;">
          Estos datos se autocompletarán de forma segura en tus futuros checkouts para agilizar tus compras en Colombia.
        </p>

        <form action="perfil.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
          <input type="hidden" name="action" value="update_profile">

          <div>
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Nombre Completo *</label>
            <input type="text" name="name" required class="form-input" style="width: 100%;" value="<?= h($customer['name']) ?>">
          </div>

          <div>
            <label style="display: block; font-size: 0.78rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Correo Electrónico (No modificable)</label>
            <input type="email" disabled class="form-input" style="width: 100%;" value="<?= h($customer['email']) ?>">
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

<script>
  function toggleAvatarPanel() {
    const p = document.getElementById('avatar-customizer-panel');
    if (p) {
      p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
      if (p.style.display === 'block') {
        p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }
  }

  function previewAvatarImage(input) {
    const previewContainer = document.getElementById('avatar-preview-container');
    const previewImg = document.getElementById('avatar-preview-img');
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        previewContainer.style.display = 'flex';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
