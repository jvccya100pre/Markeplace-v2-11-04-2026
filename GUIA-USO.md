# Guía de uso de la aplicación web

## 1. Iniciar sesión

- Ingresa con tu correo y contraseña en `/admin/login.php`.
- Si está configurado, el formulario usa Google reCAPTCHA para prevenir bots.

## 2. Administrar productos

- Ve a `/admin/products.php`.
- Aquí puedes crear, editar o eliminar productos.
- Ahora el formulario permite subir imágenes y múltiples archivos para la galería.
- También puedes establecer:
  - precio al detal
  - precio al mayor
  - mostrar o ocultar cada tipo de precio
  - permitir ventas con stock negativo
  - modo de imagen: `single` o `carousel`

## 3. Administrar vendedores

- Ve a `/admin/sellers.php`.
- Registra vendedores con nombre, correo y usuario.
- El sistema genera un enlace único para cada vendedor.
- Cuando un comprador entra con ese enlace (`/?seller=ID`), se guarda la referencia.

## 4. Compras y carrito

- En la tienda pública, haz clic en la imagen del producto para abrir el modal.
- Dentro del modal puedes seleccionar cantidad y agregar al carrito.
- El zoom en la imagen se mueve con el mouse para mejorar la experiencia.

## 5. Ventas en stock negativo

- Si un producto tiene la opción activada, el sistema permite agregar más unidades que las disponibles.
- Si el stock baja a número negativo, se notifica al administrador por correo si `company_email` está configurado.

## 6. Icono de la pestaña y navbar

- La aplicación usa `/img/company/LogoTuTiendaOnline3.png` como favicon en la tienda pública y en el panel administrativo.
- El mismo icono aparece en la barra superior izquierda de ambas interfaces.

## 7. Exportar la base de datos

- Usa `schema-update.sql` para añadir las columnas y tablas necesarias a tu base de datos.
- Esta migración incluye:
  - precios detall/wholesale
  - control de stock negativo
  - modo de galería
  - tabla de vendedores
  - relacionar pedidos con vendedores

## 8. Notas adicionales

- Si implementas la migración, revisa que los campos nuevos en `products` estén disponibles.
- Para el enlace de vendedor, usa `/?seller=<ID>` en el sitio público.
- Si necesitas crear un respaldo antes de actualizar la base de datos, exporta tu esquema actual con `mysqldump`.
