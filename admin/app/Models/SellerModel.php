<?php

class SellerModel
{
    public function tableExists()
    {
        return table_exists('sellers');
    }

    public function findAll()
    {
        if (!$this->tableExists()) {
            return array();
        }

        $sql = 'SELECT * FROM ' . table_name('sellers') . ' ORDER BY id DESC';
        return db()->query($sql)->fetchAll();
    }

    public function findById($id)
    {
        if (!$this->tableExists()) {
            return null;
        }

        $sql = 'SELECT * FROM ' . table_name('sellers') . ' WHERE id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
        return $st->fetch();
    }

    public function findByEmail($email)
    {
        if (!$this->tableExists()) {
            return null;
        }

        $sql = 'SELECT * FROM ' . table_name('sellers') . ' WHERE email = :email LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':email' => trim($email)));
        return $st->fetch();
    }

    public function findByUsername($username)
    {
        if (!$this->tableExists()) {
            return null;
        }

        $sql = 'SELECT * FROM ' . table_name('sellers') . ' WHERE username = :username LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':username' => trim($username)));
        return $st->fetch();
    }

    public function create($data)
    {
        if (!$this->tableExists()) {
            return;
        }

        $sql = 'INSERT INTO ' . table_name('sellers') . ' (full_name, email, username, link_token, created_at) VALUES (:name, :email, :username, :token, NOW())';
        $st = db()->prepare($sql);
        $st->execute(array(
            ':name' => trim($data['full_name']),
            ':email' => trim($data['email']),
            ':username' => trim($data['username']),
            ':token' => $this->generateToken(),
        ));
    }

    public function update($id, $data)
    {
        if (!$this->tableExists()) {
            return;
        }

        $sql = 'UPDATE ' . table_name('sellers') . ' SET full_name = :name, email = :email, username = :username WHERE id = :id';
        $st = db()->prepare($sql);
        $st->execute(array(
            ':name' => trim($data['full_name']),
            ':email' => trim($data['email']),
            ':username' => trim($data['username']),
            ':id' => (int)$id
        ));
    }

    public function delete($id)
    {
        if (!$this->tableExists()) {
            return;
        }

        $sql = 'DELETE FROM ' . table_name('sellers') . ' WHERE id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
    }

    public function buildLink($seller)
    {
        return app_url('/?seller=' . ((int)$seller['id']));
    }

    private function generateToken()
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(10));
        }

        return sha1(uniqid('', true));
    }
}
