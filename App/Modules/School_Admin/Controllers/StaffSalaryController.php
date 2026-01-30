<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\StaffSalaryModel;

class StaffSalaryController {
    protected $model;
    protected $school_id;

    public function __construct($DB_con) {
        $this->model = new StaffSalaryModel($DB_con);
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
    }

    public function list($session_id = null) {
        return $this->model->getAll($this->school_id, $session_id);
    }

    public function listByType($staff_type, $session_id) {
        return $this->model->getByStaffTypeAndSession($this->school_id, $staff_type, $session_id);
    }

    public function get($id) {
        return $this->model->getById($id, $this->school_id);
    }

    public function getByStaffAndSession($staff_type, $staff_id, $session_id) {
        return $this->model->getByStaffAndSession($this->school_id, $staff_type, $staff_id, $session_id);
    }

    public function createFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        
        $staff_type = isset($_POST['staff_type']) ? trim($_POST['staff_type']) : '';
        $staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;
        $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
        $basic = isset($_POST['basic_salary']) ? floatval($_POST['basic_salary']) : 0;
        $allowance = isset($_POST['allowance']) ? floatval($_POST['allowance']) : 0;
        $deduction = isset($_POST['deduction']) ? floatval($_POST['deduction']) : 0;
        $eff_from = isset($_POST['effective_from']) ? trim($_POST['effective_from']) : date('Y-m-d');
        
        if (!in_array($staff_type, ['teacher', 'employee'])) { $_SESSION['flash_error'] = 'Invalid staff type.'; return false; }
        if ($staff_id <= 0) { $_SESSION['flash_error'] = 'Staff id is required.'; return false; }
        if ($session_id <= 0) { $_SESSION['flash_error'] = 'Session is required.'; return false; }
        if ($basic <= 0) { $_SESSION['flash_error'] = 'Basic salary must be greater than 0.'; return false; }
        
        $data = [
            'staff_type' => $staff_type,
            'staff_id' => $staff_id,
            'session_id' => $session_id,
            'basic_salary' => $basic,
            'allowance' => $allowance,
            'deduction' => $deduction,
            'effective_from' => $eff_from,
            'status' => isset($_POST['status']) ? 1 : 1,
            'created_by' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'updated_by' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        ];
        
        $created = $this->model->create($this->school_id, $data);
        if ($created) { $_SESSION['flash_success'] = 'Salary record created.'; return $created; }
        $_SESSION['flash_error'] = 'Failed to create salary record.'; return false;
    }

    public function updateFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) { $_SESSION['flash_error'] = 'Invalid salary record id.'; return false; }
        
        $basic = isset($_POST['basic_salary']) ? floatval($_POST['basic_salary']) : 0;
        $allowance = isset($_POST['allowance']) ? floatval($_POST['allowance']) : 0;
        $deduction = isset($_POST['deduction']) ? floatval($_POST['deduction']) : 0;
        $eff_from = isset($_POST['effective_from']) ? trim($_POST['effective_from']) : date('Y-m-d');
        
        if ($basic <= 0) { $_SESSION['flash_error'] = 'Basic salary must be greater than 0.'; return false; }
        
        $data = [
            'basic_salary' => $basic,
            'allowance' => $allowance,
            'deduction' => $deduction,
            'effective_from' => $eff_from,
            'status' => isset($_POST['status']) ? 1 : 0,
            'updated_by' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        ];
        
        $ok = $this->model->update($id, $this->school_id, $data);
        if ($ok) { $_SESSION['flash_success'] = 'Salary record updated.'; return true; }
        $_SESSION['flash_error'] = 'Failed to update salary record.'; return false;
    }

    public function deleteFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) { $_SESSION['flash_error'] = 'Invalid salary record id.'; return false; }
        
        $ok = $this->model->delete($id, $this->school_id);
        if ($ok) { $_SESSION['flash_success'] = 'Salary record deleted.'; return true; }
        $_SESSION['flash_error'] = 'Failed to delete salary record.'; return false;
    }

    public function calculateNetSalary($basic, $allowance = 0, $deduction = 0) {
        return $this->model->calculateNetSalary($basic, $allowance, $deduction);
    }
}
