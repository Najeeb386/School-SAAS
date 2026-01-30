<?php
namespace App\Modules\School_Admin\Models;

class PayrunModel {
    protected $db;

    public function __construct($DB_con) {
        $this->db = $DB_con;
    }

    public function getAll($school_id, $session_id = null) {
        $sql = 'SELECT * FROM school_payruns WHERE school_id = :sid AND deleted_at IS NULL';
        $params = [':sid' => $school_id];
        
        if ($session_id) {
            $sql .= ' AND session_id = :session_id';
            $params[':session_id'] = $session_id;
        }
        
        $sql .= ' ORDER BY pay_year DESC, pay_month DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecentPayruns($school_id, $session_id, $limit = 3) {
        $sql = 'SELECT * FROM school_payruns 
                WHERE school_id = :sid AND session_id = :ses AND deleted_at IS NULL
                ORDER BY pay_year DESC, pay_month DESC
                LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':sid', $school_id, \PDO::PARAM_INT);
        $stmt->bindValue(':ses', $session_id, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id, $school_id) {
        $stmt = $this->db->prepare('SELECT * FROM school_payruns WHERE id = :id AND school_id = :sid AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $id, ':sid' => $school_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByMonthYear($school_id, $session_id, $month, $year) {
        $stmt = $this->db->prepare('SELECT * FROM school_payruns WHERE school_id = :sid AND session_id = :ses AND pay_month = :month AND pay_year = :year AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':sid' => $school_id, ':ses' => $session_id, ':month' => $month, ':year' => $year]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($school_id, $data) {
        $stmt = $this->db->prepare('
            INSERT INTO school_payruns 
            (school_id, session_id, pay_month, pay_year, pay_period_start, pay_period_end, status, total_employees, total_amount, created_by, created_at, updated_at) 
            VALUES (:school_id, :session_id, :pay_month, :pay_year, :pay_period_start, :pay_period_end, :status, :total_employees, :total_amount, :created_by, NOW(), NOW())
        ');
        
        $stmt->execute([
            ':school_id' => $school_id,
            ':session_id' => $data['session_id'] ?? null,
            ':pay_month' => $data['pay_month'] ?? null,
            ':pay_year' => $data['pay_year'] ?? null,
            ':pay_period_start' => $data['pay_period_start'] ?? date('Y-m-01'),
            ':pay_period_end' => $data['pay_period_end'] ?? date('Y-m-t'),
            ':status' => 'draft',
            ':total_employees' => 0,
            ':total_amount' => 0.00,
            ':created_by' => $data['created_by'] ?? null,
        ]);
        
        return intval($this->db->lastInsertId());
    }

    public function update($id, $school_id, $data) {
        $fields = [];
        $params = [':id' => $id, ':sid' => $school_id];
        
        if (isset($data['status'])) { $fields[] = 'status = :status'; $params[':status'] = $data['status']; }
        if (isset($data['total_employees'])) { $fields[] = 'total_employees = :total_employees'; $params[':total_employees'] = intval($data['total_employees']); }
        if (isset($data['total_amount'])) { $fields[] = 'total_amount = :total_amount'; $params[':total_amount'] = $data['total_amount']; }
        if (isset($data['approved_by'])) { $fields[] = 'approved_by = :approved_by'; $params[':approved_by'] = $data['approved_by']; }
        if (isset($data['approval_date'])) { $fields[] = 'approval_date = :approval_date'; $params[':approval_date'] = $data['approval_date']; }
        
        if (empty($fields)) return false;
        
        $sql = 'UPDATE school_payruns SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND school_id = :sid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $school_id) {
        $stmt = $this->db->prepare('UPDATE school_payruns SET deleted_at = NOW() WHERE id = :id AND school_id = :sid');
        return $stmt->execute([':id' => $id, ':sid' => $school_id]);
    }

    public function updateStatus($id, $school_id, $status, $extra_data = []) {
        $fields = ['status = :status'];
        $params = [':id' => $id, ':sid' => $school_id, ':status' => $status];
        
        if ($status === 'approved' && isset($extra_data['approved_by'])) {
            $fields[] = 'approved_by = :approved_by, approval_date = NOW()';
            $params[':approved_by'] = $extra_data['approved_by'];
        }
        
        $sql = 'UPDATE school_payruns SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id AND school_id = :sid';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function calculatePayrunTotals($payrun_id) {
        $stmt = $this->db->prepare('
            SELECT 
                COUNT(DISTINCT staff_id) as total_employees,
                SUM(net_salary) as total_amount
            FROM school_payrun_items 
            WHERE payrun_id = :payrun_id AND deleted_at IS NULL
        ');
        $stmt->execute([':payrun_id' => $payrun_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updatePayrunTotals($payrun_id, $school_id) {
        $totals = $this->calculatePayrunTotals($payrun_id);
        return $this->update($payrun_id, $school_id, [
            'total_employees' => $totals['total_employees'] ?? 0,
            'total_amount' => $totals['total_amount'] ?? 0.00
        ]);
    }
}
