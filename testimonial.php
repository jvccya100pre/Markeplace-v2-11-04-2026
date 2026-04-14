<?php
require_once 'app/bootstrap.php';

$controller = new TestimonialController($db);

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    default:
        $controller->index();
        break;
}
?>