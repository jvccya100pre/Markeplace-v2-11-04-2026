<?php
require_once __DIR__ . '/../Models/TestimonialModel.php';

class TestimonialsController extends BaseController {
    private $testimonialModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->testimonialModel = new TestimonialModel($db);
    }

    public function index() {
        $this->requireAuth();
        $testimonials = $this->testimonialModel->getAll();
        $this->render('testimonials', ['testimonials' => $testimonials]);
    }

    public function approve($id) {
        $this->requireAuth();
        if ($this->testimonialModel->approve($id)) {
            $this->redirect('/admin/testimonials.php');
        } else {
            echo "Error approving testimonial.";
        }
    }

    public function reject($id) {
        $this->requireAuth();
        if ($this->testimonialModel->reject($id)) {
            $this->redirect('/admin/testimonials.php');
        } else {
            echo "Error rejecting testimonial.";
        }
    }
}
?>