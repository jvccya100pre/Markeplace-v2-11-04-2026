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

    public function updatePriceByCode($code, $price)
    {
        $sql = 'UPDATE ' . table_name('products') . ' SET price = :price WHERE internal_code = :code';
        $st = db()->prepare($sql);
        $st->execute(array(':code' => trim($code), ':price' => $price));
        return $st->rowCount() > 0;
    }

    public function create($data)
    {
        $columns = array('category_id', 'name', 'description', 'internal_code', 'stock', 'color', 'price', 'image_path');
        $placeholders = array(':c', ':n', ':d', ':code', ':s', ':color', ':p', ':img');
        $params = array(
            ':c' => (int)$data['category_id'],
            ':n' => trim($data['name']),
            ':d' => trim($data['description']),
            ':code' => trim($data['internal_code']),
            ':s' => (int)$data['stock'],
            ':color' => trim($data['color']),
            ':p' => $data['price'],
            ':img' => trim($data['image_path']) !== '' ? trim($data['image_path']) : '/logo.jpg'
        );

        if ($this->hasColumn('price_retail')) {
            $columns[] = 'price_retail';
            $columns[] = 'price_wholesale';
            $columns[] = 'show_retail';
            $columns[] = 'show_wholesale';
            $columns[] = 'allow_negative_stock';
            $columns[] = 'gallery_mode';
            $placeholders = array_merge($placeholders, array(':price_retail', ':price_wholesale', ':show_retail', ':show_wholesale', ':allow_negative_stock', ':gallery_mode'));
            $params[':price_retail'] = isset($data['price_retail']) ? $data['price_retail'] : $data['price'];
            $params[':price_wholesale'] = isset($data['price_wholesale']) ? $data['price_wholesale'] : '0.00';
            $params[':show_retail'] = isset($data['show_retail']) ? (int)$data['show_retail'] : 1;
            $params[':show_wholesale'] = isset($data['show_wholesale']) ? (int)$data['show_wholesale'] : 0;
            $params[':allow_negative_stock'] = isset($data['allow_negative_stock']) ? (int)$data['allow_negative_stock'] : 0;
            $params[':gallery_mode'] = isset($data['gallery_mode']) ? trim($data['gallery_mode']) : 'single';
        }

        $columns[] = 'created_at';
        $placeholders[] = 'NOW()';

        $sql = 'INSERT INTO ' . table_name('products') . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $st = db()->prepare($sql);
        try {
            $st->execute($params);
        } catch (PDOException $e) {
            $logFile = dirname(__DIR__, 2) . '/error_log';
            error_log("[" . date('Y-m-d H:i:s') . "] Error en ProductModel::create: " . $e->getMessage() . " - SQL: $sql - Params: " . print_r($params, true) . "\n", 3, $logFile);
            throw $e; // Re-lanzar para que el controlador lo maneje si quiere
        }
    }

    public function update($id, $data)
    {
        $columns = array('category_id=:c', 'name=:n', 'description=:d', 'internal_code=:code', 'stock=:s', 'color=:color', 'price=:p', 'image_path=:img');
        $params = array(
            ':c' => (int)$data['category_id'],
            ':n' => trim($data['name']),
            ':d' => trim($data['description']),
            ':code' => trim($data['internal_code']),
            ':s' => (int)$data['stock'],
            ':color' => trim($data['color']),
            ':p' => $data['price'],
            ':img' => trim($data['image_path']) !== '' ? trim($data['image_path']) : '/logo.jpg',
            ':id' => (int)$id
        );

        if ($this->hasColumn('price_retail')) {
            $columns[] = 'price_retail=:price_retail';
            $columns[] = 'price_wholesale=:price_wholesale';
            $columns[] = 'show_retail=:show_retail';
            $columns[] = 'show_wholesale=:show_wholesale';
            $columns[] = 'allow_negative_stock=:allow_negative_stock';
            $columns[] = 'gallery_mode=:gallery_mode';
            $params[':price_retail'] = isset($data['price_retail']) ? $data['price_retail'] : $data['price'];
            $params[':price_wholesale'] = isset($data['price_wholesale']) ? $data['price_wholesale'] : '0.00';
            $params[':show_retail'] = isset($data['show_retail']) ? (int)$data['show_retail'] : 1;
            $params[':show_wholesale'] = isset($data['show_wholesale']) ? (int)$data['show_wholesale'] : 0;
            $params[':allow_negative_stock'] = isset($data['allow_negative_stock']) ? (int)$data['allow_negative_stock'] : 0;
            $params[':gallery_mode'] = isset($data['gallery_mode']) ? trim($data['gallery_mode']) : 'single';
        }

        $sql = 'UPDATE ' . table_name('products') . ' SET ' . implode(', ', $columns) . ' WHERE id=:id';
        $st = db()->prepare($sql);
        $st->execute($params);
    }

    private function hasColumn($column)
    {
        return column_exists('products', $column);
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
