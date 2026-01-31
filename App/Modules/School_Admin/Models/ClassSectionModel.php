<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class ClassSectionModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(int $school_id, int $session_id, int $class_id, string $section_name, ?string $section_code = null, ?string $room_number = null, ?int $capacity = null, ?int $class_teacher_id = null, string $status = 'active') {
        $sql = "INSERT INTO school_class_sections (school_id, session_id, class_id, section_name, section_code, room_number, capacity, class_teacher_id, current_enrollment, status, created_at) VALUES (:school_id, :session_id, :class_id, :section_name, :section_code, :room_number, :capacity, :class_teacher_id, 0, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':session_id' => $session_id,
            ':class_id' => $class_id,
            ':section_name' => $section_name,
            ':section_code' => $section_code,
            ':room_number' => $room_number,
            ':capacity' => $capacity,
            ':class_teacher_id' => $class_teacher_id,
            ':status' => $status
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByClassId(int $class_id) {
        $stmt = $this->db->prepare("SELECT * FROM school_class_sections WHERE class_id = :cid");
        $stmt->execute([':cid' => $class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByClassId(int $class_id) {
        $stmt = $this->db->prepare("DELETE FROM school_class_sections WHERE class_id = :cid");
        return $stmt->execute([':cid' => $class_id]);
    }
}
