<?php
require_once __DIR__ . '/../Core/Controller.php';

class AuthController extends Controller
{
    const VERIFICATION_RESEND_MAX = 3;
    const VERIFICATION_RESEND_COOLDOWN_SECONDS = 300;

    public function login()
    {
        $error = '';
        $message = '';

        if (!is_post() && isset($_GET['timeout'])) {
            $error = 'Tu sesión ha expirado por inactividad. Por favor inicia sesión de nuevo.';
        }

        if (!is_post() && isset($_GET['logged_out'])) {
            $message = 'Sesion cerrada correctamente.';
        }

        if (!is_post() && isset($_GET['verified'])) {
            $message = 'Correo confirmado correctamente. Ya puedes iniciar sesion.';
        }

        if (!is_post() && isset($_GET['verify_error'])) {
            $error = 'No se pudo confirmar el correo. El enlace es invalido o ha expirado.';
        }

        if (is_post()) {
            if (isset($_POST['resend_verification'])) {
                $this->ensureEmailVerificationColumns();

                $resendEmail = isset($_POST['resend_email']) ? trim($_POST['resend_email']) : '';
                if ($resendEmail === '') {
                    $error = 'Indica tu correo para reenviar la confirmacion.';
                } else {
                    $resendResult = $this->resendVerificationEmailByAddress($resendEmail);
                    if ($resendResult['status'] === 'wait') {
                        $error = 'Debes esperar ' . $this->formatWaitTime((int)$resendResult['seconds']) . ' antes de solicitar otro envio.';
                    } elseif ($resendResult['status'] === 'limit') {
                        $error = 'Alcanzaste el maximo de 3 envios de confirmacion.';
                    } else {
                        $message = 'Si tu cuenta está pendiente de confirmación, te enviamos un nuevo correo.';
                    }
                }

                $this->render('auth/login', array('error' => $error, 'message' => $message));
                return;
            }

            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $captchaToken = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';

            $captcha = $this->verifyCaptcha($captchaToken);
            if (!$captcha['ok']) {
                $error = 'Captcha invalido. Intenta nuevamente.';
                $this->render('auth/login', array('error' => $error, 'message' => $message));
                return;
            }

            $sql = 'SELECT * FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
            $stmt = db()->prepare($sql);
            $stmt->execute(array(':e' => $email));
            $user = $stmt->fetch();

            if ($user && $user['password_hash'] === sha1($password)) {
                if ($this->requiresEmailVerification($user)) {
                    $error = 'Debes confirmar tu correo antes de iniciar sesion. Revisa tu bandeja de entrada.';
                    $this->render('auth/login', array('error' => $error, 'message' => $message));
                    return;
                }

                session_regenerate_id(true);
                $_SESSION['user'] = array(
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'email' => $user['email']
                );
                $_SESSION['last_activity'] = time();
                if ($user['role'] === 'admin') {
                    $_SESSION['admin'] = $_SESSION['user'];
                }
                $next = isset($_GET['next']) && $_GET['next'] !== '' ? $_GET['next'] : route_url('home');
                redirect_to($next);
            }

            $error = 'Credenciales invalidas';
        }

        $this->render('auth/login', array('error' => $error, 'message' => $message));
    }

    public function logout()
    {
        logout_user_session();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        redirect_to(route_url('login', array('logged_out' => 1)));
    }

