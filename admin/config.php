<?php
if (session_id() === '') {
    session_start();
}

date_default_timezone_set('America/Caracas');

$config = array(
    'db' => array(
        'host' => 'localhost',
        'name' => 'createso_datosVPS',
        'user' => 'createso_vpsdatos',
        'pass' => 'Tresado37#',
        'charset' => 'utf8mb4',
        'prefix' => 'demo_markeplacev1_'
    ),
    'base_path' => '',
    'captcha_public' => '6LcPmbcsAAAAAP1EBl_9o7-EKNYoSP9XwdMj09ri',
    'captcha_secret' => '6LcPmbcsAAAAAGxREm0qnvELLLEmB-cQ0fZpuT6N'
);

function db()
{
    static $pdo = null;
    global $config;

    if ($pdo === null) {
        $db = $config['db'];
        $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'];
        $pdo = new PDO($dsn, $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return $pdo;
}

function table_name($name)
{
    global $config;
    return $config['db']['prefix'] . $name;
}

function esc($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_post()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}

function auth_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function auth_admin()
{
    return isset($_SESSION['admin']) ? $_SESSION['admin'] : null;
}

function redirect_to($path)
{
    header('Location: ' . $path);
    exit;
}

function app_url($path)
{
    global $config;

    if ($path === '') {
        return isset($config['base_path']) ? $config['base_path'] : '';
    }

    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }

    $basePath = isset($config['base_path']) ? trim($config['base_path']) : '';
    if ($basePath === '') {
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $scriptDir = trim(str_replace('\\', '/', dirname($scriptName)), '/.');
        $basePath = $scriptDir;
    }
    $basePath = trim($basePath, '/');
    $cleanPath = '/' . ltrim($path, '/');

    if ($basePath === '') {
        return $cleanPath;
    }

    return '/' . $basePath . $cleanPath;
}

function route_url($route, $params = array())
{
    $route = trim((string)$route, '/');
    $path = ($route === '' || $route === 'home') ? '/' : '/' . $route;
    $url = app_url($path);

    if (count($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

function require_user_login()
{
    if (!auth_user()) {
        $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : route_url('home');
        redirect_to(route_url('login', array('next' => $next)));
    }
}

function require_admin_login()
{
    if (!auth_admin()) {
        redirect_to(app_url('/login.php'));
    }
}

function track_visit($page)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $sql = 'INSERT INTO ' . table_name('visits') . ' (ip_address, page_visited, created_at) VALUES (:ip, :page, NOW())';
    $stmt = db()->prepare($sql);
    $stmt->execute(array(':ip' => $ip, ':page' => $page));
}

function cart_session_items()
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    return $_SESSION['cart'];
}

function cart_set_items($items)
{
    $_SESSION['cart'] = $items;
}

function get_setting($key, $default = '')
{
    $sql = 'SELECT setting_value FROM ' . table_name('settings') . ' WHERE setting_key = :k LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute(array(':k' => $key));
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function set_setting($key, $value)
{
    $exists = 'SELECT id FROM ' . table_name('settings') . ' WHERE setting_key = :k LIMIT 1';
    $stmt = db()->prepare($exists);
    $stmt->execute(array(':k' => $key));
    $row = $stmt->fetch();

    if ($row) {
        $sql = 'UPDATE ' . table_name('settings') . ' SET setting_value = :v, updated_at = NOW() WHERE id = :id';
        $up = db()->prepare($sql);
        $up->execute(array(':v' => $value, ':id' => $row['id']));
    } else {
        $sql = 'INSERT INTO ' . table_name('settings') . ' (setting_key, setting_value, updated_at) VALUES (:k, :v, NOW())';
        $in = db()->prepare($sql);
        $in->execute(array(':k' => $key, ':v' => $value));
    }
}

function table_exists($name)
{
    $stmt = db()->prepare('SHOW TABLES LIKE :table');
    $stmt->execute(array(':table' => table_name($name)));
    return (bool)$stmt->fetch();
}

function column_exists($table, $column)
{
    if (!table_exists($table)) {
        return false;
    }
    $sql = 'SHOW COLUMNS FROM ' . table_name($table) . ' LIKE :column';
    $stmt = db()->prepare($sql);
    $stmt->execute(array(':column' => $column));
    return (bool)$stmt->fetch();
}

function verify_recaptcha($token)
{
    global $config;
    $secret = isset($config['captcha_secret']) ? trim($config['captcha_secret']) : '';
    if ($secret === '' || $token === '') {
        return false;
    }
    $payload = http_build_query(array('secret' => $secret, 'response' => $token));
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 5
        )
    ));
    $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    if (!$response) {
        return false;
    }
    $json = json_decode($response, true);
    return isset($json['success']) && $json['success'] === true;
}
