import { Product } from '../types';

export const MARANA_LOGO = "https://lh3.googleusercontent.com/aida/AEtjO1XxeFyhf91NF2o6wfmAURwjPXPWLxVjdM76JCu9P1nJ33TFBTFqoa_n6qUq-V1tUtww93Nvp7-df-Yc4Wg85o6Fjd4a3RPApCLPRDNrAUEfLiztHg20_9wVs6GjhQuNK8qi_9jONRneyb7uoSV1YsqmBibgPTOYHZDp_oSS7ma68tW_t9fTqmLfhfQgC1c4xAny9J2DB0tlZ7hXnPVsuhAPZ6xENS66TByylu0krqcoGQrUn_Kmm-KpzI74YRHQDf24HQecd3x1";

export const INITIAL_PRODUCTS: Product[] = [
  {
    id: 'lulu-aranita',
    name: 'Lulú la Arañita Cósmica',
    sku: 'MAR-ARAN-004',
    category: 'espacio',
    categoryLabel: 'Criaturas del Espacio & Estrellas',
    price: 24990,
    originalPrice: 31990,
    discountBadge: '-22% OFF',
    usdApprox: 28.00,
    rating: 4.9,
    reviewCount: 38,
    stock: 4,
    maxStock: 15,
    reserved: 2,
    status: 'low-stock',
    statusLabel: '¡Solo quedan 4 unidades!',
    tagline: 'Pieza Única Artesanal · Edición Limitada',
    description: 'Lulú es una arañita amigable nacida en el telar estelar de Maraña Crochet. Diseñada con tonos púrpura intenso, lila suave y mejillas bordadas con rubor rosa. Sus ojos reflejan estrellas blancas que simbolizan la curiosidad y la compañía nocturna para niños y coleccionistas.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAVaFIx8lSl6cGACZjo78GFLORRXHKDw4rabXIFcnhHisWqu57RyzoioUtrz2RwtI3aBe6zeu3NarqSxTq3g4Tg1wLFmpBZar5r94B4-TyjmDi6EhXsW3HFTmi_fgcwbfHrnhMDsieRROClWdQ0nqJC6usUtn-xHdgpzxFB0FB1Mqxr2P0uMzPaKXET7WzscvUjGO1TtCobLBumJYWGf578WaZQddGYVGQczSIz2pSLVIslngEByLx6',
        alt: 'Lulú la Arañita Cósmica en plano general'
      },
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDut1zN58wR0uR5hShZzjzOLMPHmtzdQu97XdpSVY_RjU23pNg5BkcfXZtUmi3qp0SL6B2If4s8pm4udgZLyp48WUQMhPJ1UMO22P31GNXWeFFw7mr101CIAte7LbYkWgFiquz0tbHaycswwo7WfeRsb6KwaNWpuG6x6XdzY-8knIWO_uR9p1LyMQrZk3o0byaKU-thAzdyMxGKHF88MTDoXaeFH6ms4QtiV7emJ3nn6CnvC-GWJ0zh',
        alt: 'Pose frontal de Lulú la Arañita Cósmica'
      },
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCTZ-1bXwuJIwNCbgzfU30e_R-Ab92X11YNXvCXAXjRsompEif9lJ3G4kF6PtKElNOSkgOFXw1vIQQI5pWs1vSpKGi8nvhcTaOErpWjUnkWpXhz_tXlaf7iwusTs1jsPZR4f8A4iC6nVF1oy_guyBWJvqBBBqa2Ef1YKLEgfaprdYZURshL2UeWjMz-BCCUw42mKx5dOTT1IoiNURT8ONeIyIc3xBXQhL5_pPueXcfTcf0VWTc1rZzH',
        alt: 'Detalle macro de las puntadas y textura del hilo de algodón'
      },
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAyS4sgXlsT3P5NK2Sb_lOcMv3Fnkas9kksVYkhTC2SU8FoofKJ2OMpJbOg3kNlExnnls8LJBKSkcoizt8b80OrPINdXwBAO2ezbhskCcCYg-8N14-na5trd17mozlsA4MBLR94bKTfBRJM33jx5kZsmTQ_2HPUNGvJBDRcAfZ72Y8rWeXOLntOB-JaBHLJR3P23vllX0ulOsZ72WtT1x8EGFhpxtGt-B1qrwUaA5SDEbkz8zqw1eTJ',
        alt: 'Escala en manos de la artesana (18 cm)'
      },
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBLW2VdruoXWaXqoSgjY11mIVIBqx3gHO6EKhvY0vdvquHtZNK5esi64UudZe3P2aGWEIlQGDjajB83jr_Wa0lvud4M2n9hGwvpgto2ZozHMaqUXUGBsOwhH3lHI4eezBcqlqm78LssOJRLjoPymw49qeJpPRdRhAZlnRslkHYwjcGCozdJo5ew7Zovs9I6c6xC14k6X4GWHsugD9d9RMuWYcAh-NWyWQUCUBgv-eBhmdz95C272MKN',
        alt: 'Empaque aromático de lavanda y caja kraft de regalo'
      }
    ],
    craftHours: 6.5,
    dimensions: '18 cm x 15 cm',
    weight: '140 gramos',
    material: '100% Algodón Peinado Tangüis',
    filling: 'Vellón siliconado virgen antiácaros',
    safety: 'Ojos 100% bordados (sin plásticos duros)',
    includes: 'Caja de regalo + Certificado de Adopción numerado'
  },
  {
    id: 'murcielago-lavanda',
    name: 'Murciélago Lavanda Nocturno',
    sku: 'MAR-MURC-002',
    category: 'espacio',
    categoryLabel: 'Colección Espacio',
    price: 24990,
    usdApprox: 27.00,
    rating: 5.0,
    reviewCount: 36,
    stock: 4,
    maxStock: 12,
    reserved: 0,
    status: 'low-stock',
    statusLabel: '¡Últimas 4 unidades!',
    tagline: 'Colección Noctámbula · Orejitas Bordadas',
    description: 'Con alitas tejidas que se abrazan al cuerpo y orejitas en degrade lila cósmico. Hilado 100% algodón Tangüis con ojos estelares seguros.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZ1Gae0ANE2yDSm_fl4UDG6A99dQ-FGtiZohNlj87AMAXTeZHkHkDz5ZnmXxv8Wb3g5bGCWHzkFPorhvHAzFBTDoJISBWXwYfWyAohWUu_QDo9iNwHzCBVHg6_Y6QEhFpn1WQNBXQiov-YKMAoGyOZSWFUhTXf3Ft7fku1gfLf12MvrtcX2oca68WcDhiwyvEFHWn-DsvBoRuUa7rw4s3rgXxQ2JwzzdH5xCOXwuAIju44V2HFo66K',
        alt: 'Murciélago Lavanda Nocturno amigurumi'
      }
    ],
    craftHours: 4.8,
    dimensions: '16 cm x 20 cm con alas extendidas',
    weight: '120 gramos',
    material: '100% Algodón Tangüis',
    filling: 'Vellón hipoalergénico',
    safety: 'Alas articuladas con fijación de seguridad',
    includes: 'Caja kraft decorada + Certificado'
  },
  {
    id: 'gatito-cosmico',
    name: 'Gatito Cósmico de Felpa',
    sku: 'MAR-GAT-009',
    category: 'espacio',
    categoryLabel: 'Colección Estelar',
    price: 22990,
    originalPrice: 26500,
    usdApprox: 25.50,
    rating: 4.9,
    reviewCount: 52,
    stock: 11,
    maxStock: 15,
    reserved: 1,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (11)',
    tagline: 'Apego & Ternura · Lana Chenille Suave',
    description: 'Lana Chenille ultra suave con detalle bordado dorado. Incluye una estrellita tejida extraíble entre sus patitas delanteras.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDZ5b3WdVNoATVZeAebIdRHy83ogkaWs3hXxhT1UXHqo95kyz2unTJm-_-dZvE2WYcDH9-I0qkUQr8fO2c5bypAm77vXy1jYmRRNQWBkCvoukzr0_OFLNUD70RPk9BT1e0jnUdX7iDyPWslNgs763o_MxKqoa0FZ6uUJMnlCTF5qAKd9yqsstbZ6ZVBPPZfBjQvrhEtDjGPk_gsOlSZKWvO9GL5c8H-Z4HMZlzOPQ6_Figr4Co7PSf5',
        alt: 'Gatito Cósmico de Felpa amigurumi'
      }
    ],
    craftHours: 7.0,
    dimensions: '17 cm x 12 cm',
    weight: '160 gramos',
    material: 'Lana Chenille aterciopelada',
    filling: 'Vellón siliconado antiácaros',
    safety: 'Ojos dormilones bordados',
    includes: 'Caja regalo + Estrellita tejida extraíble'
  },
  {
    id: 'polilla-lunar',
    name: 'Polilla Lunar de Algodón',
    sku: 'MAR-POLI-012',
    category: 'bosque',
    categoryLabel: 'Bosque Encantado',
    price: 27990,
    usdApprox: 31.00,
    rating: 5.0,
    reviewCount: 18,
    stock: 0,
    maxStock: 8,
    reserved: 0,
    status: 'preorder',
    statusLabel: 'En Aguja (7 días)',
    preorderDays: 7,
    tagline: 'Colección Luna · Alas Modelables',
    description: 'Algodón pima orgánico con alas flexibles modelables decoradas con fases lunares bordadas a mano y cuellito de felpa esponjoso.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCZuedVPxDSDI0i2EBpDWcrNpBistZV7eYvKRV_vMDk-s-MaV_bOJLmETdNnS6QswGvzxxh4M3aqVTI3InQJ3d40w8HawPgcLzh5nZ8nhkOekqy9MI34Nppn9CxMbkagm_APNhMMxqd8f4Z9kAnZsn1qkikadjslMd1gBB7p53FeQG6UWtdk2t3et4LES5XOFnvHNkbHcLHEcI2KFtqTemxzZteFK1c1sJ21_2hVpplit7yyWJD-2M7',
        alt: 'Polilla Lunar de Algodón amigurumi'
      }
    ],
    craftHours: 5.2,
    dimensions: '15 cm x 18 cm de envergadura',
    weight: '110 gramos',
    material: 'Algodón Pima Orgánico',
    filling: 'Vellón siliconado',
    safety: 'Antenas suaves y bordado hilo dorado',
    includes: 'Caja ecológica + Certificado de nacimiento'
  },
  {
    id: 'pulpo-calma',
    name: 'Pulpo Calma Lavanda',
    sku: 'MAR-PULP-001',
    category: 'bebes',
    categoryLabel: 'Apego Infantil',
    price: 18990,
    usdApprox: 21.00,
    rating: 5.0,
    reviewCount: 41,
    stock: 8,
    maxStock: 14,
    reserved: 2,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (8)',
    tagline: 'Apego para Bebés · 100% Hipoalergénico',
    description: 'Espirales calmantes diseñadas para recordar el cordón umbilical. Hilado 100% hipoalergénico y ojos bordados anti-asfixia para recién nacidos.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDH4vtOTNTfxQAG7sFYfwLHOw_mudqxcv9_uQL6_zq5WU0WCPN82gsOkoEDDcEXDPT6mq5sTLDlZokWZbtZ44QfFri35Qe9CLDSAWfQ28eTtfJAu9sOZUBiucGGzX4Pn4hzuZeHnwSgm67K6x2smeXiVjWvMSEuCAJwcgWfQEssuc_sjReziJleUW11iGlYJjbu0qDVCvx9rNotepIK8lX0RLJwWrm5dyL9cEmmYnIYdle9y2O019mI',
        alt: 'Pulpo Calma Lavanda amigurumi'
      }
    ],
    craftHours: 3.5,
    dimensions: '20 cm longitud de tentáculos',
    weight: '90 gramos',
    material: 'Algodón peinado certificado OEKO-TEX',
    filling: 'Vellón antibacteriano lavable',
    safety: 'Libre de piezas plásticas',
    includes: 'Guía de apego infantil + Caja estuche'
  },
  {
    id: 'ajolote-estelar',
    name: 'Ajolote Estelar Rosa',
    sku: 'MAR-AJOL-005',
    category: 'nuevos',
    categoryLabel: 'Nuevas Creaciones',
    price: 23500,
    usdApprox: 26.00,
    rating: 4.9,
    reviewCount: 14,
    stock: 6,
    maxStock: 10,
    reserved: 0,
    status: 'in-stock',
    statusLabel: 'Nuevo Ingreso',
    tagline: 'Criaturas Mágicas · Branquias de Terciopelo',
    description: 'Branquias de terciopelo violeta suave, textura acaramelada y estrellita de deseo en sus manitos. El preferido de las amantes de la ternura.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB6nnoYxUE9ZlCij1zjxW07EPDeZ6-FQ0lvlepOyOIMJgHqyU8DANqg0F3EZD-tJCT1vcOqGIHyS20xmJfkGmuGpnxPRlV2a20ziLwmXiSRXfnAQX9YQJPe9R11BDLHsA41sLJyZsuEnI738uKvW6SqN4xbcxuCw6Y9oMGTgEBvGScd9rsC9GvjrWL0TPf1YPViJLGnz1prMle6jciLmqSwGBGH7waO2xc-2MktDQ9zLpJIkdzTRmLC',
        alt: 'Ajolote Estelar Rosa amigurumi'
      }
    ],
    craftHours: 5.5,
    dimensions: '19 cm de largo',
    weight: '130 gramos',
    material: 'Algodón Tangüis y terciopelo sintético',
    filling: 'Vellón siliconado hipoalergénico',
    safety: 'Bordado manual reforzado',
    includes: 'Caja de adopción + Estrellita'
  },
  {
    id: 'hongo-fantasia',
    name: 'Hongo Fantasía del Musgo',
    sku: 'MAR-HONG-008',
    category: 'bosque',
    categoryLabel: 'Bosque Encantado',
    price: 15990,
    usdApprox: 18.00,
    rating: 4.8,
    reviewCount: 22,
    stock: 2,
    maxStock: 8,
    reserved: 0,
    status: 'low-stock',
    statusLabel: '¡Últimas 2 unidades!',
    tagline: 'Colección Botánica · Sombrero Morado Amatista',
    description: 'Sombrero morado amatista con motas bordadas a mano en tonos pasteles. Base equilibrada para adornar repisas y escritorios.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDPvzRpZEGMe-hO8KGeIQeyD7yYp-fJ0ScaohfrT6j1PwFoxVIdkjiJrvR4XS9WIV5taXtBX6r-MGaEDMIr5VeHICgrUph2QMbk4-BsWSw3_qhPcd8joyIvdlKHtJgMVhYhjdIwyjjMCpDnKkS9kKt58i5IkAAWv1_ddQ2tKbLfErUM1j8zTOacIbbIrIPM9a-enfIC3-E9ceSQmSPU3gJqo2KR8IG9QWv9BtTR4WhI7sTpTujkkP3v',
        alt: 'Hongo Fantasía del Musgo amigurumi'
      }
    ],
    craftHours: 3.0,
    dimensions: '13 cm x 10 cm',
    weight: '85 gramos',
    material: 'Algodón mercerizado',
    filling: 'Vellón de alta densidad',
    safety: 'Bordado seguro',
    includes: 'Caja kraft + musgo preservado decorativo'
  },
  {
    id: 'dragon-estelar',
    name: 'Dragón Estelar de Bolsillo',
    sku: 'MAR-DRAG-011',
    category: 'espacio',
    categoryLabel: 'Colección Espacio',
    price: 26990,
    usdApprox: 30.00,
    rating: 5.0,
    reviewCount: 67,
    stock: 0,
    maxStock: 6,
    reserved: 0,
    status: 'soldout',
    statusLabel: 'Agotado Temporalmente',
    tagline: 'Criaturas Míticas · Lana Perlada',
    description: 'Lana acrílica perlada con alas y cuernitos de brillo cósmico. Se acurruca plácidamente durmiendo en la palma de tu mano.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBNxzruafVQyyCUPZ59ZVNJNJys4L4Q9nHDXj3MUfoTg_GiWgtDBtxrWDVopl4b64zHFIC2fwHBpf5ph4Ny0jFKB_N6vSnmcFhcSnos7HC3bJpyZzsjmBKp4NtTD-zYMZo5wi2IV79jnxWeJfGyVr8dNOyn9Wn_pWOehJ1WGfehY_iSecfk9CHY97XoD0I39GF415DQV2EX-DE80M6Ohs5N9ZJPNkaB4gCvcC7uCFBvDEpef3RSOTkt',
        alt: 'Dragón Estelar de Bolsillo amigurumi'
      }
    ],
    craftHours: 8.0,
    dimensions: '14 cm x 11 cm',
    weight: '120 gramos',
    material: 'Lana perlada y fieltro suave',
    filling: 'Vellón virgen antiácaros',
    safety: 'Costuras reforzadas',
    includes: 'Caja cofre de madera + Certificado'
  },
  {
    id: 'mantita-osito',
    name: 'Mantita de Apego Osito',
    sku: 'MAR-MAN-003',
    category: 'bebes',
    categoryLabel: 'Apego Infantil',
    price: 21990,
    usdApprox: 24.50,
    rating: 5.0,
    reviewCount: 29,
    stock: 5,
    maxStock: 10,
    reserved: 1,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (5)',
    tagline: 'Primera Infancia · Hilado Certificado OEKO-TEX',
    description: 'Hilado 100% hipoalergénico certificado OEKO-TEX. Combina la cabecita de un osito tierno con un paño de granny square en tonos lavanda y crema.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuD5DaDO9cK87KEcG-x7QkhbLs1kbZFcQVO5ohuenDheplx03bgCDHdDN2KQOUYq8wtH7vUxpzTc21hlLGQixWpFfjU2ZZFAiKmm-nh0YPQ9GTo7VYEwYUiRggSb5KKZhgfuGr5eL2F6Eq9tDr9FIJ1vzi_9ss-OL5IPHlVUrikzj2liQXwAmZU0VtWcxcQfIepcD4kqAqS1m8KVD9k2JokIXyEgkXSHHDN0SzWVSow0FXVD1EoLdX9q',
        alt: 'Mantita de Apego Osito amigurumi'
      }
    ],
    craftHours: 5.0,
    dimensions: '30 cm x 30 cm de mantita',
    weight: '150 gramos',
    material: '100% Algodón Peinado Antialérgico',
    filling: 'Vellón siliconado hipoalergénico',
    safety: 'Bordado total sin piezas sueltas',
    includes: 'Caja regalo + Tarjeta dedicatoria'
  },
  {
    id: 'ranita-sabana',
    name: 'Ranita de la Sabana Cósmica',
    sku: 'MAR-RANA-014',
    category: 'bosque',
    categoryLabel: 'Bosque Encantado',
    price: 21900,
    originalPrice: 26000,
    discountBadge: 'Popular',
    usdApprox: 24.50,
    rating: 4.9,
    reviewCount: 29,
    stock: 7,
    maxStock: 12,
    reserved: 1,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (7)',
    tagline: 'Inspirada en los humedales andinos · Ojos tiernos bordados',
    description: 'Ranita verde esmeralda tejida con hilo 100% de algodón peinado con gorrito de hongo amatista desmontable. Ideal para acompañar escritorios y rincones de meditación.',
    images: [
      {
        url: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZ1Gae0ANE2yDSm_fl4UDG6A99dQ-FGtiZohNlj87AMAXTeZHkHkDz5ZnmXxv8Wb3g5bGCWHzkFPorhvHAzFBTDoJISBWXwYfWyAohWUu_QDo9iNwHzCBVHg6_Y6QEhFpn1WQNBXQiov-YKMAoGyOZSWFUhTXf3Ft7fku1gfLf12MvrtcX2oca68WcDhiwyvEFHWn-DsvBoRuUa7rw4s3rgXxQ2JwzzdH5xCOXwuAIju44V2HFo66K',
        alt: 'Ranita de la Sabana Cósmica'
      }
    ],
    craftHours: 4.2,
    dimensions: '14 cm x 11 cm',
    weight: '95 gramos',
    material: '100% Algodón Peinado Colombiano',
    filling: 'Vellón siliconado hipoalergénico',
    safety: 'Ojos bordados con hilo satinado',
    includes: 'Gorrito de hongo + Caja kraft con ramillete de lavanda'
  },
  {
    id: 'kit-crochet-principiante',
    name: 'Kit de Crochet Artesanal Maraña',
    sku: 'MAR-KIT-001',
    category: 'kits',
    categoryLabel: 'KITS & APRENDIZAJE',
    price: 34900,
    originalPrice: 39900,
    discountBadge: 'KIT COMPLETO',
    usdApprox: 38.00,
    rating: 5.0,
    reviewCount: 48,
    stock: 12,
    maxStock: 20,
    reserved: 1,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (12)',
    tagline: 'Aprende a tejer tu propia arañita cósmica paso a paso',
    description: 'El kit oficial de iniciación de Maraña Crochet. Contiene 3 ovillos de algodón peinado hipoalergénico en tonos amatista y lavanda, ganchillo ergonómico de 2.5 mm, marcadores de puntada en bronce artesanal, vellón siliconado de alta densidad, aguja lanera y folleto de adopción con acceso a la videoguía exclusiva.',
    images: [
      {
        url: '/kit-crochet.jpg',
        alt: 'Kit de Crochet Artesanal Maraña'
      }
    ],
    craftHours: 1.5,
    dimensions: '22 cm x 18 cm x 8 cm (caja kraft)',
    weight: '320 gramos',
    material: '100% Algodón Peinado Colombiano + Aguja ergonómica',
    filling: 'Vellón siliconado incluido para 1 amigurumi completo',
    safety: 'Materiales seguros para mayores de 8 años',
    includes: '3 Ovillos + Ganchillo 2.5mm + Marcadores + Certificado + Guía Paso a Paso'
  },
  {
    id: 'kit-ranita-cosmica',
    name: 'Kit Teje tu Ranita Cósmica',
    sku: 'MAR-KIT-002',
    category: 'kits',
    categoryLabel: 'KITS & APRENDIZAJE',
    price: 32900,
    originalPrice: 36900,
    discountBadge: 'EDICIÓN ESPECIAL',
    usdApprox: 35.00,
    rating: 4.9,
    reviewCount: 31,
    stock: 8,
    maxStock: 15,
    reserved: 0,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (8)',
    tagline: 'Todo lo necesario para tejer a la Ranita de la Sabana',
    description: 'Incluye ovillos verde esmeralda y morado amatista, ojos de seguridad con traba reforzada, patrón ilustrado exclusivo con diagrama de puntos y ramillete aromático de lavanda.',
    images: [
      {
        url: '/kit-crochet.jpg',
        alt: 'Kit Teje tu Ranita Cósmica'
      }
    ],
    craftHours: 2.0,
    dimensions: '20 cm x 16 cm x 7 cm',
    weight: '290 gramos',
    material: 'Algodón peinado mercerizado',
    filling: 'Vellón siliconado hipoalergénico',
    safety: 'Ojos con traba de seguridad infantil',
    includes: 'Hilados + Patrón exclusivo + Aguja ergonómica + Ojos de seguridad'
  },
  {
    id: 'kit-mini-bosque',
    name: 'Kit Mini Criaturas del Bosque',
    sku: 'MAR-KIT-003',
    category: 'kits',
    categoryLabel: 'KITS & APRENDIZAJE',
    price: 28900,
    originalPrice: 33500,
    discountBadge: 'PRINCIPIANTES',
    usdApprox: 30.00,
    rating: 4.8,
    reviewCount: 22,
    stock: 10,
    maxStock: 15,
    reserved: 0,
    status: 'in-stock',
    statusLabel: 'Stock Disponible (10)',
    tagline: 'Pack de 3 honguitos y bellotas mágicas para iniciarse',
    description: 'Ideal para quienes quieren empezar con proyectos rápidos y encantadores. Incluye mini ovillos de 5 tonalidades terrosas y pasteles, argollas para llavero y relleno.',
    images: [
      {
        url: '/kit-crochet.jpg',
        alt: 'Kit Mini Criaturas del Bosque'
      }
    ],
    craftHours: 1.0,
    dimensions: '18 cm x 14 cm x 6 cm',
    weight: '220 gramos',
    material: 'Algodón 100% natural',
    filling: 'Vellón hipoalergénico',
    safety: 'Bordado manual reforzado',
    includes: '5 Mini ovillos + 3 Argollas llavero + Ganchillo + Guía'
  }
];

export const PRODUCTS = INITIAL_PRODUCTS;


