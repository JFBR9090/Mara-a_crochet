<?php
// includes/cart_drawer.php - Cajón emergente lateral interactivo del carrito de compras
$cartItems = getCart();
$subtotal = getCartSubtotal();
$baseUrl = getBaseUrl();
?>

<!-- Cart Drawer Overlay -->
<div id="cart-drawer-overlay" class="cart-drawer-overlay"></div>

<!-- Cart Drawer Panel -->
<aside id="cart-drawer" class="cart-drawer">
  <!-- Header -->
  <div class="cart-drawer-header">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <svg style="width: 1.25rem; height: 1.25rem; color: var(--color-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <h3 style="font-size: 1.1rem; margin: 0;">Tu Canasta de Adopción</h3>
    </div>
    <button type="button" id="close-cart-btn" class="btn btn-icon" style="background: none; border: none; color: var(--color-neutral-medium); font-size: 1.4rem; padding: 0.2rem;" aria-label="Cerrar">&times;</button>
  </div>

  <!-- Body -->
  <div class="cart-drawer-body" id="cart-drawer-items">
    <?php if (empty($cartItems)): ?>
      <div style="text-align: center; padding: 3rem 1rem; color: var(--color-neutral-light);">
        <svg style="width: 3rem; height: 3rem; margin-bottom: 1rem; opacity: 0.4;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M8 12h8"></path>
        </svg>
        <p style="font-weight: 600; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Tu canasta está vacía</p>
        <p style="font-size: 0.85rem;">Explora las criaturas cósmicas y añade tus amigurumis favoritos.</p>
        <a href="<?= $baseUrl ?>/index.php#catalogo" class="btn btn-outlined btn-sm" style="margin-top: 1rem;">Ver Catálogo</a>
      </div>
    <?php else: ?>
      <?php foreach ($cartItems as $item): ?>
        <div class="cart-item" data-product-id="<?= h($item['id']) ?>">
          <img src="<?= h($item['image']) ?>" alt="<?= h($item['name']) ?>" class="cart-item-img">
          <div style="flex: 1; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <h4 style="font-size: 0.88rem; font-weight: 700; line-height: 1.25; margin-bottom: 0.2rem;"><?= h($item['name']) ?></h4>
              <button type="button" class="btn-remove-item" onclick="removeCartItem('<?= h($item['id']) ?>')" style="background: none; border: none; color: var(--color-neutral-light); cursor: pointer; padding: 0.1rem;" title="Eliminar">&times;</button>
            </div>
            <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-bottom: 0.5rem;">SKU: <?= h($item['sku']) ?></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
              <!-- Quantity Selector -->
              <div style="display: inline-flex; align-items: center; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); overflow: hidden;">
                <button type="button" onclick="updateItemQuantity('<?= h($item['id']) ?>', -1)" style="padding: 0.2rem 0.5rem; background: var(--color-neutral-bg); border: none; cursor: pointer;">-</button>
                <span style="padding: 0.2rem 0.6rem; font-size: 0.85rem; font-weight: 600;"><?= (int)$item['quantity'] ?></span>
                <button type="button" onclick="updateItemQuantity('<?= h($item['id']) ?>', 1)" style="padding: 0.2rem 0.5rem; background: var(--color-neutral-bg); border: none; cursor: pointer;">+</button>
              </div>
              <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-neutral-dark);"><?= formatCOP($item['price'] * $item['quantity']) ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Footer (siempre presente en DOM, visible cuando hay items) -->
  <div class="cart-drawer-footer" id="cart-drawer-footer" style="display: <?= empty($cartItems) ? 'none' : 'block' ?>; padding: 1.25rem 1.5rem; background: #fff; border-top: 1px solid var(--color-neutral-border); box-shadow: 0 -4px 15px rgba(0,0,0,0.04);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
      <span style="font-size: 0.88rem; font-weight: 600; color: var(--color-neutral-medium);">Subtotal Amigurumis:</span>
      <span id="cart-drawer-subtotal" style="font-size: 1.25rem; font-weight: 800; color: var(--color-neutral-dark);"><?= formatCOP($subtotal) ?></span>
    </div>

    <!-- Estimación de envío rápida -->
    <div style="background: var(--color-neutral-bg); padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); margin: 0.75rem 0; font-size: 0.78rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
        <span style="color: var(--color-neutral-medium);">🇨🇴 Despacho Nacional:</span>
        <span style="font-weight: 700; color: var(--color-primary);">$11.500 - $14.500 COP</span>
      </div>
      <div style="font-size: 0.73rem; color: var(--color-neutral-light);">
        ✓ Empaque aromático de lavanda + Certificado incluido
      </div>
    </div>

    <!-- Medios de Pago Aceptados -->
    <div style="display: flex; justify-content: center; gap: 0.65rem; margin-bottom: 0.85rem; flex-wrap: wrap;">
      <span style="font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; background: #f3e8ff; color: #6b21a8; border-radius: 4px;">💳 Wompi / Tarjetas</span>
      <span style="font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; background: #e0f2fe; color: #0369a1; border-radius: 4px;">⚡ PSE</span>
      <span style="font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; background: #fdf2f8; color: #be185d; border-radius: 4px;">📱 Nequi / Daviplata</span>
      <span style="font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; background: #ecfdf5; color: #047857; border-radius: 4px;">💵 Contra Entrega</span>
    </div>

    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
      <a href="<?= $baseUrl ?>/checkout.php" id="cart-drawer-checkout-btn" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; font-size: 1rem; padding: 0.85rem 1rem; box-shadow: 0 4px 14px rgba(123,44,191,0.35);">
        <svg style="width: 1.15rem; height: 1.15rem; margin-right: 0.4rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        Comprar Ahora (Finalizar Compra) &rarr;
      </a>
      <a href="<?= $baseUrl ?>/cart.php" class="btn btn-outlined btn-sm" style="width: 100%; justify-content: center; font-weight: 600;">
        Ver Canasta Detallada
      </a>
    </div>
  </div>
</aside>

