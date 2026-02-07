<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\GradingCriteriaModel;

class GradingCriteriaController {
    private GradingCriteriaModel $model;
    private int $school_id;

    public function __construct($db, int $school_id) {
        $this->model = new GradingCriteriaModel($db);
        $this->school_id = $school_id;
    }

    /**
     * Get all grading criteria
     */
    public function getGradingCriteria() {
        return $this->model->getGradingCriteriaBySchool($this->school_id);
    }

    /**
     * Get single grading criteria
     */
    public function getGradingCriteriaById($id) {
        return $this->model->getGradingCriteriaById($id, $this->school_id);
    }

    /**
     * Add new grading criteria with validation
     */
    public function addGradingCriteria($data) {
        // Validate required fields
        if (empty($data['grade_name']) || empty($data['min_percentage']) || empty($data['max_percentage'])) {
            return [
                'success' => false,
                'message' => 'Grade name, minimum percentage, and maximum percentage are required'
            ];
        }

        $grade_name = trim($data['grade_name']);
        $min_percentage = (float)$data['min_percentage'];
        $max_percentage = (float)$data['max_percentage'];

        // Validate percentage range
        if ($min_percentage < 0 || $max_percentage > 100 || $min_percentage > $max_percentage) {
            return [
                'success' => false,
                'message' => 'Invalid percentage range. Minimum must be >= 0, Maximum must be <= 100, and Minimum <= Maximum'
            ];
        }

        // Check if grade name already exists
        if ($this->model->gradeNameExists($grade_name, $this->school_id)) {
            return [
                'success' => false,
                'message' => "Grade name '{$grade_name}' already exists for this school"
            ];
        }

        // Check for overlapping percentage ranges
        if ($this->model->percentageRangeExists($min_percentage, $max_percentage, $this->school_id)) {
            return [
                'success' => false,
                'message' => 'Percentage range overlaps with existing grading criteria'
            ];
        }

        // Add the grading criteria
        $result = $this->model->addGradingCriteria(
            $this->school_id,
            $grade_name,
            $min_percentage,
            $max_percentage,
            isset($data['gpa']) ? (float)$data['gpa'] : null,
            isset($data['remarks']) ? trim($data['remarks']) : null,
            isset($data['is_pass']) ? (int)$data['is_pass'] : 1,
            $data['grading_system'] ?? 'percentage',
            isset($data['status']) ? (int)$data['status'] : 1
        );

        if ($result) {
            return [
                'success' => true,
                'message' => "Grading criteria '{$grade_name}' added successfully"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to add grading criteria. Please try again.'
            ];
        }
    }

    /**
     * Update grading criteria with validation
     */
    public function updateGradingCriteria($id, $data) {
        // Validate required fields
        if (empty($data['grade_name']) || empty($data['min_percentage']) || empty($data['max_percentage'])) {
            return [
                'success' => false,
                'message' => 'Grade name, minimum percentage, and maximum percentage are required'
            ];
        }

        // Check if grading criteria exists
        $existing = $this->model->getGradingCriteriaById($id, $this->school_id);
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Grading criteria not found'
            ];
        }

        $grade_name = trim($data['grade_name']);
        $min_percentage = (float)$data['min_percentage'];
        $max_percentage = (float)$data['max_percentage'];

        // Validate percentage range
        if ($min_percentage < 0 || $max_percentage > 100 || $min_percentage > $max_percentage) {
            return [
                'success' => false,
                'message' => 'Invalid percentage range. Minimum must be >= 0, Maximum must be <= 100, and Minimum <= Maximum'
            ];
        }

        // Check if grade name already exists (excluding current record)
        if ($grade_name !== $existing['grade_name'] && $this->model->gradeNameExists($grade_name, $this->school_id, $id)) {
            return [
                'success' => false,
                'message' => "Grade name '{$grade_name}' already exists for this school"
            ];
        }

        // Check for overlapping percentage ranges (excluding current record)
        if ($this->model->percentageRangeExists($min_percentage, $max_percentage, $this->school_id, $id)) {
            return [
                'success' => false,
                'message' => 'Percentage range overlaps with existing grading criteria'
            ];
        }

        // Update the grading criteria
        $result = $this->model->updateGradingCriteria(
            $id,
            $this->school_id,
            $grade_name,
            $min_percentage,
            $max_percentage,
            isset($data['gpa']) ? (float)$data['gpa'] : null,
            isset($data['remarks']) ? trim($data['remarks']) : null,
            isset($data['is_pass']) ? (int)$data['is_pass'] : 1,
            $data['grading_system'] ?? 'percentage',
            isset($data['status']) ? (int)$data['status'] : 1
        );

        if ($result) {
            return [
                'success' => true,
                'message' => "Grading criteria '{$grade_name}' updated successfully"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update grading criteria. Please try again.'
            ];
        }
    }

    /**
     * Delete grading criteria
     */
    public function deleteGradingCriteria($id) {
        $existing = $this->model->getGradingCriteriaById($id, $this->school_id);
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Grading criteria not found'
            ];
        }

        $result = $this->model->deleteGradingCriteria($id, $this->school_id);

        if ($result) {
            return [
                'success' => true,
                'message' => "Grading criteria '{$existing['grade_name']}' deleted successfully"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to delete grading criteria. Please try again.'
            ];
        }
    }
}
