import { useState } from 'react';
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

  // Navigation Handlers
  const handleSelectProduct = (product: Product) => {
    setSelectedProduct(product);
    setCurrentView('product-detail');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleNavigate = (view: string) => {
    setCurrentView(view);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleProceedToCheckout = () => {
    setIsCartOpen(false);
    setCurrentView('checkout');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Order Placement
  const handleOrderPlaced = (newOrder: Order) => {
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
  };

  // Backoffice Actions
  const handleUpdateOrderStatus = (orderId: string, status: Order['status'], note?: string) => {
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
  };

  const handleUpdateProductStock = (productId: string, newStock: number) => {
    setProducts((prev) =>
      prev.map((p) => (p.id === productId ? { ...p, stock: newStock } : p))
    );
  };

  const handleUpdateProductPrice = (productId: string, newPrice: number) => {
    setProducts((prev) =>
      prev.map((p) => (p.id === productId ? { ...p, price: newPrice } : p))
    );
  };

  const handleUpdateReviewStatus = (
    reviewId: string,
    status: Review['status']
  ) => {
    setReviews((prev) =>
      prev.map((r) => (r.id === reviewId ? { ...r, status } : r))
    );
  };

  const handleAddReviewResponse = (reviewId: string, responseText: string) => {
    setReviews((prev) =>
      prev.map((r) =>
        r.id === reviewId
          ? {
              ...r,
              workshopReply: {
                author: 'Taller Maraña Crochet',
                role: 'Maestra Artesana',
                date: 'Hoy',
                text: responseText,
              },
            }
          : r
      )
    );
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
    </div>
  );
}
