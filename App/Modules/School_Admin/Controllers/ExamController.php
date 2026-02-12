<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\ExamModel;
use App\Modules\School_Admin\Models\SessionModel;

class ExamController {
    private ExamModel $model;
    private SessionModel $sessionModel;
    private int $school_id;

    public function __construct($db, int $school_id) {
        $this->model = new ExamModel($db);
        $this->sessionModel = new SessionModel($db);
        $this->school_id = $school_id;
    }

    /**
     * Get all exams
     */
    public function getExams() {
        return $this->model->getExamsBySchool($this->school_id);
    }

    /**
     * Get filtered exams
     */
    public function getFilteredExams($data) {
        $exam_type = $data['exam_type'] ?? null;
        $session_id = isset($data['session_id']) ? (int)$data['session_id'] : null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        $status = $data['status'] ?? null;

        return [
            'success' => true,
            'data' => $this->model->getExamsFiltered(
                $this->school_id,
                $exam_type,
                $session_id,
                $start_date,
                $end_date,
                $status
            )
        ];
    }

    /**
     * Get single exam
     */
    public function getExamById($id) {
        return $this->model->getExamById($id, $this->school_id);
    }

    /**
     * Get all sessions for dropdown
     */
    public function getSessions() {
        return $this->sessionModel->getAll($this->school_id);
    }

    /**
     * Add new exam with validation
     */
    public function addExam($data) {
        // Validate required fields
        if (empty($data['exam_name']) || empty($data['session_id']) || empty($data['exam_type']) || empty($data['start_date']) || empty($data['end_date'])) {
            return [
                'success' => false,
                'message' => 'Exam name, session, type, start date, and end date are required'
            ];
        }

        $exam_name = trim($data['exam_name']);
        $session_id = (int)$data['session_id'];
        $exam_type = trim($data['exam_type']);
        $start_date = trim($data['start_date']);
        $end_date = trim($data['end_date']);

        // Validate exam type
        $valid_types = ['midterm', 'final', 'annual', 'board_prep', 'monthly'];
        if (!in_array($exam_type, $valid_types)) {
            return [
                'success' => false,
                'message' => 'Invalid exam type selected'
            ];
        }

        // Validate dates
        if (!strtotime($start_date)) {
            return [
                'success' => false,
                'message' => 'Invalid start date'
            ];
        }

        if (!strtotime($end_date)) {
            return [
                'success' => false,
                'message' => 'Invalid end date'
            ];
        }

        // Check date logic
        if (strtotime($start_date) > strtotime($end_date)) {
            return [
                'success' => false,
                'message' => 'Start date must be before end date'
            ];
        }

        // Check if exam exists for this school/session/name
        if ($this->model->examExists($exam_name, $this->school_id, $session_id)) {
            return [
                'success' => false,
                'message' => "Exam '{$exam_name}' already exists for this session"
            ];
        }

        // Validate status
        $status = $data['status'] ?? 'draft';
        $valid_statuses = ['draft', 'published', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            $status = 'draft';
        }

        $result = $this->model->addExam(
            $this->school_id,
            $session_id,
            $exam_name,
            $exam_type,
            $start_date,
            $end_date,
            isset($data['description']) ? trim($data['description']) : null,
            $status,
            isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
        );

        if ($result) {
            return [
                'success' => true,
                'message' => "Exam '{$exam_name}' created successfully"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create exam. Please try again.'
            ];
        }
    }

