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
            $productColumns = 'id, price, stock';
            if (column_exists('products', 'allow_negative_stock')) {
                $productColumns .= ', allow_negative_stock';
            }
            $productSql = 'SELECT ' . $productColumns . ' FROM ' . table_name('products') . ' WHERE id IN (' . $ids . ')';
            $products = db()->query($productSql)->fetchAll();

            $total = 0;
            foreach ($products as $p) {
                $qty = (int)$items[$p['id']];
                $total += $qty * (float)$p['price'];
            }

            $orderColumns = 'user_id, total, status, payment_method, delivery_method, notes, created_at';
            $orderValues = ':u, :t, :s, :pm, :dm, :n, NOW()';
            $params = array(
                ':u' => $currentUser['id'],
                ':t' => $total,
                ':s' => 'pendiente',
                ':pm' => $payment,
                ':dm' => $delivery,
                ':n' => $notes
            );
            if (column_exists('orders', 'seller_id') && isset($_SESSION['seller_id']) && (int)$_SESSION['seller_id'] > 0) {
                $orderColumns .= ', seller_id';
                $orderValues .= ', :seller_id';
                $params[':seller_id'] = (int)$_SESSION['seller_id'];
            }

            $orderSql = 'INSERT INTO ' . table_name('orders') . ' (' . $orderColumns . ') VALUES (' . $orderValues . ')';
            $stmt = db()->prepare($orderSql);
            $stmt->execute($params);
            $orderId = db()->lastInsertId();

            $itemSql = 'INSERT INTO ' . table_name('order_items') . ' (order_id, product_id, quantity, unit_price) VALUES (:o, :p, :q, :u)';
            $itemStmt = db()->prepare($itemSql);

            foreach ($products as $p) {
                $qty = (int)$items[$p['id']];
                if ($qty < 1) {
                    continue;
                }
                $itemStmt->execute(array(':o' => $orderId, ':p' => $p['id'], ':q' => $qty, ':u' => $p['price']));

                $currentStock = (int)$p['stock'];
                $allowNegative = !empty($p['allow_negative_stock']);
                $stockSql = 'UPDATE ' . table_name('products') . ' SET stock = stock - :q WHERE id = :id' . ($allowNegative ? '' : ' AND stock >= :q');
                $stockStmt = db()->prepare($stockSql);
                $stockStmt->execute(array(':q' => $qty, ':id' => $p['id']));

                if ($allowNegative && $currentStock - $qty < 0) {
                    $adminEmail = get_setting('company_email', '');
                    if ($adminEmail !== '') {
                        @mail($adminEmail, 'Stock negativo en producto', 'El producto ID ' . $p['id'] . ' ha quedado en stock negativo tras un pedido. Stock anterior: ' . $currentStock . ', cantidad vendida: ' . $qty . '.');
                    }
                }
            }

            cart_set_items(array());
            $message = 'Pedido registrado correctamente. Tu numero de pedido es #' . $orderId;
        }

        $this->render('checkout/index', array('message' => $message));
    }
}
