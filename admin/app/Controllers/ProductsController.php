<?php
require_once dirname(__DIR__) . '/Models/ProductModel.php';
require_once dirname(__DIR__) . '/Models/CategoryModel.php';

class ProductsController extends BaseController
{
    public function handle()
    {
        require_admin_login();

        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();

        $message = '';
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
        $editingProduct = $editId > 0 ? $productModel->findById($editId) : null;

        if (is_post() && isset($_POST['add_product'])) {
            $_POST = $this->normalizeProductPost($_POST);
            $code = isset($_POST['internal_code']) ? trim($_POST['internal_code']) : '';
            if ($productModel->findByCode($code)) {
                $message = 'El codigo de producto ya existe. Usa uno diferente.';
            } else {
                $productModel->create($_POST);
                $message = 'Producto creado correctamente.';
            }
        }

        if (is_post() && isset($_POST['update_product'])) {
            $id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $code = isset($_POST['internal_code']) ? trim($_POST['internal_code']) : '';
            $_POST = $this->normalizeProductPost($_POST, $editingProduct);

            if ($productModel->findOtherByCode($code, $id)) {
                $message = 'No se actualizo: ya existe otro producto con ese codigo.';
                $editId = $id;
            } else {
                $productModel->update($id, $_POST);
                $message = 'Producto actualizado correctamente.';
                $editId = $id;
            }
        }

        if (is_post() && isset($_POST['delete_product'])) {
            $id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            if ($productModel->hasOrderItems($id)) {
                $message = 'No se puede eliminar: el producto tiene pedidos asociados.';
            } else {
                $productModel->delete($id);
                $message = 'Producto eliminado correctamente.';
                if ($editId === $id) {
                    $editId = 0;
                }
            }
        }

        if (is_post() && isset($_POST['import_products'])) {
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                $importResult = $this->importProductsFromCsv($_FILES['csv_file']['tmp_name'], $productModel);
                if ($importResult['imported'] === 0 && $importResult['skipped'] === 0) {
                    $message = 'No se importaron productos. Verifique el archivo CSV.';
                } else {
                    $message = sprintf('Importados %d productos. %d ya existían y no se importaron.', $importResult['imported'], $importResult['skipped']);
                }
            } else {
                $message = 'No se recibió el archivo CSV o se produjo un error en la carga.';
            }
        }

        $cats = $categoryModel->activeByName();
        $editingProduct = $editId > 0 ? $productModel->findById($editId) : null;

        $formData = array(
            'id' => $editingProduct ? (int)$editingProduct['id'] : 0,
            'category_id' => $editingProduct ? (int)$editingProduct['category_id'] : (count($cats) ? (int)$cats[0]['id'] : 0),
            'name' => $editingProduct ? $editingProduct['name'] : '',
            'description' => $editingProduct ? $editingProduct['description'] : '',
            'internal_code' => $editingProduct ? $editingProduct['internal_code'] : '',
            'stock' => $editingProduct ? (int)$editingProduct['stock'] : 0,
            'color' => $editingProduct ? $editingProduct['color'] : 'Surtido',
            'price' => $editingProduct ? $editingProduct['price'] : '0.00',
            'price_retail' => $editingProduct && isset($editingProduct['price_retail']) ? $editingProduct['price_retail'] : ($editingProduct ? $editingProduct['price'] : '0.00'),
            'price_wholesale' => $editingProduct && isset($editingProduct['price_wholesale']) ? $editingProduct['price_wholesale'] : '0.00',
            'show_retail' => $editingProduct && isset($editingProduct['show_retail']) ? (int)$editingProduct['show_retail'] : 1,
            'show_wholesale' => $editingProduct && isset($editingProduct['show_wholesale']) ? (int)$editingProduct['show_wholesale'] : 0,
            'allow_negative_stock' => $editingProduct && isset($editingProduct['allow_negative_stock']) ? (int)$editingProduct['allow_negative_stock'] : 0,
            'gallery_mode' => $editingProduct && isset($editingProduct['gallery_mode']) ? $editingProduct['gallery_mode'] : 'single',
            'image_path' => $editingProduct ? $editingProduct['image_path'] : '/logo.jpg'
        );

