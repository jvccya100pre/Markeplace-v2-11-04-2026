<?php
require_once dirname(__DIR__) . '/Models/CategoryModel.php';

class CategoriesController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new CategoryModel();

        if (is_post()) {
            $action = isset($_POST['action']) ? $_POST['action'] : '';
            if ($action === 'create') {
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                if ($name !== '') {
                    $model->create($name);
                }
            } elseif ($action === 'update') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                if ($id > 0 && $name !== '') {
                    $model->update($id, $name);
                }
            } elseif ($action === 'delete') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                if ($id > 0) {
                    $model->delete($id);
                }
            } elseif ($action === 'toggle_active') {
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                if ($id > 0) {
                    $model->toggleActive($id);
                }
            }
        }

        $this->render('categories', array('rows' => $model->allDesc(), 'edit_id' => isset($_GET['edit']) ? (int)$_GET['edit'] : 0));
    }
}
