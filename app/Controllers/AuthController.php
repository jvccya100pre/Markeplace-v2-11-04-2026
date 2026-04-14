<?php
require_once __DIR__ . '/../Core/Controller.php';

class AuthController extends Controller
{
    public function login()
    {
        $error = '';

        if (!is_post() && isset($_GET['timeout'])) {
            $error = 'Tu sesión ha expirado por inactividad. Por favor inicia sesión de nuevo.';
        }

        if (is_post()) {
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $captchaToken = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';

            $captcha = $this->verifyCaptcha($captchaToken);
            if (!$captcha['ok']) {
                $error = 'Captcha invalido. Intenta nuevamente.';
                $this->render('auth/login', array('error' => $error));
                return;
            }

            $sql = 'SELECT * FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
            $stmt = db()->prepare($sql);
            $stmt->execute(array(':e' => $email));
            $user = $stmt->fetch();

            if ($user && $user['password_hash'] === sha1($password)) {
                $_SESSION['user'] = array(
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'email' => $user['email']
                );
                if ($user['role'] === 'admin') {
                    $_SESSION['admin'] = $_SESSION['user'];
                }
                $next = isset($_GET['next']) && $_GET['next'] !== '' ? $_GET['next'] : route_url('home');
                redirect_to($next);
            }

            $error = 'Credenciales invalidas';
        }

        $this->render('auth/login', array('error' => $error));
    }

    public function register()
    {
        $msg = '';
        if (is_post()) {
            $name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $existsSql = 'SELECT id FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
            $exists = db()->prepare($existsSql);
            $exists->execute(array(':e' => $email));

            if ($exists->fetch()) {
                $msg = 'El correo ya existe';
            } else {
                $sql = 'INSERT INTO ' . table_name('users') . ' (full_name, email, password_hash, role, created_at) VALUES (:n, :e, :p, :r, NOW())';
                $stmt = db()->prepare($sql);
                $stmt->execute(array(':n' => $name, ':e' => $email, ':p' => sha1($password), ':r' => 'cliente'));
                $msg = 'Cuenta creada. Ya puedes iniciar sesion.';
            }
        }

        $this->render('auth/register', array('msg' => $msg));
    }

    public function googleOauth()
    {
        global $config;

        $clientId = isset($config['google_oauth_client_id']) ? trim($config['google_oauth_client_id']) : '';
        $clientSecret = isset($config['google_oauth_client_secret']) ? trim($config['google_oauth_client_secret']) : '';

        if ($clientId === '' || $clientSecret === '') {
            $this->render('auth/login', array('error' => 'Google OAuth no está configurado. Contacta al administrador.'));
            return;
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        if (isset($_GET['next']) && $_GET['next'] !== '') {
            $_SESSION['google_oauth_next'] = $_GET['next'];
        }

        $redirectUri = route_url('google_oauth_callback');
        $params = array(
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'include_granted_scopes' => 'true',
            'state' => $state,
            'prompt' => 'select_account'
        );

        redirect_to('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }

    public function googleOauthCallback()
    {
        if (empty($_GET['state']) || empty($_SESSION['google_oauth_state']) || $_GET['state'] !== $_SESSION['google_oauth_state']) {
            $this->render('auth/login', array('error' => 'Error de seguridad en inicio de sesión con Google.'));
            return;
        }

        if (empty($_GET['code'])) {
            $this->render('auth/login', array('error' => 'No se recibió código de Google.'));
            return;
        }

        $redirectUri = route_url('google_oauth_callback');
        $tokenResponse = $this->fetchGoogleOauthToken($_GET['code'], $redirectUri);

        if (!$tokenResponse || empty($tokenResponse['access_token'])) {
            $this->render('auth/login', array('error' => 'No se pudo iniciar sesión con Google.'));
            return;
        }

        $userInfo = $this->fetchGoogleUserInfo($tokenResponse['access_token']);
        if (!$userInfo || empty($userInfo['email'])) {
            $this->render('auth/login', array('error' => 'No se pudo obtener la información de tu cuenta de Google.'));
            return;
        }

        $email = $userInfo['email'];
        $fullName = isset($userInfo['name']) ? trim($userInfo['name']) : $email;

        $sql = 'SELECT * FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(array(':e' => $email));
        $user = $stmt->fetch();

        if (!$user) {
            $insertSql = 'INSERT INTO ' . table_name('users') . ' (full_name, email, password_hash, role, created_at) VALUES (:n, :e, :p, :r, NOW())';
            $stmt = db()->prepare($insertSql);
            $stmt->execute(array(
                ':n' => $fullName,
                ':e' => $email,
                ':p' => sha1(bin2hex(random_bytes(16))),
                ':r' => 'cliente'
            ));
            $user = array(
                'id' => db()->lastInsertId(),
                'full_name' => $fullName,
                'email' => $email,
                'role' => 'cliente'
            );
        }

        $_SESSION['user'] = array(
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email']
        );

        if (isset($user['role']) && $user['role'] === 'admin') {
            $_SESSION['admin'] = $_SESSION['user'];
        }

        $next = isset($_SESSION['google_oauth_next']) && $_SESSION['google_oauth_next'] !== '' ? $_SESSION['google_oauth_next'] : route_url('home');
        unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_next']);

        redirect_to($next);
    }

    private function fetchGoogleOauthToken($code, $redirectUri)
    {
        global $config;

        $clientId = isset($config['google_oauth_client_id']) ? trim($config['google_oauth_client_id']) : '';
        $clientSecret = isset($config['google_oauth_client_secret']) ? trim($config['google_oauth_client_secret']) : '';

        $payload = http_build_query(array(
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ));

        $responseBody = '';
        if (function_exists('curl_init')) {
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $responseBody = curl_exec($ch);
            curl_close($ch);
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 10
                )
            );
            $context = stream_context_create($opts);
            $responseBody = @file_get_contents('https://oauth2.googleapis.com/token', false, $context);
        }

        return is_string($responseBody) ? json_decode($responseBody, true) : null;
    }

    private function fetchGoogleUserInfo($accessToken)
    {
        $url = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $responseBody = '';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $responseBody = curl_exec($ch);
            curl_close($ch);
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'GET',
                    'header' => "Authorization: Bearer " . $accessToken . "\r\n",
                    'timeout' => 10
                )
            );
            $context = stream_context_create($opts);
            $responseBody = @file_get_contents($url, false, $context);
        }

        return is_string($responseBody) ? json_decode($responseBody, true) : null;
    }

    private function verifyCaptcha($token)
    {
        global $config;

        $secret = isset($config['captcha_secret']) ? trim($config['captcha_secret']) : '';
        if ($secret === '') {
            return array('ok' => false, 'reason' => 'missing_secret');
        }

        if ($token === '') {
            return array('ok' => false, 'reason' => 'missing_token');
        }

        $payload = http_build_query(array(
            'secret' => $secret,
            'response' => $token,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
        ));

        $responseBody = '';

        if (function_exists('curl_init')) {
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $responseBody = curl_exec($ch);
            curl_close($ch);
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 10
                )
            );
            $context = stream_context_create($opts);
            $responseBody = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        }

        if (!is_string($responseBody) || $responseBody === '') {
            return array('ok' => false, 'reason' => 'request_failed');
        }

        $data = json_decode($responseBody, true);
        if (!is_array($data) || empty($data['success'])) {
            return array('ok' => false, 'reason' => 'invalid_response');
        }

        return array('ok' => true, 'reason' => 'ok');
    }
}
