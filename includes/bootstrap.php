<?php
require_once dirname(__DIR__) . '/config.php';

// Backward compatibility for environments with older config.php.
if (!function_exists('route_url')) {
	function route_url($route, $params = array())
	{
		$query = array_merge(array('r' => $route), $params);
		return app_url('/index.php?' . http_build_query($query));
	}
}

track_visit(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
