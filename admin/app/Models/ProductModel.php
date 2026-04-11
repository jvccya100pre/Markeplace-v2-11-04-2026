<?php

class ProductModel
{
    public function findByCode($code)
    {
        $sql = 'SELECT id FROM ' . table_name('products') . ' WHERE internal_code = :code LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':code' => $code));
        return $st->fetch();
    }

    public function findOtherByCode($code, $id)
    {
        $sql = 'SELECT id FROM ' . table_name('products') . ' WHERE internal_code = :code AND id <> :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':code' => $code, ':id' => $id));
        return $st->fetch();
    }

    public function create($data)
    {
        $sql = 'INSERT INTO ' . table_name('products') . ' (category_id, name, description, internal_code, stock, color, price, image_path, created_at) VALUES (:c,:n,:d,:code,:s,:color,:p,:img,NOW())';
        $st = db()->prepare($sql);
        $st->execute(array(
            ':c' => (int)$data['category_id'],
            ':n' => trim($data['name']),
            ':d' => trim($data['description']),
            ':code' => trim($data['internal_code']),
            ':s' => (int)$data['stock'],
            ':color' => trim($data['color']),
            ':p' => (float)$data['price'],
            ':img' => trim($data['image_path']) !== '' ? trim($data['image_path']) : '/logo.jpg'
        ));
    }

    public function update($id, $data)
    {
        $sql = 'UPDATE ' . table_name('products') . ' SET category_id=:c, name=:n, description=:d, internal_code=:code, stock=:s, color=:color, price=:p, image_path=:img WHERE id=:id';
        $st = db()->prepare($sql);
        $st->execute(array(
            ':c' => (int)$data['category_id'],
            ':n' => trim($data['name']),
            ':d' => trim($data['description']),
            ':code' => trim($data['internal_code']),
            ':s' => (int)$data['stock'],
            ':color' => trim($data['color']),
            ':p' => (float)$data['price'],
            ':img' => trim($data['image_path']) !== '' ? trim($data['image_path']) : '/logo.jpg',
            ':id' => (int)$id
        ));
    }

    public function hasOrderItems($id)
    {
        $sql = 'SELECT COUNT(*) AS c FROM ' . table_name('order_items') . ' WHERE product_id = :id';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
        $row = $st->fetch();
        return $row ? (int)$row['c'] > 0 : false;
    }

    public function delete($id)
    {
        $sql = 'DELETE FROM ' . table_name('products') . ' WHERE id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
    }

    public function findById($id)
    {
        $sql = 'SELECT * FROM ' . table_name('products') . ' WHERE id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
        return $st->fetch();
    }

    public function searchWithCategory($search)
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM ' . table_name('products') . ' p INNER JOIN ' . table_name('categories') . ' c ON c.id = p.category_id';
        $params = array();

        if ($search !== '') {
            $sql .= ' WHERE p.name LIKE :q OR p.internal_code LIKE :q';
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY p.id DESC LIMIT 200';
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}
