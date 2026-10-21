import React, { useState } from 'react';
import { Order } from '../types';

interface BackofficeOrdersViewProps {
  orders: Order[];
  onUpdateOrderStatus: (orderId: string, status: Order['status'], note?: string) => void;
  onNavigateToView: (view: string) => void;
  onShowToast: (msg: string, sub?: string, type?: 'success' | 'info' | 'error') => void;
}

export const BackofficeOrdersView: React.FC<BackofficeOrdersViewProps> = ({
  orders,
  onUpdateOrderStatus,
  onNavigateToView,
  onShowToast,
}) => {
  const [selectedOrderId, setSelectedOrderId] = useState<string>(orders[0]?.id || '');
  const [statusFilter, setStatusFilter] = useState<string>('todos');
  const [bankFilter, setBankFilter] = useState<string>('todos');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [isZoomModalOpen, setIsZoomModalOpen] = useState<boolean>(false);
  const [rejectionReason, setRejectionReason] = useState<string>('');
  const [showRejectModal, setShowRejectModal] = useState<boolean>(false);

  const selectedOrder = orders.find((o) => o.id === selectedOrderId) || orders[0];

  // Filtering
  const filteredOrders = orders.filter((order) => {
    const matchesStatus =
      statusFilter === 'todos' ? true : order.status === statusFilter;
    const matchesBank =
      bankFilter === 'todos'
        ? true
        : order.bank.toLowerCase().includes(bankFilter.toLowerCase());
    const matchesSearch =
      order.customerName.toLowerCase().includes(searchQuery.toLowerCase()) ||
      order.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      order.customerRut.toLowerCase().includes(searchQuery.toLowerCase());

    return matchesStatus && matchesBank && matchesSearch;
  });

  // Calculate Metrics
  const readyForDispatch = orders.filter((o) => o.status === 'taller' || o.status === 'revision').length;
  const totalSalesToday = orders.reduce((acc, o) => acc + o.total, 0);
  const pendingValidation = orders.filter((o) => o.status === 'revision' || o.status === 'pendiente').length;

  const handleApprovePayment = () => {
    if (!selectedOrder) return;
    onUpdateOrderStatus(
      selectedOrder.id,
      'taller',
      'Transferencia bancaria aprobada por el taller. Comenzando empaque con certificado y regalo.'
    );
    onShowToast(
      '¡Transferencia Aprobada!',
      `Pedido ${selectedOrder.id} ha pasado a En Preparación con stock apartado.`,
      'success'
    );
  };

  const handleRejectConfirm = () => {
    if (!selectedOrder) return;
    onUpdateOrderStatus(
      selectedOrder.id,
      'rechazado',
      `Comprobante rechazado: ${rejectionReason || 'Monto o datos no coinciden'}`
    );
    setShowRejectModal(false);
    setRejectionReason('');
    onShowToast(
      'Comprobante Marcado como No Válido',
      `Se notificará al cliente para reenviar comprobante.`,
      'error'
    );
  };

  const handleMarkShipped = () => {
    if (!selectedOrder) return;
    onUpdateOrderStatus(
      selectedOrder.id,
      'despachado',
      `Paquete entregado a courier. Seguimiento activado.`
    );
    onShowToast(
      '¡Pedido Despachado!',
      `Se ha generado el comprobante de entrega para ${selectedOrder.customerName}.`,
      'success'
    );
  };

  const handleOpenWhatsApp = () => {
    if (!selectedOrder) return;
    const phoneClean = selectedOrder.customerPhone.replace(/[^0-9]/g, '');
    const message = encodeURIComponent(
      `¡Hola ${selectedOrder.customerName}! 🌸 Te escribimos de Maraña Crochet respecto a tu pedido ${selectedOrder.id}.\n\nHemos revisado tu transferencia de $${selectedOrder.total.toLocaleString('es-CO')} COP con éxito. Tu pedido de amigurumis tejidos a mano ya se encuentra en proceso de empaque con su certificado de adopción y aroma artesanal.\n\n¡Muchas gracias por apoyar nuestro taller artesanal! ✨`
    );
    window.open(`https://wa.me/${phoneClean}?text=${message}`, '_blank');
  };

  const handlePrintLabel = () => {
    window.print();
  };

  return (
    <div className="min-h-screen bg-[#f7f4fa] pb-16">
      {/* Backoffice Sub-Header */}
      <div className="bg-white border-b border-[#ede7f6] px-4 sm:px-6 lg:px-8 py-4">
        <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-[#6100a4] text-white flex items-center justify-center font-bold shadow-sm">
              <span className="material-symbols-outlined text-xl">inventory_2</span>
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-xl font-extrabold text-[#1d1a20]">Taller Maraña • Gestión de Pedidos</h1>
                <span className="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                  En Vivo
                </span>
              </div>
              <p className="text-xs text-gray-500">
                Validación de transferencias bancarias colombianas, empaque y despacho
              </p>
            </div>
          </div>

          {/* Sub Navigation */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => onNavigateToView('backoffice-orders')}
              className="px-3.5 py-1.5 rounded-xl bg-[#6100a4] text-white text-xs font-bold shadow-sm flex items-center gap-1.5 cursor-pointer"
            >
              <span className="material-symbols-outlined text-sm">receipt_long</span>
              Pedidos & Transferencias
            </button>
            <button
              onClick={() => onNavigateToView('backoffice-inventory')}
              className="px-3.5 py-1.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition-colors"
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
        {/* Top KPI Cards (Image 10) */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Listos para Despacho
              </span>
              <div className="text-2xl font-black text-[#1d1a20] mt-1">{readyForDispatch} pedidos</div>
              <span className="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">local_shipping</span>
                Inter Rapidísimo & Servientrega hoy
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">package_2</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Ventas Confirmadas Hoy
              </span>
              <div className="text-2xl font-black text-[#6100a4] mt-1">
                ${totalSalesToday.toLocaleString('es-CO')} <span className="text-xs font-bold text-gray-500">COP</span>
              </div>
              <span className="text-[11px] text-[#6100a4] font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">trending_up</span>
                100% transferencias recibidas
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-[#f1dbff] text-[#6100a4] flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">payments</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Transferencias Pendientes
              </span>
              <div className="text-2xl font-black text-rose-600 mt-1">{pendingValidation} por revisar</div>
              <span className="text-[11px] text-gray-500 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">schedule</span>
                Tiempo promedio: 8 mins
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Stock Apartado
              </span>
              <div className="text-2xl font-black text-gray-900 mt-1">
                {orders.reduce((acc, o) => acc + o.items.reduce((sum, i) => sum + i.quantity, 0), 0)} amigurumis
              </div>
              <span className="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">verified</span>
                Reservados en almacén
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">lock</span>
            </div>
          </div>
        </div>

        {/* Filter and Search Bar */}
        <div className="bg-white rounded-2xl p-4 border border-[#ede7f6] shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4">
          <div className="flex flex-wrap items-center gap-2">
            <div className="relative min-w-[240px]">
              <span className="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">
                search
              </span>
              <input
                type="text"
                placeholder="Buscar por cliente, RUT o N° pedido..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs outline-none focus:border-[#6100a4] focus:ring-1 focus:ring-[#6100a4]"
              />
            </div>

            {/* Status Tabs */}
            <div className="flex items-center gap-1 bg-[#f7f4fa] p-1 rounded-xl">
              {[
                { id: 'todos', label: 'Todos' },
                { id: 'revision', label: 'En Revisión' },
                { id: 'taller', label: 'En Taller' },
                { id: 'despachado', label: 'Despachados' },
                { id: 'pendiente', label: 'Pendientes' },
              ].map((tab) => (
                <button
                  key={tab.id}
                  onClick={() => setStatusFilter(tab.id)}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                    statusFilter === tab.id
                      ? 'bg-white text-[#6100a4] shadow-sm'
                      : 'text-gray-500 hover:text-gray-800'
                  }`}
                >
                  {tab.label}
                </button>
              ))}
            </div>
          </div>

          <div className="flex items-center gap-2 text-xs">
            <span className="text-gray-500 font-semibold">Banco:</span>
            <select
              value={bankFilter}
              onChange={(e) => setBankFilter(e.target.value)}
              className="border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-semibold bg-white outline-none focus:border-[#6100a4]"
            >
              <option value="todos">Todas las Entidades</option>
              <option value="Bancolombia">Bancolombia</option>
              <option value="Nequi">Nequi</option>
              <option value="Daviplata">Daviplata</option>
              <option value="Davivienda">Davivienda</option>
            </select>
          </div>
        </div>

        {/* Master-Detail Split Screen (Images 11, 12, 13) */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
          {/* LEFT: Master Order List (lg:col-span-5) */}
          <div className="lg:col-span-5 bg-white rounded-3xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm space-y-3">
            <div className="flex items-center justify-between pb-3 border-b border-gray-100">
              <span className="text-xs font-bold text-gray-500 uppercase tracking-wider">
                Listado de Pedidos ({filteredOrders.length})
              </span>
              <span className="text-xs text-gray-400">Clic para inspeccionar</span>
            </div>

            <div className="space-y-2.5 max-h-[750px] overflow-y-auto pr-1">
              {filteredOrders.length === 0 ? (
                <div className="text-center py-12 text-gray-400 text-xs">
                  No se encontraron pedidos con los filtros seleccionados.
                </div>
              ) : (
                filteredOrders.map((order) => {
                  const isSelected = order.id === selectedOrderId;
                  return (
                    <div
                      key={order.id}
                      onClick={() => setSelectedOrderId(order.id)}
                      className={`p-4 rounded-2xl border-2 transition-all cursor-pointer text-left ${
                        isSelected
                          ? 'border-[#6100a4] bg-[#faf6fe] shadow-sm'
                          : 'border-transparent bg-[#fcfbfe] hover:border-gray-200'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <span className="font-extrabold text-sm text-[#1d1a20]">{order.id}</span>
                        {/* Status Badge */}
                        <span
                          className={`text-[10px] font-extrabold px-2.5 py-0.5 rounded-full ${
                            order.status === 'revision'
                              ? 'bg-purple-100 text-purple-800'
                              : order.status === 'taller'
                              ? 'bg-amber-100 text-amber-800'
                              : order.status === 'despachado'
                              ? 'bg-emerald-100 text-emerald-800'
                              : order.status === 'rechazado'
                              ? 'bg-rose-100 text-rose-800'
                              : 'bg-blue-100 text-blue-800'
                          }`}
                        >
                          {order.status === 'revision' && '✓ TEF en Revisión'}
                          {order.status === 'taller' && '⚙ En Preparación'}
                          {order.status === 'despachado' && '🚚 Despachado'}
                          {order.status === 'rechazado' && '✕ Rechazado'}
                          {order.status === 'pendiente' && '⏳ Por Validar'}
                        </span>
                      </div>

                      <div className="flex items-center justify-between text-xs mb-2">
                        <span className="font-bold text-gray-800 truncate">{order.customerName}</span>
                        <span className="text-gray-400">{order.date}</span>
                      </div>

                      {/* Items thumbnails preview */}
                      <div className="flex items-center justify-between pt-2 border-t border-gray-100/70">
                        <div className="flex -space-x-2 overflow-hidden">
                          {order.items.map((item, idx) => (
                            <img
                              key={idx}
                              src={item.image}
                              alt={item.productName}
                              className="inline-block h-7 w-7 rounded-full ring-2 ring-white object-cover"
                            />
                          ))}
                          <span className="pl-3 text-[11px] text-gray-500 self-center">
                            {order.items.reduce((s, i) => s + i.quantity, 0)} items
                          </span>
                        </div>
                        <div className="font-extrabold text-sm text-[#6100a4]">
                          ${order.total.toLocaleString('es-CO')} COP
                        </div>
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </div>

          {/* RIGHT: Detailed Order Inspector (lg:col-span-7) */}
          {selectedOrder && (
            <div className="lg:col-span-7 space-y-6">
              {/* Header Card */}
              <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                  <div>
                    <div className="flex items-center gap-2">
                      <h2 className="text-xl font-extrabold text-[#1d1a20]">{selectedOrder.id}</h2>
                      <span className="text-xs text-gray-400">• {selectedOrder.date}</span>
                    </div>
                    <p className="text-xs text-gray-500 mt-0.5">
                      Cliente: <strong className="text-gray-800">{selectedOrder.customerName}</strong> ({selectedOrder.customerRut})
                    </p>
                  </div>

                  {/* Actions Dropdown / Status */}
                  <div className="flex items-center gap-2">
                    <button
                      onClick={handleOpenWhatsApp}
                      className="px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                      title="Chatear con el cliente por WhatsApp"
                    >
                      <span className="material-symbols-outlined text-base">chat</span>
                      WhatsApp
                    </button>
                    <button
                      onClick={handlePrintLabel}
                      className="px-3 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                    >
                      <span className="material-symbols-outlined text-base">print</span>
                      Etiqueta
                    </button>
                  </div>
                </div>

                {/* Primary Action Buttons */}
                <div className="mt-4 flex flex-wrap items-center gap-2">
                  {selectedOrder.status !== 'taller' && selectedOrder.status !== 'despachado' && (
                    <button
                      onClick={handleApprovePayment}
                      className="px-4 py-2.5 rounded-xl bg-[#6100a4] hover:bg-[#4f0087] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer"
                    >
                      <span className="material-symbols-outlined text-base">verified</span>
                      Aprobar Comprobante y Preparar
                    </button>
                  )}

                  {selectedOrder.status === 'taller' && (
                    <button
                      onClick={handleMarkShipped}
                      className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all cursor-pointer"
                    >
                      <span className="material-symbols-outlined text-base">local_shipping</span>
                      Marcar como Despachado
                    </button>
                  )}

                  <button
                    onClick={() => setShowRejectModal(true)}
                    className="px-3.5 py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer"
                  >
                    <span className="material-symbols-outlined text-base">block</span>
                    Rechazar Comprobante
                  </button>
                </div>
              </div>

              {/* Colombian Bank Transfer Receipt Inspector */}
              <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm">
                <div className="flex items-center justify-between mb-4">
                  <div className="flex items-center gap-2">
                    <span className="material-symbols-outlined text-[#6100a4] text-xl">receipt_long</span>
                    <h3 className="font-extrabold text-sm text-[#1d1a20]">
                      Comprobante Bancario Transferencia Electrónica (TEF)
                    </h3>
                  </div>
                  <span className="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                    <span className="material-symbols-outlined text-xs">check_circle</span>
                    Monto Coincide 100%
                  </span>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-12 gap-5">
                  {/* Voucher Preview Image with Zoom */}
                  <div className="md:col-span-5 relative group rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 max-h-56">
                    <img
                      src={selectedOrder.transferDetails.voucherUrl || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80'}
                      alt="Comprobante Bancario"
                      className="w-full h-full object-cover transition-transform group-hover:scale-105"
                    />
                    <div
                      onClick={() => setIsZoomModalOpen(true)}
                      className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer text-white text-xs font-bold gap-1.5"
                    >
                      <span className="material-symbols-outlined text-lg">zoom_in</span>
                      Ampliar Comprobante
                    </div>
                  </div>

                  {/* Transfer Details Breakdown */}
                  <div className="md:col-span-7 space-y-2.5 text-xs">
                    <div className="p-3 rounded-xl bg-[#faf6fe] border border-[#ede7f6]">
                      <div className="text-[11px] text-gray-500">Banco de Destino:</div>
                      <strong className="text-gray-900 text-sm">{selectedOrder.bank} ({selectedOrder.accountType})</strong>
                    </div>

                    <div className="grid grid-cols-2 gap-2">
                      <div className="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <span className="text-[10px] text-gray-400 block">N° Operación / TEF</span>
                        <strong className="text-gray-800 font-mono">{selectedOrder.transferDetails.tefCode}</strong>
                      </div>
                      <div className="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <span className="text-[10px] text-gray-400 block">Monto Transferido</span>
                        <strong className="text-[#6100a4] font-extrabold">
                          ${selectedOrder.transferDetails.transferredAmount.toLocaleString('es-CO')} COP
                        </strong>
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-2">
                      <div className="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <span className="text-[10px] text-gray-400 block">Fecha y Hora TEF</span>
                        <span className="text-gray-800 font-medium">{selectedOrder.transferDetails.transferDate}</span>
                      </div>
                      <div className="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <span className="text-[10px] text-gray-400 block">RUT Pagador</span>
                        <span className="text-gray-800 font-medium">{selectedOrder.customerRut}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Reserved Items in this Order */}
              <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-extrabold text-sm text-[#1d1a20] flex items-center gap-2">
                    <span className="material-symbols-outlined text-[#6100a4] text-xl">inventory</span>
                    Amigurumis a Confeccionar y Empacar ({selectedOrder.items.length})
                  </h3>
                  <span className="text-xs text-gray-400">Stock descontado automáticamente</span>
                </div>

                <div className="space-y-3">
                  {selectedOrder.items.map((item, idx) => (
                    <div
                      key={idx}
                      className="flex items-center justify-between p-3 rounded-2xl bg-[#faf9fc] border border-gray-100"
                    >
                      <div className="flex items-center gap-3">
                        <img
                          src={item.image}
                          alt={item.productName}
                          className="w-14 h-14 rounded-xl object-cover border border-gray-200"
                        />
                        <div>
                          <h4 className="text-xs font-extrabold text-[#1d1a20]">{item.productName}</h4>
                          <span className="text-[11px] text-gray-500 font-mono">ID: {item.productId}</span>
                          <div className="text-[11px] text-[#6100a4] font-semibold">
                            {item.colorDetails}
                          </div>
                        </div>
                      </div>

                      <div className="text-right">
                        <span className="text-xs font-bold text-gray-700 block">
                          Cant: <strong>{item.quantity}</strong>
                        </span>
                        <span className="text-xs font-extrabold text-[#1d1a20]">
                          ${(item.price * item.quantity).toLocaleString('es-CO')}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Shipping & Delivery Label Card (Image 13) */}
              <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm">
                <div className="flex items-center justify-between mb-3">
                  <h3 className="font-extrabold text-sm text-[#1d1a20] flex items-center gap-2">
                    <span className="material-symbols-outlined text-[#6100a4] text-xl">local_shipping</span>
                    Rótulo de Envío y Despacho
                  </h3>
                  <span className="text-xs font-bold text-[#6100a4] bg-[#f1dbff] px-2.5 py-0.5 rounded-full">
                    {selectedOrder.shippingMethod === 'nacional-colombia' || selectedOrder.shippingMethod === 'express-rm' ? 'Envío Nacional (Inter Rapidísimo / Servientrega)' : 'Envío Local Neiva'}
                  </span>
                </div>

                <div className="p-4 rounded-2xl bg-[#fcfbfe] border border-[#ede7f6] space-y-2 text-xs">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                      <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Destinatario</span>
                      <strong className="text-sm text-gray-800">{selectedOrder.customerName}</strong>
                      <p className="text-gray-500">Cédula CC: {selectedOrder.customerRut}</p>
                      <p className="text-gray-500">Tel: {selectedOrder.customerPhone}</p>
                    </div>

                    <div>
                      <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Dirección de Destino</span>
                      <p className="font-semibold text-gray-800">
                        {selectedOrder.address.street} {selectedOrder.address.dept || ''}
                      </p>
                      <p className="text-gray-600">
                        {selectedOrder.address.comuna}, {selectedOrder.address.region}
                      </p>
                      {selectedOrder.address.instructions && (
                        <p className="text-amber-800 text-[11px] bg-amber-50 p-1.5 rounded-lg mt-1">
                          Nota: {selectedOrder.address.instructions}
                        </p>
                      )}
                    </div>
                  </div>
                </div>

                {/* Gift dedication display if present */}
                {selectedOrder.giftDedication && (
                  <div className="mt-4 p-4 rounded-2xl bg-pink-50/60 border border-pink-200">
                    <div className="flex items-center gap-1.5 text-xs font-bold text-pink-900 mb-1">
                      <span className="material-symbols-outlined text-base">card_giftcard</span>
                      Tarjeta de Regalo para Caligrafiar:
                    </div>
                    <blockquote className="text-xs text-pink-800 italic font-serif pl-2 border-l-2 border-pink-300">
                      "{selectedOrder.giftDedication}"
                    </blockquote>
                  </div>
                )}
              </div>

              {/* Audit Timeline */}
              <div className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm">
                <h3 className="font-extrabold text-sm text-[#1d1a20] mb-3 flex items-center gap-2">
                  <span className="material-symbols-outlined text-[#6100a4] text-xl">history</span>
                  Historial de Auditoría de la Orden
                </h3>
                <div className="space-y-3">
                  {selectedOrder.auditLog.map((log, idx) => (
                    <div key={idx} className="flex items-start gap-3 text-xs">
                      <span className="w-2 h-2 rounded-full bg-[#6100a4] mt-1.5 flex-shrink-0"></span>
                      <div>
                        <div className="font-bold text-gray-800">{log.note}</div>
                        <span className="text-[10px] text-gray-400">
                          {log.time}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Zoom Modal for Receipt */}
      {isZoomModalOpen && selectedOrder && (
        <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto relative">
            <button
              onClick={() => setIsZoomModalOpen(false)}
              className="absolute top-4 right-4 w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 cursor-pointer"
            >
              <span className="material-symbols-outlined text-xl">close</span>
            </button>
            <h3 className="text-base font-bold text-gray-900 mb-4">
              Comprobante de Transferencia • {selectedOrder.id}
            </h3>
            <img
              src={selectedOrder.transferDetails.voucherUrl || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80'}
              alt="Voucher Ampliado"
              className="w-full rounded-2xl border border-gray-200"
            />
          </div>
        </div>
      )}

      {/* Rejection Modal */}
      {showRejectModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl">
            <h3 className="text-base font-bold text-rose-600 mb-2 flex items-center gap-2">
              <span className="material-symbols-outlined">warning</span>
              Rechazar Comprobante Bancario
            </h3>
            <p className="text-xs text-gray-600 mb-4">
              Indica el motivo para notificar al cliente vía WhatsApp o correo electrónico.
            </p>
            <textarea
              rows={3}
              value={rejectionReason}
              onChange={(e) => setRejectionReason(e.target.value)}
              placeholder="Ej. El monto transferido no coincide con el total de la orden o el voucher es ilegible..."
              className="w-full p-3 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-rose-400 outline-none mb-4"
            />
            <div className="flex justify-end gap-2">
              <button
                onClick={() => setShowRejectModal(false)}
                className="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100 cursor-pointer"
              >
                Cancelar
              </button>
              <button
                onClick={handleRejectConfirm}
                className="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 cursor-pointer"
              >
                Confirmar Rechazo
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

