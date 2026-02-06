<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\StudentAttendanceModel;

class StudentAttendanceController {
    private StudentAttendanceModel $attendanceModel;
    private int $school_id;
    private ?int $session_id;

    public function __construct(int $school_id) {
        $this->school_id = $school_id;
        
        // Get database connection
        require_once __DIR__ . '/../../../Core/database.php';
        $db = \Database::connect();
        
        $this->attendanceModel = new StudentAttendanceModel($db);
        
        // Get current session
        $currentSession = $this->attendanceModel->getCurrentSession($school_id);
        $this->session_id = $currentSession ? (int)$currentSession['id'] : null;
    }

    /**
     * Get total students count
     */
    public function getTotalStudents() {
        return $this->attendanceModel->getTotalStudents($this->school_id);
    }

    /**
     * Get attendance statistics for today
     */
    public function getAttendanceStats($date = null) {
        return $this->attendanceModel->getAttendanceStats($this->school_id, $date);
    }

    /**
     * Get attendance details for a specific date
     */
    public function getAttendanceByDate($date = null) {
        return $this->attendanceModel->getAttendanceByDate($this->school_id, $date);
    }

    /**
     * Get all classes for the school
     */
    public function getClasses() {
        if (!$this->session_id) {
            return [];
        }
        return $this->attendanceModel->getClasses($this->school_id, $this->session_id);
    }

    /**
     * Get all sections for a class
     */
    public function getSectionsByClass(int $class_id) {
        return $this->attendanceModel->getSectionsByClass($class_id, $this->school_id);
    }

    /**
     * Get class-wise attendance summary
     */
    public function getClassWiseAttendanceSummary($date = null) {
        return $this->attendanceModel->getClassWiseAttendanceSummary($this->school_id, $date);
    }

    /**
     * Get students by class and section
     */
    public function getStudentsByClassSection(int $class_id, int $section_id) {
        return $this->attendanceModel->getStudentsByClassSection($this->school_id, $class_id, $section_id);
    }

    /**
     * Get current session
     */
    public function getCurrentSession() {
        return $this->attendanceModel->getCurrentSession($this->school_id);
    }
}
