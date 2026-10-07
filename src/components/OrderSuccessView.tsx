import React from 'react';
import { Order } from '../types';

interface OrderSuccessViewProps {
  order: Order;
  onBackToStore: () => void;
  onGoToBackoffice: () => void;
}

export const OrderSuccessView: React.FC<OrderSuccessViewProps> = ({
  order,
  onBackToStore,
  onGoToBackoffice,
}) => {
  return (
    <main className="min-h-screen bg-[#faf8fc] py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        {/* Celebration Header */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 shadow-[0_10px_30px_rgba(16,185,129,0.2)] mb-4">
            <span className="material-symbols-outlined text-4xl">check_circle</span>
          </div>
          <span className="inline-block px-3 py-1 rounded-full bg-[#f1dbff] text-[#6100a4] text-xs font-bold uppercase tracking-wider mb-2">
            ¡Transferencia Recibida & Stock Reservado!
          </span>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-[#1d1a20] tracking-tight">
            ¡Gracias por tu compra, {order.customerName.split(' ')[0]}!
          </h1>
          <p className="text-gray-600 text-sm mt-2 max-w-lg mx-auto">
            Hemos registrado tu pedido <strong className="text-[#6100a4]">{order.id}</strong>. Nuestro taller artesanal está revisando tu comprobante y comenzará a preparar tu amigurumi.
          </p>
        </div>

        {/* Order Details Card */}
        <div className="bg-white rounded-3xl p-6 sm:p-8 shadow-[0_4px_30px_rgba(97,0,164,0.06)] border border-[#ede7f6] mb-6">
          <div className="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Número de Pedido</span>
              <div className="text-xl font-extrabold text-[#6100a4]">{order.id}</div>
            </div>
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</span>
              <div className="text-sm font-bold text-gray-800">{order.date}</div>
            </div>
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</span>
              <div>
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                  <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                  Comprobante en Validación
                </span>
              </div>
            </div>
          </div>

          {/* Reserved Items */}
          <div className="py-6 border-b border-gray-100">
            <h3 className="text-xs font-bold uppercase text-gray-500 tracking-wider mb-4">
              Amigurumis Reservados ({order.items.length})
            </h3>
            <div className="space-y-3">
              {order.items.map((item, idx) => (
                <div key={idx} className="flex items-center justify-between gap-4">
                  <div className="flex items-center gap-3">
                    <img
                      src={item.image}
                      alt={item.productName}
                      className="w-14 h-14 rounded-2xl object-cover bg-gray-50 border border-gray-100"
                    />
                    <div>
                      <h4 className="text-sm font-bold text-[#1d1a20]">{item.productName}</h4>
                      <p className="text-xs text-gray-500">
                        Cantidad: {item.quantity} • {item.colorDetails}
                      </p>
                    </div>
                  </div>
                  <div className="text-right">
                    <span className="text-sm font-extrabold text-[#1d1a20]">
                      ${(item.price * item.quantity).toLocaleString('es-CL')} CLP
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Shipping & Payment Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 py-6 border-b border-gray-100 text-xs">
            <div>
              <h4 className="font-bold text-gray-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <span className="material-symbols-outlined text-sm text-[#6100a4]">local_shipping</span>
                Dirección de Entrega
              </h4>
              <p className="text-gray-600 font-medium">{order.customerName}</p>
              <p className="text-gray-600">{order.address.street} {order.address.dept || ''}</p>
              <p className="text-gray-600">{order.address.comuna}, {order.address.region}</p>
              <p className="text-gray-500 mt-1">Método: <strong>{order.shippingMethod === 'express-rm' ? 'Chilexpress Prioritario' : 'Envío a Regiones'}</strong></p>
              {order.address.instructions && (
                <p className="text-gray-500 italic mt-1">Nota: "{order.address.instructions}"</p>
              )}
            </div>

            <div>
              <h4 className="font-bold text-gray-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <span className="material-symbols-outlined text-sm text-[#6100a4]">account_balance</span>
                Detalle de Transferencia
              </h4>
              <p className="text-gray-600">Banco: <strong>{order.bank} ({order.accountType})</strong></p>
              <p className="text-gray-600">Total Transferido: <strong className="text-[#6100a4]">${order.total.toLocaleString('es-CL')} CLP</strong></p>
              <p className="text-gray-600">Código TEF: <strong>{order.transferDetails.tefCode}</strong></p>
              <p className="text-gray-500 mt-1">Notificación: Enviada a {order.customerEmail}</p>
            </div>
          </div>

          {/* Gift dedication card if any */}
          {order.giftDedication && (
            <div className="mt-5 p-4 rounded-2xl bg-[#faf6fe] border border-[#e8dcf8] flex items-start gap-3">
              <span className="material-symbols-outlined text-[#6100a4] text-xl mt-0.5">card_giftcard</span>
              <div>
                <span className="text-xs font-bold text-[#6100a4] block">Tarjeta de Regalo Personalizada Incluida:</span>
                <p className="text-xs text-gray-700 italic mt-0.5 font-serif">"{order.giftDedication}"</p>
              </div>
            </div>
          )}

          {/* Bottom Total Bar */}
          <div className="mt-6 flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-100">
            <div>
              <span className="text-xs text-gray-500">Monto Final Pagado</span>
              <div className="text-2xl font-black text-[#6100a4]">
                ${order.total.toLocaleString('es-CL')} <span className="text-xs font-semibold text-gray-500">CLP</span>
              </div>
            </div>

            <div className="flex flex-wrap items-center gap-3">
              <button
                type="button"
                onClick={() => window.print()}
                className="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
              >
                <span className="material-symbols-outlined text-base">print</span>
                Imprimir Recibo
              </button>

              <button
                type="button"
                onClick={onGoToBackoffice}
                className="px-4 py-2.5 rounded-xl bg-[#f1dbff] hover:bg-[#deb7ff] text-[#6100a4] text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
              >
                <span className="material-symbols-outlined text-base">admin_panel_settings</span>
                Ver en Panel de Taller (Backoffice)
              </button>
            </div>
          </div>
        </div>

        {/* Back To Store Button */}
        <div className="text-center">
          <button
            type="button"
            onClick={onBackToStore}
            className="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#6100a4] hover:bg-[#4f0087] text-white text-sm font-bold shadow-md hover:shadow-lg transition-all cursor-pointer"
          >
            <span className="material-symbols-outlined text-lg">storefront</span>
            Volver a la Tienda y Seguir Explorando
          </button>
        </div>
      </div>
    </main>
  );
};
