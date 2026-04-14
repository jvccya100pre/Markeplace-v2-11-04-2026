<?php
require_once dirname(__DIR__) . '/config.php';

require_once __DIR__ . '/Core/BaseController.php';

track_visit(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/panel');
