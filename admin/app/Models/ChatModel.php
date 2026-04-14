<?php

class ChatModel
{
    public function users()
    {
        return db()->query('SELECT id, full_name FROM ' . table_name('users') . ' WHERE role = "cliente" ORDER BY full_name ASC')->fetchAll();
    }

    public function addAdminMessage($userId, $message)
    {
        $sql = 'INSERT INTO ' . table_name('chat_messages') . ' (user_id, sender_role, message, created_at) VALUES (:u, :r, :m, NOW())';
        $st = db()->prepare($sql);
        $st->execute(array(':u' => (int)$userId, ':r' => 'admin', ':m' => $message));
    }

    public function messagesByUser($userId)
    {
        $sql = 'SELECT * FROM ' . table_name('chat_messages') . ' WHERE user_id = :u ORDER BY id ASC';
        $st = db()->prepare($sql);
        $st->execute(array(':u' => (int)$userId));
        return $st->fetchAll();
    }
}
