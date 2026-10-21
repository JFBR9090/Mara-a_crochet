<?php
// index.php - Tienda principal y catálogo de amigurumis
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Maraña Crochet | Amigurumis Artesanales & Apego Cósmico';

// Parámetros de filtrado y búsqueda
$searchQuery = trim($_GET['q'] ?? '');
$categoryFilter = $_GET['category'] ?? 'all';
$availFilter = $_GET['availability'] ?? 'all';
$sortOrder = $_GET['sort'] ?? 'populares';
$onlyFavorites = ($_GET['filter'] ?? '') === 'favoritos';
$favorites = getFavorites();

// Construcción de consulta dinámica segura con PDO
$sql = "SELECT * FROM `products` WHERE 1=1";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (`name` LIKE :q OR `description` LIKE :q OR `tagline` LIKE :q OR `categoryLabel` LIKE :q)";
    $params[':q'] = "%{$searchQuery}%";
}

if ($categoryFilter !== 'all') {
    $sql .= " AND `category` = :cat";
    $params[':cat'] = $categoryFilter;
}

if ($availFilter === 'in-stock') {
    $sql .= " AND (`status` = 'in-stock' OR `status` = 'low-stock')";
} elseif ($availFilter === 'preorder') {
    $sql .= " AND `status` = 'preorder'";
} elseif ($availFilter === 'soldout') {
    $sql .= " AND `status` = 'soldout'";
}

if ($onlyFavorites && !empty($favorites)) {
    $inPlaceholders = implode(',', array_fill(0, count($favorites), '?'));
    // Como mezclamos parámetros nombrados y posicionales, ejecutamos orden simple
    $sql .= " AND `id` IN ('" . implode("','", array_map('addslashes', $favorites)) . "')";
}

