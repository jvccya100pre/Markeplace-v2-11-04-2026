<?php
require_once 'app/bootstrap.php';

$controller = new TestimonialsController($db);

$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'approve':
        $controller->approve($id);
        break;
    case 'reject':
        $controller->reject($id);
        break;
    default:
        $controller->index();
        break;
}
?>