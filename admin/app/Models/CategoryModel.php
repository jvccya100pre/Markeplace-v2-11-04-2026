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

    public function activeByName()
    {
        return db()->query('SELECT id, name FROM ' . table_name('categories') . ' WHERE is_active = 1 ORDER BY name ASC')->fetchAll();
    }
}