    /**
     * Update exam with validation
     */
    public function updateExam($id, $data) {
        // Validate required fields
        if (empty($data['exam_name']) || empty($data['session_id']) || empty($data['exam_type']) || empty($data['start_date']) || empty($data['end_date'])) {
            return [
                'success' => false,
                'message' => 'Exam name, session, type, start date, and end date are required'
            ];
        }

        // Check if exam exists
        $existing = $this->model->getExamById($id, $this->school_id);
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Exam not found'
            ];
        }

        $exam_name = trim($data['exam_name']);
        $session_id = (int)$data['session_id'];
        $exam_type = trim($data['exam_type']);
        $start_date = trim($data['start_date']);
        $end_date = trim($data['end_date']);

        // Validate exam type
        $valid_types = ['midterm', 'final', 'annual', 'board_prep', 'monthly'];
        if (!in_array($exam_type, $valid_types)) {
            return [
                'success' => false,
                'message' => 'Invalid exam type selected'
            ];
        }

        // Validate dates
        if (!strtotime($start_date)) {
            return [
                'success' => false,
                'message' => 'Invalid start date'
            ];
        }

        if (!strtotime($end_date)) {
            return [
                'success' => false,
                'message' => 'Invalid end date'
            ];
        }

        // Check date logic
        if (strtotime($start_date) > strtotime($end_date)) {
            return [
                'success' => false,
                'message' => 'Start date must be before end date'
            ];
        }

        // Check if exam exists (excluding current)
        if (($exam_name !== $existing['exam_name'] || $session_id !== (int)$existing['session_id']) && 
            $this->model->examExists($exam_name, $this->school_id, $session_id, $id)) {
            return [
                'success' => false,
                'message' => "Exam '{$exam_name}' already exists for this session"
            ];
        }

        // Validate status
        $status = $data['status'] ?? 'draft';
        $valid_statuses = ['draft', 'published', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            $status = 'draft';
        }

        $result = $this->model->updateExam(
            $id,
            $this->school_id,
            $session_id,
            $exam_name,
            $exam_type,
            $start_date,
            $end_date,
            isset($data['description']) ? trim($data['description']) : null,
            $status
        );

        if ($result) {
            return [
                'success' => true,
                'message' => "Exam '{$exam_name}' updated successfully"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update exam. Please try again.'
            ];
        }
    }

    /**
     * Delete exam
     */
    public function deleteExam($id) {
        $existing = $this->model->getExamById($id, $this->school_id);
        if (!$existing) {
            return [
                'success' => false,
                'message' => 'Exam not found'
            ];
        }

        $result = $this->model->deleteExam($id, $this->school_id);

        if ($result) {
            return [
                'success' => true,
                'message' => "Exam '{$existing['exam_name']}' deleted successfully"
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to delete exam. Please try again.'
            ];
        }
    }

    /**
     * Get current exams (where start_date >= today) with class/section information
     */
    public function getCurrentExamsWithDetails($data = []) {
        $exam_type = $data['exam_type'] ?? null;
        $session_id = isset($data['session_id']) ? (int)$data['session_id'] : null;
        $class_id = isset($data['class_id']) ? (int)$data['class_id'] : null;
        $section_id = isset($data['section_id']) ? (int)$data['section_id'] : null;
        $date_from = $data['date_from'] ?? null;
        $date_to = $data['date_to'] ?? null;

        return $this->model->getCurrentExamsWithClassDetails(
            $this->school_id,
            $exam_type,
            $session_id,
            $class_id,
            $section_id,
            $date_from,
            $date_to
        );
    }

    /**
     * Get all exams with class/section information
     */
    public function getAllExamsWithDetails($data = []) {
        $exam_type = $data['exam_type'] ?? null;
        $session_id = isset($data['session_id']) ? (int)$data['session_id'] : null;
        $class_id = isset($data['class_id']) ? (int)$data['class_id'] : null;
        $section_id = isset($data['section_id']) ? (int)$data['section_id'] : null;
        $date_from = $data['date_from'] ?? null;
        $date_to = $data['date_to'] ?? null;

        return $this->model->getAllExamsWithClassDetails(
            $this->school_id,
            $exam_type,
            $session_id,
            $class_id,
            $section_id,
            $date_from,
            $date_to
        );
    }

    /**
     * Get marks upload statistics
     */
    public function getMarksUploadStats() {
        return $this->model->getMarksUploadStatistics($this->school_id);
    }

    /**
     * Get classes for dropdown
     */
    public function getClasses() {
        $db = $this->model->getDb();
        $stmt = $db->prepare("
            SELECT DISTINCT c.id, c.class_name 
            FROM school_classes c 
            WHERE c.school_id = ? 
            ORDER BY c.class_order ASC
        ");
        $stmt->execute([$this->school_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get sections by class
     */
    public function getSectionsByClass(int $class_id) {
        $db = $this->model->getDb();
        $stmt = $db->prepare("
            SELECT id, section_name 
            FROM school_class_sections 
            WHERE class_id = ? 
            ORDER BY section_name ASC
        ");
        $stmt->execute([$class_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get exam results
     */
    public function getExamResults(int $exam_id) {
        return $this->model->getExamResults($exam_id, $this->school_id);
    }
}
