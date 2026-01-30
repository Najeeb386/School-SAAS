<?php
namespace App\Modules\School_Admin\Controllers;

require_once __DIR__ . '/../Models/PayrunModel.php';
require_once __DIR__ . '/../Models/PayrunItemModel.php';
require_once __DIR__ . '/../Models/StaffSalaryModel.php';

use App\Modules\School_Admin\Models\PayrunModel;
use App\Modules\School_Admin\Models\PayrunItemModel;
use App\Modules\School_Admin\Models\StaffSalaryModel;

class PayrunController {
    protected $db;
    protected $payrunModel;
    protected $payrunItemModel;
    protected $staffSalaryModel;
    protected $school_id;

    public function __construct($DB_con) {
        $this->db = $DB_con;
        $this->payrunModel = new PayrunModel($DB_con);
        $this->payrunItemModel = new PayrunItemModel($DB_con);
        $this->staffSalaryModel = new StaffSalaryModel($DB_con);
        $this->school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
    }

    public function list($session_id = null) {
        return $this->payrunModel->getAll($this->school_id, $session_id);
    }

    public function getRecent($session_id, $limit = 3) {
        return $this->payrunModel->getRecentPayruns($this->school_id, $session_id, $limit);
    }

    public function get($id) {
        return $this->payrunModel->getById($id, $this->school_id);
    }

    public function getByMonthYear($session_id, $month, $year) {
        return $this->payrunModel->getByMonthYear($this->school_id, $session_id, $month, $year);
    }

    public function createFromRequest() {
        $data = [
            'session_id' => intval($_POST['session_id'] ?? 0),
            'pay_month' => intval($_POST['pay_month'] ?? date('n')),
            'pay_year' => intval($_POST['pay_year'] ?? date('Y')),
            'pay_period_start' => $_POST['pay_period_start'] ?? date('Y-m-01'),
            'pay_period_end' => $_POST['pay_period_end'] ?? date('Y-m-t'),
            'created_by' => $_SESSION['user_id'] ?? null,
        ];

        $existing = $this->payrunModel->getByMonthYear($this->school_id, $data['session_id'], $data['pay_month'], $data['pay_year']);
        if ($existing) {
            return ['success' => false, 'message' => 'Payrun for this month/year already exists'];
        }

        $payrun_id = $this->payrunModel->create($this->school_id, $data);
        if (!$payrun_id) {
            return ['success' => false, 'message' => 'Failed to create payrun'];
        }

        // Auto-generate payrun items from staff salaries
        $this->generatePayrunItems($payrun_id, $data['session_id']);

        return ['success' => true, 'message' => 'Payrun created successfully', 'payrun_id' => $payrun_id];
    }

    public function updateFromRequest() {
        $id = intval($_POST['id'] ?? 0);
        $payrun = $this->get($id);
        
        if (!$payrun) {
            return ['success' => false, 'message' => 'Payrun not found'];
        }

        $data = [];
        if (isset($_POST['status'])) $data['status'] = $_POST['status'];
        if (isset($_POST['total_employees'])) $data['total_employees'] = intval($_POST['total_employees']);
        if (isset($_POST['total_amount'])) $data['total_amount'] = floatval($_POST['total_amount']);

        $result = $this->payrunModel->update($id, $this->school_id, $data);
        return $result 
            ? ['success' => true, 'message' => 'Payrun updated successfully']
            : ['success' => false, 'message' => 'Failed to update payrun'];
    }

    public function deleteFromRequest() {
        $id = intval($_POST['id'] ?? 0);
        $payrun = $this->get($id);
        
        if (!$payrun) {
            return ['success' => false, 'message' => 'Payrun not found'];
        }

        if ($payrun['status'] !== 'draft') {
            return ['success' => false, 'message' => 'Can only delete draft payruns'];
        }

        $result = $this->payrunModel->delete($id, $this->school_id);
        return $result 
            ? ['success' => true, 'message' => 'Payrun deleted successfully']
            : ['success' => false, 'message' => 'Failed to delete payrun'];
    }

