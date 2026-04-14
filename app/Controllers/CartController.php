<?php
require_once __DIR__ . '/../Core/Controller.php';

class CartController extends Controller
{
    public function index()
    {
        require_user_login();

        if (is_post() && isset($_POST['remove_item'])) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $items = cart_session_items();
            if (isset($items[$id])) {
                unset($items[$id]);
                cart_set_items($items);
            }
            redirect_to(route_url('cart'));
        }

        $items = cart_session_items();
        $products = array();
        $total = 0;
        if (count($items)) {
            $ids = implode(',', array_map('intval', array_keys($items)));
            $sql = 'SELECT id, name, price, stock FROM ' . table_name('products') . ' WHERE id IN (' . $ids . ')';
            $products = db()->query($sql)->fetchAll();
            foreach ($products as $p) {
                $qty = (int)$items[$p['id']];
                $total += $qty * (float)$p['price'];
            }
        }

        $this->render('cart/index', array(
            'products' => $products,
            'items' => $items,
            'total' => $total
        ));
    }
}
