import React, { useState } from 'react';
import { CartItem, Order } from '../types';

interface CheckoutViewProps {
  cartItems: CartItem[];
  onBackToStore: () => void;
  onOrderPlaced: (order: Order) => void;
}

export const CheckoutView: React.FC<CheckoutViewProps> = ({
  cartItems,
  onBackToStore,
  onOrderPlaced,
}) => {
  // Contact & Shipping Form (Colombia)
  const [email, setEmail] = useState('valentina.castro@gmail.com');
  const [phone, setPhone] = useState('+57 312 456 7890');
  const [fullName, setFullName] = useState('Valentina Castro Pizarro');
  const [rut, setRut] = useState('1.075.289.412');
  const [region, setRegion] = useState('Bogotá D.C.');
  const [comuna, setComuna] = useState('Bogotá (Chapinero)');
  const [address, setAddress] = useState('Carrera 15 # 85-30');
  const [apartment, setApartment] = useState('Apto 402, Torre 1');
  const [notes, setNotes] = useState('Dejar con vigilancia 24 horas si no respondo el timbre');

  // Gift Dedication
  const [isGift, setIsGift] = useState(true);
  const [giftDedication, setGiftDedication] = useState(
    'Para mi sobrina regalona Sofía en su 5to cumpleaños. ¡Que la Arañita Cósmica cuide tus sueños y te inspire a explorar las estrellas! ✨ Con todo el amor de tu tía Valen desde Neiva.'
  );

  // Shipping Method
  const [shippingMethod, setShippingMethod] = useState<'nacional' | 'neiva' | 'retiro'>('nacional');

  // Bank Selected for Transfer
  const [selectedBank, setSelectedBank] = useState<'bancolombia' | 'nequi' | 'daviplata'>('bancolombia');

  // Receipt upload & TEF info
  const [receiptImage, setReceiptImage] = useState<string | null>(
    'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80'
  );
  const [tefCode, setTefCode] = useState('TRF-884920');
  const [couponCode, setCouponCode] = useState('');
  const [discountPercent, setDiscountPercent] = useState(0);
  const [couponError, setCouponError] = useState('');
  const [copiedField, setCopiedField] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Totals calculation
  const subtotal = cartItems.reduce((acc, item) => acc + item.product.price * item.quantity, 0);
  const shippingCost = shippingMethod === 'nacional' ? 14500 : shippingMethod === 'neiva' ? 6000 : 0;
  const discountAmount = Math.round(subtotal * (discountPercent / 100));
  const total = subtotal - discountAmount + shippingCost;

  const handleApplyCoupon = (e: React.FormEvent) => {
    e.preventDefault();
    setCouponError('');
    const clean = couponCode.trim().toUpperCase();
    if (clean === 'MARANA10' || clean === 'COSMICO10') {
      setDiscountPercent(10);
    } else if (clean === 'BIENVENIDA' || clean === 'AMIGURUMI15') {
      setDiscountPercent(15);
    } else {
      setCouponError('Cupón inválido o expirado. Prueba MARANA10 o BIENVENIDA');
    }
  };

  const copyToClipboard = (text: string, field: string) => {
    navigator.clipboard.writeText(text);
    setCopiedField(field);
    setTimeout(() => setCopiedField(null), 2000);
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        setReceiptImage(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmitOrder = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    setTimeout(() => {
      const orderNumber = `#MC-${new Date().getFullYear()}-${Math.floor(100 + Math.random() * 900)}`;

      const newOrder: Order = {
        id: orderNumber,
        date: 'Hoy ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' hrs',
        customerName: fullName || 'Valentina Castro Pizarro',
        customerEmail: email || 'cliente@correo.cl',
        customerPhone: '+56 ' + phone,
        customerRut: rut || '18.492.301-K',
        address: {
          region: region,
          comuna: comuna,
          street: address,
          dept: apartment,
          instructions: notes,
        },
        shippingMethod: shippingMethod === 'nacional' ? 'nacional-colombia' : shippingMethod === 'neiva' ? 'envio-neiva' : 'retiro',
        shippingCost: shippingCost,
        items: cartItems.map((item) => ({
          productId: item.product.id,
          productName: item.product.name,
          colorDetails: item.product.tagline || 'Edición Cósmica Hecha a Mano',
          quantity: item.quantity,
          price: item.product.price,
          image: item.product.images[0].url,
        })),
        subtotal: subtotal,
        total: total,
        status: 'revision',
        bank: selectedBank === 'bancolombia' ? 'Bancolombia' : selectedBank === 'nequi' ? 'Nequi' : 'Daviplata',
        accountType: selectedBank === 'bancolombia' ? 'Cuenta de Ahorros' : selectedBank === 'nequi' ? 'Nequi' : 'Daviplata',
        transferDetails: {
          folio: tefCode.replace(/[^0-9]/g, '') || '94827103',
          tefCode: tefCode || 'TEF-884920',
          transferredAmount: total,
          transferDate: 'Hoy, ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' hrs',
          channel: 'App Santander Móvil',
          subject: orderNumber,
          voucherFilename: 'comprobante_transferencia_' + (tefCode || 'voucher') + '.png',
          voucherUrl: receiptImage || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80',
        },
        giftDedication: isGift ? giftDedication : undefined,
        auditLog: [
          {
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            note: 'Pedido creado en línea. Comprobante bancario cargado por el cliente.',
            type: 'info',
          },
        ],
      };

      setIsSubmitting(false);
      onOrderPlaced(newOrder);
    }, 1200);
  };

  return (
    <main className="min-h-screen bg-[#faf8fc] py-10 px-4 sm:px-6 lg:px-8">
      <div className="max-w-6xl mx-auto">
        {/* Navigation Breadcrumb / Top Bar */}
        <div className="flex flex-wrap items-center justify-between gap-4 mb-8">
          <button
            onClick={onBackToStore}
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#6100a4] hover:text-[#4f0087] transition-colors group cursor-pointer"
          >
            <span className="material-symbols-outlined text-lg transition-transform group-hover:-translate-x-1">
              arrow_back
            </span>
            Volver a la tienda
          </button>

          {/* Stepper */}
          <div className="flex items-center gap-2 text-xs font-semibold">
            <span className="flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
              <span className="material-symbols-outlined text-sm">check_circle</span>
              1. Carrito
            </span>
            <span className="text-gray-300">──</span>
            <span className="flex items-center gap-1.5 text-white bg-[#6100a4] px-3.5 py-1 rounded-full shadow-sm">
              <span className="w-4 h-4 rounded-full bg-white text-[#6100a4] flex items-center justify-center text-[10px] font-bold">2</span>
              Datos & Transferencia
            </span>
            <span className="text-gray-300">──</span>
            <span className="flex items-center gap-1.5 text-gray-400 bg-gray-100 px-3 py-1 rounded-full">
              <span className="w-4 h-4 rounded-full bg-gray-300 text-white flex items-center justify-center text-[10px] font-bold">3</span>
              Confirmación
            </span>
          </div>
        </div>

        {/* Page Title */}
        <div className="mb-8">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#f1dbff] text-[#6100a4] text-xs font-bold uppercase tracking-wider mb-2">
            <span className="material-symbols-outlined text-sm">verified_user</span>
            Pago Seguro Directo por Transferencia
          </div>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-[#1d1a20] tracking-tight">
            Finalizar Pedido Artesanal
          </h1>
          <p className="text-gray-600 text-sm mt-1">
            Reserva tus amigurumis tejidos a mano. Tu stock queda apartado inmediatamente por 30 minutos mientras confirmamos tu transferencia.
          </p>
        </div>

        <form onSubmit={handleSubmitOrder}>
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {/* LEFT COLUMN: Customer data, Shipping, Gift, Payment Voucher */}
            <div className="lg:col-span-7 space-y-6">
              {/* Step 1: Contact Information */}
              <div className="bg-white rounded-3xl p-6 sm:p-7 shadow-[0_4px_24px_rgba(97,0,164,0.06)] border border-[#ede7f6]">
                <div className="flex items-center gap-3 mb-5">
                  <div className="w-9 h-9 rounded-2xl bg-[#f1dbff] text-[#6100a4] flex items-center justify-center">
                    <span className="material-symbols-outlined text-xl">person</span>
                  </div>
                  <div>
                    <h2 className="text-lg font-bold text-[#1d1a20]">1. Datos de Contacto y Facturación</h2>
                    <p className="text-xs text-gray-500">Para notificarte el estado de confección y envío</p>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="sm:col-span-2">
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Nombre y Apellidos Completos *
                    </label>
                    <input
                      type="text"
                      required
                      value={fullName}
                      onChange={(e) => setFullName(e.target.value)}
                      placeholder="Ej. Valentina Castro Pizarro"
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Correo Electrónico *
                    </label>
                    <input
                      type="email"
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="tu@correo.com"
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Cédula de Ciudadanía (CC) / NIT *
                    </label>
                    <input
                      type="text"
                      required
                      value={rut}
                      onChange={(e) => setRut(e.target.value)}
                      placeholder="1.075.289.412"
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>

                  <div className="sm:col-span-2">
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Teléfono Móvil (WhatsApp para despacho) *
                    </label>
                    <div className="flex">
                      <span className="inline-flex items-center px-3.5 rounded-l-xl border border-r-0 border-gray-200 bg-gray-50 text-gray-500 text-sm font-semibold">
                        🇨🇴 +57
                      </span>
                      <input
                        type="tel"
                        required
                        value={phone}
                        onChange={(e) => setPhone(e.target.value)}
                        placeholder="312 456 7890"
                        className="w-full px-4 py-3 rounded-r-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Step 2: Shipping Destination */}
              <div className="bg-white rounded-3xl p-6 sm:p-7 shadow-[0_4px_24px_rgba(97,0,164,0.06)] border border-[#ede7f6]">
                <div className="flex items-center gap-3 mb-5">
                  <div className="w-9 h-9 rounded-2xl bg-[#f1dbff] text-[#6100a4] flex items-center justify-center">
                    <span className="material-symbols-outlined text-xl">local_shipping</span>
                  </div>
                  <div>
                    <h2 className="text-lg font-bold text-[#1d1a20]">2. Dirección de Entrega en Colombia</h2>
                    <p className="text-xs text-gray-500">Despachos nacionales desde nuestro taller en Neiva, Huila</p>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="sm:col-span-2">
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Departamento *
                    </label>
                    <select
                      value={region}
                      onChange={(e) => setRegion(e.target.value)}
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all bg-white"
                    >
                      <option value="Huila (Taller)">Huila (Sede Taller)</option>
                      <option value="Bogotá D.C.">Bogotá D.C.</option>
                      <option value="Antioquia">Antioquia</option>
                      <option value="Valle del Cauca">Valle del Cauca</option>
                      <option value="Cundinamarca">Cundinamarca</option>
                      <option value="Santander">Santander</option>
                      <option value="Atlántico">Atlántico</option>
                      <option value="Bolívar">Bolívar</option>
                      <option value="Tolima">Tolima</option>
                      <option value="Risaralda">Risaralda</option>
                      <option value="Caldas">Caldas</option>
                      <option value="Otro Departamento">Otro Departamento de Colombia</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Ciudad o Municipio *
                    </label>
                    <input
                      type="text"
                      required
                      value={comuna}
                      onChange={(e) => setComuna(e.target.value)}
                      placeholder="Ej. Neiva, Bogotá, Medellín, Cali..."
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Calle y Número *
                    </label>
                    <input
                      type="text"
                      required
                      value={address}
                      onChange={(e) => setAddress(e.target.value)}
                      placeholder="Ej. Av. Manuel Montt 1420"
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>

                  <div className="sm:col-span-2">
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Depto / Casa / Bloque / Referencias adicionales
                    </label>
                    <input
                      type="text"
                      value={apartment}
                      onChange={(e) => setApartment(e.target.value)}
                      placeholder="Ej. Depto 402-B, timbre rojo"
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>

                  <div className="sm:col-span-2">
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Indicaciones para el Repartidor
                    </label>
                    <input
                      type="text"
                      value={notes}
                      onChange={(e) => setNotes(e.target.value)}
                      placeholder="Ej. Dejar en conserjería si no respondo"
                      className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                  </div>
                </div>

                {/* Shipping Carrier Selector */}
                <div className="mt-6 pt-5 border-t border-gray-100">
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                    Selecciona Modalidad de Envío
                  </label>
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button
                      type="button"
                      onClick={() => setShippingMethod('nacional')}
                      className={`text-left p-3.5 rounded-2xl border-2 transition-all cursor-pointer relative ${
                        shippingMethod === 'nacional'
                          ? 'border-[#6100a4] bg-[#fbf5ff] shadow-sm'
                          : 'border-gray-200 bg-white hover:border-gray-300'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-1">
                        <span className="font-bold text-sm text-[#1d1a20]">Envío Nacional</span>
                        <span className="text-xs font-extrabold text-[#6100a4]">$14.500 COP</span>
                      </div>
                      <p className="text-[11px] text-gray-500 leading-tight">Inter Rapidísimo / Servientrega (2-4 días)</p>
                      {shippingMethod === 'nacional' && (
                        <span className="absolute top-2 right-2 material-symbols-outlined text-[#6100a4] text-base">
                          check_circle
                        </span>
                      )}
                    </button>

                    <button
                      type="button"
                      onClick={() => setShippingMethod('neiva')}
                      className={`text-left p-3.5 rounded-2xl border-2 transition-all cursor-pointer relative ${
                        shippingMethod === 'neiva'
                          ? 'border-[#6100a4] bg-[#fbf5ff] shadow-sm'
                          : 'border-gray-200 bg-white hover:border-gray-300'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-1">
                        <span className="font-bold text-sm text-[#1d1a20]">Local Neiva</span>
                        <span className="text-xs font-extrabold text-[#6100a4]">$6.000 COP</span>
                      </div>
                      <p className="text-[11px] text-gray-500 leading-tight">Mensajería urbana express el mismo día</p>
                      {shippingMethod === 'neiva' && (
                        <span className="absolute top-2 right-2 material-symbols-outlined text-[#6100a4] text-base">
                          check_circle
                        </span>
                      )}
                    </button>

                    <button
                      type="button"
                      onClick={() => setShippingMethod('retiro')}
                      className={`text-left p-3.5 rounded-2xl border-2 transition-all cursor-pointer relative ${
                        shippingMethod === 'retiro'
                          ? 'border-[#6100a4] bg-[#fbf5ff] shadow-sm'
                          : 'border-gray-200 bg-white hover:border-gray-300'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-1">
                        <span className="font-bold text-sm text-[#1d1a20]">Retiro en Taller</span>
                        <span className="text-xs font-extrabold text-[#6100a4]">Gratis</span>
                      </div>
                      <p className="text-[11px] text-gray-500 leading-tight">Neiva, Huila (Coordinación previa)</p>
                      {shippingMethod === 'retiro' && (
                        <span className="absolute top-2 right-2 material-symbols-outlined text-[#6100a4] text-base">
                          check_circle
                        </span>
                      )}
                    </button>
                  </div>
                </div>

                {/* Gift Dedication Option */}
                <div className="mt-6 pt-5 border-t border-gray-100">
                  <label className="flex items-start gap-3 cursor-pointer select-none">
                    <input
                      type="checkbox"
                      checked={isGift}
                      onChange={(e) => setIsGift(e.target.checked)}
                      className="mt-1 w-4 h-4 rounded text-[#6100a4] focus:ring-[#6100a4] accent-[#6100a4]"
                    />
                    <div>
                      <span className="text-sm font-bold text-[#1d1a20] flex items-center gap-1.5">
                        <span className="material-symbols-outlined text-[#6100a4] text-lg">card_giftcard</span>
                        ¿Es un regalo? Incluir tarjeta caligrafiada y dedicatoria personalizada (Gratis)
                      </span>
                      <p className="text-xs text-gray-500 mt-0.5">
                        Escribiremos tu dedicatoria en papel reciclado con sello de lacre artesanal Maraña.
                      </p>
                    </div>
                  </label>

                  {isGift && (
                    <div className="mt-3.5 pl-7">
                      <div className="p-4 rounded-2xl bg-[#faf6fe] border border-[#e8dcf8] space-y-2">
                        <div className="flex items-center justify-between text-xs font-bold text-[#6100a4]">
                          <span className="flex items-center gap-1">
                            <span className="material-symbols-outlined text-sm">edit_note</span>
                            Mensaje para la tarjeta de regalo
                          </span>
                          <span className="text-gray-400 font-normal">{giftDedication.length}/200 car.</span>
                        </div>
                        <textarea
                          rows={3}
                          maxLength={200}
                          value={giftDedication}
                          onChange={(e) => setGiftDedication(e.target.value)}
                          placeholder="Escribe aquí tu dedicatoria para quien recibirá este amigurumi..."
                          className="w-full p-3 rounded-xl bg-white border border-[#deb7ff]/50 text-sm text-[#1d1a20] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#6100a4]"
                        />
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Step 3: Bank Transfer & Receipt Voucher Upload */}
              <div className="bg-white rounded-3xl p-6 sm:p-7 shadow-[0_4px_24px_rgba(97,0,164,0.06)] border border-[#ede7f6]">
                <div className="flex items-center gap-3 mb-5">
                  <div className="w-9 h-9 rounded-2xl bg-[#f1dbff] text-[#6100a4] flex items-center justify-center">
                    <span className="material-symbols-outlined text-xl">account_balance</span>
                  </div>
                  <div>
                    <h2 className="text-lg font-bold text-[#1d1a20]">3. Datos de Transferencia y Comprobante</h2>
                    <p className="text-xs text-gray-500">Transfiere a cualquiera de nuestras cuentas y sube el voucher</p>
                  </div>
                </div>

                {/* Bank Select Tabs */}
                <div className="grid grid-cols-3 gap-2 p-1.5 bg-[#f4effa] rounded-2xl mb-5">
                  <button
                    type="button"
                    onClick={() => setSelectedBank('bancolombia')}
                    className={`py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                      selectedBank === 'bancolombia'
                        ? 'bg-white text-[#6100a4] shadow-sm'
                        : 'text-gray-600 hover:text-[#1d1a20]'
                    }`}
                  >
                    Bancolombia
                  </button>
                  <button
                    type="button"
                    onClick={() => setSelectedBank('nequi')}
                    className={`py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                      selectedBank === 'nequi'
                        ? 'bg-white text-[#6100a4] shadow-sm'
                        : 'text-gray-600 hover:text-[#1d1a20]'
                    }`}
                  >
                    Nequi
                  </button>
                  <button
                    type="button"
                    onClick={() => setSelectedBank('daviplata')}
                    className={`py-2 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                      selectedBank === 'daviplata'
                        ? 'bg-white text-[#6100a4] shadow-sm'
                        : 'text-gray-600 hover:text-[#1d1a20]'
                    }`}
                  >
                    Daviplata
                  </button>
                </div>

                {/* Bank Details Display Card */}
                <div className="p-4 sm:p-5 rounded-2xl bg-[#fbf8fe] border border-[#ede7f6] mb-6">
                  <div className="flex items-center justify-between pb-3 border-b border-[#ede7f6] mb-3">
                    <div className="flex items-center gap-2">
                      <span className="material-symbols-outlined text-[#6100a4] text-lg">assured_workload</span>
                      <span className="font-extrabold text-sm text-[#1d1a20]">
                        {selectedBank === 'bancolombia' && 'Bancolombia (Cuenta Ahorros)'}
                        {selectedBank === 'nequi' && 'Nequi Colombia'}
                        {selectedBank === 'daviplata' && 'Daviplata Davivienda'}
                      </span>
                    </div>
                    <span className="text-xs px-2.5 py-0.5 rounded-full bg-[#f1dbff] text-[#6100a4] font-bold">
                      {selectedBank === 'bancolombia' ? 'Ahorros' : 'Billetera Digital'}
                    </span>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                      <span className="text-gray-500 block">Titular:</span>
                      <strong className="text-gray-800 text-sm">Maraña Crochet Artesanal</strong>
                    </div>

                    <div>
                      <span className="text-gray-500 block">NIT / CC:</span>
                      <div className="flex items-center justify-between">
                        <strong className="text-gray-800 text-sm">901.542.883-1</strong>
                        <button
                          type="button"
                          onClick={() => copyToClipboard('901.542.883-1', 'rut')}
                          className="text-[#6100a4] hover:underline font-bold text-[11px] cursor-pointer"
                        >
                          {copiedField === 'rut' ? '¡Copiado!' : 'Copiar'}
                        </button>
                      </div>
                    </div>

                    <div>
                      <span className="text-gray-500 block">N° de Cuenta / Celular:</span>
                      <div className="flex items-center justify-between">
                        <strong className="text-gray-800 text-sm">
                          {selectedBank === 'bancolombia' && '824-000192-41'}
                          {selectedBank === 'nequi' && '312 456 7890'}
                          {selectedBank === 'daviplata' && '312 456 7890'}
                        </strong>
                        <button
                          type="button"
                          onClick={() =>
                            copyToClipboard(
                              selectedBank === 'bancolombia'
                                ? '824-000192-41'
                                : '3124567890',
                              'account'
                            )
                          }
                          className="text-[#6100a4] hover:underline font-bold text-[11px] cursor-pointer"
                        >
                          {copiedField === 'account' ? '¡Copiado!' : 'Copiar'}
                        </button>
                      </div>
                    </div>

                    <div>
                      <span className="text-gray-500 block">Email de comprobante:</span>
                      <div className="flex items-center justify-between">
                        <strong className="text-gray-800 text-sm">pagos@maranacrochet.cl</strong>
                        <button
                          type="button"
                          onClick={() => copyToClipboard('pagos@maranacrochet.cl', 'email')}
                          className="text-[#6100a4] hover:underline font-bold text-[11px] cursor-pointer"
                        >
                          {copiedField === 'email' ? '¡Copiado!' : 'Copiar'}
                        </button>
                      </div>
                    </div>
                  </div>

                  <div className="mt-4 pt-3 border-t border-[#ede7f6] flex items-center justify-between text-xs bg-white p-3 rounded-xl">
                    <span className="text-gray-600 font-medium">Monto exacto a transferir:</span>
                    <span className="text-lg font-extrabold text-[#6100a4]">${total.toLocaleString('es-CO')} COP</span>
                  </div>
                </div>

                {/* Voucher Upload Area */}
                <div className="space-y-4">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                      Sube tu Comprobante de Transferencia (Voucher) *
                    </label>
                    <div className="border-2 border-dashed border-[#deb7ff] rounded-2xl p-5 text-center bg-[#fbf9fe] hover:bg-[#f6eeff] transition-all relative">
                      <input
                        type="file"
                        accept="image/*,application/pdf"
                        onChange={handleFileUpload}
                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                      />
                      <div className="flex flex-col items-center">
                        <div className="w-12 h-12 rounded-full bg-[#f1dbff] text-[#6100a4] flex items-center justify-center mb-2 shadow-sm">
                          <span className="material-symbols-outlined text-2xl">cloud_upload</span>
                        </div>
                        <p className="text-sm font-bold text-[#1d1a20]">
                          Arrastra tu captura o haz clic para subir voucher
                        </p>
                        <p className="text-xs text-gray-500 mt-0.5">
                          Soporta JPG, PNG, PDF de transferencia Bancolombia, Nequi, Daviplata, etc.
                        </p>
                      </div>
                    </div>
                  </div>

                  {/* Preview of Voucher if selected */}
                  {receiptImage && (
                    <div className="p-3.5 rounded-2xl bg-[#f0fdf4] border border-[#bbf7d0] flex items-center gap-3">
                      <img
                        src={receiptImage}
                        alt="Comprobante cargado"
                        className="w-14 h-14 object-cover rounded-xl border border-emerald-200"
                      />
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-1.5 text-xs font-bold text-emerald-800">
                          <span className="material-symbols-outlined text-sm">verified</span>
                          Comprobante cargado exitosamente
                        </div>
                        <p className="text-[11px] text-emerald-600 truncate mt-0.5">
                          transferencia_bancaria_marana_{tefCode || 'voucher'}.png
                        </p>
                      </div>
                      <button
                        type="button"
                        onClick={() => setReceiptImage(null)}
                        className="text-gray-400 hover:text-red-500 transition-colors p-1"
                        title="Eliminar voucher"
                      >
                        <span className="material-symbols-outlined text-lg">close</span>
                      </button>
                    </div>
                  )}

                  {/* Optional TEF code input */}
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                      Número de Operación / Código TEF (Opcional)
                    </label>
                    <input
                      type="text"
                      value={tefCode}
                      onChange={(e) => setTefCode(e.target.value)}
                      placeholder="Ej. TRF-884920"
                      className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#6100a4] focus:ring-2 focus:ring-[#f1dbff] outline-none text-sm transition-all"
                    />
                    <p className="text-[11px] text-gray-500 mt-1">
                      Aparece en tu comprobante bancario. Ayuda a validar tu pedido en menos de 5 minutos.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            {/* RIGHT COLUMN: Order Summary, Coupon, Big CTA */}
            <div className="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
              <div className="bg-white rounded-3xl p-6 sm:p-7 shadow-[0_8px_30px_rgba(97,0,164,0.08)] border border-[#ede7f6]">
                <div className="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                  <h2 className="text-lg font-extrabold text-[#1d1a20] flex items-center gap-2">
                    <span className="material-symbols-outlined text-[#6100a4]">shopping_bag</span>
                    Resumen del Pedido
                  </h2>
                  <span className="text-xs font-bold px-2.5 py-1 rounded-full bg-[#f1dbff] text-[#6100a4]">
                    {cartItems.reduce((acc, i) => acc + i.quantity, 0)} artículos
                  </span>
                </div>

                {/* Item List */}
                <div className="space-y-3.5 max-h-72 overflow-y-auto pr-1">
                  {cartItems.map((item) => (
                    <div key={item.product.id} className="flex items-center gap-3.5">
                      <div className="relative w-16 h-16 rounded-2xl overflow-hidden bg-gray-50 flex-shrink-0 border border-gray-100">
                        <img
                          src={item.product.images[0].url}
                          alt={item.product.name}
                          className="w-full h-full object-cover"
                        />
                        <span className="absolute bottom-1 right-1 w-5 h-5 rounded-full bg-[#6100a4] text-white text-[11px] font-bold flex items-center justify-center">
                          {item.quantity}
                        </span>
                      </div>
                      <div className="flex-1 min-w-0">
                        <h4 className="text-xs font-bold text-[#1d1a20] truncate">{item.product.name}</h4>
                        <p className="text-[11px] text-gray-500">{item.product.tagline}</p>
                        <span className="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-semibold">
                          Hecho a mano
                        </span>
                      </div>
                      <div className="text-right">
                        <div className="text-xs font-bold text-[#1d1a20]">
                          ${(item.product.price * item.quantity).toLocaleString('es-CO')}
                        </div>
                        {item.quantity > 1 && (
                          <div className="text-[10px] text-gray-400">
                            ${item.product.price.toLocaleString('es-CO')} c/u
                          </div>
                        )}
                      </div>
                    </div>
                  ))}
                </div>

                {/* Coupon Box */}
                <div className="mt-6 pt-5 border-t border-gray-100">
                  <div className="flex gap-2">
                    <input
                      type="text"
                      value={couponCode}
                      onChange={(e) => setCouponCode(e.target.value)}
                      placeholder="Cupón (ej. MARANA10)"
                      className="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 uppercase tracking-wider text-xs font-semibold focus:border-[#6100a4] focus:ring-1 focus:ring-[#6100a4] outline-none"
                    />
                    <button
                      type="button"
                      onClick={handleApplyCoupon}
                      className="px-4 py-2.5 rounded-xl bg-[#6100a4] hover:bg-[#4f0087] text-white text-xs font-bold transition-all cursor-pointer shadow-sm"
                    >
                      Aplicar
                    </button>
                  </div>
                  {couponError && <p className="text-[11px] text-rose-500 mt-1">{couponError}</p>}
                  {discountPercent > 0 && (
                    <p className="text-[11px] text-emerald-600 font-bold mt-1.5 flex items-center gap-1">
                      <span className="material-symbols-outlined text-sm">loyalty</span>
                      ¡Cupón {couponCode.toUpperCase()} aplicado! ({discountPercent}% de descuento)
                    </p>
                  )}
                </div>

                {/* Totals Breakdown */}
                <div className="mt-5 space-y-2.5 text-xs pt-4 border-t border-gray-100">
                  <div className="flex justify-between text-gray-600">
                    <span>Subtotal de productos</span>
                    <span className="font-semibold text-gray-900">${subtotal.toLocaleString('es-CO')} COP</span>
                  </div>

                  {discountPercent > 0 && (
                    <div className="flex justify-between text-emerald-700 font-semibold">
                      <span>Descuento ({discountPercent}%)</span>
                      <span>-${discountAmount.toLocaleString('es-CO')} COP</span>
                    </div>
                  )}

                  <div className="flex justify-between text-gray-600">
                    <div className="flex items-center gap-1">
                      <span>Envío ({shippingMethod})</span>
                      <span className="material-symbols-outlined text-xs text-gray-400">help_outline</span>
                    </div>
                    <span className="font-semibold text-gray-900">${shippingCost.toLocaleString('es-CO')} COP</span>
                  </div>

                  <div className="pt-3 border-t border-gray-100 flex justify-between items-baseline">
                    <div>
                      <div className="text-base font-extrabold text-[#1d1a20]">Total a Pagar</div>
                      <div className="text-[11px] text-gray-500">Impuestos y empaque de regalo incluidos</div>
                    </div>
                    <div className="text-2xl font-black text-[#6100a4]">
                      ${total.toLocaleString('es-CO')} <span className="text-xs font-bold text-gray-500">COP</span>
                    </div>
                  </div>
                </div>

                {/* Submit CTA */}
                <button
                  type="submit"
                  disabled={isSubmitting || cartItems.length === 0}
                  className="mt-6 w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#6100a4] to-[#7b2cbf] hover:from-[#4f0087] hover:to-[#6100a4] text-white font-extrabold text-sm shadow-[0_10px_25px_rgba(97,0,164,0.35)] hover:shadow-[0_14px_30px_rgba(97,0,164,0.45)] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed group"
                >
                  {isSubmitting ? (
                    <>
                      <span className="material-symbols-outlined animate-spin text-xl">progress_activity</span>
                      Verificando y Apartando Stock...
                    </>
                  ) : (
                    <>
                      <span className="material-symbols-outlined text-xl transition-transform group-hover:scale-110">
                        verified
                      </span>
                      Confirmar Pedido y Enviar Comprobante
                    </>
                  )}
                </button>

                {/* Safety Guarantee */}
                <div className="mt-4 p-3.5 rounded-2xl bg-[#fbf8fe] border border-[#ede7f6] space-y-2 text-[11px] text-gray-600">
                  <div className="flex items-center gap-2 font-bold text-[#6100a4]">
                    <span className="material-symbols-outlined text-base">security</span>
                    Garantía Taller Maraña Crochet
                  </div>
                  <p>
                    • Tu producto es reservado por 30 minutos mientras el taller verifica la transferencia.
                  </p>
                  <p>
                    • Te enviaremos código de seguimiento de Inter Rapidísimo o Servientrega directo a tu WhatsApp y correo.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </main>
  );
};

