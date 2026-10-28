-- =====================================================================
-- Base de Datos y Semilla Inicial: MARAÑA CROCHET
-- Taller Artesanal de Amigurumis - Neiva, Huila (Colombia)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `marana_crochet` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `marana_crochet`;

-- 1. Tabla de Productos y Amigurumis
CREATE TABLE IF NOT EXISTS `products` (
  `id` VARCHAR(100) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `categoryLabel` VARCHAR(100) NOT NULL,
  `price` INT NOT NULL,
  `originalPrice` INT DEFAULT NULL,
  `discountBadge` VARCHAR(50) DEFAULT NULL,
  `usdApprox` DECIMAL(10,2) NOT NULL,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 5.0,
  `reviewCount` INT NOT NULL DEFAULT 0,
  `stock` INT NOT NULL DEFAULT 0,
  `maxStock` INT NOT NULL DEFAULT 10,
  `reserved` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(50) NOT NULL DEFAULT 'in-stock',
  `statusLabel` VARCHAR(100) NOT NULL DEFAULT 'Disponible',
  `tagline` VARCHAR(255) DEFAULT '',
  `description` TEXT,
  `images` LONGTEXT,
  `craftHours` DECIMAL(4,1) DEFAULT 4.0,
  `dimensions` VARCHAR(100) DEFAULT '',
  `weight` VARCHAR(100) DEFAULT '',
  `material` VARCHAR(100) DEFAULT '',
  `filling` VARCHAR(100) DEFAULT '',
  `safety` VARCHAR(100) DEFAULT '',
  `includes` VARCHAR(255) DEFAULT '',
  `preorderDays` INT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Clientes (Maraña Accounts)
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(50) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL DEFAULT 'Huila',
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `theme_preference` VARCHAR(20) NOT NULL DEFAULT 'light',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Pedidos (Orders)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` VARCHAR(100) NOT NULL PRIMARY KEY,
  `date` VARCHAR(100) NOT NULL,
  `customerId` INT NULL,
  `customerName` VARCHAR(255) NOT NULL,
  `customerEmail` VARCHAR(255) NOT NULL,
  `customerPhone` VARCHAR(100) NOT NULL,
  `customerRut` VARCHAR(50) NOT NULL,
  `address` LONGTEXT,
  `shippingMethod` VARCHAR(50) NOT NULL,
  `shippingCost` INT NOT NULL DEFAULT 0,
  `items` LONGTEXT NOT NULL,
  `subtotal` INT NOT NULL,
  `total` INT NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'revision',
  `paymentMethod` VARCHAR(100) NOT NULL DEFAULT 'wompi_card',
  `bank` VARCHAR(100) NOT NULL DEFAULT 'Bancolombia',
  `accountType` VARCHAR(100) NOT NULL DEFAULT 'Cuenta de Ahorros',
  `transferDetails` LONGTEXT,
  `giftDedication` TEXT DEFAULT NULL,
  `auditLog` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`customerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Reseñas y Testimonios (Reviews)
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` VARCHAR(100) NOT NULL PRIMARY KEY,
  `customerInitials` VARCHAR(10) NOT NULL,
  `customerName` VARCHAR(255) NOT NULL,
  `emailMasked` VARCHAR(255) NOT NULL,
  `orderId` VARCHAR(100) DEFAULT '',
  `sku` VARCHAR(100) DEFAULT '',
  `productName` VARCHAR(255) NOT NULL,
  `productIcon` VARCHAR(50) DEFAULT 'star',
  `rating` INT NOT NULL DEFAULT 5,
  `date` VARCHAR(100) NOT NULL,
  `comment` TEXT NOT NULL,
  `verified` TINYINT(1) NOT NULL DEFAULT 1,
  `status` VARCHAR(50) NOT NULL DEFAULT 'approved',
  `photos` LONGTEXT DEFAULT NULL,
  `workshopReply` LONGTEXT DEFAULT NULL,
  `rejectionReason` TEXT DEFAULT NULL,
  `helpfulCount` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS SEMILLA: PRODUCTOS ARTESANALES
-- =====================================================================

INSERT INTO `products` 
(`id`, `name`, `sku`, `category`, `categoryLabel`, `price`, `originalPrice`, `discountBadge`, `usdApprox`, `rating`, `reviewCount`, `stock`, `maxStock`, `reserved`, `status`, `statusLabel`, `tagline`, `description`, `images`, `craftHours`, `dimensions`, `weight`, `material`, `filling`, `safety`, `includes`, `preorderDays`)
VALUES
-- 1. Espacio & Estrellas
('lulu-aranita', 'Lulú la Arañita Cósmica', 'MAR-ARAN-004', 'espacio', 'Criaturas del Espacio & Estrellas', 24990, 31990, '-22% OFF', 6.25, 4.9, 38, 4, 15, 2, 'low-stock', '¡Solo quedan 4 unidades!', 'Pieza Única Artesanal · Edición Limitada', 'Lulú es una arañita amigable nacida en el telar estelar de Maraña Crochet. Diseñada con tonos púrpura intenso, lila suave y mejillas bordadas con rubor rosa. Sus ojos reflejan estrellas blancas que simbolizan la curiosidad y la compañía nocturna.', '[{"url":"https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=800&q=80","alt":"Lulú la Arañita Cósmica"}]', 6.5, '18 cm x 15 cm', '140 gramos', '100% Algodón Peinado Colombiano', 'Vellón siliconado virgen antiácaros', 'Ojos 100% bordados (sin plásticos duros)', 'Caja de regalo + Certificado de Adopción numerado', NULL),

('murcielago-lavanda', 'Murciélago Lavanda Nocturno', 'MAR-MURC-002', 'espacio', 'Colección Espacio', 24990, NULL, NULL, 6.25, 5.0, 36, 4, 12, 0, 'low-stock', '¡Últimas 4 unidades!', 'Colección Noctámbula · Orejitas Bordadas', 'Con alitas tejidas que se abrazan al cuerpo y orejitas en degrade lila cósmico. Hilado 100% algodón Tangüis con ojos estelares seguros.', '[{"url":"https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80","alt":"Murciélago Lavanda Nocturno"}]', 4.8, '16 cm x 20 cm extendido', '120 gramos', '100% Algodón Colombiano', 'Vellón hipoalergénico', 'Alas articuladas con fijación segura', 'Caja kraft decorada + Certificado', NULL),

('gatito-cosmico', 'Gatito Cósmico de Felpa', 'MAR-GAT-009', 'espacio', 'Colección Estelar', 22990, 26500, NULL, 5.75, 4.9, 52, 11, 15, 1, 'in-stock', 'Stock Disponible (11)', 'Apego & Ternura · Lana Chenille Suave', 'Lana Chenille ultra suave con detalle bordado dorado. Incluye una estrellita tejida extraíble entre sus patitas delanteras.', '[{"url":"https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=800&q=80","alt":"Gatito Cósmico de Felpa"}]', 7.0, '17 cm x 12 cm', '160 gramos', 'Lana Chenille aterciopelada', 'Vellón siliconado antiácaros', 'Ojos dormilones bordados', 'Caja regalo + Estrellita tejida extraíble', NULL),

('dragon-estelar', 'Dragón Estelar de Bolsillo', 'MAR-DRAG-011', 'espacio', 'Colección Espacio', 26990, NULL, NULL, 6.75, 5.0, 67, 3, 6, 0, 'low-stock', '¡Solo quedan 3 unidades!', 'Criaturas Míticas · Lana Perlada', 'Lana acrílica perlada con alas y cuernitos de brillo cósmico. Se acurruca plácidamente durmiendo en la palma de tu mano.', '[{"url":"https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80","alt":"Dragón Estelar de Bolsillo"}]', 8.0, '14 cm x 11 cm', '120 gramos', 'Lana perlada y fieltro suave', 'Vellón virgen antiácaros', 'Costuras reforzadas', 'Caja cofre de madera + Certificado', NULL),

-- 2. Bosque Encantado & Magia
('ranita-sabana', 'Ranita de la Sabana Cósmica', 'MAR-RANA-014', 'bosque', 'Bosque Encantado', 21900, 26000, 'Popular', 5.50, 4.9, 29, 7, 12, 1, 'in-stock', 'Stock Disponible (7)', 'Inspirada en humedales andinos · Ojos tiernos bordados', 'Ranita verde esmeralda tejida con hilo 100% de algodón peinado con gorrito de hongo amatista desmontable. Ideal para acompañar escritorios y rincones de meditación.', '[{"url":"https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80","alt":"Ranita de la Sabana Cósmica"}]', 4.2, '14 cm x 11 cm', '95 gramos', '100% Algodón Peinado Colombiano', 'Vellón siliconado hipoalergénico', 'Ojos bordados con hilo satinado', 'Gorrito de hongo + Caja kraft con ramillete de lavanda', NULL),

('polilla-lunar', 'Polilla Lunar de Algodón', 'MAR-POLI-012', 'bosque', 'Bosque Encantado', 27990, NULL, 'PRE-ORDEN', 7.00, 5.0, 18, 0, 8, 0, 'preorder', 'En Aguja (7 días)', 'Colección Luna · Alas Modelables', 'Algodón pima orgánico con alas flexibles modelables decoradas con fases lunares bordadas a mano y cuellito de felpa esponjoso. Disponible bajo confección personalizada.', '[{"url":"https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80","alt":"Polilla Lunar de Algodón"}]', 5.2, '15 cm x 18 cm de envergadura', '110 gramos', 'Algodón Pima Orgánico', 'Vellón siliconado', 'Antenas suaves y bordado hilo dorado', 'Caja ecológica + Certificado de nacimiento', 7),

('hongo-fantasia', 'Hongo Fantasía del Musgo', 'MAR-HONG-008', 'bosque', 'Bosque Encantado', 15990, NULL, NULL, 4.00, 4.8, 22, 2, 8, 0, 'low-stock', '¡Últimas 2 unidades!', 'Colección Botánica · Sombrero Morado Amatista', 'Sombrero morado amatista con motas bordadas a mano en tonos pasteles. Base equilibrada para adornar repisas y escritorios.', '[{"url":"https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80","alt":"Hongo Fantasía del Musgo"}]', 3.0, '13 cm x 10 cm', '85 gramos', 'Algodón mercerizado', 'Vellón de alta densidad', 'Bordado seguro', 'Caja kraft + musgo preservado decorativo', NULL),

-- 3. Apego Infantil & Primera Infancia
('pulpo-calma', 'Pulpo Calma Lavanda', 'MAR-PULP-001', 'bebes', 'Apego Infantil', 18990, NULL, 'OEKO-TEX', 4.75, 5.0, 41, 8, 14, 2, 'in-stock', 'Stock Disponible (8)', 'Apego para Bebés · 100% Hipoalergénico', 'Espirales calmantes diseñadas para recordar el cordón umbilical. Hilado 100% hipoalergénico con certificación OEKO-TEX Standard 100 y ojos bordados anti-asfixia para recién nacidos.', '[{"url":"https://images.unsplash.com/photo-1582845512747-e42001c95638?auto=format&fit=crop&w=800&q=80","alt":"Pulpo Calma Lavanda"}]', 3.5, '20 cm longitud de tentáculos', '90 gramos', 'Algodón peinado certificado OEKO-TEX', 'Vellón antibacteriano lavable', 'Libre de piezas plásticas (100% bordado)', 'Guía de apego infantil + Caja estuche', NULL),

('mantita-osito', 'Mantita de Apego Osito', 'MAR-MAN-003', 'bebes', 'Apego Infantil', 21990, NULL, NULL, 5.50, 5.0, 29, 5, 10, 1, 'in-stock', 'Stock Disponible (5)', 'Primera Infancia · Hilado Certificado OEKO-TEX', 'Hilado 100% hipoalergénico certificado OEKO-TEX. Combina la cabecita de un osito tierno con un paño de granny square en tonos lavanda y crema.', '[{"url":"https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=800&q=80","alt":"Mantita de Apego Osito"}]', 5.0, '30 cm x 30 cm de mantita', '150 gramos', '100% Algodón Peinado Antialérgico', 'Vellón siliconado hipoalergénico', 'Bordado total sin piezas sueltas', 'Caja regalo + Tarjeta dedicatoria', NULL),

-- 4. Kits de Crochet & Aprendizaje
('kit-crochet-principiante', 'Kit de Crochet Artesanal Maraña', 'MAR-KIT-001', 'kits', 'Kits & Aprendizaje', 34900, 39900, 'KIT COMPLETO', 8.75, 5.0, 48, 12, 20, 1, 'in-stock', 'Stock Disponible (12)', 'Aprende a tejer tu propia arañita cósmica paso a paso', 'El kit oficial de iniciación de Maraña Crochet. Contiene 3 ovillos de algodón peinado hipoalergénico en tonos amatista y lavanda, ganchillo ergonómico de 2.5 mm, marcadores de puntada en bronce artesanal, vellón siliconado de alta densidad, aguja lanera y folleto de adopción con acceso a videoguía exclusiva.', '[{"url":"https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80","alt":"Kit de Crochet Artesanal Maraña"}]', 1.5, '22 cm x 18 cm x 8 cm (caja kraft)', '320 gramos', '100% Algodón Peinado Colombiano + Aguja ergonómica', 'Vellón siliconado incluido para 1 amigurumi completo', 'Materiales seguros para mayores de 8 años', '3 Ovillos + Ganchillo 2.5mm + Marcadores + Certificado + Guía Paso a Paso', NULL),

('kit-ranita-cosmica', 'Kit Teje tu Ranita Cósmica', 'MAR-KIT-002', 'kits', 'Kits & Aprendizaje', 32900, 36900, 'EDICIÓN ESPECIAL', 8.25, 4.9, 31, 8, 15, 0, 'in-stock', 'Stock Disponible (8)', 'Todo lo necesario para tejer a la Ranita de la Sabana', 'Incluye ovillos verde esmeralda y morado amatista, ojos con traba reforzada, patrón ilustrado exclusivo con diagrama de puntos y ramillete aromático de lavanda.', '[{"url":"https://images.unsplash.com/photo-1584992236310-6edddc08acff?auto=format&fit=crop&w=800&q=80","alt":"Kit Teje tu Ranita Cósmica"}]', 2.0, '20 cm x 16 cm x 7 cm', '290 gramos', 'Algodón peinado mercerizado', 'Vellón siliconado hipoalergénico', 'Ojos con traba de seguridad infantil', 'Hilados + Patrón exclusivo + Aguja ergonómica + Ojos de seguridad', NULL),

('kit-mini-bosque', 'Kit Mini Criaturas del Bosque', 'MAR-KIT-003', 'kits', 'Kits & Aprendizaje', 28900, 33500, 'PRINCIPIANTES', 7.25, 4.8, 22, 10, 15, 0, 'in-stock', 'Stock Disponible (10)', 'Pack de 3 honguitos y bellotas mágicas para iniciarse', 'Ideal para quienes quieren empezar con proyectos rápidos y encantadores. Incluye mini ovillos de 5 tonalidades terrosas y pasteles, argollas para llavero y relleno.', '[{"url":"https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80","alt":"Kit Mini Criaturas del Bosque"}]', 1.0, '18 cm x 14 cm x 6 cm', '220 gramos', 'Algodón 100% natural', 'Vellón hipoalergénico', 'Bordado manual reforzado', '5 Mini ovillos + 3 Argollas llavero + Ganchillo + Guía', NULL)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `price`=VALUES(`price`), `stock`=VALUES(`stock`);

-- =====================================================================
-- DATOS SEMILLA: CLIENTES (Contraseña por defecto: Maraña2026!)
-- =====================================================================

INSERT INTO `customers`
(`id`, `name`, `email`, `phone`, `address`, `city`, `department`, `password`, `avatar`, `theme_preference`)
VALUES
(1, 'Valentina Castro Pizarro', 'valentina.castro@gmail.com', '+57 312 456 7890', 'Carrera 15 # 85-30 Apto 402', 'Bogotá', 'Bogotá D.C.', '$2y$12$N3K7i736E9f3w419u6J29e88W/pZJkGvWzP/6aZ12QoW7oE0kYhce', 'preset:aranita', 'light'),
(2, 'Camila Morales', 'camila.morales@crochet.com', '+57 315 889 1234', 'Calle 21 # 5-40 Barrio Quirinal', 'Neiva', 'Huila', '$2y$12$N3K7i736E9f3w419u6J29e88W/pZJkGvWzP/6aZ12QoW7oE0kYhce', 'preset:rana', 'dark')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- =====================================================================
-- DATOS SEMILLA: PEDIDO INICIAL (Ejemplo con Comprobante Oficial)
-- =====================================================================

INSERT INTO `orders`
(`id`, `date`, `customerId`, `customerName`, `customerEmail`, `customerPhone`, `customerRut`, `address`, `shippingMethod`, `shippingCost`, `items`, `subtotal`, `total`, `status`, `paymentMethod`, `bank`, `accountType`, `transferDetails`, `giftDedication`, `auditLog`)
VALUES
('ORD-2026-884', '28/10/2026 14:15', 1, 'Valentina Castro Pizarro', 'valentina.castro@gmail.com', '+57 312 456 7890', '1.075.289.412', '{"region":"Bogotá D.C.","comuna":"Bogotá (Chapinero)","street":"Carrera 15 # 85-30","dept":"Apto 402","instructions":"Dejar en portería 24h"}', 'nacional-colombia', 14500, '[{"id":"lulu-aranita","name":"Lulú la Arañita Cósmica","price":24990,"quantity":1,"image":"https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=800&q=80"},{"id":"murcielago-lavanda","name":"Murciélago Lavanda Nocturno","price":24990,"quantity":1,"image":"https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=800&q=80"}]', 49980, 64480, 'aprobado', 'transferencia', 'Bancolombia', 'Cuenta de Ahorros', '{"folio":"94827103","tefCode":"TRF-94827103","transferredAmount":64480,"voucherFilename":"comprobante_bancolombia_64480.pdf"}', 'Para Sofía en su cumpleaños con todo el amor cósmico... ¡Cuida mucho a Lulú!', '[{"time":"14:15","note":"Pedido registrado con transferencia Bancolombia.","type":"info"},{"time":"14:28","note":"Comprobante verificado. Pago aprobado por Tallerista Sofía Morales.","type":"success"}]')
ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);

-- =====================================================================
-- DATOS SEMILLA: RESEÑAS VERIFICADAS
-- =====================================================================

INSERT INTO `reviews`
(`id`, `customerInitials`, `customerName`, `emailMasked`, `orderId`, `sku`, `productName`, `productIcon`, `rating`, `date`, `comment`, `verified`, `status`, `photos`, `workshopReply`, `helpfulCount`)
VALUES
('rev-001', 'VC', 'Valentina Castro', 'vc****@gmail.com', 'ORD-2026-884', 'MAR-ARAN-004', 'Lulú la Arañita Cósmica', 'star', 5, 'Hace 2 horas', '¡Es aún más hermosa en persona! Los ojitos bordados con forma de estrella tienen una dedicación increíble. Llegó en su cajita aromática de lavanda y el certificado de adopción caligrafiado con mi dedicatoria quedó perfecto.', 1, 'approved', '[{"url":"https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=800&q=80","alt":"Lulú en el escritorio"}]', '{"author":"Sofía Morales","role":"Tallerista Principal","date":"Hace 1 hora","text":"¡Gracias Valentina! Tejer a Lulú con sus mejillitas rosadas fue un placer. ¡Que llene de magia el hogar!"}', 18),
('rev-002', 'MR', 'Matías Rojas', 'mr****@outlook.com', 'ORD-2026-883', 'MAR-POLI-012', 'Polilla Lunar de Algodón', 'star', 5, 'Hace 3 días', 'Fue un regalo para mi novia fanática del crochet y los amigurumis. Quedó fascinada con el empaque aromático de lavanda y el certificado con el nombre de la artesana.', 1, 'approved', NULL, '{"author":"Sofía Morales","role":"Tallerista Principal","date":"Hace 2 días","text":"¡Muchas gracias Matías por apoyar el talento y la artesanía hecha a mano en el Huila! 💜✨"}', 24)
ON DUPLICATE KEY UPDATE `comment`=VALUES(`comment`);
