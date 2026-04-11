<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';

$controller = new DashboardController();
$controller->handle();
