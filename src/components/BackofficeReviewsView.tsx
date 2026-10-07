import React, { useState } from 'react';
import { Review } from '../types';

interface BackofficeReviewsViewProps {
  reviews: Review[];
  onUpdateReviewStatus: (reviewId: string, status: Review['status']) => void;
  onAddReviewResponse: (reviewId: string, responseText: string) => void;
  onNavigateToView: (view: string) => void;
  onShowToast: (msg: string, sub?: string, type?: 'success' | 'info' | 'error') => void;
}

export const BackofficeReviewsView: React.FC<BackofficeReviewsViewProps> = ({
  reviews,
  onUpdateReviewStatus,
  onAddReviewResponse,
  onNavigateToView,
  onShowToast,
}) => {
  const [filterTab, setFilterTab] = useState<'todos' | 'pending' | 'approved' | 'featured'>('todos');
  const [activeReplyId, setActiveReplyId] = useState<string | null>(null);
  const [replyText, setReplyText] = useState<string>('');
  const [zoomImage, setZoomImage] = useState<string | null>(null);

  // Metrics
  const totalReviews = reviews.length;
  const pendingCount = reviews.filter((r) => r.status === 'pending').length;
  const averageRating = (
    reviews.reduce((acc, r) => acc + r.rating, 0) / (reviews.length || 1)
  ).toFixed(1);
  const responseRate = '98%';

  const filteredReviews = reviews.filter((r) => {
    if (filterTab === 'todos') return true;
    return r.status === filterTab;
  });

  const handleApprove = (reviewId: string) => {
    onUpdateReviewStatus(reviewId, 'approved');
    onShowToast('Reseña Aprobada', 'Ahora es visible en la ficha de producto y tienda.', 'success');
  };

  const handleToggleFeature = (reviewId: string, currentStatus: Review['status']) => {
    const nextStatus = currentStatus === 'featured' ? 'approved' : 'featured';
    onUpdateReviewStatus(reviewId, nextStatus);
    onShowToast(
      nextStatus === 'featured' ? '¡Reseña Destacada!' : 'Reseña Desmarcada',
      nextStatus === 'featured'
        ? 'Aparecerá en el carrusel de testimonios de la página principal.'
        : 'Removida de la sección destacada.',
      'info'
    );
  };

  const handleReject = (reviewId: string) => {
    onUpdateReviewStatus(reviewId, 'rejected');
    onShowToast('Reseña Ocultada', 'La reseña fue archivada y no se mostrará al público.', 'error');
  };

  const handleSendReply = (reviewId: string) => {
    if (!replyText.trim()) return;
    onAddReviewResponse(reviewId, replyText);
    setActiveReplyId(null);
    setReplyText('');
    onShowToast('Respuesta Publicada', 'Tu respuesta como Taller Maraña quedó registrada.', 'success');
  };

  return (
    <div className="min-h-screen bg-[#f7f4fa] pb-16">
      {/* Backoffice Sub-Header */}
      <div className="bg-white border-b border-[#ede7f6] px-4 sm:px-6 lg:px-8 py-4">
        <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-[#6100a4] text-white flex items-center justify-center font-bold shadow-sm">
              <span className="material-symbols-outlined text-xl">rate_review</span>
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-xl font-extrabold text-[#1d1a20]">Taller Maraña • Reseñas & Experiencias</h1>
                <span className="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">
                  Moderación & Calidad
                </span>
              </div>
              <p className="text-xs text-gray-500">
                Supervisión de opiniones reales de clientes, fotos compartidas y respuestas oficiales del taller
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
              className="px-3.5 py-1.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition-colors"
            >
              <span className="material-symbols-outlined text-sm">shelves</span>
              Inventario & Lanas
            </button>
            <button
              onClick={() => onNavigateToView('backoffice-reviews')}
              className="px-3.5 py-1.5 rounded-xl bg-[#6100a4] text-white text-xs font-bold shadow-sm flex items-center gap-1.5 cursor-pointer"
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
        {/* KPI Summary Cards (Image 18) */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Calificación Global
              </span>
              <div className="flex items-center gap-2 mt-1">
                <span className="text-3xl font-black text-[#1d1a20]">{averageRating}</span>
                <span className="text-xs text-amber-500 font-bold">★★★★★</span>
              </div>
              <span className="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">verified</span>
                99% recomendación
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">star</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Total Reseñas Registradas
              </span>
              <div className="text-2xl font-black text-[#6100a4] mt-1">{totalReviews} opiniones</div>
              <span className="text-[11px] text-[#6100a4] font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">thumb_up</span>
                100% compras verificadas
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-[#f1dbff] text-[#6100a4] flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">reviews</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Pendientes de Moderación
              </span>
              <div className="text-2xl font-black text-rose-600 mt-1">{pendingCount} nuevas</div>
              <span className="text-[11px] text-gray-500 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">rule</span>
                Requieren revisión manual
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">pending</span>
            </div>
          </div>

          <div className="bg-white rounded-2xl p-4 sm:p-5 border border-[#ede7f6] shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Tasa de Respuesta
              </span>
              <div className="text-2xl font-black text-emerald-700 mt-1">{responseRate}</div>
              <span className="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                <span className="material-symbols-outlined text-xs">forum</span>
                Trato cercano y artesanal
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <span className="material-symbols-outlined text-2xl">mark_chat_read</span>
            </div>
          </div>
        </div>

        {/* Filter Navigation Tabs */}
        <div className="bg-white rounded-2xl p-4 border border-[#ede7f6] shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-1 bg-[#f7f4fa] p-1 rounded-xl">
            {[
              { id: 'todos', label: 'Todas las Reseñas' },
              { id: 'pending', label: `Pendientes (${pendingCount})` },
              { id: 'approved', label: 'Publicadas' },
              { id: 'featured', label: '⭐ Destacadas en Portada' },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setFilterTab(tab.id as any)}
                className={`px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                  filterTab === tab.id
                    ? 'bg-white text-[#6100a4] shadow-sm'
                    : 'text-gray-500 hover:text-gray-800'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          {/* AI Moderation Safety Summary Pill */}
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-200 text-xs text-[#6100a4] font-semibold">
            <span className="material-symbols-outlined text-sm">shield_with_heart</span>
            Filtro de Contenido Ético: Sin palabras ofensivas ni spam detectado
          </div>
        </div>

        {/* Review Cards Stream (Images 19, 20, 21) */}
        <div className="space-y-4">
          {filteredReviews.length === 0 ? (
            <div className="bg-white rounded-3xl p-12 text-center border border-gray-200 text-gray-400 text-xs">
              No hay reseñas bajo este filtro actualmente.
            </div>
          ) : (
            filteredReviews.map((review) => (
              <div
                key={review.id}
                className="bg-white rounded-3xl p-6 border border-[#ede7f6] shadow-sm hover:border-[#6100a4]/40 transition-all space-y-4"
              >
                {/* Header: User, Product, Verified badge, Rating */}
                <div className="flex flex-wrap items-start justify-between gap-4">
                  <div className="flex items-center gap-3">
                    <div className="w-11 h-11 rounded-2xl bg-[#f1dbff] text-[#6100a4] font-black text-sm flex items-center justify-center">
                      {review.customerInitials}
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <h3 className="font-extrabold text-sm text-[#1d1a20]">{review.customerName}</h3>
                        <span className="text-[11px] text-gray-400 font-mono">({review.emailMasked})</span>
                        {review.verified && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                            <span className="material-symbols-outlined text-xs">verified</span>
                            Compra Verificada
                          </span>
                        )}
                        {review.status === 'featured' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200">
                            ⭐ Destacada en Home
                          </span>
                        )}
                      </div>
                      <div className="text-[11px] text-gray-500 mt-0.5">
                        Producto: <strong className="text-gray-800">{review.productName}</strong> • Pedido {review.orderId} • {review.date}
                      </div>
                    </div>
                  </div>

                  {/* Rating Stars & Status */}
                  <div className="flex items-center gap-3">
                    <div className="flex text-amber-400 text-sm">
                      {Array.from({ length: 5 }).map((_, i) => (
                        <span key={i} className="material-symbols-outlined text-base">
                          {i < review.rating ? 'star' : 'star_border'}
                        </span>
                      ))}
                    </div>

                    <span
                      className={`text-[11px] font-extrabold px-2.5 py-0.5 rounded-full ${
                        review.status === 'approved'
                          ? 'bg-emerald-100 text-emerald-800'
                          : review.status === 'featured'
                          ? 'bg-amber-100 text-amber-800'
                          : review.status === 'pending'
                          ? 'bg-amber-100 text-amber-800'
                          : 'bg-rose-100 text-rose-800'
                      }`}
                    >
                      {review.status === 'approved' && '✓ Publicada'}
                      {review.status === 'featured' && '⭐ Destacada'}
                      {review.status === 'pending' && '⏳ Pendiente'}
                      {review.status === 'rejected' && '✕ Ocultada'}
                    </span>
                  </div>
                </div>

                {/* Review Text */}
                <p className="text-xs sm:text-sm text-gray-700 leading-relaxed pl-14">
                  "{review.comment}"
                </p>

                {/* Attached Customer Photos */}
                {review.photos && review.photos.length > 0 && (
                  <div className="pl-14 flex items-center gap-3">
                    {review.photos.map((photo, pIdx) => (
                      <div
                        key={pIdx}
                        onClick={() => setZoomImage(photo.url)}
                        className="relative group w-20 h-20 rounded-2xl overflow-hidden border border-gray-200 cursor-pointer"
                      >
                        <img src={photo.url} alt={photo.alt} className="w-full h-full object-cover transition-transform group-hover:scale-110" />
                        <div className="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                          <span className="material-symbols-outlined text-base">zoom_in</span>
                        </div>
                      </div>
                    ))}
                  </div>
                )}

                {/* Existing Workshop Response if present */}
                {review.workshopReply && (
                  <div className="ml-14 p-4 rounded-2xl bg-[#faf6fe] border border-[#ede7f6] space-y-1">
                    <div className="flex items-center gap-1.5 text-xs font-bold text-[#6100a4]">
                      <span className="material-symbols-outlined text-sm">store</span>
                      Respuesta oficial de {review.workshopReply.author} ({review.workshopReply.date}):
                    </div>
                    <p className="text-xs text-gray-700 italic">
                      "{review.workshopReply.text}"
                    </p>
                  </div>
                )}

                {/* Reply Form if opened */}
                {activeReplyId === review.id && (
                  <div className="ml-14 p-4 rounded-2xl bg-gray-50 border border-gray-200 space-y-2">
                    <label className="block text-xs font-bold text-gray-700">
                      Escribir respuesta como Taller Maraña:
                    </label>
                    <textarea
                      rows={3}
                      value={replyText}
                      onChange={(e) => setReplyText(e.target.value)}
                      placeholder="Ej. ¡Muchas gracias por tus cálidas palabras! Nos alegra mucho saber que te encantó..."
                      className="w-full p-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-[#6100a4] outline-none"
                    />
                    <div className="flex justify-end gap-2">
                      <button
                        type="button"
                        onClick={() => setActiveReplyId(null)}
                        className="px-3 py-1.5 rounded-xl text-xs text-gray-500 hover:bg-gray-200 cursor-pointer"
                      >
                        Cancelar
                      </button>
                      <button
                        type="button"
                        onClick={() => handleSendReply(review.id)}
                        className="px-4 py-1.5 rounded-xl bg-[#6100a4] text-white text-xs font-bold hover:bg-[#4f0087] cursor-pointer"
                      >
                        Enviar Respuesta
                      </button>
                    </div>
                  </div>
                )}

                {/* Moderation Actions Toolbar */}
                <div className="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs pl-14">
                  <div className="flex flex-wrap items-center gap-2">
                    {review.status !== 'approved' && review.status !== 'featured' && (
                      <button
                        onClick={() => handleApprove(review.id)}
                        className="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold flex items-center gap-1 transition-all cursor-pointer shadow-xs"
                      >
                        <span className="material-symbols-outlined text-sm">check</span>
                        Aprobar y Publicar
                      </button>
                    )}

                    <button
                      onClick={() => handleToggleFeature(review.id, review.status)}
                      className={`px-3 py-1.5 rounded-xl font-bold flex items-center gap-1 transition-all cursor-pointer ${
                        review.status === 'featured'
                          ? 'bg-amber-100 text-amber-900 border border-amber-300'
                          : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                      }`}
                    >
                      <span className="material-symbols-outlined text-sm">star</span>
                      {review.status === 'featured' ? 'Quitar de Destacados' : 'Destacar en Portada'}
                    </button>

                    {!review.workshopReply && activeReplyId !== review.id && (
                      <button
                        onClick={() => {
                          setActiveReplyId(review.id);
                          setReplyText(
                            `¡Hola ${review.customerName.split(' ')[0]}! Muchas gracias de corazón por compartir tu experiencia con nosotros. 💜 Nos alegra profundamente que te haya gustado tanto.`
                          );
                        }}
                        className="px-3 py-1.5 rounded-xl bg-purple-50 text-[#6100a4] hover:bg-purple-100 font-bold flex items-center gap-1 transition-all cursor-pointer"
                      >
                        <span className="material-symbols-outlined text-sm">reply</span>
                        Responder
                      </button>
                    )}
                  </div>

                  <button
                    onClick={() => handleReject(review.id)}
                    className="text-gray-400 hover:text-rose-600 flex items-center gap-1 transition-colors cursor-pointer"
                    title="Ocultar reseña si contiene spam"
                  >
                    <span className="material-symbols-outlined text-sm">archive</span>
                    Ocultar Reseña
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      </div>

      {/* Zoom Image Modal */}
      {zoomImage && (
        <div
          onClick={() => setZoomImage(null)}
          className="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 cursor-pointer"
        >
          <div className="max-w-2xl max-h-[85vh] p-2 bg-white rounded-3xl overflow-hidden relative">
            <img src={zoomImage} alt="Foto cliente ampliada" className="w-full h-full object-contain rounded-2xl" />
          </div>
        </div>
      )}
    </div>
  );
};
