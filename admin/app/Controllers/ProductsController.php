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

        if (is_post() && isset($_POST['add_product'])) {
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
}
