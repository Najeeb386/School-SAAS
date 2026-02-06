<?php
// Staff Attendance Management Page
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    die('Unauthorized');
}

$db = \Database::connect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        
        .status-present {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        .status-leave {
            background-color: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }

        /* Monthly Attendance Calendar */
        .calendar-grid {
            overflow-x: auto;
        }

        /* Real Calendar View */
        .real-calendar {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .calendar-header h5 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .calendar-nav button {
            background: #007bff;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 12px;
            font-weight: 500;
        }

        .calendar-nav button:hover {
            background: #0056b3;
        }

        .calendar-nav button.btn-outline-secondary {
            background: #6c757d;
            color: white;
        }

        .calendar-nav button.btn-outline-secondary:hover {
            background: #5a6268;
        }

        .calendar-nav select {
            background: white;
            border: 1px solid #dee2e6;
            color: #333;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .calendar-nav select option {
            background: white;
            color: #333;
        }

        /* Calendar Grid */
        .calendar-grid-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            background: #dee2e6;
            padding: 2px;
            margin-bottom: 20px;
        }

        .day-header {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            color: #333;
            border-right: 1px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }

        .day-header.sunday {
            background: #ffe5e5;
            color: #dc3545;
            font-weight: 700;
        }

        .calendar-date {
            background: white;
            padding: 8px;
            min-height: 80px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        .calendar-date:hover {
            background: #f0f0f0;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }

        .calendar-date.other-month {
            background: #f8f9fa;
            color: #ccc;
        }

        .calendar-date.sunday {
            background: #fff5f5;
        }

        .calendar-date.sunday::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #dc3545;
        }

        .date-number {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 4px;
            color: #333;
        }

        .calendar-date.other-month .date-number {
            color: #ccc;
        }

        .date-attendance {
            font-size: 11px;
            line-height: 1.3;
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
        }

        .staff-attendance-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .staff-attendance-indicator:hover {
            transform: scale(1.1);
        }

        .status-present {
            background-color: #28a745;
        }

        .status-absent {
            background-color: #dc3545;
        }

        .status-leave {
            background-color: #ffc107;
            color: #333;
        }

        .status-halfday {
            background-color: #007bff;
        }

        .status-notmarked {
            background-color: #e9ecef;
            color: #999;
        }

        /* Staff List in Calendar */
        .staff-calendar-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .staff-calendar-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .staff-calendar-card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .staff-calendar-card h6 {
            margin: 0 0 8px 0;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .staff-calendar-card p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        /* Old Calendar Table Styles (keeping for reference) */
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar-table thead th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
            color: #666;
            border: 1px solid #dee2e6;
            min-width: 40px;
        }

        .calendar-table tbody td {
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
        }

        .calendar-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .staff-cell {
            min-width: 150px;
            text-align: left;
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .staff-name {
            font-weight: 600;
            color: #333;
        }

        .staff-meta {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }

        /* Attendance Status in Calendar */
        .attendance-cell {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .attendance-cell.present {
            background-color: #d4edda;
            color: #155724;
        }

        .attendance-cell.absent {
            background-color: #f8d7da;
            color: #721c24;
        }

        .attendance-cell.leave {
            background-color: #fff3cd;
            color: #856404;
        }

        .attendance-cell.empty {
            background-color: #f0f0f0;
            color: #ccc;
        }

        .attendance-cell:hover {
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1) inset;
            transform: scale(1.05);
        }

        /* Staff Attendance List Styling */
        .staff-attendance-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s ease;
        }

        .staff-attendance-item:hover {
            background-color: #f8f9fa;
        }

        .staff-attendance-item:last-child {
            border-bottom: none;
        }

        .staff-attendance-info {
            flex: 0 0 30%;
            min-width: 0;
        }

        .staff-attendance-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .staff-attendance-id {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        .staff-attendance-status {
            flex: 1;
            display: flex;
            gap: 8px;
            justify-content: space-between;
        }

        .status-radio {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .status-radio input[type="radio"] {
            cursor: pointer;
        }

        .status-radio label {
            margin: 0;
            cursor: pointer;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .status-radio input[type="radio"]:checked + label {
            font-weight: 600;
        }

        .status-radio.present label {
            color: #155724;
        }

        .status-radio.present input[type="radio"]:checked + label {
            background-color: #d4edda;
        }

        .status-radio.absent label {
            color: #721c24;
        }

        .status-radio.absent input[type="radio"]:checked + label {
            background-color: #f8d7da;
        }

        .status-radio.leave label {
            color: #856404;
        }

        .status-radio.leave input[type="radio"]:checked + label {
            background-color: #fff3cd;
        }

        .status-radio.halfday label {
            color: #004085;
        }

        .status-radio.halfday input[type="radio"]:checked + label {
            background-color: #cce5ff;
        }

        /* Print Styles */
        @media print {
            * {
                margin: 0;
                padding: 0;
            }

            body {
                background-color: white;
            }

            /* Hide specific elements */
            .page-header,
            .dashboard-stats,
            #filterSection,
            .modal,
            .btn-close,
            .navbar,
            .sidebar,
            #attendanceModal,
            .card-header .calendar-nav {
                display: none !important;
            }

            /* Show the attendance card */
            .card.mt-4 {
                display: block !important;
                margin: 0 !important;
                box-shadow: none;
                border: none;
                page-break-inside: avoid;
            }

            .card.mt-4 .card-header {
                display: block !important;
                background-color: white !important;
                border-bottom: 2px solid #333;
                padding: 10px 0;
                margin-bottom: 10px;
            }

            .card.mt-4 .card-header h6 {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
                color: #000;
            }

            .card.mt-4 .card-header .text-muted {
                font-size: 12px;
                color: #666;
            }

            .card.mt-4 .card-body {
                display: block !important;
                padding: 0;
            }

            .calendar-grid {
                padding: 0;
                background: white;
            }

            .calendar-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            .calendar-table th,
            .calendar-table td {
                border: 1px solid #000;
                padding: 5px 3px;
                text-align: center;
                font-size: 9px;
            }

            .calendar-table th {
                background-color: #e9ecef;
                font-weight: bold;
                color: #000;
            }

            .calendar-table td:first-child {
                text-align: left;
                font-weight: 500;
                width: 12%;
                background-color: #f8f9fa;
            }

            .badge {
                border: 1px solid #000;
                padding: 2px 4px !important;
                font-size: 8px;
                font-weight: bold;
            }

            .badge.bg-success {
                background-color: #d4edda !important;
                color: #155724 !important;
            }

            .badge.bg-danger {
                background-color: #f8d7da !important;
                color: #721c24 !important;
            }

            .badge.bg-warning {
                background-color: #fff3cd !important;
                color: #856404 !important;
            }

            .badge.bg-info {
                background-color: #d1ecf1 !important;
                color: #0c5460 !important;
            }

            .border-top {
                display: block !important;
                border-top: 2px solid #000 !important;
                margin-top: 15px !important;
                padding-top: 10px !important;
            }

            .border-top h6 {
                font-size: 12px;
                font-weight: bold;
                margin-bottom: 8px;
                color: #000;
            }

            .border-top .col-md-3 {
                font-size: 10px;
                margin-bottom: 3px;
                color: #000;
            }

            #calendarLoadingSpinner {
                display: none !important;
            }

            /* Print margins and sizing */
            @page {
                margin: 0.5in;
                size: A4 landscape;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list"></i> Staff Attendance</h2>
            <div class="btn-group" role="group">
                <button type="button" onclick="window.location.href='attendence.php'" class="btn btn-success" id="markPresentAll">
                    <i class="fas fa-check"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="addAttendanceBtn">
                    <i class="fas fa-plus"></i> Add Attendance
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Staff</p>
                                <h5 id="totalStaff">0</h5>
                            </div>
                            <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Present Today</p>
                                <h5 id="presentCount" class="text-success">0</h5>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-left-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Absent Today</p>
                                <h5 id="absentCount" class="text-danger">0</h5>
                            </div>
                            <i class="fas fa-times-circle fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">On Leave</p>
                                <h5 id="leaveCount" class="text-warning">0</h5>
                            </div>
                            <i class="fas fa-sun fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        

       

        <!-- Month/Year Filter Section -->
        <div class="card mt-4 mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-3">Filter by Month & Year</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filterMonth" class="form-label">Month</label>
                        <select id="filterMonth" class="form-select">
                            <option value="0">January</option>
                            <option value="1">February</option>
                            <option value="2">March</option>
                            <option value="3">April</option>
                            <option value="4">May</option>
                            <option value="5">June</option>
                            <option value="6">July</option>
                            <option value="7">August</option>
                            <option value="8">September</option>
                            <option value="9">October</option>
                            <option value="10">November</option>
                            <option value="11">December</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterYear" class="form-label">Year</label>
                        <select id="filterYear" class="form-select">
                            <!-- Years will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterDept" class="form-label">Department</label>
                        <select id="filterDept" class="form-select">
                            <option value="">All Departments</option>
                            <option value="Teaching">Teaching</option>
                            <option value="Library">Library</option>
                            <option value="Admin">Admin</option>
                            <option value="Support">Support</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button id="applyMonthFilter" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button id="resetMonthFilter" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Calendar View -->
        <div class="card mt-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Monthly Attendance Register</h6>
                    <small class="text-muted" id="calendarTitle"></small>
                </div>
                <div class="calendar-nav">
                    <button id="prevMonthBtn" class="btn btn-sm btn-outline-secondary" title="Previous Month">
                        <i class="fas fa-chevron-left"></i> Prev
                    </button>
                    <button id="todayBtn" class="btn btn-sm btn-outline-secondary" title="Go to Today">
                        Today
                    </button>
                    <button id="nextMonthBtn" class="btn btn-sm btn-outline-secondary" title="Next Month">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                    <select id="calendarYearMonth" class="form-select form-select-sm" style="width: 150px; display: inline-block;">
                        <!-- Year and month options will be populated by JavaScript -->
                    </select>
                    <button id="printCalendarBtn" class="btn btn-sm btn-primary" title="Print Attendance Register">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="calendarLoadingSpinner" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="calendar-grid" style="display: block;" id="calendarView">
                    <table class="calendar-table">
                        <thead>
                            <tr id="calendarDateHeader">
                                <th style="min-width: 180px;">Staff</th>
                                <!-- Date headers will be populated by JavaScript -->
                            </tr>
                        </thead>
                        <tbody id="calendarBody">
                            <!-- Staff rows with attendance cells will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Legend -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="mb-3">Legend:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-success me-2">P</span> = Present
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2">A</span> = Absent
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning me-2">L</span> = Leave
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-info me-2">HD</span> = Half Day
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Mark Staff Attendance</h5>
                        <small class="text-muted" id="attendanceDateDisplay">Today: February 05, 2026</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="attendanceForm">
                    <div class="modal-body">
                        <!-- Bulk Action Dropdown -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <label for="bulkActionSelect" class="form-label fw-bold mb-2">Apply to All:</label>
                            <select id="bulkActionSelect" class="form-select">
                                <option value="">-- Select Action --</option>
                                <option value="present">Mark All as Present</option>
                                <option value="absent">Mark All as Absent</option>
                                <option value="leave">Mark All as Leave</option>
                                <option value="halfday">Mark All as Half Day</option>
                            </select>
                        </div>

                        <!-- Staff Attendance List -->
                        <div class="mb-3">
                            <div class="row g-2 mb-3 fw-bold text-muted small">
                                <div class="col-md-4">Employee Name</div>
                                <div class="col-md-8">Status</div>
                            </div>
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px;">
                                <div id="staffAttendanceList">
                                    <!-- Staff items will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize variables
        const today = new Date().toISOString().split('T')[0];
        const apiBaseUrl = '/School-SAAS/App/Modules/School_Admin/Controllers/StaffAttendanceController_Simple.php'; // Using simple version for direct API calls
        let attendanceModal;
        let allStaff = [];
        let currentMonth = new Date().getMonth() + 1;
        let currentYear = new Date().getFullYear();

        // Format today's date display
        function formatTodayDate() {
            const todayDate = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: '2-digit' };
            const formattedDate = todayDate.toLocaleDateString('en-US', options);
            const displayEl = document.getElementById('attendanceDateDisplay');
            if (displayEl) {
                displayEl.textContent = `Today: ${formattedDate}`;
            }
        }

        // Load staff from database API
        function loadStaffFromDatabase() {
            const params = new URLSearchParams({
                action: 'getStaff'
            });
            
            return fetch(`${apiBaseUrl}?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allStaff = data.data;
                        populateStaffAttendanceList();
                        return allStaff;
                    } else {
                        console.error('Failed to load staff:', data.message);
                        return [];
                    }
                })
                .catch(error => {
                    console.error('Error loading staff:', error);
                    return [];
                });
        }

        // Load monthly data from database
        function loadMonthlyData(month = null, year = null) {
            if (month === null) month = currentMonth;
            if (year === null) year = currentYear;
            
            currentMonth = month;
            currentYear = year;
            
            const params = new URLSearchParams({
                action: 'getMonthlyData',
                month: month - 1,  // Convert to 0-indexed
                year: year
            });
            
            const url = `${apiBaseUrl}?${params}`;
            
            return fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allStaff = data.data;
                        generateCalendarView(data);
                        updateStats();
                        return data;
                    } else {
                        console.error('Failed to load monthly data:', data.message);
                        return null;
                    }
                })
                .catch(error => {
                    console.error('Error loading monthly data:', error);
                    return null;
                });
        }

        // Populate staff attendance list in modal
        function populateStaffAttendanceList() {
            const listContainer = document.getElementById('staffAttendanceList');
            if (!listContainer) return;
            
            listContainer.innerHTML = '';

            if (!allStaff || allStaff.length === 0) {
                listContainer.innerHTML = '<div class="p-3 text-center text-muted">No staff found.</div>';
                return;
            }

            allStaff.forEach((staff, index) => {
                const staffItem = document.createElement('div');
                staffItem.className = 'staff-attendance-item d-flex align-items-center justify-content-between p-2 border-bottom';
                const empId = staff.employee_id || staff.id || '';
                const designation = staff.designation || staff.type_label || '';
                // Determine today's status if available
                const todayStatus = (staff.attendance && staff.attendance[today]) ? staff.attendance[today] : '';

                staffItem.innerHTML = `
                    <div class="staff-attendance-info">
                        <div class="staff-attendance-name">${staff.name}</div>
                        <div class="staff-attendance-id small text-muted">${empId} · ${designation}</div>
                    </div>
                    <div class="staff-attendance-status d-flex gap-2 align-items-center">
                        <input type="hidden" name="staff_id_${index}" value="${staff.id}">
                        <input type="hidden" name="staff_type_${index}" value="${staff.staff_type}">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_${index}" id="present_${index}" value="P" ${todayStatus === 'P' ? 'checked' : ''}>
                            <label class="form-check-label" for="present_${index}">P</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_${index}" id="absent_${index}" value="A" ${todayStatus === 'A' ? 'checked' : ''}>
                            <label class="form-check-label" for="absent_${index}">A</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_${index}" id="leave_${index}" value="L" ${todayStatus === 'L' ? 'checked' : ''}>
                            <label class="form-check-label" for="leave_${index}">L</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_${index}" id="halfday_${index}" value="HD" ${todayStatus === 'HD' ? 'checked' : ''}>
                            <label class="form-check-label" for="halfday_${index}">HD</label>
                        </div>
                    </div>
                `;
                listContainer.appendChild(staffItem);
            });
        }

        // Generate calendar view from API data
        function generateCalendarView(data) {
            const calendarContainer = document.getElementById('calendarView');
            if (!calendarContainer) {
                return;
            }
            
            const { data: staff, year, month, month_name, days_in_month } = data;
            
            // Update calendar header
            const titleEl = document.getElementById('calendarTitle');
            if (titleEl) {
                titleEl.textContent = `${month_name} ${year}`;
            }
            
            // Generate dates array
            const dates = [];
            for (let i = 1; i <= days_in_month; i++) {
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                dates.push(dateStr);
            }
            
            // Generate calendar HTML
            let html = '<table class="table table-bordered calendar-table"><thead><tr><th style="width: 200px;">Staff Info</th>';
            
            // Add date headers
            dates.forEach(date => {
                const dateObj = new Date(date);
                const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
                const dayNum = dateObj.getDate();
                const isSunday = dateObj.getDay() === 0;
                const headerClass = isSunday ? 'sunday-cell' : '';
                const dayStyle = isSunday ? 'color: red; font-weight: bold;' : '';
                
                html += `<th class="${headerClass}" style="text-align: center; ${dayStyle}">${dayNum}<br><small>${dayName}</small></th>`;
            });
            
            html += '</tr></thead><tbody>';
            
            // Add staff rows
            if (!staff || staff.length === 0) {
                html += `<tr><td colspan="${dates.length + 1}" class="text-center text-muted p-4">No staff found for this month.</td></tr>`;
            } else {
                staff.forEach(member => {
                    html += `<tr>
                        <td>
                            <div><strong>${member.name}</strong></div>
                            <div class="small text-muted">${member.employee_id}</div>
                            <div class="small text-muted">${member.designation || member.type_label}</div>
                        </td>`;

                    // Add attendance cells
                    dates.forEach(date => {
                        const status = member.attendance ? member.attendance[date] : null;
                        const statusClass = status ? `bg-${getStatusClass(status)}` : 'bg-secondary';
                        const dateObj = new Date(date);
                        const isSunday = dateObj.getDay() === 0;
                        const cellClass = isSunday ? 'sunday-cell' : '';

                        // Only show badge if status exists (don't show empty dashes)
                        if (status) {
                            html += `<td class="${cellClass}" style="text-align: center;">
                                <span class="badge ${statusClass} cursor-pointer" 
                                      onclick="toggleAttendance('${member.staff_type}', ${member.id}, '${date}', this)"
                                      data-staff-type="${member.staff_type}"
                                      data-staff-id="${member.id}"
                                      data-date="${date}"
                                      data-status="${status}"
                                      style="font-size: 11px; padding: 4px 8px; cursor: pointer; display: inline-block;">${status}</span>
                            </td>`;
                        } else {
                            html += `<td class="${cellClass}" style="text-align: center;">
                            </td>`;
                        }
                    });

                    html += '</tr>';
                });
            }
            
            html += '</tbody></table>';
            
            calendarContainer.innerHTML = html;
        }

        // Get bootstrap badge class for status
        function getStatusClass(status) {
            const statusMap = {
                'P': 'success',   // Green
                'A': 'danger',    // Red
                'L': 'warning',   // Yellow
                'HD': 'info'      // Blue
            };
            return statusMap[status] || 'secondary';
        }

        // Toggle attendance status by clicking badge
        function toggleAttendance(staffType, staffId, date, element) {
            const currentStatus = element.getAttribute('data-status');
            const statusCycle = ['P', 'A', 'L', 'HD', ''];
            const currentIndex = statusCycle.indexOf(currentStatus || '');
            const nextStatus = statusCycle[(currentIndex + 1) % statusCycle.length];
            
            // Send to backend
            const payload = {
                staff_type: staffType,
                staff_id: staffId,
                attendance_date: date,
                status: nextStatus || 'P'
            };
            
            fetch(apiBaseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'mark',
                    ...payload
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the badge
                    if (nextStatus) {
                        element.textContent = nextStatus;
                        element.className = `badge bg-${getStatusClass(nextStatus)} cursor-pointer`;
                        element.setAttribute('data-status', nextStatus);
                        element.style.fontSize = '11px';
                        element.style.padding = '4px 8px';
                        element.style.cursor = 'pointer';
                        element.style.display = 'inline-block';
                    } else {
                        element.textContent = '-';
                        element.className = 'badge bg-secondary cursor-pointer';
                        element.setAttribute('data-status', '');
                        element.style.fontSize = '11px';
                        element.style.padding = '4px 8px';
                        element.style.cursor = 'pointer';
                        element.style.display = 'inline-block';
                    }
                    updateStats();
                } else {
                    console.error('Failed to mark attendance:', data.message);
                    alert('Failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error marking attendance');
            });
        }

        // Update statistics
        function updateStats() {
            let presentCount = 0, absentCount = 0, leaveCount = 0;
            
            allStaff.forEach(staff => {
                if (staff.attendance) {
                    const todayStatus = staff.attendance[today];
                    if (todayStatus === 'P') presentCount++;
                    else if (todayStatus === 'A') absentCount++;
                    else if (todayStatus === 'L') leaveCount++;
                }
            });
            
            const presentEl = document.getElementById('presentCount');
            const absentEl = document.getElementById('absentCount');
            const leaveEl = document.getElementById('leaveCount');
            
            if (presentEl) presentEl.textContent = presentCount;
            if (absentEl) absentEl.textContent = absentCount;
            if (leaveEl) leaveEl.textContent = leaveCount;
        }

        // Load dashboard stats from API (server-side counts)
        function loadStats() {
            const params = new URLSearchParams({ action: 'stats' });
            fetch(`${apiBaseUrl}?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.success && data.data) {
                        const d = data.data;
                        const totalEl = document.getElementById('totalStaff');
                        const presentEl = document.getElementById('presentCount');
                        const absentEl = document.getElementById('absentCount');
                        const leaveEl = document.getElementById('leaveCount');
                        if (totalEl) totalEl.textContent = d.total_staff ?? 0;
                        if (presentEl) presentEl.textContent = d.present_today ?? 0;
                        if (absentEl) absentEl.textContent = d.absent_today ?? 0;
                        if (leaveEl) leaveEl.textContent = d.on_leave_today ?? 0;
                    } else {
                        console.error('stats API returned error', data && data.message);
                    }
                })
                .catch(err => console.error('Error loading stats:', err));
        }

        // Mark attendance in bulk
        function markBulkAttendance(status) {
            const selectedStaff = [];
            
            // Get all checked staff
            document.querySelectorAll('input[name^="staffCheckbox"]:checked').forEach(checkbox => {
                const staffType = checkbox.getAttribute('data-staff-type');
                const staffId = checkbox.getAttribute('data-staff-id');
                selectedStaff.push({
                    staff_type: staffType,
                    staff_id: parseInt(staffId)
                });
            });
            
            if (selectedStaff.length === 0) {
                alert('Please select at least one staff member');
                return;
            }
            
            const attendanceDate = document.getElementById('filterDate')?.value || today;
            
            fetch(apiBaseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'bulkMark',
                    staff_list: selectedStaff,
                    attendance_date: attendanceDate,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Attendance marked for ${data.count} staff members`);
                    loadMonthlyData();
                } else {
                    alert('Failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error marking bulk attendance');
            });
        }

        // Handle attendance form submit (save individual statuses)
        const attendanceForm = document.getElementById('attendanceForm');
        if (attendanceForm) {
            attendanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const listContainer = document.getElementById('staffAttendanceList');
                if (!listContainer) return;

                const staffItems = listContainer.querySelectorAll('.staff-attendance-item');
                const staff_list = [];
                staffItems.forEach((item, index) => {
                    const staffIdEl = item.querySelector(`input[name=staff_id_${index}]`);
                    const staffTypeEl = item.querySelector(`input[name=staff_type_${index}]`);
                    const statusEl = item.querySelector(`input[name=status_${index}]:checked`);
                    const staff_id = staffIdEl ? staffIdEl.value : null;
                    const staff_type = staffTypeEl ? staffTypeEl.value : null;
                    const status = statusEl ? statusEl.value : '';
                    if (staff_id && staff_type) {
                        staff_list.push({ staff_type, staff_id: parseInt(staff_id), status });
                    }
                });

                if (staff_list.length === 0) {
                    alert('No staff to save');
                    return;
                }

                const attendanceDate = document.getElementById('filterDate')?.value || today;

                fetch(apiBaseUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'bulkSave', staff_list, attendance_date: attendanceDate })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Attendance saved');
                        if (attendanceModal) attendanceModal.hide();
                        loadMonthlyData(currentMonth, currentYear);
                        loadStats();
                    } else {
                        alert('Failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error('Error saving attendance:', err);
                    alert('Error saving attendance');
                });
            });
        }

        // Populate month selector
        function populateYearMonthSelector() {
            const select = document.getElementById('calendarYearMonth');
            if (!select) return;
            
            const today = new Date();
            const startYear = today.getFullYear() - 1;
            const endYear = today.getFullYear() + 1;

            // Generate all months for the year range
            for (let year = startYear; year <= endYear; year++) {
                for (let month = 0; month < 12; month++) {
                    const date = new Date(year, month, 1);
                    const option = document.createElement('option');
                    const monthYear = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                    option.value = date.toISOString().split('T')[0].slice(0, 7); // YYYY-MM format
                    option.textContent = monthYear;
                    
                    // Select current month
                    if (year === today.getFullYear() && month === today.getMonth()) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                }
            }
        }

        function populateFilterYearDropdown() {
            const yearSelect = document.getElementById('filterYear');
            if (!yearSelect) return;

            const today = new Date();
            const startYear = today.getFullYear() - 2;
            const endYear = today.getFullYear() + 2;

            for (let year = startYear; year <= endYear; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === today.getFullYear()) {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            }
        }

        // Initialize the system
        function initializeAttendanceSystem() {
            // Set filter date
            const filterDateEl = document.getElementById('filterDate');
            if (filterDateEl) {
                filterDateEl.value = today;
            }

            // Initialize modal
            const modalEl = document.getElementById('attendanceModal');
            if (modalEl) {
                attendanceModal = new bootstrap.Modal(modalEl);
            }

            // Format date display
            formatTodayDate();

            // Load initial data from database
            loadMonthlyData(currentMonth, currentYear);
            loadStaffFromDatabase();
            loadStats();

            // Add Attendance Button
            const addBtn = document.getElementById('addAttendanceBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    loadStaffFromDatabase().then(() => {
                        populateStaffAttendanceList();
                        const bulkSelect = document.getElementById('bulkActionSelect');
                        if (bulkSelect) bulkSelect.value = '';
                        if (attendanceModal) attendanceModal.show();
                    });
                });
            }

            // Bulk action handler
            const bulkActionSelect = document.getElementById('bulkActionSelect');
            if (bulkActionSelect) {
                bulkActionSelect.addEventListener('change', function() {
                    const action = this.value;
                    if (!action) return;

                    // Map action dropdown values to radio button values
                    const statusMap = {
                        'present': 'P',
                        'absent': 'A',
                        'leave': 'L',
                        'halfday': 'HD'
                    };
                    const radioValue = statusMap[action];

                    allStaff.forEach((_, index) => {
                        const radioButtons = document.querySelectorAll(`input[name="status_${index}"]`);
                        radioButtons.forEach(radio => {
                            if (radio.value === radioValue) {
                                radio.checked = true;
                            }
                        });
                    });

                    // Reset the dropdown after applying action
                    setTimeout(() => {
                        this.value = '';
                    }, 300);
                });
            }

            // Filter buttons
            const applyFiltersBtn = document.getElementById('applyFilters');
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    const month = parseInt(document.getElementById('filterMonth')?.value || currentMonth);
                    const year = parseInt(document.getElementById('filterYear')?.value || currentYear);
                    loadMonthlyData(month, year);
                });
            }

            // Reset filters button
            const resetFiltersBtn = document.getElementById('resetFilters');
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function() {
                    const today = new Date();
                    currentMonth = today.getMonth() + 1;
                    currentYear = today.getFullYear();
                    
                    const filterDateEl = document.getElementById('filterDate');
                    if (filterDateEl) filterDateEl.value = today.toISOString().split('T')[0];
                    
                    const filterDeptEl = document.getElementById('filterDepartment');
                    if (filterDeptEl) filterDeptEl.value = '';
                    
                    const filterStatusEl = document.getElementById('filterStatus');
                    if (filterStatusEl) filterStatusEl.value = '';
                    
                    loadMonthlyData(currentMonth, currentYear);
                });
            }

            // Filter Section - Apply Filter button
            const applyMonthFilterBtn = document.getElementById('applyMonthFilter');
            if (applyMonthFilterBtn) {
                applyMonthFilterBtn.addEventListener('click', function() {
                    const month = parseInt(document.getElementById('filterMonth').value) + 1;  // Convert from 0-indexed
                    const year = parseInt(document.getElementById('filterYear').value);
                    const dept = document.getElementById('filterDept').value;
                    
                    // Update calendar to selected month/year
                    loadMonthlyData(month, year);
                });
            }

            // Filter Section - Reset button
            const resetMonthFilterBtn = document.getElementById('resetMonthFilter');
            if (resetMonthFilterBtn) {
                resetMonthFilterBtn.addEventListener('click', function() {
                    const today = new Date();
                    document.getElementById('filterMonth').value = today.getMonth();
                    document.getElementById('filterYear').value = today.getFullYear();
                    document.getElementById('filterDept').value = '';
                    
                    currentMonth = today.getMonth() + 1;
                    currentYear = today.getFullYear();
                    loadMonthlyData(currentMonth, currentYear);
                });
            }

            // Populate filter dropdowns
            populateFilterYearDropdown();
            populateYearMonthSelector();

            // Calendar navigation buttons
            const prevMonthBtn = document.getElementById('prevMonthBtn');
            if (prevMonthBtn) {
                prevMonthBtn.addEventListener('click', function() {
                    currentMonth--;
                    if (currentMonth < 1) {
                        currentMonth = 12;
                        currentYear--;
                    }
                    loadMonthlyData(currentMonth, currentYear);
                });
            }

            const nextMonthBtn = document.getElementById('nextMonthBtn');
            if (nextMonthBtn) {
                nextMonthBtn.addEventListener('click', function() {
                    currentMonth++;
                    if (currentMonth > 12) {
                        currentMonth = 1;
                        currentYear++;
                    }
                    loadMonthlyData(currentMonth, currentYear);
                });
            }

            const todayBtn = document.getElementById('todayBtn');
            if (todayBtn) {
                todayBtn.addEventListener('click', function() {
                    const today = new Date();
                    currentMonth = today.getMonth() + 1;
                    currentYear = today.getFullYear();
                    loadMonthlyData(currentMonth, currentYear);
                });
            }

            const calendarYearMonth = document.getElementById('calendarYearMonth');
            if (calendarYearMonth) {
                calendarYearMonth.addEventListener('change', function() {
                    const [year, month] = this.value.split('-');
                    loadMonthlyData(parseInt(month), parseInt(year));
                });
            }

            // Print Calendar Button
            const printCalendarBtn = document.getElementById('printCalendarBtn');
            if (printCalendarBtn) {
                printCalendarBtn.addEventListener('click', function() {
                    window.print();
                });
            }
        }

        function populateFilterYearDropdown() {
            const yearSelect = document.getElementById('filterYear');
            if (!yearSelect || yearSelect.children.length > 0) return;  // Already populated

            const today = new Date();
            const startYear = today.getFullYear() - 2;
            const endYear = today.getFullYear() + 2;

            for (let year = startYear; year <= endYear; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                if (year === today.getFullYear()) {
                    option.selected = true;
                }
                yearSelect.appendChild(option);
            }
        }

        // Initialize the system once DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeAttendanceSystem();
        });
    </script>
</body>
</html>
