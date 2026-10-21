import { Order, Product, Review } from '../types';
import { PRODUCTS as FALLBACK_PRODUCTS } from '../data/products';
import { INITIAL_ORDERS as FALLBACK_ORDERS } from '../data/orders';
import { INITIAL_REVIEWS as FALLBACK_REVIEWS } from '../data/reviews';

const API_BASE = (() => {
  if (typeof window !== 'undefined') {
    if (window.location.pathname.startsWith('/marana')) {
      return '/marana/api';
    }
  }
  return 'http://localhost/marana/api';
})();

export const maranaApi = {
  // --- PRODUCTOS ---
  async getProducts(): Promise<Product[]> {
    try {
      const res = await fetch(`${API_BASE}/products.php`);
      if (!res.ok) throw new Error(`HTTP error ${res.status}`);
      const data = await res.json();
      if (data.success && Array.isArray(data.products) && data.products.length > 0) {
        return data.products;
      }
      return FALLBACK_PRODUCTS;
    } catch (err) {
      console.warn('No se pudo conectar con la API de productos en XAMPP, usando datos locales:', err);
      return FALLBACK_PRODUCTS;
    }
  },

  async updateProductStock(id: string, stock: number): Promise<Product | null> {
    try {
      const res = await fetch(`${API_BASE}/products.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, stock }),
      });
      const data = await res.json();
      if (data.success && data.product) {
        return data.product;
      }
      return null;
    } catch (err) {
      console.error('Error al actualizar stock en XAMPP MySQL:', err);
      return null;
    }
  },

  async updateProductPrice(id: string, price: number): Promise<Product | null> {
    try {
      const res = await fetch(`${API_BASE}/products.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, price }),
      });
      const data = await res.json();
      if (data.success && data.product) {
        return data.product;
      }
      return null;
    } catch (err) {
      console.error('Error al actualizar precio en XAMPP MySQL:', err);
      return null;
    }
  },

  // --- PEDIDOS (ORDERS) ---
  async getOrders(): Promise<Order[]> {
    try {
      const res = await fetch(`${API_BASE}/orders.php`);
      if (!res.ok) throw new Error(`HTTP error ${res.status}`);
      const data = await res.json();
      if (data.success && Array.isArray(data.orders)) {
        return data.orders;
      }
      return FALLBACK_ORDERS;
    } catch (err) {
      console.warn('No se pudo conectar con la API de pedidos en XAMPP, usando datos locales:', err);
      return FALLBACK_ORDERS;
    }
  },

  async createOrder(order: Order): Promise<boolean> {
    try {
      const res = await fetch(`${API_BASE}/orders.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(order),
      });
      const data = await res.json();
      return !!data.success;
    } catch (err) {
      console.error('Error guardando pedido en XAMPP MySQL:', err);
      return false;
    }
  },

  async updateOrderStatus(orderId: string, status: Order['status'], note?: string): Promise<Order | null> {
    try {
      const res = await fetch(`${API_BASE}/orders.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: orderId, status, note }),
      });
      const data = await res.json();
      if (data.success && data.order) {
        return data.order;
      }
      return null;
    } catch (err) {
      console.error('Error actualizando pedido en XAMPP MySQL:', err);
      return null;
    }
  },

  // --- RESEÑAS (REVIEWS) ---
  async getReviews(): Promise<Review[]> {
    try {
      const res = await fetch(`${API_BASE}/reviews.php`);
      if (!res.ok) throw new Error(`HTTP error ${res.status}`);
      const data = await res.json();
      if (data.success && Array.isArray(data.reviews)) {
        return data.reviews;
      }
      return FALLBACK_REVIEWS;
    } catch (err) {
      console.warn('No se pudo conectar con la API de reseñas en XAMPP, usando datos locales:', err);
      return FALLBACK_REVIEWS;
    }
  },

  async updateReviewStatus(reviewId: string, status: Review['status']): Promise<boolean> {
    try {
      const res = await fetch(`${API_BASE}/reviews.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: reviewId, status }),
      });
      const data = await res.json();
      return !!data.success;
    } catch (err) {
      console.error('Error actualizando reseña en XAMPP MySQL:', err);
      return false;
    }
  },

  async addReviewReply(
    reviewId: string,
    reply: { author: string; role: string; date: string; text: string }
  ): Promise<boolean> {
    try {
      const res = await fetch(`${API_BASE}/reviews.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: reviewId, workshopReply: reply }),
      });
      const data = await res.json();
      return !!data.success;
    } catch (err) {
      console.error('Error guardando respuesta a reseña en XAMPP MySQL:', err);
      return false;
    }
  },
};

