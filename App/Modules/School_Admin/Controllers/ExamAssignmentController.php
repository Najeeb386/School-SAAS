<?php
namespace App\Controllers;

use ExamAssignmentModel;

class ExamAssignmentController {
    
    private $model;
    private $school_id;
    
    public function __construct($pdo, $school_id) {
        // Load the model
        require_once __DIR__ . '/../Models/ExamAssignmentModel.php';
        $this->model = new ExamAssignmentModel($pdo, $school_id);
        $this->school_id = $school_id;
    }
    
    /**
     * Get exam details
     */
    public function getExam($exam_id) {
        return $this->model->getExamById($exam_id);
    }
    
    /**
     * Get all classes
     */
    public function getClasses() {
        return $this->model->getClasses();
    }
    
    /**
     * Get sections by class
     */
    public function getSectionsByClass($class_id) {
        return $this->model->getSectionsByClass($class_id);
    }
    
    /**
     * Get all subjects
     */
    public function getSubjects() {
        return $this->model->getSubjects();
    }
    
    /**
     * Save exam assignment (class + subjects)
     */
    public function saveAssignment($assignment_data) {
        try {
            // Validate required fields
            if (empty($assignment_data['exam_id']) || 
                empty($assignment_data['class_id']) || 
                empty($assignment_data['section_id']) ||
                empty($assignment_data['subjects'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields'
                ];
            }
            
            // Save exam class
            $exam_class_data = [
                'exam_id' => $assignment_data['exam_id'],
                'class_id' => $assignment_data['class_id'],
                'section_id' => $assignment_data['section_id'],
                'status' => $assignment_data['status'] ?? 'active'
            ];
            
            $exam_class_id = $this->model->saveExamClass($exam_class_data);
            
            if (!$exam_class_id) {
                return [
                    'success' => false,
                    'message' => 'Failed to create exam class assignment'
                ];
            }
            
            // Save subjects
            $subjects_saved = $this->model->saveExamSubjects($exam_class_id, $assignment_data['subjects']);
            
            if (!$subjects_saved) {
                // Rollback: delete the exam class if subjects save fails
                $this->model->deleteExamClass($exam_class_id);
                return [
                    'success' => false,
                    'message' => 'Failed to save subjects'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Exam assignment saved successfully',
                'exam_class_id' => $exam_class_id
            ];
        } catch (\Exception $e) {
            error_log("Error in saveAssignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all assignments
     */
    public function getAssignments() {
        return $this->model->getAssignments();
    }
    
    /**
     * Get assignments by exam ID
     */
    public function getAssignmentsByExamId($exam_id) {
        return $this->model->getAssignmentsByExamId($exam_id);
    }
    
    /**
     * Delete assignment
     */
    public function deleteAssignment($exam_class_id) {
        try {
            $result = $this->model->deleteExamClass($exam_class_id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Assignment deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete assignment'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error in deleteAssignment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
?>
