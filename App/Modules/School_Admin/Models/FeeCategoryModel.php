<?php
class FeeCategoryModel {
    protected $db;
    public function __construct() {
        require_once __DIR__ . '/../../../Core/database.php';
        $this->db = \Database::connect();
    }

    public function create(array $data) {
        $sql = "INSERT INTO schoo_fee_categories (school_id, name, code, description, status, created_at, updated_at) VALUES (:school_id, :name, :code, :description, :status, NOW(), NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':school_id' => $data['school_id'],
            ':name' => $data['name'],
            ':code' => $data['code'] ?? null,
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'] ?? 1,
        ]);
        return $this->db->lastInsertId();
    }

    public function getAllBySchool($school_id) {
        $sql = "SELECT * FROM schoo_fee_categories WHERE school_id = :sid ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id, $school_id=null) {
        $sql = "SELECT * FROM schoo_fee_categories WHERE id = :id" . ($school_id ? " AND school_id = :sid" : "") . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $params = [':id' => $id];
        if ($school_id) $params[':sid'] = $school_id;
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, array $data) {
        $sql = "UPDATE schoo_fee_categories SET name = :name, code = :code, description = :description, status = :status, updated_at = NOW() WHERE id = :id AND school_id = :school_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':code' => $data['code'] ?? null,
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'] ?? 1,
            ':id' => $id,
            ':school_id' => $data['school_id'] ?? 0,
        ]);
        return $stmt->rowCount();
    }

    public function delete($id, $school_id=null) {
        $sql = "DELETE FROM schoo_fee_categories WHERE id = :id" . ($school_id ? " AND school_id = :sid" : "");
        $stmt = $this->db->prepare($sql);
        $params = [':id' => $id];
        if ($school_id) $params[':sid'] = $school_id;
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