    public function register()
    {
        $msg = '';
        if (is_post()) {
            $this->ensureEmailVerificationColumns();

            $name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $existsSql = 'SELECT id FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
            $exists = db()->prepare($existsSql);
            $exists->execute(array(':e' => $email));

            if ($exists->fetch()) {
                $msg = 'El correo ya existe';
            } else {
                $verificationToken = $this->generateVerificationToken();
                $verificationTokenHash = hash('sha256', $verificationToken);
                $verificationExpiresAt = date('Y-m-d H:i:s', time() + 86400);

                $columns = array('full_name', 'email', 'password_hash', 'role', 'created_at');
                $values = array(':n', ':e', ':p', ':r', 'NOW()');
                $params = array(':n' => $name, ':e' => $email, ':p' => sha1($password), ':r' => 'cliente');

                if (column_exists('users', 'email_verification_required')) {
                    $columns[] = 'email_verification_required';
                    $values[] = ':vr';
                    $params[':vr'] = 1;
                }
                if (column_exists('users', 'email_verification_token')) {
                    $columns[] = 'email_verification_token';
                    $values[] = ':vt';
                    $params[':vt'] = $verificationTokenHash;
                }
                if (column_exists('users', 'email_verification_expires_at')) {
                    $columns[] = 'email_verification_expires_at';
                    $values[] = ':ve';
                    $params[':ve'] = $verificationExpiresAt;
                }
                if (column_exists('users', 'email_verification_sent_count')) {
                    $columns[] = 'email_verification_sent_count';
                    $values[] = ':vsc';
                    $params[':vsc'] = 1;
                }
                if (column_exists('users', 'email_verification_last_sent_at')) {
                    $columns[] = 'email_verification_last_sent_at';
                    $values[] = 'NOW()';
                }

                $sql = 'INSERT INTO ' . table_name('users') . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ')';
                $stmt = db()->prepare($sql);
                $stmt->execute($params);

                $this->sendVerificationEmail($name, $email, $verificationToken);

                $adminEmail = isset($GLOBALS['config']['mail']['from']) ? $GLOBALS['config']['mail']['from'] : get_setting('company_email', '');
                if ($adminEmail !== '') {
                    $subject = 'Nuevo cliente registrado';
                    $html = '<h3>Nuevo registro de cliente</h3>'
                        . '<p><strong>Nombre:</strong> ' . esc($name) . '</p>'
                        . '<p><strong>Correo:</strong> ' . esc($email) . '</p>'
                        . '<p><strong>Fecha:</strong> ' . date('d/m/Y H:i:s') . '</p>';
                    $text = 'Nuevo registro de cliente' . "\n"
                        . 'Nombre: ' . $name . "\n"
                        . 'Correo: ' . $email . "\n"
                        . 'Fecha: ' . date('d/m/Y H:i:s');
                    send_smtp_mail($adminEmail, 'Administrador', $subject, $html, $text);
                }

                $msg = 'Cuenta creada. Revisa tu correo para confirmar tu cuenta antes de iniciar sesion.';
            }
        }

        $this->render('auth/register', array('msg' => $msg));
    }

    public function confirmEmail()
    {
        $this->ensureEmailVerificationColumns();

        $email = isset($_GET['email']) ? trim($_GET['email']) : '';
        $token = isset($_GET['token']) ? trim($_GET['token']) : '';

        if ($email === '' || $token === '') {
            redirect_to(route_url('login', array('verify_error' => 1)));
        }

        $sql = 'SELECT id, full_name, email, email_verification_required, email_verification_token, email_verification_expires_at FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(array(':e' => $email));
        $user = $stmt->fetch();

        if (!$user) {
            redirect_to(route_url('login', array('verify_error' => 1)));
        }

        if (empty($user['email_verification_required'])) {
            redirect_to(route_url('login', array('verified' => 1)));
        }

        $tokenHash = hash('sha256', $token);
        $expiresAt = isset($user['email_verification_expires_at']) ? trim((string)$user['email_verification_expires_at']) : '';
        $isExpired = ($expiresAt === '' || strtotime($expiresAt) < time());
        $isValidToken = isset($user['email_verification_token']) && hash_equals((string)$user['email_verification_token'], $tokenHash);

        if (!$isValidToken || $isExpired) {
            redirect_to(route_url('login', array('verify_error' => 1)));
        }

        $update = 'UPDATE ' . table_name('users') . ' SET email_verification_required = 0, email_verified_at = NOW(), email_verification_token = NULL, email_verification_expires_at = NULL, email_verification_sent_count = 0, email_verification_last_sent_at = NULL WHERE id = :id LIMIT 1';
        $up = db()->prepare($update);
        $up->execute(array(':id' => (int)$user['id']));

        redirect_to(route_url('login', array('verified' => 1)));
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

    private function requiresEmailVerification($user)
    {
        if (!column_exists('users', 'email_verification_required')) {
            return false;
        }

        return !empty($user['email_verification_required']);
    }

    private function ensureEmailVerificationColumns()
    {
        if (!column_exists('users', 'email_verified_at')) {
            db()->exec('ALTER TABLE ' . table_name('users') . ' ADD COLUMN email_verified_at DATETIME NULL AFTER created_at');
        }
        if (!column_exists('users', 'email_verification_required')) {
            db()->exec('ALTER TABLE ' . table_name('users') . ' ADD COLUMN email_verification_required TINYINT(1) NOT NULL DEFAULT 0 AFTER email_verified_at');
        }
        if (!column_exists('users', 'email_verification_token')) {
            db()->exec('ALTER TABLE ' . table_name('users') . ' ADD COLUMN email_verification_token VARCHAR(128) NULL AFTER email_verification_required');
        }
        if (!column_exists('users', 'email_verification_expires_at')) {
            db()->exec('ALTER TABLE ' . table_name('users') . ' ADD COLUMN email_verification_expires_at DATETIME NULL AFTER email_verification_token');
        }
        if (!column_exists('users', 'email_verification_sent_count')) {
            db()->exec('ALTER TABLE ' . table_name('users') . ' ADD COLUMN email_verification_sent_count INT NOT NULL DEFAULT 0 AFTER email_verification_expires_at');
        }
        if (!column_exists('users', 'email_verification_last_sent_at')) {
            db()->exec('ALTER TABLE ' . table_name('users') . ' ADD COLUMN email_verification_last_sent_at DATETIME NULL AFTER email_verification_sent_count');
        }
    }

    private function generateVerificationToken()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        }

