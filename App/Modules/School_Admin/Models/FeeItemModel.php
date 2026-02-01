<?php
class FeeItemModel {
    protected $db;
    public function __construct() {
        require_once __DIR__ . '/../../../Core/database.php';
        $this->db = \Database::connect();
    }

    public function create(array $data) {
        $sql = "INSERT INTO schoo_fee_items (school_id, category_id, name, code, amount, billing_cycle, status, created_at, updated_at) VALUES (:school_id, :category_id, :name, :code, :amount, :billing_cycle, :status, NOW(), NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':school_id' => $data['school_id'],
            ':category_id' => $data['category_id'] ?? null,
            ':name' => $data['name'],
            ':code' => $data['code'] ?? null,
            ':amount' => $data['amount'] ?? 0.00,
            ':billing_cycle' => $data['billing_cycle'] ?? 'one_time',
            ':status' => $data['status'] ?? 1,
        ]);
        return $this->db->lastInsertId();
    }

    public function getAllBySchool($school_id) {
        $sql = "SELECT i.*, c.name AS category_name FROM schoo_fee_items i LEFT JOIN schoo_fee_categories c ON i.category_id = c.id WHERE i.school_id = :sid ORDER BY i.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, array $data) {
        $sql = "UPDATE schoo_fee_items SET category_id = :category_id, name = :name, code = :code, amount = :amount, billing_cycle = :billing_cycle, status = :status, updated_at = NOW() WHERE id = :id AND school_id = :school_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':category_id' => $data['category_id'] ?? null,
            ':name' => $data['name'],
            ':code' => $data['code'] ?? null,
            ':amount' => $data['amount'] ?? 0,
            ':billing_cycle' => $data['billing_cycle'] ?? 'one_time',
            ':status' => $data['status'] ?? 1,
            ':id' => $id,
            ':school_id' => $data['school_id'] ?? 0,
        ]);
        return $stmt->rowCount();
    }

    public function delete($id, $school_id=null) {
        $sql = "DELETE FROM schoo_fee_items WHERE id = :id" . ($school_id ? " AND school_id = :sid" : "");
        $stmt = $this->db->prepare($sql);
        $params = [':id' => $id];
        if ($school_id) $params[':sid'] = $school_id;
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
