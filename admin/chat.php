<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/ChatController.php';

$controller = new ChatController();
$controller->handle();
