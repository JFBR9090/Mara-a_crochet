export interface Product {
  id: string;
  name: string;
  sku: string;
  category: 'espacio' | 'bosque' | 'bebes' | 'nuevos' | 'kits';
  categoryLabel: string;
  price: number; // in COP
  originalPrice?: number;
  discountBadge?: string;
  usdApprox: number;
  rating: number;
  reviewCount: number;
  stock: number;
  maxStock: number;
  reserved: number;
  status: 'in-stock' | 'low-stock' | 'preorder' | 'soldout';
  statusLabel: string;
  tagline: string;
  description: string;
  images: {
    url: string;
    alt: string;
  }[];
  craftHours: number;
  dimensions: string;
  weight: string;
  material: string;
  filling: string;
  safety: string;
  includes: string;
  preorderDays?: number;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface Order {
  id: string;
  date: string;
  customerName: string;
  customerEmail: string;
  customerPhone: string;
  customerRut: string;
  address: {
    region: string;
    comuna: string;
    street: string;
    dept?: string;
    instructions?: string;
  };
  shippingMethod: 'express-rm' | 'regiones' | 'retiro';
  shippingCost: number;
  items: {
    productId: string;
    productName: string;
    colorDetails: string;
    quantity: number;
    price: number;
    image: string;
  }[];
  subtotal: number;
  total: number;
  status: 'revision' | 'pendiente' | 'taller' | 'despachado' | 'entregado' | 'rechazado';
  bank: 'Bancolombia' | 'Nequi' | 'Daviplata' | 'Davivienda' | 'Banco de Bogotá' | 'Otro';
  accountType: string;
  transferDetails: {
    folio: string;
    tefCode: string;
    transferredAmount: number;
    transferDate: string;
    channel: string;
    subject: string;
    voucherFilename: string;
    voucherUrl?: string;
  };
  giftDedication?: string;
  auditLog: {
    time: string;
    note: string;
    type: 'success' | 'info' | 'warning';
  }[];
}

export interface Review {
  id: string;
  customerInitials: string;
  customerName: string;
  emailMasked: string;
  orderId: string;
  sku: string;
  productName: string;
  productIcon: string;
  rating: number;
  date: string;
  comment: string;
  verified: boolean;
  status: 'pending' | 'approved' | 'featured' | 'rejected';
  photos?: {
    url: string;
    alt: string;
    caption: string;
  }[];
  workshopReply?: {
    author: string;
    role: string;
    date: string;
    text: string;
  };
  rejectionReason?: string;
  helpfulCount?: number;
}

