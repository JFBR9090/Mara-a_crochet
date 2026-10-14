<?php
// includes/navbar.php - Barra de navegación con iconos de inicio, búsqueda y usuario
$baseUrl = getBaseUrl();
$cartCount = getCartCount();
$favCount = count(getFavorites());
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$searchQuery = $_GET['q'] ?? '';
?>

<!-- Announcement Bar -->
<aside class="announcement-bar">
  <span>✨ Nueva tanda de Criaturas Cósmicas en aguja. Cada amigurumi incluye Certificado de Adopción numerado.</span>
  <a href="<?= $baseUrl ?>/index.php#catalogo">Ver Catálogo &rarr;</a>
</aside>

<!-- Main Navigation -->
<header class="site-header">
  <div class="container nav-container">
    
    <!-- Logo & Brand -->
    <a href="<?= $baseUrl ?>/index.php" class="brand-link" title="Maraña Crochet Inicio">
      <div class="brand-mark">M</div>
      <div class="brand-title">
        <span>Maraña</span>
        <span class="brand-subtitle">Crochet Artesanal</span>
      </div>
    </a>

    <!-- Minimalist Search Bar with Magnifying Glass Icon -->
    <div class="nav-search-col">
      <form action="<?= $baseUrl ?>/index.php" method="GET" class="search-wrapper">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input 
          type="text" 
          name="q" 
          value="<?= h($searchQuery) ?>" 
          placeholder="Buscar arañita, murciélago, pulpo..." 
          class="search-input"
          autocomplete="off"
        >
      </form>
    </div>

    <!-- Actions (Home, Backoffice/User, Favorites, Cart) -->
    <nav class="nav-actions">
      <!-- Icono de Inicio (Home) -->
      <a href="<?= $baseUrl ?>/index.php" class="nav-link-btn <?= ($currentScript === 'index.php') ? 'active' : '' ?>" title="Ir al Inicio">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span class="nav-link-text">Inicio</span>
      </a>

      <!-- Icono de Usuario / Backoffice Taller -->
      <a href="<?= $baseUrl ?>/admin/orders.php" class="nav-link-btn <?= (str_contains($_SERVER['REQUEST_URI'], '/admin/')) ? 'active' : '' ?>" title="Panel del Taller / Administración">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span class="nav-link-text">Taller Admin</span>
      </a>

      <!-- Favoritos -->
      <a href="<?= $baseUrl ?>/index.php?filter=favoritos" class="nav-badge-btn" title="Ver Favoritos">
        <svg style="width: 1.25rem; height: 1.25rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
        <span id="fav-counter" class="badge-count" style="display: <?= $favCount > 0 ? 'flex' : 'none' ?>;"><?= $favCount ?></span>
      </a>

      <!-- Carrito Drawer Trigger -->
      <button type="button" id="open-cart-btn" class="nav-badge-btn" title="Abrir Carrito">
        <svg style="width: 1.25rem; height: 1.25rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        <span id="cart-counter" class="badge-count"><?= $cartCount ?></span>
      </button>
    </nav>

  </div>
</header>
