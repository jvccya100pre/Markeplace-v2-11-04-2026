<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/ProductsController.php';

$controller = new ProductsController();
$controller->handle();
