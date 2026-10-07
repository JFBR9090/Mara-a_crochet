import React, { useState } from 'react';
import { Product } from '../types';

interface BackofficeInventoryViewProps {
  products: Product[];
  onUpdateProductStock: (productId: string, newStock: number) => void;
  onUpdateProductPrice: (productId: string, newPrice: number) => void;
  onNavigateToView: (view: string) => void;
  onShowToast: (msg: string, sub?: string, type?: 'success' | 'info' | 'error') => void;
}

interface RawMaterial {
  id: string;
  name: string;
  type: 'Lana' | 'Insumo' | 'Relleno' | 'Empaque';
  currentStock: number;
  unit: string;
  minLevel: number;
  status: 'ok' | 'warning' | 'critical';
  supplier: string;
}

const INITIAL_MATERIALS: RawMaterial[] = [
  {
    id: 'mat-01',
    name: 'Lana Chenille Suave Lavanda Cósmica',
    type: 'Lana',
    currentStock: 18,
    unit: 'ovillos (100g)',
    minLevel: 5,
    status: 'ok',
    supplier: 'Lanas del Sur SpA',
  },
  {
    id: 'mat-02',
    name: 'Hilo Algodón Mercerizado Púrpura Profundo',
    type: 'Lana',
    currentStock: 12,
    unit: 'madejas (100g)',
    minLevel: 4,
    status: 'ok',
    supplier: 'Hilandería Textil Santiago',
  },
  {
    id: 'mat-03',
    name: 'Ojos de Seguridad Negros 12mm con Traba',
    type: 'Insumo',
    currentStock: 14,
    unit: 'pares',
    minLevel: 25,
    status: 'critical',
    supplier: 'Mercería Central',
  },
  {
    id: 'mat-04',
    name: 'Vellón Siliconado Hipoalergénico Extra Soft',
    type: 'Relleno',
    currentStock: 8.5,
    unit: 'kg',
    minLevel: 3,
    status: 'ok',
    supplier: 'Distribuidora Rellenos Chile',
  },
  {
    id: 'mat-05',
    name: 'Hilo Brillante Plateado Galaxia',
    type: 'Lana',
    currentStock: 3,
    unit: 'conos (50g)',
    minLevel: 6,
    status: 'warning',
    supplier: 'Importadora Hebras',
  },
  {
    id: 'mat-06',
    name: 'Cajas de Embalaje Kraft & Papel Seda Aromático',
    type: 'Empaque',
    currentStock: 42,
    unit: 'unidades',
    minLevel: 15,
    status: 'ok',
    supplier: 'EcoPackaging Chile',
  },
];

