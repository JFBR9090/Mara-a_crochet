<?php
// admin/login.php - Inicio de sesión para administradores del Taller Maraña
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

header('Content-Type: text/html; charset=UTF-8');

$baseUrl = getBaseUrl();

// Si ya tiene sesión iniciada, redirigir al panel
if (isAdminLoggedIn()) {
    header('Location: ' . $baseUrl . '/admin/orders.php');
    exit();
}

$error = '';
$usernameVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameVal = trim($_POST['username'] ?? '');
    $passwordVal = trim($_POST['password'] ?? '');

    if (empty($usernameVal) || empty($passwordVal)) {
        $error = 'Por favor ingresa tu usuario y contraseña.';
    } else {
        if (adminLogin($usernameVal, $passwordVal)) {
            $redirect = $_SESSION['admin_redirect'] ?? ($baseUrl . '/admin/orders.php');
            unset($_SESSION['admin_redirect']);
            setFlash('success', '¡Bienvenida al Taller!', 'Sesión de administración iniciada con éxito.');
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Credenciales no válidas. Verifica el usuario y la contraseña e intenta de nuevo.';
        }
    }
}

$pageTitle = 'Acceso al Taller | Maraña Crochet';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem; max-width: 460px;">
  
  <div class="card-panel" style="padding: 2.25rem 2rem; border-top: 4px solid var(--color-primary); box-shadow: 0 10px 25px -5px rgba(123, 44, 191, 0.12); border-radius: var(--radius-md);">
    
    <!-- Encabezado con Icono -->
    <div style="text-align: center; margin-bottom: 2rem;">
      <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Logo Maraña Crochet" style="width: 5.5rem; height: 5.5rem; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary); box-shadow: 0 6px 20px rgba(123, 44, 191, 0.28); margin-bottom: 1rem;">
      <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.35rem;">
        Acceso al Taller
      </h1>
      <p style="font-size: 0.88rem; color: var(--color-neutral-medium); line-height: 1.4;">
        Panel de Administración y Control de Pedidos<br>
        <span style="display: inline-block; margin-top: 0.25rem; font-size: 0.8rem; color: var(--color-primary); font-weight: 600;">
          📍 Taller Maraña · Neiva, Huila, Colombia
        </span>
      </p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: var(--color-error-bg); color: var(--color-error); border: 1px solid var(--color-error-border); padding: 0.75rem 1rem; border-radius: var(--radius-sm); font-size: 0.85rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
        <svg style="width: 1.15rem; height: 1.15rem; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span><?= h($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Formulario de Acceso -->
    <form action="<?= $baseUrl ?>/admin/login.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
      
      <div>
        <label for="username" style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark); margin-bottom: 0.4rem;">
          Usuario o Correo Administrativo
        </label>
        <div style="position: relative;">
          <input 
            type="text" 
            id="username" 
            name="username" 
            value="<?= h($usernameVal) ?>" 
            placeholder="ej: admin o taller@maranacrochet.co" 
            required 
            autofocus
            class="search-input"
            style="width: 100%; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); padding: 0.75rem 0.85rem; font-size: 0.92rem;"
          >
        </div>
      </div>

      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
          <label for="password" style="font-size: 0.82rem; font-weight: 700; color: var(--color-neutral-dark);">
            Contraseña
          </label>
        </div>
        <div style="position: relative;">
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="••••••••" 
            required 
            class="search-input"
            style="width: 100%; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); padding: 0.75rem 0.85rem; font-size: 0.92rem;"
          >
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; justify-content: center; font-size: 0.95rem; margin-top: 0.5rem; font-weight: 700;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1.15rem; height: 1.15rem;">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
          <polyline points="10 17 15 12 10 7"></polyline>
          <line x1="15" y1="12" x2="3" y2="12"></line>
        </svg>
        Iniciar Sesión en el Taller
      </button>

    </form>

    <!-- Credenciales Sugeridas / Ayuda -->
    <div style="margin-top: 1.75rem; padding: 0.85rem 1rem; background: var(--color-primary-bg); border: 1px dashed var(--color-primary-border); border-radius: var(--radius-sm); font-size: 0.78rem; color: var(--color-neutral-medium);">
      <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.4rem;">
        <svg style="width: 0.9rem; height: 0.9rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        Credenciales de Acceso al Taller:
      </div>
      <div><strong>Usuario:</strong> <code>admin</code></div>
      <div><strong>Contraseña:</strong> <code>admin123</code> (o <code>admin</code>)</div>
    </div>

    <!-- Enlace de regreso a la tienda -->
    <div style="text-align: center; margin-top: 1.5rem;">
      <a href="<?= $baseUrl ?>/index.php" style="font-size: 0.85rem; color: var(--color-neutral-medium); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
        &larr; Volver a la Tienda de Maraña
      </a>
    </div>

  </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

