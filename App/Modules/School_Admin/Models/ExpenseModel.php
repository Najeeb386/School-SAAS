<?php

namespace App\Modules\School_Admin\Models;

use PDO;

class ExpenseModel
{
    private $db;

    public function __construct(PDO $connection)
    {
        $this->db = $connection;
    }

    /**
     * Get all expenses for a school
     */
    public function getAll($school_id, $session_id = null)
    {
        $query = "
            SELECT se.*, ec.name as category_name
            FROM school_expenses se
            LEFT JOIN expense_categories ec ON se.expense_category_id = ec.id
            WHERE se.school_id = ?
        ";
        $params = [$school_id];

        if ($session_id) {
            $query .= " AND se.session_id = ?";
            $params[] = $session_id;
        }

        $query .= " ORDER BY se.expense_date DESC, se.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get expense by ID
     */
    public function getById($id, $school_id)
    {
        $stmt = $this->db->prepare("
            SELECT se.*, ec.name as category_name
            FROM school_expenses se
            LEFT JOIN expense_categories ec ON se.expense_category_id = ec.id
            WHERE se.id = ? AND se.school_id = ?
        ");
        $stmt->execute([$id, $school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get expenses by category
     */
    public function getByCategory($school_id, $category_id, $session_id = null)
    {
        $query = "
            SELECT se.*, ec.name as category_name
            FROM school_expenses se
            LEFT JOIN expense_categories ec ON se.expense_category_id = ec.id
            WHERE se.school_id = ? AND se.expense_category_id = ?
        ";
        $params = [$school_id, $category_id];

        if ($session_id) {
            $query .= " AND se.session_id = ?";
            $params[] = $session_id;
        }

        $query .= " ORDER BY se.expense_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get expenses by status
     */
    public function getByStatus($school_id, $status, $session_id = null)
    {
        $query = "
            SELECT se.*, ec.name as category_name
            FROM school_expenses se
            LEFT JOIN expense_categories ec ON se.expense_category_id = ec.id
            WHERE se.school_id = ? AND se.status = ?
        ";
        $params = [$school_id, $status];

        if ($session_id) {
            $query .= " AND se.session_id = ?";
            $params[] = $session_id;
        }

        $query .= " ORDER BY se.expense_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary statistics
     */
    public function getSummary($school_id, $session_id = null)
    {
        $query = "
            SELECT 
                COALESCE(SUM(amount), 0) as total,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as approved_amount,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END), 0) as rejected_amount,
                COUNT(*) as total_count,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count
            FROM school_expenses
            WHERE school_id = ?
        ";
        $params = [$school_id];

        if ($session_id) {
            $query .= " AND session_id = ?";
            $params[] = $session_id;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new expense
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO school_expenses (
                school_id, session_id, expense_category_id, title, description,
                vendor_name, invoice_no, amount, expense_date, payment_date,
                payment_method, reference_no, status, approval_notes, created_by, approved_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['school_id'],
            $data['session_id'],
            $data['expense_category_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['vendor_name'] ?? null,
            $data['invoice_no'] ?? null,
            $data['amount'],
            $data['expense_date'],
            $data['payment_date'] ?? null,
            $data['payment_method'] ?? 'cash',
            $data['reference_no'] ?? null,
            $data['status'] ?? 'pending',
            $data['approval_notes'] ?? null,
            $data['created_by'] ?? null,
            $data['approved_by'] ?? null
        ]);
    }

    /**
     * Update expense
     */
    public function update($id, $school_id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE school_expenses SET
                expense_category_id = ?,
                title = ?,
                description = ?,
                vendor_name = ?,
                invoice_no = ?,
                amount = ?,
                expense_date = ?,
                payment_date = ?,
                payment_method = ?,
                reference_no = ?,
                status = ?,
                approval_notes = ?,
                approved_by = ?,
                updated_at = NOW()
            WHERE id = ? AND school_id = ?
        ");

        return $stmt->execute([
            $data['expense_category_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['vendor_name'] ?? null,
            $data['invoice_no'] ?? null,
            $data['amount'],
            $data['expense_date'],
            $data['payment_date'] ?? null,
            $data['payment_method'] ?? 'cash',
            $data['reference_no'] ?? null,
            $data['status'] ?? 'pending',
            $data['approval_notes'] ?? null,
            $data['approved_by'] ?? null,
            $id,
            $school_id
        ]);
    }

    /**
     * Delete expense
     */
    public function delete($id, $school_id)
    {
        $stmt = $this->db->prepare("DELETE FROM school_expenses WHERE id = ? AND school_id = ?");
        return $stmt->execute([$id, $school_id]);
    }

    /**
     * Update status
     */
    public function updateStatus($id, $school_id, $status, $approved_by = null, $approval_notes = null)
    {
        $stmt = $this->db->prepare("
            UPDATE school_expenses SET
                status = ?,
                approved_by = ?,
                approval_notes = ?,
                updated_at = NOW()
            WHERE id = ? AND school_id = ?
        ");

        return $stmt->execute([$status, $approved_by, $approval_notes, $id, $school_id]);
    }
}