export const BackofficeInventoryView: React.FC<BackofficeInventoryViewProps> = ({
  products,
  onUpdateProductStock,
  onUpdateProductPrice,
  onNavigateToView,
  onShowToast,
}) => {
  const [categoryFilter, setCategoryFilter] = useState<string>('todos');
  const [materials, setMaterials] = useState<RawMaterial[]>(INITIAL_MATERIALS);
  const [editingPriceId, setEditingPriceId] = useState<string | null>(null);
  const [tempPrice, setTempPrice] = useState<number>(0);
  const [searchQuery, setSearchQuery] = useState<string>('');

  // Metrics
  const totalStock = products.reduce((acc, p) => acc + p.stock, 0);
  const lowStockCount = products.filter((p) => p.stock > 0 && p.stock <= 4).length;
  const outOfStockCount = products.filter((p) => p.stock === 0).length;

  const filteredProducts = products.filter((product) => {
    const matchesCat =
      categoryFilter === 'todos' ? true : product.category === categoryFilter;
    const matchesSearch =
      product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      product.sku.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCat && matchesSearch;
  });

  const handleStockDelta = (productId: string, currentStock: number, delta: number) => {
    const newStock = Math.max(0, currentStock + delta);
    onUpdateProductStock(productId, newStock);
    onShowToast(
      'Stock Actualizado',
      `Nuevo stock registrado para el amigurumi seleccionado.`,
      'info'
    );
  };

  const handleSavePrice = (productId: string) => {
    if (tempPrice > 0) {
      onUpdateProductPrice(productId, tempPrice);
      setEditingPriceId(null);
      onShowToast(
        'Precio Actualizado',
        `Nuevo precio fijado en $${tempPrice.toLocaleString('es-CL')} CLP`,
        'success'
      );
    }
  };

  const handleRestockMaterial = (materialId: string) => {
    setMaterials((prev) =>
      prev.map((m) =>
        m.id === materialId
          ? {
              ...m,
              currentStock: m.currentStock + (m.unit.includes('kg') ? 5 : 20),
              status: 'ok',
            }
          : m
      )
    );
    onShowToast(
      'Materia Prima Reabastecida',
      `Se ha agregado lote de insumo al taller artesanal.`,
      'success'
    );
  };

  return (
    <div className="min-h-screen bg-[#f7f4fa] pb-16">
      {/* Backoffice Sub-Header */}
      <div className="bg-white border-b border-[#ede7f6] px-4 sm:px-6 lg:px-8 py-4">
        <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-[#6100a4] text-white flex items-center justify-center font-bold shadow-sm">
              <span className="material-symbols-outlined text-xl">shelves</span>
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-xl font-extrabold text-[#1d1a20]">Taller Maraña • Almacén e Inventario</h1>
                <span className="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[11px] font-bold">
                  Catálogo & Materias Primas
                </span>
              </div>
              <p className="text-xs text-gray-500">
                Control de piezas tejidas listas para despacho y ovillos de lana en stock
              </p>
            </div>
          </div>

          {/* Sub Navigation */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => onNavigateToView('backoffice-orders')}
              className="px-3.5 py-1.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition-colors"
            >
              <span className="material-symbols-outlined text-sm">receipt_long</span>
              Pedidos & Transferencias
            </button>
            <button
              onClick={() => onNavigateToView('backoffice-inventory')}
              className="px-3.5 py-1.5 rounded-xl bg-[#6100a4] text-white text-xs font-bold shadow-sm flex items-center gap-1.5 cursor-pointer"
            >
              <span className="material-symbols-outlined text-sm">shelves</span>
              Inventario & Lanas
            </button>
            <button
              onClick={() => onNavigateToView('backoffice-reviews')}
              className="px-3.5 py-1.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition-colors"
            >
              <span className="material-symbols-outlined text-sm">reviews</span>
              Moderación Reseñas
            </button>
            <button
              onClick={() => onNavigateToView('catalog')}
              className="px-3.5 py-1.5 rounded-xl bg-[#f1dbff] text-[#6100a4] hover:bg-[#deb7ff] text-xs font-bold flex items-center gap-1.5 cursor-pointer transition-colors ml-2"
            >
              <span className="material-symbols-outlined text-sm">storefront</span>
              Ver Tienda
            </button>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        {/* Inventory KPI Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Total SKUs en Catálogo
              </span>
              <div className="text-2xl font-black text-[#1d1a20] mt-1">{products.length} modelos</div>
              <span className="text-[11px] text-[#6100a4] font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">auto_awesome</span>
                Diseños únicos de crochet
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-[#f1dbff] text-[#6100a4] flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">category</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Unidades Listas en Taller
              </span>
              <div className="text-2xl font-black text-emerald-700 mt-1">{totalStock} piezas</div>
              <span className="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">check_circle</span>
                Tejidas y con control de calidad
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">all_inclusive</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Stock Crítico / Bajo
              </span>
              <div className="text-2xl font-black text-amber-600 mt-1">{lowStockCount} modelos</div>
              <span className="text-[11px] text-amber-700 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">priority_high</span>
                Menos de 4 unidades restantes
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">notification_important</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Agotados / Por Tejer
              </span>
              <div className="text-2xl font-black text-rose-600 mt-1">{outOfStockCount} modelos</div>
              <span className="text-[11px] text-gray-500 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">hourglass_empty</span>
                En cola de producción manual
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">production_quantity_limits</span>
            </div>
          </div>
        </div>

        {/* Section 1: Finished Amigurumis Catalog & Live Stock Stepper */}
        <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm mb-8">
          <div className="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-gray-100">
            <div>
              <h2 className="text-lg font-extrabold text-[#1d1a20] flex items-center gap-2">
                <span className="material-symbols-outlined text-[#6100a4]">inventory</span>
                Inventario de Peluches & Amigurumis
              </h2>
              <p className="text-xs text-gray-500">
                Ajusta el stock en tiempo real. Los cambios se reflejan inmediatamente en la tienda web.
              </p>
            </div>

            <div className="flex flex-wrap items-center gap-3">
              {/* Category tabs */}
              <div className="flex items-center gap-1 bg-[#f7f4fa] p-1 rounded-xl">
                {[
                  { id: 'todos', label: 'Todos' },
                  { id: 'espacio', label: 'Espacio Cósmico' },
                  { id: 'bosque', label: 'Bosque' },
                  { id: 'bebes', label: 'Bebés' },
                ].map((tab) => (
                  <button
                    key={tab.id}
                    onClick={() => setCategoryFilter(tab.id)}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                      categoryFilter === tab.id
                        ? 'bg-white text-[#6100a4] shadow-sm'
                        : 'text-gray-500 hover:text-gray-800'
                    }`}
                  >
                    {tab.label}
                  </button>
                ))}
              </div>

              {/* Search input */}
              <div className="relative">
                <span className="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">
                  search
                </span>
                <input
                  type="text"
                  placeholder="Buscar modelo o SKU..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs outline-none focus:border-[#6100a4]"
                />
              </div>
            </div>
          </div>

          {/* Table of Products */}
          <div className="overflow-x-auto mt-4">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="border-b border-gray-100 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                  <th className="py-3 px-4">Amigurumi</th>
                  <th className="py-3 px-4">Categoría / Colección</th>
                  <th className="py-3 px-4">Precio (CLP)</th>
                  <th className="py-3 px-4">Stock Disponible</th>
                  <th className="py-3 px-4">Estado</th>
                  <th className="py-3 px-4 text-right">Tiempos de Tejido</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 text-xs">
                {filteredProducts.map((product) => {
                  const isLow = product.stock > 0 && product.stock <= 4;
                  const isOut = product.stock === 0;

                  return (
                    <tr key={product.id} className="hover:bg-[#faf9fc] transition-colors">
                      {/* Product details */}
                      <td className="py-4 px-4">
                        <div className="flex items-center gap-3">
                          <img
                            src={product.images[0].url}
                            alt={product.name}
                            className="w-14 h-14 rounded-2xl object-cover border border-gray-200"
                          />
                          <div>
                            <div className="font-extrabold text-sm text-[#1d1a20]">{product.name}</div>
                            <span className="text-[11px] text-gray-500 font-mono">SKU: {product.sku}</span>
                            <div className="text-[11px] text-gray-500">{product.tagline}</div>
                          </div>
                        </div>
                      </td>

                      {/* Category */}
                      <td className="py-4 px-4">
                        <span className="px-2.5 py-1 rounded-full bg-[#f1dbff] text-[#6100a4] font-bold text-[11px]">
                          {product.categoryLabel}
                        </span>
                      </td>

                      {/* Price with edit option */}
                      <td className="py-4 px-4">
                        {editingPriceId === product.id ? (
                          <div className="flex items-center gap-1.5">
                            <span className="text-gray-400">$</span>
                            <input
                              type="number"
                              value={tempPrice}
                              onChange={(e) => setTempPrice(Number(e.target.value))}
                              className="w-24 p-1 rounded-lg border border-[#6100a4] font-bold text-xs"
                            />
                            <button
                              onClick={() => handleSavePrice(product.id)}
                              className="px-2 py-1 rounded bg-[#6100a4] text-white text-[10px] font-bold cursor-pointer"
                            >
                              Guardar
                            </button>
                            <button
                              onClick={() => setEditingPriceId(null)}
                              className="px-1.5 py-1 rounded text-gray-400 hover:text-gray-600 text-[10px] cursor-pointer"
                            >
                              ✕
                            </button>
                          </div>
                        ) : (
                          <div className="flex items-center gap-2">
                            <span className="font-extrabold text-sm text-[#1d1a20]">
                              ${product.price.toLocaleString('es-CL')}
                            </span>
                            <button
                              onClick={() => {
                                setEditingPriceId(product.id);
                                setTempPrice(product.price);
                              }}
                              className="text-gray-400 hover:text-[#6100a4] transition-colors p-1"
                              title="Editar precio"
                            >
                              <span className="material-symbols-outlined text-sm">edit</span>
                            </button>
                          </div>
                        )}
                      </td>

                      {/* Stock Stepper */}
                      <td className="py-4 px-4">
                        <div className="inline-flex items-center border border-gray-200 rounded-xl bg-white shadow-2xs">
                          <button
                            type="button"
                            onClick={() => handleStockDelta(product.id, product.stock, -1)}
                            disabled={product.stock <= 0}
                            className="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-[#6100a4] hover:bg-gray-100 rounded-l-xl transition-colors disabled:opacity-30 cursor-pointer"
                          >
                            <span className="material-symbols-outlined text-sm">remove</span>
                          </button>
                          <span className="w-10 text-center font-extrabold text-sm text-gray-800">
                            {product.stock}
                          </span>
                          <button
                            type="button"
                            onClick={() => handleStockDelta(product.id, product.stock, 1)}
                            className="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-[#6100a4] hover:bg-gray-100 rounded-r-xl transition-colors cursor-pointer"
                          >
                            <span className="material-symbols-outlined text-sm">add</span>
                          </button>
                        </div>
                      </td>

                      {/* Status */}
                      <td className="py-4 px-4">
                        {isOut ? (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold text-[11px] border border-rose-200">
                            <span className="w-2 h-2 rounded-full bg-rose-500"></span>
                            Agotado
                          </span>
                        ) : isLow ? (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-bold text-[11px] border border-amber-200">
                            <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Stock Crítico ({product.stock})
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-200">
                            <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Disponible ({product.stock})
                          </span>
                        )}
                      </td>

                      {/* Craft Time */}
                      <td className="py-4 px-4 text-right">
                        <span className="text-gray-500 block text-[11px]">
                          ~{product.craftHours || 12} hrs de confección
                        </span>
                        <span className="text-emerald-700 font-semibold text-[10px]">
                          100% Crochet Manual
                        </span>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>

        {/* Section 2: Raw Materials & Yarn Supply (Images 16, 17) */}
        <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm">
          <div className="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-gray-100">
            <div>
              <h2 className="text-lg font-extrabold text-[#1d1a20] flex items-center gap-2">
                <span className="material-symbols-outlined text-[#6100a4]">yarn</span>
                Ovillos de Lana e Insumos Críticos del Taller
              </h2>
              <p className="text-xs text-gray-500">
                Monitorea el inventario de materias primas para evitar quiebres en la confección artesanal
              </p>
            </div>

            <div className="flex items-center gap-2 text-xs">
              <span className="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                Cadena de Suministro Activa
              </span>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
            {materials.map((mat) => (
              <div
                key={mat.id}
                className="p-4 rounded-2xl border border-gray-200 bg-[#faf9fc] hover:border-[#6100a4] transition-all space-y-3"
              >
                <div className="flex items-start justify-between">
                  <div>
                    <span className="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 uppercase tracking-wider">
                      {mat.type}
                    </span>
                    <h4 className="font-extrabold text-sm text-[#1d1a20] mt-1">{mat.name}</h4>
                    <span className="text-[11px] text-gray-500">Proveedor: {mat.supplier}</span>
                  </div>
                  {mat.status === 'critical' ? (
                    <span className="p-1 rounded-full bg-rose-100 text-rose-600" title="Stock Crítico">
                      <span className="material-symbols-outlined text-lg">error</span>
                    </span>
                  ) : mat.status === 'warning' ? (
                    <span className="p-1 rounded-full bg-amber-100 text-amber-600" title="Próximo a agotarse">
                      <span className="material-symbols-outlined text-lg">warning</span>
                    </span>
                  ) : (
                    <span className="p-1 rounded-full bg-emerald-100 text-emerald-600" title="Nivel Óptimo">
                      <span className="material-symbols-outlined text-lg">check</span>
                    </span>
                  )}
                </div>

                <div className="flex items-center justify-between text-xs pt-2 border-t border-gray-200">
                  <div>
                    <span className="text-gray-400 block text-[10px]">Disponible en Taller:</span>
                    <strong className="text-sm text-gray-800">
                      {mat.currentStock} {mat.unit}
                    </strong>
                  </div>
                  <button
                    type="button"
                    onClick={() => handleRestockMaterial(mat.id)}
                    className="px-3 py-1.5 rounded-xl bg-[#6100a4] hover:bg-[#4f0087] text-white text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                  >
                    <span className="material-symbols-outlined text-xs">add_shopping_cart</span>
                    Reabastecer
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
