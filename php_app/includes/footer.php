<?php
// includes/footer.php - Pie de página minimalista y scripts globales
$baseUrl = getBaseUrl();
?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Columna 1: Marca y Filosofía -->
      <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.85rem;">
          <div class="brand-mark" style="width: 1.8rem; height: 1.8rem; font-size: 0.95rem;">M</div>
          <span style="font-size: 1.1rem; font-weight: 800; color: var(--color-neutral-dark);">Maraña Crochet</span>
        </div>
        <p style="font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem;">
          Amigurumis nacidos en telar artesanal chileno. Hilados 100% de algodón peinado hipoalergénico diseñados para coleccionistas y apego infantil seguro.
        </p>
        <div style="font-size: 0.75rem; color: var(--color-neutral-light);">
          📍 Taller Artesanal · Providencia, Santiago de Chile
        </div>
      </div>

      <!-- Columna 2: Navegación Tienda -->
      <div>
        <h4 class="footer-title">Colecciones</h4>
        <ul class="footer-links">
          <li><a href="<?= $baseUrl ?>/index.php?category=espacio">Criaturas del Espacio & Estrellas</a></li>
          <li><a href="<?= $baseUrl ?>/index.php?category=bosque">Bosque Encantado</a></li>
          <li><a href="<?= $baseUrl ?>/index.php?category=bebes">Apego para Bebés (OEKO-TEX)</a></li>
          <li><a href="<?= $baseUrl ?>/index.php?category=nuevos">Nuevas Creaciones</a></li>
        </ul>
      </div>

      <!-- Columna 3: Información y Confianza -->
      <div>
        <h4 class="footer-title">Garantía Artesanal</h4>
        <ul class="footer-links">
          <li>✓ Despachos a todo Chile vía Starken & Express RM</li>
          <li>✓ Pago seguro vía Transferencia Electrónica (TEF)</li>
          <li>✓ Caja kraft de regalo con flores secas de lavanda</li>
          <li>✓ Certificado de nacimiento y adopción numerado</li>
        </ul>
      </div>

      <!-- Columna 4: Panel del Taller -->
      <div>
        <h4 class="footer-title">Administración</h4>
        <p style="font-size: 0.82rem; margin-bottom: 0.75rem;">Acceso al panel interno de control de pedidos, inventario de lanas y moderación de opiniones.</p>
        <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-inverted btn-sm">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
          Panel del Taller
        </a>
      </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="footer-bottom">
      <div>
        &copy; <?= date('Y') ?> Maraña Crochet. Todos los derechos reservados. Hecho con amor y crochet.
      </div>
      <div style="display: flex; gap: 1.5rem;">
        <a href="<?= $baseUrl ?>/index.php" style="color: var(--color-neutral-light);">Términos y Condiciones</a>
        <a href="<?= $baseUrl ?>/index.php" style="color: var(--color-neutral-light);">Cuidados del Amigurumi</a>
      </div>
    </div>
  </div>
</footer>

<!-- Include Cart Drawer Panel -->
<?php require __DIR__ . '/cart_drawer.php'; ?>

<!-- Global Scripts -->
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>
