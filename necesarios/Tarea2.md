Agregar Impotar inventario products  en formato ".CSV" desde "Panel Admin".

los encabesados Originales del archivo ".csv" son 3 que son a continuacion:
1 Código
2 Descripción
3 ves

Las columnas llamada "name" y "description" en mysql comparten la misma columna llamada "Descripción" del archivo ".csv".

Separador de columnnas ";" (punto y coma)

La insercion seria exactamente con esta consulta:

INSERT INTO `createso_datosVPS`.`demo_markeplacev1_products` 
(`category_id`, `name`, `description`, `internal_code`, `stock`, `color`, `price`, `image_path`, `created_at`, `price_retail`, `price_wholesale`, `show_retail`, `show_wholesale`, `allow_negative_stock`, `gallery_mode`) VALUES 
(1, '@Descripción', '@Descripción', '@Código', 456, 0, '@ves', '', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'), 0,	0,	1,	1,	0,	'single');

AL importar PHP debe Cambiar los encabezados del archivo ".csv" en los pasos (a-, b-, c-, d-).
   a- Fila 1 columna 1a  donde dice "Código" se insert en "internal_code".
   b- Fila 1 columna 2a  donde dice "Descripción" se insert en "name".
   c- Fila 1 columna 2a  donde dice "Descripción" se insert en "description".
   d- Fila 1 columna 3a  donde dice "ves" se insert en "price".



