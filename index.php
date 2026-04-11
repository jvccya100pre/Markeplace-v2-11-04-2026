<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/app/Core/Router.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/CartController.php';
require_once __DIR__ . '/app/Controllers/CheckoutController.php';
require_once __DIR__ . '/app/Controllers/CatalogController.php';
require_once __DIR__ . '/app/Controllers/ContactController.php';

$router = new Router();
$router->get('home', array('HomeController', 'index'));
$router->get('login', array('AuthController', 'login'));
$router->get('register', array('AuthController', 'register'));
$router->get('logout', array('AuthController', 'logout'));
$router->get('cart', array('CartController', 'index'));
$router->get('checkout', array('CheckoutController', 'index'));
$router->get('catalog_pdf', array('CatalogController', 'download'));
$router->get('send_contact', array('ContactController', 'send'));

$route = isset($_GET['r']) ? trim($_GET['r']) : 'home';
$router->dispatch($route);
