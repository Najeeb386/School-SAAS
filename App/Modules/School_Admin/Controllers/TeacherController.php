<?php
namespace App\Modules\School_Admin\Controllers;

use App\Modules\School_Admin\Models\TeacherModel;

class TeacherController {
    protected $model;
    protected $school_id;

    public function __construct($DB_con) {
        $this->model = new TeacherModel($DB_con);
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->school_id = isset($_SESSION['school_id']) ? intval($_SESSION['school_id']) : 0;
    }

    public function list() {
        return $this->model->getAll($this->school_id);
    }

    public function createFromRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        if ($name === '') { $_SESSION['flash_error'] = 'Name is required.'; return false; }
        $data = [];
        $data['name'] = $name;
        $data['email'] = isset($_POST['email']) ? trim($_POST['email']) : null;
        $data['phone'] = isset($_POST['phone']) ? trim($_POST['phone']) : null;
        $data['id_no'] = isset($_POST['id_no']) ? trim($_POST['id_no']) : null;
        $role = isset($_POST['role']) ? trim($_POST['role']) : 'teacher';
        if ($role === 'other' && !empty($_POST['role_other'])) {
            $role = trim($_POST['role_other']);
        }
        $data['role'] = $role;
        // permissions can be provided as JSON or comma separated
        if (isset($_POST['permissions'])) {
            $data['permissions'] = is_array($_POST['permissions']) ? json_encode($_POST['permissions']) : trim($_POST['permissions']);
        }

        // handle upload (store in faculty folder, name includes school id and role)
        if (!empty($_FILES['photo']) && isset($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
            $uploadRes = $this->handleUpload($_FILES['photo'], $role);
            if ($uploadRes['success']) $data['photo_path'] = $uploadRes['path'];
            else { $_SESSION['flash_error'] = $uploadRes['error']; }
        }

        $created = $this->model->create($this->school_id, $data);
        if ($created) {
            $_SESSION['flash_success'] = 'Teacher saved.';
            return $created;
        }
        $_SESSION['flash_error'] = 'Failed to save teacher.';
        return false;
    }

    protected function handleUpload($file, $role = 'faculty') {
        // basic validation
        if ($file['error'] !== UPLOAD_ERR_OK) return ['success'=>false,'error'=>'Upload error'];
        $max = 2 * 1024 * 1024;
        if ($file['size'] > $max) return ['success'=>false,'error'=>'File too large'];
        $allowed = ['image/jpeg','image/png','image/jpg'];
        if (!in_array($file['type'], $allowed)) return ['success'=>false,'error'=>'Invalid file type'];

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        // sanitize role for filename
        $safeRole = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower($role));
        // build filename: faculty_{schoolid}_{role}_{timestamp}_{uniq}.{ext}
        $uniq = bin2hex(random_bytes(4));
        $name = 'faculty_' . $this->school_id . '_' . $safeRole . '_' . time() . '_' . $uniq . '.' . $ext;
        $destDir = __DIR__ . '/../../../../Storage/uploads/schools/school_' . $this->school_id . '/faculty/';
        if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
        $destPath = $destDir . $name;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) return ['success'=>false,'error'=>'Failed to move uploaded file'];
        // return web-relative path
        $webPath = 'Storage/uploads/schools/school_' . $this->school_id . '/faculty/' . $name;
        return ['success'=>true,'path'=>$webPath];
    }

}
