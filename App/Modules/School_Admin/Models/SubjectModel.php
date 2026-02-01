<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class SubjectModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(int $school_id, string $name, ?int $teacher_id = null, string $status = 'active') {
        $sql = "INSERT INTO school_subjects (school_id, name, teacher_id, status, created_at) VALUES (:school_id, :name, :teacher_id, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':name' => $name,
            ':teacher_id' => $teacher_id,
            ':status' => $status
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM school_subjects WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listBySchool(int $school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_subjects WHERE school_id = :sid ORDER BY name ASC');
        $stmt->execute([':sid' => $school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
