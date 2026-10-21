import { useState, useEffect } from 'react';
import { maranaApi } from './services/api';
import { Header } from './components/Header';
import { Footer } from './components/Footer';
import { CartDrawer } from './components/CartDrawer';
import { StorefrontView } from './components/StorefrontView';
import { ProductDetailView } from './components/ProductDetailView';
import { CheckoutView } from './components/CheckoutView';
import { OrderSuccessView } from './components/OrderSuccessView';
import { BackofficeOrdersView } from './components/BackofficeOrdersView';
import { BackofficeInventoryView } from './components/BackofficeInventoryView';
import { BackofficeReviewsView } from './components/BackofficeReviewsView';
import { Toast } from './components/Toast';

import { PRODUCTS as INITIAL_PRODUCTS } from './data/products';
import { INITIAL_ORDERS } from './data/orders';
import { INITIAL_REVIEWS } from './data/reviews';
import { CartItem, Order, Product, Review } from './types';

export default function App() {
  // Navigation & View State
  const [currentView, setCurrentView] = useState<string>('catalog');
  const [products, setProducts] = useState<Product[]>(INITIAL_PRODUCTS);
  const [selectedProduct, setSelectedProduct] = useState<Product>(INITIAL_PRODUCTS[0]);
  const [orders, setOrders] = useState<Order[]>(INITIAL_ORDERS);
  const [reviews, setReviews] = useState<Review[]>(INITIAL_REVIEWS);
  const [lastPlacedOrder, setLastPlacedOrder] = useState<Order | null>(null);

  // Cart & Favorites State
  const [cartItems, setCartItems] = useState<CartItem[]>([
    { product: INITIAL_PRODUCTS[0], quantity: 1 },
  ]);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [favorites, setFavorites] = useState<string[]>(['lulu-aranita']);
  const [searchQuery, setSearchQuery] = useState<string>('');

  // Global Toast
  const [toast, setToast] = useState<{
    message: string;
    subMessage?: string;
    type?: 'success' | 'info' | 'error';
  } | null>(null);

  const showToast = (
    message: string,
    subMessage?: string,
    type: 'success' | 'info' | 'error' = 'success'
  ) => {
    setToast({ message, subMessage, type });
  };

  // Carga inicial desde base de datos MySQL en XAMPP
  useEffect(() => {
    let isMounted = true;
    async function initDatabaseData() {
      try {
        const [loadedProducts, loadedOrders, loadedReviews] = await Promise.all([
          maranaApi.getProducts(),
          maranaApi.getOrders(),
          maranaApi.getReviews(),
        ]);
        if (isMounted) {
          if (loadedProducts && loadedProducts.length > 0) {
            setProducts(loadedProducts);
            setSelectedProduct((prev) => {
              const stillExists = loadedProducts.find((p) => p.id === prev.id);
              return stillExists || loadedProducts[0];
            });
          }
          if (loadedOrders && loadedOrders.length > 0) {
            setOrders(loadedOrders);
          }
          if (loadedReviews && loadedReviews.length > 0) {
            setReviews(loadedReviews);
          }
        }
      } catch (err) {
        console.warn('Error inicializando datos desde XAMPP MySQL, usando datos locales:', err);
      }
    }
    initDatabaseData();
    return () => {
      isMounted = false;
    };
  }, []);

  // Cart Handlers
  const handleAddToCart = (product: Product, quantity = 1) => {
    setCartItems((prev) => {
      const existing = prev.find((item) => item.product.id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.product.id === product.id
            ? { ...item, quantity: item.quantity + quantity }
            : item
        );
      }
      return [...prev, { product, quantity }];
    });

    showToast(
      `¡${product.name} agregado!`,
      `${quantity} unidad(es) reservada(s) en tu carrito mágico.`,
      'success'
    );
  };

  const handleUpdateQuantity = (productId: string, delta: number) => {
    setCartItems((prev) =>
      prev
        .map((item) => {
          if (item.product.id === productId) {
            const newQty = item.quantity + delta;
            return newQty > 0 ? { ...item, quantity: newQty } : null;
          }
          return item;
        })
        .filter(Boolean) as CartItem[]
    );
  };

  const handleRemoveItem = (productId: string) => {
    setCartItems((prev) => prev.filter((item) => item.product.id !== productId));
    showToast('Producto eliminado', 'El artículo fue retirado de tu carrito.', 'info');
  };

  const handleQuickAdopt = (product: Product) => {
    handleAddToCart(product, 1);
    setIsCartOpen(true);
  };

  const handleToggleFavorite = (product: Product) => {
    setFavorites((prev) => {
      const isFav = prev.includes(product.id);
      const next = isFav ? prev.filter((id) => id !== product.id) : [...prev, product.id];
      showToast(
        !isFav ? `¡${product.name} en Favoritos! ❤️` : 'Removido de Favoritos',
        !isFav ? 'Podrás consultar tus amigurumis preferidos cuando quieras.' : undefined,
        'info'
      );
      return next;
    });
  };

  // Admin Auth State
  const [isAdminAuthenticated, setIsAdminAuthenticated] = useState<boolean>(false);
  const [showAdminLoginModal, setShowAdminLoginModal] = useState<boolean>(false);
  const [pendingAdminView, setPendingAdminView] = useState<string>('backoffice-orders');
  const [adminUsernameInput, setAdminUsernameInput] = useState<string>('admin');
  const [adminPasswordInput, setAdminPasswordInput] = useState<string>('');
  const [adminLoginError, setAdminLoginError] = useState<string>('');

  // Navigation Handlers
  const handleSelectProduct = (product: Product) => {
    setSelectedProduct(product);
    setCurrentView('product-detail');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleNavigate = (view: string) => {
    if (view.startsWith('backoffice') && !isAdminAuthenticated) {
      setPendingAdminView(view);
      setAdminLoginError('');
      setShowAdminLoginModal(true);
      return;
    }
    setCurrentView(view);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleAdminLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const u = adminUsernameInput.trim().toLowerCase();
    const p = adminPasswordInput.trim();
    if (
      (u === 'admin' || u === 'taller@maranacrochet.co') &&
      (p === 'admin' || p === 'admin123' || p === 'marana2026')
    ) {
      setIsAdminAuthenticated(true);
      setShowAdminLoginModal(false);
      setCurrentView(pendingAdminView);
      showToast('¡Bienvenida al Taller!', 'Sesión de administración iniciada.', 'success');
    } else {
      setAdminLoginError('Credenciales no válidas. Prueba con admin / admin123');
    }
  };

  const handleAdminLogout = () => {
    setIsAdminAuthenticated(false);
    setCurrentView('catalog');
    showToast('Sesión Finalizada', 'Has cerrado la sesión administrativa.', 'info');
  };

  const handleProceedToCheckout = () => {
    setIsCartOpen(false);
    setCurrentView('checkout');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Order Placement
  const handleOrderPlaced = async (newOrder: Order) => {
    setOrders((prev) => [newOrder, ...prev]);
    setLastPlacedOrder(newOrder);

    // Deduct stock from products
    setProducts((prev) =>
      prev.map((prod) => {
        const boughtItem = newOrder.items.find((i) => i.productId === prod.id);
        if (boughtItem) {
          return {
            ...prod,
            stock: Math.max(0, prod.stock - boughtItem.quantity),
          };
        }
        return prod;
      })
    );

    // Empty cart
    setCartItems([]);
    setCurrentView('order-success');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    showToast('¡Pedido Registrado con Éxito!', `Código de orden: ${newOrder.id}`, 'success');

    // Sincronizar en base de datos MySQL en XAMPP
    await maranaApi.createOrder(newOrder);
  };

  // Backoffice Actions
  const handleUpdateOrderStatus = async (orderId: string, status: Order['status'], note?: string) => {
    setOrders((prev) =>
      prev.map((order) => {
        if (order.id === orderId) {
          const newAudit = note
            ? [
                ...order.auditLog,
                {
                  time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                  note: note,
                  type: 'info' as const,
                },
              ]
            : order.auditLog;
          return { ...order, status, auditLog: newAudit };
        }
        return order;
      })
    );

    // Sincronizar en base de datos MySQL en XAMPP
    await maranaApi.updateOrderStatus(orderId, status, note);
  };

  const handleUpdateProductStock = async (productId: string, newStock: number) => {
    setProducts((prev) =>
      prev.map((p) => (p.id === productId ? { ...p, stock: newStock } : p))
    );
    await maranaApi.updateProductStock(productId, newStock);
  };

  const handleUpdateProductPrice = async (productId: string, newPrice: number) => {
    setProducts((prev) =>
      prev.map((p) => (p.id === productId ? { ...p, price: newPrice } : p))
    );
    await maranaApi.updateProductPrice(productId, newPrice);
  };

  const handleUpdateReviewStatus = async (
    reviewId: string,
    status: Review['status']
  ) => {
    setReviews((prev) =>
      prev.map((r) => (r.id === reviewId ? { ...r, status } : r))
    );
    await maranaApi.updateReviewStatus(reviewId, status);
  };

  const handleAddReviewResponse = async (reviewId: string, responseText: string) => {
    const reply = {
      author: 'Taller Maraña Crochet',
      role: 'Maestra Artesana',
      date: 'Hoy',
      text: responseText,
    };
    setReviews((prev) =>
      prev.map((r) =>
        r.id === reviewId
          ? {
              ...r,
              workshopReply: reply,
            }
          : r
      )
    );
    await maranaApi.addReviewReply(reviewId, reply);
  };

  const cartTotalCount = cartItems.reduce((acc, item) => acc + item.quantity, 0);

  return (
    <div className="min-h-screen flex flex-col bg-[#faf8fc] text-[#1d1a20] font-sans antialiased selection:bg-[#f1dbff] selection:text-[#6100a4]">
      {/* Primary Navigation Bar */}
      <Header
        currentView={currentView}
        onNavigate={handleNavigate}
        cartCount={cartTotalCount}
        onOpenCart={() => setIsCartOpen(true)}
        favoritesCount={favorites.length}
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onOpenAdminAuth={() => handleNavigate('backoffice-orders')}
      />

      {/* Main View Router */}
      <div className="flex-1">
        {currentView === 'catalog' && (
          <StorefrontView
            products={products}
            onSelectProduct={handleSelectProduct}
            onAddToCart={(prod) => handleAddToCart(prod, 1)}
            onQuickAdopt={handleQuickAdopt}
            favorites={favorites}
            onToggleFavorite={handleToggleFavorite}
            searchQuery={searchQuery}
            onSearchChange={setSearchQuery}
            onShowToast={showToast}
          />
        )}

        {currentView === 'product-detail' && (
          <ProductDetailView
            product={selectedProduct}
            allProducts={products}
            onSelectProduct={handleSelectProduct}
            onAddToCart={handleAddToCart}
            onBuyNow={(prod, qty) => {
              handleAddToCart(prod, qty);
              handleProceedToCheckout();
            }}
            onBackToCatalog={() => handleNavigate('catalog')}
            isFavorite={favorites.includes(selectedProduct.id)}
            onToggleFavorite={handleToggleFavorite}
          />
        )}

        {currentView === 'checkout' && (
          <CheckoutView
            cartItems={cartItems}
            onBackToStore={() => handleNavigate('catalog')}
            onOrderPlaced={handleOrderPlaced}
          />
        )}

        {currentView === 'order-success' && lastPlacedOrder && (
          <OrderSuccessView
            order={lastPlacedOrder}
            onBackToStore={() => handleNavigate('catalog')}
            onGoToBackoffice={() => handleNavigate('backoffice-orders')}
          />
        )}

        {currentView === 'backoffice-orders' && (
          <BackofficeOrdersView
            orders={orders}
            onUpdateOrderStatus={handleUpdateOrderStatus}
            onNavigateToView={handleNavigate}
            onShowToast={showToast}
          />
        )}

        {currentView === 'backoffice-inventory' && (
          <BackofficeInventoryView
            products={products}
            onUpdateProductStock={handleUpdateProductStock}
            onUpdateProductPrice={handleUpdateProductPrice}
            onNavigateToView={handleNavigate}
            onShowToast={showToast}
          />
        )}

        {currentView === 'backoffice-reviews' && (
          <BackofficeReviewsView
            reviews={reviews}
            onUpdateReviewStatus={handleUpdateReviewStatus}
            onAddReviewResponse={handleAddReviewResponse}
            onNavigateToView={handleNavigate}
            onShowToast={showToast}
          />
        )}
      </div>

      {/* Global Footer (only shown on client views) */}
      {!currentView.startsWith('backoffice') && (
        <Footer onNavigate={handleNavigate} />
      )}

      {/* Slide-over Cart Drawer */}
      <CartDrawer
        isOpen={isCartOpen}
        onClose={() => setIsCartOpen(false)}
        items={cartItems}
        onUpdateQuantity={handleUpdateQuantity}
        onRemoveItem={handleRemoveItem}
        onProceedToCheckout={handleProceedToCheckout}
      />

      {/* Floating Toast Notification */}
      {toast && (
        <Toast
          message={toast.message}
          subMessage={toast.subMessage}
          type={toast.type}
          onClose={() => setToast(null)}
        />
      )}

      {/* Admin Login Modal */}
      {showAdminLoginModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-primary/20 animate-in fade-in zoom-in-95 duration-200">
            <div className="text-center mb-6">
              <div className="w-14 h-14 rounded-full bg-primary/10 text-primary mx-auto flex items-center justify-center mb-3">
                <span className="material-symbols-outlined text-3xl">lock</span>
              </div>
              <h3 className="font-bold text-xl text-neutral-800">Acceso al Panel del Taller</h3>
              <p className="text-xs text-neutral-500 mt-1">
                📍 Taller Maraña Crochet · Neiva, Huila (Colombia)
              </p>
            </div>

            {adminLoginError && (
              <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs flex items-center gap-2">
                <span className="material-symbols-outlined text-sm">error</span>
                <span>{adminLoginError}</span>
              </div>
            )}

            <form onSubmit={handleAdminLoginSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-neutral-700 mb-1">
                  Usuario o Correo
                </label>
                <input
                  type="text"
                  value={adminUsernameInput}
                  onChange={(e) => setAdminUsernameInput(e.target.value)}
                  placeholder="admin"
                  required
                  className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-neutral-700 mb-1">
                  Contraseña
                </label>
                <input
                  type="password"
                  value={adminPasswordInput}
                  onChange={(e) => setAdminPasswordInput(e.target.value)}
                  placeholder="••••••••"
                  required
                  autoFocus
                  className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                />
              </div>

              <div className="p-2.5 bg-primary/5 rounded-lg border border-dashed border-primary/30 text-[11px] text-neutral-600">
                Credenciales taller: <strong>admin</strong> / <strong>admin123</strong>
              </div>

              <div className="flex gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowAdminLoginModal(false)}
                  className="flex-1 px-4 py-2 border border-neutral-200 rounded-lg text-xs font-semibold text-neutral-600 hover:bg-neutral-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="flex-1 px-4 py-2 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary/90 shadow-md"
                >
                  Iniciar Sesión
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

