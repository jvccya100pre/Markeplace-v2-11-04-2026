<?php

class AuthModel
{
    public function findAdminByEmail($email)
    {
        $sql = 'SELECT * FROM ' . table_name('users') . ' WHERE email = :e AND role = :r LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(array(':e' => $email, ':r' => 'admin'));
        return $stmt->fetch();
    }
}
