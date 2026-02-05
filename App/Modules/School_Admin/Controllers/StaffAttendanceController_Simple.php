<?php
/**
 * Staff Attendance API Controller - Simple Version
 * Handles API requests for staff attendance management
 */

header('Content-Type: application/json');
session_start();

try {
    // 1. Load database configuration (returns an array)
    $configFile = dirname(dirname(dirname(__DIR__))) . '/Config/database.php';
    if (!file_exists($configFile)) {
        throw new Exception("Config file not found: $configFile");
    }
    $dbConfig = require $configFile;
    
    // 2. Create PDO connection using returned config array
    $dbHost = $dbConfig['host'] ?? '127.0.0.1';
    $dbName = $dbConfig['dbname'] ?? ($dbConfig['database'] ?? '');
    $dbUser = $dbConfig['username'] ?? ($dbConfig['user'] ?? 'root');
    $dbPass = $dbConfig['password'] ?? ($dbConfig['pass'] ?? '');

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // 3. Get action and school_id
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    // If POST JSON body contains action, prefer that (useful for fetch JSON requests)
    if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawBody = file_get_contents('php://input');
        $jsonBody = json_decode($rawBody, true);
        if (is_array($jsonBody) && !empty($jsonBody['action'])) {
            $action = $jsonBody['action'];
            // merge decoded body into _REQUEST for downstream handlers if needed
            $_REQUEST = array_merge($_REQUEST, $jsonBody);
            $_POST = array_merge($_POST, $jsonBody);
        }
    }
    $school_id = $_SESSION['school_id'] ?? 10;  // Default to school_id = 10 where test data exists
    
    if (!$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }
    
    // ============ API HANDLERS ============
    
    if ($action === 'getStaff') {
        handleGetStaff($pdo, $school_id);
    } 
    elseif ($action === 'getMonthlyData') {
        handleGetMonthlyData($pdo, $school_id);
    } 
    elseif ($action === 'mark') {
        handleMarkAttendance($pdo, $school_id);
    } 
    elseif ($action === 'bulkMark') {
        handleBulkMarkAttendance($pdo, $school_id);
    }
    elseif ($action === 'summary') {
        handleGetSummary($pdo, $school_id);
    }
    elseif ($action === 'departments') {
        handleGetDepartments($pdo, $school_id);
    }
    elseif ($action === 'stats') {
        handleGetStats($pdo, $school_id);
    }
    elseif ($action === 'bulkSave') {
        handleBulkSave($pdo, $school_id);
    }
    else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

// ============ HANDLER FUNCTIONS ============

