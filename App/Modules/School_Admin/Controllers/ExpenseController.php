<?php

namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\ExpenseModel;
use App\Modules\School_Admin\Models\ExpenseCategoryModel;

class ExpenseController
{
    private $expenseModel;
    private $categoryModel;

    public function __construct($db)
    {
        $this->expenseModel = new ExpenseModel($db);
        $this->categoryModel = new ExpenseCategoryModel($db);
    }

    /**
     * Get all expenses
     */
    public function list($school_id, $session_id = null)
    {
        return $this->expenseModel->getAll($school_id, $session_id);
    }

    /**
     * Get expense by ID
     */
    public function get($id, $school_id)
    {
        return $this->expenseModel->getById($id, $school_id);
    }

    /**
     * Get expenses by category
     */
    public function getByCategory($school_id, $category_id, $session_id = null)
    {
        return $this->expenseModel->getByCategory($school_id, $category_id, $session_id);
    }

    /**
     * Get expenses by status
     */
    public function getByStatus($school_id, $status, $session_id = null)
    {
        return $this->expenseModel->getByStatus($school_id, $status, $session_id);
    }

    /**
     * Get summary statistics
     */
    public function getSummary($school_id, $session_id = null)
    {
        return $this->expenseModel->getSummary($school_id, $session_id);
    }

    /**
     * Get all categories for a school
     */
    public function getCategories($school_id)
    {
        return $this->categoryModel->getAll($school_id);
    }

    /**
     * Create expense from request
     */
    public function createFromRequest($data)
    {
        return $this->expenseModel->create($data);
    }

    /**
     * Update expense from request
     */
    public function updateFromRequest($id, $school_id, $data)
    {
        return $this->expenseModel->update($id, $school_id, $data);
    }

    /**
     * Delete expense
     */
    public function deleteFromRequest($id, $school_id)
    {
        return $this->expenseModel->delete($id, $school_id);
    }

    /**
     * Update status
     */
    public function updateStatus($id, $school_id, $status, $approved_by = null, $approval_notes = null)
    {
        return $this->expenseModel->updateStatus($id, $school_id, $status, $approved_by, $approval_notes);
    }

    /**
     * Create category
     */
    public function createCategory($school_id, $name, $description = null)
    {
        return $this->categoryModel->create($school_id, $name, $description);
    }

    /**
     * Get summary by date range
     */
    public function getSummaryByDateRange($school_id, $start_date, $end_date, $session_id = null)
    {
        // This can be extended to use a dedicated query in the model
        $expenses = $this->expenseModel->getAll($school_id, $session_id);
        $filtered = array_filter($expenses, function($exp) use ($start_date, $end_date) {
            return $exp['expense_date'] >= $start_date && $exp['expense_date'] <= $end_date;
        });
        
        return [
            'total' => array_sum(array_column($filtered, 'amount')),
            'count' => count($filtered),
            'expenses' => array_values($filtered)
        ];
    }
}
