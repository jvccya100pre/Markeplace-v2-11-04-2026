<?php

class OrderModel
{
    public function latestWithUser($limit)
    {
        $limit = (int)$limit;
        $select = 'o.*, u.full_name, u.email';
        $join = '';
        if (column_exists('orders', 'seller_id') && table_exists('sellers')) {
            $select .= ', s.full_name AS seller_name';
            $join = ' LEFT JOIN ' . table_name('sellers') . ' s ON s.id = o.seller_id';
        }
        $sql = 'SELECT ' . $select . ' FROM ' . table_name('orders') . ' o INNER JOIN ' . table_name('users') . ' u ON u.id = o.user_id' . $join . ' ORDER BY o.id DESC LIMIT ' . $limit;
        return db()->query($sql)->fetchAll();
    }

    public function findOrderWithUser($id)
    {
        $select = 'o.*, u.full_name, u.email';
        $join = '';
        if (column_exists('orders', 'seller_id') && table_exists('sellers')) {
            $select .= ', s.full_name AS seller_name';
            $join = ' LEFT JOIN ' . table_name('sellers') . ' s ON s.id = o.seller_id';
        }
        $sql = 'SELECT ' . $select . ' FROM ' . table_name('orders') . ' o INNER JOIN ' . table_name('users') . ' u ON u.id = o.user_id' . $join . ' WHERE o.id = :id LIMIT 1';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$id));
        return $st->fetch();
    }

    public function orderItems($orderId)
    {
        $sql = 'SELECT oi.quantity, oi.unit_price, p.name FROM ' . table_name('order_items') . ' oi INNER JOIN ' . table_name('products') . ' p ON p.id = oi.product_id WHERE oi.order_id = :id';
        $st = db()->prepare($sql);
        $st->execute(array(':id' => (int)$orderId));
        return $st->fetchAll();
    }
}
