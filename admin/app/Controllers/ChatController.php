<?php
require_once dirname(__DIR__) . '/Models/ChatModel.php';

class ChatController extends BaseController
{
    public function handle()
    {
        require_admin_login();
        $model = new ChatModel();

        $users = $model->users();
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (count($users) ? (int)$users[0]['id'] : 0);

        if (is_post() && $userId > 0) {
            $msg = isset($_POST['message']) ? trim($_POST['message']) : '';
            if ($msg !== '') {
                $model->addAdminMessage($userId, $msg);
            }
            redirect_to(app_url('/chat.php?user_id=' . $userId));
        }

        $messages = $userId > 0 ? $model->messagesByUser($userId) : array();

        $this->render('chat', array(
            'users' => $users,
            'userId' => $userId,
            'messages' => $messages,
        ));
    }
}
