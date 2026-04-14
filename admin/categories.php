<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/CategoriesController.php';

$controller = new CategoriesController();
$controller->handle();
