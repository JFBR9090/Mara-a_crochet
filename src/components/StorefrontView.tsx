import React, { useState, useMemo } from 'react';
import { Product } from '../types';

interface StorefrontViewProps {
  products: Product[];
  onSelectProduct: (product: Product) => void;
  onAddToCart: (product: Product) => void;
  onQuickAdopt: (product: Product) => void;
  onToggleFavorite: (product: Product) => void;
  favorites: string[];
  searchQuery: string;
  onSearchChange: (q: string) => void;
  onShowToast: (msg: string, sub?: string) => void;
}

export const StorefrontView: React.FC<StorefrontViewProps> = ({
  products,
  onSelectProduct,
  onAddToCart,
  onQuickAdopt,
  onToggleFavorite,
  favorites,
  searchQuery,
  onSearchChange,
  onShowToast,
}) => {
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [selectedAvailability, setSelectedAvailability] = useState<string>('all');
  const [sortBy, setSortBy] = useState<string>('populares');

  // Filtered and sorted products
  const filteredProducts = useMemo(() => {
    return products
      .filter((p) => {
        // Search filter
        const query = searchQuery.toLowerCase().trim();
        const matchesSearch =
          !query ||
          p.name.toLowerCase().includes(query) ||
          p.description.toLowerCase().includes(query) ||
          p.tagline.toLowerCase().includes(query) ||
          p.categoryLabel.toLowerCase().includes(query);

        // Category filter
        const matchesCategory =
          selectedCategory === 'all' || p.category === selectedCategory;

        // Availability filter
        let matchesAvailability = true;
        if (selectedAvailability === 'in-stock') {
          matchesAvailability = p.status === 'in-stock' || p.status === 'low-stock';
        } else if (selectedAvailability === 'preorder') {
          matchesAvailability = p.status === 'preorder';
        } else if (selectedAvailability === 'soldout') {
          matchesAvailability = p.status === 'soldout';
        }

        return matchesSearch && matchesCategory && matchesAvailability;
      })
      .sort((a, b) => {
        if (sortBy === 'precio-asc') return a.price - b.price;
        if (sortBy === 'precio-desc') return b.price - a.price;
        if (sortBy === 'recientes') return b.craftHours - a.craftHours;
        return b.rating - a.rating; // default: populares
      });
  }, [products, searchQuery, selectedCategory, selectedAvailability, sortBy]);

  const luluProduct = products.find((p) => p.id === 'lulu-aranita') || products[0];

  return (
    <div className="w-full flex flex-col">
      {/* Announcement Bar */}
      <section className="w-full bg-surface-container-high py-2.5 px-4 text-center border-b border-outline-variant/10">
        <div className="max-w-7xl mx-auto flex items-center justify-center gap-2 flex-wrap text-on-surface">
          <span className="flex h-2 w-2 rounded-full bg-status-stock-success animate-ping"></span>
          <span className="font-label-md text-xs sm:text-sm text-primary font-bold">
            Lanzamiento Estelar:
          </span>
          <span className="font-body-sm text-xs sm:text-sm text-on-surface-variant">
            Nueva tanda de Criaturas Cósmicas disponible. Todos los pedidos incluyen Certificado de Adopción numerado.
          </span>
          <a
            href="#catalogo"
            className="font-label-sm text-xs uppercase tracking-wider text-secondary font-bold underline ml-1 hover:text-primary transition-colors cursor-pointer"
          >
            Ver stock en vivo
          </a>
        </div>
      </section>

      {/* Hero Section */}
      <section className="relative w-full overflow-hidden bg-gradient-to-b from-surface via-surface-container-low/60 to-surface pt-10 pb-16 lg:pb-24 px-6 lg:px-12">
        {/* Ambient decorative violet blurs */}
        <div className="absolute -top-24 -left-20 w-96 h-96 bg-primary-fixed-dim/40 rounded-full blur-3xl pointer-events-none"></div>
        <div className="absolute top-1/2 -right-32 w-80 h-80 bg-secondary-fixed/50 rounded-full blur-3xl pointer-events-none"></div>

        <div className="max-w-7xl mx-auto relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          {/* Left Column: Copy & Value Proposition */}
          <div className="lg:col-span-7 flex flex-col space-y-6">
            {/* Trust Pill */}
            <div className="inline-flex items-center gap-2 bg-badge-lavender-bg px-4 py-1.5 rounded-full w-fit shadow-sm">
              <div className="flex items-center text-accent-star-gold">
                {[...Array(5)].map((_, i) => (
                  <span
                    key={i}
                    className="material-symbols-outlined text-base"
                    style={{ fontVariationSettings: "'FILL' 1" }}
                  >
                    star
                  </span>
                ))}
              </div>
              <span className="font-label-sm text-xs text-primary font-bold">
                4.9/5 en 142 reseñas verificadas de tejedoras y coleccionistas
              </span>
            </div>

            <h1 className="font-headline-xl text-3xl sm:text-4xl lg:text-5xl text-primary font-extrabold tracking-tight leading-tight">
              Amigurumis Cósmicos con Alma &amp; Confección Artesanal
            </h1>

            <p className="font-body-lg text-base sm:text-lg text-on-surface-variant max-w-xl leading-relaxed">
              Piezas únicas tejidas punto a punto con hilado 100% de calidad. Cada creación incluye certificado de adopción digital numerado y un delicado aroma a lavanda para transformar cualquier rincón en un refugio mágico.
            </p>

            {/* Value Badges Bento Strip */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
              <div className="flex items-center gap-2.5 bg-surface-container-lowest p-3 rounded-2xl shadow-sm border border-outline-variant/10">
                <span className="material-symbols-outlined text-primary text-xl">palette</span>
                <span className="font-label-md text-xs sm:text-sm text-on-surface font-semibold">100% Hecho a Mano</span>
              </div>
              <div className="flex items-center gap-2.5 bg-surface-container-lowest p-3 rounded-2xl shadow-sm border border-outline-variant/10">
                <span className="material-symbols-outlined text-secondary text-xl">local_shipping</span>
                <span className="font-label-md text-xs sm:text-sm text-on-surface font-semibold">Envíos a toda Colombia</span>
              </div>
              <div className="flex items-center gap-2.5 bg-surface-container-lowest p-3 rounded-2xl shadow-sm border border-outline-variant/10">
                <span className="material-symbols-outlined text-accent-star-gold text-xl" style={{ fontVariationSettings: "'FILL' 1" }}>verified</span>
                <span className="font-label-md text-xs sm:text-sm text-on-surface font-semibold">Garantía de Autor</span>
              </div>
            </div>

            {/* Hero Action CTAs */}
            <div className="flex flex-wrap items-center gap-4 pt-4">
              <a
                href="#catalogo"
                className="px-8 py-4 rounded-full bg-primary-container text-on-primary font-label-lg text-sm sm:text-base font-bold shadow-lg hover:bg-brand-electric-violet hover:-translate-y-0.5 active:scale-95 transition-all flex items-center gap-3 cursor-pointer"
              >
                <span>Explorar Colección Disponible</span>
                <span className="material-symbols-outlined text-xl">arrow_back_ios_new</span>
              </a>
              <button
                onClick={() => {
                  setSelectedAvailability('preorder');
                  const el = document.getElementById('catalogo');
                  el?.scrollIntoView({ behavior: 'smooth' });
                }}
                className="px-6 py-4 rounded-full bg-surface-container-low text-primary font-label-lg text-sm sm:text-base font-bold hover:bg-surface-container-highest transition-all flex items-center gap-2 cursor-pointer"
              >
                <span className="material-symbols-outlined text-xl">yard</span>
                <span>Ver Piezas en Aguja</span>
              </button>
            </div>
          </div>

          {/* Right Column: Star Mascot Spotlight Card */}
          <div className="lg:col-span-5 relative">
            <div className="relative bg-surface-container-lowest rounded-3xl p-6 shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl border border-outline-variant/20">
              {/* Floating badge */}
              <div className="absolute top-5 left-5 z-20 bg-accent-star-gold text-[#271900] px-3.5 py-1.5 rounded-full font-label-sm text-xs font-bold flex items-center gap-1.5 shadow-sm">
                <span className="material-symbols-outlined text-sm" style={{ fontVariationSettings: "'FILL' 1" }}>
                  star
                </span>
                <span>Edición Estrella - Últimas 3 unidades</span>
              </div>

              {/* Wishlist Toggle button */}
              <button
                onClick={() => onToggleFavorite(luluProduct)}
                aria-label="Favorito rápido"
                className="absolute top-5 right-5 z-20 w-10 h-10 rounded-full bg-surface/90 backdrop-blur-md text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-colors shadow-sm cursor-pointer"
              >
                <span
                  className="material-symbols-outlined text-xl"
                  style={{ fontVariationSettings: favorites.includes(luluProduct.id) ? "'FILL' 1" : "'FILL' 0" }}
                >
                  favorite
                </span>
              </button>

              {/* Spotlight Product Image */}
              <div
                onClick={() => onSelectProduct(luluProduct)}
                className="w-full aspect-square rounded-2xl overflow-hidden bg-surface-container-low relative group cursor-pointer"
              >
                <img
                  alt="Lulú la Arañita Cósmica amigurumi púrpura tejida a mano"
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  src={luluProduct.images[0]?.url}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-primary/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-4">
                  <span className="px-4 py-1.5 rounded-full bg-white/90 text-primary font-bold text-xs shadow-md">
                    Click para ver detalles completos
                  </span>
                </div>
              </div>

              {/* Product Details Overlay */}
              <div className="pt-5 flex flex-col space-y-3">
                <div className="flex items-center justify-between">
                  <div>
                    <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-widest">
                      Insignia Maraña
                    </span>
                    <h3
                      onClick={() => onSelectProduct(luluProduct)}
                      className="font-headline-md text-xl font-bold text-on-surface hover:text-primary transition-colors cursor-pointer"
                    >
                      {luluProduct.name}
                    </h3>
                  </div>
                  <div className="text-right">
                    <span className="font-headline-md text-xl font-extrabold text-primary">
                      ${luluProduct.price.toLocaleString('es-CO')}
                    </span>
                    <span className="block font-label-sm text-xs text-on-surface-variant line-through">
                      ${luluProduct.originalPrice?.toLocaleString('es-CO')} COP
                    </span>
                  </div>
                </div>

                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant line-clamp-2">
                  {luluProduct.description}
                </p>

                <div className="pt-2 flex items-center gap-3">
                  <button
                    onClick={() => onQuickAdopt(luluProduct)}
                    className="flex-1 py-3 px-4 rounded-full bg-primary hover:bg-brand-vivid-purple text-on-primary font-label-lg text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-md cursor-pointer"
                  >
                    <span className="material-symbols-outlined text-lg">shopping_cart</span>
                    <span>Adoptar Ahora</span>
                  </button>
                  <button
                    onClick={() => onSelectProduct(luluProduct)}
                    className="p-3 rounded-full bg-surface-container text-primary hover:bg-badge-lavender-bg transition-colors cursor-pointer"
                    title="Vista Rápida"
                  >
                    <span className="material-symbols-outlined text-xl">visibility</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Interactive Search & Filtering Nexus */}
      <section className="w-full bg-surface-container-low/70 py-10 px-6 lg:px-12 border-y border-outline-variant/10" id="catalogo">
        <div className="max-w-7xl mx-auto space-y-6">
          {/* Search and Sort row */}
          <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            {/* Live Search */}
            <div className="relative flex-1 max-w-2xl">
              <span className="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary text-2xl pointer-events-none">
                search
              </span>
              <input
                value={searchQuery}
                onChange={(e) => onSearchChange(e.target.value)}
                className="w-full pl-12 pr-10 py-3.5 bg-surface-container-lowest text-on-surface rounded-full font-body-md text-sm sm:text-base placeholder:text-outline focus:outline-none focus:ring-4 focus:ring-primary-container/20 shadow-sm transition-all border border-outline-variant/10"
                placeholder="Buscar por nombre, criatura mágica o colección..."
                type="text"
              />
              {searchQuery && (
                <button
                  onClick={() => onSearchChange('')}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface cursor-pointer"
                  title="Limpiar búsqueda"
                >
                  <span className="material-symbols-outlined text-lg">close</span>
                </button>
              )}
            </div>

            {/* Sorting selector */}
            <div className="flex items-center gap-3 shrink-0 self-end md:self-center">
              <label className="font-label-md text-xs sm:text-sm text-on-surface-variant font-bold">
                Ordenar:
              </label>
              <div className="relative">
                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value)}
                  className="appearance-none bg-surface-container-lowest text-on-surface font-label-lg text-sm px-5 py-3 pr-10 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer border border-outline-variant/10"
                >
                  <option value="populares">Más populares</option>
                  <option value="recientes">Recién tejidos</option>
                  <option value="precio-asc">Menor a mayor precio</option>
                  <option value="precio-desc">Mayor a menor precio</option>
                </select>
                <span className="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xl">
                  expand_more
                </span>
              </div>
            </div>
          </div>

          {/* Category Filter Pills */}
          <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`shrink-0 px-5 py-2.5 rounded-full font-label-md text-sm font-bold transition-all cursor-pointer ${
                selectedCategory === 'all'
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              Todos ({products.length})
            </button>
            <button
              onClick={() => setSelectedCategory('espacio')}
              className={`shrink-0 px-5 py-2.5 rounded-full font-label-md text-sm font-bold transition-all cursor-pointer ${
                selectedCategory === 'espacio'
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              ⭐ Espacio &amp; Estrellas (4)
            </button>
            <button
              onClick={() => setSelectedCategory('bosque')}
              className={`shrink-0 px-5 py-2.5 rounded-full font-label-md text-sm font-bold transition-all cursor-pointer ${
                selectedCategory === 'bosque'
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              🌿 Bosque Encantado (3)
            </button>
            <button
              onClick={() => setSelectedCategory('kits')}
              className={`shrink-0 px-5 py-2.5 rounded-full font-label-md text-sm font-bold transition-all cursor-pointer ${
                selectedCategory === 'kits'
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              🧶 Kits &amp; Aprendizaje (3)
            </button>
            <button
              onClick={() => setSelectedCategory('bebes')}
              className={`shrink-0 px-5 py-2.5 rounded-full font-label-md text-sm font-bold transition-all cursor-pointer ${
                selectedCategory === 'bebes'
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              👶 Apego &amp; Ternura (2)
            </button>
            <button
              onClick={() => setSelectedCategory('nuevos')}
              className={`shrink-0 px-5 py-2.5 rounded-full font-label-md text-sm font-bold transition-all cursor-pointer ${
                selectedCategory === 'nuevos'
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-lowest text-on-surface-variant hover:text-primary hover:bg-badge-lavender-bg'
              }`}
            >
              ✨ Nuevas Creaciones (3)
            </button>
          </div>

          {/* Secondary Availability Toggles */}
          <div className="flex flex-wrap items-center gap-3 pt-2 text-on-surface-variant">
            <span className="font-label-sm text-xs uppercase font-bold text-outline tracking-wider">
              Disponibilidad:
            </span>
            <button
              onClick={() =>
                setSelectedAvailability(selectedAvailability === 'in-stock' ? 'all' : 'in-stock')
              }
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer ${
                selectedAvailability === 'in-stock'
                  ? 'ring-2 ring-primary bg-badge-lavender-bg text-primary'
                  : 'bg-surface-container-lowest text-on-surface hover:bg-badge-lavender-bg'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-status-stock-success"></span>
              <span>Envío Inmediato</span>
            </button>
            <button
              onClick={() =>
                setSelectedAvailability(selectedAvailability === 'preorder' ? 'all' : 'preorder')
              }
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer ${
                selectedAvailability === 'preorder'
                  ? 'ring-2 ring-primary bg-badge-lavender-bg text-primary'
                  : 'bg-surface-container-lowest text-on-surface hover:bg-badge-lavender-bg'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-secondary"></span>
              <span>En Aguja (Pre-orden)</span>
            </button>
            <button
              onClick={() =>
                setSelectedAvailability(selectedAvailability === 'soldout' ? 'all' : 'soldout')
              }
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer ${
                selectedAvailability === 'soldout'
                  ? 'ring-2 ring-primary bg-badge-lavender-bg text-primary'
                  : 'bg-surface-container-lowest text-on-surface hover:bg-badge-lavender-bg'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-status-out-error"></span>
              <span>Ver Agotados</span>
            </button>
          </div>
        </div>
      </section>

      {/* Product Catalog Grid */}
      <section className="w-full bg-surface py-12 px-6 lg:px-12">
        <div className="max-w-7xl mx-auto">
          {filteredProducts.length === 0 ? (
            <div className="text-center py-16 bg-surface-container-low rounded-3xl mt-6">
              <div className="w-20 h-20 mx-auto rounded-full bg-badge-lavender-bg flex items-center justify-center text-primary mb-4">
                <span className="material-symbols-outlined text-4xl">search_off</span>
              </div>
              <h3 className="font-headline-md text-xl font-bold text-primary">
                No encontramos coincidencias
              </h3>
              <p className="font-body-md text-sm text-on-surface-variant max-w-md mx-auto mt-2">
                Intenta buscando con otro término o restablece los filtros para ver todas las criaturas disponibles.
              </p>
              <button
                onClick={() => {
                  onSearchChange('');
                  setSelectedCategory('all');
                  setSelectedAvailability('all');
                }}
                className="mt-6 px-6 py-2.5 rounded-full bg-primary text-on-primary font-label-lg text-sm font-bold hover:bg-brand-vivid-purple transition-all cursor-pointer"
              >
                Restablecer Filtros
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {filteredProducts.map((product) => {
                const isFavorite = favorites.includes(product.id);
                const isSoldOut = product.status === 'soldout';
                const isPreorder = product.status === 'preorder';

                return (
                  <div
                    key={product.id}
                    className={`group bg-surface-container-lowest rounded-3xl p-4 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between border border-outline-variant/10 ${
                      isSoldOut ? 'opacity-90' : ''
                    }`}
                  >
                    <div>
                      {/* Image Frame */}
                      <div className="relative w-full aspect-square rounded-2xl overflow-hidden bg-surface-container-low mb-4">
                        <img
                          alt={product.name}
                          src={product.images[0]?.url || '/kit-crochet.jpg'}
                          onClick={() => onSelectProduct(product)}
                          onError={(e) => {
                            (e.target as HTMLImageElement).src = '/kit-crochet.jpg';
                          }}
                          className={`w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer ${
                            isSoldOut ? 'grayscale-[30%]' : ''
                          }`}
                        />

                        {/* Top Badges */}
                        {product.discountBadge ? (
                          <span className="absolute top-3 left-3 bg-amber-500 text-amber-950 px-3 py-1 rounded-full font-label-sm text-xs font-extrabold flex items-center gap-1 shadow-sm uppercase tracking-wider">
                            {product.discountBadge}
                          </span>
                        ) : product.status === 'low-stock' ? (
                          <span className="absolute top-3 left-3 bg-surface-container-lowest/90 backdrop-blur-sm text-status-low-warning px-3 py-1 rounded-full font-label-sm text-xs font-bold flex items-center gap-1 shadow-sm">
                            <span className="w-1.5 h-1.5 rounded-full bg-status-low-warning animate-pulse"></span>
                            {product.statusLabel}
                          </span>
                        ) : product.status === 'in-stock' && product.category === 'nuevos' ? (
                          <span className="absolute top-3 left-3 bg-secondary-fixed text-[#2e004d] px-3 py-1 rounded-full font-label-sm text-xs font-bold flex items-center gap-1 shadow-sm">
                            <span className="material-symbols-outlined text-xs">auto_awesome</span>
                            Nuevo Ingreso
                          </span>
                        ) : isPreorder ? (
                          <span className="absolute top-3 left-3 bg-badge-lavender-bg text-primary px-3 py-1 rounded-full font-label-sm text-xs font-bold flex items-center gap-1 shadow-sm">
                            <span className="material-symbols-outlined text-sm">yard</span>
                            En Aguja (7 días)
                          </span>
                        ) : isSoldOut ? (
                          <span className="absolute top-3 left-3 bg-surface-container-lowest/90 backdrop-blur-sm text-status-out-error px-3 py-1 rounded-full font-label-sm text-xs font-bold flex items-center gap-1 shadow-sm">
                            <span className="w-1.5 h-1.5 rounded-full bg-status-out-error"></span>
                            Agotado
                          </span>
                        ) : null}

                        {/* Vista Rápida Hover Button */}
                        <button
                          onClick={() => onSelectProduct(product)}
                          className="absolute inset-x-0 bottom-3 mx-auto w-fit px-4 py-1.5 rounded-full bg-white/95 text-primary hover:bg-primary hover:text-white font-label-sm text-xs font-bold shadow-md opacity-0 group-hover:opacity-100 transition-all flex items-center gap-1.5 cursor-pointer backdrop-blur-sm"
                        >
                          <span className="material-symbols-outlined text-sm">visibility</span>
                          <span>Vista Rápida</span>
                        </button>

                        {/* Favorite button */}
                        <button
                          onClick={() => onToggleFavorite(product)}
                          aria-label="Añadir a favoritos"
                          className="absolute top-3 right-3 w-8 h-8 rounded-full bg-surface-container-lowest/80 backdrop-blur-md text-outline hover:text-status-out-error flex items-center justify-center transition-colors cursor-pointer shadow-sm"
                        >
                          <span
                            className="material-symbols-outlined text-lg"
                            style={{ fontVariationSettings: isFavorite ? "'FILL' 1" : "'FILL' 0", color: isFavorite ? '#E11D48' : undefined }}
                          >
                            favorite
                          </span>
                        </button>
                      </div>

                      {/* Details */}
                      <div className="space-y-1">
                        <div className="flex items-center justify-between text-on-surface-variant font-label-sm text-xs">
                          <span className="text-primary font-bold uppercase tracking-wider">{product.categoryLabel}</span>
                          <span className="flex items-center text-accent-star-gold font-bold">
                            ★ {product.rating} <span className="text-outline font-normal ml-0.5">({product.reviewCount})</span>
                          </span>
                        </div>
                        <h3
                          onClick={() => onSelectProduct(product)}
                          className="font-headline-sm text-base font-bold text-on-surface group-hover:text-primary transition-colors cursor-pointer"
                        >
                          {product.name}
                        </h3>
                        <p className="font-body-sm text-xs text-on-surface-variant line-clamp-1">
                          {product.tagline}
                        </p>
                      </div>
                    </div>

                    {/* Bottom Pricing & Action */}
                    <div className="pt-3 mt-3 border-t border-surface-container-high/60 flex flex-col gap-2">
                      <div className="flex items-baseline gap-2 flex-wrap">
                        <span className={`font-headline-sm text-lg font-extrabold ${isSoldOut ? 'text-outline' : 'text-primary'}`}>
                          ${product.price.toLocaleString('es-CO')} COP
                        </span>
                        {product.originalPrice && (
                          <span className="text-xs text-outline line-through">
                            ${product.originalPrice.toLocaleString('es-CO')} COP
                          </span>
                        )}
                        <span className="text-[11px] text-on-surface-variant font-medium ml-auto">
                          ~USD ${product.usdApprox.toFixed(2)}
                        </span>
                      </div>

                      <div className="flex items-center gap-2 w-full pt-1">
                        <button
                          onClick={() => onAddToCart(product)}
                          disabled={isSoldOut}
                          className="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary hover:bg-brand-vivid-purple disabled:opacity-50 font-label-sm text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1 cursor-pointer"
                        >
                          Adoptar
                        </button>
                        <button
                          onClick={() => onSelectProduct(product)}
                          className="flex-1 py-2 px-3 rounded-xl bg-surface-container-lowest text-primary border border-primary/30 hover:bg-badge-lavender-bg font-label-sm text-xs font-bold transition-all flex items-center justify-center gap-1 cursor-pointer"
                        >
                          Detalle &amp; Reseñas
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </section>

      {/* Unboxing & Craft Experience Bento Grid */}
      <section className="w-full bg-surface-container-low py-16 px-6 lg:px-12" id="proceso">
        <div className="max-w-7xl mx-auto space-y-12">
          <div className="text-center max-w-2xl mx-auto space-y-3">
            <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-widest">
              El Viaje de tu Amigurumi
            </span>
            <h2 className="font-headline-xl text-3xl sm:text-4xl text-primary font-extrabold">
              Una Experiencia de Adopción Inolvidable
            </h2>
            <p className="font-body-md text-sm sm:text-base text-on-surface-variant">
              No enviamos paquetes ordinarios. Cada entrega es una ceremonia pensada para arrancar una sonrisa desde el primer momento que tocas la caja.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {/* Bento 1 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-primary flex items-center justify-center">
                <span className="material-symbols-outlined text-2xl">license</span>
              </div>
              <div>
                <h3 className="font-headline-sm text-base font-bold text-on-surface">Certificado de Adopción</h3>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant mt-2 leading-relaxed">
                  Cada criatura porta su nombre oficial, fecha exacta en que se cerró el último punto y número de serie único de la artesana.
                </p>
              </div>
              <span className="font-label-sm text-xs text-primary font-bold flex items-center gap-1">
                Numerado a mano <span className="material-symbols-outlined text-xs">arrow_forward</span>
              </span>
            </div>

            {/* Bento 2 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-secondary flex items-center justify-center">
                <span className="material-symbols-outlined text-2xl">local_florist</span>
              </div>
              <div>
                <h3 className="font-headline-sm text-base font-bold text-on-surface">Aroma Floral a Lavanda</h3>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant mt-2 leading-relaxed">
                  Abrir la caja es sumergirse en un campo relajante: infusionamos la seda de embalaje con esencias botánicas naturales y calmantes.
                </p>
              </div>
              <span className="font-label-sm text-xs text-secondary font-bold flex items-center gap-1">
                Sensorial y suave <span className="material-symbols-outlined text-xs">arrow_forward</span>
              </span>
            </div>

            {/* Bento 3 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-status-stock-success flex items-center justify-center">
                <span className="material-symbols-outlined text-2xl">inventory_2</span>
              </div>
              <div>
                <h3 className="font-headline-sm text-base font-bold text-on-surface">Control Anti-Sobreventa</h3>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant mt-2 leading-relaxed">
                  Inventario conectado en tiempo real. Cuando una pieza marca disponible, está 100% terminada y reservada exclusivamente para ti.
                </p>
              </div>
              <span className="font-label-sm text-xs text-status-stock-success font-bold flex items-center gap-1">
                Sin esperas imprevistas <span className="material-symbols-outlined text-xs">arrow_forward</span>
              </span>
            </div>

            {/* Bento 4 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="w-12 h-12 rounded-2xl bg-badge-lavender-bg text-accent-star-gold flex items-center justify-center">
                <span className="material-symbols-outlined text-2xl text-amber-600">receipt_long</span>
              </div>
              <div>
                <h3 className="font-headline-sm text-base font-bold text-on-surface">Transferencia Directa</h3>
                <p className="font-body-sm text-xs sm:text-sm text-on-surface-variant mt-2 leading-relaxed">
                  Paga por transferencia sin comisiones ocultas y sube tu comprobante. Lo verificamos y preparamos tu despacho en menos de 2 horas.
                </p>
              </div>
              <span className="font-label-sm text-xs text-amber-700 font-bold flex items-center gap-1">
                Validación humana rápida <span className="material-symbols-outlined text-xs">arrow_forward</span>
              </span>
            </div>
          </div>
        </div>
      </section>

      {/* Community Testimonials & Social Proof */}
      <section className="w-full bg-surface py-16 px-6 lg:px-12">
        <div className="max-w-7xl mx-auto space-y-10">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
              <span className="font-label-sm text-xs text-secondary uppercase font-bold tracking-widest">
                Testimonios Reales
              </span>
              <h2 className="font-headline-xl text-3xl sm:text-4xl text-primary font-extrabold mt-1">
                Familias que ya Abrazan su Maraña
              </h2>
            </div>
            <div className="flex items-center gap-2 text-on-surface-variant font-label-md text-sm">
              <span className="material-symbols-outlined text-status-stock-success">check_circle</span>
              <span>100% de reseñas verificadas tras entrega</span>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* Review 1 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="space-y-3">
                <div className="flex items-center text-accent-star-gold">
                  {[...Array(5)].map((_, i) => (
                    <span key={i} className="material-symbols-outlined text-lg" style={{ fontVariationSettings: "'FILL' 1" }}>
                      star
                    </span>
                  ))}
                </div>
                <p className="font-body-md text-sm text-on-surface leading-relaxed">
                  “Lulú la arañita es infinitamente más suave de lo que imaginé. El unboxing huele a lavanda de verdad y el certificado con su nombre fue el detalle más lindo para el regalo de mi hermana.”
                </p>
              </div>
              <div className="flex items-center gap-3 pt-3 border-t border-surface-container-high/60">
                <div className="w-10 h-10 rounded-full bg-badge-lavender-bg text-primary flex items-center justify-center font-bold">
                  C
                </div>
                <div>
                  <span className="font-label-lg text-sm text-on-surface font-bold block">Camila Valenzuela</span>
                  <span className="font-label-sm text-xs text-on-surface-variant">Santiago · Compró a Lulú Cósmica</span>
                </div>
              </div>
            </div>

            {/* Review 2 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="space-y-3">
                <div className="flex items-center text-accent-star-gold">
                  {[...Array(5)].map((_, i) => (
                    <span key={i} className="material-symbols-outlined text-lg" style={{ fontVariationSettings: "'FILL' 1" }}>
                      star
                    </span>
                  ))}
                </div>
                <p className="font-body-md text-sm text-on-surface leading-relaxed">
                  “El pulpo de apego para mi bebé recién nacido es una maravilla. Los tentáculos tienen el largo perfecto y la atención por transferencia fue instantánea. Llegó a Concepción en 48 horas.”
                </p>
              </div>
              <div className="flex items-center gap-3 pt-3 border-t border-surface-container-high/60">
                <div className="w-10 h-10 rounded-full bg-secondary-fixed text-[#2e004d] flex items-center justify-center font-bold">
                  M
                </div>
                <div>
                  <span className="font-label-lg text-sm text-on-surface font-bold block">Matías Henríquez</span>
                  <span className="font-label-sm text-xs text-on-surface-variant">Concepción · Compró Pulpo Calma</span>
                </div>
              </div>
            </div>

            {/* Review 3 */}
            <div className="bg-surface-container-lowest p-6 rounded-3xl shadow-sm flex flex-col justify-between space-y-4 border border-outline-variant/10">
              <div className="space-y-3">
                <div className="flex items-center text-accent-star-gold">
                  {[...Array(5)].map((_, i) => (
                    <span key={i} className="material-symbols-outlined text-lg" style={{ fontVariationSettings: "'FILL' 1" }}>
                      star
                    </span>
                  ))}
                </div>
                <p className="font-body-md text-sm text-on-surface leading-relaxed">
                  “La polilla lunar tiene detalles que solo alguien que ama el crochet puede lograr. Las costuras son impecables y firmes. Sin duda voy a coleccionar toda la línea nocturna.”
                </p>
              </div>
              <div className="flex items-center gap-3 pt-3 border-t border-surface-container-high/60">
                <div className="w-10 h-10 rounded-full bg-badge-lavender-bg text-primary flex items-center justify-center font-bold">
                  F
                </div>
                <div>
                  <span className="font-label-lg text-sm text-on-surface font-bold block">Francisca Morales</span>
                  <span className="font-label-sm text-xs text-on-surface-variant">Valparaíso · Compró Polilla Lunar</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Custom Request Banner / Artisan WhatsApp Call-to-Action */}
      <section className="w-full bg-surface-container-high py-16 px-6 lg:px-12 relative overflow-hidden">
        <div className="max-w-5xl mx-auto bg-primary-container text-on-primary rounded-3xl p-8 lg:p-14 shadow-2xl relative overflow-hidden">
          {/* Starry decoration SVG */}
          <svg className="absolute -right-12 -bottom-12 w-64 h-64 text-brand-electric-violet/40 pointer-events-none" fill="currentColor" viewBox="0 0 200 200">
            <polygon points="100,10 120,70 180,80 135,120 150,180 100,145 50,180 65,120 20,80 80,70" />
          </svg>

          <div className="relative z-10 max-w-2xl space-y-4">
            <div className="inline-flex items-center gap-2 bg-on-primary/10 backdrop-blur-md px-3.5 py-1 rounded-full text-on-primary font-label-sm text-xs">
              <span className="material-symbols-outlined text-base">auto_awesome</span>
              <span>Encargos Especiales y Personalizaciones</span>
            </div>
            <h2 className="font-headline-xl text-3xl sm:text-4xl font-extrabold leading-tight">
              ¿Tienes en mente un personaje o mascota especial?
            </h2>
            <p className="font-body-lg text-sm sm:text-base text-on-primary-container leading-relaxed">
              Tejemos réplicas de tus mascotas, versiones chibi de tus personajes favoritos de anime o piezas con paletas de colores únicas. Cuéntanos tu idea directamente al taller.
            </p>
            <div className="pt-4 flex flex-wrap items-center gap-4">
              <a
                href="https://wa.me/56987654321"
                target="_blank"
                rel="noopener noreferrer"
                className="px-8 py-3.5 rounded-full bg-surface-container-lowest text-primary font-label-lg text-sm font-bold hover:bg-badge-lavender-bg transition-all shadow-md flex items-center gap-2 cursor-pointer"
              >
                <span className="material-symbols-outlined text-xl text-status-stock-success">chat</span>
                <span>Cotizar por WhatsApp</span>
              </a>
              <span className="font-label-sm text-xs text-on-primary-container">
                Tiempo estimado de respuesta: &lt; 2 horas
              </span>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

