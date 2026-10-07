import React, { useState } from 'react';
import { MARANA_LOGO } from '../data/products';

interface HeaderProps {
  currentView: string;
  onNavigate: (view: string) => void;
  cartCount: number;
  onOpenCart: () => void;
  favoritesCount: number;
  onOpenAuth?: () => void;
  searchQuery?: string;
  onSearchChange?: (q: string) => void;
  onOpenAdminAuth?: () => void;
}

export const Header: React.FC<HeaderProps> = ({
  currentView,
  onNavigate,
  cartCount,
  onOpenCart,
  favoritesCount,
  onOpenAuth = () => {},
  searchQuery = '',
  onSearchChange = () => {},
  onOpenAdminAuth = () => onNavigate('backoffice-orders'),
}) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);

  return (
    <>
      {/* Top Experience Mode Switcher (Allows seamless review of all 7 target screen states) */}
      <div className="bg-[#241432] text-white text-xs py-1.5 px-4 z-50 relative border-b border-white/10 flex items-center justify-between overflow-x-auto gap-2">
        <div className="flex items-center gap-2 shrink-0">
          <span className="w-2 h-2 rounded-full bg-status-stock-success animate-ping"></span>
          <span className="font-bold text-accent-star-gold tracking-wide uppercase text-[11px]">Modo de Navegación:</span>
        </div>
        <div className="flex items-center gap-1.5 overflow-x-auto scrollbar-none py-0.5">
          <button
            onClick={() => onNavigate('home')}
            className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all shrink-0 ${
              currentView === 'home'
                ? 'bg-primary-container text-white shadow-sm'
                : 'text-white/70 hover:text-white hover:bg-white/10'
            }`}
          >
            Tienda (Home / Catálogo)
          </button>
          <button
            onClick={() => onNavigate('product-lulu')}
            className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all shrink-0 ${
              currentView === 'product-lulu'
                ? 'bg-primary-container text-white shadow-sm'
                : 'text-white/70 hover:text-white hover:bg-white/10'
            }`}
          >
            Ficha Producto (Lulú)
          </button>
          <button
            onClick={() => onNavigate('checkout')}
            className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all shrink-0 ${
              currentView === 'checkout'
                ? 'bg-primary-container text-white shadow-sm'
                : 'text-white/70 hover:text-white hover:bg-white/10'
            }`}
          >
            Checkout (Despacho & Pago)
          </button>
          <button
            onClick={() => onNavigate('client-auth')}
            className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all shrink-0 ${
              currentView === 'client-auth'
                ? 'bg-primary-container text-white shadow-sm'
                : 'text-white/70 hover:text-white hover:bg-white/10'
            }`}
          >
            Login Cliente / Keycloak
          </button>
          <button
            onClick={() => onNavigate('client-recovery')}
            className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all shrink-0 ${
              currentView === 'client-recovery'
                ? 'bg-primary-container text-white shadow-sm'
                : 'text-white/70 hover:text-white hover:bg-white/10'
            }`}
          >
            Recuperar Contraseña
          </button>
          <span className="text-white/30 px-1">|</span>
          <button
            onClick={() => onNavigate('backoffice-orders')}
            className={`px-2.5 py-1 rounded-full text-xs font-bold transition-all shrink-0 ${
              currentView.startsWith('backoffice')
                ? 'bg-secondary-container text-[#2e004d] shadow-sm'
                : 'text-secondary-fixed hover:text-white hover:bg-white/10'
            }`}
          >
            Panel Admin (Backoffice Taller)
          </button>
        </div>
      </div>

      <header className="sticky top-0 w-full z-40 bg-surface/90 backdrop-blur-xl shadow-[0_4px_20px_-2px_rgba(123,44,191,0.08)] transition-all">
        <div className="h-20 max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between gap-4">
          {/* Logo & Brand Identity */}
          <div
            onClick={() => onNavigate('home')}
            className="flex items-center gap-3 shrink-0 cursor-pointer group"
          >
            <img
              alt="Maraña Crochet Logo"
              className="w-10 h-10 rounded-full object-cover shadow-[0_2px_8px_rgba(123,44,191,0.15)] ring-2 ring-primary-container/20 group-hover:scale-105 transition-transform"
              src={MARANA_LOGO}
            />
            <div className="flex flex-col">
              <span className="font-headline-sm text-headline-sm text-primary font-bold tracking-tight">Maraña</span>
              <span className="font-label-sm text-[11px] text-secondary uppercase tracking-widest -mt-1 font-bold">
                Crochet Artesanal
              </span>
            </div>
          </div>

          {/* Navigation Links */}
          <nav className="hidden xl:flex items-center gap-1 p-1 bg-surface-container-low/70 rounded-full">
            <button
              onClick={() => onNavigate('home')}
              className={`px-4 py-2 rounded-full font-label-lg text-sm font-bold transition-colors cursor-pointer ${
                currentView === 'home'
                  ? 'bg-badge-lavender-bg text-primary'
                  : 'text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              Catálogo
            </button>
            <button
              onClick={() => {
                onNavigate('home');
                const el = document.getElementById('catalogo');
                el?.scrollIntoView({ behavior: 'smooth' });
              }}
              className="px-4 py-2 rounded-full font-label-lg text-sm font-bold text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg transition-colors cursor-pointer"
            >
              Colecciones
            </button>
            <button
              onClick={() => {
                onNavigate('home');
                const el = document.getElementById('proceso');
                el?.scrollIntoView({ behavior: 'smooth' });
              }}
              className="px-4 py-2 rounded-full font-label-lg text-sm font-bold text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg transition-colors cursor-pointer"
            >
              Personalizados
            </button>
            <button
              onClick={() => onNavigate('product-lulu')}
              className={`px-4 py-2 rounded-full font-label-lg text-sm font-bold transition-colors cursor-pointer ${
                currentView === 'product-lulu'
                  ? 'bg-badge-lavender-bg text-primary'
                  : 'text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              Lulú Cósmica
            </button>
            <button
              onClick={() => {
                onNavigate('home');
                const el = document.getElementById('proceso');
                el?.scrollIntoView({ behavior: 'smooth' });
              }}
              className="px-4 py-2 rounded-full font-label-lg text-sm font-bold text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg transition-colors cursor-pointer"
            >
              Sobre Nosotros
            </button>
            <button
              onClick={() => {
                window.open('https://wa.me/56987654321', '_blank');
              }}
              className="px-4 py-2 rounded-full font-label-lg text-sm font-bold text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg transition-colors cursor-pointer"
            >
              Contacto
            </button>
          </nav>

          {/* Right Action Icons & Search */}
          <div className="flex items-center gap-3">
            {/* Live Search input */}
            <div className="hidden md:flex items-center relative">
              <span className="material-symbols-outlined absolute left-3.5 text-on-surface-variant pointer-events-none text-xl">
                search
              </span>
              <input
                value={searchQuery}
                onChange={(e) => {
                  onSearchChange(e.target.value);
                  if (currentView !== 'home') onNavigate('home');
                }}
                className="w-56 lg:w-72 pl-10 pr-4 py-2 bg-surface-container-low rounded-full font-body-sm text-sm text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary-container/30 bg-opacity-70 transition-all"
                placeholder="Buscar amigurumis mágicos..."
                type="text"
              />
            </div>

            {/* Favorites Icon */}
            <button
              onClick={() => onNavigate('home')}
              aria-label="Favoritos"
              className="p-2.5 rounded-full text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg transition-all flex items-center justify-center relative cursor-pointer"
            >
              <span className="material-symbols-outlined text-2xl">favorite</span>
              {favoritesCount > 0 && (
                <span className="absolute top-1 right-1 bg-status-out-error text-on-primary font-label-sm text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold shadow-sm">
                  {favoritesCount}
                </span>
              )}
            </button>

            {/* Shopping Bag Button with Badge */}
            <button
              onClick={onOpenCart}
              aria-label="Carrito de compras"
              className="p-2.5 rounded-full text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg transition-all flex items-center justify-center relative cursor-pointer"
            >
              <span className="material-symbols-outlined text-2xl">shopping_bag</span>
              {cartCount > 0 && (
                <span className="absolute top-1 right-1 bg-primary text-on-primary font-label-sm text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold shadow-sm">
                  {cartCount}
                </span>
              )}
            </button>

            {/* Profile Avatar & Dropdown */}
            <div className="relative">
              <div
                onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                className="flex items-center pl-1 shrink-0 cursor-pointer"
                title="Menú de Usuario y Taller"
              >
                <img
                  alt="Profile"
                  className="w-8 h-8 rounded-full object-cover ring-2 ring-primary-container/30 hover:ring-primary-container transition-all"
                  src={MARANA_LOGO}
                />
              </div>

              {profileDropdownOpen && (
                <div
                  className="absolute right-0 mt-3 w-64 bg-surface-container-lowest rounded-2xl shadow-2xl border border-outline-variant/30 py-2 z-50 animate-fade-in"
                  onMouseLeave={() => setProfileDropdownOpen(false)}
                >
                  <div className="px-4 py-2.5 border-b border-surface-container-high">
                    <p className="font-label-md text-sm font-bold text-on-surface">Maraña Atelier</p>
                    <p className="font-body-sm text-xs text-on-surface-variant">Colección Textil & Amigurumis</p>
                  </div>
                  <div className="py-1">
                    <button
                      onClick={() => {
                        setProfileDropdownOpen(false);
                        onOpenAuth();
                      }}
                      className="w-full text-left px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low flex items-center gap-2.5 cursor-pointer"
                    >
                      <span className="material-symbols-outlined text-lg text-primary">person</span>
                      <span>Portal Cliente (Keycloak)</span>
                    </button>
                    <button
                      onClick={() => {
                        setProfileDropdownOpen(false);
                        onNavigate('checkout');
                      }}
                      className="w-full text-left px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low flex items-center gap-2.5 cursor-pointer"
                    >
                      <span className="material-symbols-outlined text-lg text-secondary">shopping_cart_checkout</span>
                      <span>Ir al Checkout</span>
                    </button>
                    <div className="h-px bg-surface-container-high my-1"></div>
                    <button
                      onClick={() => {
                        setProfileDropdownOpen(false);
                        onOpenAdminAuth();
                      }}
                      className="w-full text-left px-4 py-2 text-sm font-bold text-primary hover:bg-badge-lavender-bg flex items-center gap-2.5 cursor-pointer"
                    >
                      <span className="material-symbols-outlined text-lg text-brand-electric-violet">admin_panel_settings</span>
                      <span>Panel Administrador (Taller)</span>
                    </button>
                  </div>
                </div>
              )}
            </div>

            {/* Mobile hamburger */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="xl:hidden p-2 text-on-surface hover:text-primary rounded-xl"
              aria-label="Abrir menú"
            >
              <span className="material-symbols-outlined text-2xl">
                {mobileMenuOpen ? 'close' : 'menu'}
              </span>
            </button>
          </div>
        </div>

        {/* Mobile menu dropdown */}
        {mobileMenuOpen && (
          <div className="xl:hidden bg-surface-container-lowest border-t border-outline-variant/20 px-6 py-4 flex flex-col gap-3 shadow-lg">
            <div className="relative mb-2">
              <span className="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-xl">search</span>
              <input
                value={searchQuery}
                onChange={(e) => onSearchChange(e.target.value)}
                className="w-full pl-10 pr-4 py-2 bg-surface-container-low rounded-full text-sm text-on-surface placeholder:text-outline focus:outline-none"
                placeholder="Buscar amigurumis..."
                type="text"
              />
            </div>
            <button
              onClick={() => { onNavigate('home'); setMobileMenuOpen(false); }}
              className="text-left font-bold text-primary py-2"
            >
              Catálogo General
            </button>
            <button
              onClick={() => { onNavigate('product-lulu'); setMobileMenuOpen(false); }}
              className="text-left font-semibold text-on-surface py-2"
            >
              Ficha Lulú la Arañita Cósmica
            </button>
            <button
              onClick={() => { onNavigate('checkout'); setMobileMenuOpen(false); }}
              className="text-left font-semibold text-on-surface py-2"
            >
              Finalizar Pedido / Checkout
            </button>
            <button
              onClick={() => { onNavigate('client-auth'); setMobileMenuOpen(false); }}
              className="text-left font-semibold text-on-surface py-2"
            >
              Iniciar Sesión Cliente
            </button>
            <button
              onClick={() => { onNavigate('backoffice-orders'); setMobileMenuOpen(false); }}
              className="text-left font-bold text-brand-electric-violet py-2 border-t border-outline-variant/20 pt-3"
            >
              Panel Administrador Taller
            </button>
          </div>
        )}
      </header>
    </>
  );
};
