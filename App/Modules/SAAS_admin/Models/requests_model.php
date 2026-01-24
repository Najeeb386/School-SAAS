<?php
class Requests
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Get all pending/new requests
    public function getNewRequests()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM saas_school_requests 
            WHERE status = 'pending' 
            ORDER BY requested_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all approved requests
    public function getApprovedRequests()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM saas_school_requests 
            WHERE status = 'approved' 
            ORDER BY actioned_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all rejected requests
    public function getRejectedRequests()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM saas_school_requests 
            WHERE status = 'rejected' 
            ORDER BY actioned_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get request by ID
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM saas_school_requests 
            WHERE request_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update request status
    public function updateStatus($id, $status, $rejection_reason = null)
    {
        $stmt = $this->db->prepare("
            UPDATE saas_school_requests 
            SET status = :status, 
                rejection_reason = :rejection_reason,
                actioned_at = :actioned_at
            WHERE request_id = :request_id
        ");
        
        return $stmt->execute([
            ':request_id' => $id,
            ':status' => $status,
            ':rejection_reason' => $rejection_reason,
            ':actioned_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Search requests
    public function searchRequests($status, $school_name = '', $start_date = '', $end_date = '')
    {
        $query = "SELECT * FROM saas_school_requests WHERE status = :status";
        $params = [':status' => $status];

        if (!empty($school_name)) {
            $query .= " AND school_name LIKE :school_name";
            $params[':school_name'] = '%' . $school_name . '%';
        }

        if (!empty($start_date)) {
            $query .= " AND DATE(requested_at) >= :start_date";
            $params[':start_date'] = $start_date;
        }

        if (!empty($end_date)) {
            $query .= " AND DATE(requested_at) <= :end_date";
            $params[':end_date'] = $end_date;
        }

        $query .= " ORDER BY requested_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create school from approved request
    public function createSchoolFromRequest($requestId)
    {
        // First, get the request details
        $request = $this->getById($requestId);
        
        if (!$request) {
            return false;
        }

        // Generate a subdomain from school name (lowercase, replace spaces with hyphens)
        $subdomain = strtolower(str_replace(' ', '-', $request['school_name']));
        $subdomain = preg_replace('/[^a-z0-9-]/', '', $subdomain);
        $subdomain = preg_replace('/-+/', '-', $subdomain);
        $subdomain = trim($subdomain, '-');

        // Check if subdomain already exists
        $checkStmt = $this->db->prepare("SELECT id FROM schools WHERE subdomain = ?");
        $checkStmt->execute([$subdomain]);
        if ($checkStmt->fetch()) {
            // Add a random suffix if subdomain exists
            $subdomain = $subdomain . '-' . substr(uniqid(), -4);
        }

        // Generate a temporary password
        $tempPassword = bin2hex(random_bytes(8));

        // Insert into schools table
        $stmt = $this->db->prepare("
            INSERT INTO schools (name, subdomain, email, password, contact_no, estimated_students, plan, status, storage_used, db_size, start_date, expires_at, created_at, updated_at)
            VALUES (:name, :subdomain, :email, :password, :contact_no, :estimated_students, :plan, :status, :storage_used, :db_size, :start_date, :expires_at, :created_at, :updated_at)
        ");

        $result = $stmt->execute([
            ':name'                => $request['school_name'],
            ':subdomain'           => $subdomain,
            ':email'               => $request['school_email'],
            ':password'            => password_hash($tempPassword, PASSWORD_BCRYPT),
            ':contact_no'          => $request['school_phone'],
            ':estimated_students'  => $request['estimated_students'],
            ':plan'                => $request['plan_type'],
            ':status'              => 'active',
            ':storage_used'        => 0,
            ':db_size'             => 0,
            ':start_date'          => date('Y-m-d'),
            ':expires_at'          => date('Y-m-d', strtotime('+1 year')),
            ':created_at'          => date('Y-m-d H:i:s'),
            ':updated_at'          => date('Y-m-d H:i:s')
        ]);

        return $result;
    }
}
?>
