<?php
require_once __DIR__ . '/../Models/TestimonialModel.php';

class TestimonialController extends Controller
{
    private $testimonialModel;

    private function testimonialModel()
    {
        if ($this->testimonialModel === null) {
            $this->testimonialModel = new TestimonialModel();
        }

        return $this->testimonialModel;
    }

    public function index()
    {
        $testimonials = table_exists('testimonials') ? $this->testimonialModel()->getApproved() : array();
        $this->render('testimonial/index', array('testimonials' => $testimonials));
    }

    public function create()
    {
        require_user_login();

        $error = '';
        $success = !is_post() && isset($_GET['success']) && (string)$_GET['success'] === '1';

        if (is_post()) {
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            $photo = $this->uploadPhoto();

            if ($message === '' || $photo === null) {
                $error = 'Mensaje y foto son obligatorios.';
            } else {
                $user = auth_user();
                if ($user && $this->testimonialModel()->create((int)$user['id'], $message, $photo)) {
                    redirect_to(route_url('testimonial_create', array('success' => 1)));
                }

                $error = 'Error al guardar el testimonio.';
            }
        }

        $this->render('testimonial/create', array('error' => $error, 'success' => $success));
    }

    private function uploadPhoto()
    {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../uploads/testimonials';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            return null;
        }

        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $fileName = uniqid('testimonial_', true) . '.' . $extension;
        $filePath = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
            return null;
        }

        return app_url('/uploads/testimonials/' . $fileName);
    }
}