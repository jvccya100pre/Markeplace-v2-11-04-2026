<?php
require_once dirname(__DIR__) . '/Models/OrderModel.php';

class OrdersController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new OrderModel();
        $this->render('orders', array('rows' => $model->latestWithUser(250)));
    }
}
