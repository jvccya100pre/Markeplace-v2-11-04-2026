<?php
require_once __DIR__ . '/../Core/Controller.php';

class CheckoutController extends Controller
{
    public function index()
    {
        require_user_login();

        $items = cart_session_items();
        if (!count($items)) {
            redirect_to(route_url('cart'));
        }

        $message = '';
        if (is_post()) {
            $currentUser = auth_user();
            $payment = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
            $delivery = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : '';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

            $ids = implode(',', array_map('intval', array_keys($items)));
            $productSql = 'SELECT id, price, stock FROM ' . table_name('products') . ' WHERE id IN (' . $ids . ')';
            $products = db()->query($productSql)->fetchAll();

            $total = 0;
            foreach ($products as $p) {
                $qty = (int)$items[$p['id']];
                $total += $qty * (float)$p['price'];
            }

            $orderSql = 'INSERT INTO ' . table_name('orders') . ' (user_id, total, status, payment_method, delivery_method, notes, created_at) VALUES (:u, :t, :s, :pm, :dm, :n, NOW())';
            $stmt = db()->prepare($orderSql);
            $stmt->execute(array(
                ':u' => $currentUser['id'],
                ':t' => $total,
                ':s' => 'pendiente',
                ':pm' => $payment,
                ':dm' => $delivery,
                ':n' => $notes
            ));
            $orderId = db()->lastInsertId();

            $itemSql = 'INSERT INTO ' . table_name('order_items') . ' (order_id, product_id, quantity, unit_price) VALUES (:o, :p, :q, :u)';
            $itemStmt = db()->prepare($itemSql);
            $stockSql = 'UPDATE ' . table_name('products') . ' SET stock = stock - :q WHERE id = :id AND stock >= :q';
            $stockStmt = db()->prepare($stockSql);

            foreach ($products as $p) {
                $qty = (int)$items[$p['id']];
                if ($qty < 1) {
                    continue;
                }
                $itemStmt->execute(array(':o' => $orderId, ':p' => $p['id'], ':q' => $qty, ':u' => $p['price']));
                $stockStmt->execute(array(':q' => $qty, ':id' => $p['id']));
            }

            cart_set_items(array());
            $message = 'Pedido registrado correctamente. Tu numero de pedido es #' . $orderId;
        }

        $this->render('checkout/index', array('message' => $message));
    }
}
