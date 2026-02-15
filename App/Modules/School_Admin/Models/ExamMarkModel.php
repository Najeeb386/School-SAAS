<?php
/**
 * Model: ExamMarkModel
 * Handles CRUD for `school_exam_marks` table
 */
require_once __DIR__ . '/../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../Core/database.php';

class ExamMarkModel {
    protected $db;

    public function __construct() {
        $this->db = \Database::connect();
    }

    protected function hasColumn($table, $column) {
        $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$table, $column]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Insert or update a mark for a student for an exam subject
     * @param int $exam_subject_id
     * @param int $student_id
     * @param float|null $obtained_marks
     * @param int $is_absent
     * @param string|null $remarks
     * @return bool
     */
    public function upsertMark($exam_id, $exam_subject_id, $student_id, $obtained_marks, $is_absent = 0, $remarks = null, $total_marks = null) {
        // Determine whether school_id and exam_id columns exist in school_exam_marks
        $hasSchoolId = $this->hasColumn('school_exam_marks', 'school_id');
        $hasExamIdCol = $this->hasColumn('school_exam_marks', 'exam_id');
        $hasTotalMarks = $this->hasColumn('school_exam_marks', 'total_marks');
        $hasTotalMakrs = $this->hasColumn('school_exam_marks', 'total_makrs'); // Check for typo
        $school_id = $_SESSION['school_id'] ?? null;
        
        // If total_marks not provided, fetch from exam_subject
        if ($total_marks === null) {
            $total_marks = $this->getTotalMarksForSubject($exam_subject_id);
        }

        // Use the correct column name based on what exists in the database
        $totalMarksColumn = $hasTotalMarks ? 'total_marks' : ($hasTotalMakrs ? 'total_makrs' : null);

        if ($hasSchoolId && $hasExamIdCol) {
            if ($totalMarksColumn) {
                $sql = "INSERT INTO school_exam_marks (school_id, exam_id, exam_subject_id, student_id, $totalMarksColumn, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE $totalMarksColumn = VALUES($totalMarksColumn), obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$school_id, $exam_id, $exam_subject_id, $student_id, $total_marks, $obtained_marks, (int)$is_absent, $remarks]);
            } else {
                $sql = "INSERT INTO school_exam_marks (school_id, exam_id, exam_subject_id, student_id, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$school_id, $exam_id, $exam_subject_id, $student_id, $obtained_marks, (int)$is_absent, $remarks]);
            }
        } elseif ($hasSchoolId && !$hasExamIdCol) {
            if ($totalMarksColumn) {
                $sql = "INSERT INTO school_exam_marks (school_id, exam_subject_id, student_id, $totalMarksColumn, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE $totalMarksColumn = VALUES($totalMarksColumn), obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$school_id, $exam_subject_id, $student_id, $total_marks, $obtained_marks, (int)$is_absent, $remarks]);
            } else {
                $sql = "INSERT INTO school_exam_marks (school_id, exam_subject_id, student_id, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$school_id, $exam_subject_id, $student_id, $obtained_marks, (int)$is_absent, $remarks]);
            }
        } elseif (!$hasSchoolId && $hasExamIdCol) {
            if ($totalMarksColumn) {
                $sql = "INSERT INTO school_exam_marks (exam_id, exam_subject_id, student_id, $totalMarksColumn, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE $totalMarksColumn = VALUES($totalMarksColumn), obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$exam_id, $exam_subject_id, $student_id, $total_marks, $obtained_marks, (int)$is_absent, $remarks]);
            } else {
                $sql = "INSERT INTO school_exam_marks (exam_id, exam_subject_id, student_id, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$exam_id, $exam_subject_id, $student_id, $obtained_marks, (int)$is_absent, $remarks]);
            }
        } else {
            if ($totalMarksColumn) {
                $sql = "INSERT INTO school_exam_marks (exam_subject_id, student_id, $totalMarksColumn, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE $totalMarksColumn = VALUES($totalMarksColumn), obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$exam_subject_id, $student_id, $total_marks, $obtained_marks, (int)$is_absent, $remarks]);
            } else {
                $sql = "INSERT INTO school_exam_marks (exam_subject_id, student_id, obtained_marks, is_absent, remarks, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE obtained_marks = VALUES(obtained_marks), is_absent = VALUES(is_absent), remarks = VALUES(remarks), updated_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$exam_subject_id, $student_id, $obtained_marks, (int)$is_absent, $remarks]);
            }
        }
    }
    
    /**
     * Get total marks for an exam subject
     * @param int $exam_subject_id
     * @return int
     */
    private function getTotalMarksForSubject($exam_subject_id) {
        $stmt = $this->db->prepare("SELECT total_marks FROM school_exam_subjects WHERE id = ?");
        $stmt->execute([$exam_subject_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total_marks'] : 0;
    }

    /**
     * Bulk upsert marks array
     * @param array $marks Array of arrays with keys: student_id, obtained_marks, is_absent, remarks
     * @param int $exam_subject_id
     * @return bool
     */
    public function upsertMarksBulk($exam_id, $exam_subject_id, array $marks) {
        foreach ($marks as $m) {
            $studentId = (int)($m['student_id'] ?? 0);
            if (!$studentId) continue;
            $obt = isset($m['obtained_marks']) && $m['obtained_marks'] !== '' ? $m['obtained_marks'] : null;
            $is_absent = isset($m['is_absent']) ? (int)$m['is_absent'] : 0;
            $remarks = isset($m['remarks']) ? $m['remarks'] : null;
            $ok = $this->upsertMark($exam_id, $exam_subject_id, $studentId, $obt, $is_absent, $remarks);
            if (!$ok) return false;
        }
        return true;
    }
}

?>
