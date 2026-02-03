<?php

namespace App\Modules\School_Admin\Models;

class InvoiceModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get invoice by ID with line items
     */
    public function getById($invoice_id) {
        $stmt = $this->db->prepare('
            SELECT * FROM schoo_fee_invoices WHERE id = :id
        ');
        $stmt->execute([':id' => $invoice_id]);
        $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($invoice) {
            // Fetch line items
            $stmtItems = $this->db->prepare('
                SELECT * FROM schoo_fee_invoice_items WHERE invoice_id = :inv_id
            ');
            $stmtItems->execute([':inv_id' => $invoice_id]);
            $invoice['items'] = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $invoice;
    }

    /**
     * Get invoices by school with filters
     */
    public function listBySchool($school_id, $filters = []) {
        $query = 'SELECT i.*, s.first_name, s.last_name, s.admission_no FROM schoo_fee_invoices i
                  LEFT JOIN school_students s ON i.student_id = s.id
                  WHERE i.school_id = :sid';
        
        $params = [':sid' => $school_id];

        if (!empty($filters['status'])) {
            $query .= ' AND i.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['billing_month'])) {
            $query .= ' AND i.billing_month = :month';
            $params[':month'] = $filters['billing_month'];
        }

        if (!empty($filters['student_id'])) {
            $query .= ' AND i.student_id = :student_id';
            $params[':student_id'] = $filters['student_id'];
        }

        $query .= ' ORDER BY i.created_at DESC LIMIT 500';

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update invoice status
     */
    public function updateStatus($invoice_id, $status) {
        $stmt = $this->db->prepare('
            UPDATE schoo_fee_invoices SET status = :status, updated_at = NOW()
            WHERE id = :id
        ');
        
        return $stmt->execute([
            ':status' => $status,
            ':id' => $invoice_id
        ]);
    }

    /**
     * Delete invoice and its items
     */
    public function delete($invoice_id) {
        // Delete items
        $stmtItems = $this->db->prepare('DELETE FROM schoo_fee_invoice_items WHERE invoice_id = :id');
        $stmtItems->execute([':id' => $invoice_id]);

        // Delete invoice
        $stmt = $this->db->prepare('DELETE FROM schoo_fee_invoices WHERE id = :id');
        return $stmt->execute([':id' => $invoice_id]);
    }

    /**
     * Get invoice count by school and month
     */
    public function countByMonth($school_id, $billing_month) {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as count FROM schoo_fee_invoices
            WHERE school_id = :sid AND billing_month = :month
        ');
        $stmt->execute([
            ':sid' => $school_id,
            ':month' => $billing_month
        ]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Get invoice payment summary for student
     */
    public function getStudentSummary($school_id, $student_id) {
        $stmt = $this->db->prepare('
            SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = "pending" THEN total_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as paid_amount
            FROM schoo_fee_invoices
            WHERE school_id = :sid AND student_id = :stid
        ');
        
        $stmt->execute([
            ':sid' => $school_id,
            ':stid' => $student_id
        ]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
