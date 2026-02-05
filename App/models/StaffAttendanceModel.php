<?php

// Make class available in both namespace and global scope
if (!class_exists('App\\Models\\StaffAttendanceModel', false)) {
    class_alias('StaffAttendanceModel', 'App\\Models\\StaffAttendanceModel');
}

class StaffAttendanceModel {
    
    private $pdo;
    private $school_id;
    
    public function __construct($pdo, $school_id) {
        $this->pdo = $pdo;
        $this->school_id = $school_id;
    }
    
    /**
     * Get all staff (teachers and employees) with optional filters
     * 
     * @param string $staff_type 'teacher', 'employee', or null for all
     * @param string $department_filter Optional department/role filter
     * @return array Staff list
     */
    public function getAllStaff($staff_type = null, $department_filter = null) {
        try {
            $staff = [];
            
            // Get teachers
            if ($staff_type === null || $staff_type === 'teacher') {
                $teacher_query = "SELECT 
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
                WHERE school_id = :school_id
                AND status = 'active'";
                
                if ($department_filter) {
                    $teacher_query .= " AND role = :role";
                }
                
                $teacher_query .= " ORDER BY name ASC";
                
                $stmt = $this->pdo->prepare($teacher_query);
                $params = [':school_id' => $this->school_id];
                
                if ($department_filter) {
                    $params[':role'] = $department_filter;
                }
                
                $stmt->execute($params);
                $staff = array_merge($staff, $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            
            // Get employees
            if ($staff_type === null || $staff_type === 'employee') {
                $employee_query = "SELECT 
                    id,
                    school_id,
                    'employee' as staff_type,
                    'Employee' as type_label,
                    name,
                    email,
                    phone,
                    CONCAT('EMP', LPAD(id, 5, '0')) as employee_id,
                    role_id as designation,
                    'Admin' as department,
                    status
                FROM employees
                WHERE school_id = :school_id
                AND status = 1";
                
                if ($department_filter) {
                    $employee_query .= " AND role_id = :role_id";
                }
                
                $employee_query .= " ORDER BY name ASC";
                
                $stmt = $this->pdo->prepare($employee_query);
                $params = [':school_id' => $this->school_id];
                
                if ($department_filter) {
                    $params[':role_id'] = $department_filter;
                }
                
                $stmt->execute($params);
                $staff = array_merge($staff, $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            
            // Sort by name
            usort($staff, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            
            return $staff;
            
        } catch (\Exception $e) {
            throw new \Exception("Error fetching staff: " . $e->getMessage());
        }
    }
    
    /**
     * Get attendance records for a specific month
     * 
     * @param int $year
     * @param int $month (1-12)
     * @param string $staff_type Optional filter by staff type
     * @return array Attendance records
     */
    public function getMonthlyAttendance($year, $month, $staff_type = null) {
        try {
            $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $query = "SELECT 
                id,
                school_id,
                staff_type,
                staff_id,
                attendance_date,
                status,
                remarks,
                marked_by,
                created_at,
                updated_at
            FROM school_staff_attendance
            WHERE school_id = :school_id
            AND attendance_date BETWEEN :start_date AND :end_date";
            
            $params = [
                ':school_id' => $this->school_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];
            
            if ($staff_type) {
                $query .= " AND staff_type = :staff_type";
                $params[':staff_type'] = $staff_type;
            }
            
            $query .= " ORDER BY attendance_date ASC, staff_id ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            throw new \Exception("Error fetching attendance: " . $e->getMessage());
        }
    }
    
    /**
     * Mark or update attendance for a staff member
     * 
     * @param string $staff_type 'teacher' or 'employee'
     * @param int $staff_id
     * @param string $attendance_date (YYYY-MM-DD)
     * @param string $status (P, A, L, HD)
     * @param string $remarks Optional remarks
     * @param int $marked_by User ID who marked the attendance
     * @return bool Success
     */
    public function markAttendance($staff_type, $staff_id, $attendance_date, $status, $remarks = null, $marked_by = null) {
        try {
            // Validate inputs
            $valid_statuses = ['P', 'A', 'L', 'HD'];
            if (!in_array($status, $valid_statuses)) {
                throw new \Exception("Invalid attendance status: $status");
            }
            
            $valid_types = ['teacher', 'employee'];
            if (!in_array($staff_type, $valid_types)) {
                throw new \Exception("Invalid staff type: $staff_type");
            }
            
            // Prevent future dates
            if (strtotime($attendance_date) > time()) {
                throw new \Exception("Cannot mark attendance for future dates");
            }
            
            // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both insert and update
            $query = "INSERT INTO school_staff_attendance 
                (school_id, staff_type, staff_id, attendance_date, status, remarks, marked_by, created_at, updated_at)
                VALUES (:school_id, :staff_type, :staff_id, :attendance_date, :status, :remarks, :marked_by, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                remarks = VALUES(remarks),
                marked_by = VALUES(marked_by),
                updated_at = NOW()";
            
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute([
                ':school_id' => $this->school_id,
                ':staff_type' => $staff_type,
                ':staff_id' => $staff_id,
                ':attendance_date' => $attendance_date,
                ':status' => $status,
                ':remarks' => $remarks,
                ':marked_by' => $marked_by
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            throw new \Exception("Error marking attendance: " . $e->getMessage());
        }
    }
    
    /**
     * Bulk mark attendance for multiple staff
     * 
     * @param array $staff_list Array of [staff_type, staff_id]
     * @param string $attendance_date
     * @param string $status
     * @param int $marked_by
     * @return int Number of records affected
     */
    public function bulkMarkAttendance($staff_list, $attendance_date, $status, $marked_by = null) {
        try {
            $count = 0;
            
            $this->pdo->beginTransaction();
            
            foreach ($staff_list as $staff) {
                $this->markAttendance(
                    $staff['staff_type'],
                    $staff['staff_id'],
                    $attendance_date,
                    $status,
                    null,
                    $marked_by
                );
                $count++;
            }
            
            $this->pdo->commit();
            
            return $count;
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new \Exception("Error in bulk mark attendance: " . $e->getMessage());
        }
    }
    
    /**
     * Get attendance record for specific staff and date
     * 
     * @param string $staff_type
     * @param int $staff_id
     * @param string $attendance_date
     * @return array|null Attendance record or null
     */
    public function getAttendanceRecord($staff_type, $staff_id, $attendance_date) {
        try {
            $query = "SELECT *
            FROM school_staff_attendance
            WHERE school_id = :school_id
            AND staff_type = :staff_type
            AND staff_id = :staff_id
            AND attendance_date = :attendance_date";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':school_id' => $this->school_id,
                ':staff_type' => $staff_type,
                ':staff_id' => $staff_id,
                ':attendance_date' => $attendance_date
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            throw new \Exception("Error fetching attendance record: " . $e->getMessage());
        }
    }
    
    /**
     * Get attendance summary for a staff member for a month
     * 
     * @param string $staff_type
     * @param int $staff_id
     * @param int $year
     * @param int $month
     * @return array Summary with counts
     */
    public function getAttendanceSummary($staff_type, $staff_id, $year, $month) {
        try {
            $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $query = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'L' THEN 1 ELSE 0 END) as leave_days,
                SUM(CASE WHEN status = 'HD' THEN 1 ELSE 0 END) as halfday_days,
                ROUND((SUM(CASE WHEN status IN ('P', 'HD') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage
            FROM school_staff_attendance
            WHERE school_id = :school_id
            AND staff_type = :staff_type
            AND staff_id = :staff_id
            AND attendance_date BETWEEN :start_date AND :end_date";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':school_id' => $this->school_id,
                ':staff_type' => $staff_type,
                ':staff_id' => $staff_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
        } catch (\Exception $e) {
            throw new \Exception("Error fetching attendance summary: " . $e->getMessage());
        }
    }
    
    /**
     * Get all unique departments/roles
     * 
     * @return array Departments
     */
    public function getDepartments() {
        try {
            $departments = [];
            
            // Get teacher roles
            $teacher_query = "SELECT DISTINCT role FROM school_teachers 
                WHERE school_id = :school_id AND status = 'active' ORDER BY role";
            $stmt = $this->pdo->prepare($teacher_query);
            $stmt->execute([':school_id' => $this->school_id]);
            $teacher_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($teacher_roles as $role) {
                $departments[$role] = $role;
            }
            
            // Add employee department
            $departments['Admin'] = 'Admin';
            
            return array_values($departments);
            
        } catch (\Exception $e) {
            throw new \Exception("Error fetching departments: " . $e->getMessage());
        }
    }
    
    /**
     * Get staff with their latest attendance status
     * 
     * @param int $year
     * @param int $month
     * @param string $staff_type Optional filter
     * @return array Staff with attendance data
     */
    public function getStaffWithAttendance($year, $month, $staff_type = null) {
        try {
            $staff = $this->getAllStaff($staff_type);
            $attendance = $this->getMonthlyAttendance($year, $month, $staff_type);
            
            // Create attendance lookup
            $attendance_lookup = [];
            foreach ($attendance as $record) {
                $key = $record['staff_type'] . '_' . $record['staff_id'] . '_' . $record['attendance_date'];
                $attendance_lookup[$key] = $record;
            }
            
            // Add attendance to staff
            foreach ($staff as &$member) {
                $member['attendance'] = [];
                
                // Get all dates in month
                $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
                $end_date = date('Y-m-t', strtotime($start_date));
                $current = strtotime($start_date);
                
                while ($current <= strtotime($end_date)) {
                    $date = date('Y-m-d', $current);
                    $key = $member['staff_type'] . '_' . $member['id'] . '_' . $date;
                    
                    if (isset($attendance_lookup[$key])) {
                        $member['attendance'][$date] = $attendance_lookup[$key]['status'];
                    } else {
                        $member['attendance'][$date] = null;
                    }
                    
                    $current = strtotime('+1 day', $current);
                }
            }
            
            return $staff;
            
        } catch (\Exception $e) {
            throw new \Exception("Error fetching staff with attendance: " . $e->getMessage());
        }
    }
}
