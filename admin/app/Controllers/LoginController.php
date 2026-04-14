<?php
require_once dirname(__DIR__) . '/Models/AuthModel.php';

class LoginController extends BaseController
{
    public function handle()
    {
        if (isset($_GET['logout']) && (string)$_GET['logout'] === '1') {
            unset($_SESSION['admin'], $_SESSION['user']);
            session_regenerate_id(true);
            redirect_to(app_url('/login.php'));
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
                    $_SESSION['admin'] = array('id' => $user['id'], 'name' => $user['full_name'], 'email' => $user['email']);
                    $_SESSION['user'] = $_SESSION['admin'];
                    redirect_to(app_url('/index.php'));
                }

                $error = 'Credenciales invalidas';
            }
        }

        $this->render('login', array('error' => $error), false);
    }
}
