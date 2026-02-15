<?php
namespace App\Modules\School_Admin\Models;

use PDO;

class ExamModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all exams for a school
     */
    public function getExamsBySchool(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.school_id,
                e.session_id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.description,
                e.status,
                e.created_by,
                e.created_at,
                e.updated_at,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.school_id = ?
            ORDER BY e.start_date DESC
        ");
        $stmt->execute([$school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get exams filtered by criteria
     */
    public function getExamsFiltered(int $school_id, ?string $exam_type = null, ?int $session_id = null, ?string $start_date = null, ?string $end_date = null, ?string $status = null) {
        $query = "
            SELECT 
                e.id,
                e.school_id,
                e.session_id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.description,
                e.status,
                e.created_by,
                e.created_at,
                e.updated_at,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.school_id = ?
        ";
        $params = [$school_id];

        if ($exam_type) {
            $query .= " AND e.exam_type = ?";
            $params[] = $exam_type;
        }

        if ($session_id) {
            $query .= " AND e.session_id = ?";
            $params[] = $session_id;
        }

        if ($start_date) {
            $query .= " AND e.start_date >= ?";
            $params[] = $start_date;
        }

        if ($end_date) {
            $query .= " AND e.end_date <= ?";
            $params[] = $end_date;
        }

        if ($status) {
            $query .= " AND e.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY e.start_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single exam by ID
     */
    public function getExamById(int $id, int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.school_id,
                e.session_id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.description,
                e.status,
                e.created_by,
                e.created_at,
                e.updated_at,
                s.name as session_name
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            WHERE e.id = ? AND e.school_id = ?
        ");
        $stmt->execute([$id, $school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Add new exam
     */
    public function addExam(
        int $school_id,
        int $session_id,
        string $exam_name,
        string $exam_type,
        string $start_date,
        string $end_date,
        ?string $description = null,
        string $status = 'draft',
        ?int $created_by = null
    ) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO school_exams (
                    school_id,
                    session_id,
                    exam_name,
                    exam_type,
                    start_date,
                    end_date,
                    description,
                    status,
                    created_by,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            return $stmt->execute([
                $school_id,
                $session_id,
                $exam_name,
                $exam_type,
                $start_date,
                $end_date,
                $description,
                $status,
                $created_by
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Update exam
     */
    public function updateExam(
        int $id,
        int $school_id,
        int $session_id,
        string $exam_name,
        string $exam_type,
        string $start_date,
        string $end_date,
        ?string $description = null,
        string $status = 'draft'
    ) {
        try {
            $stmt = $this->db->prepare("
                UPDATE school_exams
                SET 
                    session_id = ?,
                    exam_name = ?,
                    exam_type = ?,
                    start_date = ?,
                    end_date = ?,
                    description = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ? AND school_id = ?
            ");

            return $stmt->execute([
                $session_id,
                $exam_name,
                $exam_type,
                $start_date,
                $end_date,
                $description,
                $status,
                $id,
                $school_id
            ]);
        } catch (\PDOException $e) {
            return false;
        }

    }

    /**
     * Delete exam
     */
    public function deleteExam(int $id, int $school_id) {
        try {
            $stmt = $this->db->prepare("\n                DELETE FROM school_exams\n                WHERE id = ? AND school_id = ?\n            ");
            return $stmt->execute([$id, $school_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Check if exam exists for school/session/name
     */
    public function examExists(string $exam_name, int $school_id, int $session_id, ?int $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM school_exams WHERE school_id = ? AND session_id = ? AND exam_name = ?";
        $params = [$school_id, $session_id, $exam_name];

        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get current exams (where start_date >= today) with class/section details
     */
    public function getCurrentExamsWithClassDetails(
        int $school_id,
        ?string $exam_type = null,
        ?int $session_id = null,
        ?int $class_id = null,
        ?int $section_id = null,
        ?string $date_from = null,
        ?string $date_to = null
    ) {
        $query = "
            SELECT 
                e.id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.status,
                e.session_id,
                s.name as session_name,
                CONCAT(c.class_name, ' - ', cs.section_name) as class_section,
                c.id as class_id,
                cs.id as section_id,
                COUNT(sec.id) as total_subjects,
                ec.id as exam_class_id
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            LEFT JOIN school_exam_classes ec ON e.id = ec.exam_id AND ec.school_id = ?
            LEFT JOIN school_classes c ON ec.class_id = c.id
            LEFT JOIN school_class_sections cs ON ec.section_id = cs.id
            LEFT JOIN school_exam_subjects sec ON ec.id = sec.exam_class_id
            WHERE e.school_id = ? AND DATE(e.start_date) <= CURDATE()
        ";
        
        $params = [$school_id, $school_id];

        if ($exam_type) {
            $query .= " AND e.exam_type = ?";
            $params[] = $exam_type;
        }

        if ($session_id) {
            $query .= " AND e.session_id = ?";
            $params[] = $session_id;
        }

        if ($class_id) {
            $query .= " AND ec.class_id = ?";
            $params[] = $class_id;
        }

        if ($section_id) {
            $query .= " AND ec.section_id = ?";
            $params[] = $section_id;
        }

        if ($date_from) {
            $query .= " AND DATE(e.start_date) >= ?";
            $params[] = $date_from;
        }

        if ($date_to) {
            $query .= " AND DATE(e.end_date) <= ?";
            $params[] = $date_to;
        }

        $query .= " GROUP BY e.id, ec.id ORDER BY e.start_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate marks uploaded percentage
        foreach ($results as &$exam) {
            $exam['marks_uploaded_percent'] = 0;
            if (!empty($exam['exam_class_id'])) {
                $exam['marks_uploaded_percent'] = $this->getMarksUploadPercentage($exam['exam_class_id']);
            }
        }

        return $results;
    }

    /**
     * Get all exams with class/section details
     */
    public function getAllExamsWithClassDetails(
        int $school_id,
        ?string $exam_type = null,
        ?int $session_id = null,
        ?int $class_id = null,
        ?int $section_id = null,
        ?string $date_from = null,
        ?string $date_to = null
    ) {
        $query = "
            SELECT 
                e.id,
                e.exam_name,
                e.exam_type,
                e.start_date,
                e.end_date,
                e.status,
                e.session_id,
                s.name as session_name,
                CONCAT(c.class_name, ' - ', cs.section_name) as class_section,
                c.id as class_id,
                cs.id as section_id,
                COUNT(sec.id) as total_subjects,
                ec.id as exam_class_id
            FROM school_exams e
            LEFT JOIN school_sessions s ON e.session_id = s.id
            LEFT JOIN school_exam_classes ec ON e.id = ec.exam_id AND ec.school_id = ?
            LEFT JOIN school_classes c ON ec.class_id = c.id
            LEFT JOIN school_class_sections cs ON ec.section_id = cs.id
            LEFT JOIN school_exam_subjects sec ON ec.id = sec.exam_class_id
            WHERE e.school_id = ?
        ";
        
        $params = [$school_id, $school_id];

        if ($exam_type) {
            $query .= " AND e.exam_type = ?";
            $params[] = $exam_type;
        }

        if ($session_id) {
            $query .= " AND e.session_id = ?";
            $params[] = $session_id;
        }

        if ($class_id) {
            $query .= " AND ec.class_id = ?";
            $params[] = $class_id;
        }

        if ($section_id) {
            $query .= " AND ec.section_id = ?";
            $params[] = $section_id;
        }

        if ($date_from) {
            $query .= " AND DATE(e.start_date) >= ?";
            $params[] = $date_from;
        }

        if ($date_to) {
            $query .= " AND DATE(e.end_date) <= ?";
            $params[] = $date_to;
        }

        $query .= " GROUP BY e.id, ec.id ORDER BY e.start_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate marks uploaded percentage
        foreach ($results as &$exam) {
            $exam['marks_uploaded_percent'] = 0;
            if (!empty($exam['exam_class_id'])) {
                $exam['marks_uploaded_percent'] = $this->getMarksUploadPercentage($exam['exam_class_id']);
            }
        }

        return $results;
    }

    /**
     * Get marks upload percentage for an exam class
     */
    private function getMarksUploadPercentage(int $exam_class_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT sr.student_id) as uploaded,
                COUNT(DISTINCT s.id) as total
            FROM school_exam_classes ec
            LEFT JOIN school_students s ON ec.class_id IN (
                SELECT a.class_id FROM school_student_academics a 
                WHERE a.section_id = ec.section_id
            )
            LEFT JOIN school_exam_results sr ON sr.exam_class_id = ec.id AND sr.student_id = s.id
            WHERE ec.id = ?
        ");
        $stmt->execute([$exam_class_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['total'] > 0) {
            return round(($result['uploaded'] / $result['total']) * 100);
        }
        return 0;
    }

    /**
     * Get marks upload statistics for school
     */
    public function getMarksUploadStatistics(int $school_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT e.id) as total_exams,
                COUNT(DISTINCT CASE WHEN sr.id IS NOT NULL THEN e.id END) as exams_with_marks,
                COUNT(DISTINCT CASE WHEN sr.id IS NULL THEN e.id END) as exams_without_marks,
                ROUND(COUNT(DISTINCT CASE WHEN sr.id IS NOT NULL THEN e.id END) / COUNT(DISTINCT e.id) * 100) as completion_rate
            FROM school_exams e
            LEFT JOIN school_exam_classes ec ON e.id = ec.exam_id
            LEFT JOIN school_exam_results sr ON ec.id = sr.exam_class_id
            WHERE e.school_id = ?
        ");
        $stmt->execute([$school_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get exam results
     */
    public function getExamResults(int $exam_id, int $school_id) {
        // Use total_makrs as default (common typo in the database)
        // The query will still work if the column name is different
        $sql = "
            SELECT 
                sm.id,
                sm.exam_id,
                sm.exam_subject_id,
                sm.student_id,
                COALESCE(sm.total_marks, sm.total_makrs, 0) as total_marks,
                sm.obtained_marks,
                sm.is_absent,
                sm.remarks,
                s.first_name,
                s.last_name,
                s.admission_no,
                sub.subject_name,
                c.class_name,
                c.id as class_id,
                cs.section_name,
                cs.id as section_id,
                ec.class_id as exam_class_id
            FROM school_exam_marks sm
            LEFT JOIN school_students s ON sm.student_id = s.id
            LEFT JOIN school_exam_subjects es ON sm.exam_subject_id = es.id
            LEFT JOIN school_subjects sub ON es.subject_id = sub.id
            LEFT JOIN school_exam_classes ec ON es.exam_class_id = ec.id
            LEFT JOIN school_classes c ON ec.class_id = c.id
            LEFT JOIN school_class_sections cs ON ec.section_id = cs.id
            WHERE sm.exam_id = ? AND sm.school_id = ?
            ORDER BY s.first_name, s.last_name, sub.subject_name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$exam_id, $school_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get database connection (for use in controller)
     */
    public function getDb() {
        return $this->db;
    }
}

