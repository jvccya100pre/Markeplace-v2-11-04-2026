<?php
require_once __DIR__ . '/includes/bootstrap.php';

$action = isset($_GET['action']) ? trim($_GET['action']) : 'index';
if ($action === 'create') {
    redirect_to(route_url('testimonial_create'));
}

redirect_to(route_url('testimonial'));