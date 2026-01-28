<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class SchoolModel
{
    private $db;

    public function __construct()
    {
        require_once __DIR__ . '/../../../Core/database.php';
        $this->db = \Database::connect();
    }

    /**
     * Get school by ID
     */
    public function getById($school_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM schools WHERE id = :school_id");
            $stmt->execute(['school_id' => $school_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching school: ' . $e->getMessage());
        }
    }

    /**
     * Update school information
     */
    public function update($school_id, $data)
    {
        try {
            $allowed_fields = ['name', 'address', 'city', 'contact_no', 'email', 'boards'];
            $update_data = [];
            $params = ['school_id' => $school_id];

            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_data[$field] = $data[$field];
                    $params[$field] = $data[$field];
                }
            }

            if (empty($update_data)) {
                return false;
            }

            $setClauses = [];
            foreach (array_keys($update_data) as $field) {
                $setClauses[] = "$field = :$field";
            }

            $sql = "UPDATE schools SET " . implode(', ', $setClauses) . ", updated_at = NOW() WHERE id = :school_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error updating school: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword($school_id, $new_password)
    {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->db->prepare("UPDATE schools SET password = :password, updated_at = NOW() WHERE id = :school_id");
            $stmt->execute([
                'password' => $hashed_password,
                'school_id' => $school_id
            ]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error updating password: ' . $e->getMessage());
        }
    }

    /**
     * Verify password against stored hash
     */
    public function verifyPassword($school_id, $password)
    {
        try {
            $stmt = $this->db->prepare("SELECT password FROM schools WHERE id = :school_id");
            $stmt->execute(['school_id' => $school_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false;
            }
            
            return password_verify($password, $result['password']);
        } catch (\Exception $e) {
            throw new \Exception('Error verifying password: ' . $e->getMessage());
        }
    }

    /**
     * Calculate storage usage percentage
     */
    public function getStoragePercentage($school_id)
    {
        try {
            $school = $this->getById($school_id);
            $storage_used = (float)($school['storage_used'] ?? 0);
            $db_size = (float)($school['db_size'] ?? 0);
            $total_usage = $storage_used + $db_size;
            $total_limit = 10; // 10GB default
            
            return [
                'total_usage' => $total_usage,
                'storage_used' => $storage_used,
                'db_size' => $db_size,
                'percentage' => $total_usage > 0 ? min(($total_usage / $total_limit) * 100, 100) : 0
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error calculating storage: ' . $e->getMessage());
        }
    }

    /**
     * Get billing cycles for a specific school
     */
    public function getBillingCycles($school_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM saas_billing_cycles WHERE school_id = :school_id ORDER BY created_at DESC");
            $stmt->execute(['school_id' => $school_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching billing cycles: ' . $e->getMessage());
        }
    }

    /**
     * Get payments related to a billing_id
     */
    public function getPaymentsByBilling($billing_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM saas_payments WHERE billing_id = :billing_id ORDER BY payment_date DESC");
            $stmt->execute(['billing_id' => $billing_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching payments: ' . $e->getMessage());
        }
    }

    /**
     * Upload and save school logo
     */
    public function uploadLogo($school_id, $file)
    {
        try {
            // Validate file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowed_types)) {
                throw new \Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
            }

            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new \Exception('File size exceeds 5MB limit.');
            }

            // Create upload directory structure: School-SAAS/Storage/uploads/schools/school_{id}/
            $base_upload_dir = __DIR__ . '/../../../../Storage/uploads/schools/';
            $school_dir = $base_upload_dir . 'school_' . $school_id . '/';
            
            if (!is_dir($base_upload_dir)) {
                mkdir($base_upload_dir, 0755, true);
            }
            
            if (!is_dir($school_dir)) {
                mkdir($school_dir, 0755, true);
            }

            // Generate unique filename for logo
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $file_ext;
            $file_path = $school_dir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                throw new \Exception('Failed to upload logo file.');
            }

            // Delete old logo if exists
            $school = $this->getById($school_id);
            if ($school && !empty($school['logo_path'])) {
                $old_path = $school_dir . $school['logo_path'];
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            // Update database with new logo path (store only filename)
            $stmt = $this->db->prepare("UPDATE schools SET logo_path = :logo_path, updated_at = NOW() WHERE id = :school_id");
            $stmt->execute([
                'logo_path' => $filename,
                'school_id' => $school_id
            ]);

            return $filename;
        } catch (\Exception $e) {
            throw new \Exception('Logo upload error: ' . $e->getMessage());
        }
    }

    /**
     * Get logo URL for school
     */
    public function getLogoUrl($school_id)
    {
        try {
            $school = $this->getById($school_id);
            if ($school && !empty($school['logo_path'])) {
                return '../../../Storage/uploads/schools/school_' . $school_id . '/' . $school['logo_path'];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
}
