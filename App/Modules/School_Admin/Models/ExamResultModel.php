<?php
/**
 * Model: ExamResultModel
 * Handles calculation and persistence of `school_exam_results`
 */
require_once __DIR__ . '/../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../Core/database.php';

class ExamResultModel {
    protected $db;

    public function __construct() {
        $this->db = \Database::connect();
    }

    /**
     * Calculate totals for a student for a given exam and insert/update result row
     * @param int $exam_id
     * @param int $student_id
     * @return bool
     */
    public function recalculateForExamStudent($exam_id, $student_id) {
        // Determine student's current enrollment (class + section) for this school
        $school_id = $_SESSION['school_id'] ?? null;

        $enrollSql = "SELECT class_id, section_id FROM school_student_enrollments WHERE student_id = ? AND school_id = ? AND deleted_at IS NULL ORDER BY id DESC LIMIT 1";
        $enstmt = $this->db->prepare($enrollSql);
        $enstmt->execute([$student_id, $school_id]);
        $en = $enstmt->fetch(PDO::FETCH_ASSOC);

        // Determine whether school_exam_classes has school_id column
        $colCheck = $this->db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $colCheck->execute(['school_exam_classes','school_id']);
        $hasEcSchoolId = (bool)$colCheck->fetchColumn();

        if (!$en) {
            // Fallback: sum across whole exam (best effort)
            if ($hasEcSchoolId) {
                $sql = "SELECT
                            SUM(COALESCE(m.obtained_marks,0)) AS total_obtained,
                            SUM(COALESCE(es.total_marks,0)) AS total_marks
                        FROM school_exam_subjects es
                        INNER JOIN school_exam_classes ec ON es.exam_class_id = ec.id
                        LEFT JOIN school_exam_marks m ON m.exam_subject_id = es.id AND m.student_id = ?
                        WHERE ec.exam_id = ? AND ec.school_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$student_id, $exam_id, $school_id]);
            } else {
                $sql = "SELECT
                            SUM(COALESCE(m.obtained_marks,0)) AS total_obtained,
                            SUM(COALESCE(es.total_marks,0)) AS total_marks
                        FROM school_exam_subjects es
                        INNER JOIN school_exam_classes ec ON es.exam_class_id = ec.id
                        LEFT JOIN school_exam_marks m ON m.exam_subject_id = es.id AND m.student_id = ?
                        WHERE ec.exam_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$student_id, $exam_id]);
            }
        } else {
            // Sum only subjects assigned to this student's class & section for the exam
            $classId = (int)$en['class_id'];
            $sectionId = (int)$en['section_id'];
            if ($hasEcSchoolId) {
                $sql = "SELECT
                            SUM(COALESCE(m.obtained_marks,0)) AS total_obtained,
                            SUM(COALESCE(es.total_marks,0)) AS total_marks
                        FROM school_exam_subjects es
                        INNER JOIN school_exam_classes ec ON es.exam_class_id = ec.id
                        LEFT JOIN school_exam_marks m ON m.exam_subject_id = es.id AND m.student_id = ?
                        WHERE ec.exam_id = ? AND ec.class_id = ? AND ec.section_id = ? AND ec.school_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$student_id, $exam_id, $classId, $sectionId, $school_id]);
            } else {
                $sql = "SELECT
                            SUM(COALESCE(m.obtained_marks,0)) AS total_obtained,
                            SUM(COALESCE(es.total_marks,0)) AS total_marks
                        FROM school_exam_subjects es
                        INNER JOIN school_exam_classes ec ON es.exam_class_id = ec.id
                        LEFT JOIN school_exam_marks m ON m.exam_subject_id = es.id AND m.student_id = ?
                        WHERE ec.exam_id = ? AND ec.class_id = ? AND ec.section_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$student_id, $exam_id, $classId, $sectionId]);
            }
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalObtained = isset($row['total_obtained']) ? (float)$row['total_obtained'] : 0.0;
        $totalMarks = isset($row['total_marks']) ? (float)$row['total_marks'] : 0.0;

        $percentage = $totalMarks > 0 ? ($totalObtained / $totalMarks) * 100 : 0;
        $percentage = round($percentage, 2);

        // Try grading criteria from DB first
        $grading = $this->getGradeFromCriteria($percentage, $school_id, null);
        if ($grading) {
            $grade = $grading['grade_name'];
            $result_status = $grading['is_pass'] ? 'pass' : 'fail';
        } else {
            // Fallback to simple grade mapping
            $grade = $this->gradeFromPercentage($percentage);
            $result_status = ($percentage >= 50) ? 'pass' : 'fail';
        }

        // Insert or update into school_exam_results
        // Upsert into results; include school_id only when column exists
        $colCheck2 = $this->db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $colCheck2->execute(['school_exam_results','school_id']);
        $hasResultsSchoolId = (bool)$colCheck2->fetchColumn();

        if ($hasResultsSchoolId) {
            $sqlUpsert = "INSERT INTO school_exam_results (school_id, exam_id, student_id, total_obtained, total_marks, percentage, grade, result_status, created_at)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE total_obtained = VALUES(total_obtained), total_marks = VALUES(total_marks), percentage = VALUES(percentage), grade = VALUES(grade), result_status = VALUES(result_status)";
            $stmt2 = $this->db->prepare($sqlUpsert);
            return $stmt2->execute([$school_id, $exam_id, $student_id, $totalObtained, $totalMarks, $percentage, $grade, $result_status]);
        } else {
            $sqlUpsert = "INSERT INTO school_exam_results (exam_id, student_id, total_obtained, total_marks, percentage, grade, result_status, created_at)
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE total_obtained = VALUES(total_obtained), total_marks = VALUES(total_marks), percentage = VALUES(percentage), grade = VALUES(grade), result_status = VALUES(result_status)";
            $stmt2 = $this->db->prepare($sqlUpsert);
            return $stmt2->execute([$exam_id, $student_id, $totalObtained, $totalMarks, $percentage, $grade, $result_status]);
        }
    }

    protected function gradeFromPercentage($p) {
        if ($p >= 80) return 'A';
        if ($p >= 70) return 'B';
        if ($p >= 60) return 'C';
        if ($p >= 50) return 'D';
        return 'F';
    }

    /**
     * Get grade info from `school_grading_criteria` for a given percentage and school.
     * Returns array: ['grade_name'=>..., 'gpa'=>..., 'is_pass'=>0|1] or null if not found.
     */
    protected function getGradeFromCriteria($percentage, $school_id, $grading_system = null) {
        // load active criteria for school (and grading_system if provided)
        $params = [$school_id];
        $sql = "SELECT grade_name, gpa, is_pass, min_percentage, max_percentage FROM school_grading_criteria WHERE school_id = ? AND status = 'active'";
        if ($grading_system !== null) {
            $sql .= " AND grading_system = ?";
            $params[] = $grading_system;
        }
        $sql .= " ORDER BY min_percentage DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            $min = (float)$r['min_percentage'];
            $max = (float)$r['max_percentage'];
            // inclusive range
            if ($percentage >= $min && $percentage <= $max) {
                return [
                    'grade_name' => $r['grade_name'],
                    'gpa' => isset($r['gpa']) ? $r['gpa'] : null,
                    'is_pass' => isset($r['is_pass']) ? (int)$r['is_pass'] : 0
                ];
            }
        }

        return null;
    }
}

?>
