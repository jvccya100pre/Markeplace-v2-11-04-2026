<?php
require_once __DIR__ . '/../Models/TestimonialModel.php';

class TestimonialsController extends BaseController
{
    private $testimonialModel;

    public function __construct()
    {
        $this->testimonialModel = new TestimonialModel();
    }

    public function handle()
    {
        require_admin_login();

        $action = isset($_GET['action']) ? trim($_GET['action']) : 'index';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($action === 'approve' && $id > 0) {
            $this->approve($id);
            return;
        }

        if ($action === 'reject' && $id > 0) {
            $this->reject($id);
            return;
        }

        $this->index();
    }

    public function index()
    {
        $testimonials = table_exists('testimonials') ? $this->testimonialModel->getAll() : array();
        $this->render('testimonials', array('testimonials' => $testimonials));
    }

    public function approve($id)
    {
        $this->testimonialModel->approve($id);
        redirect_to(app_url('/testimonials.php'));
    }

    public function reject($id)
    {
        $this->testimonialModel->reject($id);
        redirect_to(app_url('/testimonials.php'));
    }
}