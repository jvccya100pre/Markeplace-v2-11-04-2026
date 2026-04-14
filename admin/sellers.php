<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/SellersController.php';

$controller = new SellersController();
$controller->handle();
