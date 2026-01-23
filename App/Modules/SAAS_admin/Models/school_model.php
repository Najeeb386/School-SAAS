<?php
class School
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Get all schools
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM schools ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single school
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM schools WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create school
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO schools (name, subdomain, email, password, contact_no, estimated_students, plan, status, storage_used, db_size, start_date, expires_at, created_at, updated_at)
            VALUES (:name, :subdomain, :email, :password, :contact_no, :estimated_students, :plan, :status, :storage_used, :db_size, :start_date, :expires_at, :created_at, :updated_at)
        ");

        $result = $stmt->execute([
            ':name'                => isset($data['name']) ? $data['name'] : null,
            ':subdomain'           => isset($data['subdomain']) ? $data['subdomain'] : null,
            ':email'               => isset($data['email']) ? $data['email'] : null,
            ':password'            => isset($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null,
            ':contact_no'          => isset($data['contact_no']) ? $data['contact_no'] : null,
            ':estimated_students'  => isset($data['estimated_students']) ? $data['estimated_students'] : 0,
            ':plan'                => isset($data['plan']) ? $data['plan'] : null,
            ':status'              => isset($data['status']) ? $data['status'] : 'inactive',
            ':storage_used'        => 0,
            ':db_size'             => 0,
            ':start_date'          => isset($data['start_date']) ? $data['start_date'] : date('Y-m-d'),
            ':expires_at'          => isset($data['expires_at']) ? $data['expires_at'] : null,
            ':created_at'          => date('Y-m-d H:i:s'),
            ':updated_at'          => date('Y-m-d H:i:s')
        ]);
        
        if (!$result) {
            error_log("Database error: " . print_r($stmt->errorInfo(), true));
        }
        
        return $result;
    }

    // Update school
    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE schools 
            SET name = :name, 
                subdomain = :subdomain,
                email = :email,
                contact_no = :contact_no,
                estimated_students = :estimated_students,
                plan = :plan,
                status = :status,
                start_date = :start_date,
                expires_at = :expires_at,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $updateData = [
            ':id'                  => $id,
            ':name'                => isset($data['name']) ? $data['name'] : null,
            ':subdomain'           => isset($data['subdomain']) ? $data['subdomain'] : null,
            ':email'               => isset($data['email']) ? $data['email'] : null,
            ':contact_no'          => isset($data['contact_no']) ? $data['contact_no'] : null,
            ':estimated_students'  => isset($data['estimated_students']) ? $data['estimated_students'] : 0,
            ':plan'                => isset($data['plan']) ? $data['plan'] : null,
            ':status'              => isset($data['status']) ? $data['status'] : 'inactive',
            ':start_date'          => isset($data['start_date']) ? $data['start_date'] : date('Y-m-d'),
            ':expires_at'          => isset($data['expires_at']) ? $data['expires_at'] : null,
            ':updated_at'          => date('Y-m-d H:i:s')
        ];

        return $stmt->execute($updateData);
    }

    // Delete school
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM schools WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
