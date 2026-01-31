<?php
namespace App\Modules\School_Admin\Models;

class StaffSalaryModel {
    protected $db;

    public function __construct($DB_con) {
        $this->db = $DB_con;
    }

    public function getAll($school_id, $session_id = null) {
        $sql = 'SELECT 
                    s.*,
                    CASE 
                        WHEN s.staff_type = "employee" THEN e.name
                        WHEN s.staff_type = "teacher" THEN t.name
                        ELSE NULL
                    END AS staff_name,
                    CASE 
                        WHEN s.staff_type = "employee" THEN e.role_id
                        WHEN s.staff_type = "teacher" THEN t.role
                        ELSE NULL
                    END AS staff_role
                FROM school_staff_salaries s
                LEFT JOIN employees e ON s.staff_type = "employee" AND s.staff_id = e.id AND e.school_id = :sid
                LEFT JOIN school_teachers t ON s.staff_type = "teacher" AND s.staff_id = t.id AND t.school_id = :sid
                WHERE s.school_id = :sid AND s.deleted_at IS NULL';
        
        $params = [':sid' => $school_id];
        
        if ($session_id) {
            $sql .= ' AND s.session_id = :session_id';
            $params[':session_id'] = $session_id;
        }
        
        $sql .= ' ORDER BY s.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByStaffTypeAndSession($school_id, $staff_type, $session_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_staff_salaries WHERE school_id = :sid AND staff_type = :st AND session_id = :ses AND deleted_at IS NULL ORDER BY staff_id ASC');
        $stmt->execute([':sid' => $school_id, ':st' => $staff_type, ':ses' => $session_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id, $school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_staff_salaries WHERE id = :id AND school_id = :sid AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $id, ':sid' => $school_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByStaffAndSession($school_id, $staff_type, $staff_id, $session_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_staff_salaries WHERE school_id = :sid AND staff_type = :st AND staff_id = :stid AND session_id = :ses AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':sid' => $school_id, ':st' => $staff_type, ':stid' => $staff_id, ':ses' => $session_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($school_id, $data) {
        $stmt = $this->db->prepare('INSERT INTO school_staff_salaries (school_id,staff_type,staff_id,session_id,basic_salary,allowance,deduction,effective_from,status,created_by,updated_by,created_at,updated_at) VALUES (:school_id,:staff_type,:staff_id,:session_id,:basic,:allowance,:deduction,:eff,:status,:created_by,:updated_by,NOW(),NOW())');
        $stmt->execute([
            ':school_id' => $school_id,
            ':staff_type' => $data['staff_type'] ?? null,
            ':staff_id' => $data['staff_id'] ?? null,
            ':session_id' => $data['session_id'] ?? null,
            ':basic' => $data['basic_salary'] ?? 0,
            ':allowance' => $data['allowance'] ?? 0,
            ':deduction' => $data['deduction'] ?? 0,
            ':eff' => $data['effective_from'] ?? date('Y-m-d'),
            ':status' => isset($data['status']) ? intval($data['status']) : 1,
            ':created_by' => $data['created_by'] ?? null,
            ':updated_by' => $data['updated_by'] ?? null,
        ]);
        return intval($this->db->lastInsertId());
    }

    public function update($id, $school_id, $data) {
        $fields = [];
        $params = [':id' => $id, ':sid' => $school_id];
        
        if (isset($data['basic_salary'])) { $fields[] = 'basic_salary = :basic'; $params[':basic'] = $data['basic_salary']; }
        if (isset($data['allowance'])) { $fields[] = 'allowance = :allowance'; $params[':allowance'] = $data['allowance']; }
        if (isset($data['deduction'])) { $fields[] = 'deduction = :deduction'; $params[':deduction'] = $data['deduction']; }
        if (isset($data['effective_from'])) { $fields[] = 'effective_from = :eff'; $params[':eff'] = $data['effective_from']; }
        if (isset($data['status'])) { $fields[] = 'status = :status'; $params[':status'] = intval($data['status']); }
        if (isset($data['updated_by'])) { $fields[] = 'updated_by = :updated_by'; $params[':updated_by'] = $data['updated_by']; }
        
        if (empty($fields)) return false;
        
        $sql = 'UPDATE school_staff_salaries SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND school_id = :sid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $school_id) {
        $stmt = $this->db->prepare('UPDATE school_staff_salaries SET deleted_at = NOW() WHERE id = :id AND school_id = :sid');
        return $stmt->execute([':id' => $id, ':sid' => $school_id]);
    }

    public function calculateNetSalary($basic, $allowance, $deduction) {
        return $basic + $allowance - $deduction;
    }

    public function getUnfinalisedStaff($school_id) {
        // Get employees without salary records
        $sql = 'SELECT 
                    e.id as staff_id,
                    "employee" as staff_type,
                    e.name,
                    e.email
                FROM employees e
                WHERE e.school_id = :sid AND e.status = 1
                AND e.id NOT IN (
                    SELECT staff_id FROM school_staff_salaries 
                    WHERE school_id = :sid AND staff_type = "employee" AND deleted_at IS NULL
                )
                UNION
                SELECT 
                    t.id as staff_id,
                    "teacher" as staff_type,
                    t.name,
                    t.email
                FROM school_teachers t
                WHERE t.school_id = :sid AND t.status = 1
                AND t.id NOT IN (
                    SELECT staff_id FROM school_staff_salaries 
                    WHERE school_id = :sid AND staff_type = "teacher" AND deleted_at IS NULL
                )
                ORDER BY staff_type, name';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
