<?php

class ContactController
{
    public function send()
    {
        if (!is_post()) {
            redirect_to(route_url('home'));
        }

        $captchaToken = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';
        $captcha = $this->verifyCaptcha($captchaToken);
        if (!$captcha['ok']) {
            redirect_to(route_url('home', array('contact' => 'captcha_error', 'captcha_reason' => $captcha['reason'])));
        }

        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';

        if ($name !== '' && $email !== '' && $message !== '') {
            $sql = 'INSERT INTO ' . table_name('contact_messages') . ' (name, email, message, created_at) VALUES (:n, :e, :m, NOW())';
            $stmt = db()->prepare($sql);
            $stmt->execute(array(':n' => $name, ':e' => $email, ':m' => $message));
            redirect_to(route_url('home', array('contact' => 'ok')));
        }

        redirect_to(route_url('home', array('contact' => 'missing_fields')));
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
            $reason = 'invalid_response';
            if (is_array($data) && isset($data['error-codes']) && is_array($data['error-codes']) && count($data['error-codes']) > 0) {
                $reason = implode(',', $data['error-codes']);
            }
            return array('ok' => false, 'reason' => $reason);
        }

        return array('ok' => true, 'reason' => 'ok');
    }
}
