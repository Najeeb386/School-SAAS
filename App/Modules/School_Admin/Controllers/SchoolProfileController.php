<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\SchoolModel;

class SchoolProfileController
{
    private $school_model;
    private $school_id;
    private $school_data = [];
    private $storage_info = [];
    private $messages = [
        'success' => '',
        'error' => ''
    ];

    public function __construct($school_id)
    {
        $this->school_model = new SchoolModel();
        $this->school_id = $school_id;
        $this->loadSchoolData();
    }

    /**
     * Load school data from database
     */
    private function loadSchoolData()
    {
        try {
            $this->school_data = $this->school_model->getById($this->school_id);
            if (!$this->school_data) {
                throw new \Exception('School not found');
            }
            $this->storage_info = $this->school_model->getStoragePercentage($this->school_id);
        } catch (\Exception $e) {
            $this->messages['error'] = $e->getMessage();
        }
    }

    /**
     * Handle form submission
     */
    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'update_school') {
                $this->updateSchoolInfo();
            } elseif ($action === 'update_password') {
                $this->updatePassword();
            } elseif ($action === 'upload_logo') {
                $this->uploadLogo();
            } elseif ($action === 'logout') {
                $this->logoutUser();
                header('Location: ../../../../../index.php?login=true');
                exit();
            }
        }
    }

    /**
     * Update school information
     */
    private function updateSchoolInfo()
    {
        try {
            $this->school_model->update($this->school_id, $_POST);
            $this->messages['success'] = 'School information updated successfully!';
            // Reload data
            $this->loadSchoolData();
        } catch (\Exception $e) {
            $this->messages['error'] = $e->getMessage();
        }
    }

    /**
     * Update password
     */
    private function updatePassword()
    {
        try {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password)) {
                throw new \Exception('Current password is required');
            }

            if (empty($new_password) || empty($confirm_password)) {
                throw new \Exception('New password fields are required');
            }

            if ($new_password !== $confirm_password) {
                throw new \Exception('New passwords do not match');
            }

            if (strlen($new_password) < 8) {
                throw new \Exception('Password must be at least 8 characters long');
            }

            // Verify current password
            if (!$this->school_model->verifyPassword($this->school_id, $current_password)) {
                throw new \Exception('Current password did not match');
            }

            $this->school_model->updatePassword($this->school_id, $new_password);
            $this->messages['success'] = 'Password updated successfully! Please login again with your new password.';
            
            // Logout user after password change
            $this->logoutUser();
        } catch (\Exception $e) {
            $this->messages['error'] = $e->getMessage();
        }
    }

    /**
     * Logout user - destroy session
     */
    private function logoutUser()
    {
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Upload school logo
     */
    private function uploadLogo()
    {
        try {
            if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('No file uploaded or upload error occurred.');
            }

            $filename = $this->school_model->uploadLogo($this->school_id, $_FILES['logo']);
            $this->messages['success'] = 'Logo uploaded successfully!';
            // Reload data to get updated logo path
            $this->loadSchoolData();
        } catch (\Exception $e) {
            $this->messages['error'] = $e->getMessage();
        }
    }

    /**
     * Get school data
     */
    public function getSchoolData()
    {
        return $this->school_data;
    }

    /**
     * Get storage info
     */
    public function getStorageInfo()
    {
        return $this->storage_info;
    }

    /**
     * Get success message
     */
    public function getSuccessMessage()
    {
        return $this->messages['success'];
    }

    /**
     * Get error message
     */
    public function getErrorMessage()
    {
        return $this->messages['error'];
    }

    /**
     * Check if there are errors
     */
    public function hasError()
    {
        return !empty($this->messages['error']);
    }

    /**
     * Check if there is success message
     */
    public function hasSuccess()
    {
        return !empty($this->messages['success']);
    }
}
