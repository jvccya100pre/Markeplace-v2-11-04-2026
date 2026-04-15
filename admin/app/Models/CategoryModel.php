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

    public function getGeneralCategoryId()
    {
        $sql = 'SELECT id, name FROM ' . table_name('categories') . ' WHERE LOWER(name) IN (:general_in, :varios_in) ORDER BY FIELD(LOWER(name), :general_order, :varios_order) LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(
            ':general_in' => 'general',
            ':varios_in' => 'varios',
            ':general_order' => 'general',
            ':varios_order' => 'varios'
        ));
        $row = $st->fetch();
        if ($row) {
            if (mb_strtolower(trim($row['name']), 'UTF-8') !== 'general') {
                $up = db()->prepare('UPDATE ' . table_name('categories') . ' SET name = :name WHERE id = :id');
                $up->execute(array(':name' => 'General', ':id' => (int)$row['id']));
            }
            return (int)$row['id'];
        }

        $sql = 'INSERT INTO ' . table_name('categories') . ' (name, is_active) VALUES (:name, 1)';
        $st = db()->prepare($sql);
        $st->execute(array(':name' => 'General'));
        return (int)db()->lastInsertId();
    }

    public function getVariosCategoryId()
    {
        return $this->getGeneralCategoryId();
    }

    public function reassignProductsToCategory($fromCategoryId, $toCategoryId)
    {
        $sql = 'UPDATE ' . table_name('products') . ' SET category_id = :toId WHERE category_id = :fromId';
        $st = db()->prepare($sql);
        $st->execute(array(':toId' => (int)$toCategoryId, ':fromId' => (int)$fromCategoryId));
    }

    public function reassignUncategorizedProductsToGeneral()
    {
        $generalId = $this->getGeneralCategoryId();
        $sql = 'UPDATE ' . table_name('products') . ' SET category_id = :toId WHERE category_id IS NULL OR category_id = 0 OR category_id NOT IN (SELECT id FROM ' . table_name('categories') . ')';
        $st = db()->prepare($sql);
        $st->execute(array(':toId' => (int)$generalId));
    }

    public function reassignUncategorizedProductsToVarios()
    {
        $this->reassignUncategorizedProductsToGeneral();
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
