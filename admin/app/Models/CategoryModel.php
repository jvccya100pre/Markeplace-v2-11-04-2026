<?php

class CategoryModel
{
    public function create($name)
    {
        $sql = 'INSERT INTO ' . table_name('categories') . ' (name, is_active) VALUES (:n, 1)';
        $st = db()->prepare($sql);
        $st->execute(array(':n' => $name));
    }

    public function allDesc()
    {
        return db()->query('SELECT * FROM ' . table_name('categories') . ' ORDER BY id DESC')->fetchAll();
    }

    public function findById($id)
    {
        $sql = 'SELECT * FROM ' . table_name('categories') . ' WHERE id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
        return $st->fetch();
    }

    public function update($id, $name)
    {
        $sql = 'UPDATE ' . table_name('categories') . ' SET name = :name WHERE id = :id';
        $st = db()->prepare($sql);
        $st->execute(array(':name' => $name, ':id' => (int)$id));
    }

    public function delete($id)
    {
        $sql = 'DELETE FROM ' . table_name('categories') . ' WHERE id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
    }

    public function activeByName()
    {
        return db()->query('SELECT id, name FROM ' . table_name('categories') . ' WHERE is_active = 1 ORDER BY name ASC')->fetchAll();
    }
}
