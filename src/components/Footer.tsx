import React from 'react';
import { MARANA_LOGO } from '../data/products';

interface FooterProps {
  onNavigate?: (view: string) => void;
}

export const Footer: React.FC<FooterProps> = ({ onNavigate }) => {
  return (
    <footer className="w-full bg-surface-container-low mt-20 pt-16 pb-12 border-t border-outline-variant/20">
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
          {/* Brand info */}
          <div className="space-y-4">
            <div className="flex items-center gap-3">
              <img
                alt="Maraña Crochet"
                className="w-8 h-8 rounded-full object-cover ring-2 ring-primary-container/20"
                src={MARANA_LOGO}
              />
              <span className="font-headline-sm text-headline-sm text-primary font-bold">
                Maraña Crochet
              </span>
            </div>
            <p className="font-body-sm text-sm text-on-surface-variant leading-relaxed">
              Creando seres mágicos tejidos a mano en nuestro taller artesanal en Neiva, Huila, Colombia con hilos de algodón hipoalergénico y detalles cósmicos. Hecho punto por punto con paciencia y amor.
            </p>
            <div className="flex items-center gap-2 pt-2">
              <span className="material-symbols-outlined text-primary">location_on</span>
              <span className="font-label-md text-sm text-primary font-bold">
                Neiva, Huila · Colombia
              </span>
            </div>
            <div className="flex items-center gap-2 pt-1">
              <span className="material-symbols-outlined text-status-stock-success">verified</span>
              <span className="font-label-md text-sm text-on-surface font-semibold">
                Garantía 100% Hecho a Mano
              </span>
            </div>
          </div>

          {/* Explore column */}
          <div className="space-y-3">
            <h3 className="font-label-lg text-sm text-on-surface uppercase tracking-wider font-bold">
              Explorar
            </h3>
            <ul className="space-y-2 font-body-sm text-sm text-on-surface-variant">
              <li className="hover:text-primary transition-colors cursor-pointer">
                <a onClick={() => onNavigate?.('home')} href="#catalogo">Colección Bosque Encantado</a>
              </li>
              <li className="hover:text-primary transition-colors cursor-pointer">
                <a onClick={() => onNavigate?.('home')} href="#catalogo">Criaturas del Espacio &amp; Estrellas</a>
              </li>
              <li className="hover:text-primary transition-colors cursor-pointer">
                <a onClick={() => onNavigate?.('home')} href="#catalogo">Peluches de Apego para Bebés</a>
              </li>
              <li className="hover:text-primary transition-colors cursor-pointer">
                <a onClick={() => onNavigate?.('product-lulu')}>Lulú la Arañita Cósmica</a>
              </li>
              <li className="hover:text-primary transition-colors cursor-pointer">
                <a onClick={() => onNavigate?.('home')} href="#proceso">Guías y Patrones de Crochet</a>
              </li>
            </ul>
          </div>

          {/* Payments and Shipping column */}
          <div className="space-y-3">
            <h3 className="font-label-lg text-sm text-on-surface uppercase tracking-wider font-bold">
              Pagos y Envíos Nacionales
            </h3>
            <p className="font-body-sm text-sm text-on-surface-variant leading-relaxed">
              🇨🇴 <strong className="text-primary font-semibold">Envíos únicamente a nivel nacional en Colombia</strong> despachados directamente desde Neiva, Huila. Pago seguro por Bancolombia, Nequi y Daviplata.
            </p>
            <div className="flex items-center gap-3 text-secondary pt-1">
              <span className="material-symbols-outlined text-2xl" title="Transferencia Bancolombia / Nequi / Daviplata">account_balance</span>
              <span className="material-symbols-outlined text-2xl" title="Comprobante Oficial de Pago">receipt_long</span>
              <span className="material-symbols-outlined text-2xl" title="Envíos a toda Colombia">local_shipping</span>
              <span className="material-symbols-outlined text-2xl" title="Pago Seguro">lock</span>
            </div>
            <p className="font-label-sm text-xs text-on-surface-variant">
              Emisión de Comprobante Oficial de Pago con cada orden registrada.
            </p>
          </div>

          {/* Workshop & Community column */}
          <div className="space-y-3">
            <h3 className="font-label-lg text-sm text-on-surface uppercase tracking-wider font-bold">
              Taller &amp; Comunidad
            </h3>
            <p className="font-body-sm text-sm text-on-surface-variant leading-relaxed">
              Acompaña el proceso de confección, nuevos lanzamientos y sorteos en nuestras redes artesanales.
            </p>
            <div className="flex items-center gap-3 pt-2">
              <a
                href="https://instagram.com"
                target="_blank"
                rel="noopener noreferrer"
                className="w-9 h-9 rounded-full bg-badge-lavender-bg text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all shadow-sm"
                title="Instagram"
              >
                <span className="material-symbols-outlined text-lg">photo_camera</span>
              </a>
              <a
                href="https://wa.me/56987654321"
                target="_blank"
                rel="noopener noreferrer"
                className="w-9 h-9 rounded-full bg-badge-lavender-bg text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all shadow-sm"
                title="WhatsApp Directo"
              >
                <span className="material-symbols-outlined text-lg">send</span>
              </a>
              <a
                href="mailto:hola@maranacrochet.art"
                className="w-9 h-9 rounded-full bg-badge-lavender-bg text-primary flex items-center justify-center hover:bg-primary hover:text-on-primary transition-all shadow-sm"
                title="Correo Taller"
              >
                <span className="material-symbols-outlined text-lg">mail</span>
              </a>
            </div>
            <p className="font-body-sm text-xs font-semibold text-primary pt-2">
              hola@maranacrochet.art
            </p>
          </div>
        </div>

        {/* Bottom copyright and legal */}
        <div className="pt-8 border-t border-outline-variant/30 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="font-body-sm text-xs text-on-surface-variant text-center sm:text-left">
            © 2024 Maraña Crochet. Todos los derechos reservados. Diseñado para coleccionistas de ternura.
          </p>
          <div className="flex gap-6 font-label-md text-xs text-on-surface-variant">
            <button onClick={() => onNavigate?.('client-recovery')} className="hover:text-primary transition-colors cursor-pointer">
              Términos y Condiciones
            </button>
            <button onClick={() => onNavigate?.('product-lulu')} className="hover:text-primary transition-colors cursor-pointer">
              Cuidado del Amigurumi
            </button>
            <button onClick={() => onNavigate?.('client-recovery')} className="hover:text-primary transition-colors cursor-pointer">
              Privacidad
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
};
