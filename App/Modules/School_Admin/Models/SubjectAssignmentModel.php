<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class SubjectAssignmentModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(int $school_id, int $subject_id, int $class_id, ?int $section_id = null, ?int $teacher_id = null, ?int $session_id = null) {
        $sql = "INSERT INTO school_subject_assignments (school_id, subject_id, class_id, section_id, teacher_id, session_id, created_at) VALUES (:sid, :sub, :cid, :sec, :tid, :sess, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':sid' => $school_id,
            ':sub' => $subject_id,
            ':cid' => $class_id,
            ':sec' => $section_id,
            ':tid' => $teacher_id,
            ':sess' => $session_id
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listByClass(int $school_id, int $class_id, ?int $session_id = null) {
        $sql = 'SELECT a.*, s.name AS subject_name, t.name AS teacher_name FROM school_subject_assignments a JOIN school_subjects s ON a.subject_id = s.id LEFT JOIN school_teachers t ON a.teacher_id = t.id WHERE a.school_id = :sid AND a.class_id = :cid';
        $params = [':sid' => $school_id, ':cid' => $class_id];
        if ($session_id) {
            $sql .= ' AND a.session_id = :sess';
            $params[':sess'] = $session_id;
        }
        $sql .= ' ORDER BY s.name ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
