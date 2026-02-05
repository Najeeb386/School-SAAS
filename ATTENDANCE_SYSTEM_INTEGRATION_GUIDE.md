# Staff Attendance System - Integration Guide

## Overview
This guide explains how to integrate the Staff Attendance System with your database and create the necessary backend endpoints.

---

## Part 1: Current UI Features

### Filter Section (Above Calendar)
Located at the top of the attendance page, users can:

1. **Select Month**: Dropdown with all 12 months
2. **Select Year**: Dropdown with ±2 years from current year
3. **Select Department**: Filter by department (Teaching, Library, Admin, Finance, Support, or All)
4. **Apply Filter**: Submit the filter selection
5. **Reset Filter**: Return to current date and clear all filters

### Calendar Table Layout
- **First Column**: Staff ID, Staff Name, Designation
- **Remaining Columns**: One per date in the selected month
- **Sunday Highlighting**: Red background for Sundays
- **Attendance Badges**: Color-coded status indicators
  - **P (Green)**: Present
  - **A (Red)**: Absent
  - **L (Yellow)**: Leave
  - **HD (Blue)**: Half Day

### Current Data Source
- **Status**: Using sample data (8 hardcoded staff members)
- **Dates**: Automatically generated for selected month
- **Attendance Records**: Sample data in JavaScript

---

## Part 2: Database Setup

### Step 1: Create Tables
Execute the SQL file to create all required tables:

```bash
mysql -u [username] -p [database_name] < SQL/staff_attendance_tables.sql
```

**Tables Created**:
1. `school_staff` - Staff master data
2. `staff_attendance` - Daily attendance records
3. `staff_attendance_summary` - Monthly summaries
4. `leave_types` - Leave type definitions
5. `attendance_settings` - School configuration

**Views Created**:
1. `v_current_month_attendance` - Current month view
2. `v_attendance_summary_report` - Summary report view

### Step 2: Verify Table Creation

```sql
SHOW TABLES LIKE 'staff_%';
DESC school_staff;
DESC staff_attendance;
```

### Step 3: Insert Sample Data

Sample data is included in the SQL file. Verify:

```sql
SELECT COUNT(*) FROM school_staff WHERE school_id = 1;
SELECT COUNT(*) FROM staff_attendance WHERE school_id = 1;
```

---

## Part 3: Backend API Endpoints

### Endpoint 1: Get Staff List with Filters

**File**: `App/Modules/School_Admin/Controllers/AttendanceController.php`

```php
<?php
namespace App\Modules\School_Admin\Controllers;

use PDO;

class AttendanceController {
    
    private $pdo;
    private $school_id;
    
    public function __construct($pdo, $school_id) {
        $this->pdo = $pdo;
        $this->school_id = $school_id;
    }
    
    /**
     * Get staff list with optional filtering
     * GET /api/attendance/staff?month=2&year=2026&department=Teaching
     */
    public function getStaff() {
        try {
            $month = isset($_GET['month']) ? (int)$_GET['month'] + 1 : date('m');
            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
            $department = isset($_GET['department']) && $_GET['department'] !== '' ? $_GET['department'] : null;
            
            $query = "SELECT 
                        ss.id,
                        ss.employee_id,
                        ss.name,
                        ss.designation,
                        ss.department,
                        ss.email,
                        ss.phone
                     FROM school_staff ss
                     WHERE ss.school_id = :school_id
                     AND ss.status = 'active'";
            
            $params = [':school_id' => $this->school_id];
            
            // Add department filter if specified
            if ($department !== null) {
                $query .= " AND ss.department = :department";
                $params[':department'] = $department;
            }
            
            $query .= " ORDER BY ss.name ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $staff,
                'count' => count($staff)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get attendance records for specific month
     * GET /api/attendance/records?month=2&year=2026
     */
    public function getAttendanceRecords() {
        try {
            $month = isset($_GET['month']) ? (int)$_GET['month'] + 1 : date('m');
            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
            $department = isset($_GET['department']) && $_GET['department'] !== '' ? $_GET['department'] : null;
            
            // Build the date range
            $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $query = "SELECT 
                        sa.staff_id,
                        sa.attendance_date,
                        sa.status,
                        ss.department
                     FROM staff_attendance sa
                     JOIN school_staff ss ON sa.staff_id = ss.id
                     WHERE sa.school_id = :school_id
                     AND sa.attendance_date BETWEEN :start_date AND :end_date";
            
            $params = [
                ':school_id' => $this->school_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];
            
            // Add department filter if specified
            if ($department !== null) {
                $query .= " AND ss.department = :department";
                $params[':department'] = $department;
            }
            
            $query .= " ORDER BY sa.attendance_date ASC, sa.staff_id ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $records,
                'count' => count($records),
                'date_range' => [
                    'start' => $start_date,
                    'end' => $end_date
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
```

---

### Endpoint 2: Save Attendance Records