function handleGetStaff($pdo, $school_id) {
    try {
        $staff_type = $_GET['staff_type'] ?? null;
        $department = $_GET['department'] ?? null;
        
        $staff = [];
        
        // Get teachers
        if ($staff_type === null || $staff_type === 'teacher') {
            $sql = "SELECT 
                id, 
                school_id, 
                'teacher' as staff_type, 
                'Teacher' as type_label,
                name, 
                email, 
                phone, 
                id_no as employee_id,
                role as designation,
                role as department,
                status 
            FROM school_teachers 
            WHERE school_id = ?";
            
            if ($department) {
                $sql .= " AND role = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$school_id, $department]);
            } else {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$school_id]);
            }
            
            $staff = array_merge($staff, $stmt->fetchAll());
        }
        
        // Get employees
        if ($staff_type === null || $staff_type === 'employee') {
            $sql = "SELECT 
                id, 
                school_id, 
                'employee' as staff_type, 
                'Employee' as type_label,
                name, 
                email, 
                phone, 
                id as employee_id,
                'Staff' as designation,
                'Admin' as department,
                status 
            FROM employees 
            WHERE school_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$school_id]);
            $staff = array_merge($staff, $stmt->fetchAll());
        }
        
        echo json_encode([
            'success' => true,
            'data' => $staff,
            'count' => count($staff)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetMonthlyData($pdo, $school_id) {
    try {
        $month = (int)($_GET['month'] ?? date('m')) + 1; // Convert from 0-indexed
        $year = (int)($_GET['year'] ?? date('Y'));
        $staff_type = $_GET['staff_type'] ?? null;
        
        if ($month < 1 || $month > 12) {
            throw new Exception("Invalid month");
        }
        
        // Get staff first
        $staff = [];
        
        if ($staff_type === null || $staff_type === 'teacher') {
            $sql = "SELECT id, school_id, 'teacher' as staff_type, name, role as designation, id_no as employee_id FROM school_teachers 
                    WHERE school_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$school_id]);
            $staff = array_merge($staff, $stmt->fetchAll());
        }
        
        if ($staff_type === null || $staff_type === 'employee') {
            $sql = "SELECT id, school_id, 'employee' as staff_type, name, 'Staff' as designation, id as employee_id FROM employees 
                    WHERE school_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$school_id]);
            $staff = array_merge($staff, $stmt->fetchAll());
        }
        
        // Get attendance for this month
        $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT);
        
        $sql = "SELECT * FROM school_staff_attendance 
                WHERE school_id = ? AND attendance_date BETWEEN ? AND ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id, $startDate, $endDate]);
        $attendance_records = $stmt->fetchAll();
        
        // Build attendance map
        $attendance_map = [];
        foreach ($attendance_records as $record) {
            $key = $record['staff_type'] . '_' . $record['staff_id'];
            $attendance_map[$key][$record['attendance_date']] = $record['status'];
        }
        
        // Add attendance to staff
        foreach ($staff as &$member) {
            $key = $member['staff_type'] . '_' . $member['id'];
            $member['attendance'] = $attendance_map[$key] ?? [];
        }
        
        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                      'July', 'August', 'September', 'October', 'November', 'December'];
        
        echo json_encode([
            'success' => true,
            'data' => $staff,
            'departments' => ['Teacher', 'Admin'],
            'year' => $year,
            'month' => $month,
            'month_name' => $monthNames[$month],
            'days_in_month' => $daysInMonth
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleMarkAttendance($pdo, $school_id) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $staff_type = $data['staff_type'] ?? null;
        $staff_id = $data['staff_id'] ?? null;
        $attendance_date = $data['attendance_date'] ?? null;
        $status = $data['status'] ?? null;
        $remarks = $data['remarks'] ?? null;
        $marked_by = $_SESSION['user_id'] ?? null;
        
        // Validate
        if (!$staff_type || !$staff_id || !$attendance_date || !$status) {
            throw new Exception("Missing required fields");
        }
        
        if (!in_array($status, ['P', 'A', 'L', 'HD', ''])) {
            throw new Exception("Invalid status");
        }
        
        // Check date is not in future
        if (strtotime($attendance_date) > time()) {
            throw new Exception("Cannot mark attendance for future dates");
        }
        
        // Insert or update
        $sql = "INSERT INTO school_staff_attendance 
                (school_id, staff_type, staff_id, attendance_date, status, remarks, marked_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE status = ?, remarks = ?, created_at = NOW()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id, $staff_type, $staff_id, $attendance_date, $status, $remarks, $marked_by, $status, $remarks]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => [
                'staff_type' => $staff_type,
                'staff_id' => $staff_id,
                'attendance_date' => $attendance_date,
                'status' => $status
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleBulkMarkAttendance($pdo, $school_id) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $staff_list = $data['staff_list'] ?? [];
        $attendance_date = $data['attendance_date'] ?? null;
        $status = $data['status'] ?? null;
        $marked_by = $_SESSION['user_id'] ?? null;
        
        if (!$staff_list || !$attendance_date || !$status) {
            throw new Exception("Missing required fields");
        }
        
        $pdo->beginTransaction();
        
        $count = 0;
        $sql = "INSERT INTO school_staff_attendance 
                (school_id, staff_type, staff_id, attendance_date, status, marked_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE status = ?";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($staff_list as $staff) {
            $stmt->execute([
                $school_id,
                $staff['staff_type'],
                $staff['staff_id'],
                $attendance_date,
                $status,
                $marked_by,
                $status
            ]);
            $count++;
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Attendance marked for $count staff members",
            'count' => $count
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetSummary($pdo, $school_id) {
    try {
        $staff_type = $_GET['staff_type'] ?? null;
        $staff_id = $_GET['staff_id'] ?? null;
        $month = (int)$_GET['month'] ?? date('m');
        $year = (int)$_GET['year'] ?? date('Y');
        
        if (!$staff_type || !$staff_id) {
            throw new Exception("Missing staff_type or staff_id");
        }
        
        $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT);
        
        $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'L' THEN 1 ELSE 0 END) as leave_days,
                SUM(CASE WHEN status = 'HD' THEN 1 ELSE 0 END) as halfday_days
                FROM school_staff_attendance
                WHERE school_id = ? AND staff_type = ? AND staff_id = ? 
                AND attendance_date BETWEEN ? AND ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id, $staff_type, $staff_id, $startDate, $endDate]);
        $result = $stmt->fetch();
        
        $total_days = $result['total_days'] ?? 0;
        $present_days = $result['present_days'] ?? 0;
        $percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_days' => $total_days,
                'present_days' => $present_days,
                'absent_days' => $result['absent_days'] ?? 0,
                'leave_days' => $result['leave_days'] ?? 0,
                'halfday_days' => $result['halfday_days'] ?? 0,
                'attendance_percentage' => $percentage
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetDepartments($pdo, $school_id) {
    try {
        $sql = "SELECT DISTINCT role FROM school_teachers WHERE school_id = ? ORDER BY role";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id]);
        $departments = array_column($stmt->fetchAll(), 'role');
        $departments[] = 'Admin'; // Add Admin for employees
        
        echo json_encode([
            'success' => true,
            'data' => $departments,
            'count' => count($departments)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleBulkSave($pdo, $school_id) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $staff_list = $data['staff_list'] ?? [];
        $attendance_date = $data['attendance_date'] ?? null;
        $marked_by = $_SESSION['user_id'] ?? null;

        if (!$staff_list || !$attendance_date) {
            throw new Exception('Missing required fields');
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO school_staff_attendance 
                (school_id, staff_type, staff_id, attendance_date, status, remarks, marked_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE status = ?, remarks = ?, marked_by = ?, created_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $count = 0;
        foreach ($staff_list as $item) {
            $staff_type = $item['staff_type'] ?? null;
            $staff_id = $item['staff_id'] ?? null;
            $status = $item['status'] ?? '';
            $remarks = $item['remarks'] ?? '';

            if (!$staff_type || !$staff_id) continue;

            $stmt->execute([$school_id, $staff_type, $staff_id, $attendance_date, $status, $remarks, $marked_by, $status, $remarks, $marked_by]);
            $count++;
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'count' => $count, 'message' => "Saved $count records"]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetStats($pdo, $school_id) {
    try {
        // Total staff: active teachers + active employees
        $sql = "SELECT COUNT(*) as cnt FROM school_teachers WHERE school_id = ? AND status = 'active'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id]);
        $teachersCount = (int)$stmt->fetchColumn();

        $sql = "SELECT COUNT(*) as cnt FROM employees WHERE school_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id]);
        $employeesCount = (int)$stmt->fetchColumn();

        $totalStaff = $teachersCount + $employeesCount;

        // Today's date
        $today = date('Y-m-d');

        // Present today
        $sql = "SELECT COUNT(*) FROM school_staff_attendance WHERE school_id = ? AND attendance_date = ? AND status = 'P'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id, $today]);
        $present = (int)$stmt->fetchColumn();

        // Absent today
        $sql = "SELECT COUNT(*) FROM school_staff_attendance WHERE school_id = ? AND attendance_date = ? AND status = 'A'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id, $today]);
        $absent = (int)$stmt->fetchColumn();

        // On leave today
        $sql = "SELECT COUNT(*) FROM school_staff_attendance WHERE school_id = ? AND attendance_date = ? AND status = 'L'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id, $today]);
        $leave = (int)$stmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'data' => [
                'total_staff' => $totalStaff,
                'present_today' => $present,
                'absent_today' => $absent,
                'on_leave_today' => $leave
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
