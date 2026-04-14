<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/OrderPdfController.php';

$controller = new OrderPdfController();
$controller->handle();
