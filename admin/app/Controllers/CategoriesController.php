<?php
require_once dirname(__DIR__) . '/Models/CategoryModel.php';

class CategoriesController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new CategoryModel();

        $message = '';
        $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
        $editingCategory = $editId > 0 ? $model->findById($editId) : null;

        if (is_post()) {
            if (isset($_POST['add_category'])) {
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                if ($name !== '') {
                    $model->create($name);
                    $message = 'Categoría agregada correctamente.';
                } else {
                    $message = 'El nombre de categoría no puede estar vacío.';
                }
            }

            if (isset($_POST['update_category'])) {
                $id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                if ($id > 0 && $name !== '') {
                    $model->update($id, $name);
                    $message = 'Categoría actualizada correctamente.';
                    $editId = 0;
                    $editingCategory = null;
                } else {
                    $message = 'Nombre de categoría inválido.';
                }
            }

            if (isset($_POST['delete_category'])) {
                $id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
                if ($id > 0) {
                    $model->delete($id);
                    $message = 'Categoría eliminada correctamente.';
                    if ($editId === $id) {
                        $editId = 0;
                        $editingCategory = null;
                    }
                }
            }
        }

        $this->render('categories', array(
            'rows' => $model->allDesc(),
            'message' => $message,
            'editingCategory' => $editingCategory,
            'editId' => $editId,
        ));
    }
}
