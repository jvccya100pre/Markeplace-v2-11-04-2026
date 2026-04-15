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

        if (isset($_GET['seller']) && is_numeric($_GET['seller'])) {
            $_SESSION['seller_id'] = (int)$_GET['seller'];
        }

        if (is_post() && isset($_POST['add_cart'])) {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

            $selectedColumns = 'id, stock';
            if (column_exists('products', 'allow_negative_stock')) {
                $selectedColumns .= ', allow_negative_stock';
            }
            $sql = 'SELECT ' . $selectedColumns . ' FROM ' . table_name('products') . ' WHERE id = :id LIMIT 1';
            $stmt = db()->prepare($sql);
            $stmt->execute(array(':id' => $productId));
            $product = $stmt->fetch();

            if ($product) {
                $items = cart_session_items();
                $existing = isset($items[$productId]) ? (int)$items[$productId] : 0;
                $allowNegative = !empty($product['allow_negative_stock']);
                if ($allowNegative) {
                    $items[$productId] = $existing + $qty;
                } else {
                    $items[$productId] = min((int)$product['stock'], $existing + $qty);
                }
                cart_set_items($items);
            }

            redirect_to(route_url('home'));
        }

        $countSql = 'SELECT COUNT(*) AS c FROM ' . table_name('products');
        $count = db()->query($countSql)->fetch();
        if ((int)$count['c'] === 0) {
            $ins = 'INSERT INTO ' . table_name('products') . ' (category_id, name, description, internal_code, stock, color, price, image_path, created_at) VALUES (:c, :n, :d, :code, :s, :color, :p, :img, NOW())';
            $st = db()->prepare($ins);
            // for ($i = 1; $i <= 25; $i++) {
            //     $cat = (($i - 1) % 5) + 1;
            //     $st->execute(array(
            //         ':c' => $cat,
            //         ':n' => 'Producto Demo ' . $i,
            //         ':d' => 'Descripcion de ejemplo para el producto ' . $i,
            //         ':code' => 'SKU-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
            //         ':s' => rand(3, 50),
            //         ':color' => 'Surtido',
            //         ':p' => rand(5, 80),
            //         ':img' => '/logo.jpg'
            //     ));
            // }
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'alphabetical';
        $letter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : '';
        if ($letter !== '' && $letter !== '0-9' && $letter !== '#' && !preg_match('/^[A-ZÑ]$/', $letter)) {
            $letter = '';
        }
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
        if ($letter === '0-9') {
            $filters[] = 'p.name REGEXP :letter_pattern';
            $params[':letter_pattern'] = '^[0-9]';
        } elseif ($letter === '#') {
            $filters[] = 'p.name REGEXP :letter_pattern';
            $params[':letter_pattern'] = '^[^A-Za-z0-9ÑñÁÉÍÓÚáéíóúÜü]';
        } elseif ($letter !== '') {
            $filters[] = 'p.name LIKE :letter';
            $params[':letter'] = $letter . '%';
        }
        $where = count($filters) ? (' WHERE ' . implode(' AND ', $filters)) : '';

        $totalSql = 'SELECT COUNT(*) AS c FROM ' . table_name('products') . ' p' . $where;
        $totalSt = db()->prepare($totalSql);
        $totalSt->execute($params);
        $totalRow = $totalSt->fetch();
        $totalItems = $totalRow ? (int)$totalRow['c'] : 0;
        $totalPages = max(1, (int)ceil($totalItems / $perPage));

        $productColumns = 'p.*';
        $orderBy = ' ORDER BY p.name ASC';
        if (column_exists('products', 'price_retail')) {
            $productColumns = 'p.*, p.price_retail, p.price_wholesale, p.show_retail, p.show_wholesale, p.allow_negative_stock, p.gallery_mode';
            if ($sort === 'price_low') {
                $orderBy = ' ORDER BY (CASE WHEN p.price_retail > 0 THEN p.price_retail ELSE p.price END) ASC, p.name ASC';
            } elseif ($sort === 'price_high') {
                $orderBy = ' ORDER BY (CASE WHEN p.price_retail > 0 THEN p.price_retail ELSE p.price END) DESC, p.name ASC';
            }
        } else {
            if ($sort === 'price_low') {
                $orderBy = ' ORDER BY p.price ASC, p.name ASC';
            } elseif ($sort === 'price_high') {
                $orderBy = ' ORDER BY p.price DESC, p.name ASC';
            }
        }

        $listSql = 'SELECT ' . $productColumns . ', c.name AS category_name FROM ' . table_name('products') . ' p INNER JOIN ' . table_name('categories') . ' c ON c.id = p.category_id' . $where . $orderBy . ' LIMIT ' . (int)$offset . ', ' . (int)$perPage;
        $listSt = db()->prepare($listSql);
        $listSt->execute($params);
        $products = $listSt->fetchAll();

        $categories = db()->query('SELECT id, name FROM ' . table_name('categories') . ' WHERE is_active = 1 ORDER BY name ASC')->fetchAll();

        $cartItems = cart_session_items();
        $cartTotal = 0;
        if (count($cartItems)) {
            $ids = implode(',', array_map('intval', array_keys($cartItems)));
            $priceSql = 'SELECT id, price' . (column_exists('products', 'price_retail') ? ', price_retail' : '') . ' FROM ' . table_name('products') . ' WHERE id IN (' . $ids . ')';
            $priceRows = db()->query($priceSql)->fetchAll();
            foreach ($priceRows as $r) {
                $id = (int)$r['id'];
                $unitPrice = isset($r['price_retail']) && $r['price_retail'] > 0 ? $r['price_retail'] : $r['price'];
                $cartTotal += ((float)$unitPrice) * (int)$cartItems[$id];
            }
        }

        $this->render('home/index', array(
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
            'letter' => $letter,
            'page' => $page,
            'totalPages' => $totalPages,
            'cartTotal' => $cartTotal,
            'sellerId' => isset($_SESSION['seller_id']) ? (int)$_SESSION['seller_id'] : 0
        ));
    }
}
