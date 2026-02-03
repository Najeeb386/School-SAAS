<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class ConcessionModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(array $data) : int {
        $sql = "INSERT INTO school_student_fees_concessions (school_id, admission_no, session_id, type, value_type, value, applies_to, start_month, end_month, status, created_at) VALUES (:school_id, :admission_no, :session_id, :type, :value_type, :value, :applies_to, :start_month, :end_month, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':school_id' => $data['school_id'],
            ':admission_no' => $data['admission_no'],
            ':session_id' => $data['session_id'],
            ':type' => $data['type'],
            ':value_type' => $data['value_type'],
            ':value' => $data['value'],
            ':applies_to' => $data['applies_to'],
            ':start_month' => $data['start_month'],
            ':end_month' => $data['end_month'] ?: null,
            ':status' => $data['status'] ?? 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listBySchool(int $school_id, array $filters = []) : array {
        $where = 'c.school_id = :school_id';
        $params = [':school_id' => $school_id];
        if (!empty($filters['session_id'])) { $where .= ' AND c.session_id = :session_id'; $params[':session_id'] = $filters['session_id']; }
        if (!empty($filters['status'])) { $where .= ' AND c.status = :status'; $params[':status'] = $filters['status']; }
        if (!empty($filters['admission_no'])) { $where .= ' AND c.admission_no = :admission_no'; $params[':admission_no'] = $filters['admission_no']; }

        $sql = "SELECT c.*, s.name AS session_name, st.first_name, st.last_name FROM school_student_fees_concessions c LEFT JOIN school_sessions s ON s.id = c.session_id LEFT JOIN school_students st ON st.admission_no = c.admission_no AND st.school_id = c.school_id WHERE $where ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findStudentByAdmission(int $school_id, string $admission_no) {
        $sql = 'SELECT id, first_name, last_name, admission_no FROM school_students WHERE school_id = :sid AND admission_no = :ad LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid'=>$school_id, ':ad'=>$admission_no]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function applyBulkToClass(int $school_id, int $session_id, int $class_id, array $concessionData) : int {
        // find students by enrollment for the class/session
        $sql = 'SELECT e.student_id, st.admission_no FROM school_student_enrollments e JOIN school_students st ON st.id = e.student_id WHERE e.school_id = :sid AND e.session_id = :sess AND e.class_id = :cid';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid'=>$school_id, ':sess'=>$session_id, ':cid'=>$class_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        $ins = $this->db->prepare('INSERT INTO school_student_fees_concessions (school_id, admission_no, session_id, type, value_type, value, applies_to, start_month, end_month, status, created_at) VALUES (:school_id, :admission_no, :session_id, :type, :value_type, :value, :applies_to, :start_month, :end_month, :status, NOW())');
        foreach ($rows as $r) {
            $ins->execute([
                ':school_id'=>$school_id,
                ':admission_no'=>$r['admission_no'],
                ':session_id'=>$concessionData['session_id'],
                ':type'=>$concessionData['type'],
                ':value_type'=>$concessionData['value_type'],
                ':value'=>$concessionData['value'],
                ':applies_to'=>$concessionData['applies_to'],
                ':start_month'=>$concessionData['start_month'],
                ':end_month'=>$concessionData['end_month'] ?: null,
                ':status'=>$concessionData['status'] ?? 1
            ]);
            $count++;
        }
        return $count;
    }
}
