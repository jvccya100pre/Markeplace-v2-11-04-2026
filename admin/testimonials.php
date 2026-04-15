<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/TestimonialsController.php';

$controller = new TestimonialsController();
$controller->handle();