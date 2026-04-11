<?php
require_once __DIR__ . '/../Core/Controller.php';

class HomeController extends Controller
{
    public function index()
    {
        if (is_post() && isset($_POST['clear_cart'])) {
            cart_set_items(array());
            redirect_to(route_url('home'));
        }

        if (is_post() && isset($_POST['add_cart'])) {
            if (!auth_user()) {
                redirect_to(route_url('login', array('next' => route_url('home'))));
            }

            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

            $sql = 'SELECT id, stock FROM ' . table_name('products') . ' WHERE id = :id LIMIT 1';
            $stmt = db()->prepare($sql);
            $stmt->execute(array(':id' => $productId));
            $product = $stmt->fetch();

            if ($product) {
                $items = cart_session_items();
                $existing = isset($items[$productId]) ? (int)$items[$productId] : 0;
                $newQty = min((int)$product['stock'], $existing + $qty);
                $items[$productId] = $newQty;
                cart_set_items($items);
            }

            redirect_to(route_url('home'));
        }

        $countSql = 'SELECT COUNT(*) AS c FROM ' . table_name('products');
        $count = db()->query($countSql)->fetch();
        if ((int)$count['c'] === 0) {
            $ins = 'INSERT INTO ' . table_name('products') . ' (category_id, name, description, internal_code, stock, color, price, image_path, created_at) VALUES (:c, :n, :d, :code, :s, :color, :p, :img, NOW())';
            $st = db()->prepare($ins);
            for ($i = 1; $i <= 25; $i++) {
                $cat = (($i - 1) % 5) + 1;
                $st->execute(array(
                    ':c' => $cat,
                    ':n' => 'Producto Demo ' . $i,
                    ':d' => 'Descripcion de ejemplo para el producto ' . $i,
                    ':code' => 'SKU-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    ':s' => rand(3, 50),
                    ':color' => 'Surtido',
                    ':p' => rand(5, 80),
                    ':img' => '/logo.jpg'
                ));
            }
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $filters = array();
        $params = array();
        if ($search !== '') {
            $filters[] = '(p.name LIKE :q OR p.description LIKE :q OR p.internal_code LIKE :q)';
            $params[':q'] = '%' . $search . '%';
        }
        if ($category > 0) {
            $filters[] = 'p.category_id = :cat';
            $params[':cat'] = $category;
        }
        $where = count($filters) ? (' WHERE ' . implode(' AND ', $filters)) : '';

        $totalSql = 'SELECT COUNT(*) AS c FROM ' . table_name('products') . ' p' . $where;
        $totalSt = db()->prepare($totalSql);
        $totalSt->execute($params);
        $totalRow = $totalSt->fetch();
        $totalItems = $totalRow ? (int)$totalRow['c'] : 0;
        $totalPages = max(1, (int)ceil($totalItems / $perPage));

        $listSql = 'SELECT p.*, c.name AS category_name FROM ' . table_name('products') . ' p INNER JOIN ' . table_name('categories') . ' c ON c.id = p.category_id' . $where . ' ORDER BY p.id DESC LIMIT ' . (int)$offset . ', ' . (int)$perPage;
        $listSt = db()->prepare($listSql);
        $listSt->execute($params);
        $products = $listSt->fetchAll();

        $categories = db()->query('SELECT id, name FROM ' . table_name('categories') . ' WHERE is_active = 1 ORDER BY name ASC')->fetchAll();

        $cartItems = cart_session_items();
        $cartTotal = 0;
        if (count($cartItems)) {
            $ids = implode(',', array_map('intval', array_keys($cartItems)));
            $priceRows = db()->query('SELECT id, price FROM ' . table_name('products') . ' WHERE id IN (' . $ids . ')')->fetchAll();
            foreach ($priceRows as $r) {
                $id = (int)$r['id'];
                $cartTotal += ((float)$r['price']) * (int)$cartItems[$id];
            }
        }

        $this->render('home/index', array(
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'cartTotal' => $cartTotal
        ));
    }
}
