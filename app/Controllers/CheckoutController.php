<?php
require_once __DIR__ . '/../Core/Controller.php';

class CheckoutController extends Controller
{
    public function index()
    {
        require_user_login();
        $currentUser = auth_user();

        $items = cart_session_items();
        if (!count($items)) {
            redirect_to(route_url('cart'));
        }

        $ids = implode(',', array_map('intval', array_keys($items)));
        $productColumns = 'id, price, stock';
        if (column_exists('products', 'allow_negative_stock')) {
            $productColumns .= ', allow_negative_stock';
        }
        if (column_exists('products', 'delivery_enabled')) {
            $productColumns .= ', delivery_enabled';
        }
        $productSql = 'SELECT ' . $productColumns . ' FROM ' . table_name('products') . ' WHERE id IN (' . $ids . ')';
        $products = db()->query($productSql)->fetchAll();

        $paymentDetails = $this->getPaymentDetails();
        $selectedPayment = 'deposito bancario';
        $selectedDelivery = 'personal';
        $notes = '';
        $transferReference = '';

        $hasDelivery = false;
        foreach ($products as $p) {
            if (isset($p['delivery_enabled']) && $p['delivery_enabled']) {
                $hasDelivery = true;
                break;
            }
        }

        $message = '';
        $error = '';
        if (is_post()) {
            $selectedPayment = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'deposito bancario';
            $selectedDelivery = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : 'personal';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            $transferReference = isset($_POST['transfer_reference']) ? trim($_POST['transfer_reference']) : '';

            if (!$this->hasPaymentDetails($selectedPayment, $paymentDetails)) {
                $error = 'El metodo de pago seleccionado no tiene datos configurados. Contacta al administrador o elige otro metodo.';
            } elseif ($transferReference === '') {
                $error = 'Debes indicar los ultimos digitos de la transferencia.';
            }
        }

        if (is_post() && $error === '') {
            $proofResult = $this->storePaymentProof(isset($_FILES['payment_proof']) ? $_FILES['payment_proof'] : null);
            if (!$proofResult['ok']) {
                $error = $proofResult['error'];
            }
        }

        if (is_post() && $error === '') {
            $proofPath = $proofResult['path'];

            $total = 0;
            foreach ($products as $p) {
                $qty = (int)$items[$p['id']];
                $total += $qty * (float)$p['price'];
            }

            $orderColumns = 'user_id, total, status, payment_method, notes, created_at';
            $orderValues = ':u, :t, :s, :pm, :n, NOW()';
            $notesWithPaymentData = $notes;
            $notesWithPaymentData .= ($notesWithPaymentData !== '' ? "\n" : '') . 'Ultimos digitos de transferencia: ' . $transferReference;
            $notesWithPaymentData .= "\nComprobante: " . $proofPath;
            $params = array(
                ':u' => $currentUser['id'],
                ':t' => $total,
                ':s' => 'pendiente',
                ':pm' => $selectedPayment,
                ':n' => $notesWithPaymentData
            );

            if (column_exists('orders', 'transfer_reference')) {
                $orderColumns .= ', transfer_reference';
                $orderValues .= ', :tr';
                $params[':tr'] = $transferReference;
            }
            if (column_exists('orders', 'payment_proof_image')) {
                $orderColumns .= ', payment_proof_image';
                $orderValues .= ', :proof';
                $params[':proof'] = $proofPath;
            }
            if (column_exists('orders', 'delivery_method')) {
                $orderColumns .= ', delivery_method';
                $orderValues .= ', :dm';
                $params[':dm'] = $selectedDelivery;
            }
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

            $adminEmail = isset($GLOBALS['config']['mail']['from']) ? $GLOBALS['config']['mail']['from'] : get_setting('company_email', '');
            if ($adminEmail !== '') {
                $subject = 'Nuevo pedido confirmado #' . $orderId;
                $html = '<h3>Cliente confirmó un pedido</h3>'
                    . '<p><strong>Pedido:</strong> #' . (int)$orderId . '</p>'
                    . '<p><strong>Cliente:</strong> ' . esc($currentUser['name']) . ' (' . esc($currentUser['email']) . ')</p>'
                    . '<p><strong>Total:</strong> ' . number_format((float)$total, 2) . ' VES</p>'
                    . '<p><strong>Pago:</strong> ' . esc($selectedPayment) . '</p>'
                    . '<p><strong>Referencia:</strong> ' . esc($transferReference) . '</p>'
                    . '<p><strong>Comprobante:</strong> ' . esc($proofPath) . '</p>'
                    . '<p><strong>Entrega:</strong> ' . esc($selectedDelivery) . '</p>';
                $text = 'Cliente confirmó un pedido' . "\n"
                    . 'Pedido: #' . (int)$orderId . "\n"
                    . 'Cliente: ' . $currentUser['name'] . ' (' . $currentUser['email'] . ')' . "\n"
                    . 'Total: ' . number_format((float)$total, 2) . ' VES' . "\n"
                    . 'Pago: ' . $selectedPayment . "\n"
                    . 'Referencia: ' . $transferReference . "\n"
                    . 'Comprobante: ' . $proofPath . "\n"
                    . 'Entrega: ' . $selectedDelivery;
                send_smtp_mail($adminEmail, 'Administrador', $subject, $html, $text);
            }

            cart_set_items(array());
            $message = 'Pedido registrado correctamente. Tu numero de pedido es #' . $orderId;
        }

        $this->render('checkout/index', array(
            'message' => $message,
            'error' => $error,
            'hasDelivery' => $hasDelivery,
            'paymentDetails' => $paymentDetails,
            'selectedPayment' => $selectedPayment,
            'selectedDelivery' => $selectedDelivery,
            'notes' => $notes,
            'transferReference' => $transferReference,
        ));
    }

