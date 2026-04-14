<?php
require_once dirname(__DIR__) . '/config.php';
track_visit(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/panel');
