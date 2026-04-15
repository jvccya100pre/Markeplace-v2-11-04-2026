<?php

class TestimonialModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ? $db : db();
    }

    public function getAll()
    {
        $sql = 'SELECT t.*, COALESCE(NULLIF(u.full_name, ""), u.email) AS author_name'
            . ' FROM ' . table_name('testimonials') . ' t'
            . ' INNER JOIN ' . table_name('users') . ' u ON u.id = t.user_id'
            . ' ORDER BY t.created_at DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function getApproved()
    {
        $sql = 'SELECT t.*, COALESCE(NULLIF(u.full_name, ""), u.email) AS author_name'
            . ' FROM ' . table_name('testimonials') . ' t'
            . ' INNER JOIN ' . table_name('users') . ' u ON u.id = t.user_id'
            . ' WHERE t.approved = 1 ORDER BY t.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function approve($id)
    {
        $stmt = $this->db->prepare('UPDATE ' . table_name('testimonials') . ' SET approved = 1 WHERE id = ?');
        return $stmt->execute(array($id));
    }

    public function reject($id)
    {
        $stmt = $this->db->prepare('DELETE FROM ' . table_name('testimonials') . ' WHERE id = ?');
        return $stmt->execute(array($id));
    }

    public function create($userId, $message, $photo)
    {
        $stmt = $this->db->prepare('INSERT INTO ' . table_name('testimonials') . ' (user_id, message, photo, approved, created_at) VALUES (?, ?, ?, 0, NOW())');
        return $stmt->execute(array($userId, $message, $photo));
    }
}
