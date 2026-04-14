# Demo Public Marketplace

Este repositorio contiene un sitio web de e-commerce simple con panel administrativo y tienda pública. El sistema gestiona productos, vendedores, carrito, checkout y funcionalidades básicas de administración.

---

## Estructura principal

- `index.php`: Punto de entrada público. Carga `includes/bootstrap.php`, el `Router` y los controladores de la tienda, carrito, checkout, PDF y contacto.
- `config.php`: Configuración global y funciones compartidas. Define conexión a base de datos, funciones de sesión, formato de precios, manejo de settings y helpers.
- `includes/bootstrap.php`: Carga la configuración y registra visitas. Controla acciones globales como selección de moneda y seguimiento de páginas.
- `includes/header.php`: Cabecera pública con navbar, logo, menú hamburguesa y selector de moneda.
- `includes/footer.php`: Pie de página público con datos de contacto y enlaces sociales.

---

## Panel administrativo

- `admin/index.php`: Panel de administración principal.
- `admin/login.php`: Login administrativo.
- `admin/products.php`: Gestión de productos (crear, editar, eliminar).
- `admin/categories.php`: Gestión de categorías.
- `admin/sellers.php`: Gestión de vendedores.
- `admin/orders.php`: Visualización de pedidos y carros.
- `admin/chat.php`: Chat interno.
- `admin/testimonials.php`: Gestión de testimonios.
- `admin/order_pdf.php`: Exportación de pedidos en PDF.
- `admin/app/Controllers/`: Controladores del backend admin.
- `admin/app/Models/`: Modelos para consultas y lógica de datos.
- `admin/app/Views/`: Vistas HTML de la interfaz administrativa.
- `admin/assets/styles.css`: Estilos específicos del admin.
- `admin/assets/app.js`: JavaScript de la administración.

---

## Funcionalidades principales

### Usuario y autenticación
- `auth_user()` y `auth_admin()` en `config.php` identifican usuarios públicos y administradores.
- `require_admin_login()` protege rutas del panel de admin.
- `check_session_timeout()` cierra sesión tras inactividad de 10 minutos.

### Carrito y checkout
- `CartController.php`: Controla el carrito, eliminación de items y cálculo de totales.
- `CheckoutController.php`: Crea pedidos y procesa la compra desde el carrito.
- `cart_session_items()` guarda el carrito en `$_SESSION`.

### Productos y catálogo
- `ProductModel.php`: Búsqueda por código interno y creación de productos.
- `admin/products.php`: Administra el catálogo desde el panel.
- `app/Views/home/index.php`: Lista pública de productos y modal de detalle.

### Precios y moneda
- Se agregó selector en el navbar para `USD` y `VES`.
- `config.php` contiene:
  - `get_current_currency()`: Devuelve moneda seleccionada en sesión.
  - `get_exchange_rate()`: Devuelve la tasa USD/VES de la tabla `exchange_rates`.
  - `format_price()`: Muestra precios en la moneda correcta.
- El valor del día se guarda y actualiza desde el dashboard administrativo.

### PDF y exportaciones
- `CatalogController.php`: Genera catálogo en PDF.
- Se agregó método `download2()` para exportar nuevo formato de PDF con precios en USD.
- `admin/DashboardController.php`: Genera backup SQL de la base de datos.

### Backup y mantenimiento
- Desde el dashboard admin se puede:
  - `Generar Backup`: descarga un dump SQL de todas las tablas.
  - `Limpiar Productos`: elimina todos los registros de `products`.

---

## Base de datos

### Tablas importantes
- `demo_markeplacev1_products`: Productos con columnas de precio, stock, galería y delivery.
- `demo_markeplacev1_orders`: Pedidos con posible `seller_id` y `delivery_method`.
- `demo_markeplacev1_sellers`: Vendedores.
- `demo_markeplacev1_testimonials`: Testimonios de clientes.
- `demo_markeplacev1_exchange_rates`: Tasa USD a VES por fecha.
- `demo_markeplacev1_settings`: Configuración general del sitio.

### Scripts SQL
- `necesarios/schema-update.sql`: Migra la base de datos para nuevas tablas y columnas.
- `necesarios/faltante.sql`: Script para crear tablas faltantes y agregar columnas si no existen.

---

## Uso del sistema

### Tienda pública
- `index.php`: Página principal con catálogo y filtros.
- `testimonial.php`: Formulario para enviar testimonios.
- `cart.php`: Muestra el carrito.
- `checkout.php`: Finaliza el pedido.
- `generate_catalog_pdf.php`: Descarga catálogo en PDF.

### Dashboard administrativo
- Inicia sesión en `/admin/login.php`.
- Administra productos, categorías, vendedores, pedidos y testimonios.
- Ajusta la tasa de cambio USD/VES desde el `dashboard`.
- Genera backup y limpia productos desde el dashboard.

### Moneda y precios
- El selector de moneda se encuentra en el navbar público.
- Al elegir `USD`, los precios se muestran convertidos a dólares usando la tasa almacenada.
- Al elegir `VES`, los precios se muestran en bolívares.

### Instagram y contacto
- El pie de página público ahora apunta a `http://instagram.com/tutiendaonlinelq`.
- El contacto de WhatsApp aparece en el footer y en las descargas de PDF.

---

## Comentarios en el sistema

### `config.php`
- `session_start()`: Inicializa la sesión PHP.
- `db()`: Crea la conexión PDO a MySQL.
- `table_name()`: Agrega prefijo de tabla configurado.
- `esc()`: Escapa valores para HTML.
- `route_url()`: Genera URLs internas según ruteo.
- `set_setting()` / `get_setting()`: Guardan y leen configuraciones desde la base.

### `includes/bootstrap.php`
- `require_once dirname(__DIR__) . '/config.php';`: Carga configuración global.
- `track_visit()`: Registra visitas por URL.
- Maneja la selección de moneda y redirige para evitar reenvío de formularios.

### `includes/header.php`
- Contiene el menú principal, el logo y el botón hamburguesa para móvil.
- Incluye el selector de moneda para la tienda pública.

### `app/Controllers/*.php`
- Cada controlador maneja una sección específica de la aplicación.
- `HomeController`: Lógica de listado, filtrado y carrito.
- `CartController`: Gestión del carrito.
- `CheckoutController`: Creación de pedidos.
- `CatalogController`: Generación de PDF.

### `admin/app/Controllers/*.php`
- `DashboardController`: Estadísticas, settings, tasa de cambio, backup y limpieza.
- `ProductsController`, `CategoriesController`, `SellersController`, `OrdersController`, `TestimonialsController`: CRUD para cada entidad.

---

## Notas finales

- Asegúrate de ejecutar el script SQL correcto antes de usar el sistema en producción.
- Si agregas o modificas columnas, actualiza también los modelos y las vistas correspondientes.
- Esta documentación combina la guía de uso existente y los cambios recientes agregados al sistema.

---

## Ubicación de archivos importantes

- `includes/`: Plantillas de header/footer y bootstrap global.
- `assets/`: Estilos y JavaScript públicos.
- `admin/assets/`: Estilos y JS del panel admin.
- `admin/app/Views/`: Vistas admin.
- `app/Views/`: Vistas públicas.
- `admin/app/Models/`: Lógica de datos admin.
- `app/Models/`: Lógica de datos pública.
- `necesarios/`: Documentación adicional y scripts SQL.
