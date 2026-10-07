import React from 'react';
import { CartItem } from '../types';

interface CartDrawerProps {
  isOpen: boolean;
  onClose: () => void;
  items: CartItem[];
  onUpdateQuantity: (productId: string, delta: number) => void;
  onRemoveItem: (productId: string) => void;
  onProceedToCheckout: () => void;
}

export const CartDrawer: React.FC<CartDrawerProps> = ({
  isOpen,
  onClose,
  items,
  onUpdateQuantity,
  onRemoveItem,
  onProceedToCheckout,
}) => {
  const subtotal = items.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0);
  const freeGiftThreshold = 52000;
  const missingForGift = Math.max(0, freeGiftThreshold - subtotal);
  const progressPercent = Math.min(100, Math.round((subtotal / freeGiftThreshold) * 100));

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 transition-opacity duration-300">
      {/* Tinted Backdrop Overlay */}
      <div
        onClick={onClose}
        className="absolute inset-0 bg-[#241432]/50 backdrop-blur-sm transition-opacity"
      />

      {/* Slide-Over Drawer Panel */}
      <div className="absolute top-0 right-0 h-full w-full max-w-md bg-surface flex flex-col shadow-[0_20px_40px_-8px_rgba(29,17,40,0.35)] transform transition-transform duration-300 ease-out z-10 animate-slide-left">
        {/* Drawer Header */}
        <div className="p-6 bg-surface-container-low border-b border-outline-variant/20 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-11 h-11 rounded-2xl bg-badge-lavender-bg text-primary flex items-center justify-center shadow-sm">
              <span className="material-symbols-outlined text-2xl">shopping_bag</span>
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h3 className="font-headline-sm text-lg font-bold text-on-surface">Tu Carrito Mágico</h3>
                <span className="px-2 py-0.5 rounded-full bg-primary-container text-on-primary font-label-sm text-xs font-bold">
                  {totalItems}
                </span>
              </div>
              <span className="font-label-sm text-xs text-secondary font-medium">
                Piezas tejidas a mano reservadas
              </span>
            </div>
          </div>
          <button
            onClick={onClose}
            aria-label="Cerrar carrito"
            className="w-9 h-9 rounded-full bg-surface-container-high hover:bg-surface-container-highest text-on-surface hover:text-primary flex items-center justify-center transition-all cursor-pointer"
            type="button"
          >
            <span className="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        {/* Free Gift Packaging Progression Bar */}
        <div className="px-6 py-3 bg-badge-lavender-bg/95 border-b border-outline-variant/20 flex flex-col gap-1.5">
          <div className="flex items-center justify-between font-label-sm text-xs">
            <span className="text-primary font-bold flex items-center gap-1">
              <span className="material-symbols-outlined text-base text-accent-star-gold" style={{ fontVariationSettings: "'FILL' 1" }}>
                auto_awesome
              </span>
              {missingForGift > 0
                ? `✨ ¡Falta solo $${missingForGift.toLocaleString('es-CL')} CLP para Empaque de Regalo Gratis!`
                : '✨ ¡Empaque de Regalo Gratis Desbloqueado!'}
            </span>
            <span className="font-bold text-primary">{progressPercent}%</span>
          </div>
          <div className="w-full bg-surface-container-highest rounded-full h-2 overflow-hidden">
            <div
              className="bg-gradient-to-r from-brand-vivid-purple to-primary h-full rounded-full transition-all duration-700"
              style={{ width: `${progressPercent}%` }}
            />
          </div>
        </div>

        {/* Cart Item List */}
        <div className="flex-1 overflow-y-auto p-6 space-y-4">
          {items.length === 0 ? (
            <div className="text-center py-12 flex flex-col items-center justify-center space-y-3">
              <div className="w-16 h-16 rounded-full bg-badge-lavender-bg text-primary flex items-center justify-center">
                <span className="material-symbols-outlined text-3xl">shopping_basket</span>
              </div>
              <p className="font-headline-sm text-base font-bold text-on-surface">Tu canasta está vacía</p>
              <p className="font-body-sm text-xs text-on-surface-variant max-w-xs">
                Explora el catálogo y adopta a una criatura mágica hecha a mano.
              </p>
              <button
                onClick={onClose}
                className="mt-2 px-5 py-2 rounded-full bg-primary text-on-primary font-label-md text-xs font-bold"
              >
                Explorar Catálogo
              </button>
            </div>
          ) : (
            items.map((item) => (
              <div
                key={item.product.id}
                className="p-4 rounded-3xl bg-surface-container-lowest shadow-[0_4px_16px_rgba(123,44,191,0.05)] border border-outline-variant/20 flex gap-3.5 items-start relative"
              >
                {/* Thumbnail */}
                <div className="w-20 h-20 rounded-2xl overflow-hidden bg-surface-container-high shrink-0 ring-1 ring-primary-container/20">
                  <img
                    alt={item.product.name}
                    className="w-full h-full object-cover"
                    src={item.product.images[0]?.url}
                  />
                </div>

                {/* Details */}
                <div className="flex-1 min-w-0">
                  <div className="flex items-start justify-between gap-1">
                    <h4 className="font-label-lg text-sm font-bold text-on-surface truncate">
                      {item.product.name}
                    </h4>
                    <button
                      onClick={() => onRemoveItem(item.product.id)}
                      className="text-outline hover:text-status-out-error p-1 transition-colors cursor-pointer"
                      title="Eliminar del carrito"
                      type="button"
                    >
                      <span className="material-symbols-outlined text-lg">delete</span>
                    </button>
                  </div>
                  <p className="font-label-sm text-xs text-secondary">
                    {item.product.tagline}
                  </p>

                  {/* Stock pill */}
                  <div className="mt-1 mb-2.5">
                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-900 font-label-sm text-[10px] font-bold">
                      <span className="w-1.5 h-1.5 rounded-full bg-status-low-warning"></span>
                      {item.product.stock <= 4 ? `¡Últimas ${item.product.stock} unidades en stock!` : `Stock confirmado: ${item.product.stock} disp.`}
                    </span>
                  </div>

                  {/* Stepper + Price */}
                  <div className="flex items-center justify-between">
                    <div className="inline-flex items-center rounded-full bg-surface-container-high p-0.5 shadow-inner">
                      <button
                        onClick={() => onUpdateQuantity(item.product.id, -1)}
                        disabled={item.quantity <= 1}
                        className="w-7 h-7 rounded-full bg-surface-container-lowest text-primary flex items-center justify-center font-bold hover:bg-primary hover:text-on-primary transition-all disabled:opacity-40 cursor-pointer"
                        type="button"
                      >
                        <span className="material-symbols-outlined text-sm">remove</span>
                      </button>
                      <span className="w-8 text-center font-label-md text-xs font-bold text-on-surface">
                        {item.quantity}
                      </span>
                      <button
                        onClick={() => onUpdateQuantity(item.product.id, 1)}
                        disabled={item.quantity >= item.product.stock}
                        className="w-7 h-7 rounded-full bg-surface-container-lowest text-primary flex items-center justify-center font-bold hover:bg-primary hover:text-on-primary transition-all cursor-pointer"
                        type="button"
                      >
                        <span className="material-symbols-outlined text-sm">add</span>
                      </button>
                    </div>
                    <span className="font-headline-sm text-base font-extrabold text-primary">
                      ${(item.product.price * item.quantity).toLocaleString('es-CL')} CLP
                    </span>
                  </div>
                </div>
              </div>
            ))
          )}

          {/* Guarantee Reservation Callout */}
          {items.length > 0 && (
            <div className="p-4 rounded-2xl bg-badge-lavender-bg/70 border border-primary-container/20 flex gap-3 items-start">
              <span className="material-symbols-outlined text-primary text-xl shrink-0 mt-0.5">
                shield
              </span>
              <p className="font-body-sm text-xs text-on-surface-variant leading-relaxed">
                <strong className="text-on-surface font-semibold">🧶 Reserva garantizada:</strong> Tus piezas artesanales quedan apartadas temporalmente durante el checkout. Método de pago: Transferencia bancaria directa con verificación manual en &lt; 24h.
              </p>
            </div>
          )}
        </div>

        {/* Drawer Footer & Checkout Flow CTA */}
        {items.length > 0 && (
          <div className="p-6 bg-surface-container-lowest shadow-[0_-4px_20px_rgba(123,44,191,0.06)] border-t border-outline-variant/20 space-y-4">
            <div className="space-y-2 font-body-sm text-xs">
              <div className="flex justify-between text-on-surface-variant">
                <span>Subtotal</span>
                <span className="font-bold text-on-surface">
                  ${subtotal.toLocaleString('es-CL')} CLP{' '}
                  <span className="font-normal text-on-surface-variant text-[11px]">
                    (~${(subtotal / 890).toFixed(2)} USD)
                  </span>
                </span>
              </div>
              <div className="flex justify-between text-on-surface-variant items-center">
                <span>Empaque Especial Artesanal</span>
                <span className="text-status-stock-success font-bold text-xs bg-status-stock-success/15 px-2 py-0.5 rounded-full">
                  Gratis (Caja + Certificado Oficial)
                </span>
              </div>
              <div className="flex justify-between text-on-surface-variant">
                <span>Estimación de Envío</span>
                <span className="text-on-surface font-medium">Calculado en checkout (Todo Chile)</span>
              </div>
              <div className="flex justify-between pt-2.5 border-t border-surface-container-high items-baseline">
                <span className="font-headline-sm text-base font-bold text-on-surface">Total Final</span>
                <span className="font-headline-lg text-2xl font-extrabold text-primary">
                  ${subtotal.toLocaleString('es-CL')} CLP
                </span>
              </div>
            </div>

            {/* Checkout Action Button */}
            <div className="space-y-2 pt-1">
              <button
                onClick={onProceedToCheckout}
                className="w-full py-3.5 px-6 rounded-full bg-primary-container hover:bg-brand-electric-violet active:scale-[0.98] text-on-primary flex flex-col items-center justify-center shadow-[0_4px_20px_-2px_rgba(123,44,191,0.35)] transition-all cursor-pointer group"
                type="button"
              >
                <span className="font-label-lg text-sm font-bold flex items-center gap-2">
                  Proceder al Checkout / Transferencia{' '}
                  <span className="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">
                    arrow_forward
                  </span>
                </span>
                <span className="font-label-sm text-[11px] opacity-90">
                  Paso 1 de 2: Datos y Comprobante
                </span>
              </button>
              <button
                onClick={onClose}
                className="w-full py-2 text-center text-secondary hover:text-primary font-label-md text-xs font-semibold transition-colors cursor-pointer"
                type="button"
              >
                Seguir explorando el catálogo
              </button>
            </div>

            {/* Mini Trust perks */}
            <div className="flex items-center justify-center gap-4 pt-2 border-t border-outline-variant/20 text-on-surface-variant font-label-sm text-[11px]">
              <span className="flex items-center gap-1">
                <span className="material-symbols-outlined text-sm text-primary">verified</span> 100% Hecho a Mano
              </span>
              <span className="flex items-center gap-1">
                <span className="material-symbols-outlined text-sm text-primary">lock</span> Pago Seguro
              </span>
              <span className="flex items-center gap-1">
                <span className="material-symbols-outlined text-sm text-status-stock-success">chat</span> Soporte WhatsApp
              </span>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
