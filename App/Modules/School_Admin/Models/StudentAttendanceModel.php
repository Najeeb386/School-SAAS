<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class StudentAttendanceModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get total number of students enrolled in the school
     */
    public function getTotalStudents(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM school_student_enrollments 
            WHERE school_id = :school_id AND status = 'active' AND deleted_at IS NULL
        ");
        $stmt->execute([':school_id' => $school_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Get attendance statistics for a specific date (defaults to today)
     */
    public function getAttendanceStats(int $school_id, string $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $stmt = $this->db->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM school_student_attendance
            WHERE school_id = :school_id AND attendance_date = :date
            GROUP BY status
        ");
        $stmt->execute([
            ':school_id' => $school_id,
            ':date' => $date
        ]);
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Initialize counts
        $stats = [
            'P' => 0,  // Present
            'A' => 0,  // Absent
            'L' => 0,  // Leave
            'HD' => 0  // Half Day
        ];
        
        foreach ($rows as $row) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = (int)$row['count'];
            }
        }
        
        return $stats;
    }

    /**
     * Get all attendance records for a specific date with student details
     */
    public function getAttendanceByDate(int $school_id, string $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $stmt = $this->db->prepare("
            SELECT 
                sa.id,
                sa.student_id,
                sa.class_id,
                sa.section_id,
                sa.status,
                sa.remarks,
                sa.attendance_date,
                se.admission_no,
                eee.first_name,
                eee.last_name,
                sc.class_name,
                scs.section_name
            FROM school_student_attendance sa
            JOIN school_student_enrollments se ON se.student_id = sa.student_id
            JOIN school_employees eee ON eee.id = se.student_id
            JOIN school_classes sc ON sc.id = sa.class_id
            JOIN school_class_sections scs ON scs.id = sa.section_id
            WHERE sa.school_id = :school_id AND sa.attendance_date = :date
            ORDER BY sc.class_order, scs.section_name, eee.first_name
        ");
        $stmt->execute([
            ':school_id' => $school_id,
            ':date' => $date
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all classes for a school
     */
    public function getClasses(int $school_id, int $session_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                class_name,
                class_code,
                grade_level,
                status,
                class_order
            FROM school_classes
            WHERE school_id = :school_id AND session_id = :session_id AND status = 1
            ORDER BY class_order ASC
        ");
        $stmt->execute([
            ':school_id' => $school_id,
            ':session_id' => $session_id
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all sections for a specific class
     */
    public function getSectionsByClass(int $class_id, int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                section_name,
                section_code,
                class_teacher_id,
                room_number,
                capacity,
                current_enrollment,
                status
            FROM school_class_sections
            WHERE class_id = :class_id AND school_id = :school_id
            ORDER BY section_name ASC
        ");
        $stmt->execute([
            ':class_id' => $class_id,
            ':school_id' => $school_id
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get attendance summary for each class and section
     */
    public function getClassWiseAttendanceSummary(int $school_id, string $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT sc.id as class_id, sc.class_name, scs.id as section_id, scs.section_name, scs.current_enrollment, " .
               "SUM(CASE WHEN sa.status = 'P' THEN 1 ELSE 0 END) as present_count, " .
               "SUM(CASE WHEN sa.status = 'A' THEN 1 ELSE 0 END) as absent_count, " .
               "SUM(CASE WHEN sa.status = 'L' THEN 1 ELSE 0 END) as leave_count, " .
               "SUM(CASE WHEN sa.status = 'HD' THEN 1 ELSE 0 END) as half_day_count " .
               "FROM school_classes sc " .
               "JOIN school_class_sections scs ON scs.class_id = sc.id " .
               "LEFT JOIN school_student_attendance sa ON sa.class_id = sc.id AND sa.section_id = scs.id AND sa.school_id = ? AND sa.attendance_date = ? " .
               "WHERE sc.school_id = ? AND sc.status = 1 " .
               "GROUP BY sc.id, scs.id " .
               "ORDER BY sc.class_order, scs.section_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$school_id, $date, $school_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get current active session for the school
     */
    public function getCurrentSession(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                name,
                start_date,
                end_date
            FROM school_sessions
            WHERE school_id = :school_id AND is_active = 1 AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':school_id' => $school_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get student enrollment by class and section
     */
    public function getStudentsByClassSection(int $school_id, int $class_id, int $section_id) {
        $stmt = $this->db->prepare("
            SELECT 
                se.student_id,
                se.admission_no,
                se.roll_no,
                ee.first_name,
                ee.last_name,
                ee.email,
                ee.avatar
            FROM school_student_enrollments se
            JOIN school_employees ee ON ee.id = se.student_id
            WHERE se.school_id = :school_id 
                AND se.class_id = :class_id 
                AND se.section_id = :section_id
                AND se.status = 'active'
                AND se.deleted_at IS NULL
            ORDER BY se.roll_no ASC, ee.first_name ASC
        ");
        $stmt->execute([
            ':school_id' => $school_id,
            ':class_id' => $class_id,
            ':section_id' => $section_id
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