```php
/**
 * Save or update attendance record
 * POST /api/attendance/mark
 * 
 * Request body:
 * {
 *     "staff_id": 1,
 *     "attendance_date": "2026-02-05",
 *     "status": "present",
 *     "remarks": "Optional remarks"
 * }
 */
public function markAttendance() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($input['staff_id']) || !isset($input['attendance_date']) || !isset($input['status'])) {
            return [
                'success' => false,
                'error' => 'Missing required fields: staff_id, attendance_date, status'
            ];
        }
        
        $valid_statuses = ['present', 'absent', 'leave', 'halfday', 'not_marked'];
        if (!in_array($input['status'], $valid_statuses)) {
            return [
                'success' => false,
                'error' => 'Invalid status. Must be: ' . implode(', ', $valid_statuses)
            ];
        }
        
        // Prevent future dates
        if (strtotime($input['attendance_date']) > time()) {
            return [
                'success' => false,
                'error' => 'Cannot mark attendance for future dates'
            ];
        }
        
        // Get user ID from session (assuming it's stored in $_SESSION)
        $marked_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        $query = "INSERT INTO staff_attendance 
                  (staff_id, school_id, attendance_date, status, remarks, marked_by, marked_at)
                  VALUES (:staff_id, :school_id, :attendance_date, :status, :remarks, :marked_by, NOW())
                  ON DUPLICATE KEY UPDATE
                  status = VALUES(status),
                  remarks = VALUES(remarks),
                  marked_by = VALUES(marked_by),
                  marked_at = NOW(),
                  updated_at = NOW()";
        
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute([
            ':staff_id' => $input['staff_id'],
            ':school_id' => $this->school_id,
            ':attendance_date' => $input['attendance_date'],
            ':status' => $input['status'],
            ':remarks' => $input['remarks'] ?? null,
            ':marked_by' => $marked_by
        ]);
        
        if ($result) {
            // Optionally recalculate monthly summary
            $this->recalculateMonthlySummary($input['staff_id'], $input['attendance_date']);
            
            return [
                'success' => true,
                'message' => 'Attendance marked successfully',
                'data' => [
                    'staff_id' => $input['staff_id'],
                    'attendance_date' => $input['attendance_date'],
                    'status' => $input['status']
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to mark attendance'
            ];
        }
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Recalculate monthly summary after marking attendance
 */
private function recalculateMonthlySummary($staff_id, $attendance_date) {
    try {
        $month = date('m', strtotime($attendance_date));
        $year = date('Y', strtotime($attendance_date));
        
        $query = "CALL sp_calculate_monthly_summary(:school_id, :year, :month)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':school_id' => $this->school_id,
            ':year' => $year,
            ':month' => $month
        ]);
    } catch (\Exception $e) {
        // Log error but don't fail the attendance marking
        error_log("Summary calculation failed: " . $e->getMessage());
    }
}
```

---

## Part 4: Frontend Integration

### Step 1: Replace Sample Data with API Call

**Current Code** (lines 486-495 in staff_attendence.php):
```javascript
// SAMPLE DATA - Replace with database query
const allStaff = [
    {id: 1, employee_id: 'EMP001', name: 'John Smith', designation: 'Teacher', department: 'Teaching'},
    // ... more sample data
];
```

**New Code** (Replace with):
```javascript
let allStaff = [];

// Fetch staff list from API
function loadStaffFromDatabase() {
    const month = document.getElementById('filterMonth')?.value || new Date().getMonth();
    const year = document.getElementById('filterYear')?.value || new Date().getFullYear();
    const department = document.getElementById('filterDept')?.value || '';
    
    const params = new URLSearchParams({
        month: month,
        year: year,
        department: department
    });
    
    fetch('/App/Modules/School_Admin/Controllers/AttendanceController.php?action=getStaff&' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allStaff = data.data;
                loadMonthlyCalendar();
            } else {
                console.error('Error loading staff:', data.error);
            }
        })
        .catch(error => console.error('Error:', error));
}
```

### Step 2: Save Attendance to Database

**Current Code** (lines 1143 in toggleAttendanceStatus function):
```javascript
// TODO: Send to server
console.log('Status changed:', {staff_id, date, status});
```

**New Code** (Replace with):
```javascript
// Send to server
const attendanceData = {
    staff_id: staffId,
    attendance_date: date,
    status: status,
    remarks: ''
};

fetch('/App/Modules/School_Admin/Controllers/AttendanceController.php?action=markAttendance', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(attendanceData)
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Attendance marked successfully:', data.message);
    } else {
        console.error('Error marking attendance:', data.error);
        // Revert the badge change on error
        badge.classList.remove(...statuses);
        badge.classList.add('badge-secondary');
        badge.textContent = currentStatus;
    }
})
.catch(error => {
    console.error('Error:', error);
    // Revert the badge change on error
    badge.classList.remove(...statuses);
    badge.classList.add('badge-secondary');
    badge.textContent = currentStatus;
});
```

