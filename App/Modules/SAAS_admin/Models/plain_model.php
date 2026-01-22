<?php
class Plan
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Get all plans
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM plans");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single plan
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create plan
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO plans (name, price_per_student_year, hosting_type, features, status, created_at)
            VALUES (:name, :price, :hosting, :features, :status, :created_at)
        ");

        $result = $stmt->execute([
            ':name'       => isset($data['name']) ? $data['name'] : null,
            ':price'      => isset($data['price_per_student_year']) ? $data['price_per_student_year'] : null,
            ':hosting'    => isset($data['hosting_type']) ? $data['hosting_type'] : null,
            ':features'   => isset($data['features']) ? $data['features'] : null,
            ':status'     => isset($data['status']) ? $data['status'] : null,
            ':created_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$result) {
            error_log("Database error: " . print_r($stmt->errorInfo(), true));
        }
        
        return $result;
    }

    // Update plan
    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE plans 
            SET name = :name, 
                price_per_student_year = :price,
                hosting_type = :hosting,
                features = :features,
                status = :status
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'       => $id,
            ':name'     => $data['name'],
            ':price'    => $data['price_per_student_year'],
            ':hosting'  => $data['hosting_type'],
            ':features' => $data['features'],
            ':status'   => $data['status']
        ]);
    }

    // Delete plan
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM plans WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>