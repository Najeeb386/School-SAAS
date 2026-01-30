<?php
namespace App\Modules\School_Admin\Models;

class PayrunItemModel {
    protected $db;

    public function __construct($DB_con) {
        $this->db = $DB_con;
    }

    public function getAllByPayrun($payrun_id, $school_id) {
        $sql = 'SELECT * FROM school_payrun_items WHERE payrun_id = :payrun_id AND school_id = :sid AND deleted_at IS NULL ORDER BY id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':payrun_id' => $payrun_id, ':sid' => $school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id, $school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_payrun_items WHERE id = :id AND school_id = :sid AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $id, ':sid' => $school_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByPayrunAndStaff($payrun_id, $school_id, $staff_type, $staff_id) {
        $stmt = $this->db->prepare('
            SELECT * FROM school_payrun_items 
            WHERE payrun_id = :payrun_id AND school_id = :sid AND staff_type = :staff_type AND staff_id = :staff_id AND deleted_at IS NULL 
            LIMIT 1
        ');
        $stmt->execute([
            ':payrun_id' => $payrun_id,
            ':sid' => $school_id,
            ':staff_type' => $staff_type,
            ':staff_id' => $staff_id
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($payrun_id, $school_id, $data) {
        $stmt = $this->db->prepare('
            INSERT INTO school_payrun_items 
            (payrun_id, school_id, staff_type, staff_id, session_id, basic_salary, allowance, deduction, net_salary, payment_status, created_at, updated_at) 
            VALUES (:payrun_id, :school_id, :staff_type, :staff_id, :session_id, :basic_salary, :allowance, :deduction, :net_salary, :payment_status, NOW(), NOW())
        ');
        
        $stmt->execute([
            ':payrun_id' => $payrun_id,
            ':school_id' => $school_id,
            ':staff_type' => $data['staff_type'] ?? null,
            ':staff_id' => $data['staff_id'] ?? null,
            ':session_id' => $data['session_id'] ?? null,
            ':basic_salary' => $data['basic_salary'] ?? 0,
            ':allowance' => $data['allowance'] ?? 0,
            ':deduction' => $data['deduction'] ?? 0,
            ':net_salary' => $data['net_salary'] ?? 0,
            ':payment_status' => 'pending',
        ]);
        
        return intval($this->db->lastInsertId());
    }

    public function update($id, $school_id, $data) {
        $fields = [];
        $params = [':id' => $id, ':sid' => $school_id];
        
        if (isset($data['basic_salary'])) { $fields[] = 'basic_salary = :basic_salary'; $params[':basic_salary'] = $data['basic_salary']; }
        if (isset($data['allowance'])) { $fields[] = 'allowance = :allowance'; $params[':allowance'] = $data['allowance']; }
        if (isset($data['deduction'])) { $fields[] = 'deduction = :deduction'; $params[':deduction'] = $data['deduction']; }
        if (isset($data['net_salary'])) { $fields[] = 'net_salary = :net_salary'; $params[':net_salary'] = $data['net_salary']; }
        if (isset($data['payment_status'])) { $fields[] = 'payment_status = :payment_status'; $params[':payment_status'] = $data['payment_status']; }
        if (isset($data['payment_date'])) { $fields[] = 'payment_date = :payment_date'; $params[':payment_date'] = $data['payment_date']; }
        
        if (empty($fields)) return false;
        
        $sql = 'UPDATE school_payrun_items SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND school_id = :sid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $school_id) {
        $stmt = $this->db->prepare('UPDATE school_payrun_items SET deleted_at = NOW() WHERE id = :id AND school_id = :sid');
        return $stmt->execute([':id' => $id, ':sid' => $school_id]);
    }

    public function updatePaymentStatus($id, $school_id, $payment_status, $payment_date = null) {
        $data = ['payment_status' => $payment_status];
        if ($payment_date) {
            $data['payment_date'] = $payment_date;
        }
        return $this->update($id, $school_id, $data);
    }

    public function getPaymentSummary($payrun_id, $school_id) {
        $stmt = $this->db->prepare('
            SELECT 
                payment_status,
                COUNT(*) as count,
                SUM(net_salary) as total
            FROM school_payrun_items 
            WHERE payrun_id = :payrun_id AND school_id = :sid AND deleted_at IS NULL
            GROUP BY payment_status
        ');
        $stmt->execute([':payrun_id' => $payrun_id, ':sid' => $school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBulkPaymentItems($payrun_id, $school_id, $limit = 50, $offset = 0) {
        $sql = 'SELECT * FROM school_payrun_items WHERE payrun_id = :payrun_id AND school_id = :sid AND deleted_at IS NULL AND payment_status = "pending" ORDER BY staff_id LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':payrun_id', $payrun_id, \PDO::PARAM_INT);
        $stmt->bindValue(':sid', $school_id, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function markAllPaid($payrun_id, $school_id, $payment_date) {
        $stmt = $this->db->prepare('
            UPDATE school_payrun_items 
            SET payment_status = "paid", payment_date = :payment_date, updated_at = NOW() 
            WHERE payrun_id = :payrun_id AND school_id = :sid AND deleted_at IS NULL
        ');
        return $stmt->execute([
            ':payrun_id' => $payrun_id,
            ':sid' => $school_id,
            ':payment_date' => $payment_date
        ]);
    }
}
