<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\ClassModel;
use App\Modules\School_Admin\Models\ClassSectionModel;
use PDO;

class ClassController {
    private PDO $db;
    private ClassModel $classModel;
    private ClassSectionModel $sectionModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->classModel = new ClassModel($db);
        $this->sectionModel = new ClassSectionModel($db);
    }

    /**
     * Create class with sections. $data expected to contain:
     * ['class_name','class_code','class_order','description','sections'=>[ ['section_name','room','capacity'], ... ] ]
     */
    public function createFromArray(int $school_id, int $session_id, array $data) {
        $this->db->beginTransaction();
        try {
            $class_name = $data['class_name'] ?? '';
            $class_code = $data['class_code'] ?? null;
            $grade_level = $data['grade_level'] ?? null;
            $class_order = $data['class_order'] ?? 0;
            $description = $data['description'] ?? null;
            $status = $data['status'] ?? 'active';

            $class_id = $this->classModel->create($school_id, $session_id, $class_name, $class_code, $grade_level, $class_order, $description, $status);

            // If class_code was empty, generate one as: id-<safe-class-name>-<session_id>
            if (empty($class_code)) {
                // try to fetch session name for a readable code
                $sessionLabel = $session_id;
                try {
                    $st = $this->db->prepare("SELECT name FROM school_sessions WHERE id = :id LIMIT 1");
                    $st->execute([':id' => $session_id]);
                    $sr = $st->fetch(PDO::FETCH_ASSOC);
                    if ($sr && !empty($sr['name'])) {
                        $sessionLabel = $sr['name'];
                    }
                } catch (\Exception $e) {
                    // ignore and fall back to id
                }

                $safe = preg_replace('/[^a-zA-Z0-9\-]+/', '-', strtolower(trim($class_name)));
                $sessSafe = preg_replace('/[^a-zA-Z0-9\-]+/', '-', strtolower(trim($sessionLabel)));
                $generated = $class_id . '-' . $safe . '-' . $sessSafe;
                // update record
                $this->classModel->update((int)$class_id, ['class_code' => $generated]);
            }

            if (!empty($data['sections']) && is_array($data['sections'])) {
                foreach ($data['sections'] as $s) {
                    $section_name = $s['section_name'] ?? null;
                    if (!$section_name) continue;
                    $section_code = $s['section_code'] ?? null;
                    $room_number = $s['room_number'] ?? null;
                    $capacity = isset($s['capacity']) && $s['capacity'] !== '' ? (int)$s['capacity'] : null;
                    $class_teacher_id = $s['class_teacher_id'] ?? null;
                    $this->sectionModel->create($school_id, $session_id, (int)$class_id, $section_name, $section_code, $room_number, $capacity, $class_teacher_id, 'active');
                }
            }

            $this->db->commit();
            return $class_id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
