<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class ClassModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(int $school_id, int $session_id, string $class_name, ?string $class_code = null, ?string $grade_level = null, int $class_order = 0, ?string $description = null, string $status = 'active') {
        $sql = "INSERT INTO school_classes (school_id, session_id, class_name, class_code, grade_level, class_order, description, status, created_at) VALUES (:school_id, :session_id, :class_name, :class_code, :grade_level, :class_order, :description, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':school_id' => $school_id,
            ':session_id' => $session_id,
            ':class_name' => $class_name,
            ':class_code' => $class_code,
            ':grade_level' => $grade_level,
            ':class_order' => $class_order,
            ':description' => $description,
            ':status' => $status
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM school_classes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data) {
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $k => $v) {
            $fields[] = "`$k` = :$k";
            $params[":$k"] = $v;
        }
        if (empty($fields)) return false;
        $sql = "UPDATE school_classes SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM school_classes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