    public function processPayrun($payrun_id) {
        $payrun = $this->get($payrun_id);
        if (!$payrun || $payrun['status'] !== 'draft') {
            return ['success' => false, 'message' => 'Invalid payrun or already processed'];
        }

        $result = $this->payrunModel->updateStatus($payrun_id, $this->school_id, 'processed');
        return $result 
            ? ['success' => true, 'message' => 'Payrun processed successfully']
            : ['success' => false, 'message' => 'Failed to process payrun'];
    }

    public function approvePayrun($payrun_id) {
        $payrun = $this->get($payrun_id);
        if (!$payrun || $payrun['status'] !== 'processed') {
            return ['success' => false, 'message' => 'Invalid payrun or not yet processed'];
        }

        $extra = ['approved_by' => $_SESSION['user_id'] ?? null];
        $result = $this->payrunModel->updateStatus($payrun_id, $this->school_id, 'approved', $extra);
        return $result 
            ? ['success' => true, 'message' => 'Payrun approved successfully']
            : ['success' => false, 'message' => 'Failed to approve payrun'];
    }

    public function payPayrun($payrun_id) {
        $payrun = $this->get($payrun_id);
        if (!$payrun || $payrun['status'] !== 'approved') {
            return ['success' => false, 'message' => 'Invalid payrun or not yet approved'];
        }

        // Mark all payrun items as paid
        $payment_date = date('Y-m-d');
        $this->payrunItemModel->markAllPaid($payrun_id, $this->school_id, $payment_date);

        $extra = ['payment_date' => $payment_date];
        $result = $this->payrunModel->updateStatus($payrun_id, $this->school_id, 'paid', $extra);
        return $result 
            ? ['success' => true, 'message' => 'Payrun marked as paid successfully']
            : ['success' => false, 'message' => 'Failed to mark payrun as paid'];
    }

    public function generatePayrunItems($payrun_id, $session_id) {
        $payrun = $this->get($payrun_id);
        if (!$payrun) {
            return ['success' => false, 'message' => 'Payrun not found'];
        }

        // Get all active salaries for the session
        $salaries = $this->staffSalaryModel->getAll($this->school_id, $session_id);
        
        if (empty($salaries)) {
            return ['success' => false, 'message' => 'No salaries found for this session'];
        }

        $added_count = 0;
        foreach ($salaries as $salary) {
            // Check if item already exists
            $existing = $this->payrunItemModel->getByPayrunAndStaff(
                $payrun_id, 
                $this->school_id, 
                $salary['staff_type'], 
                $salary['staff_id']
            );

            if (!$existing) {
                $item_data = [
                    'staff_type' => $salary['staff_type'],
                    'staff_id' => $salary['staff_id'],
                    'session_id' => $session_id,
                    'basic_salary' => $salary['basic_salary'],
                    'allowance' => $salary['allowance'],
                    'deduction' => $salary['deduction'],
                    'net_salary' => ($salary['basic_salary'] + $salary['allowance'] - $salary['deduction']),
                ];

                $this->payrunItemModel->create($payrun_id, $this->school_id, $item_data);
                $added_count++;
            }
        }

        // Update payrun totals
        $this->payrunModel->updatePayrunTotals($payrun_id, $this->school_id);

        return ['success' => true, 'message' => "Added {$added_count} items to payrun", 'count' => $added_count];
    }

    public function getPayrunItems($payrun_id) {
        return $this->payrunItemModel->getAllByPayrun($payrun_id, $this->school_id);
    }

    public function getPaymentSummary($payrun_id) {
        return $this->payrunItemModel->getPaymentSummary($payrun_id, $this->school_id);
    }

    public function getPayrunWithItems($payrun_id) {
        $payrun = $this->get($payrun_id);
        if (!$payrun) {
            return null;
        }

        $payrun['items'] = $this->getPayrunItems($payrun_id);
        $payrun['payment_summary'] = $this->getPaymentSummary($payrun_id);
        return $payrun;
    }
}
