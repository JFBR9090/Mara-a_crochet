<?php
// admin/reviews.php - Moderación de reseñas y respuestas del taller artesanal
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Exigir inicio de sesión
requireAdminAuth();

$baseUrl = getBaseUrl();
$adminUser = getAdminUser();

// 1. Procesar Acciones de Moderación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewId = $_POST['reviewId'] ?? '';

    if ($action === 'status' && $reviewId) {
        $newStatus = $_POST['status'] ?? 'approved';
        $stmt = $pdo->prepare("UPDATE `reviews` SET `status` = :st WHERE `id` = :id");
        $stmt->execute([':st' => $newStatus, ':id' => $reviewId]);

        setFlash('success', 'Estado de reseña actualizado', "La reseña ahora está en estado " . strtoupper($newStatus));
        header('Location: ' . $baseUrl . '/admin/reviews.php');
        exit();
    }

    if ($action === 'reply' && $reviewId) {
        $replyText = trim($_POST['replyText'] ?? '');
        if ($replyText) {
            $replyJson = json_encode([
                'author' => 'Sofía Morales',
                'role' => 'Tallerista Principal',
                'date' => 'Hoy',
                'text' => $replyText
            ], JSON_UNESCAPED_UNICODE);

            $stmt = $pdo->prepare("UPDATE `reviews` SET `workshopReply` = :wr WHERE `id` = :id");
            $stmt->execute([':wr' => $replyJson, ':id' => $reviewId]);

            setFlash('success', 'Respuesta publicada', 'Tu respuesta de taller ya es visible para los compradores.');
            header('Location: ' . $baseUrl . '/admin/reviews.php');
            exit();
        }
    }
}

// 2. Filtros
$filter = $_GET['filter'] ?? 'todos';
$sql = "SELECT * FROM `reviews`";
$params = [];
if ($filter !== 'todos') {
    $sql .= " WHERE `status` = :st";
    $params[':st'] = $filter;
}
$sql .= " ORDER BY `created_at` DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// 3. Métricas
$allRevStmt = $pdo->query("SELECT * FROM `reviews`");
$allRevs = $allRevStmt->fetchAll();

$countTotal = count($allRevs);
$countPending = count(array_filter($allRevs, fn($r) => $r['status'] === 'pending'));
$countApproved = count(array_filter($allRevs, fn($r) => $r['status'] === 'approved'));
$countFeatured = count(array_filter($allRevs, fn($r) => $r['status'] === 'featured'));

