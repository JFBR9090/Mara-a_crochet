<?php
declare(strict_types=1);

// auth.php - Registro e Inicio de Sesión de Clientes Maraña Crochet
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$baseUrl = getBaseUrl();
$rawRedirect = trim($_POST['redirect'] ?? $_GET['redirect'] ?? '');
$redirect = $baseUrl . '/perfil.php';

if (!empty($rawRedirect)) {
    if (str_starts_with($rawRedirect, '/') && !str_starts_with($rawRedirect, '//')) {
        $redirect = $rawRedirect;
    } elseif (str_starts_with($rawRedirect, $baseUrl)) {
        $redirect = $rawRedirect;
    }
}

$reason = trim($_GET['reason'] ?? $_POST['reason'] ?? '');

// Si ya tiene sesión iniciada, redirigir al destino solicitado
if (isCustomerLoggedIn()) {
    header('Location: ' . $redirect);
    exit();
}

$tab = $_GET['tab'] ?? 'login'; // 'login' | 'register'
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Por favor ingresa tu correo electrónico y tu contraseña.';
            $tab = 'login';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `customers` WHERE `email` = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $customer = $stmt->fetch();

            if ($customer && password_verify($password, $customer['password'])) {
                loginCustomer($customer);
                setFlash('success', '¡Bienvenida/o de nuevo, ' . htmlspecialchars($customer['name']) . '! 🧶✨', 'Tu sesión está activa.');
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = 'Correo o contraseña incorrectos. Por favor verifica tus credenciales.';
                $tab = 'login';
            }
        }
    } elseif ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $department = trim($_POST['department'] ?? 'Cundinamarca');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validaciones
        if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($password)) {
            $error = 'Todos los campos marcados con (*) son obligatorios.';
            $tab = 'register';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor ingresa un correo electrónico válido.';
            $tab = 'register';
        } elseif (strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
            $tab = 'register';
        } elseif ($password !== $password_confirm) {
            $error = 'Las contraseñas no coinciden.';
            $tab = 'register';
        } else {
            // Verificar si el correo ya existe
            $check = $pdo->prepare("SELECT id FROM `customers` WHERE `email` = :email LIMIT 1");
            $check->execute([':email' => $email]);
            if ($check->fetch()) {
                $error = 'Ya existe una cuenta registrada con este correo electrónico. Por favor inicia sesión.';
                $tab = 'login';
            } else {
                // Registrar nuevo cliente
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $insert = $pdo->prepare("
                    INSERT INTO `customers` (`name`, `email`, `phone`, `address`, `city`, `department`, `password`)
                    VALUES (:name, :email, :phone, :address, :city, :department, :password)
                ");
                $insert->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':address' => $address,
                    ':city' => $city,
                    ':department' => $department,
                    ':password' => $hash,
                ]);
                $newId = (int)$pdo->lastInsertId();

                // Asociar órdenes previas si había hecho pedidos con este correo
                $updOrders = $pdo->prepare("UPDATE `orders` SET `customerId` = :cid WHERE `customerEmail` = :email AND `customerId` IS NULL");
                $updOrders->execute([':cid' => $newId, ':email' => $email]);

                $newCustomer = [
                    'id' => $newId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'city' => $city,
                    'department' => $department,
                    'avatar' => null,
                    'theme_preference' => 'light',
                ];

                loginCustomer($newCustomer);
                setFlash('success', '¡Cuenta creada con éxito! Bienvenida/o a la familia Maraña. 💜🧶', 'Tus datos quedaron guardados para tus próximas adopciones.');
                header('Location: ' . $redirect);
                exit();
            }
        }
    }
}