        return sha1(uniqid(mt_rand(), true)) . sha1(uniqid(mt_rand(), true));
    }

    private function sendVerificationEmail($name, $email, $token)
    {
        $confirmPath = route_url('confirm_email', array('email' => $email, 'token' => $token));
        $confirmUrl = $this->toAbsoluteUrl($confirmPath);

        $subject = 'Confirma tu correo';
        $html = '<h3>Hola ' . esc($name) . '</h3>'
            . '<p>Gracias por registrarte. Para activar tu cuenta, confirma tu correo haciendo clic en el siguiente enlace:</p>'
            . '<p><a href="' . esc($confirmUrl) . '">Confirmar correo</a></p>'
            . '<p>Este enlace expira en 24 horas.</p>';
        $text = 'Hola ' . $name . "\n"
            . 'Gracias por registrarte. Confirma tu correo con este enlace:' . "\n"
            . $confirmUrl . "\n"
            . 'Este enlace expira en 24 horas.';

        return send_smtp_mail($email, $name, $subject, $html, $text);
    }

    private function resendVerificationEmailByAddress($email)
    {
        $sql = 'SELECT id, full_name, email, email_verification_required, email_verification_sent_count, email_verification_last_sent_at FROM ' . table_name('users') . ' WHERE email = :e LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':e' => $email));
        $user = $st->fetch();

        if (!$user || empty($user['email_verification_required'])) {
            return array('status' => 'noop', 'seconds' => 0);
        }

        $sentCount = isset($user['email_verification_sent_count']) ? (int)$user['email_verification_sent_count'] : 0;
        if ($sentCount >= self::VERIFICATION_RESEND_MAX) {
            return array('status' => 'limit', 'seconds' => 0);
        }

        $lastSentAt = isset($user['email_verification_last_sent_at']) ? trim((string)$user['email_verification_last_sent_at']) : '';
        if ($lastSentAt !== '') {
            $elapsed = time() - strtotime($lastSentAt);
            if ($elapsed < self::VERIFICATION_RESEND_COOLDOWN_SECONDS) {
                return array('status' => 'wait', 'seconds' => self::VERIFICATION_RESEND_COOLDOWN_SECONDS - $elapsed);
            }
        }

        $verificationToken = $this->generateVerificationToken();
        $verificationTokenHash = hash('sha256', $verificationToken);
        $verificationExpiresAt = date('Y-m-d H:i:s', time() + 86400);

        $up = db()->prepare('UPDATE ' . table_name('users') . ' SET email_verification_token = :t, email_verification_expires_at = :e, email_verification_sent_count = email_verification_sent_count + 1, email_verification_last_sent_at = NOW() WHERE id = :id LIMIT 1');
        $up->execute(array(
            ':t' => $verificationTokenHash,
            ':e' => $verificationExpiresAt,
            ':id' => (int)$user['id']
        ));

        $this->sendVerificationEmail($user['full_name'], $user['email'], $verificationToken);
        return array('status' => 'sent', 'seconds' => 0);
    }

    private function formatWaitTime($seconds)
    {
        $seconds = max(1, (int)$seconds);
        $minutes = (int)floor($seconds / 60);
        $remainingSeconds = (int)($seconds % 60);

        if ($minutes > 0 && $remainingSeconds > 0) {
            return $minutes . ' min ' . $remainingSeconds . ' seg';
        }
        if ($minutes > 0) {
            return $minutes . ' min';
        }

        return $remainingSeconds . ' seg';
    }

    private function toAbsoluteUrl($path)
    {
        $path = (string)$path;
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '';
        if ($host === '') {
            return $path;
        }

        return $scheme . '://' . $host . $path;
    }
}
