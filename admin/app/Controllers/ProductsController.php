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
}
