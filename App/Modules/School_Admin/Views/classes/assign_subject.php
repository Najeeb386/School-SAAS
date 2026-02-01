<?php
// robust include of autoloader
function require_autoloader() {
    $p = __DIR__;
    for ($i = 0; $i < 7; $i++) {
        $candidate = $p . DIRECTORY_SEPARATOR . 'autoloader.php';
        if (file_exists($candidate)) {
            require_once $candidate;
            // also try to include Core/database.php if present
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
    if (!require_autoloader()) throw new Exception('Autoloader not found');

    $school_id = $_POST['school_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;
    $class_id = $_POST['class_id'] ?? null;
    if (!$school_id || !$subject_id || !$class_id) {
        throw new Exception('Missing required fields');
    }
    $ctrl = new \App\Modules\School_Admin\Controllers\SubjectController();
    // Support arrays: section_id[] and teacher_id[]
    $sections = [];
    $teachers = [];
    if (isset($_POST['section_id'])) {
        $sections = is_array($_POST['section_id']) ? $_POST['section_id'] : [$_POST['section_id']];
    }
    if (isset($_POST['teacher_id'])) {
        $teachers = is_array($_POST['teacher_id']) ? $_POST['teacher_id'] : [$_POST['teacher_id']];
    }
    $session_id = $_POST['session_id'] ?? null;

    $created = [];
    // normalize ints
    $school_id = (int)$school_id; $subject_id = (int)$subject_id; $class_id = (int)$class_id;
    $sections = array_map('intval', $sections);
    $teachers = array_map('intval', $teachers);

    // If no sections and no teachers, create single assignment without section/teacher
    if (empty($sections) && empty($teachers)) {
        $id = $ctrl->assignToClass([
            'school_id' => $school_id,
            'subject_id' => $subject_id,
            'class_id' => $class_id,
            'section_id' => null,
            'teacher_id' => null,
            'session_id' => $session_id,
        ]);
        $created[] = $id;
    } else if (!empty($sections) && empty($teachers)) {
        // assign to each selected section without teacher
        foreach ($sections as $sec) {
            $id = $ctrl->assignToClass([
                'school_id' => $school_id,
                'subject_id' => $subject_id,
                'class_id' => $class_id,
                'section_id' => $sec,
                'teacher_id' => null,
                'session_id' => $session_id,
            ]);
            $created[] = $id;
        }
    } else if (empty($sections) && !empty($teachers)) {
        // assign to class with each teacher (no section)
        foreach ($teachers as $t) {
            $id = $ctrl->assignToClass([
                'school_id' => $school_id,
                'subject_id' => $subject_id,
                'class_id' => $class_id,
                'section_id' => null,
                'teacher_id' => $t,
                'session_id' => $session_id,
            ]);
            $created[] = $id;
        }
    } else {
        // both sections and teachers selected: create combinations
        foreach ($sections as $sec) {
            foreach ($teachers as $t) {
                $id = $ctrl->assignToClass([
                    'school_id' => $school_id,
                    'subject_id' => $subject_id,
                    'class_id' => $class_id,
                    'section_id' => $sec,
                    'teacher_id' => $t,
                    'session_id' => $session_id,
                ]);
                $created[] = $id;
            }
        }
    }

    echo json_encode(['success' => true, 'ids' => $created]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
