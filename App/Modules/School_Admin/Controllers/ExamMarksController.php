<?php
/**
 * Controller: ExamMarksController
 * Coordinates mark upserts and result recalculation
 */
require_once __DIR__ . '/../Models/ExamMarkModel.php';
require_once __DIR__ . '/../Models/ExamResultModel.php';

class ExamMarksController {
    protected $markModel;
    protected $resultModel;

    public function __construct() {
        $this->markModel = new ExamMarkModel();
        $this->resultModel = new ExamResultModel();
    }

    /**
     * Save marks for an exam subject and recalculate results for affected students
     * @param int $exam_id
     * @param int $exam_subject_id
     * @param array $marks Array of mark rows
     * @return array [success => bool, message => string]
     */
    public function saveMarks($exam_id, $exam_subject_id, array $marks) {
        $db = \Database::connect();
        $school_id = $_SESSION['school_id'] ?? null;
        try {
            $db->beginTransaction();

            // Upsert marks (pass exam_id for models that store it)
            $ok = $this->markModel->upsertMarksBulk($exam_id, $exam_subject_id, $marks);
            if (!$ok) {
                $db->rollBack();
                return ['success' => false, 'message' => 'Failed to insert marks'];
            }

            // Recalculate results for unique students
            $studentIds = [];
            foreach ($marks as $m) {
                $sid = (int)($m['student_id'] ?? 0);
                if ($sid) $studentIds[$sid] = true;
            }

            foreach (array_keys($studentIds) as $sid) {
                $this->resultModel->recalculateForExamStudent($exam_id, $sid);
            }

            $db->commit();
            return ['success' => true, 'message' => 'Marks saved and results updated'];
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

?>