    private function storePaymentProof($file)
    {
        if (!is_array($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return array('ok' => false, 'error' => 'Debes cargar una imagen de comprobante en formato JPG o PNG.', 'path' => '');
        }

        $originalName = isset($file['name']) ? (string)$file['name'] : '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png');
        if (!in_array($extension, $allowedExtensions, true)) {
            return array('ok' => false, 'error' => 'Formato de comprobante no valido. Solo se permite .jpg, .jpeg y .png.', 'path' => '');
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/payment_proofs';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $safeBaseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $targetName = uniqid('proof_', true) . '_' . $safeBaseName . '.' . $extension;
        $targetPath = $uploadDir . '/' . $targetName;

        if (!@move_uploaded_file($file['tmp_name'], $targetPath)) {
            return array('ok' => false, 'error' => 'No se pudo guardar la imagen del comprobante. Intenta nuevamente.', 'path' => '');
        }

        return array('ok' => true, 'error' => '', 'path' => '/uploads/payment_proofs/' . $targetName);
    }

    private function getPaymentDetails()
    {
        return array(
            'deposito bancario' => array(
                'label' => 'Deposito bancario',
                'fields' => array(
                    'Banco' => get_setting('pm_bank', ''),
                    'Documento' => trim(get_setting('pm_identity_type', '') . ' ' . get_setting('pm_identity_number', '')),
                    'Telefono' => trim(get_setting('pm_phone_prefix', '') . ' ' . get_setting('pm_phone_number', '')),
                ),
            ),
            'paypal' => array(
                'label' => 'Paypal',
                'fields' => array(
                    'Correo' => get_setting('paypal_email', ''),
                ),
            ),
            'binance' => array(
                'label' => 'Binance',
                'fields' => array(
                    'UID o correo' => get_setting('binance_uid', ''),
                ),
            ),
        );
    }

    private function hasPaymentDetails($paymentMethod, $paymentDetails)
    {
        if (!isset($paymentDetails[$paymentMethod])) {
            return false;
        }

        foreach ($paymentDetails[$paymentMethod]['fields'] as $value) {
            if (trim((string)$value) !== '') {
                return true;
            }
        }

        return false;
    }
}
