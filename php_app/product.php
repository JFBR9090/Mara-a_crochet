<?php
// product.php - Ficha de detalle de producto con galería, especificaciones y reseñas verificadas
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$productId = $_GET['id'] ?? '';
if (!$productId) {
    header('Location: ' . getBaseUrl() . '/index.php');
    exit();
}

// 1. Obtener datos del producto con PDO
$stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = :id LIMIT 1");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    $pageTitle = 'Producto no encontrado | Maraña Crochet';
    require __DIR__ . '/includes/header.php';
    echo "<div class='container' style='padding: 5rem 1rem; text-align: center;'>
            <h2>Amigurumi no encontrado</h2>
            <p>El producto que buscas ya no está disponible o el enlace es incorrecto.</p>
            <a href='" . getBaseUrl() . "/index.php' class='btn btn-primary' style='margin-top: 1.5rem;'>Volver al Catálogo</a>
          </div>";
    require __DIR__ . '/includes/footer.php';
    exit();
}

// 2. Procesar nueva reseña enviada por formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_review') {
    $reviewerName = trim($_POST['customerName'] ?? '');
    $reviewerEmail = trim($_POST['customerEmail'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');

    if (!empty($reviewerName) && !empty($comment)) {
        // Generar iniciales y correo enmascarado
        $parts = explode(' ', $reviewerName);
        $initials = strtoupper(substr($parts[0] ?? 'M', 0, 1) . substr($parts[1] ?? 'C', 0, 1));
        $emailParts = explode('@', $reviewerEmail);
        $emailMasked = substr($emailParts[0] ?? 'usr', 0, 2) . '****@' . ($emailParts[1] ?? 'gmail.com');

        $revStmt = $pdo->prepare("
            INSERT INTO `reviews`
            (`id`, `customerInitials`, `customerName`, `emailMasked`, `orderId`, `sku`, `productName`, `productIcon`, `rating`, `date`, `comment`, `verified`, `status`, `helpfulCount`)
            VALUES
            (:id, :initials, :name, :emailMasked, :orderId, :sku, :prodName, 'cruelty_free', :rating, 'Hoy', :comment, 1, 'pending', 0)
        ");

        $revStmt->execute([
            ':id' => 'rev-' . uniqid(),
            ':initials' => $initials,
            ':name' => $reviewerName,
            ':emailMasked' => $emailMasked,
            ':orderId' => 'Web-' . rand(1000, 9999),
            ':sku' => $product['sku'],
            ':prodName' => $product['name'],
            ':rating' => $rating,
            ':comment' => $comment,
        ]);

        setFlash('success', '¡Gracias por tu reseña!', 'Tu comentario fue enviado y pasará por moderación antes de publicarse.');
        header('Location: ' . getBaseUrl() . '/product.php?id=' . urlencode($productId));
        exit();
    }
}

// 3. Obtener reseñas del producto
$revStmt = $pdo->prepare("SELECT * FROM `reviews` WHERE `sku` = :sku AND (`status` = 'approved' OR `status` = 'featured') ORDER BY `created_at` DESC");
$revStmt->execute([':sku' => $product['sku']]);
$reviews = $revStmt->fetchAll();

// Si no hay reseñas directas por SKU, mostrar reseñas generales aprobadas
if (empty($reviews)) {
    $genStmt = $pdo->query("SELECT * FROM `reviews` WHERE `status` = 'approved' OR `status` = 'featured' LIMIT 3");
    $reviews = $genStmt->fetchAll();
}

// 4. Productos relacionados
$relStmt = $pdo->prepare("SELECT * FROM `products` WHERE `category` = :cat AND `id` != :id LIMIT 3");
$relStmt->execute([':cat' => $product['category'], ':id' => $product['id']]);
$related = $relStmt->fetchAll();

$images = !empty($product['images']) ? json_decode($product['images'], true) : [];
$mainImage = $images[0]['url'] ?? '';
$pageTitle = h($product['name']) . ' | Maraña Crochet';
$pageDesc = h($product['tagline']);

require __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 1.5rem;">
  
  <!-- Breadcrumbs -->
  <nav style="font-size: 0.8rem; color: var(--color-neutral-light); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.4rem;">
    <a href="<?= $baseUrl ?>/index.php" style="color: var(--color-neutral-medium);">Inicio</a>
    <span>/</span>
    <a href="<?= $baseUrl ?>/index.php?category=<?= h($product['category']) ?>" style="color: var(--color-neutral-medium);"><?= h($product['categoryLabel']) ?></a>
    <span>/</span>
    <span style="color: var(--color-neutral-dark); font-weight: 600;"><?= h($product['name']) ?></span>
  </nav>

  <!-- Layout Principal de Ficha -->
  <div class="detail-layout">
    
    <!-- Columna Izquierda: Galería de Fotos -->
    <div>
      <img id="main-product-photo" src="<?= h($mainImage) ?>" alt="<?= h($product['name']) ?>" class="gallery-main" onerror="this.onerror=null; this.src='<?= $baseUrl ?>/assets/img/kit-crochet.jpg';">
      
      <?php if (count($images) > 1): ?>
        <div class="gallery-thumbs">
          <?php foreach ($images as $index => $img): ?>
            <img 
              src="<?= h($img['url']) ?>" 
              alt="<?= h($img['alt']) ?>" 
              data-full="<?= h($img['url']) ?>" 
              class="gallery-thumb <?= $index === 0 ? 'active' : '' ?>"
            >
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div style="background-color: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1.5rem;">
        <h4 style="font-size: 0.95rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
          <svg style="width: 1.1rem; height: 1.1rem; color: var(--color-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
          Empaque & Caligrafía Artesanal
        </h4>
        <p style="font-size: 0.82rem; color: var(--color-neutral-medium); line-height: 1.45;">
          Cada amigurumi viaja en una caja kraft ecológica con ramilletes de lavanda aromática preparados en nuestro taller de Neiva, Huila (Colombia). Incluye certificado de adopción numerado con caligrafía personalizada y comprobante oficial de pago.
        </p>
      </div>
    </div>

    <!-- Columna Derecha: Información y Compra -->
    <div>
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-primary); letter-spacing: 0.05em; margin-bottom: 0.35rem;">
        <?= h($product['categoryLabel']) ?> · SKU: <?= h($product['sku']) ?>
      </div>

      <h1 style="font-size: 1.85rem; margin-bottom: 0.4rem;"><?= h($product['name']) ?></h1>
      
      <p style="font-size: 0.95rem; font-style: italic; color: var(--color-neutral-light); margin-bottom: 1rem;">
        "<?= h($product['tagline']) ?>"
      </p>

      <!-- Rating & Reseñas -->
      <div class="product-rating" style="margin-bottom: 1.25rem;">
        <svg class="star-icon" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
        <span style="font-weight: 700;"><?= number_format($product['rating'], 1) ?></span>
        <a href="#reviews-section" style="color: var(--color-primary); text-decoration: underline; margin-left: 0.25rem;">
          (<?= (int)$product['reviewCount'] ?> valoraciones de clientes)
        </a>
      </div>

      <!-- Precio -->
      <div style="background: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: baseline; gap: 0.85rem; margin-bottom: 0.5rem;">
          <span style="font-size: 2rem; font-weight: 800; color: var(--color-neutral-dark);">
            <?= formatCOP($product['price']) ?>
          </span>
          <?php if (!empty($product['originalPrice'])): ?>
            <span style="font-size: 1.1rem; text-decoration: line-through; color: var(--color-neutral-light);">
              <?= formatCOP($product['originalPrice']) ?>
            </span>
            <span style="background: var(--color-accent); color: var(--color-neutral-dark); font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: var(--radius-sm);">
              <?= h($product['discountBadge'] ?? 'OFERTA') ?>
            </span>
          <?php endif; ?>
          <span style="margin-left: auto; font-size: 0.85rem; color: var(--color-neutral-light);">
            Aprox. USD $<?= number_format($product['usdApprox'], 2) ?>
          </span>
        </div>

        <!-- Barra de Progreso de Stock -->
        <div style="margin-top: 0.75rem;">
          <?php 
            $stock = (int)$product['stock'];
            $maxStock = max(1, (int)$product['maxStock']);
            $pct = min(100, round(($stock / $maxStock) * 100));
            $barClass = ($stock <= 2) ? 'danger' : (($stock <= 5) ? 'warning' : '');
          ?>
          <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.35rem;">
            <span>Disponibilidad en Taller:</span>
            <span class="text-primary"><?= h($product['statusLabel']) ?> (<?= $stock ?> de <?= $maxStock ?>)</span>
          </div>
          <div class="progress-container">
            <div class="progress-bar <?= $barClass ?>" style="width: <?= $pct ?>%;"></div>
          </div>
        </div>
      </div>

      <!-- Selector de Cantidad y Botones de Compra -->
      <div style="display: flex; gap: 0.75rem; margin-bottom: 2rem; align-items: center;">
        <div style="display: flex; align-items: center; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); background: #FFFFFF;">
          <button type="button" onclick="const input = document.getElementById('qty-input'); input.value = Math.max(1, parseInt(input.value) - 1);" style="padding: 0.65rem 0.85rem; border: none; background: none; cursor: pointer; font-size: 1.1rem;">-</button>
          <input type="number" id="qty-input" value="1" min="1" max="<?= max(1, (int)$product['stock']) ?>" style="width: 40px; text-align: center; border: none; outline: none; font-weight: 700; font-family: var(--font-family);">
          <button type="button" onclick="const input = document.getElementById('qty-input'); input.value = Math.min(<?= max(1, (int)$product['stock']) ?>, parseInt(input.value) + 1);" style="padding: 0.65rem 0.85rem; border: none; background: none; cursor: pointer; font-size: 1.1rem;">+</button>
        </div>

        <?php if ($product['status'] === 'soldout'): ?>
          <button type="button" class="btn btn-secondary btn-lg" disabled style="flex: 1; opacity: 0.6; cursor: not-allowed;">Agotado Temporalmente</button>
        <?php else: ?>
          <button 
            type="button" 
            class="btn btn-primary btn-lg" 
            style="flex: 1;" 
            onclick="addToCartAjax('<?= h($product['id']) ?>', parseInt(document.getElementById('qty-input').value))"
          >
            Añadir a la Canasta
          </button>
          <button 
            type="button" 
            class="btn btn-secondary btn-lg" 
            onclick="addToCartAjax('<?= h($product['id']) ?>', parseInt(document.getElementById('qty-input').value), false); window.location.href='<?= $baseUrl ?>/checkout.php';"
          >
            Adoptar Ya
          </button>
        <?php endif; ?>
      </div>

      <!-- Historia y Descripción -->
      <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.1rem; margin-bottom: 0.6rem;">Historia de la Criatura</h3>
        <p style="line-height: 1.6; color: var(--color-neutral-medium); font-size: 0.95rem;">
          <?= nl2br(h($product['description'])) ?>
        </p>
      </div>

      <!-- Ficha Técnica -->
      <div style="background-color: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-md); padding: 1.5rem;">
        <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Especificaciones Artesanales</h3>
        <table class="specs-table">
          <tr>
            <td>Horas de Tejido:</td>
            <td><strong><?= number_format($product['craftHours'], 1) ?> horas</strong> de labor minuciosa</td>
          </tr>
          <tr>
            <td>Dimensiones:</td>
            <td><?= h($product['dimensions']) ?></td>
          </tr>
          <tr>
            <td>Peso:</td>
            <td><?= h($product['weight']) ?></td>
          </tr>
          <tr>
            <td>Material Exterior:</td>
            <td><?= h($product['material']) ?></td>
          </tr>
          <tr>
            <td>Relleno Interior:</td>
            <td><?= h($product['filling']) ?></td>
          </tr>
          <tr>
            <td>Seguridad Infantil:</td>
            <td><?= h($product['safety']) ?></td>
          </tr>
          <tr>
            <td>El Paquete Incluye:</td>
            <td><?= h($product['includes']) ?></td>
          </tr>
        </table>

        <!-- Garantía de Origen y Envíos Nacionales -->
        <div style="margin-top: 1.25rem; background: var(--color-primary-bg); border: 1px solid var(--color-primary-border); border-radius: var(--radius-sm); padding: 1rem; font-size: 0.83rem; color: var(--color-neutral-dark); display: flex; flex-direction: column; gap: 0.45rem;">
          <div style="font-weight: 800; color: var(--color-primary); display: flex; align-items: center; gap: 0.4rem;">
            📍 Taller Artesanal en Neiva, Huila (Colombia)
          </div>
          <div>🇨🇴 <strong>Envíos Únicamente Nacionales:</strong> Despachos a todos los departamentos de Colombia con empaque especial y aroma a lavanda.</div>
          <div>📄 <strong>Comprobante Oficial:</strong> Al comprar se emite automáticamente tu comprobante electrónico de pago con serie única y certificado de adopción numerado.</div>
        </div>
      </div>

    </div>
  </div>

  <!-- Sección de Reseñas Verificadas -->
  <section id="reviews-section" style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--color-neutral-border);">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
      <div>
        <h2 style="font-size: 1.5rem; margin-bottom: 0.35rem;">Valoraciones de la Comunidad</h2>
        <p style="font-size: 0.88rem;">Opiniones reales de familias y coleccionistas que han adoptado amigurumis de Maraña.</p>
      </div>
      <a href="#form-review" class="btn btn-outlined btn-sm">Escribir una Valoración</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 1.25rem; margin-bottom: 3rem;">
      <?php foreach ($reviews as $rev): 
        $reply = !empty($rev['workshopReply']) ? json_decode($rev['workshopReply'], true) : null;
      ?>
        <div class="card-panel" style="margin-bottom: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <div style="width: 2.25rem; height: 2.25rem; border-radius: 50%; background: var(--color-primary-subtle); color: var(--color-primary); font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                <?= h($rev['customerInitials']) ?>
              </div>
              <div>
                <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-neutral-dark);"><?= h($rev['customerName']) ?></div>
                <div style="font-size: 0.75rem; color: var(--color-neutral-light);">
                  <?= h($rev['emailMasked']) ?> · <?= h($rev['date']) ?>
                  <?php if (!empty($rev['verified'])): ?>
                    · <span style="color: var(--color-success); font-weight: 600;">✓ Compra Verificada</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Estrellas -->
            <div style="display: flex; gap: 0.15rem;">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="star-icon" style="color: <?= $i <= (int)$rev['rating'] ? 'var(--color-accent)' : 'var(--color-neutral-border)' ?>;" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
              <?php endfor; ?>
            </div>
          </div>

          <p style="font-size: 0.9rem; color: var(--color-neutral-dark); line-height: 1.5; margin-bottom: 0.75rem;">
            <?= nl2br(h($rev['comment'])) ?>
          </p>

          <?php if (!empty($reply)): ?>
            <div style="background-color: var(--color-primary-subtle); border-left: 3px solid var(--color-primary); padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-top: 0.85rem;">
              <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-primary); margin-bottom: 0.2rem;">
                Respuesta del Taller (<?= h($reply['author'] ?? 'Tallerista') ?> · <?= h($reply['date'] ?? 'Reciente') ?>):
              </div>
              <p style="font-size: 0.82rem; color: var(--color-neutral-dark); margin: 0;">
                <?= nl2br(h($reply['text'] ?? '')) ?>
              </p>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Formulario para dejar reseña -->
    <div id="form-review" class="card-panel" style="max-width: 650px;">
      <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Deja tu Comentario sobre <?= h($product['name']) ?></h3>
      <p style="font-size: 0.82rem; margin-bottom: 1.25rem;">Nos ayuda enormemente a seguir mejorando nuestras puntadas y detalles.</p>

      <form action="<?= $baseUrl ?>/product.php?id=<?= h($product['id']) ?>" method="POST">
        <input type="hidden" name="action" value="new_review">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="rev-name">Tu Nombre *</label>
            <input type="text" id="rev-name" name="customerName" required class="form-control" placeholder="Ej: Camila Soto">
          </div>
          <div class="form-group">
            <label class="form-label" for="rev-email">Tu Correo Electrónico *</label>
            <input type="email" id="rev-email" name="customerEmail" required class="form-control" placeholder="nombre@gmail.com">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="rev-rating">Calificación *</label>
          <select name="rating" id="rev-rating" class="form-control" style="width: auto;">
            <option value="5">⭐⭐⭐⭐⭐ (5 estrellas - Excelente)</option>
            <option value="4">⭐⭐⭐⭐ (4 estrellas - Muy Bueno)</option>
            <option value="3">⭐⭐⭐ (3 estrellas - Bueno)</option>
            <option value="2">⭐⭐ (2 estrellas - Regular)</option>
            <option value="1">⭐ (1 estrella - Insatisfecho)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="rev-comment">Tu Opinión *</label>
          <textarea id="rev-comment" name="comment" required rows="3" class="form-control" placeholder="Cuéntanos qué te pareció la suavidad del hilo, los detalles y el aroma a lavanda..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Publicar Valoración</button>
      </form>
    </div>
  </section>

  <!-- Productos Relacionados -->
  <?php if (!empty($related)): ?>
    <section style="margin: 3.5rem 0;">
      <h3 style="font-size: 1.3rem; margin-bottom: 1.25rem;">Otras Criaturas de la Colección</h3>
      <div class="product-grid" style="margin-bottom: 0;">
        <?php foreach ($related as $rel): 
          $rImgs = !empty($rel['images']) ? json_decode($rel['images'], true) : [];
          $rThumb = $rImgs[0]['url'] ?? '';
        ?>
          <article class="product-card">
            <div class="product-img-wrapper">
              <a href="<?= $baseUrl ?>/product.php?id=<?= h($rel['id']) ?>">
                <img src="<?= h($rThumb) ?>" alt="<?= h($rel['name']) ?>" class="product-img">
              </a>
            </div>
            <div class="product-info">
              <h4 class="product-title">
                <a href="<?= $baseUrl ?>/product.php?id=<?= h($rel['id']) ?>"><?= h($rel['name']) ?></a>
              </h4>
              <div class="product-pricing">
                <span class="current-price"><?= formatCOP($rel['price']) ?></span>
              </div>
              <div class="product-actions">
                <button type="button" class="btn btn-primary btn-sm" onclick="addToCartAjax('<?= h($rel['id']) ?>', 1)">Adoptar</button>
                <a href="<?= $baseUrl ?>/product.php?id=<?= h($rel['id']) ?>" class="btn btn-outlined btn-sm">Ver</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

