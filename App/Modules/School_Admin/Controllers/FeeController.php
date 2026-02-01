<?php
class FeeController {
    protected $db;
    protected $school_id;
    public function __construct() {
        require_once __DIR__ . '/../../Core/database.php';
        $this->db = \Database::connect();
        $this->school_id = $_SESSION['school_id'] ?? null;
    }

    public function listCategories() {
        require_once __DIR__ . '/../Models/FeeCategoryModel.php';
        $m = new FeeCategoryModel();
        return $m->getAllBySchool($this->school_id);
    }

    public function saveCategory($data) {
        require_once __DIR__ . '/../Models/FeeCategoryModel.php';
        $m = new FeeCategoryModel();
        $data['school_id'] = $this->school_id;
        return $m->create($data);
    }
}
