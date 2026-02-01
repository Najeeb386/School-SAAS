<?php
// robust include of autoloader: search up to 6 dirs
function require_autoloader() {
    $p = __DIR__;
    for ($i = 0; $i < 7; $i++) {
        $candidate = $p . DIRECTORY_SEPARATOR . 'autoloader.php';
        if (file_exists($candidate)) {
            require_once $candidate;
            // also try to include Core/database.php if present (Database class is not autoloaded)
            $coreDb = dirname($candidate) . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'database.php';
            if (file_exists($coreDb)) require_once $coreDb;
            return true;
        }
        $p = dirname($p);
        if ($p === DIRECTORY_SEPARATOR || $p === '.' || $p === '') break;
    }
    return false;
}

header('Content-Type: application/json');
try {
    if (!require_autoloader()) {
        throw new Exception('Autoloader not found');
    }

    $school_id = $_POST['school_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    if (!$school_id || $name === '') {
        throw new Exception('Missing required fields');
    }
    $ctrl = new \App\Modules\School_Admin\Controllers\SubjectController();
    $id = $ctrl->saveSubject([
        'school_id' => (int)$school_id,
        'name' => $name,
        'teacher_id' => isset($_POST['teacher_id']) ? ($_POST['teacher_id'] === '' ? null : (int)$_POST['teacher_id']) : null,
        'status' => $_POST['status'] ?? 'active'
    ]);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
