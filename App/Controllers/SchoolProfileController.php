<?php
namespace App\Controllers;

use App\Models\School;

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
        $this->school_model = new School();
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
            $password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($password) || empty($confirm_password)) {
                throw new \Exception('Password fields are required');
            }

            if ($password !== $confirm_password) {
                throw new \Exception('Passwords do not match');
            }

            if (strlen($password) < 8) {
                throw new \Exception('Password must be at least 8 characters long');
            }

            $this->school_model->updatePassword($this->school_id, $password);
            $this->messages['success'] = 'Password updated successfully!';
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
