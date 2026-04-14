<?php
require_once __DIR__ . '/../../admin/app/Models/TestimonialModel.php';

class TestimonialController extends Controller {
    private $testimonialModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->testimonialModel = new TestimonialModel($db);
    }

    public function index() {
        $testimonials = $this->testimonialModel->getApproved();
        $this->render('testimonial/index', ['testimonials' => $testimonials]);
    }

    public function create() {
        if (!auth_user()) {
            $this->redirect('/login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $_POST['message'] ?? '';
            $photo = $this->uploadPhoto();

            if ($message && $photo) {
                $userId = auth_user()['id'];
                if ($this->testimonialModel->create($userId, $message, $photo)) {
                    $this->redirect('/testimonial.php?success=1');
                } else {
                    $error = 'Error al guardar el testimonio.';
                }
            } else {
                $error = 'Mensaje y foto son obligatorios.';
            }
        }

        $this->render('testimonial/create', ['error' => $error ?? null]);
    }

    private function uploadPhoto() {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/testimonials/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                return $filePath;
            }
        }
        return null;
    }
}
?>