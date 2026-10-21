import { Order } from '../types';

export const INITIAL_ORDERS: Order[] = [
  {
    id: '#MC-2024-884',
    date: 'Hoy 14:15 hrs',
    customerName: 'Valentina Castro Pizarro',
    customerEmail: 'valentina.castro@gmail.com',
    customerPhone: '+57 312 456 7890',
    customerRut: '1.075.289.412',
    address: {
      region: 'Bogotá D.C.',
      comuna: 'Bogotá (Chapinero)',
      street: 'Carrera 15 # 85-30',
      dept: 'Apto 402, Torre 1',
      instructions: 'Dejar con vigilancia 24 horas si no respondo'
    },
    shippingMethod: 'nacional-colombia',
    shippingCost: 14500,
    items: [
      {
        productId: 'lulu-aranita',
        productName: 'Lulú la Arañita Cósmica',
        colorDetails: 'Púrpura Galáctico | Ojos: Estelares',
        quantity: 1,
        price: 24990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCCBQTkO4MJPbLrA7fXU-AQavWCLMp5Azeuh40WLRIA9GkxVW47IRRdI5Z_iiD7IeKOIYN6PDZdE96B54flHKEV3hsYvP5KsEHT20DhcyN5GND2kajRzkyjbSAkQHtJofHuMJbZ9l1DkDEIohEiSRaJMO-ARJxyPcWOllrxQo3F8B8XZEb8gzTQLH19H0BYRwvi0uYuMVLtdH8gUGjmaG3zgH5hvKufvjsXXPhcDbd7Be_ZPhsAFxWV'
      },
      {
        productId: 'murcielago-lavanda',
        productName: 'Murciélago Lavanda Nocturno',
        colorDetails: 'Lavanda Pastel | Alas articuladas',
        quantity: 1,
        price: 21990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDUIXWZT-1f2ubjr6jhjcDrKsmjVFS-bf_BXtNrU2SguPxQeob1zv9DtZGXFzGd040456NhXcahfKfsrQ6hIMjpYKrBsjAYQVs4j4fkXMvEMnmRem6H83g_wamup6BnRmnm_r3gPiT60wD5yWhYGnuYI1RI88IXciolh8Xq5BAavKvlGdFC-r8rhsQNQ-RUhEsfeawfllpL4SQfRigQ7PJt49WlojmNMgjojPcMmqys8vstB06F0P-q'
      }
    ],
    subtotal: 46980,
    total: 61480,
    status: 'revision',
    bank: 'Bancolombia',
    accountType: 'Cuenta de Ahorros Bancolombia',
    transferDetails: {
      folio: '94827103',
      tefCode: 'TRF-94827103',
      transferredAmount: 61480,
      transferDate: 'Hoy, 14:28:14 hrs',
      channel: 'App Bancolombia / Nequi',
      subject: '#MC-2024-884',
      voucherFilename: 'comprobante_bancolombia_61480.pdf'
    },
    giftDedication: 'Para Sofía con todo el amor cósmico en su cumpleaños... ¡Cuida mucho a Lulú! ✨',
    auditLog: [
      { time: '14:15', note: 'Pedido creado en línea desde carrito web. 2 unidades de stock bloqueadas temporalmente.', type: 'info' },
      { time: '14:28', note: 'Cliente subió comprobante de transferencia Bancolombia (comprobante_bancolombia_61480.pdf).', type: 'info' },
      { time: '14:40', note: 'Abierto en inspector por Administradora Sofía Morales. Verificación de transferencia coincidente al 100%.', type: 'success' }
    ]
  },
  {
    id: '#MC-2024-883',
    date: 'Hoy 13:45 hrs',
    customerName: 'Matías Rojas',
    customerEmail: 'matias.rojas.g@outlook.com',
    customerPhone: '+57 315 883 4112',
    customerRut: '1.094.301.992',
    address: {
      region: 'Antioquia',
      comuna: 'Medellín (El Poblado)',
      street: 'Calle 10 # 43E-20',
      instructions: 'Casa con reja negra'
    },
    shippingMethod: 'nacional-colombia',
    shippingCost: 14500,
    items: [
      {
        productId: 'polilla-lunar',
        productName: 'Polilla Lunar de Algodón',
        colorDetails: 'Edición Estelar Lunar',
        quantity: 1,
        price: 27990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCZuedVPxDSDI0i2EBpDWcrNpBistZV7eYvKRV_vMDk-s-MaV_bOJLmETdNnS6QswGvzxxh4M3aqVTI3InQJ3d40w8HawPgcLzh5nZ8nhkOekqy9MI34Nppn9CxMbkagm_APNhMMxqd8f4Z9kAnZsn1qkikadjslMd1gBB7p53FeQG6UWtdk2t3et4LES5XOFnvHNkbHcLHEcI2KFtqTemxzZteFK1c1sJ21_2hVpplit7yyWJD-2M7'
      }
    ],
    subtotal: 27990,
    total: 42490,
    status: 'revision',
    bank: 'Nequi',
    accountType: 'Billetera Digital Nequi',
    transferDetails: {
      folio: '78291033',
      tefCode: 'TRF-78291033',
      transferredAmount: 42490,
      transferDate: 'Hoy, 13:50:00 hrs',
      channel: 'App Nequi Colombia',
      subject: '#MC-2024-883',
      voucherFilename: 'voucher_nequi_42490.pdf'
    },
    giftDedication: 'Para mi novia hermosa en su aniversario. Hecho con amor artesanal colombiano.',
    auditLog: [
      { time: '13:45', note: 'Pedido creado en tienda.', type: 'info' },
      { time: '13:52', note: 'Comprobante Nequi recibido.', type: 'info' }
    ]
  },
  {
    id: '#MC-2024-882',
    date: 'Hoy 13:00 hrs',
    customerName: 'Camila Soto',
    customerEmail: 'cami.soto@gmail.com',
    customerPhone: '+57 320 772 2991',
    customerRut: '1.020.123.884',
    address: {
      region: 'Valle del Cauca',
      comuna: 'Cali (Granada)',
      street: 'Avenida 9N # 14-22',
      dept: 'Apto 301'
    },
    shippingMethod: 'nacional-colombia',
    shippingCost: 14500,
    items: [
      {
        productId: 'murcielago-lavanda',
        productName: 'Murciélago Lavanda Nocturno',
        colorDetails: 'Lavanda Pastel',
        quantity: 1,
        price: 24990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDUIXWZT-1f2ubjr6jhjcDrKsmjVFS-bf_BXtNrU2SguPxQeob1zv9DtZGXFzGd040456NhXcahfKfsrQ6hIMjpYKrBsjAYQVs4j4fkXMvEMnmRem6H83g_wamup6BnRmnm_r3gPiT60wD5yWhYGnuYI1RI88IXciolh8Xq5BAavKvlGdFC-r8rhsQNQ-RUhEsfeawfllpL4SQfRigQ7PJt49WlojmNMgjojPcMmqys8vstB06F0P-q'
      }
    ],
    subtotal: 24990,
    total: 39490,
    status: 'pendiente',
    bank: 'Daviplata',
    accountType: 'Daviplata',
    transferDetails: {
      folio: '',
      tefCode: '',
      transferredAmount: 0,
      transferDate: '',
      channel: '',
      subject: '#MC-2024-882',
      voucherFilename: ''
    },
    auditLog: [
      { time: '13:00', note: 'Orden creada. Reserva vence en 2 horas si no se adjunta comprobante.', type: 'warning' }
    ]
  },
  {
    id: '#MC-2024-880',
    date: 'Hoy 11:30 hrs',
    customerName: 'Ignacio Vega',
    customerEmail: 'ignacio.vega@gmail.com',
    customerPhone: '+57 311 661 1223',
    customerRut: '1.016.940.221',
    address: {
      region: 'Huila',
      comuna: 'Neiva (Centro)',
      street: 'Carrera 7 # 14-35',
      dept: 'Casa'
    },
    shippingMethod: 'envio-neiva',
    shippingCost: 6000,
    items: [
      {
        productId: 'gatito-cosmico',
        productName: 'Gatito Cósmico de Felpa',
        colorDetails: 'Violeta y Crema',
        quantity: 2,
        price: 22990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDZ5b3WdVNoATVZeAebIdRHy83ogkaWs3hXxhT1UXHqo95kyz2unTJm-_-dZvE2WYcDH9-I0qkUQr8fO2c5bypAm77vXy1jYmRRNQWBkCvoukzr0_OFLNUD70RPk9BT1e0jnUdX7iDyPWslNgs763o_MxKqoa0FZ6uUJMnlCTF5qAKd9yqsstbZ6ZVBPPZfBjQvrhEtDjGPk_gsOlSZKWvO9GL5c8H-Z4HMZlzOPQ6_Figr4Co7PSf5'
      }
    ],
    subtotal: 45980,
    total: 51980,
    status: 'taller',
    bank: 'Bancolombia',
    accountType: 'Cuenta de Ahorros Bancolombia',
    transferDetails: {
      folio: '94821102',
      tefCode: 'TRF-94821102',
      transferredAmount: 51980,
      transferDate: 'Hoy, 11:42:00 hrs',
      channel: 'App Bancolombia',
      subject: '#MC-2024-880',
      voucherFilename: 'bancolombia_vega_51980.pdf'
    },
    auditLog: [
      { time: '11:30', note: 'Pedido ingresado.', type: 'info' },
      { time: '11:42', note: 'Comprobante recibido.', type: 'info' },
      { time: '12:05', note: 'Aprobado y enviado a taller por Sofía Morales en Neiva.', type: 'success' }
    ]
  }
];

