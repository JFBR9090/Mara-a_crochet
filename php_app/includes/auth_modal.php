<?php
// includes/auth_modal.php - Modal / Pestaña interactiva de Acceso y Bienvenida (Cliente, Admin e Invitado)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/admin_auth.php';

$baseUrl = getBaseUrl();
$isCustLogged = isCustomerLoggedIn();
$isAdminLogged = isAdminLoggedIn();
$currentUri = $_SERVER['REQUEST_URI'] ?? ($baseUrl . '/index.php');
?>

<!-- Contenedor del Modal de Acceso / Pestaña de Bienvenida -->
<div id="marana-auth-modal-overlay" class="auth-modal-overlay" aria-hidden="true" style="display: none;">
  <div class="auth-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
    
    <!-- Botón de Cerrar / Continuar como Invitado -->
    <button type="button" class="auth-modal-close" id="btn-close-auth-modal" aria-label="Cerrar pestaña" title="Continuar explorando como invitado">
      &times;
    </button>

    <!-- Cabecera de la Marca Maraña -->
    <div class="auth-modal-header">
      <div class="auth-modal-brand">
        <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Logo Maraña Crochet" class="auth-modal-logo">
        <div>
          <h2 id="auth-modal-title" class="auth-modal-title">Maraña Crochet</h2>
          <p class="auth-modal-subtitle">Amigurumis con ternura cósmica · Neiva, Huila 🇨🇴</p>
        </div>
      </div>
      <p class="auth-modal-lead">
        Elige cómo deseas ingresar al taller y catálogo artesanal:
      </p>
    </div>

    <!-- PESTAÑA PRINCIPAL SUPERIOR (SWITCHER ADMIN vs USUARIO) -->
    <div class="auth-role-tabs" role="tablist" aria-label="Seleccionar rol de ingreso">
      <button 
        type="button" 
        class="auth-role-tab active" 
        id="role-tab-user" 
        role="tab" 
        aria-selected="true" 
        aria-controls="role-panel-user"
        onclick="switchAuthRole('user')"
      >
        <span class="role-tab-icon">👤</span>
        <span class="role-tab-label">
          <strong>Usuario / Cliente</strong>
          <small>Amantes del crochet & compras</small>
        </span>
      </button>

      <button 
        type="button" 
        class="auth-role-tab" 
        id="role-tab-admin" 
        role="tab" 
        aria-selected="false" 
        aria-controls="role-panel-admin"
        onclick="switchAuthRole('admin')"
      >
        <span class="role-tab-icon">🛡️</span>
        <span class="role-tab-label">
          <strong>Administrador</strong>
          <small>Panel del Taller Maraña</small>
        </span>
      </button>
    </div>

    <!-- ============================================================= -->
    <!-- PANEL 1: CLIENTE / USUARIO                                     -->
    <!-- ============================================================= -->
    <div id="role-panel-user" class="auth-role-panel active" role="tabpanel" aria-labelledby="role-tab-user">
      
      <!-- Sub-pestañas: Iniciar Sesión / Registrarse -->
      <div class="auth-subtabs" role="tablist">
        <button 
          type="button" 
          class="auth-subtab active" 
          id="subtab-login" 
          onclick="switchUserSubTab('login')"
        >
          Iniciar Sesión
        </button>
        <button 
          type="button" 
          class="auth-subtab" 
          id="subtab-register" 
          onclick="switchUserSubTab('register')"
        >
          Registrarse (Crear Cuenta)
        </button>
      </div>

      <!-- FORMULARIO 1A: INICIAR SESIÓN CLIENTE -->
      <div id="user-subpanel-login" class="auth-subpanel active">
        <form action="<?= $baseUrl ?>/auth.php" method="POST" class="auth-modal-form">
          <input type="hidden" name="action" value="login">
          <input type="hidden" name="redirect" value="<?= h($currentUri) ?>">

          <div class="auth-form-group">
            <label for="modal-login-email" class="auth-label">Correo Electrónico</label>
            <input 
              type="email" 
              id="modal-login-email" 
              name="email" 
              class="auth-input" 
              placeholder="tu-correo@ejemplo.com" 
              required 
              autocomplete="email"
            >
          </div>

          <div class="auth-form-group">
            <div class="auth-label-row">
              <label for="modal-login-password" class="auth-label">Contraseña</label>
              <span class="auth-hint">Demo: marana123</span>
            </div>
            <input 
              type="password" 
              id="modal-login-password" 
              name="password" 
              class="auth-input" 
              placeholder="••••••••" 
              required 
              autocomplete="current-password"
            >
          </div>

          <button type="submit" class="btn btn-primary btn-lg auth-submit-btn">
            <svg style="width: 1.15rem; height: 1.15rem; margin-right: 0.4rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10 17 15 12 10 7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            Ingresar a Mi Cuenta
          </button>
        </form>
      </div>

      <!-- FORMULARIO 1B: REGISTRARSE CLIENTE -->
      <div id="user-subpanel-register" class="auth-subpanel" style="display: none;">
        <form action="<?= $baseUrl ?>/auth.php" method="POST" class="auth-modal-form">
          <input type="hidden" name="action" value="register">
          <input type="hidden" name="redirect" value="<?= h($currentUri) ?>">

          <div class="auth-form-group">
            <label for="modal-reg-name" class="auth-label">Nombre Completo *</label>
            <input 
              type="text" 
              id="modal-reg-name" 
              name="name" 
              class="auth-input" 
              placeholder="Ej: Laura Castro" 
              required 
              autocomplete="name"
            >
          </div>

          <div class="auth-form-row">
            <div class="auth-form-group">
              <label for="modal-reg-email" class="auth-label">Correo Electrónico *</label>
              <input 
                type="email" 
                id="modal-reg-email" 
                name="email" 
                class="auth-input" 
                placeholder="tu@correo.com" 
                required 
                autocomplete="email"
              >
            </div>
            <div class="auth-form-group">
              <label for="modal-reg-phone" class="auth-label">WhatsApp / Teléfono *</label>
              <input 
                type="tel" 
                id="modal-reg-phone" 
                name="phone" 
                class="auth-input" 
                placeholder="312 456 7890" 
                required 
                autocomplete="tel"
              >
            </div>
          </div>

          <div class="auth-form-group">
            <label for="modal-reg-address" class="auth-label">Dirección de Entrega en Colombia *</label>
            <input 
              type="text" 
              id="modal-reg-address" 
              name="address" 
              class="auth-input" 
              placeholder="Calle / Carrera #, Barrio, Apto" 
              required 
              autocomplete="street-address"
            >
          </div>

          <div class="auth-form-row">
            <div class="auth-form-group">
              <label for="modal-reg-city" class="auth-label">Ciudad / Municipio *</label>
              <input 
                type="text" 
                id="modal-reg-city" 
                name="city" 
                class="auth-input" 
                placeholder="Ej: Neiva, Bogotá" 
                required
              >
            </div>
            <div class="auth-form-group">
              <label for="modal-reg-dept" class="auth-label">Departamento *</label>
              <select id="modal-reg-dept" name="department" class="auth-select">
                <option value="Huila" selected>Huila</option>
                <option value="Bogotá D.C.">Bogotá D.C.</option>
                <option value="Antioquia">Antioquia</option>
                <option value="Valle del Cauca">Valle del Cauca</option>
                <option value="Cundinamarca">Cundinamarca</option>
                <option value="Santander">Santander</option>
                <option value="Atlántico">Atlántico</option>
                <option value="Bolívar">Bolívar</option>
                <option value="Tolima">Tolima</option>
                <option value="Caldas">Caldas</option>
                <option value="Risaralda">Risaralda</option>
                <option value="Quindío">Quindío</option>
                <option value="Meta">Meta</option>
                <option value="Nariño">Nariño</option>
              </select>
            </div>
          </div>

          <div class="auth-form-row">
            <div class="auth-form-group">
              <label for="modal-reg-password" class="auth-label">Contraseña (Mín. 6) *</label>
              <input 
                type="password" 
                id="modal-reg-password" 
                name="password" 
                class="auth-input" 
                placeholder="••••••••" 
                required 
                autocomplete="new-password"
              >
            </div>
            <div class="auth-form-group">
              <label for="modal-reg-passconf" class="auth-label">Confirmar Contraseña *</label>
              <input 
                type="password" 
                id="modal-reg-passconf" 
                name="password_confirm" 
                class="auth-input" 
                placeholder="••••••••" 
                required 
                autocomplete="new-password"
              >
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg auth-submit-btn">
            <svg style="width: 1.15rem; height: 1.15rem; margin-right: 0.4rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="8.5" cy="7" r="4"></circle>
              <line x1="20" y1="8" x2="20" y2="14"></line>
              <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            Crear Mi Cuenta & Comenzar
          </button>
        </form>
      </div>

      <!-- SECCIÓN DESTACADA: CONTINUAR COMO INVITADO CON CONDICIÓN OBLIGATORIA -->
      <div class="auth-guest-box">
        <div class="guest-box-notice">
          <span class="guest-notice-icon">🛍️</span>
          <div>
            <strong>¿Prefieres explorar primero como invitado?</strong>
            <p>
              Puedes recorrer el catálogo libremente y armar tu canasta de amigurumis. 
              <span class="guest-warning-highlight">
                ⚠️ Para realizar o finalizar una compra será <u>obligatorio</u> iniciar sesión o registrarte.
              </span>
            </p>
          </div>
        </div>

        <button 
          type="button" 
          class="btn btn-outlined auth-guest-btn" 
          id="btn-continue-guest"
          onclick="continueAsGuest()"
        >
          Continuar como Invitado y Explorar Tienda &rarr;
        </button>
      </div>

    </div>

    <!-- ============================================================= -->
    <!-- PANEL 2: ADMINISTRADOR DEL TALLER                              -->
    <!-- ============================================================= -->
    <div id="role-panel-admin" class="auth-role-panel" style="display: none;" role="tabpanel" aria-labelledby="role-tab-admin">
      
      <div class="admin-badge-banner">
        <div class="admin-badge-icon">🔒</div>
        <div>
          <strong>Panel Interno de Administración · Taller Maraña</strong>
          <p>Control de pedidos en tiempo real, inventario de lanas, amigurumis y auditoría bancaria.</p>
        </div>
      </div>

      <form action="<?= $baseUrl ?>/admin/login.php" method="POST" class="auth-modal-form">
        <div class="auth-form-group">
          <label for="modal-admin-username" class="auth-label">Usuario o Correo Administrativo</label>
          <input 
            type="text" 
            id="modal-admin-username" 
            name="username" 
            class="auth-input" 
            placeholder="admin o taller@maranacrochet.co" 
            required 
            autocomplete="username"
          >
        </div>

        <div class="auth-form-group">
          <div class="auth-label-row">
            <label for="modal-admin-password" class="auth-label">Contraseña de Administrador</label>
            <span class="auth-hint">Demo: admin123</span>
          </div>
          <input 
            type="password" 
            id="modal-admin-password" 
            name="password" 
            class="auth-input" 
            placeholder="••••••••" 
            required 
            autocomplete="current-password"
          >
        </div>

        <button type="submit" class="btn btn-inverted btn-lg auth-submit-btn" style="background: var(--color-neutral-dark); color: #fff; border: 1.5px solid var(--color-primary-light);">
          <svg style="width: 1.15rem; height: 1.15rem; margin-right: 0.4rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          Ingresar al Panel del Taller &rarr;
        </button>
      </form>

      <div style="margin-top: 1.25rem; text-align: center;">
        <button type="button" class="btn btn-outlined btn-sm" onclick="switchAuthRole('user')" style="font-size: 0.8rem;">
          &larr; Volver a Vista de Cliente
        </button>
      </div>

    </div>

    <!-- Pie del Modal -->
    <div class="auth-modal-footer">
      <span>🧶 Maraña Crochet · Neiva, Huila (Colombia)</span>
      <span>🔒 Conexión segura & Datos protegidos</span>
    </div>

  </div>
</div>
