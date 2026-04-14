<?php
class TestimonialModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT t.*, u.username FROM demo_markeplacev1_testimonials t JOIN demo_markeplacev1_users u ON t.user_id = u.id ORDER BY t.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApproved() {
        $stmt = $this->db->prepare("SELECT t.*, u.username FROM demo_markeplacev1_testimonials t JOIN demo_markeplacev1_users u ON t.user_id = u.id WHERE t.approved = 1 ORDER BY t.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve($id) {
        $stmt = $this->db->prepare("UPDATE demo_markeplacev1_testimonials SET approved = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function reject($id) {
        $stmt = $this->db->prepare("DELETE FROM demo_markeplacev1_testimonials WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function create($userId, $message, $photo) {
        $stmt = $this->db->prepare("INSERT INTO demo_markeplacev1_testimonials (user_id, message, photo) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $message, $photo]);
    }
}
?>