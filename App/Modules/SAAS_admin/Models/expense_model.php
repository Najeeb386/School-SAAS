<?php
class Expense
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        // Try to join with super_admin table to get creator name; if super_admin table missing, fall back to expenses-only query
        try {
            $stmt = $this->db->prepare("SELECT e.*, s.name AS creator_name FROM saas_expenses e LEFT JOIN super_admin s ON e.created_by = s.id ORDER BY e.expense_date DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            // Table not found or other DB error — fallback to selecting expenses only
            error_log("Expense::getAll fallback due to DB error: " . $ex->getMessage());
            try {
                $stmt = $this->db->prepare("SELECT e.* FROM saas_expenses e ORDER BY e.expense_date DESC");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Fallback: use created_by ID as creator_name if super_admin table not available
                foreach ($rows as &$r) {
                    if (!isset($r['creator_name']) || is_null($r['creator_name'])) {
                        $r['creator_name'] = !empty($r['created_by']) ? 'User ID: ' . $r['created_by'] : 'Unknown';
                    }
                }
                return $rows;
            } catch (PDOException $ex2) {
                error_log("Expense::getAll fallback failed: " . $ex2->getMessage());
                return [];
            }
        }
    }

    public function getById($id)
    {
        // Try to join with super_admin table to get creator name
        try {
            $stmt = $this->db->prepare("SELECT e.*, s.name AS creator_name FROM saas_expenses e LEFT JOIN super_admin s ON e.created_by = s.id WHERE e.expense_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            // Fallback to simple select without join
            error_log("Expense::getById fallback due to DB error: " . $ex->getMessage());
            $stmt = $this->db->prepare("SELECT * FROM saas_expenses WHERE expense_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO saas_expenses
            (title, description, category, amount, payment_method, expense_date, is_recurring, recurring_cycle, vendor_name, invoice_no, status, created_by, created_at)
            VALUES
            (:title, :description, :category, :amount, :payment_method, :expense_date, :is_recurring, :recurring_cycle, :vendor_name, :invoice_no, :status, :created_by, :created_at)"
        );

        $params = [
            ':title' => $data['title'] ?: null,
            ':description' => $data['description'] ?: null,
            ':category' => $data['category'] ?: null,
            ':amount' => $data['amount'] ?: null,
            ':payment_method' => $data['payment_method'] ?: null,
            ':expense_date' => $data['expense_date'] ?: null,
            ':is_recurring' => isset($data['is_recurring']) ? 1 : 0,
            ':recurring_cycle' => $data['recurring_cycle'] ?: null,
            ':vendor_name' => $data['vendor_name'] ?: null,
            ':invoice_no' => $data['invoice_no'] ?: null,
            ':status' => $data['status'] ?: null,
            ':created_by' => $data['created_by'] ?: null,
            ':created_at' => date('Y-m-d H:i:s')
        ];

        $result = $stmt->execute($params);
        if (!$result) {
            error_log("Expense create error: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE saas_expenses SET
                title = :title,
                description = :description,
                category = :category,
                amount = :amount,
                payment_method = :payment_method,
                expense_date = :expense_date,
                is_recurring = :is_recurring,
                recurring_cycle = :recurring_cycle,
                vendor_name = :vendor_name,
                invoice_no = :invoice_no,
                status = :status,
                updated_at = :updated_at
            WHERE expense_id = :id"
        );

        $params = [
            ':id' => $id,
            ':title' => $data['title'] ?: null,
            ':description' => $data['description'] ?: null,
            ':category' => $data['category'] ?: null,
            ':amount' => $data['amount'] ?: null,
            ':payment_method' => $data['payment_method'] ?: null,
            ':expense_date' => $data['expense_date'] ?: null,
            ':is_recurring' => isset($data['is_recurring']) ? 1 : 0,
            ':recurring_cycle' => $data['recurring_cycle'] ?: null,
            ':vendor_name' => $data['vendor_name'] ?: null,
            ':invoice_no' => $data['invoice_no'] ?: null,
            ':status' => $data['status'] ?: null,
            ':updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $stmt->execute($params);
        if (!$result) {
            error_log("Expense update error: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM saas_expenses WHERE expense_id = ?");
        return $stmt->execute([$id]);
    }
}

?>
