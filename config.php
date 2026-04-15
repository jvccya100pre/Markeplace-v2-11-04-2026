<?php
if (session_id() === '') {
    session_start();
}

date_default_timezone_set('America/Caracas');

$config = array(
    'db' => array(
        'host' => 'localhost',
        'name' => 'createso_no_borrar_tutiendaonlinelq_donato',
        'user' => 'createso_vpsdatos',
        'pass' => 'Tresado37#',
        'charset' => 'utf8mb4',
        'prefix' => 'no_borrar_markeplacev1_'
    ),
    'base_path' => '',
    'captcha_public' => '6LdpnLcsAAAAAP4WgOwpDgB6NKk-nTIsvQhgd2S4',
    'captcha_secret' => '6LdpnLcsAAAAABiPFfqXyHD2aRgzUYxJ21rdMktA',
    'mail' => array(
        'host' => 'mail.tutiendaonlinelq.com',
        'port' => 465,
        'secure' => 'ssl',
        'username' => 'info@tutiendaonlinelq.com',
        'password' => 'Losteques.2026.',
        'from' => 'info@tutiendaonlinelq.com',
        'from_name' => 'Tu Tienda Online',
        'basepath' => 'http://mail.tutiendaonlinelq.com.'
    )
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

function logout_user_session()
{
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();
}

function check_session_timeout()
{
    $timeoutSeconds = 600; // 10 minutos

    if (!isset($_SESSION['user'])) {
        return;
    }

    $now = time();
    if (isset($_SESSION['last_activity']) && ($now - (int)$_SESSION['last_activity']) > $timeoutSeconds) {
        logout_user_session();
        redirect_to(route_url('login', array('timeout' => 1)));
    }

    $_SESSION['last_activity'] = $now;
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
        redirect_to(app_url('/panel/login.php'));
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

check_session_timeout();

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

function get_current_currency()
{
    return isset($_SESSION['currency']) ? $_SESSION['currency'] : 'VES';
}

function get_exchange_rate($date = null)
{
    if (!$date) {
        $date = date('Y-m-d');
    }

    if (!table_exists('exchange_rates')) {
        return 1.0;
    }

    try {
        $sql = 'SELECT usd_to_ves FROM ' . table_name('exchange_rates') . ' WHERE date = :date LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':date' => $date));
        $row = $st->fetch();
        return $row ? (float)$row['usd_to_ves'] : 1.0;
    } catch (Exception $e) {
        return 1.0;
    }
}

function format_price($price_in_ves, $currency = null)
{
    if ($currency === null) {
        $currency = get_current_currency();
    }
    if ($currency === 'USD') {
        $rate = get_exchange_rate();
        $price = $price_in_ves / $rate;
        return '$' . number_format($price, 2);
    } else {
        return 'VES ' . number_format($price_in_ves, 2);
    }
}

function send_smtp_mail($toEmail, $toName, $subject, $htmlBody, $textBody = '')
{
    global $config;

    $mailConfig = isset($config['mail']) && is_array($config['mail']) ? $config['mail'] : array();
    $smtpHost = isset($mailConfig['host']) ? trim($mailConfig['host']) : '';
    $smtpUser = isset($mailConfig['username']) ? trim($mailConfig['username']) : '';
    $smtpPass = isset($mailConfig['password']) ? (string)$mailConfig['password'] : '';
    $smtpFrom = isset($mailConfig['from']) ? trim($mailConfig['from']) : $smtpUser;

    if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '' || $smtpFrom === '' || trim((string)$toEmail) === '') {
        return false;
    }

    $phpMailerBase = __DIR__ . '/lib/PHPMailer/PHPMailer-6.9.1/src/';
    if (!is_file($phpMailerBase . 'PHPMailer.php') || !is_file($phpMailerBase . 'SMTP.php') || !is_file($phpMailerBase . 'Exception.php')) {
        return false;
    }

    require_once $phpMailerBase . 'Exception.php';
    require_once $phpMailerBase . 'PHPMailer.php';
    require_once $phpMailerBase . 'SMTP.php';

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = isset($mailConfig['secure']) ? trim($mailConfig['secure']) : 'ssl';
        $mail->Port = isset($mailConfig['port']) ? (int)$mailConfig['port'] : 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($smtpFrom, isset($mailConfig['from_name']) ? $mailConfig['from_name'] : 'Tu Tienda Online');
        $mail->addAddress(trim((string)$toEmail), trim((string)$toName));

        $mail->isHTML(true);
        $mail->Subject = (string)$subject;
        $mail->Body = (string)$htmlBody;
        $mail->AltBody = $textBody !== '' ? (string)$textBody : strip_tags((string)$htmlBody);

        return $mail->send();
    } catch (Exception $e) {
        $logFile = __DIR__ . '/error_log';
        error_log('[' . date('Y-m-d H:i:s') . '] Error enviando SMTP: ' . $e->getMessage() . "\n", 3, $logFile);
        return false;
    }
}
