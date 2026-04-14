<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/LoginController.php';

$controller = new LoginController();
$controller->handle();
