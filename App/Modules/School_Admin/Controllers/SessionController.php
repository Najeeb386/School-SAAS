<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\SessionModel;

class SessionController {
    protected $model;
    protected $school_id;

    public function __construct($DB_con) {
        $this->model = new SessionModel($DB_con);
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
    }

    public function list() {
        return $this->model->getAll($this->school_id);
    }

    public function get($id) {
        return $this->model->getById($id, $this->school_id);
    }

    public function createFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if ($name === '') { $_SESSION['flash_error'] = 'Session name is required.'; return false; }

        $start = isset($_POST['start_date']) ? trim($_POST['start_date']) : null;
        $end = isset($_POST['end_date']) ? trim($_POST['end_date']) : null;
        if (!$start || !$end) { $_SESSION['flash_error'] = 'Start and end dates are required.'; return false; }

        $data = [
            'name'=>$name,
            'start_date'=>$start,
            'end_date'=>$end,
            'is_active'=>isset($_POST['is_active']) ? 1 : 0,
            'created_by'=>isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'updated_by'=>isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        ];

        $created = $this->model->create($this->school_id, $data);
        if ($created) { $_SESSION['flash_success'] = 'Session created.'; return $created; }
        $_SESSION['flash_error'] = 'Failed to create session.'; return false;
    }

    public function updateFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) { $_SESSION['flash_error'] = 'Invalid session id.'; return false; }

        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if ($name === '') { $_SESSION['flash_error'] = 'Session name is required.'; return false; }

        $data = [
            'name'=>$name,
            'start_date'=>isset($_POST['start_date']) ? trim($_POST['start_date']) : null,
            'end_date'=>isset($_POST['end_date']) ? trim($_POST['end_date']) : null,
            'is_active'=>isset($_POST['is_active']) ? 1 : 0,
            'updated_by'=>isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        ];

        $ok = $this->model->update($id, $this->school_id, $data);
        if ($ok) { $_SESSION['flash_success'] = 'Session updated.'; return true; }
        $_SESSION['flash_error'] = 'Failed to update session.'; return false;
    }

    public function deleteFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) { $_SESSION['flash_error'] = 'Invalid session id.'; return false; }
        $ok = $this->model->delete($id, $this->school_id);
        if ($ok) { $_SESSION['flash_success'] = 'Session deleted.'; return true; }
        $_SESSION['flash_error'] = 'Failed to delete session.'; return false;
    }
}
