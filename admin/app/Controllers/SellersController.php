<?php
require_once dirname(__DIR__) . '/Models/SellerModel.php';

class SellersController extends BaseController
{
    public function handle()
    {
        require_admin_login();

        $sellerModel = new SellerModel();
        $message = '';
        $error = '';
        $editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

        if (!$sellerModel->tableExists()) {
            $error = 'La tabla de vendedores no existe. Aplica la migración SQL antes de usar este módulo.';
        } else {
            if (is_post() && isset($_POST['add_seller'])) {
                $email = isset($_POST['email']) ? trim($_POST['email']) : '';
                $username = isset($_POST['username']) ? trim($_POST['username']) : '';
                $name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';

                if ($email === '' || $name === '' || $username === '') {
                    $error = 'Completa todos los campos del vendedor.';
                } elseif ($sellerModel->findByEmail($email)) {
                    $error = 'Ya existe un vendedor con ese correo.';
                } elseif ($sellerModel->findByUsername($username)) {
                    $error = 'El nombre de usuario ya está en uso.';
                } else {
                    $sellerModel->create(array('full_name' => $name, 'email' => $email, 'username' => $username));
                    $message = 'Vendedor creado correctamente.';
                }
            }

            if (is_post() && isset($_POST['update_seller'])) {
                $id = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 0;
                $email = isset($_POST['email']) ? trim($_POST['email']) : '';
                $username = isset($_POST['username']) ? trim($_POST['username']) : '';
                $name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';

                $existing = $sellerModel->findById($id);
                if (!$existing) {
                    $error = 'Vendedor no encontrado.';
                } elseif ($email === '' || $name === '' || $username === '') {
                    $error = 'Completa todos los campos del vendedor.';
                } else {
                    $other = $sellerModel->findByEmail($email);
                    if ($other && $other['id'] !== $id) {
                        $error = 'Ya existe otro vendedor con ese correo.';
                    } else {
                        $other = $sellerModel->findByUsername($username);
                        if ($other && $other['id'] !== $id) {
                            $error = 'El nombre de usuario ya está en uso.';
                        } else {
                            $sellerModel->update($id, array('full_name' => $name, 'email' => $email, 'username' => $username));
                            $message = 'Vendedor actualizado correctamente.';
                            $editingId = $id;
                        }
                    }
                }
            }

            if (is_post() && isset($_POST['delete_seller'])) {
                $id = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 0;
                if ($id > 0) {
                    $sellerModel->delete($id);
                    $message = 'Vendedor eliminado correctamente.';
                    if ($editingId === $id) {
                        $editingId = 0;
                    }
                }
            }
        }

        $editingSeller = $editingId > 0 ? $sellerModel->findById($editingId) : null;
        $formData = array(
            'id' => $editingSeller ? (int)$editingSeller['id'] : 0,
            'full_name' => $editingSeller ? $editingSeller['full_name'] : '',
            'email' => $editingSeller ? $editingSeller['email'] : '',
            'username' => $editingSeller ? $editingSeller['username'] : ''
        );

        $this->render('sellers', array(
            'message' => $message,
            'error' => $error,
            'rows' => $sellerModel->findAll(),
            'formData' => $formData,
            'hasTable' => $sellerModel->tableExists()
        ));
    }
}