        $this->render('products', array(
            'message' => $message,
            'search' => $search,
            'cats' => $cats,
            'formData' => $formData,
            'rows' => $productModel->searchWithCategory($search),
        ));
    }

    private function normalizeProductPost($data, $editingProduct = null)
    {
        $data['show_retail'] = isset($data['show_retail']) ? 1 : 0;
        $data['show_wholesale'] = isset($data['show_wholesale']) ? 1 : 0;
        $data['allow_negative_stock'] = isset($data['allow_negative_stock']) ? 1 : 0;
        $data['gallery_mode'] = isset($data['gallery_mode']) && $data['gallery_mode'] === 'carousel' ? 'carousel' : 'single';
        $currentPath = isset($data['current_image_path']) ? trim($data['current_image_path']) : '';
        if ($editingProduct && isset($editingProduct['image_path'])) {
            $currentPath = trim($editingProduct['image_path']);
        }
        $data['image_path'] = $this->storeUploadedImages($currentPath);
        return $data;
    }

    private function storeUploadedImages($currentPath)
    {
        $uploadDir = dirname(__DIR__, 3) . '/img';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $savedFiles = array();
        $allowed = array('jpg', 'jpeg', 'png');

        if (isset($_FILES['image_file']) && is_uploaded_file($_FILES['image_file']['tmp_name'])) {
            $fileName = basename($_FILES['image_file']['name']);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($extension, $allowed, true)) {
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
                $targetName = uniqid('img_', true) . '_' . $safeName . '.' . $extension;
                $targetPath = $uploadDir . '/' . $targetName;
                if (@move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
                    $savedFiles[] = '/img/' . $targetName;
                }
            }
        }

        if (isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
            foreach ($_FILES['gallery_files']['name'] as $index => $name) {
                if (empty($name) || !is_uploaded_file($_FILES['gallery_files']['tmp_name'][$index])) {
                    continue;
                }
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowed, true)) {
                    continue;
                }
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($name, PATHINFO_FILENAME));
                $targetName = uniqid('img_', true) . '_' . $safeName . '.' . $extension;
                $targetPath = $uploadDir . '/' . $targetName;
                if (@move_uploaded_file($_FILES['gallery_files']['tmp_name'][$index], $targetPath)) {
                    $savedFiles[] = '/img/' . $targetName;
                }
            }
        }

        if (count($savedFiles) > 0) {
            return implode('|', $savedFiles);
        }

        return $currentPath !== '' ? $currentPath : '/logo.jpg';
    }

    private function importProductsFromCsv($filePath, $productModel)
    {
        $logFile = dirname(__DIR__, 2) . '/error_log';
        error_log("[" . date('Y-m-d H:i:s') . "] Inicio de importación CSV: $filePath\n", 3, $logFile);

        $result = array('imported' => 0, 'skipped' => 0);

        if (!is_readable($filePath)) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error: Archivo no legible: $filePath\n", 3, $logFile);
            return $result;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error: No se pudo abrir el archivo: $filePath\n", 3, $logFile);
            return $result;
        }

        $header = fgetcsv($handle, 0, ';');
        if ($header === false) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error: No se pudo leer el encabezado\n", 3, $logFile);
            fclose($handle);
            return $result;
        }

        // Convertir de ISO-8859-1 a UTF-8 si es necesario
        $header = array_map(function($h) {
            return mb_convert_encoding($h, 'UTF-8', 'ISO-8859-1');
        }, $header);

        error_log("[" . date('Y-m-d H:i:s') . "] Encabezado leído: " . implode(';', $header) . "\n", 3, $logFile);

        $header = array_map(array($this, 'normalizeCsvHeaderValue'), $header);

        $positions = array(
            'internal_code' => $this->findCsvHeaderIndex($header, array('codigo', 'internal_code')),
            'description' => $this->findCsvHeaderIndex($header, array('descripcion', 'description', 'name')),
            'price' => $this->findCsvHeaderIndex($header, array('ves', 'price', 'precio')),
        );

        if ($positions['internal_code'] === false || $positions['description'] === false || $positions['price'] === false) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error: Encabezados no encontrados. Posiciones: " . print_r($positions, true) . "\n", 3, $logFile);
            fclose($handle);
            return $result;
        }

        error_log("[" . date('Y-m-d H:i:s') . "] Posiciones mapeadas: " . print_r($positions, true) . "\n", 3, $logFile);

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Convertir fila a UTF-8
            $row = array_map(function($cell) {
                return mb_convert_encoding($cell, 'UTF-8', 'ISO-8859-1');
            }, $row);

            $row = array_map('trim', $row);
            if (count(array_filter($row, 'strlen')) === 0) {
                continue;
            }

            $internalCode = isset($row[$positions['internal_code']]) ? $row[$positions['internal_code']] : '';
            if ($internalCode === '') {
                error_log("[" . date('Y-m-d H:i:s') . "] Fila omitida: Código vacío\n", 3, $logFile);
                continue;
            }

            if ($productModel->findByCode($internalCode)) {
                error_log("[" . date('Y-m-d H:i:s') . "] Fila omitida: Código duplicado: $internalCode\n", 3, $logFile);
                $result['skipped']++;
                continue;
            }

            $descriptionValue = isset($row[$positions['description']]) ? $row[$positions['description']] : '';
            $priceValue = isset($row[$positions['price']]) ? $row[$positions['price']] : '0';

            error_log("[" . date('Y-m-d H:i:s') . "] Procesando fila: Código=$internalCode, Descripción=$descriptionValue, Precio=$priceValue (como texto)\n", 3, $logFile);

            try {
                $productModel->create(array(
                    'category_id' => 1,
                    'name' => $descriptionValue,
                    'description' => $descriptionValue,
                    'internal_code' => $internalCode,
                    'stock' => 0,
                    'color' => '0',
                    'price' => $priceValue,
                    'image_path' => '/logo.jpg',
                ));
                $result['imported']++;
                error_log("[" . date('Y-m-d H:i:s') . "] Producto importado: $internalCode\n", 3, $logFile);
            } catch (Exception $e) {
                error_log("[" . date('Y-m-d H:i:s') . "] Error al crear producto $internalCode: " . $e->getMessage() . "\n", 3, $logFile);
            }
        }

        fclose($handle);
        error_log("[" . date('Y-m-d H:i:s') . "] Fin de importación: Importados=" . $result['imported'] . ", Omitidos=" . $result['skipped'] . "\n", 3, $logFile);
        return $result;
    }

    private function normalizeCsvHeaderValue($value)
    {
        $value = trim($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'), array('a', 'e', 'i', 'o', 'u', 'n', 'u'), $value);
        return $value;
    }

    private function findCsvHeaderIndex($header, array $candidates)
    {
        foreach ($header as $index => $name) {
            if (in_array($name, $candidates, true)) {
                return $index;
            }
        }

        return false;
    }
}
