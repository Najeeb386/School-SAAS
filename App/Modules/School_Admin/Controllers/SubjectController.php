<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\SubjectModel;
use App\Modules\School_Admin\Models\SubjectAssignmentModel;
use Database;

class SubjectController {
    protected SubjectModel $subjectModel;
    protected SubjectAssignmentModel $assignmentModel;

    public function __construct()
    {
        $db = Database::connect();
        $this->subjectModel = new SubjectModel($db);
        $this->assignmentModel = new SubjectAssignmentModel($db);
    }

    public function saveSubject(array $data) {
        $teacher_id = $data['teacher_id'] ?? null;
        $status = $data['status'] ?? 'active';
        return $this->subjectModel->create($data['school_id'], $data['name'], $teacher_id, $status);
    }

    public function assignToClass(array $data) {
        return $this->assignmentModel->create(
            $data['school_id'],
            $data['subject_id'],
            $data['class_id'],
            $data['section_id'] ?? null,
            $data['teacher_id'] ?? null,
            $data['session_id'] ?? null
        );
    }

    public function listAssignments(int $school_id, int $class_id, ?int $session_id = null) {
        return $this->assignmentModel->listByClass($school_id, $class_id, $session_id);
    }
}
