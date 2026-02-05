<?php

namespace App\Modules\School_Admin\Controllers;

use App\Models\StaffAttendanceModel;
use PDO;

class StaffAttendanceController {
    
    private $pdo;
    private $school_id;
    private $model;
    private $user_id;
    
    public function __construct($pdo = null, $school_id = null, $user_id = null) {
        // Get database connection
        if ($pdo === null) {
            require_once __DIR__ . '/../../Config/database.php';
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        $this->pdo = $pdo;
        
        // Get school_id from session or parameter
        if ($school_id === null) {
            session_start();
            $this->school_id = $_SESSION['school_id'] ?? 1;
        } else {
            $this->school_id = $school_id;
        }
        
        // Get user_id from session or parameter
        if ($user_id === null) {
            $this->user_id = $_SESSION['user_id'] ?? null;
        } else {
            $this->user_id = $user_id;
        }
        
        // Initialize model
        $this->model = new StaffAttendanceModel($this->pdo, $this->school_id);
    }
    
    /**
     * API Endpoint: Get staff list
     * GET /api/attendance/staff?month=2&year=2026&staff_type=teacher
     */
    public function getStaff() {
        try {
            $staff_type = $_GET['staff_type'] ?? null;
            $department = $_GET['department'] ?? null;
            
            $staff = $this->model->getAllStaff($staff_type, $department);
            
            return $this->response(true, [
                'data' => $staff,
                'count' => count($staff)
            ]);
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 500);
        }
    }
    
