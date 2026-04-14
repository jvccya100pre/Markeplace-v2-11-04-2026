<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/OrdersController.php';

$controller = new OrdersController();
$controller->handle();