$pageTitle = 'Mi Cuenta de Cliente | Maraña Crochet';
$pageDesc = 'Accede a tu cuenta de cliente en Maraña Crochet para ver tus pedidos, certificados de adopción y actualizar tu dirección.';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 680px; margin: 3.5rem auto 5rem; padding: 0 1rem;">
  
  <div style="text-align: center; margin-bottom: 2rem;">
    <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Maraña Crochet" style="width: 4.5rem; height: 4.5rem; border-radius: 50%; border: 3px solid var(--color-primary); margin-bottom: 0.75rem; box-shadow: 0 4px 14px rgba(123,44,191,0.25);">
    <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Portal de Clientes Maraña</h1>
    <p style="font-size: 0.92rem; color: var(--color-neutral-medium);">
      Gestiona tus amigurumis, sigue el estado de tus envíos y consulta tus comprobantes oficiales.
    </p>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background: #fff0f0; border-left: 4px solid #e63946; color: #b71c1c; padding: 0.85rem 1.15rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 500;">
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($reason === 'buy_required'): ?>
    <div style="background: linear-gradient(135deg, #FAF5FF 0%, #F3E8FF 100%); border: 1.5px solid var(--color-primary-border); border-left: 5px solid var(--color-primary); padding: 1.25rem 1.45rem; border-radius: var(--radius-md); margin-bottom: 1.75rem; box-shadow: 0 4px 15px rgba(123,44,191,0.08);">
      <div style="display: flex; gap: 0.95rem; align-items: flex-start;">
        <span style="font-size: 2rem; line-height: 1;">🧶</span>
        <div>
          <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--color-neutral-dark); margin: 0 0 0.35rem;">
            ¡Paso Obligatorio para Completar tu Compra!
          </h3>
          <p style="font-size: 0.88rem; color: var(--color-neutral-medium); margin: 0; line-height: 1.45;">
            Has ingresado como invitado y seleccionado amigurumis. Para finalizar tu compra en Maraña Crochet es <strong>obligatorio iniciar sesión o registrarte</strong>. Así podremos asignarte tu <strong>Certificado Oficial de Adopción numerado</strong> y despachar tu pedido a tu dirección en Colombia.
          </p>
          <div style="margin-top: 0.5rem; font-size: 0.82rem; font-weight: 700; color: var(--color-primary);">
            ✓ Tu canasta de compras quedará intacta y pasarás directo al pago tras ingresar.
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="checkout-card" style="padding: 2rem; border-radius: 1.25rem;">
    <!-- Selector de Pestañas -->
    <div style="display: flex; gap: 0.5rem; background: var(--color-neutral-bg); padding: 0.35rem; border-radius: var(--radius-md); margin-bottom: 2rem;">
      <a href="?tab=login&redirect=<?= urlencode($redirect) ?>&reason=<?= urlencode($reason) ?>" style="flex: 1; text-align: center; padding: 0.65rem 1rem; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: all 0.2s; <?= $tab === 'login' ? 'background: #fff; color: var(--color-primary); box-shadow: 0 2px 6px rgba(0,0,0,0.06);' : 'color: var(--color-neutral-medium);' ?>">
        Iniciar Sesión
      </a>
      <a href="?tab=register&redirect=<?= urlencode($redirect) ?>&reason=<?= urlencode($reason) ?>" style="flex: 1; text-align: center; padding: 0.65rem 1rem; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: all 0.2s; <?= $tab === 'register' ? 'background: #fff; color: var(--color-primary); box-shadow: 0 2px 6px rgba(0,0,0,0.06);' : 'color: var(--color-neutral-medium);' ?>">
        Crear Cuenta Nueva
      </a>
    </div>

    <!-- PESTAÑA 1: INICIAR SESIÓN -->
    <?php if ($tab === 'login'): ?>
      <form action="auth.php?tab=login" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
        <input type="hidden" name="reason" value="<?= h($reason) ?>">

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Correo Electrónico *</label>
          <input type="email" name="email" required placeholder="ejemplo@gmail.com" class="form-input" style="width: 100%;" value="<?= h($_POST['email'] ?? '') ?>" autocomplete="email">
        </div>

        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
            <label style="font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark);">Contraseña *</label>
            <span style="font-size: 0.75rem; color: var(--color-primary);">Demo: marana123</span>
          </div>
          <input type="password" name="password" required placeholder="••••••••" class="form-input" style="width: 100%;" autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem; justify-content: center;">
          Ingresar a Mi Cuenta
        </button>

        <div style="text-align: center; font-size: 0.85rem; color: var(--color-neutral-medium); margin-top: 0.5rem;">
          ¿No tienes una cuenta aún? 
          <a href="?tab=register&redirect=<?= urlencode($redirect) ?>&reason=<?= urlencode($reason) ?>" style="color: var(--color-primary); font-weight: 700; text-decoration: none;">Regístrate aquí</a>
        </div>
      </form>

    <!-- PESTAÑA 2: REGISTRO DE NUEVO CLIENTE -->
    <?php else: ?>
      <form action="auth.php?tab=register" method="POST" style="display: flex; flex-direction: column; gap: 1.15rem;">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
        <input type="hidden" name="reason" value="<?= h($reason) ?>">

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Nombre Completo *</label>
          <input type="text" name="name" required placeholder="Ej: Valentina Castro" class="form-input" style="width: 100%;" value="<?= h($_POST['name'] ?? '') ?>" autocomplete="name">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Correo Electrónico *</label>
            <input type="email" name="email" required placeholder="tu@correo.com" class="form-input" style="width: 100%;" value="<?= h($_POST['email'] ?? '') ?>" autocomplete="email">
          </div>
          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Teléfono / WhatsApp *</label>
            <input type="tel" name="phone" required placeholder="312 456 7890" class="form-input" style="width: 100%;" value="<?= h($_POST['phone'] ?? '') ?>" autocomplete="tel">
          </div>
        </div>

        <div>
          <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Dirección de Entrega en Colombia *</label>
          <input type="text" name="address" required placeholder="Calle / Carrera #, Apto, Barrio" class="form-input" style="width: 100%;" value="<?= h($_POST['address'] ?? '') ?>" autocomplete="street-address">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Ciudad / Municipio *</label>
            <input type="text" name="city" required placeholder="Ej: Neiva, Bogotá, Medellín" class="form-input" style="width: 100%;" value="<?= h($_POST['city'] ?? '') ?>">
          </div>
          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Departamento *</label>
            <select name="department" class="form-select" style="width: 100%;">
              <?php
              $deptos = [
                  'Huila', 'Bogotá D.C.', 'Antioquia', 'Valle del Cauca', 'Cundinamarca',
                  'Atlántico', 'Santander', 'Bolívar', 'Caldas', 'Risaralda', 'Quindío',
                  'Boyacá', 'Tolima', 'Nariño', 'Cauca', 'Cesar', 'Córdoba', 'Meta'
              ];
              $selectedDep = $_POST['department'] ?? 'Huila';
              foreach ($deptos as $d): ?>
                <option value="<?= h($d) ?>" <?= $selectedDep === $d ? 'selected' : '' ?>><?= h($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Contraseña *</label>
            <input type="password" name="password" required placeholder="Mínimo 6 caracteres" class="form-input" style="width: 100%;" autocomplete="new-password">
          </div>
          <div>
            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">Confirmar Contraseña *</label>
            <input type="password" name="password_confirm" required placeholder="Repite tu contraseña" class="form-input" style="width: 100%;" autocomplete="new-password">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.75rem; justify-content: center;">
          Crear Mi Cuenta & Guardar Datos
        </button>

        <div style="text-align: center; font-size: 0.85rem; color: var(--color-neutral-medium); margin-top: 0.5rem;">
          ¿Ya tienes una cuenta registrada? 
          <a href="?tab=login&redirect=<?= urlencode($redirect) ?>&reason=<?= urlencode($reason) ?>" style="color: var(--color-primary); font-weight: 700; text-decoration: none;">Inicia sesión aquí</a>
        </div>
      </form>
    <?php endif; ?>

  </div>

  <!-- Ventajas de Tener Cuenta -->
  <div style="margin-top: 2rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
    <div style="padding: 1rem; background: #fff; border-radius: var(--radius-md); border: 1px solid var(--color-neutral-border);">
      <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📦</div>
      <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-neutral-dark);">Historial de Adopciones</div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">Acceso permanente a tus órdenes y certificados</div>
    </div>
    <div style="padding: 1rem; background: #fff; border-radius: var(--radius-md); border: 1px solid var(--color-neutral-border);">
      <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">⚡</div>
      <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-neutral-dark);">Checkout Rápido</div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">Dirección y teléfono autocompletados al comprar</div>
    </div>
    <div style="padding: 1rem; background: #fff; border-radius: var(--radius-md); border: 1px solid var(--color-neutral-border);">
      <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🇨🇴</div>
      <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-neutral-dark);">Soporte Neiva, Huila</div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-medium);">Atención directa de nuestras artesanas por WhatsApp</div>
    </div>
  </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
