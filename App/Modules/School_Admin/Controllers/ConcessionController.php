<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\ConcessionModel;
use PDO;

class ConcessionController {
    private ConcessionModel $model;

    public function __construct(PDO $db) {
        $this->model = new ConcessionModel($db);
    }

    public function saveConcession(array $data) {
        // basic validation
        if (empty($data['admission_no'])) throw new \Exception('Missing admission_no');
        if (empty($data['session_id'])) throw new \Exception('Missing session');
        if (empty($data['type'])) throw new \Exception('Missing type');
        if (!isset($data['value']) || $data['value'] === '') throw new \Exception('Missing value');
        return $this->model->create($data);
    }

    public function listConcessions(int $school_id, array $filters = []) {
        return $this->model->listBySchool($school_id, $filters);
    }

    public function findStudent(int $school_id, string $admission_no) {
        return $this->model->findStudentByAdmission($school_id, $admission_no);
    }

    public function applyBulk(int $school_id, int $session_id, int $class_id, array $concessionData) {
        return $this->model->applyBulkToClass($school_id, $session_id, $class_id, $concessionData);
    }
}
