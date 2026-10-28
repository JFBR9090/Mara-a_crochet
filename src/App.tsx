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

  // Customer & Admin Auth State
  const [isCustomerAuthenticated, setIsCustomerAuthenticated] = useState<boolean>(false);
  const [customerUser, setCustomerUser] = useState<{ name: string; email: string } | null>(null);
  const [showAuthModal, setShowAuthModal] = useState<boolean>(() => {
    try {
      return !sessionStorage.getItem('marana_auth_dismissed');
    } catch {
      return true;
    }
  });
  const [authRoleTab, setAuthRoleTab] = useState<'user' | 'admin'>('user');
  const [authUserSubTab, setAuthUserSubTab] = useState<'login' | 'register'>('login');
  const [isAdminAuthenticated, setIsAdminAuthenticated] = useState<boolean>(false);
  const [pendingAdminView, setPendingAdminView] = useState<string>('backoffice-orders');
  
  // Inputs
  const [adminUsernameInput, setAdminUsernameInput] = useState<string>('admin');
  const [adminPasswordInput, setAdminPasswordInput] = useState<string>('');
  const [adminLoginError, setAdminLoginError] = useState<string>('');
  const [custEmailInput, setCustEmailInput] = useState<string>('');
  const [custPasswordInput, setCustPasswordInput] = useState<string>('');
  const [custNameInput, setCustNameInput] = useState<string>('');
  const [custPhoneInput, setCustPhoneInput] = useState<string>('');
  const [custCityInput, setCustCityInput] = useState<string>('');

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
      setAuthRoleTab('admin');
      setShowAuthModal(true);
      return;
    }
    if (view === 'checkout' && !isCustomerAuthenticated) {
      showToast(
        'Inicio de sesión obligatorio para comprar 🧶',
        'Como ingresaste como invitado, debes iniciar sesión o registrarte para completar el pedido.',
        'info'
      );
      setAuthRoleTab('user');
      setAuthUserSubTab('login');
      setShowAuthModal(true);
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
      setShowAuthModal(false);
      setCurrentView(pendingAdminView);
      showToast('¡Bienvenida al Taller!', 'Sesión de administración iniciada.', 'success');
    } else {
      setAdminLoginError('Credenciales no válidas. Prueba con admin / admin123');
    }
  };

  const handleCustomerLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!custEmailInput || !custPasswordInput) return;
    const namePart = custEmailInput.split('@')[0];
    const cleanName = namePart.charAt(0).toUpperCase() + namePart.slice(1);
    setIsCustomerAuthenticated(true);
    setCustomerUser({ name: cleanName, email: custEmailInput });
    setShowAuthModal(false);
    showToast('¡Bienvenida/o de nuevo! 🧶', `Sesión iniciada como ${cleanName}.`, 'success');
  };

  const handleCustomerRegisterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!custNameInput || !custEmailInput) return;
    setIsCustomerAuthenticated(true);
    setCustomerUser({ name: custNameInput, email: custEmailInput });
    setShowAuthModal(false);
    showToast('¡Cuenta Creada con Éxito! 💜', `Bienvenida a la familia Maraña, ${custNameInput}.`, 'success');
  };

  const handleContinueAsGuest = () => {
    try {
      sessionStorage.setItem('marana_auth_dismissed', '1');
    } catch {}
    setShowAuthModal(false);
    showToast(
      'Bienvenida/o como Invitada/o 🧶',
      'Puedes recorrer la tienda. Recuerda que para comprar será necesario registrarte o iniciar sesión.',
      'info'
    );
  };

  const handleAdminLogout = () => {
    setIsAdminAuthenticated(false);
    setCurrentView('catalog');
    showToast('Sesión Finalizada', 'Has cerrado la sesión administrativa.', 'info');
  };

  const handleProceedToCheckout = () => {
    setIsCartOpen(false);
    if (!isCustomerAuthenticated) {
      showToast(
        'Inicio de sesión obligatorio para comprar 🧶',
        'Como ingresaste como invitado, debes iniciar sesión o registrarte para completar el pedido.',
        'info'
      );
      setAuthRoleTab('user');
      setAuthUserSubTab('login');
      setShowAuthModal(true);
      return;
    }
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
        onOpenAuth={() => {
          setAuthRoleTab('user');
          setAuthUserSubTab('login');
          setShowAuthModal(true);
        }}
        onOpenAdminAuth={() => {
          setAuthRoleTab('admin');
          setShowAuthModal(true);
        }}
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

      {/* Modal / Pestaña de Bienvenida y Acceso (Admin vs Usuario) */}
      {showAuthModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/65 backdrop-blur-md p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-primary/25 relative max-h-[90vh] overflow-y-auto">
            {/* Botón Cerrar */}
            <button
              type="button"
              onClick={handleContinueAsGuest}
              className="absolute top-4 right-4 w-9 h-9 rounded-full bg-neutral-100 hover:bg-red-50 hover:text-red-600 text-neutral-500 flex items-center justify-center text-lg font-bold transition-all"
              title="Continuar como invitado"
            >
              &times;
            </button>

            {/* Cabecera Maraña */}
            <div className="text-center mb-5">
              <div className="w-16 h-16 rounded-full bg-primary/10 border-2 border-primary mx-auto flex items-center justify-center mb-2 overflow-hidden shadow-lg">
                <span className="text-2xl">🧶</span>
              </div>
              <h2 className="text-2xl font-extrabold text-[#1d1128]">Maraña Crochet</h2>
              <p className="text-xs font-semibold text-primary mt-0.5">
                Amigurumis con ternura cósmica · Neiva, Huila 🇨🇴
              </p>
              <p className="text-xs text-neutral-500 mt-2">
                Elige cómo deseas ingresar al taller y catálogo artesanal:
              </p>
            </div>

            {/* Switcher Superior de Roles (Pestaña Arribita: Admin vs Usuario) */}
            <div className="grid grid-cols-2 gap-2 bg-neutral-100 p-1.5 rounded-xl mb-5 border border-neutral-200">
              <button
                type="button"
                onClick={() => setAuthRoleTab('user')}
                className={`flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition-all ${
                  authRoleTab === 'user'
                    ? 'bg-gradient-to-r from-primary to-[#4D1282] text-white shadow-md'
                    : 'text-neutral-600 hover:text-neutral-900'
                }`}
              >
                <span className="text-base">👤</span>
                <span className="text-left leading-tight">
                  <span className="block text-xs font-extrabold">Usuario / Cliente</span>
                  <span className="block text-[10px] opacity-80 font-normal">Compras y catálogo</span>
                </span>
              </button>

              <button
                type="button"
                onClick={() => setAuthRoleTab('admin')}
                className={`flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition-all ${
                  authRoleTab === 'admin'
                    ? 'bg-gradient-to-r from-primary to-[#4D1282] text-white shadow-md'
                    : 'text-neutral-600 hover:text-neutral-900'
                }`}
              >
                <span className="text-base">🛡️</span>
                <span className="text-left leading-tight">
                  <span className="block text-xs font-extrabold">Administrador</span>
                  <span className="block text-[10px] opacity-80 font-normal">Panel del Taller</span>
                </span>
              </button>
            </div>

            {/* PANEL USUARIO / CLIENTE */}
            {authRoleTab === 'user' && (
              <div>
                {/* Subpestañas: Iniciar Sesión / Registrarse */}
                <div className="flex border-b border-neutral-200 mb-4">
                  <button
                    type="button"
                    onClick={() => setAuthUserSubTab('login')}
                    className={`flex-1 py-2 text-xs font-bold transition-all border-b-2 ${
                      authUserSubTab === 'login'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-neutral-400 hover:text-neutral-600'
                    }`}
                  >
                    Iniciar Sesión
                  </button>
                  <button
                    type="button"
                    onClick={() => setAuthUserSubTab('register')}
                    className={`flex-1 py-2 text-xs font-bold transition-all border-b-2 ${
                      authUserSubTab === 'register'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-neutral-400 hover:text-neutral-600'
                    }`}
                  >
                    Registrarse (Crear Cuenta)
                  </button>
                </div>

                {/* Subpanel 1: Login */}
                {authUserSubTab === 'login' && (
                  <form onSubmit={handleCustomerLoginSubmit} className="space-y-3.5">
                    <div>
                      <label className="block text-xs font-bold text-neutral-700 mb-1">
                        Correo Electrónico
                      </label>
                      <input
                        type="email"
                        value={custEmailInput}
                        onChange={(e) => setCustEmailInput(e.target.value)}
                        placeholder="ejemplo@gmail.com"
                        required
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                      />
                    </div>
                    <div>
                      <div className="flex justify-between items-center mb-1">
                        <label className="text-xs font-bold text-neutral-700">Contraseña</label>
                        <span className="text-[11px] text-primary font-semibold">Demo: marana123</span>
                      </div>
                      <input
                        type="password"
                        value={custPasswordInput}
                        onChange={(e) => setCustPasswordInput(e.target.value)}
                        placeholder="••••••••"
                        required
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                      />
                    </div>
                    <button
                      type="submit"
                      className="w-full py-2.5 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold shadow-md transition-all"
                    >
                      Ingresar a Mi Cuenta &rarr;
                    </button>
                  </form>
                )}

                {/* Subpanel 2: Registro */}
                {authUserSubTab === 'register' && (
                  <form onSubmit={handleCustomerRegisterSubmit} className="space-y-3">
                    <div>
                      <label className="block text-xs font-bold text-neutral-700 mb-1">Nombre Completo *</label>
                      <input
                        type="text"
                        value={custNameInput}
                        onChange={(e) => setCustNameInput(e.target.value)}
                        placeholder="Ej: Valentina Castro"
                        required
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                      <div>
                        <label className="block text-xs font-bold text-neutral-700 mb-1">Correo *</label>
                        <input
                          type="email"
                          value={custEmailInput}
                          onChange={(e) => setCustEmailInput(e.target.value)}
                          placeholder="tu@correo.com"
                          required
                          className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-bold text-neutral-700 mb-1">WhatsApp / Teléfono *</label>
                        <input
                          type="tel"
                          value={custPhoneInput}
                          onChange={(e) => setCustPhoneInput(e.target.value)}
                          placeholder="312 456 7890"
                          required
                          className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                        />
                      </div>
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-neutral-700 mb-1">Ciudad *</label>
                      <input
                        type="text"
                        value={custCityInput}
                        onChange={(e) => setCustCityInput(e.target.value)}
                        placeholder="Ej: Neiva, Bogotá"
                        required
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                      />
                    </div>
                    <button
                      type="submit"
                      className="w-full py-2.5 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold shadow-md transition-all mt-1"
                    >
                      Crear Cuenta & Comenzar &rarr;
                    </button>
                  </form>
                )}

                {/* Opción de Invitado con Advertencia de Compra Obligatoria */}
                <div className="mt-5 p-3.5 rounded-xl bg-purple-50/80 border border-dashed border-primary/30 flex flex-col gap-2.5">
                  <div className="flex gap-2.5 items-start">
                    <span className="text-xl leading-none shrink-0">🛍️</span>
                    <div>
                      <strong className="block text-xs font-bold text-neutral-800">
                        ¿Prefieres explorar primero como invitado?
                      </strong>
                      <p className="text-[11px] text-neutral-600 leading-snug mt-0.5">
                        Puedes recorrer el catálogo y armar tu canasta libremente.{' '}
                        <span className="block mt-1 font-bold text-[#4D1282]">
                          ⚠️ Para realizar o finalizar una compra será <u>obligatorio</u> iniciar sesión o registrarte.
                        </span>
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={handleContinueAsGuest}
                    className="w-full py-2 border border-primary text-primary hover:bg-primary hover:text-white rounded-lg text-xs font-bold transition-all"
                  >
                    Continuar como Invitado y Explorar Tienda &rarr;
                  </button>
                </div>
              </div>
            )}

            {/* PANEL ADMINISTRADOR */}
            {authRoleTab === 'admin' && (
              <div>
                <div className="p-3 bg-neutral-100 border-l-4 border-primary rounded-md mb-4 flex items-center gap-2.5">
                  <span className="text-lg">🔒</span>
                  <div>
                    <strong className="block text-xs text-neutral-800 font-bold">
                      Panel Interno de Administración · Taller Maraña
                    </strong>
                    <p className="text-[11px] text-neutral-500">
                      Gestión de pedidos, inventario de lanas y auditoría.
                    </p>
                  </div>
                </div>

                {adminLoginError && (
                  <div className="mb-3 p-2.5 bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs flex items-center gap-2">
                    <span className="text-sm">⚠️</span>
                    <span>{adminLoginError}</span>
                  </div>
                )}

                <form onSubmit={handleAdminLoginSubmit} className="space-y-3.5">
                  <div>
                    <label className="block text-xs font-bold text-neutral-700 mb-1">
                      Usuario o Correo Administrativo
                    </label>
                    <input
                      type="text"
                      value={adminUsernameInput}
                      onChange={(e) => setAdminUsernameInput(e.target.value)}
                      placeholder="admin o taller@maranacrochet.co"
                      required
                      className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                    />
                  </div>
                  <div>
                    <div className="flex justify-between items-center mb-1">
                      <label className="text-xs font-bold text-neutral-700">Contraseña</label>
                      <span className="text-[11px] text-primary font-semibold">Demo: admin123</span>
                    </div>
                    <input
                      type="password"
                      value={adminPasswordInput}
                      onChange={(e) => setAdminPasswordInput(e.target.value)}
                      placeholder="••••••••"
                      required
                      className="w-full px-3 py-2 border border-neutral-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary/40"
                    />
                  </div>
                  <button
                    type="submit"
                    className="w-full py-2.5 bg-[#1d1128] hover:bg-[#2d1c3d] text-white rounded-lg text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2"
                  >
                    <span>Ingresar al Panel del Taller &rarr;</span>
                  </button>
                </form>

                <div className="mt-4 text-center">
                  <button
                    type="button"
                    onClick={() => setAuthRoleTab('user')}
                    className="text-xs text-neutral-500 hover:text-primary font-medium"
                  >
                    &larr; Volver a Vista de Cliente
                  </button>
                </div>
              </div>
            )}

            {/* Pie del Modal */}
            <div className="mt-6 pt-3 border-t border-neutral-200 flex justify-between items-center text-[10px] text-neutral-400">
              <span>🧶 Maraña Crochet · Neiva, Huila (Colombia)</span>
              <span>🔒 Conexión segura</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

