// assets/js/main.js - Interactividad frontend para Maraña Crochet
document.addEventListener('DOMContentLoaded', () => {
  const baseUrl = document.querySelector('.site-header a')?.getAttribute('href')?.replace('/index.php', '') || '';

  // 1. Manejo del Cajón del Carrito (Drawer)
  const cartDrawer = document.getElementById('cart-drawer');
  const cartOverlay = document.getElementById('cart-drawer-overlay');
  const openCartBtn = document.getElementById('open-cart-btn');
  const closeCartBtn = document.getElementById('close-cart-btn');

  function openCart() {
    if (cartDrawer && cartOverlay) {
      cartDrawer.classList.add('open');
      cartOverlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeCart() {
    if (cartDrawer && cartOverlay) {
      cartDrawer.classList.remove('open');
      cartOverlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  if (openCartBtn) openCartBtn.addEventListener('click', openCart);
  if (closeCartBtn) closeCartBtn.addEventListener('click', closeCart);
  if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

  // Exponer a nivel global para llamadas inline
  window.openCart = openCart;
  window.closeCart = closeCart;

  // 2. Operaciones de Carrito vía AJAX
  window.addToCartAjax = async function(productId, quantity = 1, showDrawer = true) {
    try {
      const res = await fetch(`${baseUrl}/api/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', productId, quantity })
      });
      const data = await res.json();
      if (data.success) {
        updateCartBadge(data.cartCount);
        updateCartDrawerHtml(data.items, data.subtotal);
        showToast('¡Amigurumi apartado!', 'Se ha agregado correctamente a tu canasta.', 'success');
        if (showDrawer) openCart();
      }
    } catch (err) {
      console.error('Error agregando al carrito:', err);
    }
  };

  window.updateItemQuantity = async function(productId, delta) {
    try {
      const res = await fetch(`${baseUrl}/api/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', productId, delta })
      });
      const data = await res.json();
      if (data.success) {
        updateCartBadge(data.cartCount);
        updateCartDrawerHtml(data.items, data.subtotal);
        // Si estamos en la página cart.php, recargar para actualizar totales
        if (window.location.pathname.endsWith('cart.php')) {
          window.location.reload();
        }
      }
    } catch (err) {
      console.error('Error actualizando cantidad:', err);
    }
  };

  window.removeCartItem = async function(productId) {
    try {
      const res = await fetch(`${baseUrl}/api/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', productId })
      });
      const data = await res.json();
      if (data.success) {
        updateCartBadge(data.cartCount);
        updateCartDrawerHtml(data.items, data.subtotal);
        showToast('Producto eliminado', 'El artículo fue retirado de tu canasta.', 'info');
        if (window.location.pathname.endsWith('cart.php')) {
          window.location.reload();
        }
      }
    } catch (err) {
      console.error('Error eliminando del carrito:', err);
    }
  };

  // 3. Manejo de Favoritos
  window.toggleFavoriteAjax = async function(productId, btnEl) {
    try {
      const res = await fetch(`${baseUrl}/api/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_fav', productId })
      });
      const data = await res.json();
      if (data.success) {
        const favCounter = document.getElementById('fav-counter');
        if (favCounter) {
          favCounter.textContent = data.favCount;
          favCounter.style.display = data.favCount > 0 ? 'flex' : 'none';
        }
        if (btnEl) {
          btnEl.classList.toggle('active', data.isFav);
          const svg = btnEl.querySelector('svg');
          if (svg) svg.style.fill = data.isFav ? 'currentColor' : 'none';
        }
        showToast(
          data.isFav ? '¡Guardado en Favoritos! ❤️' : 'Removido de Favoritos',
          data.isFav ? 'Podrás consultar tus amigurumis preferidos cuando quieras.' : '',
          'info'
        );
      }
    } catch (err) {
      console.error('Error en favoritos:', err);
    }
  };

  // 4. Actualización del DOM del Drawer
  function updateCartBadge(count) {
    const badge = document.getElementById('cart-counter');
    if (badge) badge.textContent = count;
  }

  function updateCartDrawerHtml(items, subtotal) {
    const body = document.getElementById('cart-drawer-items');
    const subtotalEl = document.getElementById('cart-drawer-subtotal');
    if (subtotalEl) {
      subtotalEl.textContent = '$' + new Intl.NumberFormat('es-CL').format(subtotal);
    }

    if (!body) return;

    if (!items || Object.keys(items).length === 0) {
      body.innerHTML = `
        <div style="text-align: center; padding: 3rem 1rem; color: var(--color-neutral-light);">
          <svg style="width: 3rem; height: 3rem; margin-bottom: 1rem; opacity: 0.4;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M8 12h8"></path>
          </svg>
          <p style="font-weight: 600; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">Tu canasta está vacía</p>
          <p style="font-size: 0.85rem;">Explora las criaturas cósmicas y añade tus amigurumis favoritos.</p>
          <a href="${baseUrl}/index.php#catalogo" class="btn btn-outlined btn-sm" style="margin-top: 1rem;">Ver Catálogo</a>
        </div>
      `;
      const footer = document.querySelector('.cart-drawer-footer');
      if (footer) footer.style.display = 'none';
      return;
    }

    let html = '';
    for (const id in items) {
      const item = items[id];
      const itemTotal = item.price * item.quantity;
      html += `
        <div class="cart-item" data-product-id="${item.id}">
          <img src="${item.image}" alt="${item.name}" class="cart-item-img">
          <div style="flex: 1; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <h4 style="font-size: 0.88rem; font-weight: 700; line-height: 1.25; margin-bottom: 0.2rem;">${item.name}</h4>
              <button type="button" class="btn-remove-item" onclick="removeCartItem('${item.id}')" style="background: none; border: none; color: var(--color-neutral-light); cursor: pointer; padding: 0.1rem;" title="Eliminar">&times;</button>
            </div>
            <div style="font-size: 0.75rem; color: var(--color-neutral-light); margin-bottom: 0.5rem;">SKU: ${item.sku}</div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
              <div style="display: inline-flex; align-items: center; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-sm); overflow: hidden;">
                <button type="button" onclick="updateItemQuantity('${item.id}', -1)" style="padding: 0.2rem 0.5rem; background: var(--color-neutral-bg); border: none; cursor: pointer;">-</button>
                <span style="padding: 0.2rem 0.6rem; font-size: 0.85rem; font-weight: 600;">${item.quantity}</span>
                <button type="button" onclick="updateItemQuantity('${item.id}', 1)" style="padding: 0.2rem 0.5rem; background: var(--color-neutral-bg); border: none; cursor: pointer;">+</button>
              </div>
              <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-neutral-dark);">$${new Intl.NumberFormat('es-CL').format(itemTotal)}</span>
            </div>
          </div>
        </div>
      `;
    }
    body.innerHTML = html;
  }

  // 5. Función de Toast
  window.showToast = function(title, desc = '', type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast-card ${type}`;
    toast.innerHTML = `
      <div class="toast-title">${title}</div>
      ${desc ? `<div class="toast-desc">${desc}</div>` : ''}
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  };

  // Auto-desvanecer toasts existentes de PHP
  const existingToasts = document.querySelectorAll('.toast-card');
  existingToasts.forEach((t) => {
    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transform = 'translateX(100%)';
      t.style.transition = 'all 0.3s ease';
      setTimeout(() => t.remove(), 300);
    }, 4500);
  });

  // 6. Galería de Fotos en Ficha de Producto
  const mainPhoto = document.getElementById('main-product-photo');
  const thumbs = document.querySelectorAll('.gallery-thumb');
  thumbs.forEach((thumb) => {
    thumb.addEventListener('click', () => {
      thumbs.forEach((t) => t.classList.remove('active'));
      thumb.classList.add('active');
      if (mainPhoto) {
        mainPhoto.src = thumb.getAttribute('data-full');
      }
    });
  });
});
