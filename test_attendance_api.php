<?php
/**
 * Staff Attendance System - API Testing Script
 * 
 * This script helps test all API endpoints for the staff attendance system.
 * Usage: Open in browser at /test_attendance_api.php
 * 
 * @version 1.0
 * @author System
 */

// Start session for authentication context
session_start();

// Set test mode - comment out for production
define('TEST_MODE', true);

// Set default school_id for testing
if (!isset($_SESSION['school_id'])) {
    $_SESSION['school_id'] = 1;  // Adjust based on your school ID
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;    // Adjust based on your user ID
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Attendance API Testing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .test-section {
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .endpoint-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .test-button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .response-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
        .success-response {
            border-left: 4px solid #28a745;
        }
        .error-response {
            border-left: 4px solid #dc3545;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">
                    <i class="fas fa-flask-vial"></i> Staff Attendance API Testing
                </h1>

                <div class="info-box">
                    <strong>Session Information:</strong><br>
                    School ID: <strong><?php echo $_SESSION['school_id']; ?></strong><br>
                    User ID: <strong><?php echo $_SESSION['user_id']; ?></strong><br>
                    API Base URL: <strong>/App/Modules/School_Admin/Controllers/StaffAttendanceController.php</strong>
                </div>

                <!-- Test 1: Get Staff -->
                <div class="test-section">
                    <h3 class="endpoint-title">1. Get Staff List (GET)</h3>
                    <p><code>?action=getStaff</code></p>
                    <div>
                        <button class="btn btn-primary test-button" onclick="testGetStaff()">
                            Test Get All Staff
                        </button>
                        <button class="btn btn-outline-primary test-button" onclick="testGetStaffByType('teacher')">
                            Test Get Teachers
                        </button>
                        <button class="btn btn-outline-primary test-button" onclick="testGetStaffByType('employee')">
                            Test Get Employees
                        </button>
                    </div>
                    <div id="response1" class="response-box" style="display:none;"></div>
                </div>

                <!-- Test 2: Get Monthly Data -->
                <div class="test-section">
                    <h3 class="endpoint-title">2. Get Monthly Data (GET)</h3>
                    <p><code>?action=getMonthlyData&month=X&year=YYYY</code></p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="testMonth">Month (0-11):</label>
                            <select class="form-control" id="testMonth">
                                <?php 
                                $today = new DateTime();
                                for ($m = 0; $m < 12; $m++) {
                                    $selected = ($m == $today->format('n') - 1) ? 'selected' : '';
                                    $monthName = date('F', mktime(0, 0, 0, $m + 1, 1));
                                    echo "<option value='$m' $selected>$m - $monthName</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="testYear">Year:</label>
                            <select class="form-control" id="testYear">
                                <?php 
                                $year = (int)$today->format('Y');
                                for ($y = $year - 1; $y <= $year + 1; $y++) {
                                    $selected = ($y == $year) ? 'selected' : '';
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4" style="padding-top: 32px;">
                            <button class="btn btn-primary test-button w-100" onclick="testGetMonthlyData()">
                                Test Get Monthly Data
                            </button>
                        </div>
                    </div>
                    <div id="response2" class="response-box" style="display:none;"></div>
                </div>

                <!-- Test 3: Mark Single Attendance -->
                <div class="test-section">
                    <h3 class="endpoint-title">3. Mark Single Attendance (POST)</h3>
                    <p><code>{action: 'mark', staff_type, staff_id, attendance_date, status}</code></p>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="markStaffType">Staff Type:</label>
                            <select class="form-control" id="markStaffType">
                                <option value="teacher">Teacher</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="markStaffId">Staff ID:</label>
                            <input type="number" class="form-control" id="markStaffId" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label for="markDate">Date:</label>
                            <input type="date" class="form-control" id="markDate" 
                                   value="<?php echo $today->format('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="markStatus">Status:</label>
                            <select class="form-control" id="markStatus">
                                <option value="P">P - Present</option>
                                <option value="A">A - Absent</option>
                                <option value="L">L - Leave</option>
                                <option value="HD">HD - Half Day</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-primary test-button" onclick="testMarkAttendance()">
                            Mark Attendance
                        </button>
                    </div>
                    <div id="response3" class="response-box" style="display:none;"></div>
                </div>

                <!-- Test 4: Bulk Mark Attendance -->
                <div class="test-section">
                    <h3 class="endpoint-title">4. Bulk Mark Attendance (POST)</h3>
                    <p><code>{action: 'bulkMark', staff_list, attendance_date, status}</code></p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="bulkDate">Date:</label>
                            <input type="date" class="form-control" id="bulkDate" 
                                   value="<?php echo $today->format('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="bulkStatus">Status:</label>
                            <select class="form-control" id="bulkStatus">
                                <option value="P">P - Present</option>
                                <option value="A">A - Absent</option>
                                <option value="L">L - Leave</option>
                                <option value="HD">HD - Half Day</option>
                            </select>
                        </div>
                        <div class="col-md-4" style="padding-top: 32px;">
                            <button class="btn btn-primary test-button w-100" onclick="testBulkMarkAttendance()">
                                Bulk Mark (First 3 Staff)
                            </button>
                        </div>
                    </div>
                    <div id="response4" class="response-box" style="display:none;"></div>
                </div>

                <!-- Test 5: Get Attendance Summary -->
                <div class="test-section">
                    <h3 class="endpoint-title">5. Get Attendance Summary (GET)</h3>
                    <p><code>?action=summary&staff_type=X&staff_id=X&month=X&year=YYYY</code></p>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="summaryStaffType">Staff Type:</label>
                            <select class="form-control" id="summaryStaffType">
                                <option value="teacher">Teacher</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="summaryStaffId">Staff ID:</label>
                            <input type="number" class="form-control" id="summaryStaffId" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label for="summaryMonth">Month:</label>
                            <select class="form-control" id="summaryMonth">
                                <?php 
                                for ($m = 1; $m <= 12; $m++) {
                                    $selected = ($m == $today->format('n')) ? 'selected' : '';
                                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                                    echo "<option value='$m' $selected>$m - $monthName</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="summaryYear">Year:</label>
                            <select class="form-control" id="summaryYear">
                                <?php 
                                for ($y = (int)$today->format('Y') - 1; $y <= (int)$today->format('Y') + 1; $y++) {
                                    $selected = ($y == $today->format('Y')) ? 'selected' : '';
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary test-button" onclick="testGetSummary()">
                        Get Summary
                    </button>
                    <div id="response5" class="response-box" style="display:none;"></div>
                </div>

                <!-- Test 6: Get Departments -->
                <div class="test-section">
                    <h3 class="endpoint-title">6. Get Departments (GET)</h3>
                    <p><code>?action=departments</code></p>
                    <button class="btn btn-primary test-button" onclick="testGetDepartments()">
                        Get Departments
                    </button>
                    <div id="response6" class="response-box" style="display:none;"></div>
                </div>

                <!-- Test 7: Get Attendance Records -->
                <div class="test-section">
                    <h3 class="endpoint-title">7. Get Attendance Records (GET)</h3>
                    <p><code>?action=getRecords&month=X&year=YYYY</code></p>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="recordsMonth">Month (0-11):</label>
                            <select class="form-control" id="recordsMonth">
                                <?php 
                                for ($m = 0; $m < 12; $m++) {
                                    $selected = ($m == $today->format('n') - 1) ? 'selected' : '';
                                    $monthName = date('F', mktime(0, 0, 0, $m + 1, 1));
                                    echo "<option value='$m' $selected>$m - $monthName</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="recordsYear">Year:</label>
                            <select class="form-control" id="recordsYear">
                                <?php 
                                for ($y = (int)$today->format('Y') - 1; $y <= (int)$today->format('Y') + 1; $y++) {
                                    $selected = ($y == $today->format('Y')) ? 'selected' : '';
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary test-button" onclick="testGetRecords()">
                        Get Records
                    </button>
                    <div id="response7" class="response-box" style="display:none;"></div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const apiBaseUrl = '/App/Modules/School_Admin/Controllers/StaffAttendanceController.php';

        // Helper function to format JSON response
        function formatResponse(response) {
            return JSON.stringify(response, null, 2);
        }

        // Helper function to display response
        function displayResponse(elementId, response, isError = false) {
            const element = document.getElementById(elementId);
            element.textContent = formatResponse(response);
            element.className = 'response-box ' + (isError ? 'error-response' : 'success-response');
            element.style.display = 'block';
        }

        // Test 1: Get Staff
        function testGetStaff() {
            fetch(`${apiBaseUrl}?action=getStaff`)
                .then(res => res.json())
                .then(data => displayResponse('response1', data))
                .catch(err => displayResponse('response1', {error: err.message}, true));
        }

        function testGetStaffByType(type) {
            fetch(`${apiBaseUrl}?action=getStaff&staff_type=${type}`)
                .then(res => res.json())
                .then(data => displayResponse('response1', data))
                .catch(err => displayResponse('response1', {error: err.message}, true));
        }

        // Test 2: Get Monthly Data
        function testGetMonthlyData() {
            const month = document.getElementById('testMonth').value;
            const year = document.getElementById('testYear').value;
            fetch(`${apiBaseUrl}?action=getMonthlyData&month=${month}&year=${year}`)
                .then(res => res.json())
                .then(data => displayResponse('response2', data))
                .catch(err => displayResponse('response2', {error: err.message}, true));
        }

        // Test 3: Mark Single Attendance
        function testMarkAttendance() {
            const payload = {
                action: 'mark',
                staff_type: document.getElementById('markStaffType').value,
                staff_id: parseInt(document.getElementById('markStaffId').value),
                attendance_date: document.getElementById('markDate').value,
                status: document.getElementById('markStatus').value,
                remarks: 'Test marking'
            };

            fetch(apiBaseUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => displayResponse('response3', data))
            .catch(err => displayResponse('response3', {error: err.message}, true));
        }

        // Test 4: Bulk Mark Attendance
        function testBulkMarkAttendance() {
            const payload = {
                action: 'bulkMark',
                staff_list: [
                    { staff_type: 'teacher', staff_id: 1 },
                    { staff_type: 'teacher', staff_id: 2 },
                    { staff_type: 'employee', staff_id: 1 }
                ],
                attendance_date: document.getElementById('bulkDate').value,
                status: document.getElementById('bulkStatus').value
            };

            fetch(apiBaseUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => displayResponse('response4', data))
            .catch(err => displayResponse('response4', {error: err.message}, true));
        }

        // Test 5: Get Summary
        function testGetSummary() {
            const query = `?action=summary&staff_type=${document.getElementById('summaryStaffType').value}&staff_id=${document.getElementById('summaryStaffId').value}&month=${document.getElementById('summaryMonth').value}&year=${document.getElementById('summaryYear').value}`;
            fetch(apiBaseUrl + query)
                .then(res => res.json())
                .then(data => displayResponse('response5', data))
                .catch(err => displayResponse('response5', {error: err.message}, true));
        }

        // Test 6: Get Departments
        function testGetDepartments() {
            fetch(`${apiBaseUrl}?action=departments`)
                .then(res => res.json())
                .then(data => displayResponse('response6', data))
                .catch(err => displayResponse('response6', {error: err.message}, true));
        }

        // Test 7: Get Records
        function testGetRecords() {
            const month = document.getElementById('recordsMonth').value;
            const year = document.getElementById('recordsYear').value;
            fetch(`${apiBaseUrl}?action=getRecords&month=${month}&year=${year}`)
                .then(res => res.json())
                .then(data => displayResponse('response7', data))
                .catch(err => displayResponse('response7', {error: err.message}, true));
        }
    </script>
</body>
</html>