    /**
     * API Endpoint: Get monthly attendance data
     * GET /api/attendance/monthly?month=2&year=2026
     */
    public function getMonthlyData() {
        try {
            $month = (int)($_GET['month'] ?? date('m'));
            $year = (int)($_GET['year'] ?? date('Y'));
            $staff_type = $_GET['staff_type'] ?? null;
            
            // Validate month
            if ($month < 1 || $month > 12) {
                throw new \Exception("Invalid month: $month");
            }
            
            $staff = $this->model->getStaffWithAttendance($year, $month, $staff_type);
            $departments = $this->model->getDepartments();
            
            return $this->response(true, [
                'data' => $staff,
                'departments' => $departments,
                'year' => $year,
                'month' => $month,
                'month_name' => $this->getMonthName($month),
                'days_in_month' => cal_days_in_month(CAL_GREGORIAN, $month, $year)
            ]);
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 500);
        }
    }
    
    /**
     * API Endpoint: Get attendance records for specific date range
     * GET /api/attendance/records?start_date=2026-02-01&end_date=2026-02-28
     */
    public function getAttendanceRecords() {
        try {
            $month = (int)($_GET['month'] ?? date('m'));
            $year = (int)($_GET['year'] ?? date('Y'));
            
            $records = $this->model->getMonthlyAttendance($year, $month);
            
            return $this->response(true, [
                'data' => $records,
                'count' => count($records),
                'year' => $year,
                'month' => $month
            ]);
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 500);
        }
    }
    
    /**
     * API Endpoint: Mark or update attendance
     * POST /api/attendance/mark
     * 
     * Request body:
     * {
     *     "staff_type": "teacher",
     *     "staff_id": 1,
     *     "attendance_date": "2026-02-05",
     *     "status": "P",
     *     "remarks": "Optional remarks"
     * }
     */
    public function markAttendance() {
        try {
            $input = $this->getJsonInput();
            
            // Validate required fields
            $required = ['staff_type', 'staff_id', 'attendance_date', 'status'];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    throw new \Exception("Missing required field: $field");
                }
            }
            
            // Mark attendance
            $result = $this->model->markAttendance(
                $input['staff_type'],
                (int)$input['staff_id'],
                $input['attendance_date'],
                $input['status'],
                $input['remarks'] ?? null,
                $this->user_id
            );
            
            if ($result) {
                return $this->response(true, [
                    'message' => 'Attendance marked successfully',
                    'data' => [
                        'staff_type' => $input['staff_type'],
                        'staff_id' => $input['staff_id'],
                        'attendance_date' => $input['attendance_date'],
                        'status' => $input['status']
                    ]
                ]);
            } else {
                return $this->response(false, [], 'Failed to mark attendance');
            }
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 400);
        }
    }
    
    /**
     * API Endpoint: Bulk mark attendance
     * POST /api/attendance/bulk-mark
     * 
     * Request body:
     * {
     *     "staff_list": [
     *         {"staff_type": "teacher", "staff_id": 1},
     *         {"staff_type": "employee", "staff_id": 2}
     *     ],
     *     "attendance_date": "2026-02-05",
     *     "status": "P"
     * }
     */
    public function bulkMarkAttendance() {
        try {
            $input = $this->getJsonInput();
            
            if (!isset($input['staff_list']) || !isset($input['attendance_date']) || !isset($input['status'])) {
                throw new \Exception("Missing required fields: staff_list, attendance_date, status");
            }
            
            $count = $this->model->bulkMarkAttendance(
                $input['staff_list'],
                $input['attendance_date'],
                $input['status'],
                $this->user_id
            );
            
            return $this->response(true, [
                'message' => "Attendance marked for $count staff members",
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 400);
        }
    }
    
    /**
     * API Endpoint: Get attendance summary for a staff member
     * GET /api/attendance/summary?staff_type=teacher&staff_id=1&month=2&year=2026
     */
    public function getAttendanceSummary() {
        try {
            $staff_type = $_GET['staff_type'] ?? null;
            $staff_id = (int)($_GET['staff_id'] ?? 0);
            $month = (int)($_GET['month'] ?? date('m'));
            $year = (int)($_GET['year'] ?? date('Y'));
            
            if (!$staff_type || !$staff_id) {
                throw new \Exception("Missing required parameters: staff_type, staff_id");
            }
            
            $summary = $this->model->getAttendanceSummary($staff_type, $staff_id, $year, $month);
            
            return $this->response(true, [
                'data' => $summary,
                'staff_type' => $staff_type,
                'staff_id' => $staff_id,
                'year' => $year,
                'month' => $month
            ]);
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 400);
        }
    }
    
    /**
     * API Endpoint: Get departments
     * GET /api/attendance/departments
     */
    public function getDepartments() {
        try {
            $departments = $this->model->getDepartments();
            
            return $this->response(true, [
                'data' => $departments,
                'count' => count($departments)
            ]);
            
        } catch (\Exception $e) {
            return $this->response(false, [], $e->getMessage(), 500);
        }
    }
    
    /**
     * Route API requests
     */
    public function handleRequest($action) {
        header('Content-Type: application/json');
        
        // Check request method
        $method = $_SERVER['REQUEST_METHOD'];
        
        try {
            switch ($action) {
                case 'getStaff':
                    if ($method !== 'GET') throw new \Exception("Method not allowed");
                    echo json_encode($this->getStaff());
                    break;
                    
                case 'getMonthlyData':
                    if ($method !== 'GET') throw new \Exception("Method not allowed");
                    echo json_encode($this->getMonthlyData());
                    break;
                    
                case 'getRecords':
                    if ($method !== 'GET') throw new \Exception("Method not allowed");
                    echo json_encode($this->getAttendanceRecords());
                    break;
                    
                case 'mark':
                    if ($method !== 'POST') throw new \Exception("Method not allowed");
                    echo json_encode($this->markAttendance());
                    break;
                    
                case 'bulkMark':
                    if ($method !== 'POST') throw new \Exception("Method not allowed");
                    echo json_encode($this->bulkMarkAttendance());
                    break;
                    
                case 'summary':
                    if ($method !== 'GET') throw new \Exception("Method not allowed");
                    echo json_encode($this->getAttendanceSummary());
                    break;
                    
                case 'departments':
                    if ($method !== 'GET') throw new \Exception("Method not allowed");
                    echo json_encode($this->getDepartments());
                    break;
                    
                default:
                    throw new \Exception("Unknown action: $action");
            }
        } catch (\Exception $e) {
            echo json_encode($this->response(false, [], $e->getMessage(), 400));
        }
    }
    
    // ======================== HELPER METHODS ========================
    
    /**
     * Get JSON input from request body
     */
    private function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Send JSON response
     */
    private function response($success, $data = [], $message = '', $status = 200) {
        http_response_code($status);
        
        $response = [
            'success' => $success,
            'data' => $data
        ];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        return $response;
    }
    
    /**
     * Get month name
     */
    private function getMonthName($month) {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$month] ?? 'Unknown';
    }
}
