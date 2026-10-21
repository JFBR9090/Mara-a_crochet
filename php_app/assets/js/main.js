// assets/js/main.js - Interactividad frontend para Maraña Crochet (Colombia)
document.addEventListener('DOMContentLoaded', () => {
  const baseUrl = document.querySelector('.site-header a')?.getAttribute('href')?.replace('/index.php', '') || '';

  // Formato COP oficial
  function formatCOP(amount) {
    return '$' + new Intl.NumberFormat('es-CO').format(amount) + ' COP';
  }

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
        showToast('¡Amigurumi apartado!', 'Se ha agregado correctamente a tu canasta de adopción.', 'success');
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
        showToast('Producto eliminado', 'El amigurumi fue retirado de tu canasta.', 'info');
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
    if (badge) {
      badge.textContent = count;
      badge.classList.remove('badge-bump');
      void badge.offsetWidth; // Forzar reflow
      badge.classList.add('badge-bump');
    }
  }

  function updateCartDrawerHtml(items, subtotal) {
    const body = document.getElementById('cart-drawer-items');
    const subtotalEl = document.getElementById('cart-drawer-subtotal');
    if (subtotalEl) {
      subtotalEl.textContent = formatCOP(subtotal);
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
          <a href="${baseUrl}/index.php#catalogo" class="btn btn-outlined btn-sm" style="margin-top: 1rem;" onclick="closeCart()">Ver Catálogo</a>
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
              <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-neutral-dark);">${formatCOP(itemTotal)}</span>
            </div>
          </div>
        </div>
      `;
    }
    body.innerHTML = html;
    const footer = document.querySelector('.cart-drawer-footer');
    if (footer) footer.style.display = 'block';
  }

  // 5. Toast Notification System
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

  // 7. Búsqueda y Filtrado Instantáneo en el Catálogo
  window.handleLiveCatalogSearch = function(query) {
    const q = query.trim().toLowerCase();
    const cards = document.querySelectorAll('#main-product-grid .product-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const text = card.getAttribute('data-name') || '';
      if (!q || text.includes(q)) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    const resultsCountEl = document.getElementById('results-count');
    if (resultsCountEl) resultsCountEl.textContent = visibleCount;

    let noResultsEl = document.getElementById('no-products-box');
    if (visibleCount === 0) {
      if (!noResultsEl) {
        const grid = document.getElementById('main-product-grid');
        noResultsEl = document.createElement('div');
        noResultsEl.id = 'no-products-box';
        noResultsEl.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; background: #FFFFFF; border: 1px solid var(--color-neutral-border); border-radius: var(--radius-md);';
        noResultsEl.innerHTML = `
          <h3 style="margin-bottom: 0.5rem;">No se encontraron amigurumis para "${query}"</h3>
          <p style="color: var(--color-neutral-medium); margin-bottom: 1.5rem;">Prueba con otros términos como 'arañita', 'murciélago', 'algodón' o 'bebé'.</p>
          <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('catalog-search-input').value=''; handleLiveCatalogSearch('');">Ver todas las criaturas</button>
        `;
        grid.appendChild(noResultsEl);
      } else {
        noResultsEl.style.display = '';
      }
    } else if (noResultsEl) {
      noResultsEl.style.display = 'none';
    }
  };

  // 8. Modal de Vista Rápida (Quick View)
  let catalogData = [];
  try {
    const dataEl = document.getElementById('catalog-products-data');
    if (dataEl) {
      catalogData = JSON.parse(dataEl.textContent);
    }
  } catch (e) {
    console.error('Error cargando catálogo:', e);
  }

  window.openQuickView = function(productId) {
    const product = catalogData.find(p => p.id === productId);
    if (!product) return;

    const overlay = document.getElementById('quickview-overlay');
    const modal = document.getElementById('quickview-modal');
    const content = document.getElementById('quickview-content');
    if (!overlay || !modal || !content) return;

    let images = [];
    try {
      images = typeof product.images === 'string' ? JSON.parse(product.images) : product.images;
    } catch(e) {
      images = [];
    }
    const mainImg = images[0]?.url || '';

    let thumbsHtml = '';
    if (images.length > 1) {
      thumbsHtml = '<div class="quickview-thumbs">';
      images.forEach((img, idx) => {
        thumbsHtml += `
          <button type="button" class="quickview-thumb ${idx === 0 ? 'active' : ''}" onclick="document.getElementById('qv-main-img').src='${img.url}'; document.querySelectorAll('.quickview-thumb').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
            <img src="${img.url}" alt="${img.alt || product.name}">
          </button>
        `;
      });
      thumbsHtml += '</div>';
    }

    content.innerHTML = `
      <div class="quickview-img-col">
        <div class="quickview-main-wrapper">
          <img id="qv-main-img" src="${mainImg}" alt="${product.name}">
        </div>
        ${thumbsHtml}
      </div>

      <div class="quickview-info-col">
        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-primary); letter-spacing: 0.05em; margin-bottom: 0.3rem;">
          ${product.categoryLabel} · SKU: ${product.sku}
        </div>
        <h2 style="font-size: 1.55rem; font-weight: 800; color: var(--color-neutral-dark); margin-bottom: 0.25rem;">
          ${product.name}
        </h2>
        <p style="font-size: 0.88rem; font-style: italic; color: var(--color-neutral-light); margin-bottom: 0.85rem;">
          "${product.tagline || 'Tejido artesanalmente en Neiva, Huila (Colombia)'}"
        </p>

        <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 1rem;">
          <span style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark);">
            ${formatCOP(product.price)}
          </span>
          ${product.originalPrice ? `<span style="font-size: 0.95rem; text-decoration: line-through; color: var(--color-neutral-light);">${formatCOP(product.originalPrice)}</span>` : ''}
          ${product.discountBadge ? `<span style="background: var(--color-accent); color: var(--color-neutral-dark); font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: var(--radius-sm);">${product.discountBadge}</span>` : ''}
        </div>

        <p style="font-size: 0.85rem; color: var(--color-neutral-medium); line-height: 1.5; margin-bottom: 1.25rem;">
          ${product.description}
        </p>

        <!-- Ficha Técnica Artesanal -->
        <div class="quickview-specs-box">
          <div><strong>🧵 Tejido:</strong> ${product.craftHours}h a mano</div>
          <div><strong>🌿 Material:</strong> ${product.material}</div>
          <div><strong>📏 Tamaño:</strong> ${product.dimensions}</div>
          <div><strong>🌸 Empaque:</strong> Ramillete de lavanda + Certificado</div>
        </div>

        <!-- Cantidad y Añadir -->
        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.25rem;">
          <div style="display: inline-flex; align-items: center; border: 1.5px solid var(--color-neutral-border); border-radius: var(--radius-sm); overflow: hidden; background: #fff;">
            <button type="button" onclick="const q = document.getElementById('qv-qty'); q.value = Math.max(1, parseInt(q.value) - 1);" style="padding: 0.5rem 0.75rem; background: var(--color-neutral-bg); border: none; cursor: pointer; font-weight: 700;">-</button>
            <input type="number" id="qv-qty" value="1" min="1" max="10" style="width: 2.8rem; text-align: center; border: none; font-weight: 700; font-size: 0.95rem;">
            <button type="button" onclick="const q = document.getElementById('qv-qty'); q.value = parseInt(q.value) + 1;" style="padding: 0.5rem 0.75rem; background: var(--color-neutral-bg); border: none; cursor: pointer; font-weight: 700;">+</button>
          </div>

          <button type="button" class="btn btn-primary" style="flex: 1; padding: 0.75rem 1.25rem; font-size: 0.95rem;" onclick="addToCartAjax('${product.id}', parseInt(document.getElementById('qv-qty').value)); closeQuickView();">
            Adoptar Amigurumi
          </button>
        </div>

        <div style="margin-top: 1rem; text-align: center;">
          <a href="${baseUrl}/product.php?id=${product.id}" style="font-size: 0.82rem; font-weight: 700; color: var(--color-primary); text-decoration: underline;">
            Ver Ficha Completa, Medidas y Reseñas de Clientes &rarr;
          </a>
        </div>
      </div>
    `;

    overlay.classList.add('open');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeQuickView = function() {
    const overlay = document.getElementById('quickview-overlay');
    const modal = document.getElementById('quickview-modal');
    if (overlay && modal) {
      overlay.classList.remove('open');
      modal.classList.remove('open');
      document.body.style.overflow = '';
    }
  };

  // Tecla Escape cierra modales
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeCart();
      closeQuickView();
    }
  });
});
