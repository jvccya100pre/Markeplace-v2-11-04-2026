<?php
require_once dirname(__DIR__) . '/Models/CategoryModel.php';

class CategoriesController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new CategoryModel();

        if (is_post()) {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            if ($name !== '') {
                $model->create($name);
            }
        }

        $this->render('categories', array('rows' => $model->allDesc()));
    }
}
