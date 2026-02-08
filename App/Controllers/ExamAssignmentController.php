<?php

class ExamAssignmentController {
    
    private $model;
    private $school_id;
    
    public function __construct($pdo, $school_id) {
        require_once __DIR__ . '/../Modules/School_Admin/Models/ExamAssignmentModel.php';
        $this->model = new \ExamAssignmentModel($pdo, $school_id);
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
     * Get subjects for a specific class and section
     */
    public function getSubjectsByClassAndSection($class_id, $section_id) {
        return $this->model->getSubjectsByClassAndSection($class_id, $section_id);
    }
    
    /**
     * Save exam assignment (class + subjects)
     */
    public function saveAssignment($assignment_data) {
        try {
            $missing = [];
            if (empty($assignment_data['exam_id'])) $missing[] = 'exam_id';
            if (empty($assignment_data['class_id'])) $missing[] = 'class_id';
            if (empty($assignment_data['subjects'])) $missing[] = 'subjects';
            
            // Section is required only if not applying to all sections
            $apply_to_all = $assignment_data['apply_to_all_sections'] ?? false;
            if (!$apply_to_all && empty($assignment_data['section_id'])) {
                $missing[] = 'section_id';
            }
            
            if (!empty($missing)) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ];
            }
            
            // If apply_to_all_sections is true, save for all sections
            if ($apply_to_all) {
                return $this->model->saveAssignmentToAllSections(
                    $assignment_data['exam_id'],
                    $assignment_data['class_id'],
                    $assignment_data['subjects']
                );
            }
            
            // Otherwise, save for single section (original behavior)
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
                    'message' => 'Failed to create exam class assignment. This might be a duplicate assignment or invalid IDs.'
                ];
            }
            
            $subjects_saved = $this->model->saveExamSubjects($exam_class_id, $assignment_data['subjects']);
            
            if (!$subjects_saved) {
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
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update assignment (class + subjects)
     */
    public function updateAssignment($assignment_data) {
        try {
            if (empty($assignment_data['exam_class_id']) ||
                empty($assignment_data['exam_id']) || 
                empty($assignment_data['class_id']) || 
                empty($assignment_data['section_id']) ||
                empty($assignment_data['subjects'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields'
                ];
            }
            
            $exam_class_id = $assignment_data['exam_class_id'];
            
            $exam_class_data = [
                'exam_id' => $assignment_data['exam_id'],
                'class_id' => $assignment_data['class_id'],
                'section_id' => $assignment_data['section_id'],
                'status' => $assignment_data['status'] ?? 'active'
            ];
            
            $updated = $this->model->updateExamClass($exam_class_id, $exam_class_data);
            
            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Failed to update exam class assignment'
                ];
            }
            
            $this->model->deleteExamSubjects($exam_class_id);
            
            $subjects_saved = $this->model->saveExamSubjects($exam_class_id, $assignment_data['subjects']);
            
            if (!$subjects_saved) {
                return [
                    'success' => false,
                    'message' => 'Failed to update subjects'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Exam assignment updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update single subject details only
     */
    public function updateSubject($subject_data) {
        try {
            $missing = [];
            if (empty($subject_data['subject_id'])) $missing[] = 'subject_id';
            if (empty($subject_data['exam_date'])) $missing[] = 'exam_date';
            if (empty($subject_data['exam_time'])) $missing[] = 'exam_time';
            if (!isset($subject_data['total_marks'])) $missing[] = 'total_marks';
            if (!isset($subject_data['passing_marks'])) $missing[] = 'passing_marks';
            if (!isset($subject_data['status'])) $missing[] = 'status';

            if (!empty($missing)) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ];
            }

            $result = $this->model->updateExamSubject($subject_data);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Subject updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update subject'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete single subject
     */
    public function deleteSubject($subject_id) {
        try {
            $result = $this->model->deleteExamSubjectById($subject_id);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Subject deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete subject'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get subjects for a specific class (all sections)
     */
    public function getSubjectsByClass($class_id) {
        return $this->model->getSubjectsByClass($class_id);
    }
}
?>
