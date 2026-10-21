import React, { useState } from 'react';
import { Product } from '../types';

interface ProductDetailViewProps {
  product: Product;
  allProducts: Product[];
  onSelectProduct: (product: Product) => void;
  onAddToCart: (product: Product, quantity: number) => void;
  onBuyNow: (product: Product, quantity: number) => void;
  onToggleFavorite: (product: Product) => void;
  isFavorite: boolean;
  onBackToCatalog: () => void;
}

export const ProductDetailView: React.FC<ProductDetailViewProps> = ({
  product,
  allProducts,
  onSelectProduct,
  onAddToCart,
  onBuyNow,
  onToggleFavorite,
  isFavorite,
  onBackToCatalog,
}) => {
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState(0);

  const maxAvailable = product.stock || 4;

  const handleQuantityChange = (delta: number) => {
    const next = quantity + delta;
    if (next >= 1 && next <= maxAvailable) {
      setQuantity(next);
    }
  };

  const currentImageUrl = product.images[selectedImageIndex]?.url || product.images[0]?.url;

  // Filter 3 related products
  const relatedProducts = allProducts.filter((p) => p.id !== product.id).slice(0, 3);

  return (
    <div className="w-full bg-surface min-h-screen py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
      {/* Breadcrumb Bar */}
      <nav aria-label="Breadcrumb" className="flex items-center gap-2 text-on-surface-variant font-label-md text-xs sm:text-sm mb-6 overflow-x-auto whitespace-nowrap py-1">
        <button
          onClick={onBackToCatalog}
          className="hover:text-primary transition-colors flex items-center gap-1 cursor-pointer"
        >
          <span className="material-symbols-outlined text-base">home</span>
          <span>Inicio</span>
        </button>
        <span className="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
        <button
          onClick={onBackToCatalog}
          className="hover:text-primary transition-colors cursor-pointer"
        >
          Catálogo
        </button>
        <span className="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
        <span className="text-on-surface-variant">{product.categoryLabel}</span>
        <span className="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
        <span className="text-primary font-bold">{product.name}</span>
        <span className="ml-auto text-on-surface-variant/70 font-label-sm text-xs hidden sm:inline-block font-mono">
          SKU: {product.sku}
        </span>
      </nav>

      {/* Main Product Showcase Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 mb-16 items-start">
        {/* Gallery Column (7 cols on lg) */}
        <div className="lg:col-span-7 flex flex-col-reverse md:flex-row gap-4 items-start">
          {/* Interactive Thumbnails */}
          <div className="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-visible w-full md:w-24 shrink-0 pb-2 md:pb-0">
            {product.images.map((img, idx) => (
              <button
                key={idx}
                type="button"
                onClick={() => setSelectedImageIndex(idx)}
                className={`relative rounded-2xl overflow-hidden aspect-square bg-surface-container-high transition-all shadow-sm focus:outline-none cursor-pointer ${
                  selectedImageIndex === idx
                    ? 'ring-2 ring-primary-container opacity-100 scale-105'
                    : 'opacity-70 hover:opacity-100'
                }`}
              >
                <img
                  alt={img.alt}
                  src={img.url}
                  className="w-full h-full object-cover"
                />
              </button>
            ))}
          </div>

          {/* Main Image Container */}
          <div className="relative w-full aspect-square rounded-3xl overflow-hidden bg-surface-container-lowest shadow-[0_10px_30px_-5px_rgba(123,44,191,0.12)] border border-outline-variant/15 group">
            {/* Floating Badges */}
            <div className="absolute top-4 left-4 z-10 flex flex-col gap-2">
              <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-badge-lavender-bg/95 backdrop-blur-md text-primary font-label-sm text-xs shadow-sm font-bold">
                <span className="material-symbols-outlined text-sm text-accent-star-gold" style={{ fontVariationSettings: "'FILL' 1" }}>
                  stars
                </span>
                Colección Estelar
              </span>
              <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-on-surface font-label-sm text-xs text-status-stock-success font-bold shadow-sm">
                <span className="w-2 h-2 rounded-full bg-status-stock-success animate-pulse"></span>
                100% Hecho a Mano
              </span>
            </div>

            {/* Wishlist Toggle Button */}
            <button
              onClick={() => onToggleFavorite(product)}
              aria-label="Guardar en favoritos"
              type="button"
              className="absolute top-4 right-4 z-10 w-11 h-11 rounded-full bg-surface-container-lowest/90 backdrop-blur-md flex items-center justify-center text-on-surface-variant hover:text-status-out-error shadow-sm hover:scale-105 transition-all cursor-pointer"
            >
              <span
                className="material-symbols-outlined text-2xl transition-transform active:scale-125"
                style={{
                  fontVariationSettings: isFavorite ? "'FILL' 1" : "'FILL' 0",
                  color: isFavorite ? '#E11D48' : undefined,
                }}
              >
                favorite
              </span>
            </button>

            {/* Main Display Image */}
            <img
              alt={product.name}
              src={currentImageUrl}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            {/* Zoom hint chip */}
            <div className="absolute bottom-4 right-4 bg-neutral-ink-card/75 backdrop-blur-md text-on-primary px-3 py-1.5 rounded-full flex items-center gap-1.5 font-label-sm text-xs opacity-80 group-hover:opacity-100 transition-opacity">
              <span className="material-symbols-outlined text-base">zoom_in</span>
              <span>Vista detallada</span>
            </div>
          </div>
        </div>

        {/* Product Info & Purchasing Column (5 cols on lg) */}
        <div className="lg:col-span-5 flex flex-col gap-6">
          {/* Title & Rating Header */}
          <div>
            <div className="flex items-center gap-2 mb-2">
              <span className="px-2.5 py-0.5 rounded-full bg-badge-lavender-bg font-label-sm text-xs text-primary font-bold tracking-wide uppercase">
                {product.tagline}
              </span>
            </div>
            <h1 className="font-headline-xl text-3xl sm:text-4xl text-on-surface font-extrabold tracking-tight leading-tight">
              {product.name}
            </h1>
            <div className="flex items-center gap-3 mt-3">
              <div className="flex items-center text-accent-star-gold">
                {[...Array(5)].map((_, i) => (
                  <span
                    key={i}
                    className="material-symbols-outlined text-lg"
                    style={{ fontVariationSettings: "'FILL' 1" }}
                  >
                    star
                  </span>
                ))}
              </div>
              <span className="font-label-lg text-sm font-bold text-on-surface">
                {product.rating}
              </span>
              <span className="text-outline-variant">•</span>
              <a
                href="#resenas"
                className="font-label-md text-sm text-secondary hover:text-primary underline underline-offset-4 font-semibold"
              >
                {product.reviewCount} reseñas verificadas
              </a>
            </div>
          </div>

          {/* Pricing Block */}
          <div className="p-5 rounded-3xl bg-surface-container-low/80 flex flex-col gap-2 border border-outline-variant/15">
            <div className="flex items-baseline gap-3">
              <span className="font-headline-lg text-3xl font-extrabold text-primary">
                ${product.price.toLocaleString('es-CO')} COP
              </span>
              {product.originalPrice && (
                <span className="font-body-md text-base line-through text-outline">
                  ${product.originalPrice.toLocaleString('es-CO')} COP
                </span>
              )}
              {product.discountBadge && (
                <span className="px-2.5 py-1 rounded-full bg-status-stock-success/15 text-status-stock-success font-label-sm text-xs font-bold">
                  {product.discountBadge}
                </span>
              )}
            </div>
            <div className="flex items-center gap-2 text-on-surface-variant font-body-sm text-xs">
              <span className="material-symbols-outlined text-base text-secondary">payments</span>
              <span>Aprox. <strong>${product.usdApprox.toFixed(2)} USD</strong> • IVA Incluido</span>
            </div>
            <div className="mt-2 pt-3 bg-surface-container-lowest/70 rounded-2xl p-3 flex items-start gap-2.5 text-on-surface-variant font-body-sm text-xs">
              <span className="material-symbols-outlined text-primary text-xl shrink-0">account_balance</span>
              <p className="leading-relaxed">
                <strong className="text-on-surface font-semibold">Transferencia Bancaria con validación ágil:</strong>{' '}
                Reserva tu pieza al instante y sube tu comprobante en un clic. Validamos en menos de 24 horas.
              </p>
            </div>
          </div>

          {/* Real-Time Stock Bar & Alert */}
          <div className="p-4 rounded-2xl bg-amber-500/10 flex flex-col gap-2.5 border border-amber-500/20">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="relative flex h-3 w-3">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-status-low-warning opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-3 w-3 bg-status-low-warning"></span>
                </span>
                <span className="font-label-lg text-xs sm:text-sm font-bold text-amber-950">
                  ¡Solo quedan {product.stock} unidades disponibles!
                </span>
              </div>
              <span className="font-label-sm text-xs text-amber-900 font-semibold">
                {product.stock} / {product.maxStock} en stock
              </span>
            </div>

            {/* Progress bar */}
            <div className="w-full bg-amber-200/50 rounded-full h-2 overflow-hidden">
              <div
                className="bg-gradient-to-r from-status-low-warning to-brand-vivid-purple h-full rounded-full transition-all duration-700"
                style={{ width: `${Math.round((product.stock / product.maxStock) * 100)}%` }}
              ></div>
            </div>

            <p className="font-label-sm text-xs text-amber-900/90 leading-relaxed">
              Debido a la confección manual de {product.craftHours} horas por amigurumi, el stock se repone semanalmente.
            </p>
          </div>

          {/* Purchase Interaction Module */}
          <div className="flex flex-col gap-4">
            {/* Quantity Stepper with Validation */}
            <div className="flex items-center gap-4">
              <span className="font-label-lg text-sm text-on-surface font-semibold">Cantidad:</span>
              <div className="inline-flex items-center rounded-full bg-surface-container-high p-1 shadow-inner">
                <button
                  type="button"
                  onClick={() => handleQuantityChange(-1)}
                  disabled={quantity <= 1}
                  className="w-10 h-10 rounded-full bg-surface-container-lowest text-primary flex items-center justify-center font-bold text-lg hover:bg-primary hover:text-on-primary transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                >
                  <span className="material-symbols-outlined text-lg">remove</span>
                </button>
                <span className="w-12 text-center font-headline-sm text-lg font-bold text-on-surface select-none">
                  {quantity}
                </span>
                <button
                  type="button"
                  onClick={() => handleQuantityChange(1)}
                  disabled={quantity >= maxAvailable}
                  className="w-10 h-10 rounded-full bg-surface-container-lowest text-primary flex items-center justify-center font-bold text-lg hover:bg-primary hover:text-on-primary transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                >
                  <span className="material-symbols-outlined text-lg">add</span>
                </button>
              </div>

              {quantity >= maxAvailable && (
                <div className="text-status-low-warning font-label-sm text-xs flex items-center gap-1 animate-pulse">
                  <span className="material-symbols-outlined text-base">info</span>
                  <span>Tope máximo de {maxAvailable} unidades alcanzado</span>
                </div>
              )}
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row gap-3 pt-2">
              <button
                type="button"
                onClick={() => onAddToCart(product, quantity)}
                className="flex-1 py-4 px-6 rounded-full bg-primary-container hover:bg-brand-electric-violet active:scale-[0.98] text-on-primary font-label-lg text-sm sm:text-base font-bold flex items-center justify-center gap-2 shadow-[0_4px_20px_-2px_rgba(123,44,191,0.35)] transition-all cursor-pointer"
              >
                <span className="material-symbols-outlined text-xl">shopping_bag</span>
                <span>Añadir al Carrito</span>
              </button>
              <button
                type="button"
                onClick={() => onBuyNow(product, quantity)}
                className="flex-1 py-4 px-6 rounded-full bg-badge-lavender-bg hover:bg-surface-container-highest text-primary active:scale-[0.98] font-label-lg text-sm sm:text-base font-bold flex items-center justify-center gap-2 transition-all cursor-pointer"
              >
                <span className="material-symbols-outlined text-xl">bolt</span>
                <span>Comprar Ahora</span>
              </button>
            </div>
          </div>

          {/* Trust Badges Bento Strip */}
          <div className="grid grid-cols-3 gap-2.5 pt-4">
            <div className="p-3 rounded-2xl bg-surface-container-low/70 flex flex-col items-center text-center gap-1 border border-outline-variant/10">
              <span className="material-symbols-outlined text-primary text-2xl">local_shipping</span>
              <span className="font-label-md text-xs font-bold text-on-surface">Envíos Rápidos</span>
              <span className="font-label-sm text-[10px] text-on-surface-variant">A todo el país en caja</span>
            </div>
            <div className="p-3 rounded-2xl bg-surface-container-low/70 flex flex-col items-center text-center gap-1 border border-outline-variant/10">
              <span className="material-symbols-outlined text-primary text-2xl">sanitizer</span>
              <span className="font-label-md text-xs font-bold text-on-surface">Algodón 100%</span>
              <span className="font-label-sm text-[10px] text-on-surface-variant">Fibras hipoalergénicas</span>
            </div>
            <div className="p-3 rounded-2xl bg-surface-container-low/70 flex flex-col items-center text-center gap-1 border border-outline-variant/10">
              <span className="material-symbols-outlined text-primary text-2xl">verified_user</span>
              <span className="font-label-md text-xs font-bold text-on-surface">Tejido Seguro</span>
              <span className="font-label-sm text-[10px] text-on-surface-variant">Ojos anti-desprendimiento</span>
            </div>
          </div>
        </div>
      </div>

      {/* Product Deep Specifications Tabs */}
      <div className="w-full bg-surface-container-lowest rounded-3xl p-6 sm:p-10 shadow-[0_4px_20px_-2px_rgba(123,44,191,0.06)] mb-16 border border-outline-variant/15">
        {/* Tab Headers */}
        <div className="flex items-center gap-2 pb-4 overflow-x-auto border-b border-surface-container-high/60">
          <button
            type="button"
            onClick={() => setActiveTab(0)}
            className={`px-6 py-3 rounded-full font-label-lg text-sm font-bold transition-all whitespace-nowrap cursor-pointer ${
              activeTab === 0
                ? 'bg-primary text-on-primary shadow-sm'
                : 'bg-surface-container-low text-on-surface-variant hover:text-primary'
            }`}
          >
            Detalles &amp; Materiales
          </button>
          <button
            type="button"
            onClick={() => setActiveTab(1)}
            className={`px-6 py-3 rounded-full font-label-lg text-sm font-bold transition-all whitespace-nowrap cursor-pointer ${
              activeTab === 1
                ? 'bg-primary text-on-primary shadow-sm'
                : 'bg-surface-container-low text-on-surface-variant hover:text-primary'
            }`}
          >
            Cuidados del Tejido
          </button>
          <button
            type="button"
            onClick={() => setActiveTab(2)}
            className={`px-6 py-3 rounded-full font-label-lg text-sm font-bold transition-all whitespace-nowrap cursor-pointer ${
              activeTab === 2
                ? 'bg-primary text-on-primary shadow-sm'
                : 'bg-surface-container-low text-on-surface-variant hover:text-primary'
            }`}
          >
            Flujo de Pago y Envíos
          </button>
        </div>

        {/* Tab 0: Details & Specs */}
        {activeTab === 0 && (
          <div className="pt-6 animate-fade-in">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div className="space-y-4">
                <h3 className="font-headline-sm text-xl font-bold text-on-surface">
                  Magia hecha a mano punto por punto
                </h3>
                <p className="text-on-surface-variant font-body-md text-sm sm:text-base leading-relaxed">
                  {product.description}
                </p>
                <div className="grid grid-cols-2 gap-3 pt-2">
                  <div className="p-4 rounded-2xl bg-surface-container-low">
                    <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-wider">Dimensiones</span>
                    <p className="font-headline-sm text-base sm:text-lg font-bold text-on-surface mt-1">{product.dimensions}</p>
                    <span className="font-label-sm text-[11px] text-on-surface-variant">Altura x Envergadura patitas</span>
                  </div>
                  <div className="p-4 rounded-2xl bg-surface-container-low">
                    <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-wider">Peso Total</span>
                    <p className="font-headline-sm text-base sm:text-lg font-bold text-on-surface mt-1">{product.weight}</p>
                    <span className="font-label-sm text-[11px] text-on-surface-variant">Ultra liviano y suave</span>
                  </div>
                </div>
              </div>

              <div className="p-6 rounded-3xl bg-badge-lavender-bg/40 flex flex-col justify-between border border-primary-container/10">
                <h4 className="font-label-lg text-xs font-bold text-primary uppercase tracking-wider mb-4">
                  Ficha Técnica Textil
                </h4>
                <dl className="space-y-3 font-body-sm text-xs sm:text-sm">
                  <div className="flex justify-between py-1.5 border-b border-outline-variant/20">
                    <dt className="text-on-surface-variant font-semibold">Composición del Hilo:</dt>
                    <dd className="text-on-surface text-right font-medium">{product.material}</dd>
                  </div>
                  <div className="flex justify-between py-1.5 border-b border-outline-variant/20">
                    <dt className="text-on-surface-variant font-semibold">Relleno Interior:</dt>
                    <dd className="text-on-surface text-right font-medium">{product.filling}</dd>
                  </div>
                  <div className="flex justify-between py-1.5 border-b border-outline-variant/20">
                    <dt className="text-on-surface-variant font-semibold">Seguridad Infantil:</dt>
                    <dd className="text-on-surface text-right font-medium">{product.safety}</dd>
                  </div>
                  <div className="flex justify-between py-1.5 border-b border-outline-variant/20">
                    <dt className="text-on-surface-variant font-semibold">Tiempo de Confección:</dt>
                    <dd className="text-on-surface text-right font-medium">{product.craftHours} horas de tejido dedicado</dd>
                  </div>
                  <div className="flex justify-between py-1.5">
                    <dt className="text-on-surface-variant font-semibold">Incluye:</dt>
                    <dd className="text-on-surface text-right font-medium">{product.includes}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        )}

        {/* Tab 1: Care Instructions */}
        {activeTab === 1 && (
          <div className="pt-6 animate-fade-in">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
              <div className="p-5 rounded-2xl bg-surface-container-low flex flex-col items-start gap-3">
                <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-primary flex items-center justify-center">
                  <span className="material-symbols-outlined text-2xl">wash</span>
                </div>
                <h4 className="font-headline-sm text-base font-bold text-on-surface">Lavado Delicado</h4>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                  Lavar a mano en agua fría utilizando jabón neutro o para prendas delicadas. No usar cloro ni blanqueadores abrasivos.
                </p>
              </div>
              <div className="p-5 rounded-2xl bg-surface-container-low flex flex-col items-start gap-3">
                <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-primary flex items-center justify-center">
                  <span className="material-symbols-outlined text-2xl">dry</span>
                </div>
                <h4 className="font-headline-sm text-base font-bold text-on-surface">Secado Plano</h4>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                  No retorcer ni centrifugar. Retira el exceso de agua presionando suavemente con una toalla y seca en horizontal a la sombra.
                </p>
              </div>
              <div className="p-5 rounded-2xl bg-surface-container-low flex flex-col items-start gap-3">
                <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-primary flex items-center justify-center">
                  <span className="material-symbols-outlined text-2xl">iron</span>
                </div>
                <h4 className="font-headline-sm text-base font-bold text-on-surface">Preservación</h4>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                  No planchar. Si el amigurumi pierde forma tras un abrazo prolongado, simplemente dale suaves masajes para redistribuir el vellón interior.
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Tab 2: Payment & Shipping */}
        {activeTab === 2 && (
          <div className="pt-6 animate-fade-in space-y-6">
            <h4 className="font-headline-sm text-lg font-bold text-on-surface">
              ¿Cómo funciona el pago manual por transferencia bancaria?
            </h4>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div className="p-4 rounded-2xl bg-surface-container-low flex flex-col gap-2 relative">
                <span className="font-headline-xl text-3xl font-black text-secondary/30">01</span>
                <span className="font-label-lg text-sm font-bold text-on-surface -mt-2">Reserva tu pedido</span>
                <p className="font-body-sm text-xs text-on-surface-variant">
                  El stock de {product.name} queda reservado exclusivamente para ti en nuestra base de datos durante el checkout.
                </p>
              </div>
              <div className="p-4 rounded-2xl bg-surface-container-low flex flex-col gap-2 relative">
                <span className="font-headline-xl text-3xl font-black text-secondary/30">02</span>
                <span className="font-label-lg text-sm font-bold text-on-surface -mt-2">Transfiere el total</span>
                <p className="font-body-sm text-xs text-on-surface-variant">
                  Usa los datos bancarios que verás en pantalla desde tu banco habitual (Cuenta Rut, Corriente o Vista).
                </p>
              </div>
              <div className="p-4 rounded-2xl bg-surface-container-low flex flex-col gap-2 relative">
                <span className="font-headline-xl text-3xl font-black text-secondary/30">03</span>
                <span className="font-label-lg text-sm font-bold text-on-surface -mt-2">Sube tu voucher</span>
                <p className="font-body-sm text-xs text-on-surface-variant">
                  Cargas la captura o PDF en nuestra pasarela de comprobantes con validación rápida.
                </p>
              </div>
              <div className="p-4 rounded-2xl bg-surface-container-low flex flex-col gap-2 relative">
                <span className="font-headline-xl text-3xl font-black text-secondary/30">04</span>
                <span className="font-label-lg text-sm font-bold text-on-surface -mt-2">Aprobación y despacho</span>
                <p className="font-body-sm text-xs text-on-surface-variant">
                  Validamos en menos de 24h hábiles y despachamos con número de seguimiento en vivo.
                </p>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Customer Reviews Section */}
      <div className="w-full mb-16" id="resenas">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="material-symbols-outlined text-accent-star-gold text-2xl" style={{ fontVariationSettings: "'FILL' 1" }}>
                reviews
              </span>
              <span className="font-label-lg text-xs text-secondary uppercase font-bold tracking-wider">
                Opiniones Reales
              </span>
            </div>
            <h2 className="font-headline-lg text-2xl sm:text-3xl font-extrabold text-on-surface">
              Reseñas de la Comunidad
            </h2>
          </div>
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-badge-lavender-bg text-primary font-label-sm text-xs font-bold">
            <span className="material-symbols-outlined text-base">verified</span>
            <span>Solo compradores verificados pueden calificar</span>
          </div>
        </div>

        {/* Rating Overview Card */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 sm:p-8 rounded-3xl bg-surface-container-low/70 mb-10 items-center border border-outline-variant/15">
          <div className="lg:col-span-4 flex flex-col items-center justify-center text-center p-4">
            <span className="font-headline-xl text-5xl font-black text-primary">4.9</span>
            <div className="flex items-center text-accent-star-gold my-2">
              {[...Array(5)].map((_, i) => (
                <span key={i} className="material-symbols-outlined text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>
                  star
                </span>
              ))}
            </div>
            <span className="font-label-md text-xs sm:text-sm text-on-surface-variant">
              Basado en 38 valoraciones comprobadas
            </span>
          </div>

          <div className="lg:col-span-8 flex flex-col gap-2.5">
            <div className="flex items-center gap-3 font-label-md text-xs sm:text-sm">
              <span className="w-16 text-on-surface font-semibold text-right">5 estrellas</span>
              <div className="flex-1 bg-surface-container-highest rounded-full h-2.5 overflow-hidden">
                <div className="bg-primary-container h-full rounded-full" style={{ width: '92%' }}></div>
              </div>
              <span className="w-8 text-on-surface-variant font-bold text-left">35</span>
            </div>
            <div className="flex items-center gap-3 font-label-md text-xs sm:text-sm">
              <span className="w-16 text-on-surface font-semibold text-right">4 estrellas</span>
              <div className="flex-1 bg-surface-container-highest rounded-full h-2.5 overflow-hidden">
                <div className="bg-primary-container h-full rounded-full" style={{ width: '8%' }}></div>
              </div>
              <span className="w-8 text-on-surface-variant font-bold text-left">3</span>
            </div>
            <div className="flex items-center gap-3 font-label-md text-xs sm:text-sm opacity-40">
              <span className="w-16 text-on-surface font-semibold text-right">3 estrellas</span>
              <div className="flex-1 bg-surface-container-highest rounded-full h-2.5 overflow-hidden">
                <div className="bg-primary-container h-full rounded-full" style={{ width: '0%' }}></div>
              </div>
              <span className="w-8 text-on-surface-variant font-bold text-left">0</span>
            </div>
            <div className="flex items-center gap-3 font-label-md text-xs sm:text-sm opacity-40">
              <span className="w-16 text-on-surface font-semibold text-right">2 estrellas</span>
              <div className="flex-1 bg-surface-container-highest rounded-full h-2.5 overflow-hidden">
                <div className="bg-primary-container h-full rounded-full" style={{ width: '0%' }}></div>
              </div>
              <span className="w-8 text-on-surface-variant font-bold text-left">0</span>
            </div>
            <div className="flex items-center gap-3 font-label-md text-xs sm:text-sm opacity-40">
              <span className="w-16 text-on-surface font-semibold text-right">1 estrella</span>
              <div className="flex-1 bg-surface-container-highest rounded-full h-2.5 overflow-hidden">
                <div className="bg-primary-container h-full rounded-full" style={{ width: '0%' }}></div>
              </div>
              <span className="w-8 text-on-surface-variant font-bold text-left">0</span>
            </div>
          </div>
        </div>

        {/* Reviews Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {/* Review 1: Valentina */}
          <div className="p-6 rounded-3xl bg-surface-container-lowest shadow-[0_4px_16px_rgba(123,44,191,0.05)] border border-outline-variant/15 flex flex-col justify-between">
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-9 h-9 rounded-full bg-badge-lavender-bg text-primary font-bold flex items-center justify-center font-label-lg text-sm">
                    VC
                  </div>
                  <div>
                    <h4 className="font-label-lg text-sm font-bold text-on-surface">Valentina Castro</h4>
                    <div className="flex items-center gap-1 text-status-stock-success font-label-sm text-[11px] font-bold">
                      <span className="material-symbols-outlined text-xs">verified</span>
                      <span>Compradora Verificada</span>
                    </div>
                  </div>
                </div>
                <span className="text-on-surface-variant font-label-sm text-xs">Hace 4 días</span>
              </div>

              <div className="flex text-accent-star-gold">
                {[...Array(5)].map((_, i) => (
                  <span key={i} className="material-symbols-outlined text-base" style={{ fontVariationSettings: "'FILL' 1" }}>
                    star
                  </span>
                ))}
              </div>

              <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                "¡Es aún más hermosa en persona! Los ojitos bordados con forma de estrella tienen una dedicación increíble. La transferencia bancaria fue súper rápida, validaron mi comprobante en 3 horas."
              </p>
            </div>

            <div className="mt-4 pt-3 flex items-center gap-2 border-t border-surface-container-high">
              <div className="w-14 h-14 rounded-xl overflow-hidden bg-surface-container-high shrink-0">
                <img
                  alt="Foto adjunta de Valentina"
                  className="w-full h-full object-cover"
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuB0P23pmjW3CJAJYcxTgP1sbnmkexTQv3E-nwsOUB8lvf8au-wqOJu49nFxvW-6Dma0fWqeXbvZ-6JB8FcW8OdThAg6-RHsjDKBC5E2L--O0IKiuP6Rg2lRJj3F8qIXpOBODU33OjWQ1BI6etyVVMHq6G2ssD9p0YSaS2JTBHeDjG8Y2C75-7bTFwjCy_0iOPKeFPBmgnf81eUJCnDbvEF7wioABvnNBjOZrtO-QOOAEDyUOTmKkcNJ"
                />
              </div>
              <span className="font-label-sm text-xs text-secondary font-semibold">
                Foto adjunta de Valentina
              </span>
            </div>
          </div>

          {/* Review 2: Matías */}
          <div className="p-6 rounded-3xl bg-surface-container-lowest shadow-[0_4px_16px_rgba(123,44,191,0.05)] border border-outline-variant/15 flex flex-col justify-between">
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-9 h-9 rounded-full bg-secondary-fixed text-[#2e004d] font-bold flex items-center justify-center font-label-lg text-sm">
                    MR
                  </div>
                  <div>
                    <h4 className="font-label-lg text-sm font-bold text-on-surface">Matías Rojas</h4>
                    <div className="flex items-center gap-1 text-status-stock-success font-label-sm text-[11px] font-bold">
                      <span className="material-symbols-outlined text-xs">verified</span>
                      <span>Comprador Verificado</span>
                    </div>
                  </div>
                </div>
                <span className="text-on-surface-variant font-label-sm text-xs">Hace 1 semana</span>
              </div>

              <div className="flex text-accent-star-gold">
                {[...Array(5)].map((_, i) => (
                  <span key={i} className="material-symbols-outlined text-base" style={{ fontVariationSettings: "'FILL' 1" }}>
                    star
                  </span>
                ))}
              </div>

              <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                "Fue un regalo para mi novia fanática de las arañitas y el crochet. Quedó fascinada con el empaque aromático de lavanda y el certificado con el nombre de la artesana."
              </p>
            </div>

            <div className="mt-4 pt-3 flex items-center gap-2 border-t border-surface-container-high">
              <div className="w-14 h-14 rounded-xl overflow-hidden bg-surface-container-high shrink-0">
                <img
                  alt="Foto adjunta de Matías"
                  className="w-full h-full object-cover"
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuBVY6oIba_MDBgvTlSheKiHgoliH-xdkD_KztzkC6Gp0wlzagRtAb7NfLDkW5nHQMt2ugzq901mJSNp6UKr6K3FHPuuaKIOJpozay_qTk3-Yt-ACRv0vaWNvAsrEeVxmaP1a9_zlKxmmtllO6Ptjo5HPCHPLXGPhfjb5EYdBgmQbaRFC06novX9TmOCCjk8UoSWi8SHfc2xne7V1IOqXFS_vEIHaIQLEsruDsZIxw_IZFOgq5OIdjgm"
                />
              </div>
              <span className="font-label-sm text-xs text-secondary font-semibold">
                Foto adjunta de Matías
              </span>
            </div>
          </div>

          {/* Review 3: Sofía */}
          <div className="p-6 rounded-3xl bg-surface-container-lowest shadow-[0_4px_16px_rgba(123,44,191,0.05)] border border-outline-variant/15 flex flex-col justify-between">
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-9 h-9 rounded-full bg-badge-lavender-bg text-primary font-bold flex items-center justify-center font-label-lg text-sm">
                    SA
                  </div>
                  <div>
                    <h4 className="font-label-lg text-sm font-bold text-on-surface">Sofía Alarcón</h4>
                    <div className="flex items-center gap-1 text-status-stock-success font-label-sm text-[11px] font-bold">
                      <span className="material-symbols-outlined text-xs">verified</span>
                      <span>Compradora Verificada</span>
                    </div>
                  </div>
                </div>
                <span className="text-on-surface-variant font-label-sm text-xs">Hace 2 semanas</span>
              </div>

              <div className="flex text-accent-star-gold">
                {[...Array(5)].map((_, i) => (
                  <span key={i} className="material-symbols-outlined text-base" style={{ fontVariationSettings: "'FILL' 1" }}>
                    star
                  </span>
                ))}
              </div>

              <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                "La calidad es inmejorable, cero pelusas y el hilo es de algodón genuino super suave para bebés. Se siente el cariño en cada puntada. ¡Volveré a comprar el murciélago!"
              </p>
            </div>

            <div className="mt-4 pt-3 flex items-center gap-2 text-on-surface-variant font-label-sm text-xs border-t border-surface-container-high">
              <span className="material-symbols-outlined text-base text-primary">thumb_up</span>
              <span>12 personas encontraron útil esta reseña</span>
            </div>
          </div>
        </div>
      </div>

      {/* Related Products Carousel / Grid */}
      <div className="w-full mb-12">
        <div className="flex items-center justify-between mb-8">
          <div>
            <span className="font-label-lg text-xs text-secondary uppercase font-bold tracking-wider">
              Colección Mágica
            </span>
            <h2 className="font-headline-lg text-2xl sm:text-3xl font-extrabold text-on-surface">
              También te encantará
            </h2>
          </div>
          <button
            onClick={onBackToCatalog}
            className="font-label-lg text-sm text-primary hover:text-secondary flex items-center gap-1 font-bold cursor-pointer"
          >
            <span>Ver toda la colección</span>
            <span className="material-symbols-outlined text-base">arrow_forward</span>
          </button>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {relatedProducts.map((rel) => (
            <div
              key={rel.id}
              className="group rounded-3xl bg-surface-container-lowest p-4 shadow-[0_4px_20px_-2px_rgba(123,44,191,0.08)] hover:shadow-[0_10px_25px_-4px_rgba(123,44,191,0.16)] transition-all flex flex-col justify-between border border-outline-variant/15"
            >
              <div>
                <div
                  onClick={() => onSelectProduct(rel)}
                  className="relative aspect-square rounded-2xl overflow-hidden bg-surface-container-high mb-4 cursor-pointer"
                >
                  <span className="absolute top-3 left-3 z-10 px-3 py-1 rounded-full bg-surface-container-lowest/90 backdrop-blur-md text-status-stock-success font-label-sm text-xs font-bold flex items-center gap-1">
                    <span className="w-1.5 h-1.5 rounded-full bg-status-stock-success"></span>
                    En Stock ({rel.stock})
                  </span>
                  <img
                    alt={rel.name}
                    src={rel.images[0]?.url}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                </div>
                <div className="space-y-1">
                  <span className="font-label-sm text-xs text-secondary uppercase font-bold">
                    {rel.categoryLabel}
                  </span>
                  <h3
                    onClick={() => onSelectProduct(rel)}
                    className="font-headline-sm text-base font-bold text-on-surface group-hover:text-primary transition-colors cursor-pointer"
                  >
                    {rel.name}
                  </h3>
                  <p className="font-body-sm text-xs text-on-surface-variant line-clamp-2">
                    {rel.description}
                  </p>
                </div>
              </div>

              <div className="pt-4 mt-2 flex items-center justify-between border-t border-surface-container-high">
                <span className="font-headline-sm text-base font-extrabold text-primary">
                  ${rel.price.toLocaleString('es-CO')} COP
                </span>
                <button
                  type="button"
                  onClick={() => onAddToCart(rel, 1)}
                  className="w-10 h-10 rounded-full bg-badge-lavender-bg hover:bg-primary hover:text-on-primary text-primary flex items-center justify-center transition-all cursor-pointer shadow-sm"
                  title="Añadir al carrito"
                >
                  <span className="material-symbols-outlined text-lg">add_shopping_cart</span>
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

