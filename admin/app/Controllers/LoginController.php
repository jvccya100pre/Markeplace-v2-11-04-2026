<?php
require_once dirname(__DIR__) . '/Models/AuthModel.php';

class LoginController extends BaseController
{
    public function handle()
    {
        $message = '';

        if (isset($_GET['logout']) && (string)$_GET['logout'] === '1') {
            logout_user_session();
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            session_regenerate_id(true);
            redirect_to(app_url('/login.php?logged_out=1'));
        }

        if (!is_post() && isset($_GET['logged_out']) && (string)$_GET['logged_out'] === '1') {
            $message = 'Sesion cerrada correctamente.';
        }

        $model = new AuthModel();
        $error = '';

        if (is_post()) {
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $captchaToken = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';

            if (!empty($config['captcha_public']) && !verify_recaptcha($captchaToken)) {
                $error = 'Verifica el captcha de Google antes de continuar.';
            } else {
                $user = $model->findAdminByEmail($email);
                if ($user && $user['password_hash'] === sha1($password)) {
                    session_regenerate_id(true);
                    $_SESSION['admin'] = array('id' => $user['id'], 'name' => $user['full_name'], 'email' => $user['email']);
                    $_SESSION['user'] = $_SESSION['admin'];
                    $_SESSION['last_activity'] = time();
                    redirect_to(app_url('/index.php'));
                }

                $error = 'Credenciales invalidas';
            }
        }

        $this->render('login', array('error' => $error, 'message' => $message), false);
    }
}
