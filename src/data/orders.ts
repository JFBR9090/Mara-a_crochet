import { Order } from '../types';

export const INITIAL_ORDERS: Order[] = [
  {
    id: '#MC-2024-884',
    date: 'Hoy 14:15 hrs',
    customerName: 'Valentina Castro Pizarro',
    customerEmail: 'v.castro@correo.cl',
    customerPhone: '+56 9 9123 4567',
    customerRut: '19.482.012-3',
    address: {
      region: 'Región Metropolitana de Santiago',
      comuna: 'Providencia',
      street: 'Av. Providencia 1240',
      dept: 'Dpto 802, Torre B',
      instructions: 'Dejar con conserjería las 24 horas si no respondo'
    },
    shippingMethod: 'express-rm',
    shippingCost: 3990,
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
    total: 50970,
    status: 'revision',
    bank: 'Santander',
    accountType: 'Santander Cuenta Vista',
    transferDetails: {
      folio: '94827103',
      tefCode: 'TEF-94827103',
      transferredAmount: 50970,
      transferDate: 'Hoy, 14:28:14 hrs',
      channel: 'App Santander Móvil',
      subject: '#MC-2024-884',
      voucherFilename: 'comprobante_banco_santander_50970.pdf'
    },
    giftDedication: 'Para Sofía con todo el amor cósmico en su cumpleaños... ¡Cuida mucho a Lulú! ✨',
    auditLog: [
      { time: '14:15', note: 'Pedido creado en línea desde carrito web. 2 unidades de stock bloqueadas temporalmente.', type: 'info' },
      { time: '14:28', note: 'Cliente subió comprobante bancario Santander (comprobante_banco_santander_50970.pdf).', type: 'info' },
      { time: '14:40', note: 'Abierto en inspector por Administradora Sofía Morales. Verificación de TEF coincidente al 100%.', type: 'success' }
    ]
  },
  {
    id: '#MC-2024-883',
    date: 'Hoy 13:45 hrs',
    customerName: 'Matías Rojas',
    customerEmail: 'matias.rojas.g@outlook.com',
    customerPhone: '+56 9 8834 1122',
    customerRut: '17.301.992-8',
    address: {
      region: 'Biobío',
      comuna: 'Concepción',
      street: 'Los Aromos 450',
      instructions: 'Casa portón café'
    },
    shippingMethod: 'regiones',
    shippingCost: 5500,
    items: [
      {
        productId: 'polilla-lunar',
        productName: 'Polilla Lunar de Algodón',
        colorDetails: 'Edición Estelar Lunar',
        quantity: 1,
        price: 21000,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCZuedVPxDSDI0i2EBpDWcrNpBistZV7eYvKRV_vMDk-s-MaV_bOJLmETdNnS6QswGvzxxh4M3aqVTI3InQJ3d40w8HawPgcLzh5nZ8nhkOekqy9MI34Nppn9CxMbkagm_APNhMMxqd8f4Z9kAnZsn1qkikadjslMd1gBB7p53FeQG6UWtdk2t3et4LES5XOFnvHNkbHcLHEcI2KFtqTemxzZteFK1c1sJ21_2hVpplit7yyWJD-2M7'
      }
    ],
    subtotal: 21000,
    total: 26500,
    status: 'revision',
    bank: 'BancoEstado',
    accountType: 'Cuenta RUT',
    transferDetails: {
      folio: '78291033',
      tefCode: 'TEF-78291033',
      transferredAmount: 26500,
      transferDate: 'Hoy, 13:50:00 hrs',
      channel: 'App BancoEstado',
      subject: '#MC-2024-883',
      voucherFilename: 'voucher_bancoestado_26500.pdf'
    },
    giftDedication: 'Para mi novia hermosa en su aniversario. Hecho con amor.',
    auditLog: [
      { time: '13:45', note: 'Pedido creado en tienda.', type: 'info' },
      { time: '13:52', note: 'Voucher BancoEstado recibido.', type: 'info' }
    ]
  },
  {
    id: '#MC-2024-882',
    date: 'Hoy 13:00 hrs',
    customerName: 'Camila Soto',
    customerEmail: 'cami.soto@gmail.com',
    customerPhone: '+56 9 7722 9911',
    customerRut: '20.123.884-1',
    address: {
      region: 'Valparaíso',
      comuna: 'Viña del Mar',
      street: '1 Norte 320',
      dept: 'Oficina 402'
    },
    shippingMethod: 'regiones',
    shippingCost: 5500,
    items: [
      {
        productId: 'murcielago-lavanda',
        productName: 'Murciélago Lavanda Nocturno',
        colorDetails: 'Lavanda Pastel',
        quantity: 1,
        price: 21990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDUIXWZT-1f2ubjr6jhjcDrKsmjVFS-bf_BXtNrU2SguPxQeob1zv9DtZGXFzGd040456NhXcahfKfsrQ6hIMjpYKrBsjAYQVs4j4fkXMvEMnmRem6H83g_wamup6BnRmnm_r3gPiT60wD5yWhYGnuYI1RI88IXciolh8Xq5BAavKvlGdFC-r8rhsQNQ-RUhEsfeawfllpL4SQfRigQ7PJt49WlojmNMgjojPcMmqys8vstB06F0P-q'
      }
    ],
    subtotal: 21990,
    total: 27490,
    status: 'pendiente',
    bank: 'Santander',
    accountType: 'Transferencia por pagar',
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
      { time: '13:00', note: 'Orden creada. Reserva vence a las 15:00 hrs si no se adjunta comprobante.', type: 'warning' }
    ]
  },
  {
    id: '#MC-2024-880',
    date: 'Hoy 11:30 hrs',
    customerName: 'Ignacio Vega',
    customerEmail: 'ignacio.vega@live.cl',
    customerPhone: '+56 9 6611 2233',
    customerRut: '16.940.221-5',
    address: {
      region: 'Región Metropolitana de Santiago',
      comuna: 'Ñuñoa',
      street: 'Simón Bolívar 2800'
    },
    shippingMethod: 'express-rm',
    shippingCost: 3990,
    items: [
      {
        productId: 'gatito-cosmico',
        productName: 'Dúo Gatitos Místicos',
        colorDetails: 'Violeta y Crema',
        quantity: 2,
        price: 42990,
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDZ5b3WdVNoATVZeAebIdRHy83ogkaWs3hXxhT1UXHqo95kyz2unTJm-_-dZvE2WYcDH9-I0qkUQr8fO2c5bypAm77vXy1jYmRRNQWBkCvoukzr0_OFLNUD70RPk9BT1e0jnUdX7iDyPWslNgs763o_MxKqoa0FZ6uUJMnlCTF5qAKd9yqsstbZ6ZVBPPZfBjQvrhEtDjGPk_gsOlSZKWvO9GL5c8H-Z4HMZlzOPQ6_Figr4Co7PSf5'
      }
    ],
    subtotal: 42990,
    total: 46980,
    status: 'taller',
    bank: 'Santander',
    accountType: 'Santander Vista',
    transferDetails: {
      folio: '94821102',
      tefCode: 'TEF-94821102',
      transferredAmount: 46980,
      transferDate: 'Hoy, 11:42:00 hrs',
      channel: 'Santander Móvil',
      subject: '#MC-2024-880',
      voucherFilename: 'santander_vega_46980.pdf'
    },
    auditLog: [
      { time: '11:30', note: 'Pedido ingresado.', type: 'info' },
      { time: '11:42', note: 'Comprobante recibido.', type: 'info' },
      { time: '12:05', note: 'Aprobado y enviado a taller por Sofía Morales.', type: 'success' }
    ]
  }
];