### Step 3: Update Filter Handlers

**Current Code** (lines 1002 in applyMonthFilter):
```javascript
const month = parseInt(document.getElementById('filterMonth').value);
const year = parseInt(document.getElementById('filterYear').value);
const dept = document.getElementById('filterDept').value;

currentCalendarDate = new Date(year, month, 1);
loadMonthlyCalendar();
```

**New Code** (Replace with):
```javascript
const month = parseInt(document.getElementById('filterMonth').value);
const year = parseInt(document.getElementById('filterYear').value);
const dept = document.getElementById('filterDept').value;

currentCalendarDate = new Date(year, month, 1);
loadStaffFromDatabase();  // This will call loadMonthlyCalendar() after loading staff
```

---

## Part 5: Testing

### Test Scenario 1: Load Staff for February 2026
```javascript
// In browser console
fetch('/App/Modules/School_Admin/Controllers/AttendanceController.php?action=getStaff&month=1&year=2026')
    .then(r => r.json())
    .then(d => console.log(d))
```

**Expected Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "employee_id": "EMP001",
            "name": "John Smith",
            "designation": "Teacher",
            "department": "Teaching",
            "email": "john.smith@school.com",
            "phone": "9876543210"
        },
        // ... more staff
    ],
    "count": 8
}
```

### Test Scenario 2: Mark Attendance
```javascript
const attendanceData = {
    staff_id: 1,
    attendance_date: "2026-02-05",
    status: "present",
    remarks: "Test marking"
};

fetch('/App/Modules/School_Admin/Controllers/AttendanceController.php?action=markAttendance', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(attendanceData)
})
.then(r => r.json())
.then(d => console.log(d))
```

**Expected Response**:
```json
{
    "success": true,
    "message": "Attendance marked successfully",
    "data": {
        "staff_id": 1,
        "attendance_date": "2026-02-05",
        "status": "present"
    }
}
```

### Test Scenario 3: Verify Database Record
```sql
SELECT * FROM staff_attendance 
WHERE staff_id = 1 AND attendance_date = '2026-02-05';
```

Should return a record with status = 'present'

---

## Part 6: Bulk Operations

### Bulk Mark All Present (From Modal)

The modal already has this feature. It needs backend support:

**HTML Button** (In modal):
```html
<button class="dropdown-item" data-bulk-action="present">Mark All Present</button>
```

**JavaScript Handler**:
```javascript
document.querySelectorAll('[data-bulk-action]').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.getAttribute('data-bulk-action');
        const selectedStaff = getSelectedStaffFromModal();
        const date = document.getElementById('attendanceDate').value;
        
        const bulkData = {
            staff_ids: selectedStaff.map(s => s.id),
            attendance_date: date,
            status: action
        };
        
        fetch('/api/attendance/bulk-mark', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(bulkData)
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('Attendance marked for ' + d.count + ' staff members');
                loadMonthlyCalendar();
            }
        });
    });
});
```

---

## Part 7: Database Queries Reference

### Get attendance for staff for a month
```sql
SELECT ss.name, sa.attendance_date, sa.status
FROM staff_attendance sa
JOIN school_staff ss ON sa.staff_id = ss.id
WHERE ss.school_id = 1
    AND YEAR(sa.attendance_date) = 2026
    AND MONTH(sa.attendance_date) = 2
ORDER BY ss.name, sa.attendance_date;
```

### Get staff with low attendance
```sql
SELECT ss.name, sas.attendance_percentage
FROM staff_attendance_summary sas
JOIN school_staff ss ON sas.staff_id = ss.id
WHERE sas.attendance_percentage < 75
    AND sas.year = 2026
    AND sas.month = 2
ORDER BY sas.attendance_percentage;
```

### Get department-wise attendance
```sql
SELECT ss.department, 
       AVG(sas.attendance_percentage) as avg_attendance,
       COUNT(DISTINCT ss.id) as staff_count
FROM staff_attendance_summary sas
JOIN school_staff ss ON sas.staff_id = ss.id
WHERE sas.year = 2026 AND sas.month = 2
GROUP BY ss.department;
```

---

## Part 8: Troubleshooting

| Issue | Solution |
|-------|----------|
| "Cannot find table staff_attendance" | Execute SQL file to create tables |
| Filter not updating calendar | Check if loadStaffFromDatabase() is called |
| Attendance not saving | Check marked_by is set from session |
| Department filter shows no results | Verify department names match in both tables |
| Dates not displaying correctly | Check timezone settings in PHP.ini |

---

## Summary

✅ **Completed**:
- Filter UI with Month, Year, Department
- Calendar table layout with date columns
- Sample data integration
- Database schema

🔄 **Next Steps**:
1. Create AttendanceController.php with API endpoints
2. Connect frontend to backend API
3. Test all scenarios
4. Add bulk operations support
5. Create reporting views

---

**Questions?** Refer to DATABASE_DOCUMENTATION.md for table structures or the SQL file for schema details.
