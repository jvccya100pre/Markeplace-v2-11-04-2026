# Instrucciones iniciales - tareasbyjvc.md

Este documento lista las mejoras prioritarias para la tienda web y describe el comportamiento esperado de cada una.

## Objetivo

Convertir los requerimientos en tareas claras para el desarrollo y la revisión.

## Tareas

1. Crear link de ventas para vendedores
   - El administrador registra vendedores con correo/usuario.
   - El vendedor comparte un enlace único con su ID.
   - Cuando un comprador compra desde ese enlace, el sistema envía un correo con los datos del comprador.
   - El dueño de tienda asigna el porcentaje de venta al vendedor y el correo informa lo ganado por la venta.

2. Permitir ventas en negativo
   - Si un producto está agotado en stock, el sistema debe notificar al administrador por correo.
   - El administrador decide si aumenta la cantidad o permite seguir vendiendo en stock negativo.

3. Subir imágenes desde el panel admin
   - Cambiar el campo de texto "Ruta imagen" por un uploader de archivos.
   - Aceptar formatos jpg, png y jpeg.
   - Guardar las imágenes en `/img/`.

4. Restringir carga de items por clientes
   - Verificar que los clientes no puedan subir sus propios productos para vender.

5. Doble precio por item (detal y mayor)
   - Cada producto puede tener precio al detal y precio al mayor visibles para compradores.
   - El administrador puede activar/desactivar ventas al mayor con un radio on/off.
   - El administrador puede activar/desactivar ventas al detal con un radio on/off.

6. Agregar cantidad desde la imagen
   - Al hacer clic en la imagen de un producto, permitir especificar la cantidad que se agregará al carrito.

7. Mejorar el zoom de imagen
   - Después de hacer clic en la imagen, el zoom debe seguir el movimiento del mouse.

8. Modo de galería de imágenes en admin
   - En el panel administrativo, el dueño puede seleccionar "modo carousel" o "modo una sola foto".
   - "Modo carousel" es para items con múltiples modelos o colores.
   - "Modo una sola foto" es para items con una sola imagen.
   - En la vista pública, el comportamiento debe cambiar según el selector del administrador.
   - Si está en modo carousel, mostrar flechas izquierda/derecha y un mini visor tipo WhatsApp.

9. Extraer el archivo `.sql`
   - Generar y/o documentar la exportación de la base de datos en `.sql`.

10. Crear guía de uso
   - Realizar una guía de uso para la aplicación web.

11. Agregar reCAPTCHA en admin/login.php
   - Incluir Google reCAPTCHA en el formulario de login de `/admin/login.php`.

12. Icono de pestaña y navbar
   - Colocar favicon usando `/img/company/LogoTuTiendaOnline3.png`.
   - Mostrar el mismo icono en el navbar superior izquierdo.

## Siguiente paso sugerido

1. Revisar la estructura actual de administración y producto.
2. Seleccionar la tarea más urgente para implementar primero.
3. Realizar un branch o copia de seguridad antes de cambios significativos.
