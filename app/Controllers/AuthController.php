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
