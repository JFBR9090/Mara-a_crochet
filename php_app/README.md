# Maraña Crochet - Migración a PHP Nativo & Sistema de Diseño Minimalista

Este proyecto es la migración completa a **PHP 8+** del sitio de comercio artesanal **Maraña Crochet**, implementando una arquitectura limpia con plantillas reutilizables, persistencia en **MySQL mediante PDO con consultas preparadas**, y el sistema de diseño minimalista **"Artisanal Violet Craft"**.

---

## 🎨 Sistema de Diseño: "Artisanal Violet Craft"

Basado estrictamente en la paleta de colores indicada, con tonalidades generadas para estados `hover`, `focus`, bordes y fondos sutiles:

- **Morado Primario**: `#7B2CBF`
  - Hover: `#651FA4` | Oscuro: `#4D1282` | Claro: `#9D4EDD` | Fondo Sutil: `#F6EDFC` | Borde: `#E8D4F7`
- **Morado Secundario Claro**: `#C77DFF`
  - Hover: `#B55EFF` | Claro: `#E0AAFF` | Fondo Sutil: `#FAF5FF`
- **Dorado Terciario (Acento Puntual)**: `#FFB703`
  - Utilizado **únicamente** en insignias destacadas, estrellas de calificación y detalles clave para preservar la sobriedad minimalista.
  - Hover: `#FB8500` | Fondo Sutil: `#FFFDF0`
- **Gris Oscuro Neutro (Texto y Fondos)**: `#1D1128`
  - Texto principal: `#1D1128` | Medio: `#4A3E56` | Suave: `#7E738B`
  - Fondo de página: `#FAF8FC` | Fondo de tarjetas: `#FFFFFF` | Bordes finos: `#E8E2EE`

### Componentes Implementados según el Sistema
- **Botones**:
  - `btn-primary`: Relleno morado `#7B2CBF`, texto blanco.
  - `btn-secondary`: Relleno morado claro `#C77DFF`, texto blanco.
  - `btn-inverted`: Fondo oscuro `#1D1128`, texto morado `#C77DFF`.
  - `btn-outlined`: Fondo blanco con borde morado fino `1.5px solid #7B2CBF`.
- **Barra de Búsqueda**: Campo con ícono de lupa SVG, bordes limpios y foco con halo violeta sutil.
- **Barra de Navegación**: Cabecera fija con íconos de Inicio, Búsqueda, Usuario/Taller Admin, Favoritos y Carrito con contador interactivo.
- **Barras de Progreso**: Indicadores de stock en tiempo real (`4 de 15 unidades`), progreso del checkout en 3 pasos y timeline de pedidos.
- **Botones de Etiqueta con Ícono de Edición**: `.tag-btn-edit` con ícono SVG de lápiz para edición inline de stock y precios en el panel de inventario y pedidos.
- **Tipografía**: Importación oficial de Google Fonts: **"Plus Jakarta Sans"** para todos los títulos, textos y etiquetas.

---

## 📁 Estructura del Proyecto en PHP

```
php_app/
├── config/
│   └── db.php                  # Conexión PDO centralizada y auto-conexión a MySQL
├── includes/
│   ├── functions.php           # Utilidades (formateo CLP, helpers de sesión, carrito, favoritos)
│   ├── header.php              # Cabecera HTML común con Plus Jakarta Sans y CSS
│   ├── navbar.php              # Barra de navegación con iconos (Inicio, Búsqueda, Usuario/Admin, Carrito)
│   ├── footer.php              # Pie de página minimalista con enlaces e información artesanal
│   └── cart_drawer.php         # Cajón lateral interactivo (slide-over) de la canasta
├── assets/
│   ├── css/
│   │   └── style.css           # Hoja de estilos con variables de color y componentes minimalistas
│   └── js/
│       └── main.js             # Interactividad (Drawer, favoritos, AJAX, edición rápida)
├── index.php                   # Catálogo / Tienda (Hero, categorías, buscador, grilla de amigurumis)
├── product.php                 # Ficha de detalle (Galería de fotos, especificaciones, reseñas verificadas)
├── cart.php                    # Vista completa de la Canasta de Compras
├── checkout.php                # Proceso de compra con transferencia electrónica bancaria (TEF)
├── order-success.php           # Pantalla de confirmación con recibo y seguimiento
├── admin/
│   ├── index.php               # Redirección a pedidos
│   ├── orders.php              # Backoffice: Gestión de Pedidos, validación TEF y auditoría
│   ├── inventory.php           # Backoffice: Control de inventario de amigurumis y materias primas
│   └── reviews.php             # Backoffice: Moderación de opiniones y respuestas del taller
├── api/
│   ├── cors.php                # Cabeceras CORS y JSON
│   ├── cart.php                # Endpoint AJAX para agregar/actualizar/remover del carrito
│   ├── products.php            # Endpoint REST para productos (GET, PUT)
│   ├── orders.php              # Endpoint REST para pedidos (GET, POST, PUT)
│   └── reviews.php             # Endpoint REST para reseñas (GET, POST, PUT)
└── database/
    └── schema.sql              # Esquema SQL para creación de base de datos y tablas
```

---

## ⚙️ Cómo Ejecutar el Proyecto en XAMPP

1. **Asegúrate de que Apache y MySQL estén iniciados** en el XAMPP Control Panel.
2. El proyecto ya ha sido desplegado directamente en `C:\xampp\htdocs\marana`.
3. Abre tu navegador favorito y accede a:
   - **Tienda**: [http://localhost/marana](http://localhost/marana)
   - **Ficha de Producto**: [http://localhost/marana/product.php?id=lulu-aranita](http://localhost/marana/product.php?id=lulu-aranita)
   - **Panel del Taller (Admin)**: [http://localhost/marana/admin/orders.php](http://localhost/marana/admin/orders.php)
   - **Gestor phpMyAdmin**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin) (Base de datos: `marana_crochet`)

---

## 📝 Informe de Migración y Decisiones de Diseño

1. **Gestión del Carrito y Favoritos**:
   - En el original (React), el carrito y los favoritos estaban en memoria del estado de React (`useState`), perdiéndose al recargar la página.
   - En esta versión PHP se migraron a **Sesiones Nativas de PHP (`$_SESSION['cart']`, `$_SESSION['favorites']`)** combinadas con un endpoint AJAX (`/api/cart.php`). Esto permite que los amigurumis permanezcan en el carrito y favoritos incluso al navegar entre páginas o recargar el navegador, manteniendo además la apertura instantánea del cajón lateral sin recarga brusca.

2. **Cálculo de Transacciones e Inventario**:
   - Al completar el checkout, la inserción del pedido en la tabla `orders` y el descuento de unidades en la tabla `products` se ejecutan dentro de una **transacción PDO (`beginTransaction()`, `commit()`)**, garantizando consistencia atómica y evitando sobreventas.

3. **Subida de Comprobantes de Transferencia**:
   - El formulario de checkout incluye un campo `<input type="file" name="voucher">` con simulación y registro del nombre del archivo en el campo JSON `transferDetails` de MySQL. En un entorno de producción final, el usuario puede habilitar una carpeta de almacenamiento local (por ejemplo `uploads/vouchers/`) con validación de tamaño y tipo MIME de archivo.

4. **Autenticación del Panel de Administración**:
   - El acceso al panel del taller (`/admin/orders.php`, `/admin/inventory.php`, `/admin/reviews.php`) está disponible de forma directa para facilitar la revisión y pruebas locales. Si se desea en el futuro, se puede añadir una pantalla de login con contraseña hash (`password_hash` / `password_verify`).
