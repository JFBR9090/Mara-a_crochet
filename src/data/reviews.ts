import { Review } from '../types';

export const INITIAL_REVIEWS: Review[] = [
  {
    id: 'rev-001',
    customerInitials: 'VC',
    customerName: 'Valentina Castro',
    emailMasked: 'vc****@gmail.com',
    orderId: '#MC-2024-884',
    sku: '#MAR-ARAN-004',
    productName: 'Lulú la Arañita Cósmica',
    productIcon: 'cruelty_free',
    rating: 5,
    date: 'Hace 2 horas',
    comment: '¡Es aún más hermosa en persona! Los ojitos bordados con forma de estrella tienen una dedicación increíble. Llegó en su cajita aromática de lavanda y el certificado de adopción caligrafiado con mi dedicatoria quedó perfecto. ¡1000% recomendada!',
    verified: true,
    status: 'pending',
    photos: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAq-1Vv9JIPob3K8av55ITbF6ZifJ24CiZJQtvaTyVVeYuZbzRfdF20Qpey2L56j3VhCtBWZH_bNpj0FWJn0PjYdw5xXKo01Lik2Gflhlq7uPz4p8YRXQhoeC0lW_FtqYhszdlBu92ziJl1cLEq-ygTcjlWdJDOOBN1vkOdQMlZaX0yWjVj1kBSDn-FUEUr4YfSDBSmwbS1DElKNwYEPXgxXEnGf6cIR5aQwIJBp9QOq82tI52-R0mE',
        alt: 'Lulú la Arañita en el escritorio de Valentina',
        caption: 'Foto de Valentina en su escritorio de estudio'
      },
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAPjIzrlQVy2SRY1iGfGdnx_tESnsrgKqzsmS5J-A3wyPCwJvwSMLKA3sY7LTpRwavolsepUnb6Bl29X_GG4WgqqyVob4MLA5avUgqC7wFpHd1XuiCb3yZ-7daRo17mrTs4PjuHjlbayF3pcQq4osQZ2qqInjxAcf15G_s5BI2OCrunifw-k83GbsUAVuXN_CoafBcFA5Dt2ji_W0-eoYMBijGiEtnYmAAcKyK5RY7F9Yy-4i-w31ac',
        alt: 'Certificado de adopción y lavanda en caja kraft',
        caption: 'Unboxing con flores secas de lavanda'
      }
    ],
    helpfulCount: 16
  },
  {
    id: 'rev-002',
    customerInitials: 'MR',
    customerName: 'Matías Rojas',
    emailMasked: 'matias.rojas.g@outlook.com',
    orderId: '#MC-2024-883',
    sku: '#MAR-POLI-012',
    productName: 'Polilla Lunar Amigurumi',
    productIcon: 'spa',
    rating: 5,
    date: 'Hace 3 días',
    comment: 'Fue un regalo para mi novia fanática de las arañitas y el crochet. Quedó fascinada con el empaque aromático de lavanda y el certificado con el nombre de la artesana. La textura del algodón Tangüis es inigualable.',
    verified: true,
    status: 'featured',
    photos: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCjmbsJsd_JHxQOH5qu__jBCxJFxJwmOMicmWSmnRl8R8m2JrlptMfQtX0vkO3cLi6KRGzrDwMHw5cyKCr6n7vdQpJcS5atf5xAJ8K-RFyzHoCwuHFQrLwtM0giqziTsdEnpmWOrMktjHtDkE_IGog-nDgv2QjLtqe2jBwXUIgBBVkL5RpV0OLbrju3s0gn-rXpiHm2jJ-wKvFS6rxf3VW8Moo9Rh_aVAGgFQd6ywllS6qase0S9C9S',
        alt: 'Empaque verificado con lavanda y polilla',
        caption: 'Foto adjunta de Matías en el empaque'
      }
    ],
    workshopReply: {
      author: 'Sofía Morales',
      role: 'Tallerista Principal',
      date: 'Hace 2 días',
      text: '¡Hola Matías! Nos alegra de corazón saber que a tu novia le encantó su nueva compañera mágica. Tejer cada alita lunar fue un placer enorme. ¡Muchas gracias por apoyar la artesanía chilena! 💜✨'
    },
    helpfulCount: 24
  },
  {
    id: 'rev-003',
    customerInitials: 'CS',
    customerName: 'Camila Soto',
    emailMasked: 'cami.soto@gmail.com',
    orderId: '#MC-2024-882',
    sku: '#MAR-MURC-002',
    productName: 'Murciélago Lavanda Nocturno',
    productIcon: 'dark_mode',
    rating: 4,
    date: 'Hace 1 día',
    comment: 'El amigurumi es bellísimo y los tonos lavanda son idénticos a las fotos. Solo pongo 4 estrellas porque el despacho de Starken demoró un día más de lo estimado, pero la atención de Sofía por WhatsApp fue súper cordial y atenta.',
    verified: true,
    status: 'pending',
    helpfulCount: 8
  },
  {
    id: 'rev-004',
    customerInitials: 'SA',
    customerName: 'Sofía Alarcón',
    emailMasked: 'sofia.alarcon@gmail.com',
    orderId: '#MC-2024-870',
    sku: '#MAR-ARAN-004',
    productName: 'Lulú la Arañita Cósmica',
    productIcon: 'cruelty_free',
    rating: 5,
    date: 'Hace 2 semanas',
    comment: 'La calidad es inmejorable, cero pelusas y el hilo es de algodón genuino super suave para bebés. Se siente el cariño en cada puntada. ¡Volveré a comprar el murciélago!',
    verified: true,
    status: 'approved',
    helpfulCount: 12
  },
  {
    id: 'rev-005',
    customerInitials: 'UA',
    customerName: 'Usuario Anónimo',
    emailMasked: 'anonimo@externo.net',
    orderId: 'Sin Orden',
    sku: '#EXT-SPAM-001',
    productName: 'Intento de valoración externa',
    productIcon: 'person_off',
    rating: 1,
    date: 'Hace 4 días',
    comment: 'Hacen envíos a México? Venden el patrón en PDF? Contactar a mi telegram...',
    verified: false,
    status: 'rejected',
    rejectionReason: 'Regla TRD #REV-01 Aplicada: Solamente clientes Keycloak con estado de despacho "Entregado" pueden dejar reseñas de productos.'
  }
];