// Ordenamiento
switch ($sortOrder) {
    case 'precio-asc':
        $sql .= " ORDER BY `price` ASC";
        break;
    case 'precio-desc':
        $sql .= " ORDER BY `price` DESC";
        break;
    case 'recientes':
        $sql .= " ORDER BY `craftHours` DESC";
        break;
    default:
        $sql .= " ORDER BY `rating` DESC, `reviewCount` DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Producto destacado para el Hero
$featuredStmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = 'lulu-aranita' LIMIT 1");
$featuredStmt->execute();
$featuredProduct = $featuredStmt->fetch() ?: ($products[0] ?? null);

$featuredImages = !empty($featuredProduct['images']) ? json_decode($featuredProduct['images'], true) : [];
$featuredImgUrl = $featuredImages[0]['url'] ?? '';

require __DIR__ . '/includes/header.php';
?>

<!-- Hero Section Minimalista -->
<section class="hero-section">
  <div class="container hero-grid">
    <div>
      <div class="hero-pill">
        <span class="hero-pill-dot"></span>
        <span>📍 Taller en Neiva, Huila · 🇨🇴 Solo Envíos Nacionales en Colombia</span>
      </div>
      <h1 class="hero-title">
        Criaturas tejidas con paciencia,<br>
        <span>ternura cósmica</span> y dedicación.
      </h1>
      <p class="hero-lead">
        Cada amigurumi nace en nuestro taller de Neiva, Huila, tejido a mano con hilados de algodón peinado hipoalergénico, aromatizado con flores de lavanda y despachado con Comprobante Oficial de Pago y Certificado de Adopción numerado.
      </p>
      <div class="hero-buttons">
        <a href="#catalogo" class="btn btn-primary btn-lg">Explorar Catálogo</a>
        <a href="<?= $baseUrl ?>/product.php?id=<?= h($featuredProduct['id'] ?? '') ?>" class="btn btn-outlined btn-lg">Conocer a Lulú Cósmica</a>
      </div>
    </div>

    <?php if ($featuredProduct): ?>
      <div>
        <div class="hero-card">
          <span class="hero-card-badge"><?= h($featuredProduct['discountBadge'] ?? 'Edición Limitada') ?></span>
          <img src="<?= h($featuredImgUrl) ?>" alt="<?= h($featuredProduct['name']) ?>" class="hero-card-img">
          <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
              <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--color-primary); font-weight: 700; margin-bottom: 0.2rem;">
                Pieza Destacada
              </div>
              <h3 style="margin-bottom: 0.25rem;"><?= h($featuredProduct['name']) ?></h3>
              <div class="product-rating">
                <svg class="star-icon" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                <span><?= number_format($featuredProduct['rating'], 1) ?> (<?= (int)$featuredProduct['reviewCount'] ?> reseñas)</span>
              </div>
            </div>
            <div style="text-align: right;">
              <div class="current-price" style="font-size: 1.35rem;"><?= formatCLP($featuredProduct['price']) ?></div>
              <button type="button" class="btn btn-primary btn-sm" onclick="addToCartAjax('<?= h($featuredProduct['id']) ?>', 1)" style="margin-top: 0.4rem;">
                Adoptar
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Catálogo y Filtros -->
<section id="catalogo" class="container" style="padding-top: 2rem;">
  
  <!-- Barra de Filtros Minimalista -->
  <div class="filters-bar">
    <div class="category-pills">
      <a href="<?= $baseUrl ?>/index.php#catalogo" class="cat-pill <?= ($categoryFilter === 'all' && !$onlyFavorites) ? 'active' : '' ?>">Todas las Criaturas</a>
      <a href="<?= $baseUrl ?>/index.php?category=espacio#catalogo" class="cat-pill <?= ($categoryFilter === 'espacio') ? 'active' : '' ?>">Espacio & Estrellas</a>
      <a href="<?= $baseUrl ?>/index.php?category=bosque#catalogo" class="cat-pill <?= ($categoryFilter === 'bosque') ? 'active' : '' ?>">Bosque Encantado</a>
      <a href="<?= $baseUrl ?>/index.php?category=bebes#catalogo" class="cat-pill <?= ($categoryFilter === 'bebes') ? 'active' : '' ?>">Apego Bebés</a>
      <a href="<?= $baseUrl ?>/index.php?category=nuevos#catalogo" class="cat-pill <?= ($categoryFilter === 'nuevos') ? 'active' : '' ?>">Nuevas Creaciones</a>
      <a href="<?= $baseUrl ?>/index.php?filter=favoritos#catalogo" class="cat-pill <?= $onlyFavorites ? 'active' : '' ?>">Favoritos (<?= count($favorites) ?>)</a>
    </div>

    <!-- Ordenamiento -->
    <form action="<?= $baseUrl ?>/index.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
      <?php if ($categoryFilter !== 'all'): ?>
        <input type="hidden" name="category" value="<?= h($categoryFilter) ?>">
      <?php endif; ?>
      <?php if (!empty($searchQuery)): ?>
        <input type="hidden" name="q" value="<?= h($searchQuery) ?>">
      <?php endif; ?>
      <label for="sort" class="text-muted" style="font-size: 0.8rem; font-weight: 600;">Ordenar:</label>
      <select name="sort" id="sort" class="sort-select" onchange="this.form.submit()">
        <option value="populares" <?= $sortOrder === 'populares' ? 'selected' : '' ?>>Más Populares</option>
        <option value="precio-asc" <?= $sortOrder === 'precio-asc' ? 'selected' : '' ?>>Menor Precio</option>
        <option value="precio-desc" <?= $sortOrder === 'precio-desc' ? 'selected' : '' ?>>Mayor Precio</option>
        <option value="recientes" <?= $sortOrder === 'recientes' ? 'selected' : '' ?>>Horas de Tejido</option>
      </select>
    </form>
  </div>

  <?php if (!empty($searchQuery)): ?>
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
      <p style="font-size: 0.9rem;">Resultados de búsqueda para: <strong>"<?= h($searchQuery) ?>"</strong> (<?= count($products) ?> encontrados)</p>
      <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-outlined btn-sm">Limpiar búsqueda</a>
    </div>
  <?php endif; ?>

  <!-- Grilla de Productos -->
  <?php if (empty($products)): ?>
    <div style="text-align: center; padding: 5rem 1rem; background: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-md); margin-bottom: 4rem;">
      <svg style="width: 3.5rem; height: 3.5rem; color: var(--color-neutral-light); margin-bottom: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <h3 style="margin-bottom: 0.5rem;">No se encontraron amigurumis</h3>
      <p style="color: var(--color-neutral-medium); margin-bottom: 1.5rem;">Prueba con otros términos de búsqueda o cambia la categoría seleccionada.</p>
      <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-primary">Ver todos los productos</a>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $prod): 
        $imgs = !empty($prod['images']) ? json_decode($prod['images'], true) : [];
        $thumbUrl = $imgs[0]['url'] ?? '';
        $isFav = in_array($prod['id'], $favorites);
      ?>
        <article class="product-card">
          <!-- Image Wrapper -->
          <div class="product-img-wrapper">
            <?php if (!empty($prod['discountBadge'])): ?>
              <span class="badge-tag badge-discount"><?= h($prod['discountBadge']) ?></span>
            <?php elseif ($prod['status'] === 'low-stock'): ?>
              <span class="badge-tag badge-status"><?= h($prod['statusLabel']) ?></span>
            <?php elseif ($prod['status'] === 'preorder'): ?>
              <span class="badge-tag" style="background-color: var(--color-secondary); color: #fff;">En Aguja (7d)</span>
            <?php elseif ($prod['status'] === 'soldout'): ?>
              <span class="badge-tag" style="background-color: var(--color-danger); color: #fff;">Agotado</span>
            <?php endif; ?>

            <!-- Botón de Favorito -->
            <button 
              type="button" 
              class="fav-btn <?= $isFav ? 'active' : '' ?>" 
              onclick="toggleFavoriteAjax('<?= h($prod['id']) ?>', this)" 
              title="Guardar en favoritos"
              aria-label="Favorito"
            >
              <svg viewBox="0 0 24 24" fill="<?= $isFav ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </button>

            <a href="<?= $baseUrl ?>/product.php?id=<?= h($prod['id']) ?>" tabindex="-1">
              <img src="<?= h($thumbUrl) ?>" alt="<?= h($prod['name']) ?>" class="product-img" loading="lazy">
            </a>
          </div>

          <!-- Product Details -->
          <div class="product-info">
            <div class="product-category"><?= h($prod['categoryLabel']) ?></div>
            <h3 class="product-title">
              <a href="<?= $baseUrl ?>/product.php?id=<?= h($prod['id']) ?>"><?= h($prod['name']) ?></a>
            </h3>

            <div class="product-rating">
              <svg class="star-icon" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
              <span><?= number_format($prod['rating'], 1) ?></span>
              <span class="rating-count">(<?= (int)$prod['reviewCount'] ?>)</span>
            </div>

            <!-- Precios -->
            <div class="product-pricing">
              <span class="current-price"><?= formatCLP($prod['price']) ?></span>
              <?php if (!empty($prod['originalPrice'])): ?>
                <span class="original-price"><?= formatCLP($prod['originalPrice']) ?></span>
              <?php endif; ?>
              <span class="usd-approx">~USD $<?= number_format($prod['usdApprox'], 2) ?></span>
            </div>

            <!-- Botones de Acción -->
            <div class="product-actions">
              <?php if ($prod['status'] === 'soldout'): ?>
                <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.6; cursor: not-allowed;">Agotado</button>
              <?php else: ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="addToCartAjax('<?= h($prod['id']) ?>', 1)">
                  Adoptar
                </button>
              <?php endif; ?>
              <a href="<?= $baseUrl ?>/product.php?id=<?= h($prod['id']) ?>" class="btn btn-outlined btn-sm" title="Ver ficha técnica completa">
                Detalle
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Garantías Artesanales y Confianza -->
  <div style="background-color: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-lg); padding: 2.5rem; margin-bottom: 3.5rem;">
    <div style="text-align: center; max-width: 650px; margin: 0 auto 2.5rem;">
      <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">El Sello Artesanal de Maraña</h3>
      <p>Cuidamos cada aspecto de la experiencia desde la primera puntada hasta que abres la caja con aroma a lavanda en tu hogar.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1.5rem;">
      <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
        <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-sm); background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
        </div>
        <div>
          <h4 style="margin-bottom: 0.2rem;">100% Hecho a Mano</h4>
          <p style="font-size: 0.8rem;">Promedio de 5 a 8 horas de tejido minucioso por cada pieza con hilo Tangüis.</p>
        </div>
      </div>

      <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
        <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-sm); background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </div>
        <div>
          <h4 style="margin-bottom: 0.2rem;">Apego Seguro OEKO-TEX</h4>
          <p style="font-size: 0.8rem;">Ojos bordados a mano sin piezas plásticas sueltas. Seguro para bebés y niños.</p>
        </div>
      </div>

      <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
        <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-sm); background: var(--color-primary-subtle); color: var(--color-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
        </div>
        <div>
          <h4 style="margin-bottom: 0.2rem;">Envíos Cuidados a Todo Chile</h4>
          <p style="font-size: 0.8rem;">Despacho rápido en Santiago y encomiendas protegidas a todas las regiones.</p>
        </div>
      </div>
    </div>
  </div>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
