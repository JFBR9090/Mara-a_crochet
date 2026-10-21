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
          <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Logo Maraña Crochet" style="width: 2.8rem; height: 2.8rem; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-primary); box-shadow: 0 2px 8px rgba(123, 44, 191, 0.2);">
          <span style="font-size: 1.1rem; font-weight: 800; color: var(--color-neutral-dark);">Maraña Crochet</span>
        </div>
        <p style="font-size: 0.85rem; line-height: 1.5; margin-bottom: 1rem;">
          Amigurumis tejidos a mano en nuestro taller artesanal en Neiva, Huila, Colombia. Hilados 100% de algodón peinado hipoalergénico diseñados para coleccionistas y apego infantil seguro.
        </p>
        <div style="font-size: 0.75rem; color: var(--color-primary); font-weight: 700;">
          📍 Taller Artesanal · Neiva, Huila, Colombia
        </div>
        <div style="font-size: 0.73rem; color: var(--color-neutral-light); margin-top: 0.2rem;">
          🇨🇴 Despachos exclusivamente a nivel nacional
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
          <li>✓ <strong>Envíos únicamente nacionales</strong> en toda Colombia</li>
          <li>✓ Despachos directos desde <strong>Neiva, Huila</strong></li>
          <li>✓ Pago seguro vía Bancolombia, Nequi y Daviplata</li>
          <li>✓ Emisión de <strong>Comprobante Oficial de Pago</strong></li>
          <li>✓ Certificado de nacimiento y adopción numerado</li>
        </ul>
      </div>

      <!-- Columna 4: Panel del Taller -->
      <div>
        <h4 class="footer-title">Administración</h4>
        <p style="font-size: 0.82rem; margin-bottom: 0.75rem;">Acceso protegido al panel interno de control de pedidos, inventario y comprobantes bancarios.</p>
        <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-inverted btn-sm">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
          Panel del Taller (Admin)
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

