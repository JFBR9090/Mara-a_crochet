<?php
// cart.php - Vista completa de la Canasta de Compras
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tu Canasta de Adopción | Maraña Crochet';
$cartItems = getCart();
$subtotal = getCartSubtotal();
$baseUrl = getBaseUrl();

// Acciones POST directas (soporte sin JavaScript activado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pId = $_POST['productId'] ?? '';

    if ($action === 'update' && $pId) {
        $delta = (int)($_POST['delta'] ?? 0);
        updateCartQuantity($pId, $delta);
        header('Location: ' . $baseUrl . '/cart.php');
        exit();
    } elseif ($action === 'remove' && $pId) {
        removeFromCart($pId);
        setFlash('info', 'Producto eliminado', 'El artículo fue retirado de tu canasta.');
        header('Location: ' . $baseUrl . '/cart.php');
        exit();
    } elseif ($action === 'clear') {
        clearCart();
        setFlash('info', 'Canasta vaciada');
        header('Location: ' . $baseUrl . '/cart.php');
        exit();
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">
  <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 2rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 1rem;">
    <div>
      <h1 style="font-size: 1.85rem; margin-bottom: 0.35rem;">Tu Canasta de Adopción</h1>
      <p style="font-size: 0.88rem;">Revisa las criaturas cósmicas que te acompañarán antes de pasar al pago.</p>
    </div>
    <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-outlined btn-sm">&larr; Seguir Explorando</a>
  </div>

  <?php if (empty($cartItems)): ?>
    <div style="text-align: center; padding: 5rem 1rem; background: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-md);">
      <svg style="width: 4rem; height: 4rem; color: var(--color-neutral-light); margin-bottom: 1.25rem; opacity: 0.5;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>
      <h3 style="margin-bottom: 0.5rem;">Tu canasta de adopción está vacía</h3>
      <p style="color: var(--color-neutral-medium); margin-bottom: 1.5rem; max-width: 450px; margin-left: auto; margin-right: auto;">
        Nuestros amigurumis están listos para encontrar un hogar lleno de cariño. Explora el catálogo para agregar tus favoritos.
      </p>
      <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-primary btn-lg">Descubrir Criaturas Cósmicas</a>
    </div>
  <?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
      <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;" class="cart-layout-grid">
        <style>
          @media (min-width: 900px) {
            .cart-layout-grid { grid-template-columns: 1.5fr 1fr !important; }
          }
        </style>

        <!-- Columna Izquierda: Tabla de Productos -->
        <div class="card-panel" style="margin-bottom: 0;">
          <table class="table-custom" style="width: 100%;">
            <thead>
              <tr>
                <th>Amigurumi</th>
                <th>Precio</th>
                <th style="text-align: center;">Cantidad</th>
                <th style="text-align: right;">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item): 
                $itemTotal = $item['price'] * $item['quantity'];
              ?>
                <tr>
                  <td>
                    <div style="display: flex; gap: 0.85rem; align-items: center;">
                      <img src="<?= h($item['image']) ?>" alt="<?= h($item['name']) ?>" style="width: 54px; height: 54px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--color-neutral-border);">
                      <div>
                        <div style="font-weight: 700; font-size: 0.92rem;"><?= h($item['name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-neutral-light);">SKU: <?= h($item['sku']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="font-weight: 600;"><?= formatCOP($item['price']) ?></td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; align-items: center; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); overflow: hidden;">
                      <button type="button" onclick="updateItemQuantity('<?= h($item['id']) ?>', -1)" style="padding: 0.25rem 0.55rem; background: var(--color-neutral-bg); border: none; cursor: pointer;">-</button>
                      <span style="padding: 0.25rem 0.65rem; font-weight: 700;"><?= (int)$item['quantity'] ?></span>
                      <button type="button" onclick="updateItemQuantity('<?= h($item['id']) ?>', 1)" style="padding: 0.25rem 0.55rem; background: var(--color-neutral-bg); border: none; cursor: pointer;">+</button>
                    </div>
                  </td>
                  <td style="text-align: right; font-weight: 800;"><?= formatCOP($itemTotal) ?></td>
                  <td style="text-align: right;">
                    <button type="button" onclick="removeCartItem('<?= h($item['id']) ?>')" style="background: none; border: none; color: var(--color-neutral-light); cursor: pointer; font-size: 1.25rem;" title="Eliminar">&times;</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--color-neutral-border-light);">
            <form action="<?= $baseUrl ?>/cart.php" method="POST">
              <input type="hidden" name="action" value="clear">
              <button type="submit" class="btn btn-outlined btn-sm" onclick="return confirm('¿Seguro que deseas vaciar tu canasta?');">Vaciar Canasta</button>
            </form>
            <span style="font-size: 0.82rem; color: var(--color-neutral-medium);">✓ Los amigurumis seleccionados quedan reservados durante tu sesión</span>
          </div>
        </div>

        <!-- Columna Derecha: Resumen de Adopción -->
        <div>
          <div class="card-panel">
            <h3 style="font-size: 1.15rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-neutral-border-light); padding-bottom: 0.75rem;">Resumen del Pedido</h3>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.75rem;">
              <span class="text-muted">Subtotal productos:</span>
              <span style="font-weight: 700;"><?= formatCOP($subtotal) ?></span>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.75rem;">
              <span class="text-muted">Empaque aromático de lavanda:</span>
              <span style="color: var(--color-success); font-weight: 700;">Gratis</span>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 1.25rem;">
              <span class="text-muted">Certificado de Adopción:</span>
              <span style="color: var(--color-success); font-weight: 700;">Incluido</span>
            </div>

            <div style="background: var(--color-primary-subtle); border-radius: var(--radius-sm); padding: 0.85rem; margin-bottom: 1.5rem; font-size: 0.8rem; color: var(--color-primary);">
              <strong>Despacho Nacional (Colombia):</strong> Se selecciona en el siguiente paso (Envío Local Neiva $6.000 COP / Envío Nacional Colombia $14.500 COP / Retiro en Taller Neiva Gratis).
            </div>

            <div style="display: flex; justify-content: space-between; align-items: baseline; font-size: 1.15rem; border-top: 1px solid var(--color-neutral-border); padding-top: 1rem; margin-bottom: 1.5rem;">
              <span style="font-weight: 700;">Total Estimado:</span>
              <span style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);"><?= formatCOP($subtotal) ?></span>
            </div>

            <a href="<?= $baseUrl ?>/checkout.php" class="btn btn-primary btn-lg" style="width: 100%;">
              Continuar al Checkout Seguro &rarr;
            </a>

            <div style="margin-top: 1rem; text-align: center; font-size: 0.75rem; color: var(--color-neutral-light);">
              🔒 Pago seguro directo vía Bancolombia, Nequi, Daviplata o Davivienda con Comprobante Oficial.
            </div>
          </div>
        </div>

      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