$pageTitle = 'Moderación de Reseñas | Backoffice Taller Maraña';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">
  
  <!-- Sub-Navegación del Backoffice -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 1rem;">
    <div>
      <h1 style="font-size: 1.65rem; margin-bottom: 0.25rem;">Moderación de Opiniones y Reseñas</h1>
      <p style="font-size: 0.85rem;">Validación de testimonios y respuestas oficiales del taller.</p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
      <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-outlined btn-sm">Pedidos y Pagos</a>
      <a href="<?= $baseUrl ?>/admin/inventory.php" class="btn btn-outlined btn-sm">Inventario de Lanas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php" class="btn btn-primary btn-sm">Moderación de Reseñas</a>
      <a href="<?= $baseUrl ?>/admin/status.php" class="btn btn-outlined btn-sm" style="border-color: #9D4EDD; color: #7B2CBF;">⚡ Motor PHP 8.2+</a>
      <a href="<?= $baseUrl ?>/index.php" class="btn btn-outlined btn-sm">Ver Tienda</a>

      <!-- Usuario Logueado & Botón Cerrar Sesión -->
      <div style="margin-left: 0.5rem; display: flex; align-items: center; gap: 0.5rem; background: var(--color-primary-bg); border: 1px solid var(--color-primary-border); padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); font-size: 0.78rem;">
        <span style="font-weight: 700; color: var(--color-primary);">👤 <?= h($adminUser['name'] ?? 'Admin') ?> (Neiva)</span>
        <a href="<?= $baseUrl ?>/admin/logout.php" title="Cerrar Sesión" style="color: var(--color-danger); text-decoration: none; font-weight: 700; border-left: 1px solid var(--color-primary-border); padding-left: 0.5rem;">
          Salir
        </a>
      </div>
    </div>
  </div>

  <!-- Métricas KPI -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem;">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-neutral-light); margin-bottom: 0.25rem;">Total Reseñas</div>
      <div style="font-size: 1.65rem; font-weight: 800;"><?= $countTotal ?></div>
    </div>
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid var(--color-warning);">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-warning); margin-bottom: 0.25rem;">Pendientes Moderación</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-warning);"><?= $countPending ?></div>
    </div>
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid var(--color-success);">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-success); margin-bottom: 0.25rem;">Aprobadas Públicas</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-success);"><?= $countApproved ?></div>
    </div>
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid var(--color-accent);">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-accent); margin-bottom: 0.25rem;">Destacadas Inicio</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-accent);"><?= $countFeatured ?></div>
    </div>
  </div>

  <!-- Pestañas de Filtro -->
  <div class="filters-bar" style="margin-top: 0;">
    <div class="category-pills">
      <a href="<?= $baseUrl ?>/admin/reviews.php" class="cat-pill <?= $filter === 'todos' ? 'active' : '' ?>">Todas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php?filter=pending" class="cat-pill <?= $filter === 'pending' ? 'active' : '' ?>">Pendientes (<?= $countPending ?>)</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php?filter=approved" class="cat-pill <?= $filter === 'approved' ? 'active' : '' ?>">Aprobadas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php?filter=featured" class="cat-pill <?= $filter === 'featured' ? 'active' : '' ?>">Destacadas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php?filter=rejected" class="cat-pill <?= $filter === 'rejected' ? 'active' : '' ?>">Rechazadas</a>
    </div>
  </div>

  <!-- Lista de Reseñas para Moderación -->
  <div style="display: flex; flex-direction: column; gap: 1.25rem;">
    <?php if (empty($reviews)): ?>
      <div class="card-panel" style="text-align: center; padding: 3rem 1rem; color: var(--color-neutral-light);">
        No hay reseñas que coincidan con el filtro actual.
      </div>
    <?php else: ?>
      <?php foreach ($reviews as $r): 
        $reply = !empty($r['workshopReply']) ? json_decode($r['workshopReply'], true) : null;
      ?>
        <div class="card-panel" style="margin-bottom: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.85rem;">
            <div>
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                <strong style="font-size: 1rem; color: var(--color-neutral-dark);"><?= h($r['customerName']) ?></strong>
                <span class="status-pill status-<?= ($r['status'] === 'approved' || $r['status'] === 'featured') ? 'entregado' : (($r['status'] === 'pending') ? 'revision' : 'rechazado') ?>">
                  <?= strtoupper(h($r['status'])) ?>
                </span>
                <?php if ($r['status'] === 'featured'): ?>
                  <span style="font-size: 0.72rem; font-weight: 700; color: var(--color-accent); background: var(--color-accent-subtle); padding: 0.15rem 0.45rem; border-radius: var(--radius-sm); border: 1px solid var(--color-accent-border);">
                    ★ DESTACADA
                  </span>
                <?php endif; ?>
              </div>
              <div style="font-size: 0.78rem; color: var(--color-neutral-light);">
                Producto: <strong><?= h($r['productName']) ?></strong> (SKU: <?= h($r['sku']) ?>) · Orden: <?= h($r['orderId']) ?> · <?= h($r['date']) ?>
              </div>
            </div>

            <!-- Estrellas de Calificación -->
            <div style="display: flex; gap: 0.15rem;">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="star-icon" style="color: <?= $i <= (int)$r['rating'] ? 'var(--color-accent)' : 'var(--color-neutral-border)' ?>;" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
              <?php endfor; ?>
            </div>
          </div>

          <p style="font-size: 0.9rem; color: var(--color-neutral-dark); line-height: 1.5; margin-bottom: 1rem;">
            <?= nl2br(h($r['comment'])) ?>
          </p>

          <!-- Respuesta Existente del Taller -->
          <?php if (!empty($reply)): ?>
            <div style="background-color: var(--color-primary-subtle); border-left: 3px solid var(--color-primary); padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; font-size: 0.82rem;">
              <strong style="color: var(--color-primary);">Respuesta del Taller (<?= h($reply['author']) ?>):</strong>
              <div style="margin-top: 0.2rem;"><?= nl2br(h($reply['text'])) ?></div>
            </div>
          <?php endif; ?>

          <!-- Barra de Acciones de Moderación -->
          <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; border-top: 1px solid var(--color-neutral-border-light); padding-top: 0.85rem;">
            <!-- Aprobar -->
            <form action="<?= $baseUrl ?>/admin/reviews.php" method="POST" style="display: inline;">
              <input type="hidden" name="action" value="status">
              <input type="hidden" name="reviewId" value="<?= h($r['id']) ?>">
              <input type="hidden" name="status" value="approved">
              <button type="submit" class="btn btn-outlined btn-sm">✓ Aprobar</button>
            </form>

            <!-- Destacar -->
            <form action="<?= $baseUrl ?>/admin/reviews.php" method="POST" style="display: inline;">
              <input type="hidden" name="action" value="status">
              <input type="hidden" name="reviewId" value="<?= h($r['id']) ?>">
              <input type="hidden" name="status" value="<?= ($r['status'] === 'featured') ? 'approved' : 'featured' ?>">
              <button type="submit" class="btn btn-secondary btn-sm">
                <?= ($r['status'] === 'featured') ? '★ Quitar Destacado' : '★ Destacar' ?>
              </button>
            </form>

            <!-- Rechazar / Ocultar -->
            <form action="<?= $baseUrl ?>/admin/reviews.php" method="POST" style="display: inline;">
              <input type="hidden" name="action" value="status">
              <input type="hidden" name="reviewId" value="<?= h($r['id']) ?>">
              <input type="hidden" name="status" value="rejected">
              <button type="submit" class="btn btn-sm" style="background-color: var(--color-danger); color: #fff;">✕ Ocultar</button>
            </form>

            <!-- Botón Responder -->
            <button type="button" class="btn btn-inverted btn-sm" onclick="const f = document.getElementById('reply-form-<?= h($r['id']) ?>'); f.style.display = (f.style.display === 'none' ? 'block' : 'none');">
              💬 Responder como Taller
            </button>
          </div>

          <!-- Formulario de Respuesta Colapsable -->
          <div id="reply-form-<?= h($r['id']) ?>" style="display: none; margin-top: 1rem; padding: 1rem; background: var(--color-neutral-bg); border-radius: var(--radius-sm); border: 1px solid var(--color-neutral-border);">
            <form action="<?= $baseUrl ?>/admin/reviews.php" method="POST">
              <input type="hidden" name="action" value="reply">
              <input type="hidden" name="reviewId" value="<?= h($r['id']) ?>">
              <label class="form-label" for="reply-text-<?= h($r['id']) ?>">Escribe la respuesta del Taller Maraña:</label>
              <textarea id="reply-text-<?= h($r['id']) ?>" name="replyText" rows="2" class="form-control" required placeholder="¡Hola! Muchas gracias por tus bellas palabras..."><?= h($reply['text'] ?? '') ?></textarea>
              <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.5rem;">
                <button type="button" class="btn btn-outlined btn-sm" onclick="document.getElementById('reply-form-<?= h($r['id']) ?>').style.display='none';">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Publicar Respuesta</button>
              </div>
            </form>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
